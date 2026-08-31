// Mock database & state management for standalone frontend operation
const STORAGE_KEY_USERS = 'medicon_mock_users'
const STORAGE_KEY_APPTS = 'medicon_mock_appointments'
const STORAGE_KEY_RECORDS = 'medicon_mock_records'
const STORAGE_KEY_RX = 'medicon_mock_prescriptions'
const STORAGE_KEY_LOGS = 'medicon_mock_audit_logs'

export const defaultDoctors = [
  {
    id: 1,
    name: 'Dr. Sarah Jenkins, MD, FACC',
    email: 'sarah.jenkins@medicon.health',
    specialty: 'Cardiology',
    license_number: 'MD-99281-STATE',
    consultation_fee: 90,
    rating: 4.95,
    bio: 'Board-certified cardiologist specializing in cardiovascular health and preventive diagnostics.',
    avatar_url: 'https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
  },
  {
    id: 2,
    name: 'Dr. Marcus Chen, MD, PhD',
    email: 'marcus.chen@medicon.health',
    specialty: 'Neurology',
    license_number: 'MD-88310-STATE',
    consultation_fee: 110,
    rating: 4.88,
    bio: 'Director of Neurosciences with expertise in stroke rehabilitation and cognitive disorders.',
    avatar_url: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Wednesday', 'Friday'],
  },
  {
    id: 3,
    name: 'Dr. Elena Rostova, MD',
    email: 'elena.rostova@medicon.health',
    specialty: 'Dermatology',
    license_number: 'MD-77192-STATE',
    consultation_fee: 85,
    rating: 4.92,
    bio: 'Clinical dermatologist specializing in oncological dermatology and inflammatory skin conditions.',
    avatar_url: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&auto=format&fit=crop&q=80',
    available_days: ['Tuesday', 'Thursday', 'Saturday'],
  },
  {
    id: 4,
    name: 'Dr. James Wilson, MD',
    email: 'james.wilson@medicon.health',
    specialty: 'General Practice',
    license_number: 'MD-66290-STATE',
    consultation_fee: 65,
    rating: 4.90,
    bio: 'Attending physician providing primary preventive care, chronic disease management, and urgent triage.',
    avatar_url: 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
  },
  {
    id: 5,
    name: 'Dr. Priya Patel, MD',
    email: 'priya.patel@medicon.health',
    specialty: 'Pediatrics',
    license_number: 'MD-55410-STATE',
    consultation_fee: 75,
    rating: 4.97,
    bio: 'Pediatric care specialist focused on childhood development and preventive immunizations.',
    avatar_url: 'https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Thursday', 'Friday'],
  },
  {
    id: 6,
    name: 'Dr. Robert Torres, MD',
    email: 'robert.torres@medicon.health',
    specialty: 'Orthopedic',
    license_number: 'MD-44321-STATE',
    consultation_fee: 120,
    rating: 4.85,
    bio: 'Orthopedic surgeon focusing on joint reconstruction, sports injuries, and spinal mobility.',
    avatar_url: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=300&auto=format&fit=crop&q=80',
    available_days: ['Wednesday', 'Thursday', 'Friday'],
  }
]

