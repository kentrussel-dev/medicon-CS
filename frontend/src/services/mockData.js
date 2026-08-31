// Mock database & state management for standalone frontend operation
const STORAGE_KEY_USERS = 'medicon_mock_users_v3'
const STORAGE_KEY_APPTS = 'medicon_mock_appointments_v3'
const STORAGE_KEY_RECORDS = 'medicon_mock_records_v3'
const STORAGE_KEY_RX = 'medicon_mock_prescriptions_v3'
const STORAGE_KEY_LOGS = 'medicon_mock_audit_logs_v3'
const STORAGE_KEY_PAYMENTS = 'medicon_mock_payments_v3'
const STORAGE_KEY_2FA = 'medicon_mock_2fa_v3'

export const defaultDoctors = [
  {
    id: 1,
    name: 'Dr. Sarah Jenkins, MD, FACC',
    email: 'sarah.jenkins@medicon.health',
    specialty: 'Cardiology',
    license_number: 'MD-CAR-88210',
    consultation_fee: 1500,
    consultation_fee_cents: 150000,
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
    consultation_fee: 1200,
    consultation_fee_cents: 120000,
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
    consultation_fee: 800,
    consultation_fee_cents: 80000,
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
    consultation_fee: 500,
    consultation_fee_cents: 50000,
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
    consultation_fee: 1800,
    consultation_fee_cents: 180000,
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
    consultation_fee: 1250,
    consultation_fee_cents: 125000,
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
    scheduled_start: new Date(Date.now() + 86400000 * 2).toISOString(),
    scheduled_end: new Date(Date.now() + 86400000 * 2 + 1800000).toISOString(),
    status: 'CONFIRMED',
    payment_status: 'paid',
    consultation_fee_cents: 150000,
    type: 'TELEHEALTH',
    reason: 'Hypertension Follow-Up & Holter Review',
    meeting_link: 'https://meet.medicon.health/room/th-7821',
    room_code: 'k9x-yqp2-481',
    no_show_risk_score: 0.14,
    no_show_risk_level: 'LOW',
    risk_factors: ['Confirmed in Advance', 'Prior Show History'],
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 3,
    doctor_name: 'Dr. Elena Rostova, MD',
    doctor_specialty: 'Dermatology',
    scheduled_start: new Date(Date.now() + 86400000 * 5).toISOString(),
    scheduled_end: new Date(Date.now() + 86400000 * 5 + 1800000).toISOString(),
    status: 'CONFIRMED',
    payment_status: 'paid',
    consultation_fee_cents: 80000,
    type: 'IN_PERSON',
    reason: 'Annual Full-Body Skin Screening',
    meeting_link: null,
    no_show_risk_score: 0.28,
    no_show_risk_level: 'LOW',
    risk_factors: ['Routine Appointment', 'Standard Lead Time'],
  },
  {
    id: 3,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 4,
    doctor_name: 'Dr. James Wilson, MD',
    doctor_specialty: 'General Practice',
    scheduled_start: new Date(Date.now() - 86400000 * 14).toISOString(),
    scheduled_end: new Date(Date.now() - 86400000 * 14 + 1800000).toISOString(),
    status: 'COMPLETED',
    payment_status: 'paid',
    consultation_fee_cents: 50000,
    type: 'IN_PERSON',
    reason: 'Comprehensive Annual Wellness Physical',
    meeting_link: null,
    no_show_risk_score: 0.08,
    no_show_risk_level: 'LOW',
    risk_factors: ['Annual Routine Visit'],
  },
]

export const defaultRecords = [
  {
    id: 1,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    record_date: new Date(Date.now() - 86400000 * 14).toISOString().split('T')[0],
    diagnosis: 'Essential (Primary) Hypertension, Benign',
    clinical_notes: 'Patient reports well-tolerated Lisinopril 10mg daily. No dizzy spells or orthostatic hypotension. Heart sounds S1/S2 regular, no audible murmurs or gallops. Advised dietary sodium moderation.',
    treatment_plan: 'Continue Lisinopril 10mg PO daily. Log morning home blood pressure readings. Follow up in 6 months or via telehealth if BP exceeds 135/85.',
    vital_signs: {
      blood_pressure: '122/78 mmHg',
      heart_rate: '68 bpm',
      temperature: '98.4 °F (36.9 °C)',
      oxygen_saturation: '99% SpO2',
      weight: '64 kg',
      height: '168 cm',
      bmi: '22.7',
    },
    icd_10_codes: ['I10 - Essential (primary) hypertension', 'Z71.3 - Dietary counseling and surveillance'],
    created_at: new Date(Date.now() - 86400000 * 14).toISOString(),
  },
  {
    id: 2,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 4,
    doctor_name: 'Dr. James Wilson, MD',
    record_date: new Date(Date.now() - 86400000 * 90).toISOString().split('T')[0],
    diagnosis: 'Acute Upper Respiratory Tract Infection',
    clinical_notes: 'Mild pharyngeal erythema without exudate. Tympanic membranes clear bilaterally. Lungs clear to auscultation. Viral etiology suspected.',
    treatment_plan: 'Symptomatic hydration, saline nasal spray, acetaminophen 500mg as needed for throat discomfort. Return if fever persists beyond 72 hours.',
    vital_signs: {
      blood_pressure: '118/76 mmHg',
      heart_rate: '74 bpm',
      temperature: '99.1 °F (37.3 °C)',
      oxygen_saturation: '98% SpO2',
      weight: '64.5 kg',
      height: '168 cm',
      bmi: '22.9',
    },
    icd_10_codes: ['J06.9 - Acute upper respiratory infection, unspecified'],
    created_at: new Date(Date.now() - 86400000 * 90).toISOString(),
  },
]

