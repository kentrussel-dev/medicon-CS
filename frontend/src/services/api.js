import axios from 'axios'
import { useNotificationStore } from '@/stores/notifications'
import {
  defaultDoctors,
  getStoredAppointments,
  saveStoredAppointments,
  getStoredRecords,
  saveStoredRecords,
  getStoredPrescriptions,
  saveStoredPrescriptions,
  getStoredAuditLogs,
} from './mockData'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Request Interceptor: Attach Auth Bearer Token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('medicon_auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Client-Side Mock Handler for Standalone Local Execution
const handleMockRoute = (config) => {
  const method = (config.method || 'get').toLowerCase()
  const rawUrl = (config.url || '').replace(/^\/api/, '')
  const [urlPath, queryString] = rawUrl.split('?')
  const url = urlPath.replace(/\/$/, '')
  const params = config.params || {}
  const body = typeof config.data === 'string' ? JSON.parse(config.data || '{}') : config.data || {}

  // 1. Auth routes
  if (url === '/auth/login' && method === 'post') {
    const email = (body.email || '').toLowerCase()
    let user = {
      id: 1,
      name: 'Jane Doe',
      email: email || 'patient@medicon.health',
      role: 'patient',
      avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
      patient: { id: 1, allergies: 'Penicillin, Sulfa', blood_type: 'O+' },
    }
    if (email.includes('admin')) {
      user = {
        id: 3,
        name: 'Operations Administrator',
        email: email,
        role: 'admin',
        avatar_url: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
      }
    } else if (email.includes('doctor') || email.includes('jenkins') || email.includes('sarah')) {
      user = {
        id: 2,
        name: 'Dr. Sarah Jenkins, MD, FACC',
        email: email,
        role: 'doctor',
        avatar_url: 'https://images.unsplash.com/photo-1594824813593-9c8df6cbeeb0?w=150&auto=format&fit=crop&q=80',
        doctor: { id: 1, specialty: 'Cardiology', license_number: 'MD-99281-STATE', consultation_fee: 90, rating: 4.95 },
      }
    }
    return { status: 200, data: { user, token: 'mock_jwt_token_' + Date.now() } }
  }

  if (url === '/auth/register' && method === 'post') {
    const user = {
      id: Date.now(),
      name: body.name || 'Registered Patient',
      email: body.email,
      role: 'patient',
      avatar_url: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80',
      patient: {
        id: Date.now(),
        date_of_birth: body.date_of_birth || '1995-01-01',
        gender: body.gender || 'F',
        allergies: body.allergies || 'None recorded',
        blood_type: 'O+',
      },
    }
    return { status: 201, data: { user, token: 'mock_jwt_token_' + Date.now() } }
  }

  if (url === '/auth/me' && method === 'get') {
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    return { status: 200, data: { user } }
  }

  if (url === '/auth/logout' && method === 'post') {
    return { status: 200, data: { message: 'Logged out' } }
  }

  // 2. Admin Analytics
  if (url === '/admin/analytics/overview' && method === 'get') {
    return {
      status: 200,
      data: {
        overview: {
          total_patients: 1240,
          total_doctors: 28,
          total_appointments: 3410,
          no_show_rate: 11.2,
        },
        doctor_utilization: [
          { doctor_id: 1, name: 'Dr. Sarah Jenkins', specialty: 'Cardiology', total_appointments: 48, rating: 4.95 },
          { doctor_id: 2, name: 'Dr. Marcus Chen', specialty: 'Neurology', total_appointments: 36, rating: 4.88 },
          { doctor_id: 3, name: 'Dr. Elena Rostova', specialty: 'Dermatology', total_appointments: 42, rating: 4.92 },
          { doctor_id: 4, name: 'Dr. James Wilson', specialty: 'General Practice', total_appointments: 64, rating: 4.90 },
        ],
      },
    }
  }

  if (url === '/admin/high-risk-appointments' && method === 'get') {
    const appts = getStoredAppointments().filter((a) => a.no_show_risk_level === 'HIGH')
    return { status: 200, data: { appointments: appts } }
  }

  if (url === '/admin/users' && method === 'get') {
    return {
      status: 200,
      data: {
        data: [
          { id: 1, name: 'Jane Doe', email: 'patient@medicon.health', role: 'patient', is_active: true, created_at: '2026-01-15' },
          { id: 2, name: 'Dr. Sarah Jenkins', email: 'sarah.jenkins@medicon.health', role: 'doctor', is_active: true, created_at: '2025-11-20' },
          { id: 3, name: 'Operations Administrator', email: 'admin@medicon.health', role: 'admin', is_active: true, created_at: '2025-08-01' },
          { id: 4, name: 'Dr. Marcus Chen', email: 'marcus.chen@medicon.health', role: 'doctor', is_active: true, created_at: '2025-10-05' },
        ],
      },
    }
  }

  if (url.startsWith('/admin/users/') && url.endsWith('/role') && method === 'patch') {
    const parts = url.split('/')
    const userId = Number(parts[3])
    return { status: 200, data: { user: { id: userId, role: body.role } } }
  }

  if (url === '/admin/audit-logs' && method === 'get') {
    return { status: 200, data: { data: getStoredAuditLogs() } }
  }

  // 3. Appointments
  if (url === '/appointments' && method === 'get') {
    let list = getStoredAppointments()
    if (params.status) {
      list = list.filter((a) => a.status === params.status)
    }
    return {
      status: 200,
      data: {
        data: list,
        meta: { current_page: 1, last_page: 1, total: list.length },
      },
    }
  }

  if (url === '/appointments' && method === 'post') {
    const list = getStoredAppointments()
    const newAppt = {
      id: Date.now(),
      patient_id: 1,
      patient_name: 'Jane Doe',
      doctor_id: body.doctor_id || 1,
      doctor_name: body.doctor_name || 'Dr. Sarah Jenkins, MD, FACC',
      doctor_specialty: body.doctor_specialty || 'Cardiology',
      scheduled_start: body.scheduled_start || new Date(Date.now() + 2 * 86400000).toISOString(),
      scheduled_end: body.scheduled_end || new Date(Date.now() + 2 * 86400000 + 1800000).toISOString(),
      status: 'CONFIRMED',
      type: body.type || 'TELEHEALTH',
      reason: body.reason || 'Clinical Consultation',
      meeting_link: body.type === 'TELEHEALTH' ? `https://meet.medicon.health/room/th-${Date.now().toString().slice(-4)}` : null,
      no_show_risk_score: 0.12,
      no_show_risk_level: 'LOW',
    }
    list.unshift(newAppt)
    saveStoredAppointments(list)
    return { status: 201, data: { appointment: newAppt } }
  }

  if (url.match(/^\/appointments\/\d+\/reschedule$/) && method === 'post') {
    const id = Number(url.split('/')[2])
    const list = getStoredAppointments()
    const item = list.find((a) => a.id === id)
    if (item) {
      item.scheduled_start = body.scheduled_start
      item.status = 'CONFIRMED'
      saveStoredAppointments(list)
    }
    return { status: 200, data: { appointment: item } }
  }

  if (url.match(/^\/appointments\/\d+\/cancel$/) && method === 'post') {
    const id = Number(url.split('/')[2])
    const list = getStoredAppointments()
    const item = list.find((a) => a.id === id)
    if (item) {
      item.status = 'CANCELLED'
      item.cancellation_reason = body.cancellation_reason
      saveStoredAppointments(list)
    }
    return { status: 200, data: { appointment: item } }
  }

  if (url.match(/^\/appointments\/\d+\/status$/) && method === 'patch') {
    const id = Number(url.split('/')[2])
    const list = getStoredAppointments()
    const item = list.find((a) => a.id === id)
    if (item) {
      item.status = body.status
      saveStoredAppointments(list)
    }
    return { status: 200, data: { appointment: item } }
  }

  if (url.match(/^\/appointments\/\d+$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const item = getStoredAppointments().find((a) => a.id === id)
    return { status: 200, data: { appointment: item } }
  }

  // Telehealth WebRTC Room Endpoints
  if (url.match(/^\/appointments\/\d+\/telehealth\/token$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const appt = getStoredAppointments().find((a) => a.id === id) || {
      id: id,
      doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
      doctor_specialty: 'Cardiology',
      patient_name: 'Jane Doe',
      scheduled_start: new Date().toISOString(),
      type: 'TELEHEALTH',
      status: 'CONFIRMED',
      reason: 'Cardiology Multi-Party Consultation',
    }
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const role = (user?.role || 'patient').toUpperCase()
    const roomCode = 'sdf-sdyy-125'

    return {
      status: 200,
      data: {
        success: true,
        room_code: roomCode,
        appointment: { ...appt, room_code: roomCode },
        session: {
          token: `lk_mock_jwt_token_${id}_${Date.now()}`,
          room_name: `medicon_room_${roomCode}`,
          livekit_url: 'ws://localhost:7880',
          identity: `user_${user?.id || 1}_${role.toLowerCase()}`,
          participant_name: user?.name || 'Jane Doe',
          role: role,
          is_host: role === 'DOCTOR' || role === 'ADMIN',
          expires_at: new Date(Date.now() + 7200000).toISOString(),
        },
      },
    }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/close$/) && method === 'post') {
    const id = Number(url.split('/')[2])
    localStorage.removeItem(`medicon_chat_room_${id}`)
    localStorage.removeItem(`medicon_chat_room_sdf-sdyy-125`)
    return {
      status: 200,
      data: {
        success: true,
        message: 'Consultation room closed and in-call messages purged.',
        new_room_code: 'med-' + Math.random().toString(36).substring(2, 6) + '-' + Math.floor(100 + Math.random() * 900),
      },
    }
  }

  if (url.match(/^\/telehealth\/rooms\/[^/]+\/close$/) && method === 'post') {
    const code = url.split('/')[3]
    localStorage.removeItem(`medicon_chat_room_${code}`)
    return {
      status: 200,
      data: {
        success: true,
        message: 'Room closed and all in-call data purged.',
      },
    }
  }

  if (url === '/telehealth/rooms/create' && method === 'post') {
    const part1 = Math.random().toString(36).substring(2, 5)
    const part2 = Math.random().toString(36).substring(2, 6)
    const part3 = Math.floor(100 + Math.random() * 900)
    const code = `${part1}-${part2}-${part3}`
    return {
      status: 201,
      data: {
        success: true,
        room: {
          id: Date.now(),
          room_code: code,
          title: body.title || 'Instant Clinical Consultation',
          status: 'ACTIVE',
        },
        join_url: `${window.location.origin}/telehealth/room/${code}`,
      },
    }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/participants$/) && method === 'get') {
    return {
      status: 200,
      data: {
        success: true,
        participants: [
          { id: 1, name: 'Dr. Marcus Chen (Neurology Specialist)', role: 'specialist' },
          { id: 2, name: 'Carlos Silva (Medical Translator)', role: 'translator' },
        ],
      },
    }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/participants$/) && method === 'post') {
    const id = Number(url.split('/')[2])
    const newParticipant = {
      id: Date.now(),
      appointment_id: id,
      name: body.name || 'Invited Specialist',
      role: body.role || 'specialist',
      email: body.email || null,
      created_at: new Date().toISOString(),
    }
    return {
      status: 201,
      data: {
        success: true,
        participant: newParticipant,
        session: {
          token: `lk_mock_participant_token_${newParticipant.id}`,
          room_name: `medicon_room_appt_${id}`,
          livekit_url: 'ws://localhost:7880',
          identity: `participant_${newParticipant.id}_${newParticipant.role}`,
          participant_name: newParticipant.name,
          role: newParticipant.role.toUpperCase(),
          is_host: false,
          expires_at: new Date(Date.now() + 7200000).toISOString(),
        },
      },
    }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/events$/) && method === 'post') {
    return { status: 200, data: { success: true } }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/messages$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const stored = JSON.parse(localStorage.getItem(`medicon_chat_room_${id}`) || 'null') || [
      {
        id: 1,
        sender_name: 'Dr. Sarah Jenkins, MD, FACC',
        sender_role: 'DOCTOR',
        message: 'Hello! Welcome to our telehealth room. Dr. Marcus Chen has joined us as well for your consultation.',
        time: '6:30 PM',
      },
      {
        id: 2,
        sender_name: 'Dr. Marcus Chen',
        sender_role: 'SPECIALIST',
        message: 'Good day! I have your diagnostic timeline and vital logs ready.',
        time: '6:31 PM',
      },
    ]
    return { status: 200, data: { success: true, messages: stored } }
  }

  if (url.match(/^\/appointments\/\d+\/telehealth\/messages$/) && method === 'post') {
    const id = Number(url.split('/')[2])
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const key = `medicon_chat_room_${id}`
    const stored = JSON.parse(localStorage.getItem(key) || 'null') || []
    const newMsg = {
      id: Date.now(),
      appointment_id: id,
      sender_name: user?.name || 'Jane Doe',
      sender_role: (user?.role || 'patient').toUpperCase(),
      message: body.message,
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    }
    stored.push(newMsg)
    localStorage.setItem(key, JSON.stringify(stored))
    return { status: 201, data: { success: true, message: newMsg } }
  }

  // 4. Doctors
  if (url === '/doctors' && method === 'get') {
    let list = defaultDoctors
    if (params.specialty) {
      list = list.filter((d) => d.specialty.toLowerCase().includes(params.specialty.toLowerCase()))
    }
    if (params.search) {
      const q = params.search.toLowerCase()
      list = list.filter((d) => d.name.toLowerCase().includes(q) || d.specialty.toLowerCase().includes(q))
    }
    return { status: 200, data: { data: list } }
  }

  if (url === '/doctors/specialties' && method === 'get') {
    return {
      status: 200,
      data: {
        specialties: ['Cardiology', 'Dermatology', 'Neurology', 'Orthopedic', 'Pediatrics', 'General Practice'],
      },
    }
  }

  if (url.match(/^\/doctors\/\d+$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const item = defaultDoctors.find((d) => d.id === id) || defaultDoctors[0]
    return { status: 200, data: { doctor: item } }
  }

  if (url === '/doctor-availabilities' && method === 'get') {
    return {
      status: 200,
      data: {
        availabilities: [
          { day_of_week: 'Monday', start_time: '09:00', end_time: '17:00', is_active: true },
          { day_of_week: 'Tuesday', start_time: '09:00', end_time: '17:00', is_active: true },
          { day_of_week: 'Wednesday', start_time: '09:00', end_time: '17:00', is_active: true },
          { day_of_week: 'Thursday', start_time: '09:00', end_time: '17:00', is_active: true },
          { day_of_week: 'Friday', start_time: '09:00', end_time: '17:00', is_active: true },
        ],
      },
    }
  }

  if (url === '/doctor-availabilities' && method === 'post') {
    return { status: 200, data: { availabilities: body.slots } }
  }

  // 5. Medical Records
  if (url === '/medical-records' && method === 'get') {
    return { status: 200, data: { data: getStoredRecords() } }
  }

  if (url === '/medical-records' && method === 'post') {
    const list = getStoredRecords()
    const newRec = {
      id: Date.now(),
      appointment_id: body.appointment_id || null,
      patient_id: body.patient_id || 1,
      patient_name: 'Jane Doe',
      doctor_id: 1,
      doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
      doctor_specialty: 'Cardiology',
      diagnosis: body.diagnosis || 'Clinical Follow-Up',
      clinical_notes: body.clinical_notes || '',
      vital_signs: body.vital_signs || { blood_pressure: '120/80', heart_rate: '70 bpm' },
      created_at: new Date().toISOString(),
    }
    list.unshift(newRec)
    saveStoredRecords(list)
    return { status: 201, data: { record: newRec } }
  }

  if (url.match(/^\/medical-records\/\d+$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const item = getStoredRecords().find((r) => r.id === id)
    return { status: 200, data: { record: item } }
  }

  // 6. Prescriptions
  if (url === '/prescriptions' && method === 'get') {
    return { status: 200, data: { data: getStoredPrescriptions() } }
  }

  if (url === '/prescriptions' && method === 'post') {
    const list = getStoredPrescriptions()
    const newRx = {
      id: Date.now(),
      patient_id: body.patient_id || 1,
      patient_name: 'Jane Doe',
      doctor_id: 1,
      doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
      doctor_specialty: 'Cardiology',
      medication_name: body.medication_name || 'Prescription Drug',
      dosage: body.dosage || '10mg',
      frequency: body.frequency || 'Once daily',
      duration: body.duration || '30 days',
      instructions: body.instructions || 'Take as directed.',
      refills_remaining: body.refills_remaining !== undefined ? body.refills_remaining : 1,
      status: 'ACTIVE',
      prescribed_date: new Date().toISOString(),
    }
    list.unshift(newRx)
    saveStoredPrescriptions(list)
    return { status: 201, data: { prescription: newRx } }
  }

  if (url.match(/^\/prescriptions\/\d+$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const item = getStoredPrescriptions().find((p) => p.id === id)
    return { status: 200, data: { prescription: item } }
  }

  // 7. AI Clinical Assistant (Gemini Flash Model)
  if (url === '/ai/chat' && method === 'post') {
    const prompt = (body.message || '').toLowerCase()
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const role = user?.role || 'patient'
    let answer = ''
    let isCached = false

    // General "What is this website about?" across all roles
    if (prompt.includes('website') || prompt.includes('about') || prompt.includes('what is medicon') || prompt.includes('purpose')) {
      if (role === 'admin') {
        answer = "**Medicon Clinical Operations Platform**\n\n" +
          "Medicon is an enterprise hospital management and telehealth platform. As an **Operations Administrator**, your portal allows you to:\n\n" +
          "1. **Monitor Clinical Utilization:** Track hospital-wide encounters, doctor workload, and department capacity.\n" +
          "2. **Attendance Risk Triage:** Review appointments flagged by our ML attendance prediction model to prevent clinic no-shows.\n" +
          "3. **HIPAA Compliance Audit Trail:** Inspect immutable audit logs of all user actions and medical record access.\n" +
          "4. **User & RBAC Management:** Manage patient, doctor, and staff roles and access permissions."
      } else if (role === 'doctor') {
        answer = "**Medicon Clinical Portal**\n\n" +
          "Medicon is an integrated telehealth and practice management system for medical providers. In your **Physician Portal**, you can:\n\n" +
          "1. **Manage Clinical Schedule:** Configure weekly consultation hours and appointment slots.\n" +
          "2. **Conduct Encounters:** Launch encrypted HD telehealth video visits with scheduled patients.\n" +
          "3. **Document EHR Records:** Record diagnostic notes, ICD-10 codes, and vital signs.\n" +
          "4. **Issue Prescriptions:** Prescribe and manage electronic medication courses with automatic refills."
      } else {
        answer = "**Medicon Healthcare & Patient Portal**\n\n" +
          "Medicon is a modern telehealth and patient care platform. In your **Patient Portal**, you can:\n\n" +
          "1. **Book Consultations:** Schedule in-person or virtual HD video appointments with certified specialists.\n" +
          "2. **Encrypted Medical Records:** View your post-consultation diagnoses, doctor notes, and vitals history.\n" +
          "3. **Active Prescriptions:** Review prescribed medications, dosages, frequency, and refills remaining.\n" +
          "4. **Clinical AI Assistant:** Ask questions about clinic hours, appointment preparation, and medications."
      }
      isCached = true
    } else if (role === 'admin') {
      // Specific Admin queries
      if (prompt.includes('risk') || prompt.includes('attendance') || prompt.includes('no-show') || prompt.includes('ml')) {
        answer = "**Attendance Risk Stratification (ML Model)**\n\n" +
          "- **Function:** Predicts the probability of patient missed visits based on lead time, prior history, and scheduling parameters.\n" +
          "- **Tiers:** Low (<35%), Moderate (35%-64%), and High (≥65%).\n" +
          "- **Action:** High-risk appointments appear in the Active Triage Queue for targeted reminder confirmations."
      } else if (prompt.includes('hipaa') || prompt.includes('audit') || prompt.includes('compliance') || prompt.includes('log')) {
        answer = "**HIPAA Audit Compliance**\n\n" +
          "- **Audit Standard:** All patient record access, downloads, role modifications, and AI queries are permanently logged.\n" +
          "- **Storage:** Immutable records stored with actor ID, IP address, user agent, and ISO-8601 timestamps with 7-year retention."
      } else if (prompt.includes('utilization') || prompt.includes('doctor') || prompt.includes('metric')) {
        answer = "**Physician Utilization Registry**\n\n" +
          "- Active attending physicians: 28 licensed specialists.\n" +
          "- System-wide consultation completion rate: 88.8%.\n" +
          "- Real-time workload and rating distributions are tracked on your Executive Dashboard."
      } else {
        answer = `Hello, ${user?.name || 'Administrator'}! I am your Medicon Hospital Operations Assistant. I can help analyze clinical attendance metrics, doctor utilization, and HIPAA compliance policies.`
      }
    } else if (role === 'doctor') {
      // Doctor scope
      if (prompt.includes('soap') || prompt.includes('draft') || prompt.includes('note')) {
        answer = "**Draft SOAP Clinical Encounter Note**\n\n" +
          "**S (Subjective):** Patient presents for routine cardiology follow-up. Reports strict adherence to Lisinopril 10mg. Denies chest pain, palpitations, or lightheadedness.\n" +
          "**O (Objective):** BP 124/80 mmHg, HR 68 bpm regular, SpO2 99%. S1/S2 present, no murmurs. Lungs clear to auscultation bilaterally.\n" +
          "**A (Assessment):** Primary Essential Hypertension (ICD-10 I10) - well-controlled under current monotherapy.\n" +
          "**P (Plan):** Maintain Lisinopril 10mg daily. Routine comprehensive metabolic panel in 6 months. Follow-up clinic visit in 6 months."
      } else if (prompt.includes('interaction') || prompt.includes('potassium') || prompt.includes('nsaid') || prompt.includes('lisinopril')) {
        answer = "**Pharmacology Clinical Reference: ACE Inhibitors + NSAIDs / Potassium**\n\n" +
          "- **NSAIDs Interaction:** Co-administration may diminish the antihypertensive effect of ACE inhibitors and increase the risk of acute renal functional deterioration.\n" +
          "- **Potassium Interaction:** ACE inhibitors reduce aldosterone secretion. Concomitant potassium-sparing agents or potassium supplements elevate hyperkalemia risks.\n" +
          "- **Clinical Guidance:** Monitor serum potassium and renal function (BUN/Creatinine) periodically."
      } else if (prompt.includes('patient') || prompt.includes('history') || prompt.includes('summary')) {
        answer = "**Patient Clinical Summary**\n\n" +
          "- **Patient:** Jane Doe (Age: 31, Blood Group: O+)\n" +
          "- **Recorded Allergies:** Penicillin, Sulfa\n" +
          "- **Active Diagnoses:** Essential Hypertension (ICD-10 I10), Contact Dermatitis (ICD-10 L23.9)\n" +
          "- **Active Medication:** Lisinopril 10mg PO QD (Refills: 3)"
      } else {
        answer = `Hello Dr. ${user?.name || 'Physician'}! I can help draft structured SOAP notes, check pharmacology drug interactions, or summarize patient histories before an appointment.`
      }
    } else {
      // Patient scope - Personal Clinical Nurse Persona
      const prescriptions = getStoredPrescriptions()
      const appointments = getStoredAppointments()
      const records = getStoredRecords()
      const firstName = (user?.name || 'Jane').split(' ')[0]

      // Natural Greetings
      if (prompt === 'hello' || prompt === 'hi' || prompt === 'hey' || prompt.startsWith('hello') || prompt.startsWith('hi ') || prompt.includes('good morning') || prompt.includes('good afternoon') || prompt.includes('good evening')) {
        const nextAppt = appointments[0]
        if (nextAppt) {
          answer = `Hi ${firstName}! Good to see you. How are you feeling today?\n\nBy the way, you have an upcoming visit with **${nextAppt.doctor_name}** (${nextAppt.doctor_specialty}) coming up soon. Let me know if you have any questions about it or your medications!`
        } else {
          answer = `Hi ${firstName}! Good to see you. I'm here as your personal nurse coordinator. How are you feeling today? Let me know if you'd like to check on appointments, prescriptions, or clinic information.`
        }
      }
      // Conversational inquiries
      else if (prompt.includes('how do you answer so fast') || prompt.includes('so fast') || prompt.includes('fast reply')) {
        answer = `I have your patient chart and clinic records right in front of me so you don't have to wait on hold! I'm here whenever you need a quick answer about your care. What's on your mind?`
      } else if (prompt.includes('how are you') || prompt.includes('how are you doing') || prompt.includes('how r u')) {
        answer = `I'm doing well, thank you for asking, ${firstName}! Just keeping an eye on your care schedule and health records. How are you doing today? Are you feeling alright?`
      } else if (prompt.includes('thank') || prompt.includes('thanks') || prompt.includes('appreciate')) {
        answer = `You're very welcome, ${firstName}! I'm always right here if anything else comes up. Take care of yourself!`
      } else if (prompt.includes('who are you') || prompt.includes('what are you') || prompt.includes('what can you do') || prompt.includes('help me')) {
        answer = `Think of me as your personal clinic nurse here at Medicon! I'm here to:\n\n` +
          `• Keep track of your upcoming doctor appointments and help you prepare\n` +
          `• Explain what your medications are for and when to take them\n` +
          `• Walk you through your past lab results and doctor notes in plain language\n` +
          `• Answer questions about clinic visiting hours and procedures\n\n` +
          `Is there anything specific you'd like to look at together?`
      }
      // Profile & Identity
      else if (prompt.includes('name') || prompt.includes('who am i') || prompt.includes('my account') || prompt.includes('my profile') || prompt.includes('email')) {
        answer = `You are logged in as **${user?.name || 'Jane Doe'}**.\n\n` +
          `• **Email:** ${user?.email || 'patient@medicon.health'}\n` +
          `• **Blood Type:** ${user?.patient?.blood_type || 'O+'}\n` +
          `• **Documented Allergies:** ${user?.patient?.allergies || 'Penicillin, Sulfa'}\n\n` +
          `Everything is up to date in your active chart!`
      }
      // Prescriptions
      else if (prompt.includes('prescription') || prompt.includes('medication') || prompt.includes('drug') || prompt.includes('meds') || prompt.includes('lisinopril')) {
        if (prescriptions.length === 0) {
          answer = `I checked your medical chart, and you don't have any active prescriptions right now. If your doctor recently prescribed something, it will appear here once finalized.`
        } else {
          answer = `Here is what we currently have on file for your medications:\n\n` +
            prescriptions.map((rx, i) => `**${i + 1}. ${rx.medication_name} ${rx.dosage}**\n• ${rx.instructions}\n• Frequency: ${rx.frequency} (${rx.duration})\n• Refills Remaining: **${rx.refills_remaining}**`).join('\n\n') +
            `\n\nBe sure to take them consistently, and let your doctor know if you notice any unusual side effects!`
        }
      }
      // Appointments
      else if (prompt.includes('appointment') || prompt.includes('schedule') || prompt.includes('visit') || prompt.includes('next')) {
        if (appointments.length === 0) {
          answer = `You don't have any upcoming appointments scheduled right now. If you'd like to see a specialist, you can click **'Book Appointment'** right from your dashboard!`
        } else {
          const next = appointments[0]
          answer = `Your next visit is coming up with **${next.doctor_name}** (${next.doctor_specialty}) on **${new Date(next.scheduled_start).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })}**.\n\n` +
            `• **Format:** ${next.type === 'TELEHEALTH' ? 'Encrypted HD Video Call (link will be ready on your dashboard)' : 'In-Person Clinic Visit'}\n` +
            `• **Reason for visit:** ${next.reason}\n\n` +
            `Would you like instructions on how to prepare for this visit?`
        }
      }
      // Records & Vitals
      else if (prompt.includes('record') || prompt.includes('diagnos') || prompt.includes('ehr') || prompt.includes('vital') || prompt.includes('history') || prompt.includes('blood pressure')) {
        if (records.length === 0) {
          answer = `No clinical encounter summaries have been filed yet. After your upcoming consultation, your doctor's diagnostic notes and vitals will be saved here.`
        } else {
          const recent = records[0]
          answer = `Here is the summary from your most recent visit on **${new Date(recent.created_at).toLocaleDateString()}** with **${recent.doctor_name}**:\n\n` +
            `• **Diagnosis:** ${recent.diagnosis}\n` +
            `• **Vitals Recorded:** Blood Pressure ${recent.vital_signs?.blood_pressure || '120/80'}, Heart Rate ${recent.vital_signs?.heart_rate || '72 bpm'}\n` +
            `• **Doctor's Note:** ${recent.clinical_notes}\n\n` +
            `Let me know if there are specific medical terms you'd like me to explain in plain language!`
        }
      }
      // Allergies
      else if (prompt.includes('allergy') || prompt.includes('allergies')) {
        answer = `Your chart clearly lists **${user?.patient?.allergies || 'Penicillin, Sulfa'}** under your allergy alerts. All doctors you consult with are automatically notified so they choose safe alternative medications for you.`
      }
      // Clinic Hours
      else if (prompt.includes('hour') || prompt.includes('open') || prompt.includes('time') || prompt.includes('when')) {
        answer = `Our outpatient clinics are open **Monday through Friday, 8:00 AM to 5:00 PM**. If you have an urgent question outside those hours, telehealth video consultations are available based on your physician's schedule.`
        isCached = true
      }
      // Lab Prep
      else if (prompt.includes('blood test') || prompt.includes('prepare') || prompt.includes('fasting') || prompt.includes('lab')) {
        answer = `For standard fasting blood tests (like lipid panels or fasting glucose), it's best to avoid eating or drinking anything other than plain water for **8 to 12 hours** before your appointment. You can still take your normal morning medications with water unless your doctor said otherwise!`
        isCached = true
      }
      // Symptoms / Emergency Safety
      else if (prompt.includes('symptom') || prompt.includes('pain') || prompt.includes('sick') || prompt.includes('hurt') || prompt.includes('fever')) {
        answer = `⚠️ **Important Nurse Note:** I want to make sure you stay safe! While I can explain your records and medications, I cannot evaluate new symptoms or give a medical diagnosis. If you are having severe pain, chest discomfort, shortness of breath, or feel very sick, please call **911** or contact our urgent triage desk at **+63-2-8521-0020** right away.`
      }
      // Conversational Default
      else {
        answer = `I hear you, ${firstName}! How can I help you today? Whether you want to check your upcoming appointments, go over your medications, or look up clinic info, I'm here for you.`
      }
    }

    return {
      status: 200,
      data: {
        success: true,
        message: answer,
        role: role,
        cached: isCached,
        timestamp: new Date().toISOString(),
      },
    }
  }

  // Fallback 200 OK
  return { status: 200, data: { message: 'Success' } }
}

// Custom Axios Adapter providing instantaneous local mock routing
api.defaults.adapter = async (config) => {
  // If explicitly configured to use live backend API
  if (import.meta.env.VITE_USE_LIVE_API === 'true') {
    return axios.defaults.adapter(config)
  }

  // Serve with mock handler
  const res = handleMockRoute(config)
  return {
    data: res.data,
    status: res.status,
    statusText: 'OK',
    headers: {},
    config,
    request: {},
  }
}

// Response Interceptor: Error Handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const notificationStore = useNotificationStore()
    if (error.response) {
      const status = error.response.status
      if (status === 401) {
        localStorage.removeItem('medicon_auth_token')
        localStorage.removeItem('medicon_user')
        if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
          notificationStore.error('Session expired. Please log in again.')
          window.location.href = '/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
