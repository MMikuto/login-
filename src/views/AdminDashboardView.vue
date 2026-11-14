<template>
  <main class="admin-page">
    <div class="admin-shell">
      <header class="topbar">
        <router-link class="brand" :to="{ name: 'admin' }">
          <span class="brand-icon" aria-hidden="true">LF</span>
          <span>LeaveFlow Admin</span>
        </router-link>
        <button class="logout" type="button" :disabled="logoutLoading" @click="logout">
          {{ logoutLoading ? 'กำลังออก...' : 'ออกจากระบบ' }}
        </button>
      </header>

      <nav class="main-nav" aria-label="เมนูผู้ดูแล">
        <router-link class="nav-link active" :to="{ name: 'admin' }">คำขออนุมัติ</router-link>
      </nav>

      <section class="page-heading">
        <div>
          <p class="eyebrow">ADMINISTRATION</p>
          <h1>จัดการคำขอลา</h1>
          <p>ตรวจสอบและพิจารณาคำขอจากผู้ใช้งาน</p>
        </div>
        <div class="pending-count"><strong>{{ pendingCount }}</strong><span>รายการรออนุมัติ</span></div>
      </section>

      <p v-if="error" class="message error" role="alert">{{ error }}</p>
      <p v-if="notice" class="message success" role="status">{{ notice }}</p>

      <section class="request-panel" aria-labelledby="requests-title">
        <div class="panel-heading">
          <h2 id="requests-title">คำขอลาทั้งหมด</h2>
          <button class="refresh" type="button" :disabled="loading" @click="loadRequests">
            รีเฟรช
          </button>
        </div>

        <p v-if="loading && requests.length === 0" class="empty-state">กำลังโหลดคำขอ...</p>
        <p v-else-if="requests.length === 0" class="empty-state">ยังไม่มีคำขอลา</p>
        <div v-else class="request-list">
          <article v-for="request in requests" :key="request.id" class="request-row">
            <div class="request-main">
              <div class="request-title">
                <h3>{{ request.employee_name }}</h3>
                <span class="status" :class="statusClass(request.status)">{{ statusLabel(request.status) }}</span>
              </div>
              <p class="request-meta">
                {{ request.department }} <span>·</span> {{ request.leave_type }} <span>·</span>
                {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
                <strong>{{ request.total_days }} วัน</strong>
              </p>
              <p class="reason">{{ request.reason }}</p>
              <p v-if="request.username" class="account">บัญชี: {{ request.username }}</p>
              <a
                v-if="request.attachment_name"
                class="attachment-link"
                :href="attachmentUrl(request.id)"
                target="_blank"
                rel="noopener"
              >เปิดเอกสารแนบ: {{ request.attachment_name }}</a>
            </div>
            <div v-if="request.status === 'pending'" class="actions">
              <button
                class="approve"
                type="button"
                :disabled="busyId === request.id"
                @click="review(request, 'approved')"
              >อนุมัติ</button>
              <button
                class="reject"
                type="button"
                :disabled="busyId === request.id"
                @click="review(request, 'rejected')"
              >ไม่อนุมัติ</button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiRequest, apiUrl } from '../services/authApi'

const router = useRouter()
const requests = ref([])
const loading = ref(false)
const logoutLoading = ref(false)
const busyId = ref(null)
const error = ref('')
const notice = ref('')
const pendingCount = computed(() => requests.value.filter((request) => request.status === 'pending').length)

