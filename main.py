import io
import uvicorn
from fastapi import FastAPI, UploadFile, File, HTTPException, Form, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles  
from PIL import Image
from ultralytics import YOLO
import numpy as np
import os
import cv2  
import uuid 
import asyncio
import json
import math
import torch  # Detect hardware capability
from sse_starlette.sse import EventSourceResponse
import pymysql
import time
import laspy

# 1. Initialize FastAPI Application Configuration
app = FastAPI(title="Hoverscan AI Backend", description="YOLOv8 Inference API with Real-time Defect Geotagging & Severity Analytics")

# 2. Enable CORS for React/Blade Frontend Connections
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
) 

# Ensure Local Media Processing Cache Ecosystem Exists
os.makedirs("static/processed_videos", exist_ok=True)
os.makedirs("static/uploads", exist_ok=True)
os.makedirs("temp", exist_ok=True)
app.mount("/static", StaticFiles(directory="static"), name="static")

# 3. Load YOLOv8 Model with Hardware Acceleration Auto-Detection
base_dir = os.path.dirname(os.path.abspath(__file__))
model_path = os.path.join(base_dir, "best.pt")

# Select the specific target GPU index (cuda:1 for your RTX 2060)
device = "cuda:1" if torch.cuda.is_available() and torch.cuda.device_count() > 1 else ("cuda" if torch.cuda.is_available() else "cpu")
print(f"Accelerating inference pipeline using device layout framework: System target platform -> {device.upper()}")

try:
    model = YOLO(model_path).to(device)
    print(f"Custom model loaded successfully from {model_path}")
except Exception as e:
    print(f"Error loading custom model: {e}. Falling back to default nano model.")
    model = YOLO("yolov8n.pt").to(device)

# --- COLD START OPTIMIZATION: Warm up the GPU/CPU allocation cache immediately on server boot ---
print("Warming up YOLO inference engine...")
try:
    dummy_frame = np.zeros((640, 640, 3), dtype=np.uint8)
    model.predict(source=dummy_frame, verbose=False, device=device)
    print("Inference engine warmed up and ready.")
except Exception as warmup_err:
    print(f"Inference warm-up skipped: {warmup_err}")


# 4. Database Connection Utility (XAMPP MariaDB Architecture Setup)
def get_db_connection():
    return pymysql.connect(
        host="localhost",
        user="root",         # Default XAMPP username
        password="",         # Default XAMPP password is empty
        database="hoverscan",
        cursorclass=pymysql.cursors.DictCursor
    )

# Updated Dynamic Telemetry Utilities with Altitude/FOV Scaling Support
def calculate_object_gps(cam_lat, cam_lng, heading, bbox, frame_width, frame_height, altitude=5.0, fov_horizontal=80.0):
    x_center = (bbox[0] + bbox[2]) / 2.0
    y_bottom = bbox[3]  
    pixel_offset_x = x_center - 0.5  
    pixel_offset_y = 1.0 - y_bottom  
    
    fov_rad_h = math.radians(fov_horizontal)
    fov_rad_v = fov_rad_h * (frame_height / frame_width)
    
    total_width_m = 2.0 * altitude * math.tan(fov_rad_h / 2.0)
    total_height_m = 2.0 * altitude * math.tan(fov_rad_v / 2.0)
    
    dist_forward = pixel_offset_y * total_height_m
    dist_lateral = pixel_offset_x * total_width_m
    
    total_distance = math.sqrt(dist_forward**2 + dist_lateral**2)
    angle_offset = math.degrees(math.atan2(dist_lateral, dist_forward))
    target_heading = (heading + angle_offset) % 360
    
    earth_radius = 6378137.0  
    d_lat = (total_distance * math.cos(math.radians(target_heading))) / earth_radius
    d_lng = (total_distance * math.sin(math.radians(target_heading))) / (earth_radius * math.cos(math.radians(cam_lat)))
    
    obj_lat = cam_lat + math.degrees(d_lat)
    obj_lng = cam_lng + math.degrees(d_lng)
    
    return round(obj_lat, 6), round(obj_lng, 6)

