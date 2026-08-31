# Medicon System Architecture & Technical Specification

Medicon is an enterprise-grade telehealth, clinical encounter, and patient management platform engineered for medical providers and healthcare networks.

---

## 1. High-Level System Architecture

```
                                +---------------------------+
                                |    Client Web Browser     |
                                | (Vue 3 + Pinia + Tailwind)|
                                +-------------+-------------+
                                              |
                                              | HTTPS / REST / JSON
                                              v
                            +-----------------+-----------------+
                            |       Nginx Gateway / Proxy       |
                            +--------+------------------+-------+
                                     |                  |
                       /api /sanctum |                  | /ml
                                     v                  v
                       +-------------+----+   +---------+-----------+
                       |  Laravel 11 API  |   | Python FastAPI ML   |
                       |  (PHP 8.2 FPM)   |   | Scikit-Learn Model  |
                       +-------+----------+   +---------------------+
                               |
            +------------------+------------------+
            |                  |                  |
            v                  v                  v
     +------+------+    +------+------+    +------+------+
     |  MySQL 8.0  |    |  Redis 7.2  |    |  MinIO / S3 |
     | Primary DB  |    | Cache/Queue |    | Encrypted   |
     +-------------+    +-------------+    | Object Store|
                                           +-------------+
```

---

## 2. Core Architectural Pillars

### 2.1 Role-Based Access Control (RBAC)
Medicon enforces strict least-privilege authorization using Laravel Policies and Gates mapped to three distinct roles:
- **Patient**: Can manage personal demographic records, book/reschedule appointments with active specialists, view their encrypted medical history and prescriptions, and upload encrypted lab documents.
- **Doctor**: Can configure weekly operating hours and slot lengths, conduct telemedicine visits, view patient medical records, create clinical encounter notes with encrypted diagnoses and vital signs, and formulate electronic multi-item prescriptions.
- **Admin**: Can provision staff accounts, manage global appointments, inspect doctor utilization analytics, review high-risk no-show triage flagged by the ML pipeline, and audit forensic HIPAA access logs.

### 2.2 Data Protection & HIPAA Compliance
1. **Encrypted Casts at Rest**:
   - Sensitive clinical fields (`allergies`, `medical_notes`, `diagnosis`, `clinical_notes`, `treatment_plan`, and `vital_signs`) are encrypted transparently in the database via AES-256-CBC encryption using Laravel Eloquent encrypted casts.
   - Raw database dumps and backups never expose plaintext patient diagnostic data.
2. **Immutable Forensic Audit Logging**:
   - The `audit_logs` table records every `VIEW`, `CREATE`, `UPDATE`, `DELETE`, and `DOWNLOAD` action.
   - The `AuditLog` Eloquent model implements model-layer mutation guards throwing `RuntimeException` on any `update` or `delete` attempt.
   - `UPDATED_AT` timestamp tracking is permanently disabled (`const UPDATED_AT = null`).
3. **Soft Deletes**:
   - Patient records and medical history utilize `SoftDeletes` to prevent irreversible loss of clinical records.
4. **Expiring Signed URLs for Object Storage**:
   - Lab reports and attachments stored on S3 / MinIO are never public. Access requires time-bounded signed URLs (`getTemporarySignedUrl`) verified by authorization policies.

### 2.3 Machine Learning Microservice & Resilient Heuristic Fallback
- **Microservice**: Built with FastAPI and Python 3.11, running a trained `GradientBoostingClassifier` evaluated on precision, recall, F1, and ROC-AUC.
- **Endpoint**: `/predict` receives patient age, lead time, historical no-show counts, SMS reminder status, and chronic condition flags, outputting a risk score (`0.0` - `1.0`), categorical level (`LOW`, `MEDIUM`, `HIGH`), and key contributing risk factors.
- **Fault-Tolerant Heuristic Fallback**: The backend `NoShowPredictionService` enforces a strict 2.5-second HTTP timeout. If the ML container is unreachable or restarting, it executes a deterministic fallback formula:
  $$\text{Score} = \text{clamp}\left(0.10 + (\text{LeadTime} \times 0.015) + (\text{NoShowRatio} \times 0.40) - (\text{SMS} \times 0.08) + (\text{Scholarship} \times 0.05) + \text{DayOffset}, 0.05, 0.95\right)$$
  This guarantees that patient appointment bookings never fail or hang.

---

## 3. Background Queue Architecture

Queued background jobs handle asynchronous workflows via Redis:
- **`ComputeAppointmentRiskJob`**: Evaluates new appointments against the ML microservice in the background.
- **`NotifyHighRiskAppointmentJob`**: Dispatches proactive triage notifications to clinic staff when risk score exceeds 65%.
- **`SendAppointmentReminderJob`**: Formulates automated reminders dispatched 24h/48h prior to scheduled encounters.

---

## 4. Database Schema Entity Relationship

| Entity | Primary Key | Key Foreign Keys & Security Attributes |
|---|---|---|
| `users` | `id` | `role` (`patient`, `doctor`, `admin`), `is_active` |
| `patients` | `id` | `user_id` $\to$ `users.id`, `allergies` (encrypted), `medical_notes` (encrypted), soft deleted |
| `doctors` | `id` | `user_id` $\to$ `users.id`, `license_number` (unique), `specialty`, `rating` |
| `doctor_availabilities`| `id` | `doctor_id` $\to$ `doctors.id`, `day_of_week` (0-6), `slot_duration_minutes` |
| `appointments` | `id` | `patient_id`, `doctor_id`, `scheduled_start`, `status`, `no_show_risk_score`, `no_show_risk_level` |
| `medical_records` | `id` | `patient_id`, `doctor_id`, `diagnosis` (encrypted), `clinical_notes` (encrypted), `vital_signs` (encrypted array), soft deleted |
| `prescriptions` | `id` | `patient_id`, `doctor_id`, `valid_until`, `is_dispensed` |
| `prescription_items` | `id` | `prescription_id` $\to$ `prescriptions.id`, `medication_name`, `dosage`, `frequency` |
| `attachments` | `id` | `attachable_type`, `attachable_id`, `s3_key`, `mime_type` |
| `audit_logs` | `id` | `user_id`, `patient_id`, `action`, `record_type`, `record_id`, `ip_address`, immutable |
