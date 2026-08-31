import pytest
from app.model import NoShowPredictor
from app.schemas import AppointmentFeatureInput, GenderEnum, RiskLevel

@pytest.fixture
def predictor():
    return NoShowPredictor()

def test_single_prediction_low_risk(predictor):
    feat = AppointmentFeatureInput(
        lead_time_days=1,
        age=55,
        gender=GenderEnum.FEMALE,
        scholarship=0,
        hypertension=1,
        diabetes=0,
        alcoholism=0,
        handicap=0,
        sms_received=1,
        prior_appointments=10,
        prior_no_shows=0,
        day_of_week=1,
        appointment_hour=10
    )
    result = predictor.predict_single(feat, appointment_id=101)
    assert result.appointment_id == 101
    assert 0.0 <= result.no_show_probability <= 1.0
    assert result.risk_level in [RiskLevel.LOW, RiskLevel.MEDIUM, RiskLevel.HIGH]
    assert isinstance(result.contributing_factors, list)

def test_single_prediction_high_risk(predictor):
    feat = AppointmentFeatureInput(
        lead_time_days=45,
        age=22,
        gender=GenderEnum.MALE,
        scholarship=1,
        hypertension=0,
        diabetes=0,
        alcoholism=1,
        handicap=0,
        sms_received=0,
        prior_appointments=6,
        prior_no_shows=5,
        day_of_week=5,
        appointment_hour=16
    )
    result = predictor.predict_single(feat, appointment_id=102)
    assert result.appointment_id == 102
    assert result.no_show_probability >= 0.50
    assert result.is_high_risk is True or result.risk_level == RiskLevel.HIGH or result.risk_level == RiskLevel.MEDIUM
    assert any("lead time" in f.lower() or "prior" in f.lower() for f in result.contributing_factors)

def test_batch_prediction(predictor):
    items = [
        (1, AppointmentFeatureInput(lead_time_days=2, age=30, prior_appointments=2, prior_no_shows=0)),
        (2, AppointmentFeatureInput(lead_time_days=30, age=20, prior_appointments=5, prior_no_shows=4, sms_received=0)),
    ]
    results = predictor.predict_batch(items)
    assert len(results) == 2
    assert results[0].appointment_id == 1
    assert results[1].appointment_id == 2
    assert results[1].no_show_probability > results[0].no_show_probability
