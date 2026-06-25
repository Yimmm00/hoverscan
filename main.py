import io
import uvicorn
from fastapi import FastAPI, UploadFile, File, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles  
from PIL import Image
from ultralytics import YOLO
from fastapi import BackgroundTasks
import numpy as np
import os
import cv2  
import uuid 
import asyncio
import json
import math
import torch  
from sse_starlette.sse import EventSourceResponse
import pymysql
import time
import laspy

# 1. Initialize FastAPI Application Configuration
app = FastAPI(title="Hoverscan AI Backend", description="YOLOv8 Inference API with Real-time Defect Geotagging & Severity Analytics")

model = YOLO('best.pt')

# 2. Enable CORS for Connections
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

# Select the target GPU graphics layout framework (uses cuda:1 if multi-GPU is available)
device = "cuda:1" if torch.cuda.is_available() and torch.cuda.device_count() > 1 else ("cuda" if torch.cuda.is_available() else "cpu")
print(f"Accelerating inference pipeline using device layout framework: System target platform -> {device.upper()}")

try:
    if os.path.exists(model_path):
        model = YOLO(model_path).to(device)
        print(f"Custom model loaded successfully from {model_path}")
    else:
        print(f"Custom weights file 'best.pt' not found at {model_path}. Downloading fallback model...")
        model = YOLO("yolov8n.pt").to(device)
except Exception as e:
    print(f"Error loading custom model: {e}. Falling back to default nano model.")
    model = YOLO("yolov8n.pt").to(device)

# --- COLD START OPTIMIZATION: Warm up the inference engine immediately on server boot ---
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

# Dynamic Telemetry Utilities with Altitude/FOV Scaling Support
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

def clear_old_media_cache(max_age_seconds=21600):  
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
        print(f"[+] Media Cache Cleanup complete: Scrubbed {deleted_count} stale asset files.")


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

@app.post("/analyze")
async def analyze_image(
    file: UploadFile = File(...), 
    temperature: float = 31.0, 
    humidity: float = 78.0,
    bridge_name: str = "Batang Sadong Bridge"
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

        # --- DATABASE SCHEMA SYNCHRONIZATION TRANSACTION ---
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

@app.get("/")
async def root():
    return {"status": "online", "model": "YOLOv8-Bridge-Damage-v6", "device": device}

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8001)