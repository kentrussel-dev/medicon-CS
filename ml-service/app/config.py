import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
MODEL_PATH = os.getenv("MODEL_PATH", str(BASE_DIR / "models" / "noshow_model_v1.joblib"))
METRICS_PATH = os.getenv("METRICS_PATH", str(BASE_DIR / "models" / "metrics.json"))
HIGH_RISK_THRESHOLD = float(os.getenv("HIGH_RISK_THRESHOLD", "0.65"))
MEDIUM_RISK_THRESHOLD = float(os.getenv("MEDIUM_RISK_THRESHOLD", "0.35"))
APP_VERSION = "1.0.0"
APP_NAME = "Medicon ML No-Show Prediction Service"
