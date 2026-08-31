import json
import os
from pathlib import Path
import joblib
import numpy as np
import pandas as pd
from sklearn.compose import ColumnTransformer
from sklearn.ensemble import GradientBoostingClassifier, RandomForestClassifier
from sklearn.metrics import (
    accuracy_score,
    classification_report,
    confusion_matrix,
    f1_score,
    precision_score,
    recall_score,
    roc_auc_score,
)
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.preprocessing import OneHotEncoder, StandardScaler

def generate_synthetic_kaggle_dataset(n_samples: int = 15000, random_state: int = 42) -> pd.DataFrame:
    np.random.seed(random_state)
    
    lead_time_days = np.clip(np.random.exponential(scale=12, size=n_samples).astype(int), 0, 120)
    age = np.clip(np.random.normal(loc=38, scale=22, size=n_samples).astype(int), 0, 100)
    gender = np.random.choice(["M", "F"], size=n_samples, p=[0.35, 0.65])
    scholarship = np.random.choice([0, 1], size=n_samples, p=[0.90, 0.10])
    
    hypertension = (age > 45) & (np.random.rand(n_samples) < 0.45)
    hypertension = hypertension.astype(int)
    
    diabetes = (age > 40) & (np.random.rand(n_samples) < 0.20)
    diabetes = diabetes.astype(int)
    
    alcoholism = (age > 18) & (np.random.rand(n_samples) < 0.05)
    alcoholism = alcoholism.astype(int)
    
    handicap = np.random.choice([0, 1, 2], size=n_samples, p=[0.96, 0.03, 0.01])
    
    sms_received = (lead_time_days >= 3) & (np.random.rand(n_samples) < 0.65)
    sms_received = sms_received.astype(int)
    
    prior_appointments = np.random.poisson(lam=3.5, size=n_samples)
    prior_no_shows = np.array([
        np.random.binomial(n=prior_appointments[i], p=0.22) if prior_appointments[i] > 0 else 0
        for i in range(n_samples)
    ])
    
    day_of_week = np.random.choice([0, 1, 2, 3, 4, 5], size=n_samples, p=[0.20, 0.22, 0.21, 0.19, 0.15, 0.03])
    appointment_hour = np.random.choice(range(8, 18), size=n_samples)
    
    prior_no_show_ratio = np.divide(
        prior_no_shows.astype(float),
        prior_appointments.astype(float),
        out=np.full(n_samples, 0.15),
        where=(prior_appointments > 0)
    )
    
    # Calculate baseline log-odds of no-show
    z = (
        -1.8
        + 0.045 * lead_time_days
        - 0.015 * (age - 35)
        + 0.35 * scholarship
        - 0.45 * sms_received
        + 1.80 * prior_no_show_ratio
        + 0.20 * (day_of_week >= 4)
        + 0.25 * alcoholism
        - 0.15 * hypertension
    )
    
    # Sigmoid probability
    probabilities = 1.0 / (1.0 + np.exp(-z))
    no_show = (np.random.rand(n_samples) < probabilities).astype(int)
    
    data = pd.DataFrame({
        "lead_time_days": lead_time_days,
        "age": age,
        "gender": gender,
        "scholarship": scholarship,
        "hypertension": hypertension,
        "diabetes": diabetes,
        "alcoholism": alcoholism,
        "handicap": handicap,
        "sms_received": sms_received,
        "prior_appointments": prior_appointments,
        "prior_no_shows": prior_no_shows,
        "prior_no_show_ratio": prior_no_show_ratio,
        "day_of_week": day_of_week,
        "appointment_hour": appointment_hour,
        "no_show": no_show,
    })
    
    return data

def build_and_train_model():
    print("Generating training dataset based on Kaggle Medical Appointment No-Shows...")
    df = generate_synthetic_kaggle_dataset(n_samples=20000, random_state=42)
    
    X = df.drop(columns=["no_show"])
    y = df["no_show"]
    
    numeric_features = [
        "lead_time_days",
        "age",
        "prior_appointments",
        "prior_no_shows",
        "prior_no_show_ratio",
        "appointment_hour",
    ]
    
    categorical_features = [
        "gender",
        "scholarship",
        "hypertension",
        "diabetes",
        "alcoholism",
        "handicap",
        "sms_received",
        "day_of_week",
    ]
    
    preprocessor = ColumnTransformer(
        transformers=[
            ("num", StandardScaler(), numeric_features),
            ("cat", OneHotEncoder(handle_unknown="ignore", sparse_output=False), categorical_features),
        ]
    )
    
    classifier = GradientBoostingClassifier(
        n_estimators=150,
        learning_rate=0.08,
        max_depth=4,
        subsample=0.85,
        random_state=42,
    )
    
    pipeline = Pipeline(
        steps=[
            ("preprocessor", preprocessor),
            ("classifier", classifier),
        ]
    )
    
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.20, random_state=42, stratify=y
    )
    
    print("Fitting model pipeline...")
    pipeline.fit(X_train, y_train)
    
    y_pred = pipeline.predict(X_test)
    y_proba = pipeline.predict_proba(X_test)[:, 1]
    
    acc = accuracy_score(y_test, y_pred)
    prec = precision_score(y_test, y_pred, zero_division=0)
    rec = recall_score(y_test, y_pred, zero_division=0)
    f1 = f1_score(y_test, y_pred, zero_division=0)
    roc_auc = roc_auc_score(y_test, y_proba)
    cm = confusion_matrix(y_test, y_pred).tolist()
    
    print("\n--- Model Evaluation ---")
    print(f"Accuracy:  {acc:.4f}")
    print(f"Precision: {prec:.4f}")
    print(f"Recall:    {rec:.4f}")
    print(f"F1-Score:  {f1:.4f}")
    print(f"ROC-AUC:   {roc_auc:.4f}")
    print("Confusion Matrix:")
    print(cm)
    
    models_dir = Path(__file__).resolve().parent / "models"
    models_dir.mkdir(parents=True, exist_ok=True)
    
    model_path = models_dir / "noshow_model_v1.joblib"
    joblib.dump(pipeline, model_path)
    print(f"\nSaved model artifact to: {model_path}")
    
    metrics = {
        "model_version": "v1.0.0",
        "algorithm": "GradientBoostingClassifier",
        "dataset_samples": len(df),
        "test_samples": len(X_test),
        "metrics": {
            "accuracy": round(float(acc), 4),
            "precision": round(float(prec), 4),
            "recall": round(float(rec), 4),
            "f1_score": round(float(f1), 4),
            "roc_auc": round(float(roc_auc), 4),
        },
        "confusion_matrix": cm,
        "features": {
            "numeric": numeric_features,
            "categorical": categorical_features,
        },
    }
    
    metrics_path = models_dir / "metrics.json"
    with open(metrics_path, "w") as f:
        json.dump(metrics, f, indent=2)
    print(f"Saved metrics to: {metrics_path}")

if __name__ == "__main__":
    build_and_train_model()
