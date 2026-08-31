// Mock database & state management for standalone frontend operation
const STORAGE_KEY_USERS = 'medicon_mock_users_v2'
const STORAGE_KEY_APPTS = 'medicon_mock_appointments_v2'
const STORAGE_KEY_RECORDS = 'medicon_mock_records_v2'
const STORAGE_KEY_RX = 'medicon_mock_prescriptions_v2'
const STORAGE_KEY_LOGS = 'medicon_mock_audit_logs_v2'

export const defaultDoctors = [
  {
    id: 1,
    name: 'Dr. Sarah Jenkins, MD, FACC',
    email: 'sarah.jenkins@medicon.health',
    specialty: 'Cardiology',
    license_number: 'MD-CAR-88210',
    consultation_fee: 120,
    rating: 4.96,
    experience: 14,
    bio: 'Harvard Medical School alumna specializing in preventative cardiology, cardiac arrhythmias, echocardiography, and remote vital monitoring.',
    avatar_url: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
  },
  {
    id: 2,
    name: 'Dr. Marcus Chen, MD, PhD',
    email: 'marcus.chen@medicon.health',
    specialty: 'Neurology',
    license_number: 'MD-NEU-41903',
    consultation_fee: 115,
    rating: 4.91,
    experience: 10,
    bio: 'Board-certified Neurologist focusing on chronic migraine management, cognitive assessment, neuromuscular disorders, and tele-stroke triage.',
    avatar_url: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Wednesday', 'Friday'],
  },
  {
    id: 3,
    name: 'Dr. Elena Rostova, MD',
    email: 'elena.rostova@medicon.health',
    specialty: 'Dermatology',
    license_number: 'MD-DER-33918',
    consultation_fee: 95,
    rating: 4.93,
    experience: 8,
    bio: 'Clinical dermatologist dedicated to teledermatology, eczema and psoriasis protocols, autoimmune skin conditions, and early skin lesion assessment.',
    avatar_url: 'https://images.unsplash.com/photo-1594824813689-53b53c7c25a0?w=300&auto=format&fit=crop&q=80',
    available_days: ['Tuesday', 'Thursday', 'Saturday'],
  },
  {
    id: 4,
    name: 'Dr. James Wilson, MD',
    email: 'james.wilson@medicon.health',
    specialty: 'General Practice',
    license_number: 'MD-GEN-77401',
    consultation_fee: 75,
    rating: 4.89,
    experience: 16,
    bio: 'Primary care physician providing comprehensive family health, chronic disease management, metabolic screenings, and routine clinical checkups.',
    avatar_url: 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
  },
  {
    id: 5,
    name: 'Dr. Aisha Patel, MD',
    email: 'aisha.patel@medicon.health',
    specialty: 'Psychiatry',
    license_number: 'MD-PSY-92014',
    consultation_fee: 135,
    rating: 4.98,
    experience: 11,
    bio: 'Adult psychiatrist providing compassionate behavioral health, depression and anxiety management, psychopharmacology, and stress mitigation therapy.',
    avatar_url: 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=300&auto=format&fit=crop&q=80',
    available_days: ['Monday', 'Tuesday', 'Thursday', 'Friday'],
  },
  {
    id: 6,
    name: 'Dr. Robert Taylor, MD',
    email: 'robert.taylor@medicon.health',
    specialty: 'Orthopedics',
    license_number: 'MD-ORT-50119',
    consultation_fee: 125,
    rating: 4.92,
    experience: 13,
    bio: 'Orthopedic surgeon and musculoskeletal specialist focusing on sports injuries, joint rehabilitation, osteoarthritis, and pre-op evaluation.',
    avatar_url: 'https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=300&auto=format&fit=crop&q=80',
    available_days: ['Wednesday', 'Thursday', 'Friday'],
  },
]

// Unique room code generator (e.g. k9x-yqp2-481)
export function generateUniqueRoomCode() {
  const chars = 'abcdefghijklmnopqrstuvwxyz'
  const digits = '0123456789'
  const p1 = Array.from({ length: 3 }, () => chars[Math.floor(Math.random() * chars.length)]).join('')
  const p2 = Array.from({ length: 4 }, () => (Math.random() > 0.5 ? chars[Math.floor(Math.random() * chars.length)] : digits[Math.floor(Math.random() * digits.length)])).join('')
  const p3 = Math.floor(100 + Math.random() * 900)
  return `${p1}-${p2}-${p3}`
}

