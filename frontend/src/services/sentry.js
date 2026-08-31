/**
 * Medicon Frontend Error Monitoring & HIPAA PII Sanitizer
 */

const SENSITIVE_PATTERNS = [
  /password/i,
  /token/i,
  /secret/i,
  /card/i,
  /cvv/i,
  /clinical/i,
  /diagnos/i,
  /vital/i,
  /prescri/i,
  /allerg/i,
  /symptom/i,
]

export function sanitizeErrorContext(data) {
  if (!data || typeof data !== 'object') return data

  const sanitized = Array.isArray(data) ? [] : {}

  for (const [key, value] of Object.entries(data)) {
    const isSensitive = SENSITIVE_PATTERNS.some((pattern) => pattern.test(key))

    if (isSensitive) {
      sanitized[key] = '[REDACTED_HIPAA_PII]'
    } else if (typeof value === 'object' && value !== null) {
      sanitized[key] = sanitizeErrorContext(value)
    } else {
      sanitized[key] = value
    }
  }

  return sanitized
}

export function initErrorMonitoring(app, router) {
  const dsn = import.meta.env.VITE_SENTRY_DSN

  if (!dsn || import.meta.env.DEV) {
    // Development local logger with HIPAA sanitization
    app.config.errorHandler = (err, vm, info) => {
      const sanitizedContext = sanitizeErrorContext({
        info,
        route: router?.currentRoute?.value?.fullPath,
        component: vm?.$options?.name || 'AnonymousComponent',
      })
      console.warn('[Medicon Error Monitor - Dev Logger]', err?.message, sanitizedContext)
    }
    return
  }

  // Production Sentry handler
  app.config.errorHandler = (err, vm, info) => {
    const role = localStorage.getItem('medicon_user_role') || 'guest'
    const payload = sanitizeErrorContext({
      message: err?.message,
      stack: err?.stack,
      info,
      user_role: role,
      route: router?.currentRoute?.value?.fullPath,
      timestamp: new Date().toISOString(),
    })

    console.error('[Medicon Sentry Production Report]', payload)
  }
}
