import sys
import os
import argparse
import json
import uuid
import math
import numpy as np
import pymysql
from PIL import Image
import torch
from ultralytics import YOLO

# Define strict metric evaluation constants
MOISTURE_RISK_CLASSES = ['rust', 'mold', 'staining', 'peeling']
THERMAL_STRESS_CLASSES = ['crack', 'spalling', 'spalling expose rebar', 'bridge joint']

def parse_args():
    parser = argparse.ArgumentParser(description="Hoverscan CLI Inference Pipeline Engine")
    parser.add_argument("--image", required=True, help="Absolute filesystem path to the target image")
    parser.add_argument("--temp", type=float, default=31.0, help="Field ambient temperature metric")
    parser.add_argument("--humidity", type=float, default=78.0, help="Field relative humidity status percentage")
    parser.add_argument("--bridge", default="Batang Sadong Bridge", help="Name of structural target node")
    return parser.parse_parse_args() if hasattr(parser, 'parse_parse_args') else parser.parse_args()

def get_db_connection():
    return pymysql.connect(
        host="localhost",
        user="root",
        password="",
        database="hoverscan",
        cursorclass=pymysql.cursors.DictCursor
    )

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
    base_modifier = 1.0
    if humidity > 80.0 and class_name in MOISTURE_RISK_CLASSES:
        base_modifier += 0.35  
    if temperature > 32.0 and class_name in THERMAL_STRESS_CLASSES:
        base_modifier += 0.25  
    return round(base_modifier, 2)

def main():
    args = parse_args()
    
    if not os.path.exists(args.image):
        print(json.dumps({"error": f"Target capture frame not found: {args.image}"}))
        sys.exit(1)

    try:
        # Auto-configure hardware target matching local CUDA parameters
        device = "cuda:1" if torch.cuda.is_available() and torch.cuda.device_count() > 1 else ("cuda" if torch.cuda.is_available() else "cpu")
        
        # Load localized weights
        model_path = os.path.join(os.path.dirname(__file__), "best.pt")
        model = YOLO(model_path).to(device)
        
        image = Image.open(args.image).convert("RGB")
        results = model.predict(source=image, conf=0.1, save=False, verbose=False, device=device)
        
        detections = []
        for r in results:
            for box in r.boxes:
                coords = box.xyxyn[0].tolist()
                raw_class_name = str(model.names[int(box.cls[0])]).lower()
                confidence = float(box.conf[0])
                
                class_name = "concrete spalling" if raw_class_name == "spalling" else raw_class_name
                dimensions = estimate_defect_dimensions(coords, class_name)
                env_stress = calculate_environmental_stress_factor(args.temp, args.humidity, class_name)
                
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
            print(json.dumps({"message": "No defects detected", "highest_defect": None, "all_detections": []}))
            sys.exit(0)

        detections.sort(key=lambda x: x["confidence"] * x["environmental_stress_modifier"], reverse=True)
        highest_defect = detections[0]

        # Extract file reference relative path matching your Laravel configurations
        filename = os.path.basename(args.image)
        laravel_relative_path = f"uploads/{filename}"

        # Write execution rows to master transaction schemas
        connection = get_db_connection()
        with connection.cursor() as cursor:
            sql = """
            INSERT INTO defect_records 
            (dataset_id, bridge_name, defect_class, severity, confidence_score, image_path, humidity, temperature, bbox_coordinates) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            for defect in detections:
                dataset_id = f"AST-{uuid.uuid4().hex[:10].upper()}"
                cursor.execute(sql, (
                    dataset_id,
                    args.bridge,
                    defect["type"],
                    defect["severity"],
                    defect["confidence"],
                    laravel_relative_path,
                    int(args.humidity),
                    int(args.temp),
                    json.dumps(defect["bbox"])
                ))
            
            # Increment overall anomaly logs context count within master asset index row
            cursor.execute(
                "UPDATE bridges SET total_anomalies = total_anomalies + %s, last_inspection = NOW() WHERE name = %s",
                (len(detections), args.bridge)
            )
            
        connection.commit()
        connection.close()

        # Output payload back into Laravel buffer stream
        print(json.dumps({
            "highest_defect": highest_defect,
            "count": len(detections),
            "all_detections": detections
        }))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()