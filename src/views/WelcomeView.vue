<template>
  <main class="home-page">
    <div class="home-shell">
      <header class="topbar">
        <a class="brand" href="#home" aria-label="LeaveFlow หน้าหลัก">
          <span class="brand-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="6" width="16" height="15" rx="2" />
              <path d="M8 3v6M16 3v6M4 10h16M8 14h3M8 17h5" />
            </svg>
          </span>
          <span>LeaveFlow</span>
        </a>
        <button class="user-button" type="button" :disabled="logoutLoading" @click="logout">
          {{ logoutLoading ? 'กำลังออก...' : 'ออกจากระบบ' }}
        </button>
      </header>

      <nav class="main-nav" aria-label="เมนูหลัก">
        <router-link class="nav-link active" :to="{ name: 'welcome' }" aria-current="page">หน้าหลัก</router-link>
        <router-link class="nav-link" :to="{ name: 'leave-request' }">ยื่นคำขอลา</router-link>
        <router-link class="nav-link" :to="{ name: 'leave-history' }">ประวัติการลา</router-link>
      </nav>

      <section id="home" class="dashboard">
        <div class="page-heading">
          <h1>หน้าหลัก</h1>
          <p>ยินดีต้อนรับ {{ account ? (account.fullName || account.username) : 'เข้าสู่ระบบของคุณ' }}</p>
        </div>

        <section class="stats-grid" aria-label="สรุปคำขอลา">
          <article class="stat-card">
            <p class="stat-label purple">คำขอทั้งหมด</p>
            <strong>{{ requests.length }}</strong>
          </article>
          <article class="stat-card">
            <p class="stat-label orange">รออนุมัติ</p>
            <strong>{{ countByStatus('pending') }}</strong>
          </article>
          <article class="stat-card">
            <p class="stat-label green">อนุมัติแล้ว</p>
            <strong>{{ countByStatus('approved') }}</strong>
          </article>
          <article class="stat-card">
            <p class="stat-label pink">ไม่อนุมัติ</p>
            <strong>{{ countByStatus('rejected') }}</strong>
          </article>
        </section>

        <section id="leave-history" class="recent-section">
          <div class="section-heading">
            <h2>คำขอล่าสุด</h2>
            <p>คำขอลาล่าสุดของคุณ</p>
          </div>
          <ul class="leave-list">
            <li v-for="request in recentRequests" :key="request.id" class="leave-row">
              <span>{{ request.leave_type }} · {{ request.total_days }} วัน</span>
              <span class="status" :class="statusClass(request.status)">{{ statusLabel(request.status) }}</span>
            </li>
            <li v-if="!loading && requests.length === 0" class="leave-row empty-row">ยังไม่มีคำขอลา</li>
          </ul>
        </section>

        <p v-if="error" class="error" role="alert">{{ error }}</p>
      </section>
    </div>
  </main>
</template>

<script>
import { apiRequest } from '../services/authApi'

export default {
  name: 'WelcomeView',
  data() {
    return {
      account: null,
      requests: [],
      loading: false,
      logoutLoading: false,
      error: ''
    }
  },
  computed: {
    recentRequests() {
      return this.requests.slice(0, 3)
    }
  },
  async created() {
    this.loading = true
    try {
      const data = await apiRequest('auth.php')
      this.account = data.account
      const history = await apiRequest('leave.php')
      this.requests = history.requests
    } catch (error) {
      this.error = error.message
    } finally {
      this.loading = false
    }
  },
  methods: {
    countByStatus(status) {
      return this.requests.filter((request) => request.status === status).length
    },
    statusClass(status) {
      if (status === 'approved') return 'approved'
      if (status === 'rejected') return 'rejected'
      return 'pending'
    },
    statusLabel(status) {
      if (status === 'approved') return 'อนุมัติแล้ว'
      if (status === 'rejected') return 'ไม่อนุมัติ'
      return 'รออนุมัติ'
    },
    async logout() {
      this.logoutLoading = true
      this.error = ''

      try {
        await apiRequest('auth.php', { method: 'DELETE' })
        await this.$router.replace({ name: 'login' })
      } catch (error) {
        this.error = error.message
      } finally {
        this.logoutLoading = false
      }
    }
  }
}
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  box-sizing: border-box;
  padding: 0 12px;
  background: #08070d;
  color: #f8f6fc;
}

.home-shell {
  width: min(100%, 1120px);
  min-height: 100vh;
  margin: 0 auto;
  box-sizing: border-box;
  border: 1px solid #30253a;
  border-top: 0;
  border-radius: 0 0 18px 18px;
  background: #0c0a12;
  padding: 0 16px 12px;
}

.topbar {
  min-height: 68px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #30253a;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #f8f6fc;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
}

.brand-icon {
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border-radius: 10px;
  background: #9229e8;
}

.brand-icon svg {
  width: 20px;
  height: 20px;
  stroke: #fff;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.8;
}

.user-button {
  border: 0;
  background: transparent;
  color: #d385ff;
  cursor: pointer;
  font: inherit;
  font-size: 13px;
  padding: 8px 0 8px 12px;
}

.user-button:disabled {
  cursor: wait;
  opacity: 0.7;
}

.main-nav {
  display: flex;
  min-height: 58px;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #211927;
}

.nav-link {
  color: #d3c3df;
  font-size: 13px;
  text-decoration: none;
}

.nav-link.active,
.nav-link:hover {
  color: #d385ff;
}

.dashboard {
  padding-top: 14px;
}

.page-heading h1,
.section-heading h2 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
}

.page-heading p,
.section-heading p {
  margin: 10px 0 12px;
  color: #b9a7ca;
  font-size: 12px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.stat-card {
  min-height: 80px;
  box-sizing: border-box;
  border-radius: 12px;
  background: #191420;
  padding: 12px;
}

.stat-label {
  margin: 0 0 8px;
  font-size: 12px;
}

.stat-card strong {
  font-size: 23px;
  line-height: 1;
}

.purple { color: #d385ff; }
.orange { color: #ff9a3d; }
.green { color: #38df95; }
.pink { color: #ff4d9d; }

.recent-section {
  margin-top: 12px;
  border-radius: 12px;
  background: #191420;
  padding: 14px 12px 0;
}

.section-heading p {
  margin: 10px 0 0;
}

.leave-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.leave-row {
  display: flex;
  min-height: 52px;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #34283e;
  font-size: 12px;
}

.leave-row:last-child {
  border-bottom: 0;
}

.status {
  border-radius: 999px;
  padding: 4px 9px;
  font-size: 11px;
}

.status.pending {
  background: #3a211d;
  color: #ff8a45;
}

.status.approved {
  background: #143427;
  color: #37df92;
}

.status.rejected {
  background: #3a1823;
  color: #ff6f91;
}

.empty-row {
  color: #b9a7ca;
}

.error {
  margin: 12px 0 0;
  color: #ff9aa8;
  font-size: 13px;
}

@media (max-width: 480px) {
  .home-page {
    padding: 0;
  }

  .home-shell {
    border-right: 0;
    border-left: 0;
    border-radius: 0;
    padding-right: 12px;
    padding-left: 12px;
  }
}
</style>