export const defaultAppointments = [
  {
    id: 1,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    scheduled_start: new Date(Date.now() + 1 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 1 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    room_code: 'k9x-yqp2-481',
    reason: 'Cardiovascular Follow-up & Blood Pressure Regulation Review',
    meeting_link: 'https://meet.medicon.health/telehealth/room/k9x-yqp2-481',
    no_show_risk_score: 0.082,
    no_show_risk_level: 'LOW',
    risk_factors: ['High patient engagement score', 'SMS reminder confirmed'],
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    scheduled_start: new Date(Date.now() + 4 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 4 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    room_code: 'der-881-209',
    reason: 'Atopic Dermatitis & Contact Eczema Progress Assessment',
    meeting_link: 'https://meet.medicon.health/telehealth/room/der-881-209',
    no_show_risk_score: 0.145,
    no_show_risk_level: 'LOW',
    risk_factors: ['Follow-up consultation'],
  },
  {
    id: 3,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 4,
    doctor_name: 'Dr. James Wilson, MD',
    doctor_specialty: 'General Practice',
    scheduled_start: new Date(Date.now() + 7 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 7 * 86400000 + 1800000).toISOString(),
    status: 'PENDING',
    type: 'IN_PERSON',
    room_code: null,
    reason: 'Annual Routine Health Checkup & Comprehensive Metabolic Panel',
    meeting_link: null,
    no_show_risk_score: 0.21,
    no_show_risk_level: 'LOW',
    risk_factors: ['Routine wellness booking'],
  },
  {
    id: 4,
    patient_id: 3,
    patient_name: 'Emily Clark',
    doctor_id: 2,
    doctor_name: 'Dr. Marcus Chen, MD, PhD',
    doctor_specialty: 'Neurology',
    scheduled_start: new Date(Date.now() + 2 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 2 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    room_code: 'neu-512-304',
    reason: 'Severe Episodic Migraine Consultation & Preventive Care Plan',
    meeting_link: 'https://meet.medicon.health/telehealth/room/neu-512-304',
    no_show_risk_score: 0.12,
    no_show_risk_level: 'LOW',
    risk_factors: ['Direct neurology referral'],
  },
  {
    id: 5,
    patient_id: 4,
    patient_name: 'Robert Vance',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    scheduled_start: new Date(Date.now() + 10 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() + 10 * 86400000 + 1800000).toISOString(),
    status: 'CONFIRMED',
    type: 'TELEHEALTH',
    room_code: 'gen-104-550',
    reason: 'Uncontrolled Hypertension Consultation & Dual Medication Titration',
    meeting_link: 'https://meet.medicon.health/telehealth/room/gen-104-550',
    no_show_risk_score: 0.742,
    no_show_risk_level: 'HIGH',
    risk_factors: [
      'Extended lead time (10 days)',
      'History of missed follow-ups in prior records',
      'Late Friday afternoon slot',
    ],
  },
  {
    id: 6,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    scheduled_start: new Date(Date.now() - 21 * 86400000).toISOString(),
    scheduled_end: new Date(Date.now() - 21 * 86400000 + 1800000).toISOString(),
    status: 'COMPLETED',
    type: 'TELEHEALTH',
    room_code: 'car-192-800',
    reason: 'Initial Cardiology Telehealth Evaluation & Baseline ECG Review',
    meeting_link: 'https://meet.medicon.health/telehealth/room/car-192-800',
    no_show_risk_score: 0.08,
    no_show_risk_level: 'LOW',
    risk_factors: ['Patient completed session on time'],
  },
]

export const defaultRecords = [
  {
    id: 1,
    appointment_id: 6,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    chief_complaint: 'Quarterly cardiovascular evaluation and episodic borderline systolic hypertension readings at home.',
    diagnosis: 'Essential Primary Hypertension (ICD-10: I10), Mild Hyperlipidemia (ICD-10: E78.5)',
    treatment_plan: 'Initiated Lisinopril 10mg and Atorvastatin 20mg. Recommended DASH dietary regimen (<2,000mg Na/day) and daily telemetry blood pressure logging.',
    subjective: 'Patient reports occasional tension headaches in late afternoons. Denies chest pain, orthopnea, palpitations, or lower extremity edema. Compliance with aerobic walking routine is 4x weekly.',
    objective: 'BP: 138/86 mmHg | HR: 72 bpm regular | SpO2: 99% on room air | BMI: 23.8 kg/m2\nCardiovascular: Normal S1/S2, no murmurs. Peripheral pulses +2 bilaterally.\nLungs: Clear to auscultation bilaterally.',
    assessment: 'Stage 1 Essential Hypertension with excellent functional status. Low-to-moderate cardiovascular risk profile.',
    plan: '1. Lisinopril 10mg PO daily in morning.\n2. Atorvastatin 20mg PO daily at bedtime.\n3. Basic Metabolic Panel (BMP) & Lipid Profile in 8 weeks.\n4. Follow-up via Telehealth in 3 months.',
    vital_signs: {
      blood_pressure: '138/86 mmHg',
      heart_rate: '72 bpm',
      temperature: '98.6 °F',
      spo2: '99%',
      weight: '62.5 kg',
    },
    created_at: new Date(Date.now() - 21 * 86400000).toISOString(),
  },
  {
    id: 2,
    appointment_id: null,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    chief_complaint: 'Pruritic erythematous rash across inner forearms following cosmetic detergent exposure.',
    diagnosis: 'Allergic Contact Dermatitis (ICD-10: L23.9)',
    treatment_plan: 'Topical Hydrocortisone 2.5% cream twice daily for 14 days. Emollient barrier cream recommended.',
    subjective: 'Patient noticed itchy flare-up 3 days after switching laundry detergents. No facial swelling or airway symptoms.',
    objective: 'Skin: Well-demarcated erythematous papules and mild excoriation over bilateral volar forearms. No secondary infection or vesicular weeping.',
    assessment: 'Acute Contact Eczema responsive to mild topical corticosteroid therapy.',
    plan: 'Apply thin layer of Hydrocortisone 2.5% BID for 14 days. Discontinue irritant detergent.',
    vital_signs: {
      blood_pressure: '120/78 mmHg',
      heart_rate: '70 bpm',
      temperature: '98.4 °F',
      spo2: '99%',
      weight: '62.5 kg',
    },
    created_at: new Date(Date.now() - 35 * 86400000).toISOString(),
  },
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
    instructions: 'Take 1 tablet every morning with or without water. Monitor home blood pressure weekly.',
    refills_remaining: 3,
    status: 'ACTIVE',
    prescribed_date: new Date(Date.now() - 21 * 86400000).toISOString(),
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    doctor_specialty: 'Cardiology',
    medication_name: 'Atorvastatin Calcium',
    dosage: '20mg',
    frequency: 'Once daily at bedtime',
    duration: '90 days',
    instructions: 'Take 1 tablet orally at bedtime. Report any unexplained muscle soreness.',
    refills_remaining: 3,
    status: 'ACTIVE',
    prescribed_date: new Date(Date.now() - 21 * 86400000).toISOString(),
  },
  {
    id: 3,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    medication_name: 'Hydrocortisone Cream 2.5%',
    dosage: 'Apply thin layer',
    frequency: 'Twice daily',
    duration: '14 days',
    instructions: 'Apply to affected forearm skin areas only. Discontinue if rash clears completely.',
    refills_remaining: 0,
    status: 'COMPLETED',
    prescribed_date: new Date(Date.now() - 35 * 86400000).toISOString(),
  },
]

export const defaultAuditLogs = [
  {
    id: 1,
    user_id: 3,
    user_name: 'Dr. Eleanor Vance, MD (CMO)',
    action: 'TELEHEALTH_SECURITY_AUDIT',
    entity_type: 'AuditLog',
    entity_id: 1,
    ip_address: '10.0.4.12',
    user_agent: 'Medicon/Clinical Gateway 2.0 (TLSv1.3)',
    created_at: new Date(Date.now() - 1800000).toISOString(),
  },
  {
    id: 2,
    user_id: 2,
    user_name: 'Dr. Sarah Jenkins, MD, FACC',
    action: 'TELEHEALTH_JOIN',
    entity_type: 'Appointment',
    entity_id: 1,
    ip_address: '10.0.8.44',
    user_agent: 'Medicon/WebRTC Telehealth Room sdf-sdyy-125',
    created_at: new Date(Date.now() - 3600000).toISOString(),
  },
  {
    id: 3,
    user_id: 1,
    user_name: 'Jane Doe',
    action: 'PATIENT_PORTAL_LOGIN',
    entity_type: 'User',
    entity_id: 1,
    ip_address: '192.168.1.105',
    user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0',
    created_at: new Date(Date.now() - 7200000).toISOString(),
  },
  {
    id: 4,
    user_id: 2,
    user_name: 'Dr. Sarah Jenkins, MD, FACC',
    action: 'PRESCRIPTION_ISSUED',
    entity_type: 'Prescription',
    entity_id: 1,
    ip_address: '10.0.8.44',
    user_agent: 'Medicon/EHR Portal 1.0',
    created_at: new Date(Date.now() - 86400000).toISOString(),
  },
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
