<template>
  <main class="history-page">
    <div class="history-shell">
      <header class="topbar">
        <router-link class="brand" :to="{ name: 'welcome' }">
          <span class="brand-icon" aria-hidden="true">LF</span>
          <span>LeaveFlow</span>
        </router-link>
        <button class="logout" type="button" :disabled="logoutLoading" @click="logout">
          {{ logoutLoading ? 'กำลังออก...' : 'ออกจากระบบ' }}
        </button>
      </header>

      <nav class="main-nav" aria-label="เมนูหลัก">
        <router-link class="nav-link" :to="{ name: 'welcome' }">หน้าหลัก</router-link>
        <router-link class="nav-link" :to="{ name: 'leave-request' }">ยื่นคำขอลา</router-link>
        <router-link class="nav-link active" :to="{ name: 'leave-history' }" aria-current="page">
          ประวัติการลา
        </router-link>
      </nav>

      <section class="heading">
        <div>
          <p class="eyebrow">MY LEAVE</p>
          <h1>ประวัติการลา</h1>
          <p>รายการคำขอลาของบัญชีนี้</p>
        </div>
        <button class="refresh" type="button" :disabled="loading" @click="loadHistory">รีเฟรช</button>
      </section>

      <p v-if="error" class="message error" role="alert">{{ error }}</p>
      <section class="history-panel" aria-label="รายการคำขอลาของฉัน">
        <p v-if="loading && requests.length === 0" class="empty">กำลังโหลดประวัติ...</p>
        <p v-else-if="requests.length === 0" class="empty">ยังไม่มีประวัติการลา</p>
        <article v-for="request in requests" :key="request.id" class="request-row">
          <div>
            <div class="request-title">
              <h2>{{ request.leave_type }}</h2>
              <span class="status" :class="statusClass(request.status)">{{ statusLabel(request.status) }}</span>
            </div>
            <p class="dates">
              {{ formatDate(request.start_date) }} - {{ formatDate(request.end_date) }}
              <strong>{{ request.total_days }} วัน</strong>
            </p>
            <p class="reason">{{ request.reason }}</p>
            <p v-if="request.attachment_name" class="attachment">เอกสารแนบ: {{ request.attachment_name }}</p>
          </div>
          <time>{{ formatDate(request.created_at) }}</time>
        </article>
      </section>
    </div>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiRequest } from '../services/authApi'

const router = useRouter()
const requests = ref([])
const loading = ref(false)
const logoutLoading = ref(false)
const error = ref('')

async function loadHistory() {
  loading.value = true
  error.value = ''
  try {
    const data = await apiRequest('leave.php')
    requests.value = data.requests
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
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
  return new Date(`${value.slice(0, 10)}T00:00:00`).toLocaleDateString('th-TH', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
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

onMounted(loadHistory)
</script>

<style scoped>
.history-page { min-height: 100vh; box-sizing: border-box; padding: 0 12px 24px; background: #08070d; color: #f8f6fc; }
.history-shell { width: min(100%, 1120px); min-height: 100vh; margin: 0 auto; box-sizing: border-box; border: 1px solid #30253a; border-top: 0; border-radius: 0 0 18px 18px; background: #0c0a12; padding: 0 16px 24px; }
.topbar { min-height: 68px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #30253a; }
.brand { display: inline-flex; align-items: center; gap: 9px; color: #f8f6fc; font-size: 15px; font-weight: 700; text-decoration: none; }
.brand-icon { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 10px; background: #9229e8; color: white; font-size: 12px; }
.logout, .refresh { border: 0; background: transparent; color: #d385ff; cursor: pointer; font: inherit; font-size: 13px; }
button:disabled { cursor: wait; opacity: .6; }
.main-nav { display: flex; min-height: 58px; align-items: center; justify-content: space-between; border-bottom: 1px solid #211927; }
.nav-link { color: #d3c3df; font-size: 13px; text-decoration: none; }
.nav-link.active, .nav-link:hover { color: #d385ff; }
.heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 0 18px; }
.eyebrow { margin: 0 0 6px; color: #d385ff; font-size: 11px; font-weight: 700; }
h1 { margin: 0; font-size: 22px; }
.heading p:last-child { margin: 8px 0 0; color: #b9a7ca; font-size: 13px; }
.history-panel { border-radius: 12px; background: #191420; padding: 0 16px; }
.request-row { display: flex; justify-content: space-between; gap: 16px; border-bottom: 1px solid #34283e; padding: 16px 0; }
.request-row:last-child { border-bottom: 0; }
.request-title { display: flex; align-items: center; gap: 10px; }
.request-title h2 { margin: 0; font-size: 14px; }
.status { border-radius: 999px; padding: 4px 9px; font-size: 11px; white-space: nowrap; }
.pending { background: #3a211d; color: #ff9a45; }
.approved { background: #143427; color: #37df92; }
.rejected { background: #3a1823; color: #ff6f91; }
.dates, .reason, .attachment { margin: 8px 0 0; color: #b9a7ca; font-size: 12px; line-height: 1.6; }
.dates strong { margin-left: 8px; color: #f8f6fc; }
.reason { color: #e3d8ed; white-space: pre-wrap; }
time { flex: 0 0 auto; color: #9887a3; font-size: 11px; }
.empty { margin: 0; color: #b9a7ca; font-size: 13px; padding: 24px 0; text-align: center; }
.message { border-radius: 7px; margin: 0 0 12px; font-size: 13px; padding: 10px 12px; }
.error { background: #3a211d; color: #ff9a88; }
@media (max-width: 600px) { .history-page { padding: 0 8px 16px; } .history-shell { padding: 0 12px 20px; } .request-row { flex-direction: column; } }
</style>
