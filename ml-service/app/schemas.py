from enum import Enum
from typing import Dict, List, Optional
from pydantic import BaseModel, Field, field_validator

class RiskLevel(str, Enum):
    LOW = "LOW"
    MEDIUM = "MEDIUM"
    HIGH = "HIGH"

class GenderEnum(str, Enum):
    MALE = "M"
    FEMALE = "F"

class AppointmentFeatureInput(BaseModel):
    lead_time_days: int = Field(
        ...,
        ge=0,
        le=365,
        description="Number of days between appointment booking and scheduled time",
        json_schema_extra={"example": 7}
    )
    age: int = Field(
        ...,
        ge=0,
        le=120,
        description="Age of patient in years",
        json_schema_extra={"example": 45}
    )
    gender: GenderEnum = Field(
        default=GenderEnum.FEMALE,
        description="Biological gender ('M' or 'F')",
        json_schema_extra={"example": "F"}
    )
    scholarship: int = Field(
        default=0,
        ge=0,
        le=1,
        description="Enrolled in social welfare assistance (0 = No, 1 = Yes)",
        json_schema_extra={"example": 0}
    )
    hypertension: int = Field(
        default=0,
        ge=0,
        le=1,
        description="Diagnosed hypertension (0 = No, 1 = Yes)",
        json_schema_extra={"example": 1}
    )
    diabetes: int = Field(
        default=0,
        ge=0,
        le=1,
        description="Diagnosed diabetes (0 = No, 1 = Yes)",
        json_schema_extra={"example": 0}
    )
    alcoholism: int = Field(
        default=0,
        ge=0,
        le=1,
        description="Diagnosed alcoholism (0 = No, 1 = Yes)",
        json_schema_extra={"example": 0}
    )
    handicap: int = Field(
        default=0,
        ge=0,
        le=4,
        description="Number of handicap conditions (0-4)",
        json_schema_extra={"example": 0}
    )
    sms_received: int = Field(
        default=1,
        ge=0,
        le=1,
        description="Whether an SMS reminder was sent (0 = No, 1 = Yes)",
        json_schema_extra={"example": 1}
    )
    prior_appointments: int = Field(
        default=0,
        ge=0,
        le=500,
        description="Total prior appointments booked by the patient",
        json_schema_extra={"example": 4}
    )
    prior_no_shows: int = Field(
        default=0,
        ge=0,
        le=500,
        description="Total prior appointments the patient failed to attend",
        json_schema_extra={"example": 1}
    )
    day_of_week: int = Field(
        default=1,
        ge=0,
        le=6,
        description="Day of the week (0 = Monday, 6 = Sunday)",
        json_schema_extra={"example": 2}
    )
    appointment_hour: int = Field(
        default=10,
        ge=6,
        le=22,
        description="Hour of day (24-hour format, 6-22)",
        json_schema_extra={"example": 10}
    )

    @field_validator("prior_no_shows")
    @classmethod
    def validate_no_shows_not_exceed_appointments(cls, v: int, info) -> int:
        prior_appts = info.data.get("prior_appointments", 0)
        if v > prior_appts:
            # Clamp or validate
            raise ValueError("prior_no_shows cannot exceed prior_appointments")
        return v

class SinglePredictionRequest(BaseModel):
    appointment_id: Optional[int] = Field(None, description="Optional appointment ID reference")
    features: AppointmentFeatureInput

class BatchPredictionRequest(BaseModel):
    items: List[SinglePredictionRequest] = Field(..., min_length=1, max_length=100)

class PredictionResponse(BaseModel):
    appointment_id: Optional[int] = None
    no_show_probability: float = Field(..., ge=0.0, le=1.0, description="Predicted probability of no-show [0.0 - 1.0]")
    risk_level: RiskLevel = Field(..., description="Categorical risk rating: LOW, MEDIUM, HIGH")
    is_high_risk: bool = Field(..., description="True if no_show_probability >= 0.65")
    contributing_factors: List[str] = Field(default_factory=list, description="Top positive risk driver explanations")
    model_version: str

class BatchPredictionResponse(BaseModel):
    total_processed: int
    predictions: List[PredictionResponse]

class ModelInfoResponse(BaseModel):
    name: str
    version: str
    algorithm: str
    metrics: Dict[str, float]
    features: Dict[str, List[str]]

class HealthCheckResponse(BaseModel):
    status: str
    service: str
    version: str
    model_loaded: bool
