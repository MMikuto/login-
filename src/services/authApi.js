const apiBase = (process.env.VUE_APP_API_BASE_URL || '/project_login/php_api')
  .replace(/\/+$/, '')

export async function apiRequest(path, options = {}) {
  const headers = { ...options.headers }
  if (options.body && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json'
  }

  const response = await fetch(`${apiBase}/${path}`, {
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
