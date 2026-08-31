# Medicon &mdash; Telehealth & Clinical Patient Management Platform

[![CI Pipeline](https://github.com/medicon/medicon/actions/workflows/ci.yml/badge.svg)](https://github.com/medicon/medicon/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PHP: 8.2](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![Vue: 3.5](https://img.shields.io/badge/Vue-3.5-brightgreen.svg)](https://vuejs.org)
[![FastAPI: 0.115](https://img.shields.io/badge/FastAPI-0.115-teal.svg)](https://fastapi.tiangolo.com)
[![HIPAA Compliant](https://img.shields.io/badge/HIPAA-AES--256-blueviolet.svg)](docs/ARCHITECTURE.md)

**Medicon** is a production-grade telehealth and clinical encounter management platform engineered for medical providers, multi-specialty clinics, and healthcare systems.

Built with a modern multi-tier microservices architecture:
- **Frontend**: Vue.js 3, Pinia 2, Vue Router 4, TailwindCSS, and Lucide Icons.
- **Backend API**: Laravel 11, Laravel Sanctum, PHP 8.2 FPM, Eloquent Encrypted Casts, Form Requests, and Queued Background Jobs.
- **Machine Learning Microservice**: Python 3.11, FastAPI, and Scikit-Learn `GradientBoostingClassifier` predicting appointment no-show probabilities with dynamic risk factors.
- **Data & Storage**: MySQL 8.0 (Primary DB), Redis 7.2 (Cache/Queues), and MinIO/S3 (Encrypted Medical Object Storage).
- **Deployment**: Docker & Docker Compose with multi-stage production builds and hardened Nginx reverse proxy.

---

## 🌟 Core Features & Workflows

### 1. Granular Role-Based Access Control (RBAC)
- **Patient Portal**:
  - User registration and authenticated session management.
  - Interactive physician directory filtering by medical specialty, experience, and fee.
  - Real-time appointment scheduling with conflict prevention and slot duration management.
  - In-browser telemedicine video call integration.
  - Encrypted personal clinical encounters, diagnostic notes, and lab attachment downloads.
  - Active and historical electronic prescription management.
- **Doctor Portal**:
  - Weekly schedule and slot length customizer (15, 30, 45, 60 minutes).
  - Patient encounter management: Start visit, update clinical status, and track attendance.
  - Clinical documentation tool: Primary diagnosis, ICD-10 coding, vital signs matrix (BP, HR, SpO2, Temp, Weight), and encrypted examination notes.
  - Multi-item electronic prescription formulation (dosages, frequencies, durations, and instructions).
  - Patient clinical history and timeline explorer.
- **Administrator Portal**:
  - Executive operations dashboard: Active patients, on-duty physicians, no-show rates, and revenue.
  - Machine Learning Attendance Triage: High-risk patient absence monitoring ($\ge 65\%$) with key contributing factors and intervention protocols.
  - Physician utilization and productivity metrics.
  - Staff and user management with one-click activation/deactivation.
  - Immutable HIPAA forensic audit trail inspecting data access and modification diffs.

---

## 🔒 Security, Compliance & Data Protection
1. **Encrypted Casts at Rest**: Sensitive diagnostic fields (`allergies`, `medical_notes`, `diagnosis`, `clinical_notes`, `treatment_plan`, `vital_signs`) are encrypted using `AES-256-CBC` in the database.
2. **Immutable Audit Trail**: The `audit_logs` table records every `VIEW`, `CREATE`, `UPDATE`, `DELETE`, and `DOWNLOAD` event. The Eloquent model enforces mutation blocks preventing tampering or deletion.
3. **Soft Deletes**: Patient records and clinical histories use soft deletes to prevent permanent data loss.
4. **Time-Bounded Signed URLs**: Lab documents and medical attachments stored in S3/MinIO are strictly accessible through short-lived signed URLs.
5. **Resilient ML Fallback**: The backend connects to the Python ML microservice with a 2.5s timeout. If unavailable, it executes a deterministic heuristic fallback to ensure uninterrupted appointment booking.

---

## 🚀 Quick Start with Docker

### Prerequisites
- [Docker Engine 24+](https://docs.docker.com/engine/install/)
- [Docker Compose v2+](https://docs.docker.com/compose/)

### 1. Clone & Configure Environment
```bash
git clone https://github.com/medicon/medicon.git
cd medicon
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### 2. Launch Multi-Container Stack
```bash
docker compose up -d --build
```

### 3. Initialize Database & Seed Demo Data
```bash
docker compose exec backend php artisan migrate --seed
```

### 4. Access Services
- **Web Application (SPA)**: `http://localhost:5173`
- **REST API Gateway**: `http://localhost:8080/api`
- **FastAPI ML Microservice**: `http://localhost:8000` (Docs at `/docs`)
- **MinIO Object Storage Console**: `http://localhost:9001` (User: `minioadmin` / Pass: `minioadmin`)

---

## 👥 Instant Demo Login Credentials

The login screen provides one-click demo role auto-fill buttons:

| Role | Email | Password | Primary Capabilities |
|---|---|---|---|
| **Patient** | `patient@medicon.health` | `Secret123!` | Book visits, view encrypted records & prescriptions |
| **Doctor** | `sarah.jenkins@medicon.health` | `Secret123!` | Manage availability, document encounters, issue Rx |
| **Admin** | `admin@medicon.health` | `Secret123!` | ML risk triage, utilization analytics, audit logs |

---

## 🧪 Testing Suite

### 1. Run Backend Tests (PHPUnit)
```bash
cd backend
php artisan test
```
Covers:
- `AppointmentBookingConflictTest`: Slot collision prevention and operating hours validation.
- `RoleBasedAccessControlTest`: Policy enforcement across patient, doctor, and admin endpoints.
- `EncryptedMedicalRecordTest`: Ciphertext persistence in raw DB and transparent decryption.
- `ImmutableAuditLogTest`: Append-only compliance and mutation blocking.
- `NoShowPredictionServiceTest`: Microservice client communication and heuristic scoring.

### 2. Run Machine Learning Tests (Pytest)
```bash
cd ml-service
python -m pytest tests/ -v
```
Verifies model inference, high-risk triage thresholds, and FastAPI endpoints (`/predict`, `/batch-predict`, `/health`, `/model-info`).

### 3. Build Frontend SPA (Vite)
```bash
cd frontend
npm run build
```

---

## 📂 Project Architecture

```
medicon/
├── backend/                  # Laravel 11 REST API
│   ├── app/
│   │   ├── Enums/            # PHP 8.2 Enums (UserRole, RiskLevel, etc.)
│   │   ├── Http/Controllers/ # REST API Resource Controllers
│   │   ├── Http/Requests/    # Form Request Validations
│   │   ├── Http/Resources/   # JSON Output Mappings
│   │   ├── Jobs/             # Queued Asynchronous Jobs
│   │   ├── Models/           # Eloquent Models with Encrypted Casts
│   │   ├── Policies/         # RBAC Authorization Policies
│   │   └── Services/         # Domain Services (Audit, ML Client, S3 Storage)
│   ├── database/migrations/  # 13 Database Migrations
│   ├── database/seeders/     # Clinical & Demographic Seeders
│   └── tests/                # PHPUnit Feature and Unit Tests
│
├── frontend/                 # Vue 3 Single Page Application
│   ├── src/
│   │   ├── components/       # Common, Patient, Doctor, & Admin Components
│   │   ├── layouts/          # AppLayout & AuthLayout
│   │   ├── router/           # Vue Router with RBAC Guards
│   │   ├── stores/           # Pinia Stores (Auth, Appointments, Admin, etc.)
│   │   └── views/            # Responsive Role-Specific Views
│   └── vite.config.js
│
├── ml-service/               # Python 3.11 FastAPI Microservice
│   ├── app/                  # FastAPI Application & Pydantic Schemas
│   ├── models/               # Trained Scikit-Learn Model & Metrics
│   ├── tests/                # Pytest Test Suite
│   └── train.py              # Model Training & Evaluation Pipeline
│
├── docker/                   # Nginx & PHP Configuration Files
├── docs/                     # Architecture & OpenAPI Specifications
├── .github/workflows/        # Automated CI/CD Pipelines
└── docker-compose.yml        # Development Orchestration
```

---

## 📄 License
This project is open-source under the [MIT License](LICENSE).
