const apiBase = (process.env.VUE_APP_API_BASE_URL || '/project_login/php_api')
  .replace(/\/+$/, '')

export function apiUrl(path) {
  return `${apiBase}/${path}`
}

export async function apiRequest(path, options = {}) {
  const headers = { ...options.headers }
  const isFormData = typeof FormData !== 'undefined' && options.body instanceof FormData
  if (options.body && !isFormData && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(apiUrl(path), {
    credentials: 'include',
    ...options,
    headers
  })

  let data
  try {
    data = await response.json()
  } catch (error) {
    throw new Error('เซิร์ฟเวอร์ส่งข้อมูลกลับมาไม่ถูกต้อง')
  }

  if (!response.ok) {
    throw new Error(data.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ')
  }

  return data
}
