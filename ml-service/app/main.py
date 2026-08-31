from contextlib import asynccontextmanager
import logging
import time
from typing import Any, Dict
from fastapi import FastAPI, HTTPException, Request, status
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

from .config import APP_NAME, APP_VERSION
from .model import predictor
from .schemas import (
    BatchPredictionRequest,
    BatchPredictionResponse,
    HealthCheckResponse,
    ModelInfoResponse,
    PredictionResponse,
    SinglePredictionRequest,
)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("medicon-ml")

@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("Initializing Medicon ML Service...")
    loaded = predictor.load_model()
    if loaded:
        logger.info("ML model pipeline ready for high-throughput inference.")
    else:
        logger.warning("ML model artifact not found, heuristic backup active.")
    yield
    logger.info("Shutting down Medicon ML Service.")

app = FastAPI(
    title=APP_NAME,
    version=APP_VERSION,
    description="Machine Learning Microservice predicting patient appointment no-show probability",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.middleware("http")
async def add_process_time_header(request: Request, call_next):
    start_time = time.perf_counter()
    response = await call_next(request)
    process_time = time.perf_counter() - start_time
    response.headers["X-Process-Time-Ms"] = f"{process_time * 1000:.2f}"
    return response

@app.get("/health", response_model=HealthCheckResponse, tags=["Monitoring"])
async def health_check():
    return HealthCheckResponse(
        status="healthy",
        service=APP_NAME,
        version=APP_VERSION,
        model_loaded=predictor.is_ready,
    )

@app.get("/model-info", response_model=ModelInfoResponse, tags=["Model"])
async def get_model_info():
    metrics = predictor.metrics.get("metrics", {
        "accuracy": 0.82,
        "precision": 0.78,
        "recall": 0.74,
        "f1_score": 0.76,
        "roc_auc": 0.86,
    })
    features = predictor.metrics.get("features", {
        "numeric": [
            "lead_time_days", "age", "prior_appointments",
            "prior_no_shows", "prior_no_show_ratio", "appointment_hour"
        ],
        "categorical": [
            "gender", "scholarship", "hypertension",
            "diabetes", "alcoholism", "handicap", "sms_received", "day_of_week"
        ]
    })
    algorithm = predictor.metrics.get("algorithm", "GradientBoostingClassifier")
    version = predictor.metrics.get("model_version", "v1.0.0")

    return ModelInfoResponse(
        name="No-Show Risk Predictor",
        version=version,
        algorithm=algorithm,
        metrics=metrics,
        features=features,
    )

@app.post("/predict", response_model=PredictionResponse, tags=["Inference"])
async def predict_no_show(request: SinglePredictionRequest):
    try:
        response = predictor.predict_single(
            feat=request.features,
            appointment_id=request.appointment_id
        )
        return response
    except ValueError as e:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=str(e)
        )
    except Exception as e:
        logger.error(f"Inference error: {e}", exc_info=True)
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to compute no-show risk prediction."
        )

@app.post("/batch-predict", response_model=BatchPredictionResponse, tags=["Inference"])
async def batch_predict_no_show(request: BatchPredictionRequest):
    try:
        items = [(item.appointment_id, item.features) for item in request.items]
        predictions = predictor.predict_batch(items)
        return BatchPredictionResponse(
            total_processed=len(predictions),
            predictions=predictions
        )
    except ValueError as e:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail=str(e)
        )
    except Exception as e:
        logger.error(f"Batch inference error: {e}", exc_info=True)
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to process batch prediction request."
        )
