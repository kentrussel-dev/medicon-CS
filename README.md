# Medicon &mdash; Production Telehealth & Clinical Patient Management Platform

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PHP: 8.2](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![Laravel: 11](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Vue: 3.5](https://img.shields.io/badge/Vue-3.5-brightgreen.svg)](https://vuejs.org)
[![FastAPI: 0.115](https://img.shields.io/badge/FastAPI-0.115-teal.svg)](https://fastapi.tiangolo.com)
[![Tailwind CSS: 3.4](https://img.shields.io/badge/Tailwind-3.4-38bdf8.svg)](https://tailwindcss.com)
[![WebRTC: Realtime HD](https://img.shields.io/badge/WebRTC-1080p--HD-indigo.svg)](docs/ARCHITECTURE.md)
[![HIPAA Compliance](https://img.shields.io/badge/HIPAA-AES--256--Encrypted-blueviolet.svg)](docs/ARCHITECTURE.md)

**Medicon** is a modern, production-grade clinical encounter and telehealth platform designed for medical centers, hospitals, and outpatient clinics. It unifies electronic health records (EHR), multi-party WebRTC video consultations, automated electronic prescribing, machine learning appointment attendance triage, and HIPAA audit forensics into a crisp, accessible clinical interface.

---

## 🌟 Key Features

### 1. 🎥 Google Meet-Style Telehealth Suite
- **Pre-Join Green Room / Waiting Lobby**:
  - Live 16:9 video mirror with self-check preview before entering.
  - Audio and video device toggles (Microphone starts muted & Camera starts closed by default for patient privacy).
  - Real-time in-room attendance preview showing which physicians or specialists are already in the call.
  - Clinical case summary and 1-click **Join Now** transition.
- **Widescreen 16:9 Multi-Party Video Grid**:
  - Strict 16:9 (`aspect-video`) card layout adapting seamlessly across 1, 2, 3 (2 top + 1 centered bottom), and 4+ participant conferences without vertical scrolling.
  - High-definition WebRTC video pipeline with dynamic `:ref` binding and simulated 30 FPS clinical sandbox stream fallback.
- **Screen Sharing / Spotlight Presentation Mode**:
  - Native browser `getDisplayMedia()` screen capture for presenting diagnostic scans, laboratory results, or medical records.
  - Automatic transition to an 80% spotlight stage alongside a vertical participant video strip.
- **Encrypted In-Call Consultation Chat**:
  - Integrated drawer for exchanging real-time clinical notes, dosage instructions, and links with unread counter badges.
- **Unique Random Room Codes (`k9x-yqp2-481`)**:
  - Google Meet-style 3-part alphanumeric identifiers generated per appointment and instant session.
  - 1-click URL copying and Instant Meeting creation from doctor and patient dashboards.
- **Ephemeral Data Purge on Session End**:
  - Closing a consultation automatically wipes in-room chat messages and media tokens from the database to enforce strict HIPAA compliance.

### 2. 🏥 Comprehensive Clinical Encounter & SOAP Workflow
- **Medical Documentation**: Structured Subjective, Objective, Assessment, and Plan (SOAP) clinical encounter builder.
- **ICD-10 Diagnostic Coding**: Direct standardized diagnostic categorization with severity classification.
- **Vital Signs Matrix**: Live recording for Blood Pressure, Heart Rate, Respiratory Rate, Oxygen Saturation (SpO2), Body Temperature, and Weight.
- **Electronic Prescriptions (e-Rx)**: Multi-drug formulation engine with automated dosage, frequency, route, and refill instructions.

### 3. 🤖 Machine Learning No-Show Risk Engine
- **FastAPI Microservice**: Powered by a trained Scikit-Learn `GradientBoostingClassifier`.
- **Predictive Scoring**: Computes real-time no-show probability upon appointment booking based on lead time, historical attendance, demographics, time-of-day, and engagement flags.
- **Clinical Intervention Triage**: Automatically flags high-risk appointments ($\ge 65\%$) for administrative staff intervention (SMS confirmations, telemedicine re-routing, telephone reminders).
- **Deterministic Heuristic Fallback**: Ensures uninterrupted booking resilience even during microservice maintenance.

### 4. 🔒 Role-Based Access Control (RBAC) & Security
- **Dr. Eleanor Vance, MD (Chief Medical Officer / Admin)**: Executive operations dashboard, system-wide physician utilization analytics, ML risk triage center, user directory management, and immutable HIPAA forensic audit logs.
- **Attending Physicians & Specialists**: Cardiology, Neurology, Dermatology, Primary Care, Psychiatry, and Orthopedics portals with custom availability slots (15/30/45/60 min), patient records, and instant room launcher.
- **Patients**: Self-service appointment scheduling, instant telehealth room access, encrypted diagnostic records, and prescription management.
- **Data Protection**: `AES-256-CBC` database field encryption for clinical notes, immutable audit logging with mutation blocking, and soft deletes.

---

## 🏛️ System Architecture

```
                                  ┌───────────────────────────────┐
                                  │      Client Web Browser       │
                                  │   Vue 3 SPA + Tailwind CSS    │
                                  └──────────────┬────────────────┘
                                                 │
                                ┌────────────────┴────────────────┐
                                │                                 │
                     REST / Sanctum Auth                     WebRTC Media
                                │                                 │
                                ▼                                 ▼
                     ┌──────────────────────┐        ┌─────────────────────────┐
                     │   Laravel 11 API     │        │ WebRTC Live Gateway     │
                     │  (PHP 8.2 / FPM)     │        │ 1080p Screen & Audio    │
                     └──────────┬───────────┘        └─────────────────────────┘
                                │
          ┌─────────────────────┼─────────────────────┐
          │                     │                     │
          ▼                     ▼                     ▼
┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐
│   MySQL 8.0 DB   │  │   Redis 7.2      │  │  FastAPI ML Svc  │
│  Encrypted Casts │  │  Cache & Queues  │  │ Scikit-Learn GBC │
└──────────────────┘  └──────────────────┘  └──────────────────┘
```

---

## 👥 Seeded Clinical Accounts & Credentials

The application is pre-seeded with clean, realistic clinical datasets across all roles:

| Role | Name / Title | Specialty / Focus | Email | Password |
|---|---|---|---|---|
| **Admin** | Dr. Eleanor Vance, MD | Chief Medical Officer | `admin@medicon.health` | `Secret123!` |
| **Doctor** | Dr. Sarah Jenkins, MD, FACC | Cardiology | `sarah.jenkins@medicon.health` | `Secret123!` |
| **Doctor** | Dr. Marcus Chen, MD, PhD | Neurology | `marcus.chen@medicon.health` | `Secret123!` |
| **Doctor** | Dr. Elena Rostova, MD | Dermatology | `elena.rostova@medicon.health` | `Secret123!` |
| **Doctor** | Dr. James Wilson, MD | General Practice | `james.wilson@medicon.health` | `Secret123!` |
| **Doctor** | Dr. Aisha Patel, MD | Psychiatry | `aisha.patel@medicon.health` | `Secret123!` |
| **Doctor** | Dr. Robert Taylor, MD | Orthopedics | `robert.taylor@medicon.health` | `Secret123!` |
| **Patient** | Jane Doe | Hypertension / Wellness | `patient@medicon.health` | `Secret123!` |

---

## 🚀 Quick Start Guide

### Prerequisites
- **Node.js**: v18.0 or higher (`npm` v9+)
- **PHP**: v8.2+ with `pdo_mysql`, `mbstring`, `openssl`, `bcmath`
- **Python**: v3.10+ (for ML microservice)
- **Composer**: v2+

### 1. Clone the Repository
```bash
git clone https://github.com/kentrussel-dev/medicon-CS.git
cd medicon-CS
```

### 2. Configure Environment Files
Copy the template files (sensitive variables are ignored from git):
```bash
# Backend Environment
cp backend/.env.example backend/.env

# Frontend Environment
cp frontend/.env.example frontend/.env

# ML Service Environment
cp ml-service/.env.example ml-service/.env
```

### 3. Setup Backend (Laravel)
```bash
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8080
```

### 4. Setup Machine Learning Microservice (FastAPI)
```bash
cd ml-service
python -m venv venv
# On Windows:
.\venv\Scripts\activate
# On Linux/macOS:
# source venv/bin/activate
pip install -r requirements.txt
python train.py
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

### 5. Setup Frontend Application (Vue 3 + Vite)
```bash
cd frontend
npm install
npm run dev
```

The portal will be live at: **`http://localhost:5173`**

---

## 🐳 Docker Deployment

To launch the complete containerized multi-service stack with a single command:

```bash
docker compose up -d --build
```

- **Frontend SPA**: `http://localhost:5173`
- **REST API Gateway**: `http://localhost:8080/api`
- **FastAPI ML Service & Swagger Docs**: `http://localhost:8000/docs`
- **MinIO Storage Console**: `http://localhost:9001`

---

## 🧪 Testing & Quality Assurance

### Run Backend Feature & Unit Tests (PHPUnit)
```bash
cd backend
php artisan test
```

### Run Machine Learning Model Tests (Pytest)
```bash
cd ml-service
pytest tests/ -v
```

### Build & Validate Frontend SPA (Vite)
```bash
cd frontend
npm run build
```

---

## 📂 Project Structure

```
medicon/
├── backend/                  # Laravel 11 REST API
│   ├── app/
│   │   ├── Enums/            # PHP 8.2 Enumerations (Roles, Statuses)
│   │   ├── Http/Controllers/ # Controllers (Telehealth, Appointments, Records)
│   │   ├── Models/           # Eloquent Models with Encrypted Casts
│   │   ├── Policies/         # HIPAA & RBAC Authorization Policies
│   │   └── Services/         # ML Client, Audit Service, Storage Services
│   ├── database/migrations/  # Database Schema & Telehealth Rooms
│   ├── database/seeders/     # Clinical Demo Datasets
│   └── tests/                # PHPUnit Test Suite
│
├── frontend/                 # Vue 3 SPA (Vite + Tailwind CSS + Pinia)
│   ├── src/
│   │   ├── components/       # UI Modals, Badges, Header & Navigation
│   │   ├── layouts/          # Responsive App & Auth Layouts
│   │   ├── router/           # Navigation Guards & Role Protection
│   │   ├── stores/           # Pinia State Management Stores
│   │   ├── views/            # TelehealthRoomView, Doctor, Patient, & Admin Views
│   │   └── services/         # API Client & Mock Clinical Adapter
│   └── vite.config.js
│
├── ml-service/               # Python 3.11 FastAPI Microservice
│   ├── app/                  # REST Predictors & Pydantic Validation
│   ├── models/               # Serialized GradientBoosting Classifier
│   ├── tests/                # Pytest Test Suite
│   └── train.py              # ML Training & Evaluation Pipeline
│
├── docker/                   # Hardened Nginx & PHP Container Configs
├── docs/                     # Architecture Specifications
└── docker-compose.yml        # Development Stack Orchestration
```

---

## 📄 License

This software is released under the **MIT License**. See [`LICENSE`](LICENSE) for complete details.
