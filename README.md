# Medicon - Clinical Encounter & Telehealth Management Platform

[![License: MIT](https://img.shields.io/badge/License-MIT-emerald.svg)](LICENSE)
[![PHP: 8.2](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![Laravel: 11](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Vue: 3.5](https://img.shields.io/badge/Vue-3.5-brightgreen.svg)](https://vuejs.org)
[![FastAPI: 0.115](https://img.shields.io/badge/FastAPI-0.115-teal.svg)](https://fastapi.tiangolo.com)
[![Tailwind CSS: 3.4](https://img.shields.io/badge/Tailwind-3.4-38bdf8.svg)](https://tailwindcss.com)
[![HIPAA Compliance](https://img.shields.io/badge/HIPAA-AES--256--Encrypted-blueviolet.svg)](docs/ARCHITECTURE.md)

---

## What is Medicon?

Medicon is a full-stack clinical encounter and telehealth platform built for healthcare providers, outpatient clinics, and hospital networks. It bridges modern telemedicine consultations with institutional electronic medical record (EMR) workflows.

### Purpose & Problem Solved
Traditional clinic management systems frequently suffer from fragmented tooling: video calls occur in disconnected third-party apps, diagnostic notes are documented in outdated desktop software, and missed appointments lead to lost physician utilization and delayed patient care.

Medicon consolidates these workflows into a single HIPAA-conscious platform:
- **Integrated Telemedicine**: Conduct multi-party video consultations directly inside the portal without external software installations.
- **Structured Clinical Documentation**: Create standardized SOAP notes (Subjective, Objective, Assessment, Plan), record vital signs, and assign ICD-10 diagnostic codes during or after visits.
- **Electronic Prescriptions (e-Rx)**: Formulate multi-drug prescriptions with exact dosage, route, frequency, and refill instructions.
- **Predictive Attendance Triage**: Utilize a machine learning microservice to score patient no-show risks at the moment of booking, allowing clinical staff to perform targeted outreach before appointments occur.
- **Privacy & Security First**: Sensitive medical fields are encrypted with AES-256 at rest, all record access is immutably logged in HIPAA audit trails, and in-room consultation data is purged upon session termination.

---

## Core Capabilities by Role

### 1. Patient Portal
- Browse credentialed physicians filtered by medical specialty, experience, and fees.
- Schedule, reschedule, or cancel telehealth and in-person consultations.
- Enter the Google Meet-style Green Room waiting lobby to preview and verify camera/microphone settings before joining.
- Access encrypted personal medical history, diagnosis records, and electronic prescriptions.
- Join ad-hoc instant rooms using 3-part random consultation codes.

### 2. Attending Physician Portal
- Configure weekly availability schedules and flexible consultation durations (15, 30, 45, or 60 minutes).
- Manage patient appointments with attendance status tracking (Confirmed, In-Progress, Completed, Cancelled, No-Show).
- Host multi-party WebRTC consultations with strict 16:9 widescreen video grids, screen sharing (spotlight mode), and in-call encrypted chat.
- Document clinical encounters with structured SOAP notes, ICD-10 codes, and vitals matrices (BP, HR, SpO2, Temp, Weight).
- Formulate electronic prescriptions and review patient medical histories.

### 3. Chief Medical Officer / Administrator Portal
- High-level executive dashboard tracking total patients, active doctors, appointment volume, and clinic revenue.
- Machine Learning Attendance Triage Center displaying flagged high-risk appointments (>= 65% risk score) with contributing risk factors.
- Clinic-wide physician productivity and room utilization analytics.
- User and staff directory management with activation toggles.
- Immutable HIPAA forensic audit trail detailing all record views, edits, and downloads.

---

## Tech Stack Overview

- **Frontend**: Vue 3 (Composition API, `<script setup>`), Vite 6, Tailwind CSS 3.4, Pinia 2 (State Management), Vue Router 4, Lucide Icons.
- **Backend REST API**: Laravel 11 (PHP 8.2), Laravel Sanctum Authentication, Eloquent ORM with Encrypted Casts, Form Requests, Policies, and Seeders.
- **Machine Learning Service**: Python 3.11, FastAPI, Scikit-Learn (Gradient Boosting Classifier), Pydantic v2, Uvicorn.
- **Database & Cache**: MySQL 8.0 (Primary Storage), Redis 7.2 (Sessions, Cache, Queues).
- **Object Storage**: S3-compatible storage (MinIO) for encrypted medical attachments.
- **Deployment**: Docker, Docker Compose, Multi-stage production builds.

---

## Step-by-Step Setup Guide

You can run Medicon using either Docker (recommended for a full-stack experience) or Local Development (step-by-step per service).

---

### Method A: Quick Start with Docker (Recommended)

#### Prerequisites
- Docker Engine 24.0+ ([Install Docker](https://docs.docker.com/engine/install/))
- Docker Compose v2.0+

#### Step 1: Clone the Repository
```bash
git clone https://github.com/kentrussel-dev/medicon-CS.git
cd medicon-CS
```

#### Step 2: Configure Environment Files
Copy the template configuration files:
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp ml-service/.env.example ml-service/.env
```

#### Step 3: Start the Multi-Container Stack
```bash
docker compose up -d --build
```

#### Step 4: Run Migrations and Seed Clinical Data
```bash
docker compose exec backend php artisan migrate --seed
```

#### Step 5: Access the Services
- Frontend Web Portal: `http://localhost:5173`
- REST API Gateway: `http://localhost:8080/api`
- FastAPI ML Service Documentation: `http://localhost:8000/docs`
- MinIO Object Storage Console: `http://localhost:9001` (User: `minioadmin`, Pass: `minioadmin`)

---

### Method B: Local Development Setup (Manual)

#### Prerequisites
- **Node.js**: v18.0 or higher (`npm` v9+)
- **PHP**: v8.2 or higher with extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`
- **Composer**: v2.0+
- **Python**: v3.10 or v3.11 with `pip`
- **MySQL**: v8.0+ running locally on port 3306

---

#### Step 1: Setup the Backend API (Laravel 11)

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Configure your `.env` file:
   ```bash
   cp .env.example .env
   ```
   Ensure your database credentials in `backend/.env` match your local MySQL server:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=medicon_db
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_password
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Run database schema migrations and seed clinical datasets:
   ```bash
   php artisan migrate --seed
   ```

6. Start the Laravel backend server:
   ```bash
   php artisan serve --port=8080
   ```
   The backend API will run on `http://localhost:8080`.

---

#### Step 2: Setup the Machine Learning Microservice (FastAPI)

1. Open a new terminal and navigate to `ml-service`:
   ```bash
   cd ml-service
   ```

2. Create and activate a Python virtual environment:
   - On Windows (PowerShell):
     ```powershell
     python -m venv venv
     .\venv\Scripts\Activate.ps1
     ```
   - On Linux / macOS:
     ```bash
     python3 -m venv venv
     source venv/bin/activate
     ```

3. Install Python dependencies:
   ```bash
   pip install -r requirements.txt
   ```

4. Train and serialize the initial no-show prediction model:
   ```bash
   python train.py
   ```

5. Start the FastAPI development server:
   ```bash
   uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
   ```
   The ML service will run on `http://localhost:8000` (Interactive Swagger docs available at `http://localhost:8000/docs`).

---

#### Step 3: Setup the Frontend Application (Vue 3 + Vite)

1. Open a third terminal and navigate to `frontend`:
   ```bash
   cd frontend
   ```

2. Install Node dependencies:
   ```bash
   npm install
   ```

3. Configure frontend environment:
   ```bash
   cp .env.example .env
   ```

4. Launch the Vite development server:
   ```bash
   npm run dev
   ```
   The web application will be live at: `http://localhost:5173`.

---

## Seeded Clinical Accounts & Credentials

The application is pre-seeded with clean, realistic clinical accounts for all roles:

| Role | Name / Title | Specialty / Focus | Email | Password |
|---|---|---|---|---|
| Admin | Dr. Eleanor Vance, MD | Chief Medical Officer | `admin@medicon.health` | `Secret123!` |
| Doctor | Dr. Sarah Jenkins, MD, FACC | Cardiology | `sarah.jenkins@medicon.health` | `Secret123!` |
| Doctor | Dr. Marcus Chen, MD, PhD | Neurology | `marcus.chen@medicon.health` | `Secret123!` |
| Doctor | Dr. Elena Rostova, MD | Dermatology | `elena.rostova@medicon.health` | `Secret123!` |
| Doctor | Dr. James Wilson, MD | General Practice | `james.wilson@medicon.health` | `Secret123!` |
| Doctor | Dr. Aisha Patel, MD | Psychiatry | `aisha.patel@medicon.health` | `Secret123!` |
| Doctor | Dr. Robert Taylor, MD | Orthopedics | `robert.taylor@medicon.health` | `Secret123!` |
| Patient | Jane Doe | Hypertension / Wellness | `patient@medicon.health` | `Secret123!` |
| Patient | John Miller | Post-Op Orthopedics | `john.miller@medicon.health` | `Secret123!` |
| Patient | Emily Clark | Chronic Migraine | `emily.clark@medicon.health` | `Secret123!` |

*(The login screen also features 1-click role auto-fill buttons for quick testing).*

---

## Testing & Quality Assurance

### 1. Run Backend Automated Tests (PHPUnit)
```bash
cd backend
php artisan test
```
Test suites include:
- `AppointmentBookingConflictTest`: Validates slot collision prevention and doctor operating hours.
- `RoleBasedAccessControlTest`: Ensures strict endpoint authorization across Patient, Doctor, and Admin roles.
- `EncryptedMedicalRecordTest`: Verifies raw database ciphertext persistence and transparent Eloquent decryption.
- `ImmutableAuditLogTest`: Confirms append-only audit trail integrity and mutation blocking.
- `NoShowPredictionServiceTest`: Tests ML client communication and heuristic fallback behavior.

### 2. Run Machine Learning Microservice Tests (Pytest)
```bash
cd ml-service
pytest tests/ -v
```
Verifies single predictions, batch triage, input validation schemas, and API health endpoints.

### 3. Build & Type Check Frontend (Vite)
```bash
cd frontend
npm run build
```

---

## Telehealth Usage Walkthrough

1. **Starting or Joining a Consultation**:
   - Navigate to the Doctor or Patient dashboard.
   - Click "Join Call" on any scheduled appointment, click "New Room" to generate an ad-hoc session, or enter an existing 3-part code (e.g. `k9x-yqp2-481`).
2. **Pre-Join Green Room / Waiting Lobby**:
   - The camera and microphone start muted/off by default for privacy.
   - Toggle camera and microphone buttons on the 16:9 preview tile to verify your audio and video.
   - Check the clinical case summary and review who is already in the call.
   - Click "Join Now" to enter the active stage.
3. **In-Call Controls**:
   - Microphone and camera toggles.
   - Screen Sharing: Click "Present Screen" to share diagnostic scans, clinical reports, or browser tabs in an 80% spotlight stage.
   - In-Call Chat: Click the chat button to open the real-time encrypted messaging drawer.
   - Roster / Add Participants: Doctors can invite secondary specialists or medical interpreters.
4. **Ending the Consultation**:
   - Click "Leave Call" to exit the room while keeping it active for others, or select "End Call for Everyone & Purge Data" (Doctor/Admin) to wipe all in-room messages and tokens from the database.

---

## Project Structure

```
medicon/
├── backend/                  # Laravel 11 REST API
│   ├── app/
│   │   ├── Enums/            # PHP 8.2 Enumerations (UserRole, RiskLevel, etc.)
│   │   ├── Http/Controllers/ # REST Resource Controllers (Telehealth, Records, etc.)
│   │   ├── Http/Requests/    # Form Request Validations
│   │   ├── Http/Resources/   # JSON Output Resources
│   │   ├── Models/           # Eloquent Models with Encrypted Casts
│   │   ├── Policies/         # HIPAA Authorization Policies
│   │   └── Services/         # ML Client, Audit Service, Storage Services
│   ├── database/migrations/  # Database Migrations (13 tables + Telehealth rooms)
│   ├── database/seeders/     # Clinical Demo Datasets
│   └── tests/                # PHPUnit Test Suite
│
├── frontend/                 # Vue 3 SPA
│   ├── src/
│   │   ├── components/       # UI Modals, Badges, Header & Navigation
│   │   ├── layouts/          # AppLayout & AuthLayout
│   │   ├── router/           # Navigation Guards & RBAC Routes
│   │   ├── stores/           # Pinia State Stores (Auth, Appointments, Records, etc.)
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
├── docs/                     # Architecture & Technical Documentation
├── .gitignore                # Strict Git Exclusion Policy
└── docker-compose.yml        # Multi-Container Development Orchestration
```

---

## License

This project is licensed under the [MIT License](LICENSE).
