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
  getStoredPayments,
  saveStoredPayments,
  getTwoFactorState,
  setTwoFactorState,
  generateUniqueRoomCode,
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
      avatar_url: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
      patient: {
        id: 1,
        allergies: 'Penicillin, Sulfa Drugs',
        blood_type: 'O+',
        emergency_contact_name: 'Mark Doe (Spouse)',
        emergency_contact_phone: '+1 (555) 019-9831',
      },
    }
    if (email.includes('admin') || email.includes('eleanor') || email.includes('vance')) {
      user = {
        id: 3,
        name: 'Dr. Eleanor Vance, MD (CMO)',
        email: email || 'admin@medicon.health',
        role: 'admin',
        avatar_url: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
      }
    } else if (email.includes('doctor') || email.includes('jenkins') || email.includes('sarah')) {
      user = {
        id: 2,
        name: 'Dr. Sarah Jenkins, MD, FACC',
        email: email || 'sarah.jenkins@medicon.health',
        role: 'doctor',
        avatar_url: 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&auto=format&fit=crop&q=80',
        doctor: {
          id: 1,
          specialty: 'Cardiology',
          license_number: 'MD-CAR-88210',
          consultation_fee: 1500,
          consultation_fee_cents: 150000,
          rating: 4.96,
          years_of_experience: 14,
        },
      }
    } else if (email.includes('chen') || email.includes('marcus')) {
      user = {
        id: 4,
        name: 'Dr. Marcus Chen, MD, PhD',
        email: email || 'marcus.chen@medicon.health',
        role: 'doctor',
        avatar_url: 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=150&auto=format&fit=crop&q=80',
        doctor: {
          id: 2,
          specialty: 'Neurology',
          license_number: 'MD-NEU-41903',
          consultation_fee: 1200,
          consultation_fee_cents: 120000,
          rating: 4.91,
          years_of_experience: 10,
        },
      }
    }

    const twoFactorState = getTwoFactorState()
    if (twoFactorState.enabled) {
      return {
        status: 200,
        data: {
          success: true,
          two_factor_required: true,
          two_factor_token: 'mock_2fa_token_' + Date.now(),
          message: 'Two-factor authentication required. Enter your 6-digit code or recovery code.',
        },
      }
    }

    return { status: 200, data: { user, token: 'mock_jwt_token_' + Date.now(), two_factor_required: false } }
  }

  if (url === '/auth/2fa/enable' && method === 'post') {
    const recoveryCodes = [
      'A7B2-99F1', '4C3D-88E2', 'K9X1-77M4', 'P2Q9-66R3',
      'W5V8-55T2', 'Z1Y4-44X9', 'N3M7-33L8', 'H6J2-22G5',
    ]
    const secret = 'JBSWY3DPEHPK3PXP'
    const qr_code_uri = `otpauth://totp/Medicon:patient@medicon.health?secret=${secret}&issuer=Medicon%20Healthcare&algorithm=SHA1&digits=6&period=30`
    return {
      status: 200,
      data: {
        success: true,
        data: {
          secret,
          qr_code_uri,
          recovery_codes: recoveryCodes,
        },
      },
    }
  }

  if (url === '/auth/2fa/confirm' && method === 'post') {
    setTwoFactorState({
      enabled: true,
      confirmed_at: new Date().toISOString(),
      secret: 'JBSWY3DPEHPK3PXP',
      recovery_codes: [
        'A7B2-99F1', '4C3D-88E2', 'K9X1-77M4', 'P2Q9-66R3',
        'W5V8-55T2', 'Z1Y4-44X9', 'N3M7-33L8', 'H6J2-22G5',
      ],
    })
    return {
      status: 200,
      data: {
        success: true,
        message: 'Two-factor authentication is now active.',
        data: { two_factor_enabled: true },
      },
    }
  }

  if (url === '/auth/2fa/disable' && method === 'post') {
    setTwoFactorState({ enabled: false, secret: null, recovery_codes: [] })
    return {
      status: 200,
      data: {
        success: true,
        message: 'Two-factor authentication disabled.',
        data: { two_factor_enabled: false },
      },
    }
  }

  if (url === '/auth/2fa/challenge' && method === 'post') {
    const user = {
      id: 1,
      name: 'Jane Doe',
      email: 'patient@medicon.health',
      role: 'patient',
      avatar_url: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
      patient: {
        id: 1,
        allergies: 'Penicillin, Sulfa Drugs',
        blood_type: 'O+',
        emergency_contact_name: 'Mark Doe (Spouse)',
        emergency_contact_phone: '+1 (555) 019-9831',
      },
    }
    return {
      status: 200,
      data: {
        success: true,
        message: 'Two-factor code verified.',
        user,
        token: 'mock_jwt_token_' + Date.now(),
      },
    }
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
        emergency_contact_name: 'Primary Contact',
        emergency_contact_phone: '+1 (555) 019-9999',
      },
    }
    return { status: 201, data: { user, token: 'mock_jwt_token_' + Date.now() } }
  }

  if (url === '/auth/me' && method === 'get') {
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const twoFactorState = getTwoFactorState()
    return { status: 200, data: { user, two_factor_enabled: !!twoFactorState.enabled } }
  }

  if (url === '/auth/logout' && method === 'post') {
    return { status: 200, data: { message: 'Logged out' } }
  }

  // Payments & Checkout Mock Routes
  if (url === '/payments/checkout' && method === 'post') {
    const payments = getStoredPayments()
    const appts = getStoredAppointments()
    const appt = appts.find((a) => a.id === Number(body.appointment_id))

    const methodType = body.payment_method || 'gcash'
    const isCard = methodType === 'card'
    const amountCents = body.amount_cents || (appt?.consultation_fee_cents || 50000)
    const gateway = isCard ? (Math.random() > 0.1 ? 'paymongo' : 'stripe') : 'paymongo'

    const newPayment = {
      id: Date.now(),
      appointment_id: Number(body.appointment_id),
      user_id: 1,
      amount_cents: amountCents,
      amount_pesos: (amountCents / 100).toFixed(2),
      currency: 'PHP',
      gateway,
      payment_method: methodType,
      status: 'paid',
      gateway_payment_id: `pay_${gateway}_${Date.now()}`,
      refund_amount_cents: 0,
      refund_amount_pesos: '0.00',
      refunded_at: null,
      created_at: new Date().toISOString(),
    }

    payments.unshift(newPayment)
    saveStoredPayments(payments)

    if (appt) {
      appt.status = 'CONFIRMED'
      appt.payment_status = 'paid'
      appt.consultation_fee_cents = amountCents
      saveStoredAppointments(appts)
    }

    return {
      status: 200,
      data: {
        success: true,
        message: 'Payment processed successfully.',
        data: {
          payment_id: newPayment.id,
          gateway,
          amount_cents: amountCents,
          amount_pesos: (amountCents / 100).toFixed(2),
          currency: 'PHP',
          status: 'paid',
          checkout_url: `https://pm.link/pay/${newPayment.gateway_payment_id}`,
        },
      },
    }
  }

  if (url.match(/^\/payments\/\d+$/) && method === 'get') {
    const id = Number(url.split('/')[2])
    const payment = getStoredPayments().find((p) => p.id === id) || getStoredPayments()[0]
    return { status: 200, data: { success: true, data: payment } }
  }

  // Universal Full-Text Search Mock Route
  if (url === '/search' && method === 'get') {
    const q = (params.q || '').toLowerCase().trim()
    const type = params.type || 'all'

    const matchedDoctors = defaultDoctors.filter(
      (d) => d.name.toLowerCase().includes(q) || d.specialty.toLowerCase().includes(q) || d.bio.toLowerCase().includes(q)
    )

    const matchedRecords = getStoredRecords().filter(
      (r) => r.diagnosis?.toLowerCase().includes(q) || r.clinical_notes?.toLowerCase().includes(q) || r.doctor_name?.toLowerCase().includes(q)
    )

    const matchedPrescriptions = getStoredPrescriptions().filter(
      (rx) => rx.notes?.toLowerCase().includes(q) || rx.items?.some((i) => i.medication_name.toLowerCase().includes(q))
    )

    return {
      status: 200,
      data: {
        success: true,
        query: q,
        data: {
          doctors: matchedDoctors,
          records: matchedRecords,
          prescriptions: matchedPrescriptions,
        },
      },
    }
  }

  // Data Compliance & Privacy Mock Routes
  if (url === '/compliance/export' && method === 'get') {
    const user = JSON.parse(localStorage.getItem('medicon_user') || '{}')
    return {
      status: 200,
      data: {
        success: true,
        filename: `medicon_health_export_${user.id || 1}_${Date.now()}.json`,
        data: {
          compliance_standard: 'HIPAA / Data Privacy Act (DPA) Complete Health Record Export',
          export_generated_at: new Date().toISOString(),
          patient_profile: user,
          appointments: getStoredAppointments(),
          medical_records: getStoredRecords(),
          prescriptions: getStoredPrescriptions(),
          payments: getStoredPayments(),
          audit_logs: getStoredAuditLogs(),
        },
      },
    }
  }

  if (url === '/compliance/account-deletion' && method === 'post') {
    localStorage.removeItem('medicon_auth_token')
    localStorage.removeItem('medicon_user')
    setTwoFactorState({ enabled: false, secret: null, recovery_codes: [] })

    return {
      status: 200,
      data: {
        success: true,
        message: 'Your account has been permanently anonymized and closed in compliance with HIPAA.',
      },
    }
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
          no_show_rate: 8.4,
        },
        doctor_utilization: [
          { doctor_id: 1, name: 'Dr. Sarah Jenkins, MD, FACC', specialty: 'Cardiology', total_appointments: 48, rating: 4.96 },
          { doctor_id: 2, name: 'Dr. Marcus Chen, MD, PhD', specialty: 'Neurology', total_appointments: 36, rating: 4.91 },
          { doctor_id: 3, name: 'Dr. Elena Rostova, MD', specialty: 'Dermatology', total_appointments: 42, rating: 4.93 },
          { doctor_id: 4, name: 'Dr. James Wilson, MD', specialty: 'General Practice', total_appointments: 64, rating: 4.89 },
          { doctor_id: 5, name: 'Dr. Aisha Patel, MD', specialty: 'Psychiatry', total_appointments: 31, rating: 4.98 },
          { doctor_id: 6, name: 'Dr. Robert Taylor, MD', specialty: 'Orthopedics', total_appointments: 29, rating: 4.92 },
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
          { id: 2, name: 'Dr. Sarah Jenkins, MD, FACC', email: 'sarah.jenkins@medicon.health', role: 'doctor', is_active: true, created_at: '2025-11-20' },
          { id: 3, name: 'Dr. Eleanor Vance, MD (CMO)', email: 'admin@medicon.health', role: 'admin', is_active: true, created_at: '2025-08-01' },
          { id: 4, name: 'Dr. Marcus Chen, MD, PhD', email: 'marcus.chen@medicon.health', role: 'doctor', is_active: true, created_at: '2025-10-05' },
          { id: 5, name: 'Dr. Elena Rostova, MD', email: 'elena.rostova@medicon.health', role: 'doctor', is_active: true, created_at: '2025-12-01' },
          { id: 6, name: 'John Miller', email: 'john.miller@medicon.health', role: 'patient', is_active: true, created_at: '2026-02-10' },
          { id: 7, name: 'Emily Clark', email: 'emily.clark@medicon.health', role: 'patient', is_active: true, created_at: '2026-02-18' },
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
    const appts = getStoredAppointments()
    const appt = appts.find((a) => a.id === id) || {
      id: id,
      doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
      doctor_specialty: 'Cardiology',
      patient_name: 'Jane Doe',
      scheduled_start: new Date().toISOString(),
      type: 'TELEHEALTH',
      status: 'CONFIRMED',
      reason: 'Cardiology Multi-Party Consultation',
      room_code: generateUniqueRoomCode(),
    }
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const role = (user?.role || 'patient').toUpperCase()
    const roomCode = appt.room_code || generateUniqueRoomCode()
    appt.room_code = roomCode

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

  if (url.match(/^\/telehealth\/rooms\/[^/]+\/token$/) && method === 'get') {
    const code = url.split('/')[3]
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const role = (user?.role || 'patient').toUpperCase()
    const appts = getStoredAppointments()
    const matchedAppt = appts.find((a) => a.room_code === code)

    return {
      status: 200,
      data: {
        success: true,
        room_code: code,
        appointment: matchedAppt || {
          id: Date.now(),
          room_code: code,
          reason: 'Direct Clinical Telehealth Consultation',
          patient_name: user?.name || 'Jane Doe',
          doctor_name: 'Dr. Sarah Jenkins, MD, FACC',
          doctor_specialty: 'Cardiology',
          scheduled_start: new Date().toISOString(),
        },
        session: {
          token: `lk_mock_jwt_token_${code}_${Date.now()}`,
          room_name: `medicon_room_${code}`,
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
    const newCode = generateUniqueRoomCode()
    const appts = getStoredAppointments()
    const appt = appts.find((a) => a.id === id)
    if (appt) {
      localStorage.removeItem(`medicon_chat_room_${appt.room_code}`)
      appt.room_code = newCode
      saveStoredAppointments(appts)
    }
    localStorage.removeItem(`medicon_chat_room_${id}`)
    return {
      status: 200,
      data: {
        success: true,
        message: 'Consultation room closed and in-call messages purged.',
        new_room_code: newCode,
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
    const code = generateUniqueRoomCode()
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

  // 7. AI Clinical Assistant (Gemini Flash Model & Clinical Conversational Engine)
  if (url === '/ai/chat' && method === 'post') {
    const prompt = (body.message || '').trim().toLowerCase()
    const screenCtx = body.screen_context || {}
    const screenPath = screenCtx.path || ''
    const screenTitle = screenCtx.title || 'Medicon Workspace'
    const screenDetails = screenCtx.details || {}
    const user = JSON.parse(localStorage.getItem('medicon_user') || 'null')
    const role = user?.role || (user ? 'patient' : 'guest')
    let answer = ''
    let isCached = false

    // Direct On-Screen Contextual Questions ("Where am I?", "What is this page?", "What should I do here?")
    if (
      prompt.includes('where am i') ||
      prompt.includes('what page') ||
      prompt.includes('what is this screen') ||
      prompt.includes('what is this page') ||
      prompt.includes('this page') ||
      prompt.includes('what to do here') ||
      prompt.includes('help here') ||
      prompt.includes('explain this screen') ||
      prompt.includes('what am i looking at')
    ) {
      if (screenPath.includes('checkout')) {
        answer = `**You are on the Secure Consultation Payment Page**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Transaction Purpose:** Authorizing consultation fee for your medical specialist.\n` +
          `• **Supported Payment Methods:** Credit / Debit Cards, GCash, Maya, GrabPay (in Philippine Pesos ₱).\n` +
          `• **Security:** 256-bit SSL encrypted tunnel with PayMongo & Stripe.\n` +
          `• **Refund Policy:** 100% refund for cancellations > 24 hours prior to appointment; 50% for 12–24 hours.`
      } else if (screenPath.includes('telehealth/room')) {
        const roomCode = screenDetails.roomCode || 'Active'
        answer = `**You are inside a Live Telehealth Consultation Room (#${roomCode})**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Video Session:** Private, peer-to-peer encrypted WebRTC video conference.\n` +
          `• **Controls:** You can toggle your camera, mute your microphone, or send in-room chat messages.\n` +
          `• **Clinical Notes & Rx:** After the call concludes, your doctor will automatically save your diagnostic summary and electronic prescriptions to your portal.`
      } else if (screenPath === '/patient/appointments') {
        answer = `**You are on your Medical Appointments Schedule**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Capabilities:** View all upcoming and historical doctor appointments.\n` +
          `• **Telehealth Encounters:** Click the green **'Join Room'** button when your session is starting.\n` +
          `• **Reschedule & Cancel:** Available up to your clinic's advance notice window.`
      } else if (screenPath === '/patient/prescriptions') {
        answer = `**You are viewing your Active Prescriptions**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Information Displayed:** Prescribed medications, exact daily dosage instructions, administration frequency, and remaining authorized refills.`
      } else if (screenPath === '/patient/records') {
        answer = `**You are viewing your Electronic Health Records (EHR)**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Information Displayed:** Past clinical visit summaries, attending physicians, diagnosis descriptions, and vital signs telemetry (Blood Pressure, Heart Rate).`
      } else if (screenPath === '/profile') {
        answer = `**You are on your Account & Security Settings**\n\n` +
          `• **Screen In Focus:** ${screenTitle}\n` +
          `• **Security:** Enable or configure Two-Factor Authentication (2FA TOTP).\n` +
          `• **Compliance & Rights:** Download a complete JSON health data export or exercise your Right to be Forgotten.`
      } else {
        answer = `**You are viewing:** ${screenTitle} (${screenPath || '/'})\n\n` +
          `I am actively tracking your screen context. You can ask me questions about any controls, forms, or clinical data on this page!`
      }
    }
    // Natural Greetings (Warm, Conversational & Context-Aware)
    else if (
      prompt === 'hello' ||
      prompt === 'hi' ||
      prompt === 'hey' ||
      prompt === 'hello there' ||
      prompt.startsWith('hello') ||
      prompt.startsWith('hi ') ||
      prompt.startsWith('hey ') ||
      prompt.includes('good morning') ||
      prompt.includes('good afternoon') ||
      prompt.includes('good evening')
    ) {
      if (!user) {
        answer = `Hello there! Welcome to Medicon Medical Center. I can see you are currently on **${screenTitle}**.\n\nHow can I help you today? Whether you're looking for a doctor, have questions about clinic hours, or want to know how telehealth works, I'm happy to chat!`
      } else if (role === 'doctor') {
        answer = `Hello Dr. ${user?.name || 'Physician'}! I am tracking your active workstation (**${screenTitle}**). Ready to assist with drafting SOAP notes, reviewing patient histories, or checking pharmacology references.`
      } else if (role === 'admin') {
        answer = `Hello ${user?.name || 'Admin'}! I am monitoring **${screenTitle}**. Ready to assist you with hospital operations, ML attendance analytics, or HIPAA audit policies.`
      } else {
        const appointments = getStoredAppointments()
        const firstName = (user?.name || 'Jane').split(' ')[0]
        answer = `Hi ${firstName}! I am tracking your active screen (**${screenTitle}**). How are you feeling today? Let me know if you have questions about what is on your screen or your care.`
      }
    }
    // Checkout-specific questions
    else if (screenPath.includes('checkout') && (prompt.includes('method') || prompt.includes('pay') || prompt.includes('gcash') || prompt.includes('maya') || prompt.includes('card') || prompt.includes('promo') || prompt.includes('discount'))) {
      answer = `**Payment Information on this Checkout Page:**\n\n` +
        `• **Payment Options:** You can choose between Credit/Debit Card or local Philippine E-Wallets (**GCash**, **Maya**, **GrabPay**).\n` +
        `• **Promo Codes:** Enter \`MEDICON10\` or \`SAVE10\` in the promo code box to receive 10% off.\n` +
        `• **Philippine Peso:** All amounts are processed in PHP centavos through PayMongo/Stripe.\n` +
        `• **Instant Confirmation:** Once authorized, your consultation slot is automatically locked and confirmed.`
    }
    // Conversational Small Talk
    else if (prompt.includes('how are you') || prompt.includes('how r u') || prompt.includes('how are u') || prompt.includes("how's it going") || prompt.includes('hows it going')) {
      answer = `I'm doing great, thank you for asking! I'm currently monitoring **${screenTitle}** and ready 24/7 to assist you with healthcare navigation, doctor recommendations, and clinical questions. How can I help?`
    }
    else if (prompt.includes('thank') || prompt.includes('thanks') || prompt.includes('appreciate')) {
      answer = "You're very welcome! If you have any other questions about your screen or services, feel free to ask anytime. Take care!"
    }
    else if (prompt.includes('who are you') || prompt.includes('what are you') || prompt.includes('what can you do') || prompt.includes('help me') || prompt.includes('what is your name')) {
      if (!user) {
        answer = `I'm your Medicon Virtual Health Assistant! I am context-aware and know you are currently on **${screenTitle}**.\n\nI can help you with:\n` +
          "• Finding board-certified doctors across our specialties\n" +
          "• Explaining how our HD Telehealth consultations work\n" +
          "• Providing clinic locations, outpatient hours, and 24/7 hotlines\n" +
          "• Guiding you through booking in-person or virtual appointments\n\n" +
          "What can I help you explore today?"
      } else {
        answer = `Think of me as your personal care coordinator here at Medicon! I am currently tracking **${screenTitle}** and can:\n\n` +
          "• Explain controls and data on your current screen\n" +
          "• Keep track of your upcoming appointments and help you prepare\n" +
          "• Explain your prescribed medications and instructions\n" +
          "• Review your past diagnoses, vitals, and doctor notes in plain language\n" +
          "• Answer questions about clinic visiting hours and procedures"
      }
    }
    else if (prompt.includes('bye') || prompt.includes('goodbye') || prompt.includes('see you') || prompt.includes('cya')) {
      answer = "Goodbye! Wishing you good health. Don't hesitate to reach out if you need anything in the future!"
    }
    // Symptoms / Empathetic Health Guidance
    else if (prompt.includes('headache') || prompt.includes('migraine') || prompt.includes('head hurt') || prompt.includes('dizzy') || prompt.includes('vertigo')) {
      answer = "I'm sorry you're dealing with a headache! Mild headaches often improve with hydration, resting in a quiet dim room, and gentle neck relaxation. However, if your headache is sudden, unusually severe, or accompanied by blurred vision or numbness, please seek immediate medical attention.\n\nOur **Neurology specialist, Dr. Marcus Chen, MD, PhD** (₱1,200.00), is available for chronic migraine and neurological evaluations if symptoms persist."
    }
    else if (prompt.includes('chest pain') || prompt.includes('shortness of breath') || prompt.includes('hard to breathe') || prompt.includes('heart pain') || prompt.includes('palpitation')) {
      answer = "⚠️ **Immediate Clinical Safety Alert:** If you or someone nearby is experiencing acute chest tightness, difficulty breathing, or pain radiating to the left arm or jaw, please call **911** or contact our 24/7 emergency hotline at **+63-2-8521-0020** immediately.\n\nFor non-emergency preventative check-ups, **Dr. Sarah Jenkins, MD, FACC** (₱1,500.00) specializes in cardiovascular assessments and rhythm monitoring."
    }
    else if (prompt.includes('skin') || prompt.includes('rash') || prompt.includes('itch') || prompt.includes('eczema') || prompt.includes('acne') || prompt.includes('mole') || prompt.includes('spot')) {
      answer = "Skin rashes and irritations can stem from allergies, contact dermatitis, or eczema. Keep the area clean and avoid harsh soaps or scratching.\n\nOur Dermatology specialist, **Dr. Elena Rostova, MD** (₱800.00), offers both in-person visits and rapid HD Telehealth photo consultations to assess skin concerns."
    }
    else if (prompt.includes('stomach') || prompt.includes('nausea') || prompt.includes('vomit') || prompt.includes('diarrhea') || prompt.includes('acid reflux') || prompt.includes('belly')) {
      answer = "For mild stomach upset, staying hydrated with small sips of water or electrolyte solution, and eating bland foods (like crackers or rice) can help soothe your digestive tract. If pain is severe or fever develops, seeing a doctor is recommended.\n\nOur **General Practice** team (Dr. James Wilson, MD, ₱500.00) is available for same-day walk-ins and virtual visits."
    }
    else if (prompt.includes('fever') || prompt.includes('cough') || prompt.includes('cold') || prompt.includes('flu') || prompt.includes('sore throat')) {
      answer = "For cold and flu symptoms, plenty of rest, warm fluids, and monitoring your temperature are key. If your fever exceeds 38.5°C (101.3°F) for more than 3 days, consulting a physician is advisable.\n\nOur Primary Care doctors (₱500.00) can evaluate your symptoms and prescribe appropriate treatments."
    }
    else if (prompt.includes('anxiety') || prompt.includes('depress') || prompt.includes('stress') || prompt.includes('mental health') || prompt.includes('insomnia') || prompt.includes('sleep')) {
      answer = "Mental well-being is just as essential as physical health. Chronic stress, anxiety, or sleep disturbances can significantly impact daily life.\n\nOur Psychiatry specialist, **Dr. Aisha Patel, MD** (₱1,800.00 fee), provides supportive psychotherapy, stress reduction strategies, and medication management via private telehealth."
    }
    else if (prompt.includes('knee') || prompt.includes('joint') || prompt.includes('back pain') || prompt.includes('bone') || prompt.includes('sprain') || prompt.includes('shoulder')) {
      answer = "For musculoskeletal aches or joint sprains, resting the joint, applying a cold pack for 15-20 minutes, and gentle elevation can provide initial relief.\n\nOur Orthopedic surgeon, **Dr. Robert Taylor, MD** (₱1,250.00 fee), specializes in joint rehabilitation, sports injuries, and spine health."
    }
    // Doctors & Specialists Recommendations
    else if (prompt.includes('cardio') || prompt.includes('heart') || prompt.includes('blood pressure') || prompt.includes('hypertension')) {
      answer = "For cardiovascular health and hypertension, **Dr. Sarah Jenkins, MD, FACC** is our Director of Cardiology. She is a Harvard Medical School alumna with 14 years of experience (₱1,500.00 consultation fee).\n\nShe provides comprehensive cardiac screenings, vital telemetry, and ECG evaluations."
    }
    else if (prompt.includes('neuro') || prompt.includes('brain') || prompt.includes('stroke') || prompt.includes('seizure') || prompt.includes('nerve')) {
      answer = "For brain and nervous system care, **Dr. Marcus Chen, MD, PhD** is our Director of Neurology (10 years experience, ₱1,200.00 consultation fee). He specializes in migraine treatment, stroke prevention, and cognitive health."
    }
    else if (prompt.includes('derma') || prompt.includes('skin doctor')) {
      answer = "Our clinical dermatologist is **Dr. Elena Rostova, MD** (8 years experience, ₱800.00 consultation fee). She specializes in teledermatology, eczema protocols, and early skin lesion detection."
    }
    else if (prompt.includes('doctor') || prompt.includes('specialist') || prompt.includes('fee') || prompt.includes('cost') || prompt.includes('price') || prompt.includes('physician')) {
      answer = "**Medicon Board-Certified Specialists (Philippine Rates):**\n\n" +
        "• **General Practice:** Dr. James Wilson, MD (₱500.00)\n" +
        "• **Dermatology:** Dr. Elena Rostova, MD (₱800.00)\n" +
        "• **Neurology:** Dr. Marcus Chen, MD, PhD (₱1,200.00)\n" +
        "• **Orthopedics:** Dr. Robert Taylor, MD (₱1,250.00)\n" +
        "• **Cardiology:** Dr. Sarah Jenkins, MD, FACC (₱1,500.00)\n" +
        "• **Psychiatry:** Dr. Aisha Patel, MD (₱1,800.00)\n\n" +
        "You can browse full physician profiles on our homepage or book a consultation!"
    }
    // Telehealth & Video Consultations
    else if (prompt.includes('telehealth') || prompt.includes('video') || prompt.includes('virtual') || prompt.includes('room') || prompt.includes('green room') || prompt.includes('code')) {
      answer = "**Medicon Telehealth Video Consultations:**\n\n" +
        "• **Direct in Browser:** Uses high-definition encrypted WebRTC. No software downloads required!\n" +
        "• **Pre-Join Green Room:** Test your camera, microphone, and view who is already in the room before joining.\n" +
        "• **Unique Room Codes:** Each consultation has a private 3-part code (e.g. \`k9x-yqp2-481\`).\n" +
        "• **Privacy Guaranteed:** In-room chat and ephemeral media tokens are purged when the room closes.\n\n" +
        "If you have a room code, enter it directly in the Telehealth bar on our homepage to join!"
    }
    // How to Book
    else if (prompt.includes('how to book') || prompt.includes('book an appointment') || prompt.includes('how do i book') || prompt.includes('booking') || (prompt.includes('book') && prompt.includes('visit'))) {
      answer = "**How to Book a Consultation:**\n\n" +
        "1. **Select a Specialist:** Browse by specialty (Cardiology, Neurology, Dermatology, Primary Care, etc.).\n" +
        "2. **Choose Format:** Select an **In-Person Clinic Visit** or an online **HD Telehealth Video Call**.\n" +
        "3. **Pick a Time:** Choose a date and time slot that fits your schedule.\n" +
        "4. **Confirm & Checkout:** Choose payment via Card, GCash, Maya, or GrabPay on our checkout page.\n\n" +
        "Ready to book? You can click 'Schedule Appointment' on the homepage or sign in to your portal."
    }
    // Clinic Hours & Hotlines
    else if (prompt.includes('hour') || prompt.includes('open') || prompt.includes('time') || prompt.includes('location') || prompt.includes('address') || prompt.includes('phone') || prompt.includes('hotline') || prompt.includes('contact')) {
      answer = "**Medicon Locations & Hours:**\n\n" +
        "• **Outpatient Clinics:** Monday – Friday, 8:00 AM – 5:00 PM\n" +
        "• **Quezon City Hospital:** 279 E. Rodriguez Sr. Ave (+63-2-8723-0101)\n" +
        "• **Global City Hospital:** 32nd St cor. 5th Ave, BGC (+63-2-8789-7700)\n" +
        "• **24/7 Emergency Triage:** +63-2-8521-0020\n" +
        "• **Telehealth Video Consults:** Available 24/7 based on doctor schedule."
    }
    // Auth & Portals
    else if (prompt.includes('login') || prompt.includes('sign in') || prompt.includes('account') || prompt.includes('register') || prompt.includes('signup') || prompt.includes('portal')) {
      answer = "**Medicon Portal Access:**\n\n" +
        "Sign in to your portal to manage appointments, view diagnostic summaries, and access prescriptions:\n\n" +
        "• **Sign In:** [/login](/login)\n" +
        "• **Create Account:** [/register](/register) (Takes under 1 minute)\n\n" +
        "You can also use the 1-click Demo accounts on the sign-in page to test any role instantly!"
    }
    // Medical Records / Prescriptions (Authenticated vs Guest)
    else if (prompt.includes('prescription') || prompt.includes('medication') || prompt.includes('drug') || prompt.includes('meds')) {
      if (!user) {
        answer = "To view personal prescriptions and refills, please [Sign In to your Patient Portal](/login). Your doctor's active medication orders are safely encrypted under your account!"
      } else {
        const prescriptions = getStoredPrescriptions()
        if (prescriptions.length === 0) {
          answer = "I checked your medical chart, and you don't have any active prescriptions right now."
        } else {
          answer = "Here are your active prescriptions on file:\n\n" +
            prescriptions.map((rx, i) => `**${i + 1}. ${rx.medication_name} ${rx.dosage}**\n• ${rx.instructions}\n• Frequency: ${rx.frequency} (${rx.duration})\n• Refills: **${rx.refills_remaining}**`).join('\n\n')
        }
      }
    }
    else if (prompt.includes('record') || prompt.includes('diagnos') || prompt.includes('ehr') || prompt.includes('vital') || prompt.includes('history')) {
      if (!user) {
        answer = "To view your medical chart, diagnostic summaries, and vitals history, please [Sign In to your Patient Portal](/login)."
      } else {
        const records = getStoredRecords()
        if (records.length === 0) {
          answer = "No clinical encounter summaries have been filed yet. Your notes will appear here after your upcoming consultation."
        } else {
          const recent = records[0]
          answer = `Here is the summary from your most recent visit on **${new Date(recent.created_at).toLocaleDateString()}** with **${recent.doctor_name}**:\n\n` +
            `• **Diagnosis:** ${recent.diagnosis}\n` +
            `• **Vitals:** Blood Pressure ${recent.vital_signs?.blood_pressure || '120/80'}, HR ${recent.vital_signs?.heart_rate || '72 bpm'}\n` +
            `• **Doctor's Note:** ${recent.clinical_notes}`
        }
      }
    }
    // Specific Doctor Clinical Copilot Queries
    else if (role === 'doctor' && (prompt.includes('soap') || prompt.includes('draft') || prompt.includes('note'))) {
      answer = "**Draft SOAP Clinical Encounter Note**\n\n" +
        "**S (Subjective):** Patient presents for routine cardiology follow-up. Reports strict adherence to Lisinopril 10mg. Denies chest pain or palpitations.\n" +
        "**O (Objective):** BP 124/80 mmHg, HR 68 bpm regular, SpO2 99%. S1/S2 present, no murmurs. Lungs clear to auscultation bilaterally.\n" +
        "**A (Assessment):** Primary Essential Hypertension (ICD-10 I10) - well-controlled under current monotherapy.\n" +
        "**P (Plan):** Maintain Lisinopril 10mg daily. Routine comprehensive metabolic panel in 6 months."
    }
    else if (role === 'doctor' && (prompt.includes('interaction') || prompt.includes('nsaid') || prompt.includes('lisinopril'))) {
      answer = "**Pharmacology Reference: ACE Inhibitors + NSAIDs**\n\n" +
        "- **NSAIDs Interaction:** Co-administration may diminish the antihypertensive effect of ACE inhibitors and elevate acute renal impairment risk.\n" +
        "- **Clinical Guidance:** Monitor serum potassium and renal function (BUN/Creatinine) periodically."
    }
    // Specific Admin Operations Queries
    else if (role === 'admin' && (prompt.includes('risk') || prompt.includes('attendance') || prompt.includes('no-show') || prompt.includes('ml'))) {
      answer = "**Attendance Risk Stratification (ML Model)**\n\n" +
        "- **Function:** Predicts the probability of patient missed visits based on lead time and prior attendance history.\n" +
        "- **Tiers:** Low (<35%), Moderate (35%-64%), and High (≥65%).\n" +
        "- **Action:** High-risk appointments appear in the Active Triage Queue for confirmation reminders."
    }
    // Conversational Fallback
    else {
      if (!user) {
        answer = `I am tracking your active screen (**${screenTitle}**). You can ask me about finding a doctor, medical specialties, clinic visiting hours, or how to schedule a visit. How can I assist you?`
      } else {
        const firstName = (user?.name || 'Jane').split(' ')[0]
        answer = `I hear you, ${firstName}! I am tracking **${screenTitle}**. How can I help you today? Whether you want to check what is on your screen, review upcoming appointments, or go over medications, I'm here for you.`
      }
    }
    isCached = true

    return {
      status: 200,
      data: {
        success: true,
        message: answer,
        role: role,
        cached: isCached,
        screen_context: screenCtx,
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