def clear_old_media_cache(max_age_seconds=21600):  # Default: 6 hours
    target_dirs = ["temp", "static/uploads", "static/processed_videos"]
    now = time.time()
    deleted_count = 0
    
    for folder in target_dirs:
        if not os.path.exists(folder):
            continue
        for filename in os.listdir(folder):
            file_path = os.path.join(folder, filename)
            if filename.startswith('.'):
                continue
            try:
                if os.path.isfile(file_path):
                    file_time = os.path.getmtime(file_path)
                    if (now - file_time) > max_age_seconds:
                        os.remove(file_path)
                        deleted_count += 1
            except Exception as e:
                print(f"[-] Failed to delete cache asset {file_path}: {e}")
                
    if deleted_count > 0:
        print(f"[+] Media Cache Cleanup complete: Scrubbed {deleted_count} stale asset files from server storage.")


def estimate_defect_dimensions(bbox, class_name, altitude=5.0, fov_horizontal=80.0, aspect_ratio=1.777):
    norm_w = bbox[2] - bbox[0]
    norm_h = bbox[3] - bbox[1]

    fov_rad_h = math.radians(fov_horizontal)
    fov_rad_v = fov_rad_h / aspect_ratio

    total_width_m = 2.0 * altitude * math.tan(fov_rad_h / 2.0)
    total_height_m = 2.0 * altitude * math.tan(fov_rad_v / 2.0)

    actual_w_m = norm_w * total_width_m
    actual_h_m = norm_h * total_height_m

    if class_name in ['crack', 'bridge joint']:
        linear_extent = math.sqrt(actual_w_m**2 + actual_h_m**2)
        return {"value": round(linear_extent, 2), "unit": "m", "metric_type": "Linear Extension"}
    else:
        surface_area_sqm = actual_w_m * actual_h_m
        return {"value": round(surface_area_sqm, 3), "unit": "m²", "metric_type": "Surface Area Footprint"}


def calculate_environmental_stress_factor(temperature, humidity, class_name):
    moisture_risk_classes = ['rust', 'mold', 'staining', 'peeling']
    thermal_stress_classes = ['crack', 'spalling', 'spalling expose rebar', 'bridge joint']

    base_modifier = 1.0
    if humidity > 80.0 and class_name in moisture_risk_classes:
        base_modifier += 0.35  
    if temperature > 32.0 and class_name in thermal_stress_classes:
        base_modifier += 0.25  

    return round(base_modifier, 2)

def process_and_cache_las(file_path: str, output_json_path: str, sample_rate: int = 100):
    try:
        print(f"[+] Reading spatial data from {file_path}...")
        las = laspy.read(file_path)
        
        x_raw = np.array(las.x)[::sample_rate]
        y_raw = np.array(las.y)[::sample_rate]
        z_raw = np.array(las.z)[::sample_rate]
        
        x_centered = x_raw - np.mean(x_raw)
        y_centered = y_raw - np.mean(y_raw)
        z_centered = z_raw - np.mean(z_raw)
        
        spread_x = float(np.max(x_centered) - np.min(x_centered)) or 1.0
        spread_y = float(np.max(y_centered) - np.min(y_centered)) or 1.0
        spread_z = float(np.max(z_centered) - np.min(z_centered)) or 1.0
        
        max_spread = max(spread_x, spread_y, spread_z)
        master_scale = 140.0
        
        point_cloud_data = []
        for x, y, z in zip(x_centered, y_centered, z_centered):
            point_cloud_data.append({
                "x": round((float(x) / max_spread) * master_scale, 3), 
                "y": round((float(y) / max_spread) * master_scale, 3),  
                "z": round((float(z) / max_spread) * master_scale, 3)
            })
            
        with open(output_json_path, 'w') as f:
            json.dump(point_cloud_data, f)
            
        print(f"[+] Successfully generated 3D cloud cache asset: {output_json_path} ({len(point_cloud_data)} points)")
        return True
    except Exception as e:
        print(f"[-] Failed processing point cloud matrix layer: {e}")
        return False

