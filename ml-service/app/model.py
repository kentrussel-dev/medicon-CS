import json
import logging
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple
import joblib
import numpy as np
import pandas as pd

from .config import HIGH_RISK_THRESHOLD, MEDIUM_RISK_THRESHOLD, METRICS_PATH, MODEL_PATH
from .schemas import AppointmentFeatureInput, PredictionResponse, RiskLevel

logger = logging.getLogger(__name__)

class NoShowPredictor:
    def __init__(self, model_path: Optional[str] = None, metrics_path: Optional[str] = None):
        self.model_path = Path(model_path or MODEL_PATH)
        self.metrics_path = Path(metrics_path or METRICS_PATH)
        self.pipeline = None
        self.metrics: Dict[str, Any] = {}
        self.load_model()

    def load_model(self) -> bool:
        try:
            if self.model_path.exists():
                self.pipeline = joblib.load(self.model_path)
                logger.info(f"Successfully loaded ML model from {self.model_path}")
            else:
                logger.warning(f"Model file not found at {self.model_path}. Will use heuristic fallback until trained.")

            if self.metrics_path.exists():
                with open(self.metrics_path, "r") as f:
                    self.metrics = json.load(f)
            return self.pipeline is not None
        except Exception as e:
            logger.error(f"Error loading model: {e}")
            self.pipeline = None
            return False

    @property
    def is_ready(self) -> bool:
        return self.pipeline is not None

    def _determine_risk_level(self, probability: float) -> Tuple[RiskLevel, bool]:
        if probability >= HIGH_RISK_THRESHOLD:
            return RiskLevel.HIGH, True
        elif probability >= MEDIUM_RISK_THRESHOLD:
            return RiskLevel.MEDIUM, False
        else:
            return RiskLevel.LOW, False

    def _extract_contributing_factors(self, feat: AppointmentFeatureInput, probability: float) -> List[str]:
        factors = []
        if feat.lead_time_days > 14:
            factors.append(f"High booking lead time ({feat.lead_time_days} days)")
        elif feat.lead_time_days > 7:
            factors.append(f"Moderate booking lead time ({feat.lead_time_days} days)")

        if feat.prior_appointments > 0:
            ratio = feat.prior_no_shows / feat.prior_appointments
            if ratio >= 0.5:
                factors.append(f"History of missed appointments ({feat.prior_no_shows}/{feat.prior_appointments} prior no-shows)")
            elif feat.prior_no_shows > 0:
                factors.append(f"Prior missed appointment history ({feat.prior_no_shows} recorded)")
        elif feat.prior_appointments == 0:
            factors.append("New patient with no prior attendance history")

        if feat.sms_received == 0 and feat.lead_time_days >= 3:
            factors.append("No SMS reminder sent/received")

        if feat.day_of_week in (4, 5):  # Friday or Saturday
            factors.append("Weekend-adjacent appointment schedule")

        if feat.scholarship == 1:
            factors.append("Welfare program beneficiary (statistically higher transit barriers)")

        if not factors and probability < 0.35:
            factors.append("Strong attendance profile with low risk indicators")

        return factors

    def _features_to_dataframe(self, features: List[AppointmentFeatureInput]) -> pd.DataFrame:
        records = []
        for feat in features:
            prior_ratio = (
                feat.prior_no_shows / feat.prior_appointments
                if feat.prior_appointments > 0
                else 0.15
            )
            records.append({
                "lead_time_days": feat.lead_time_days,
                "age": feat.age,
                "gender": feat.gender.value,
                "scholarship": feat.scholarship,
                "hypertension": feat.hypertension,
                "diabetes": feat.diabetes,
                "alcoholism": feat.alcoholism,
                "handicap": feat.handicap,
                "sms_received": feat.sms_received,
                "prior_appointments": feat.prior_appointments,
                "prior_no_shows": feat.prior_no_shows,
                "prior_no_show_ratio": prior_ratio,
                "day_of_week": feat.day_of_week,
                "appointment_hour": feat.appointment_hour,
            })
        return pd.DataFrame(records)

    def _fallback_predict_proba(self, feat: AppointmentFeatureInput) -> float:
        """Heuristic risk score calculation if model pipeline is unavailable."""
        score = 0.18  # baseline
        score += min(0.35, feat.lead_time_days * 0.015)
        if feat.sms_received == 0 and feat.lead_time_days >= 2:
            score += 0.12
        if feat.prior_appointments > 0:
            score += (feat.prior_no_shows / feat.prior_appointments) * 0.40
        if feat.scholarship == 1:
            score += 0.08
        if feat.day_of_week in (4, 5):
            score += 0.05
        return float(np.clip(score, 0.02, 0.98))

    def predict_single(self, feat: AppointmentFeatureInput, appointment_id: Optional[int] = None) -> PredictionResponse:
        if self.pipeline is not None:
            df = self._features_to_dataframe([feat])
            proba = float(self.pipeline.predict_proba(df)[0, 1])
        else:
            proba = self._fallback_predict_proba(feat)

        risk_level, is_high_risk = self._determine_risk_level(proba)
        factors = self._extract_contributing_factors(feat, proba)
        version = self.metrics.get("model_version", "v1.0.0-fallback")

        return PredictionResponse(
            appointment_id=appointment_id,
            no_show_probability=round(proba, 4),
            risk_level=risk_level,
            is_high_risk=is_high_risk,
            contributing_factors=factors,
            model_version=version,
        )

    def predict_batch(self, items: List[Tuple[Optional[int], AppointmentFeatureInput]]) -> List[PredictionResponse]:
        if not items:
            return []

        features_list = [feat for _, feat in items]
        if self.pipeline is not None:
            df = self._features_to_dataframe(features_list)
            probas = self.pipeline.predict_proba(df)[:, 1]
        else:
            probas = [self._fallback_predict_proba(feat) for feat in features_list]

        version = self.metrics.get("model_version", "v1.0.0-fallback")
        results = []
        for i, (appt_id, feat) in enumerate(items):
            proba = float(probas[i])
            risk_level, is_high_risk = self._determine_risk_level(proba)
            factors = self._extract_contributing_factors(feat, proba)
            results.append(
                PredictionResponse(
                    appointment_id=appt_id,
                    no_show_probability=round(proba, 4),
                    risk_level=risk_level,
                    is_high_risk=is_high_risk,
                    contributing_factors=factors,
                    model_version=version,
                )
            )
        return results

# Singleton instance
predictor = NoShowPredictor()
