import pytest
from fastapi.testclient import TestClient
from app.main import app

@pytest.fixture
def client():
    with TestClient(app) as test_client:
        yield test_client

def test_health_endpoint(client):
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "healthy"
    assert "version" in data
    assert "model_loaded" in data

def test_model_info_endpoint(client):
    response = client.get("/model-info")
    assert response.status_code == 200
    data = response.json()
    assert "metrics" in data
    assert "features" in data
    assert "algorithm" in data

def test_predict_endpoint_valid_payload(client):
    payload = {
        "appointment_id": 42,
        "features": {
            "lead_time_days": 10,
            "age": 40,
            "gender": "F",
            "scholarship": 0,
            "hypertension": 0,
            "diabetes": 0,
            "alcoholism": 0,
            "handicap": 0,
            "sms_received": 1,
            "prior_appointments": 3,
            "prior_no_shows": 0,
            "day_of_week": 2,
            "appointment_hour": 14
        }
    }
    response = client.post("/predict", json=payload)
    assert response.status_code == 200
    data = response.json()
    assert data["appointment_id"] == 42
    assert "no_show_probability" in data
    assert 0.0 <= data["no_show_probability"] <= 1.0
    assert data["risk_level"] in ["LOW", "MEDIUM", "HIGH"]
    assert isinstance(data["contributing_factors"], list)
    assert "model_version" in data

def test_predict_endpoint_validation_error(client):
    payload = {
        "appointment_id": 43,
        "features": {
            "lead_time_days": -5,  # Invalid negative lead time
            "age": 40,
        }
    }
    response = client.post("/predict", json=payload)
    assert response.status_code == 422

def test_predict_prior_no_shows_exceeding_appointments(client):
    payload = {
        "features": {
            "lead_time_days": 5,
            "age": 30,
            "prior_appointments": 2,
            "prior_no_shows": 5  # Invalid: cannot exceed prior_appointments
        }
    }
    response = client.post("/predict", json=payload)
    assert response.status_code == 422

def test_batch_predict_endpoint(client):
    payload = {
        "items": [
            {
                "appointment_id": 1,
                "features": {
                    "lead_time_days": 1,
                    "age": 60,
                    "prior_appointments": 10,
                    "prior_no_shows": 0
                }
            },
            {
                "appointment_id": 2,
                "features": {
                    "lead_time_days": 35,
                    "age": 21,
                    "scholarship": 1,
                    "sms_received": 0,
                    "prior_appointments": 4,
                    "prior_no_shows": 3
                }
            }
        ]
    }
    response = client.post("/batch-predict", json=payload)
    assert response.status_code == 200
    data = response.json()
    assert data["total_processed"] == 2
    assert len(data["predictions"]) == 2
    assert data["predictions"][0]["appointment_id"] == 1
    assert data["predictions"][1]["appointment_id"] == 2