export const defaultAppointments = [
  {
    id: 1,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    scheduled_start: new Date(Date.now() + 2 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 2 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    reason: 'Cardiovascular follow-up & blood pressure regulation review',
    meeting_link: 'https://meet.medicon.health/room/cv-7782',
    no_show_risk_score: 0.12,
    no_show_risk_level: 'LOW',
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 4,
    doctor_name: 'Dr. James Wilson, MD',
    doctor_specialty: 'General Practice',
    scheduled_start: new Date(Date.now() + 5 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 5 * 86400000 + 1800000).toISOString(),
    status: 'PENDING',
    type: 'IN_PERSON',
    reason: 'Annual routine health checkup & comprehensive metabolic panel',
    meeting_link: null,
    no_show_risk_score: 0.18,
    no_show_risk_level: 'LOW',
  },
  {
    id: 3,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    scheduled_start: new Date(Date.now() - 14 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() - 14 * 86400000 + 1800000).toISOString(),
    status: 'COMPLETED',
    type: 'TELEHEALTH',
    reason: 'Allergic skin reaction evaluation & prescription renewal',
    meeting_link: 'https://meet.medicon.health/room/dm-1102',
    no_show_risk_score: 0.08,
    no_show_risk_level: 'LOW',
  },
  {
    id: 4,
    patient_id: 2,
    patient_name: 'Robert Vance',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    scheduled_start: new Date(Date.now() + 10 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 10 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    reason: 'Hypertension consultation and stress test results',
    meeting_link: 'https://meet.medicon.health/room/cv-9901',
    no_show_risk_score: 0.74,
    no_show_risk_level: 'HIGH',
    risk_factors: [
      'High booking lead time (10 days)',
      'Prior missed appointment recorded',
      'Friday afternoon scheduling slot',
    ],
  }
]

export const defaultRecords = [
  {
    id: 1,
    appointment_id: 3,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    diagnosis: 'Contact Dermatitis (ICD-10 L23.9)',
    clinical_notes: 'Patient presented with localized erythema and pruritus. No signs of infection. Prescribed topical hydrocortisone cream 1% for 10 days.',
    vital_signs: {
      blood_pressure: '118/76',
      heart_rate: '72 bpm',
      temperature: '98.6 °F',
      spo2: '99%',
      weight: '64 kg',
    },
    created_at: new Date(Date.now() - 14 * 86400000).toISOString(),
  },
  {
    id: 2,
    appointment_id: null,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    diagnosis: 'Essential Hypertension (ICD-10 I10)',
    clinical_notes: 'Cardiovascular examination normal. Baseline ECG shows normal sinus rhythm. Continued low-sodium diet and Lisinopril 10mg daily.',
    vital_signs: {
      blood_pressure: '124/80',
      heart_rate: '68 bpm',
      temperature: '98.4 °F',
      spo2: '98%',
      weight: '65 kg',
    },
    created_at: new Date(Date.now() - 45 * 86400000).toISOString(),
  }
]

export const defaultPrescriptions = [
  {
    id: 1,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    medication_name: 'Lisinopril',
    dosage: '10mg',
    frequency: 'Once daily in the morning',
    duration: '90 days',
    instructions: 'Take with or without food. Monitor blood pressure weekly.',
    refills_remaining: 3,
    status: 'ACTIVE',
    prescribed_date: new Date(Date.now() - 15 * 86400000).toISOString(),
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    medication_name: 'Hydrocortisone 1% Cream',
    dosage: 'Apply thin layer',
    frequency: 'Twice daily',
    duration: '10 days',
    instructions: 'Apply to affected areas only. Discontinue if rash spreads.',
    refills_remaining: 0,
    status: 'COMPLETED',
    prescribed_date: new Date(Date.now() - 14 * 86400000).toISOString(),
  }
]

export const defaultAuditLogs = [
  {
    id: 1,
    user_id: 3,
    user_name: 'Operations Administrator',
    action: 'USER_ROLE_UPDATED',
    entity_type: 'User',
    entity_id: 2,
    ip_address: '127.0.0.1',
    user_agent: 'Medicon/EHR Client 1.0',
    created_at: new Date(Date.now() - 3600000).toISOString(),
  },
  {
    id: 2,
    user_id: 2,
    user_name: 'Dr. Sarah Jenkins',
    action: 'RECORD_ACCESSED',
    entity_type: 'MedicalRecord',
    entity_id: 1,
    ip_address: '127.0.0.1',
    user_agent: 'Medicon/EHR Client 1.0',
    created_at: new Date(Date.now() - 7200000).toISOString(),
  },
  {
    id: 3,
    user_id: 1,
    user_name: 'Jane Doe',
    action: 'APPOINTMENT_SCHEDULED',
    entity_type: 'Appointment',
    entity_id: 1,
    ip_address: '127.0.0.1',
    user_agent: 'Medicon/EHR Client 1.0',
    created_at: new Date(Date.now() - 86400000).toISOString(),
  }
]

export function getStoredAppointments() {
  const data = localStorage.getItem(STORAGE_KEY_APPTS)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_APPTS, JSON.stringify(defaultAppointments))
    return defaultAppointments
  }
  return JSON.parse(data)
}

export function saveStoredAppointments(appts) {
  localStorage.setItem(STORAGE_KEY_APPTS, JSON.stringify(appts))
}

export function getStoredRecords() {
  const data = localStorage.getItem(STORAGE_KEY_RECORDS)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_RECORDS, JSON.stringify(defaultRecords))
    return defaultRecords
  }
  return JSON.parse(data)
}

export function saveStoredRecords(records) {
  localStorage.setItem(STORAGE_KEY_RECORDS, JSON.stringify(records))
}

export function getStoredPrescriptions() {
  const data = localStorage.getItem(STORAGE_KEY_RX)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_RX, JSON.stringify(defaultPrescriptions))
    return defaultPrescriptions
  }
  return JSON.parse(data)
}

export function saveStoredPrescriptions(rxs) {
  localStorage.setItem(STORAGE_KEY_RX, JSON.stringify(rxs))
}

export function getStoredAuditLogs() {
  const data = localStorage.getItem(STORAGE_KEY_LOGS)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_LOGS, JSON.stringify(defaultAuditLogs))
    return defaultAuditLogs
  }
  return JSON.parse(data)
}