async function loadRequests() {
  loading.value = true
  error.value = ''
  try {
    const data = await apiRequest('admin_leave.php')
    requests.value = data.requests
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

async function review(request, status) {
  busyId.value = request.id
  error.value = ''
  notice.value = ''
  try {
    await apiRequest('admin_leave.php', {
      method: 'PATCH',
      body: JSON.stringify({ id: request.id, status })
    })
    request.status = status
    notice.value = `บันทึกผล: ${status}`
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    busyId.value = null
  }
}

function statusClass(status) {
  if (status === 'approved') return 'approved'
  if (status === 'rejected') return 'rejected'
  return 'pending'
}

function statusLabel(status) {
  if (status === 'approved') return 'อนุมัติแล้ว'
  if (status === 'rejected') return 'ไม่อนุมัติ'
  return 'รออนุมัติ'
}

function formatDate(value) {
  return new Date(`${value}T00:00:00`).toLocaleDateString('th-TH', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

function attachmentUrl(requestId) {
  return apiUrl(`admin_attachment.php?id=${encodeURIComponent(requestId)}`)
}

async function logout() {
  logoutLoading.value = true
  try {
    await apiRequest('auth.php', { method: 'DELETE' })
    await router.replace({ name: 'login' })
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    logoutLoading.value = false
  }
}

onMounted(loadRequests)
</script>

<style scoped>
.admin-page { min-height: 100vh; box-sizing: border-box; padding: 0 12px 24px; background: #08070d; color: #f8f6fc; }
.admin-shell { width: min(100%, 1120px); min-height: 100vh; margin: 0 auto; box-sizing: border-box; border: 1px solid #30253a; border-top: 0; border-radius: 0 0 18px 18px; background: #0c0a12; padding: 0 16px 24px; }
.topbar { min-height: 68px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #30253a; }
.brand { display: inline-flex; align-items: center; gap: 9px; color: #f8f6fc; font-size: 15px; font-weight: 700; text-decoration: none; }
.brand-icon { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #9229e8; color: white; font-size: 12px; }
.logout, .refresh { border: 0; background: transparent; color: #d385ff; cursor: pointer; font: inherit; font-size: 13px; }
.logout:disabled, button:disabled { cursor: wait; opacity: .6; }
.main-nav { display: flex; min-height: 58px; align-items: center; border-bottom: 1px solid #211927; }
.nav-link { color: #d385ff; font-size: 13px; text-decoration: none; }
.page-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 0 18px; }
.eyebrow { margin: 0 0 6px; color: #d385ff; font-size: 11px; font-weight: 700; }
h1 { margin: 0; font-size: 22px; }
.page-heading p:last-child { margin: 8px 0 0; color: #b9a7ca; font-size: 13px; }
.pending-count { display: grid; gap: 3px; justify-items: end; border-radius: 10px; background: #191420; padding: 10px 14px; }
.pending-count strong { color: #ff9a45; font-size: 22px; }
.pending-count span { color: #b9a7ca; font-size: 11px; }
.request-panel { border-radius: 12px; background: #191420; padding: 16px; }
.panel-heading { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; }
.panel-heading h2 { margin: 0; font-size: 15px; }
.request-row { display: flex; justify-content: space-between; gap: 20px; border-top: 1px solid #34283e; padding: 16px 0; }
.request-main { min-width: 0; }
.request-title { display: flex; align-items: center; gap: 10px; }
.request-title h3 { margin: 0; font-size: 14px; }
.status { border-radius: 999px; padding: 4px 9px; font-size: 11px; white-space: nowrap; }
.pending { background: #3a211d; color: #ff9a45; }
.approved { background: #143427; color: #37df92; }
.rejected { background: #3a1823; color: #ff6f91; }
.request-meta, .reason, .account, .attachment { margin: 8px 0 0; color: #b9a7ca; font-size: 12px; line-height: 1.6; }
.attachment-link { display: inline-block; margin-top: 8px; color: #d385ff; font-size: 12px; }
.request-meta span { margin: 0 4px; color: #71647a; }
.request-meta strong { margin-left: 8px; color: #f8f6fc; }
.reason { color: #e3d8ed; white-space: pre-wrap; }
.actions { display: flex; flex: 0 0 auto; align-items: center; gap: 8px; }
.actions button { min-height: 34px; border: 1px solid transparent; border-radius: 7px; cursor: pointer; font: inherit; font-size: 12px; padding: 0 11px; }
.approve { background: #17432f; color: #5be3a0; }
.reject { background: #411c26; color: #ff8da4; }
.empty-state { margin: 0; border-top: 1px solid #34283e; color: #b9a7ca; font-size: 13px; padding: 24px 0 8px; text-align: center; }
.message { border-radius: 7px; margin: 0 0 12px; font-size: 13px; padding: 10px 12px; }
.error { background: #3a211d; color: #ff9a88; }
.success { background: #143427; color: #51e4a1; }
@media (max-width: 650px) { .admin-page { padding: 0 8px 16px; } .admin-shell { padding: 0 12px 20px; } .request-row { flex-direction: column; gap: 12px; } .actions { justify-content: flex-end; } .page-heading { align-items: flex-start; } }
</style>
