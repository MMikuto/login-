<template>
  <main class="welcome-page">
    <section class="welcome-card">
      <div class="success-icon" aria-hidden="true">✓</div>
      <h1>เข้าสู่ระบบสำเร็จ</h1>
      <p v-if="admin">ยินดีต้อนรับ {{ admin.username }}</p>
      <p v-else>คุณเข้าสู่ระบบแล้ว</p>
      <button type="button" :disabled="loading" @click="logout">
        {{ loading ? 'กำลังออกจากระบบ...' : 'ออกจากระบบ' }}
      </button>
      <p v-if="error" class="error" role="alert">{{ error }}</p>
    </section>
  </main>
</template>

<script>
import { apiRequest } from '../services/authApi'

export default {
  name: 'WelcomeView',
  data() {
    return {
      admin: null,
      loading: false,
      error: ''
    }
  },
  async created() {
    try {
      const data = await apiRequest('auth.php')
      this.admin = data.admin
    } catch (error) {
      this.error = error.message
    }
  },
  methods: {
    async logout() {
      this.loading = true
      this.error = ''

      try {
        await apiRequest('auth.php', { method: 'DELETE' })
        await this.$router.replace({ name: 'login' })
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.welcome-page {
  display: grid;
  min-height: 100vh;
  box-sizing: border-box;
  place-items: center;
  padding: 16px;
  background: #07060c;
  color: #f8f6fc;
}

.welcome-card {
  width: min(100%, 420px);
  box-sizing: border-box;
  border: 1px solid #34283f;
  border-radius: 20px;
  background: #100d18;
  padding: 32px 24px;
  text-align: center;
}

.success-icon {
  display: grid;
  width: 58px;
  height: 58px;
  margin: 0 auto 16px;
  place-items: center;
  border-radius: 50%;
  background: #173a2b;
  color: #53d39a;
  font-size: 30px;
}

h1 {
  margin: 0 0 8px;
  font-size: 22px;
}

p {
  color: #bda9cf;
  font-size: 14px;
}

button {
  margin-top: 12px;
  border: 0;
  border-radius: 10px;
  background: #913be8;
  color: #fff;
  cursor: pointer;
  font-size: 14px;
  padding: 11px 20px;
}

button:disabled {
  cursor: wait;
  opacity: 0.7;
}

.error {
  color: #ff9aa8;
}
</style>