export const defaultPrescriptions = [
  {
    id: 1,
    patient_id: 1,
    patient_name: 'Jane Doe',
    doctor_id: 1,
    doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
    notes: 'Take with full glass of water in the morning. Avoid potassium-containing salt substitutes without physician consultation.',
    is_dispensed: true,
    valid_until: new Date(Date.now() + 86400000 * 180).toISOString().split('T')[0],
    items: [
      {
        id: 1,
        medication_name: 'Lisinopril',
        dosage: '10mg',
        route: 'Oral Tablet',
        frequency: 'Once Daily (Morning)',
        refills_remaining: 3,
        instructions: 'Take 1 tablet every morning with or without food. Monitor blood pressure weekly.',
      },
      {
        id: 2,
        medication_name: 'Coenzyme Q10 (Ubiquinone)',
        dosage: '100mg',
        route: 'Oral Capsule',
        frequency: 'Once Daily',
        refills_remaining: 5,
        instructions: 'Cardioprotective dietary supplement. Take with meal containing healthy fats.',
      },
    ],
    created_at: new Date(Date.now() - 86400000 * 14).toISOString(),
  },
]

export const defaultPayments = [
  {
    id: 1,
    appointment_id: 1,
    user_id: 1,
    amount_cents: 150000,
    amount_pesos: '1,500.00',
    currency: 'PHP',
    gateway: 'paymongo',
    payment_method: 'gcash',
    status: 'paid',
    gateway_payment_id: 'pay_pm_gcash_882910',
    refund_amount_cents: 0,
    refund_amount_pesos: '0.00',
    refunded_at: null,
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
  },
  {
    id: 2,
    appointment_id: 2,
    user_id: 1,
    amount_cents: 80000,
    amount_pesos: '800.00',
    currency: 'PHP',
    gateway: 'paymongo',
    payment_method: 'card',
    status: 'paid',
    gateway_payment_id: 'pay_pm_card_399120',
    refund_amount_cents: 0,
    refund_amount_pesos: '0.00',
    refunded_at: null,
    created_at: new Date(Date.now() - 86400000 * 5).toISOString(),
  },
]

export const defaultAuditLogs = [
  {
    id: 1,
    user_id: 1,
    user_name: 'Jane Doe',
    action: 'TELEHEALTH_SESSION_JOINED',
    entity_type: 'TelehealthRoom',
    entity_id: 1,
    ip_address: '192.168.1.45',
    user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0',
    created_at: new Date(Date.now() - 3600000).toISOString(),
  },
  {
    id: 2,
    user_id: 2,
    user_name: 'Dr. Sarah Jenkins, MD, FACC',
    action: 'MEDICAL_RECORD_CREATED',
    entity_type: 'MedicalRecord',
    entity_id: 1,
    ip_address: '10.0.8.44',
    user_agent: 'Medicon/EHR Portal 1.0',
    created_at: new Date(Date.now() - 86400000 * 14).toISOString(),
  },
  {
    id: 3,
    user_id: 1,
    user_name: 'Jane Doe',
    action: 'PAYMENT_COMPLETED_GCASH',
    entity_type: 'Payment',
    entity_id: 1,
    ip_address: '192.168.1.45',
    user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/128.0',
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
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

export function getStoredPayments() {
  const data = localStorage.getItem(STORAGE_KEY_PAYMENTS)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_PAYMENTS, JSON.stringify(defaultPayments))
    return defaultPayments
  }
  return JSON.parse(data)
}

export function saveStoredPayments(payments) {
  localStorage.setItem(STORAGE_KEY_PAYMENTS, JSON.stringify(payments))
}

export function getStoredAuditLogs() {
  const data = localStorage.getItem(STORAGE_KEY_LOGS)
  if (!data) {
    localStorage.setItem(STORAGE_KEY_LOGS, JSON.stringify(defaultAuditLogs))
    return defaultAuditLogs
  }
  return JSON.parse(data)
}

export function getTwoFactorState() {
  const data = localStorage.getItem(STORAGE_KEY_2FA)
  if (!data) {
    return { enabled: false, secret: null, recovery_codes: [] }
  }
  return JSON.parse(data)
}

export function setTwoFactorState(state) {
  localStorage.setItem(STORAGE_KEY_2FA, JSON.stringify(state))
}
