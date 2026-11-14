<template>
  <main class="login-page">
    <section class="login-panel">
      <header class="topbar">
        <router-link class="brand" to="/" aria-label="LeaveFlow admin login">
          <span class="brand-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="5" width="16" height="16" rx="3" />
              <path d="M8 3v4m8-4v4M4 10h16m-11 5 2 2 4-4" />
            </svg>
          </span>
          <span></span>
        </router-link>
        <span class="login-tag">{{ role === 'admin' ? 'ADMIN LOGIN' : 'USER LOGIN' }}</span>
      </header>

      <section class="login-content" aria-labelledby="login-title">
        <div class="user-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="7.5" r="3.5" />
            <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
          </svg>
        </div>
        <h1 id="login-title">{{ role === 'admin' ? 'Admin Login' : 'User Login' }}</h1>
        <p class="subtitle">{{ role === 'admin' ? 'เข้าสู่ระบบผู้ดูแลระบบ' : 'เข้าสู่ระบบผู้ใช้งาน' }}</p>

        <div class="role-switch" role="group" aria-label="เลือกประเภทบัญชี">
          <button type="button" :class="{ selected: role === 'user' }" @click="role = 'user'">
            User
          </button>
          <button type="button" :class="{ selected: role === 'admin' }" @click="role = 'admin'">
            Admin
          </button>
        </div>

        <form class="login-form" @submit.prevent="login">
          <label for="username">Username</label>
          <div class="input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="12" cy="8" r="3.5" />
              <path d="M5 20a7 7 0 0 1 14 0" />
            </svg>
            <input
              id="username"
              v-model.trim="username"
              type="text"
              autocomplete="username"
              placeholder="กรอกชื่อผู้ใช้งาน"
              required
            />
          </div>

          <label for="password">Password</label>
          <div class="input-wrap">
            <svg class="field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="5" y="10" width="14" height="11" rx="2" />
              <path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3" />
            </svg>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              placeholder="กรอกรหัสผ่าน"
              required
            />
            <button
              class="password-toggle"
              type="button"
              :aria-label="showPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน'"
              @click="showPassword = !showPassword"
            >
              <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                <circle cx="12" cy="12" r="2.5" />
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a16 16 0 0 1-3.1 3.6M6.2 6.8C3.8 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1 0 1.9-.2 2.7-.5" />
                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
              </svg>
            </button>
          </div>

          <p v-if="error" class="login-error" role="alert">{{ error }}</p>
          <button class="submit-button" type="submit" :disabled="loading">
            {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ →' }}
          </button>
        </form>
      </section>

      <footer class="footer">68703256 "©" 2026</footer>
    </section>
  </main>
</template>

<script>
import { apiRequest } from '../services/authApi'

export default {
  name: 'LoginView',
  data() {
    return {
      username: '',
      password: '',
      role: 'user',
      error: '',
      loading: false,
      showPassword: false
    }
  },
  methods: {
    async login() {
      this.error = ''
      this.loading = true

      try {
        await apiRequest('auth.php', {
          method: 'POST',
          body: JSON.stringify({
            role: this.role,
            username: this.username,
            password: this.password
          })
        })
        await this.$router.push(this.role === 'admin' ? '/admin' : '/welcome')
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
:global(html),
:global(body),
:global(#app) {
  min-height: 100%;
  margin: 0;
}

.login-page {
  display: grid;
  min-height: 100vh;
  box-sizing: border-box;
  place-items: center;
  padding: 16px;
  color: #913be8;
  background: #07060c;
  font-family: Arial, Helvetica, sans-serif;
}

.login-panel {
  position: relative;
  display: flex;
  width: min(100%, 780px);
  min-height: 505px;
  flex-direction: column;
  overflow: hidden;
  border-radius: 20px;
  background: #090810;
  padding: 12px;
  box-sizing: border-box;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #913be8;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
}

.brand-icon {
  display: grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border-radius: 12px;
  background: #852de0;
  color: #fff;
}

.brand-icon svg {
  width: 21px;
  stroke: currentColor;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.8;
}

.login-tag {
  border-radius: 20px;
  background: #1b1129;
  color: #913be8;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
}

.login-content {
  width: 100%;
  margin: 28px auto 0;
  text-align: left;
}

.user-icon {
  display: grid;
  width: 68px;
  height: 68px;
  margin: 0 auto 10px;
  place-items: center;
  border-radius: 20px;
  background: #201331;
  color: #be72ff;
}

.user-icon svg {
  width: 31px;
  stroke: currentColor;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.8;
}

h1 {
  margin: 0;
  color: #913be8;
  font-size: 24px;
  font-weight: 700;
  text-align: center;
}

.subtitle {
  margin: 5px 0 24px;
  color: #913be8;
  font-size: 13px;
  text-align: center;
}

.role-switch {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  width: min(100%, 360px);
  margin: 0 auto 20px;
  border: 1px solid #392749;
  border-radius: 9px;
  background: #100d18;
  padding: 3px;
}

.role-switch button {
  min-height: 36px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #bda9cf;
  cursor: pointer;
  font: inherit;
}

.role-switch button.selected {
  background: #30203d;
  color: #e7c9ff;
}

.login-form label {
  display: block;
  margin: 0 0 6px;
  color: #913be8;
  font-size: 13px;
  font-weight: 600;
}

.input-wrap {
  display: flex;
  height: 52px;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  border: 1px solid #3a2a4b;
  border-radius: 13px;
  background: #17131e;
  padding: 0 12px;
}

.field-icon {
  width: 14px;
  flex: 0 0 14px;
  stroke: #bd75ff;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.7;
}

.input-wrap input {
  width: 100%;
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: #913be8;
  font: inherit;
  font-size: 13px;
}

.input-wrap input::placeholder {
  color: #913be8;
  opacity: 1;
}

.input-wrap:focus-within {
  border-color: #9b42ee;
  box-shadow: 0 0 0 2px rgb(155 66 238 / 15%);
}

.password-toggle {
  display: grid;
  width: 24px;
  height: 30px;
  flex: 0 0 24px;
  place-items: center;
  border: 0;
  background: transparent;
  color: #978ba3;
  padding: 0;
}

.password-toggle svg {
  width: 16px;
  stroke: currentColor;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.7;
}

.login-error {
  margin: 0 0 12px;
  border: 1px solid #733d48;
  border-radius: 10px;
  background: #301820;
  color: #ffb9c3;
  font-size: 13px;
  padding: 9px 12px;
}

.submit-button {
  width: 100%;
  min-height: 50px;
  border: 0;
  border-radius: 13px;
  background: #913be8;
  color: #fff;
  font-size: 15px;
  font-weight: 700;
  transition: background-color 150ms ease, transform 150ms ease;
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-1px);
  background: #a44af4;
}

.submit-button:disabled {
  cursor: wait;
  opacity: 0.75;
}

.footer {
  margin-top: auto;
  padding-top: 12px;
  color: #913be8;
  font-size: 11px;
  text-align: center;
}

@media (min-height: 650px) {
  .login-content {
    margin-top: 54px;
  }
}

@media (max-width: 520px) {
  .login-page {
    padding: 10px;
  }

  .login-panel {
    min-height: min(505px, calc(100vh - 20px));
    border-radius: 16px;
  }
}

@media (max-height: 600px) {
  .login-content {
    margin-top: 18px;
  }
}
</style>
