import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Layouts
import AppLayout from '@/layouts/AppLayout.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'

// Public Landing View (Doc.Wise Design)
import LandingView from '@/views/public/LandingView.vue'

// Auth Views
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'

// Patient Views
import PatientDashboardView from '@/views/patient/PatientDashboardView.vue'
import DoctorDirectoryView from '@/views/patient/DoctorDirectoryView.vue'
import PatientAppointmentsView from '@/views/patient/PatientAppointmentsView.vue'
import PatientRecordsView from '@/views/patient/PatientRecordsView.vue'
import PatientPrescriptionsView from '@/views/patient/PatientPrescriptionsView.vue'
import CheckoutView from '@/views/patient/CheckoutView.vue'

// Doctor Views
import DoctorDashboardView from '@/views/doctor/DoctorDashboardView.vue'
import DoctorScheduleView from '@/views/doctor/DoctorScheduleView.vue'
import DoctorAppointmentsView from '@/views/doctor/DoctorAppointmentsView.vue'
import DoctorPatientsView from '@/views/doctor/DoctorPatientsView.vue'

// Admin Views
import AdminDashboardView from '@/views/admin/AdminDashboardView.vue'
import AdminAppointmentsView from '@/views/admin/AdminAppointmentsView.vue'
import AdminUsersView from '@/views/admin/AdminUsersView.vue'
import AdminAuditLogsView from '@/views/admin/AdminAuditLogsView.vue'

// Common Views
import ProfileView from '@/views/common/ProfileView.vue'
import ForbiddenView from '@/views/common/ForbiddenView.vue'
import NotFoundView from '@/views/common/NotFoundView.vue'
import TelehealthRoomView from '@/views/telehealth/TelehealthRoomView.vue'

const routes = [
  // Public Landing Page
  {
    path: '/',
    name: 'landing',
    component: LandingView,
  },
  {
    path: '/',
    component: AuthLayout,
    children: [
      { path: 'login', name: 'login', component: LoginView, meta: { guestOnly: true } },
      { path: 'register', name: 'register', component: RegisterView, meta: { guestOnly: true } },
    ],
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      // Common Authenticated
      { path: 'profile', name: 'profile', component: ProfileView },
      { path: 'forbidden', name: 'forbidden', component: ForbiddenView },

      // Patient Portal
      {
        path: 'patient/dashboard',
        name: 'patient-dashboard',
        component: PatientDashboardView,
        meta: { roles: ['patient'] },
      },
      {
        path: 'patient/doctors',
        name: 'patient-doctors',
        component: DoctorDirectoryView,
        meta: { roles: ['patient'] },
      },
      {
        path: 'patient/appointments',
        name: 'patient-appointments',
        component: PatientAppointmentsView,
        meta: { roles: ['patient'] },
      },
      {
        path: 'patient/records',
        name: 'patient-records',
        component: PatientRecordsView,
        meta: { roles: ['patient'] },
      },
      {
        path: 'patient/prescriptions',
        name: 'patient-prescriptions',
        component: PatientPrescriptionsView,
        meta: { roles: ['patient'] },
      },
      {
        path: 'patient/checkout/:appointmentId',
        name: 'patient-checkout',
        component: CheckoutView,
        meta: { roles: ['patient', 'doctor', 'admin'] },
      },

      // Doctor Portal
      {
        path: 'doctor/dashboard',
        name: 'doctor-dashboard',
        component: DoctorDashboardView,
        meta: { roles: ['doctor'] },
      },
      {
        path: 'doctor/schedule',
        name: 'doctor-schedule',
        component: DoctorScheduleView,
        meta: { roles: ['doctor'] },
      },
      {
        path: 'doctor/appointments',
        name: 'doctor-appointments',
        component: DoctorAppointmentsView,
        meta: { roles: ['doctor'] },
      },
      {
        path: 'doctor/patients',
        name: 'doctor-patients',
        component: DoctorPatientsView,
        meta: { roles: ['doctor'] },
      },

      // Admin Portal
      {
        path: 'admin/dashboard',
        name: 'admin-dashboard',
        component: AdminDashboardView,
        meta: { roles: ['admin'] },
      },
      {
        path: 'admin/appointments',
        name: 'admin-appointments',
        component: AdminAppointmentsView,
        meta: { roles: ['admin'] },
      },
      {
        path: 'admin/users',
        name: 'admin-users',
        component: AdminUsersView,
        meta: { roles: ['admin'] },
      },
      {
        path: 'admin/audit-logs',
        name: 'admin-audit-logs',
        component: AdminAuditLogsView,
        meta: { roles: ['admin'] },
      },
    ],
  },
  // Telehealth Multi-Party Consultation Room (Dedicated full screen)
  {
    path: '/telehealth/room/:id',
    name: 'telehealth-room',
    component: TelehealthRoomView,
    meta: { requiresAuth: true },
  },
  // 404 Catch-All
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFoundView,
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

// Navigation Guard: Authentication & RBAC Role Enforcement
router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guestOnly && auth.isAuthenticated) {
    if (auth.isAdmin) return next({ name: 'admin-dashboard' })
    if (auth.isDoctor) return next({ name: 'doctor-dashboard' })
    return next({ name: 'patient-dashboard' })
  }

  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      return next({ name: 'login', query: { redirect: to.fullPath } })
    }

    if (to.meta.roles && !to.meta.roles.includes(auth.role)) {
      // Admins are allowed everywhere, otherwise forbidden
      if (!auth.isAdmin) {
        return next({ name: 'forbidden' })
      }
    }
  }

  next()
})

export default router