@app.get("/api/dashboard/stats")
async def get_dashboard_stats():
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            cursor.execute("SELECT COUNT(*) as total_bridges FROM bridges")
            total_bridges = cursor.fetchone()['total_bridges']
            
            cursor.execute("SELECT COUNT(*) as total_anomalies FROM defect_records")
            total_anomalies = cursor.fetchone()['total_anomalies']
            
            cursor.execute("SELECT COUNT(*) as critical_count FROM defect_records WHERE severity = 'Critical'")
            critical_count = cursor.fetchone()['critical_count']
            
            cursor.execute("SELECT AVG(confidence_score) as avg_conf FROM defect_records")
            avg_conf_res = cursor.fetchone()['avg_conf']
            avg_confidence = round(float(avg_conf_res) * 100, 1) if avg_conf_res else 0.0
            
            cursor.execute("""
                SELECT defect_class, COUNT(*) as instance_count 
                FROM defect_records 
                GROUP BY defect_class
            """)
            distribution_rows = cursor.fetchall()
            
            cursor.execute("""
                SELECT bridge_name, defect_class, severity,
                       DATE_FORMAT(created_at, '%i minutes ago') as timing 
                FROM defect_records 
                ORDER BY created_at DESC LIMIT 4
            """)
            recent_logs = cursor.fetchall()

        breakdown = {row['defect_class']: row['instance_count'] for row in distribution_rows}

        return {
            "status": "success",
            "total_bridges": total_bridges,
            "total_anomalies": total_anomalies,
            "critical_count": critical_count,
            "avg_confidence": f"{avg_confidence}%" if avg_confidence > 0 else "0.0%",
            "confidence_raw": avg_confidence,
            "distribution": breakdown,
            "recent_logs": recent_logs
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Metrics aggregation loop crashed: {str(e)}")
    finally:
        connection.close()

@app.get("/api/defect-class-sample/{class_name}")
async def get_defect_class_sample(class_name: str):
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            sql = "SELECT image_path FROM defect_records WHERE defect_class = %s ORDER BY created_at DESC LIMIT 1"
            cursor.execute(sql, (class_name,))
            record = cursor.fetchone()
        
        if record and record['image_path']:
            path = record['image_path']
            full_url = f"http://localhost:8001{path}" if path.startswith("/") else f"http://localhost:8001/{path}"
            return {"found": True, "image_url": full_url}
        
        return {"found": False, "image_url": None}
    except Exception as e:
        return {"found": False, "detail": f"Database lookup failed: {str(e)}"}
    finally:
        connection.close()


@app.get("/api/defect-class-records/{class_name}")
async def get_all_defect_records_by_class(class_name: str, bridge_name: str = None):
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            base_query = """
                SELECT dataset_id, bridge_name, defect_class, severity, confidence_score, image_path, 
                       DATE_FORMAT(created_at, '%%Y-%%m-%%d %%H:%%i') as date_logged 
                FROM defect_records 
            """
            
            if class_name.lower() == "all" and bridge_name:
                sql = base_query + " WHERE bridge_name = %s ORDER BY created_at DESC"
                cursor.execute(sql, (str(bridge_name),))
            elif bridge_name and bridge_name.strip() and bridge_name != "undefined":
                sql = base_query + " WHERE defect_class = %s AND bridge_name = %s ORDER BY created_at DESC"
                cursor.execute(sql, (str(class_name), str(bridge_name)))
            else:
                sql = base_query + " WHERE defect_class = %s ORDER BY created_at DESC"
                cursor.execute(sql, (str(class_name),))
                
            records = cursor.fetchall()
            
        for r in records:
            if r.get('image_path'):
                path = r['image_path']
                r['image_url'] = f"http://localhost:8001{path}" if path.startswith("/") else f"http://localhost:8001/{path}"
            else:
                r['image_url'] = None
                
        return {"status": "success", "count": len(records), "data": records}
    except Exception as e:
        print(f"[-] PYMYSQL CRASH LOG ENCOUNTERED: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Database execution failed: {str(e)}")
    finally:
        connection.close()

@app.get("/api/bridges/blueprint/mukah")
async def get_mukah_blueprint_cloud():
    raw_las_source = os.path.join("static", "uploads", "batang_mukah_bridge.las")
    cached_json_output = os.path.join("static", "uploads", "mukah_cache.json")
    
    if os.path.exists(cached_json_output):
        with open(cached_json_output, 'r') as f:
            return {"status": "success", "points": json.load(f)}
            
    if os.path.exists(raw_las_source):
        loop = asyncio.get_running_loop()
        success = await loop.run_in_executor(
            None, 
            lambda: process_and_cache_las(raw_las_source, cached_json_output, sample_rate=200)
        )
        if success and os.path.exists(cached_json_output):
            with open(cached_json_output, 'r') as f:
                return {"status": "success", "points": json.load(f)}
                
    return {
        "status": "simulation", 
        "message": "Processing raw file or file not found. Keep checking back.",
        "points": []
    }

@app.post("/api/bridges/register")
async def register_new_bridge(payload: dict):
    if not payload.get("name") or not payload.get("location_coords"):
        raise HTTPException(status_code=400, detail="Missing required parameters: name, location_coords")
        
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            sql = "INSERT INTO bridges (name, district, location_coords, total_anomalies) VALUES (%s, %s, %s, 0)"
            cursor.execute(sql, (payload.get("name"), payload.get("district", "Sarawak"), payload.get("location_coords")))
        connection.commit()
        return {"status": "success", "message": f"Bridge '{payload.get('name')}' registered successfully."}
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Database execution crash: {str(e)}")
    finally:
        connection.close()


@app.get("/api/bridges")
async def get_all_bridges():
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            cursor.execute("SELECT id, name, district, location_coords, DATE_FORMAT(last_inspection, '%Y-%m-%d') as last_inspection, total_anomalies FROM bridges")
            records = cursor.fetchall()
        return {"status": "success", "data": records}
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Bridges fetch failed: {str(e)}")
    finally:
        connection.close()


@app.post("/analyze")
async def analyze_image(
    file: UploadFile = File(...), 
    temperature: float = Form(31.0), 
    humidity: float = Form(78.0),
    bridge_name: str = Form("Batang Sadong Bridge")
):
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File must be an image type asset context")

    try:
        contents = await file.read()
        image = Image.open(io.BytesIO(contents)).convert("RGB")

        unique_file_prefix = uuid.uuid4().hex[:8]
        clean_filename = f"{unique_file_prefix}_{file.filename.replace(' ', '_')}"
        local_upload_path = os.path.join("static", "uploads", clean_filename)
        
        with open(local_upload_path, "wb") as buffer:
            buffer.write(contents)

        results = model.predict(source=image, conf=0.1, save=False, device=device)
        
        detections = []
        for r in results:
            for box in r.boxes:
                coords = box.xyxyn[0].tolist()
                raw_class_name = str(model.names[int(box.cls[0])]).lower()
                confidence = float(box.conf[0])
                
                if raw_class_name == "spalling":
                    class_name = "concrete spalling"
                else:
                    class_name = raw_class_name
                
                dimensions = estimate_defect_dimensions(coords, class_name)
                env_stress = calculate_environmental_stress_factor(temperature, humidity, class_name)
                
                adjusted_severity_score = confidence * env_stress
                severity_tier = "Low"
                
                if class_name == "spalling expose rebar":
                    severity_tier = "Critical"
                elif class_name == "concrete spalling":
                    severity_tier = "High" if adjusted_severity_score > 0.75 else "Medium"
                elif adjusted_severity_score > 1.1:
                    severity_tier = "Critical"
                elif adjusted_severity_score > 0.75:
                    severity_tier = "High"
                elif adjusted_severity_score > 0.45:
                    severity_tier = "Medium"

                detections.append({
                    "type": class_name,
                    "confidence": confidence,
                    "bbox": coords,
                    "severity": severity_tier,
                    "dimensions": dimensions,
                    "environmental_stress_modifier": env_stress
                })

        if not detections:
            return {"message": "No defects detected", "highest_defect": None, "all_detections": []}

        detections.sort(key=lambda x: x["confidence"] * x["environmental_stress_modifier"], reverse=True)
        highest_defect = detections[0]

        try:
            connection = get_db_connection()
            with connection.cursor() as cursor:
                sql = """
                INSERT INTO defect_records 
                (dataset_id, bridge_name, defect_class, severity, confidence_score, image_path, humidity, temperature) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                """
                for defect in detections:
                    dataset_id = f"AST-{uuid.uuid4().hex[:10].upper()}"
                    saved_image_path = f"/static/uploads/{clean_filename}"
                    
                    cursor.execute(sql, (
                        dataset_id,
                        bridge_name,
                        defect["type"],
                        defect["severity"],
                        defect["confidence"],
                        saved_image_path,
                        int(humidity),
                        int(temperature)
                    ))
            connection.commit()
            print(f"[+] Successfully saved {len(detections)} inference records to the database.")
        except Exception as db_err:
            print(f"[-] Database batch insertion error logged: {db_err}")
        finally:
            connection.close()

        return {
            "highest_defect": highest_defect,
            "count": len(detections),
            "all_detections": detections
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


class TemporalTrackSmoother:
    def __init__(self, max_age_frames=10, smoothing_alpha=0.4):
        self.max_age_frames = max_age_frames
        self.smoothing_alpha = smoothing_alpha
        self.active_tracks = {}

    def update_and_smooth(self, current_frame_detections):
        seen_ids = set()
        smoothed_outputs = []

        for track_id, cls_idx, bbox, conf in current_frame_detections:
            seen_ids.add(track_id)
            if track_id in self.active_tracks:
                prev_bbox = self.active_tracks[track_id]["bbox"]
                smoothed_bbox = [
                    self.smoothing_alpha * current_coord + (1 - self.smoothing_alpha) * prev_coord
                    for current_coord, prev_coord in zip(bbox, prev_bbox)
                ]
                self.active_tracks[track_id] = {
                    "bbox": smoothed_bbox,
                    "cls": cls_idx,
                    "conf": max(conf, self.active_tracks[track_id]["conf"]),
                    "age": 0
                }
            else:
                self.active_tracks[track_id] = {
                    "bbox": bbox,
                    "cls": cls_idx,
                    "conf": conf,
                    "age": 0
                }

        dead_ids = []
        for track_id in list(self.active_tracks.keys()):
            if track_id not in seen_ids:
                self.active_tracks[track_id]["age"] += 1
                if self.active_tracks[track_id]["age"] > self.max_age_frames:
                    dead_ids.append(track_id)

        for track_id in dead_ids:
            del self.active_tracks[track_id]

        for track_id, data in self.active_tracks.items():
            smoothed_outputs.append((track_id, data["cls"], data["bbox"], data["conf"]))

        return smoothed_outputs


@app.post("/analyze-video")
async def analyze_video(
    file: UploadFile = File(...), 
    temperature: float = Form(31.0), 
    humidity: float = Form(78.0),
    bridge_name: str = Form("Batang Sadong Bridge"),
    background_tasks: BackgroundTasks = BackgroundTasks()
):
    if not file.content_type.startswith("video/"):
        raise HTTPException(status_code=400, detail="Uploaded file must be a video format stream context")

    unique_id = uuid.uuid4().hex
    input_file_path = os.path.join("temp", f"input_{unique_id}_{file.filename}")
    final_web_filename = f"processed_{unique_id}.webm"
    final_web_path = os.path.join("static", "processed_videos", final_web_filename)

    try:
        contents = await file.read()
        with open(input_file_path, "wb") as f:
            f.write(contents)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to write uploaded file: {str(e)}")

    async def progress_generator():
        cap = None
        out = None
        try:
            yield {"data": json.dumps({"status": "processing", "progress": 0, "status_text": "Opening optical media file stream..."})}

            cap = cv2.VideoCapture(input_file_path)
            if not cap.isOpened():
                yield {"data": json.dumps({"status": "error", "message": "OpenCV failed to open input source path."})}
                return

            width  = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
            height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
            fps    = int(cap.get(cv2.CAP_PROP_FPS)) or 30
            
            try:
                total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
                if total_frames <= 0: total_frames = 1
            except Exception:
                total_frames = 1

            fourcc = cv2.VideoWriter_fourcc(*'VP80')
            out = cv2.VideoWriter(final_web_path, fourcc, fps, (width, height))

            yield {"data": json.dumps({"status": "processing", "progress": 1, "status_text": "Spreading sequential tracker weights..."})}

            smoother = TemporalTrackSmoother(max_age_frames=12, smoothing_alpha=0.35)
            verified_defect_registry = set()
            geotagged_detections_payload = []
            
            has_saved_screenshot = False
            snapshot_filename = f"snapshot_{unique_id}.jpg"
            snapshot_local_path = os.path.join("static", "uploads", snapshot_filename)
            snapshot_url = None

            base_lat, base_lng = 1.4473, 110.6897
            current_heading = 45.0
            frame_count = 0
            loop = asyncio.get_running_loop()

            while cap.isOpened():
                ret, frame = await loop.run_in_executor(None, cap.read)
                if not ret:
                    break
                
                raw_frame_detections = []

                if frame_count % 1 == 0:
                    results = await loop.run_in_executor(
                        None, 
                        lambda: model.track(
                            source=frame, conf=0.12, iou=0.45, imgsz=640, 
                            persist=True, tracker="botsort.yaml", save=False, verbose=False, device=device
                        )
                    )
                    
                    boxes = results[0].boxes
                    if boxes is not None and hasattr(boxes, 'id') and boxes.id is not None:
                        track_ids = boxes.id.int().tolist()
                        class_indices = boxes.cls.int().tolist()
                        box_coordinates = boxes.xyxy.tolist()
                        confidences = boxes.conf.tolist()
                        
                        for tid, cidx, bbox, conf in zip(track_ids, class_indices, box_coordinates, confidences):
                            raw_frame_detections.append((tid, cidx, bbox, conf))

                active_smoothed_tracks = smoother.update_and_smooth(raw_frame_detections)

                if active_smoothed_tracks and not has_saved_screenshot:
                    await loop.run_in_executor(None, lambda: cv2.imwrite(snapshot_local_path, frame))
                    snapshot_url = f"/static/uploads/{snapshot_filename}"
                    has_saved_screenshot = True

                for tid, cidx, bbox, conf in active_smoothed_tracks:
                    raw_class_name = model.names[cidx].lower()
                    if raw_class_name == "spalling":
                        class_name = "concrete spalling"
                    else:
                        class_name = raw_class_name
                        
                    x1, y1, x2, y2 = map(int, bbox)
                    box_color = (229, 70, 79) if class_name == 'spalling expose rebar' else (198, 99, 79)
                    
                    cv2.rectangle(frame, (x1, y1), (x2, y2), box_color, 2)
                    label_text = f"{class_name.upper()} #{tid} ({int(conf*100)}%)"
                    (w, h), _ = cv2.getTextSize(label_text, cv2.FONT_HERSHEY_SIMPLEX, 0.45, 1)
                    cv2.rectangle(frame, (x1, y1 - h - 6), (x1 + w + 10, y1), box_color, -1)
                    cv2.putText(frame, label_text, (x1 + 5, y1 - 4), cv2.FONT_HERSHEY_SIMPLEX, 0.45, (255, 255, 255), 1, cv2.LINE_AA)

                    unique_defect_id = f"{class_name}-{tid}"
                    if unique_defect_id not in verified_defect_registry:
                        verified_defect_registry.add(unique_defect_id)
                        
                        norm_bbox = [x1/width, y1/height, x2/width, y2/height]
                        simulated_drift_factor = frame_count * 0.000005
                        current_frame_lat = base_lat + (simulated_drift_factor * math.cos(math.radians(current_heading)))
                        current_frame_lng = base_lng + (simulated_drift_factor * math.sin(math.radians(current_heading)))

                        obj_lat, obj_lng = calculate_object_gps(
                            current_frame_lat, current_frame_lng, current_heading,
                            norm_bbox, width, height
                        )
                        
                        dimensions = estimate_defect_dimensions(norm_bbox, class_name)
                        env_stress = calculate_environmental_stress_factor(temperature, humidity, class_name)

                        geotagged_detections_payload.append({
                            "id": unique_defect_id,
                            "type": class_name,
                            "confidence": float(conf),
                            "bbox": norm_bbox,
                            "geotag": f"{obj_lat}, {obj_lng}",
                            "dimensions": dimensions,
                            "environmental_stress_modifier": env_stress
                        })

                await loop.run_in_executor(None, lambda: out.write(frame))
                frame_count += 1
                
                if total_frames > 0 and frame_count % 15 == 0:
                    progress_pct = min(99, int((frame_count / total_frames) * 100))
                    yield {"data": json.dumps({"status": "processing", "progress": progress_pct, "status_text": f"Analyzing video frames: {progress_pct}%"})}
                
                await asyncio.sleep(0.001)

            cap.release()
            out.release()
            cap = None
            out = None

            video_url = f"/static/processed_videos/{final_web_filename}"

            if geotagged_detections_payload:
                try:
                    db_conn = get_db_connection()
                    with db_conn.cursor() as db_cursor:
                        sql_vid_query = """
                        INSERT INTO defect_records 
                        (dataset_id, bridge_name, defect_class, severity, confidence_score, image_path, humidity, temperature) 
                        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                        """
                        for defect in geotagged_detections_payload:
                            vid_dataset_id = f"AST-{uuid.uuid4().hex[:10].upper()}"
                            vid_relative_url_path = f"/static/uploads/{snapshot_filename}" if has_saved_screenshot else f"/static/processed_videos/{final_web_filename}"
                            
                            db_cursor.execute(sql_vid_query, (
                                vid_dataset_id,
                                bridge_name,
                                defect["type"],
                                "High" if defect["confidence"] > 0.6 else "Medium",
                                defect["confidence"],
                                vid_relative_url_path,
                                int(humidity),
                                int(temperature)
                            ))
                    db_conn.commit()
                except Exception as vid_db_err:
                    print(f"Video SQL tracking transaction failed: {vid_db_err}")
                finally:
                    db_conn.close()

            yield {"data": json.dumps({
                "status": "complete", 
                "progress": 100, 
                "video_url": video_url,
                "snapshot_url": snapshot_url, \
                "all_detections": geotagged_detections_payload
            })}

            background_tasks.add_task(clear_old_media_cache)
        except Exception as e:
            yield {"data": json.dumps({"status": "error", "message": f"Pipeline crash trace: {str(e)}"})}
        finally:
            if cap is not None: cap.release()
            if out is not None: out.release()
            if os.path.exists(input_file_path):
                try: os.remove(input_file_path)
                except Exception: pass

    return EventSourceResponse(progress_generator())

@app.get("/")
async def root():
    return {"status": "online", "model": "YOLOv8-Bridge-Damage-v6", "device": device}

if __name__ == "__main__":
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)