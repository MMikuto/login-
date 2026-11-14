<template>
  <main class="request-page">
    <div class="request-shell">
      <header class="topbar">
        <router-link class="brand" :to="{ name: 'welcome' }" aria-label="LeaveFlow หน้าหลัก">
          <span class="brand-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <rect x="4" y="6" width="16" height="15" rx="2" />
              <path d="M8 3v6M16 3v6M4 10h16M8 14h3M8 17h5" />
            </svg>
          </span>
          <span>LeaveFlow</span>
        </router-link>
        <button class="user-button" type="button" :disabled="logoutLoading" @click="logout">
          {{ logoutLoading ? 'กำลังออก...' : 'ออกจากระบบ' }}
        </button>
      </header>

      <nav class="main-nav" aria-label="เมนูหลัก">
        <router-link class="nav-link" :to="{ name: 'welcome' }">หน้าหลัก</router-link>
        <router-link class="nav-link active" :to="{ name: 'leave-request' }" aria-current="page">
          ยื่นคำขอลา
        </router-link>
        <router-link class="nav-link" :to="{ name: 'welcome', hash: '#leave-history' }">
          ประวัติการลา
        </router-link>
      </nav>

      <div class="page-heading">
        <div>
          <p class="eyebrow">LEAVE MANAGEMENT</p>
          <h1>ยื่นคำขอลา</h1>
          <p class="description">กรอกข้อมูลเพื่อส่งคำขออนุญาตลา</p>
        </div>
        <router-link class="back-link" :to="{ name: 'welcome' }">กลับหน้าหลัก</router-link>
      </div>

      <div class="content-grid">
        <section class="form-panel" aria-labelledby="form-title">
          <div class="panel-heading">
            <span class="panel-icon" aria-hidden="true">+</span>
            <div>
              <h2 id="form-title">แบบฟอร์มขออนุญาตลา</h2>
              <p>กรุณากรอกข้อมูลให้ครบถ้วน</p>
            </div>
          </div>

          <p v-if="message" class="form-message" :class="messageType" role="status">
            {{ message }}
          </p>

          <form @submit.prevent="submitRequest">
            <fieldset>
              <legend>ข้อมูลผู้ยื่นคำขอ</legend>
              <label for="employee-name">ชื่อ-นามสกุล</label>
              <input
                id="employee-name"
                v-model.trim="form.name"
                type="text"
                autocomplete="name"
                placeholder="กรอกชื่อ-นามสกุล"
                readonly
                required
              >

              <label for="department">แผนก / หน่วยงาน</label>
              <input
                id="department"
                v-model.trim="form.department"
                type="text"
                placeholder="กรอกแผนกหรือหน่วยงาน"
                required
              >
            </fieldset>

            <fieldset>
              <legend>รายละเอียดการลา</legend>
              <label for="leave-type">ประเภทการลา</label>
              <select id="leave-type" v-model="form.type">
                <option value="ลาป่วย">ลาป่วย</option>
                <option value="ลากิจ">ลากิจ</option>
                <option value="ลาพักร้อน">ลาพักร้อน</option>
                <option value="ลาอื่น ๆ">ลาอื่น ๆ</option>
              </select>

              <div class="date-grid">
                <div>
                  <label for="start-date">วันที่เริ่มลา</label>
                  <input id="start-date" v-model="form.startDate" type="date" required>
                </div>
                <div>
                  <label for="end-date">วันที่สิ้นสุด</label>
                  <input
                    id="end-date"
                    v-model="form.endDate"
                    type="date"
                    :min="form.startDate || undefined"
                    required
                  >
                </div>
              </div>
              <p class="days-summary">
                จำนวนวันที่ลา <strong>{{ totalDays }} วัน</strong>
              </p>

              <label for="reason">เหตุผลในการลา</label>
              <textarea
                id="reason"
                v-model.trim="form.reason"
                rows="4"
                placeholder="อธิบายเหตุผลในการลา..."
                required
              ></textarea>
            </fieldset>

            <fieldset>
              <legend>เอกสารประกอบ <span class="optional">ไม่บังคับ</span></legend>
              <input
                ref="fileInput"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png"
                @change="selectFile"
              >
              <p class="field-hint">รองรับ PDF, JPG และ PNG (ไม่เกิน 5 MB)</p>
            </fieldset>

            <div class="form-actions">
              <button class="reset-button" type="button" :disabled="loading" @click="resetForm">
                ล้างข้อมูล
              </button>
              <button class="submit-button" type="submit" :disabled="loading">
                {{ loading ? 'กำลังส่งคำขอ...' : 'ส่งคำขอลา' }}
              </button>
            </div>
          </form>
        </section>

        <aside class="side-column">
          <section class="info-panel">
            <h2>คำแนะนำ</h2>
            <ul>
              <li>กรอกชื่อและแผนกให้ถูกต้อง</li>
              <li>เลือกประเภทการลาให้ตรงกับความต้องการ</li>
              <li>ตรวจสอบวันที่เริ่มต้นและสิ้นสุด</li>
              <li>ระบุเหตุผลในการลาให้ชัดเจน</li>
              <li>แนบเอกสารประกอบได้ (ถ้ามี)</li>
            </ul>
          </section>
          <section class="info-panel status-panel">
            <h2>สถานะคำขอ</h2>
            <p>เมื่อส่งคำขอเรียบร้อยแล้ว สถานะเริ่มต้นจะเป็น</p>
            <span class="pending-badge">รออนุมัติ</span>
          </section>
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { apiRequest } from '../services/authApi'

const router = useRouter()
const form = reactive({
  name: '',
  department: '',
  type: 'ลาป่วย',
  startDate: '',
  endDate: '',
  reason: ''
})
const fileInput = ref(null)
const file = ref(null)
const loading = ref(false)
const logoutLoading = ref(false)
const message = ref('')
const messageType = ref('')

onMounted(async () => {
  try {
    const data = await apiRequest('auth.php')
    form.name = data.account.fullName || data.account.username
    form.department = data.account.department || ''
  } catch (error) {
    message.value = error.message
    messageType.value = 'danger'
  }
})

const totalDays = computed(() => {
  if (!form.startDate || !form.endDate) return 0

  const start = new Date(`${form.startDate}T00:00:00Z`)
  const end = new Date(`${form.endDate}T00:00:00Z`)
  if (end < start) return 0

  return Math.floor((end - start) / 86400000) + 1
})

function selectFile(event) {
  file.value = event.target.files[0] || null
}

function resetForm() {
  Object.assign(form, {
    name: '',
    department: '',
    type: 'ลาป่วย',
    startDate: '',
    endDate: '',
    reason: ''
  })
  file.value = null
  if (fileInput.value) fileInput.value.value = ''
  message.value = ''
  messageType.value = ''
}

async function submitRequest() {
  message.value = ''
  messageType.value = ''

  if (!form.name || !form.department || !form.startDate || !form.endDate || !form.reason) {
    message.value = 'กรุณากรอกข้อมูลให้ครบทุกช่อง'
    messageType.value = 'danger'
    return
  }

  if (totalDays.value <= 0) {
    message.value = 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มลา'
    messageType.value = 'danger'
    return
  }

  if (file.value && file.value.size > 5 * 1024 * 1024) {
    message.value = 'ไฟล์แนบต้องมีขนาดไม่เกิน 5 MB'
    messageType.value = 'danger'
    return
  }

  const request = new FormData()
  Object.entries(form).forEach(([key, value]) => request.append(key, value))
  if (file.value) request.append('attachment', file.value)

  loading.value = true
  try {
    await apiRequest('leave.php', { method: 'POST', body: request })
    resetForm()
    message.value = 'ส่งคำขอลาสำเร็จแล้ว'
    messageType.value = 'success'
  } catch (error) {
    message.value = error.message
    messageType.value = 'danger'
  } finally {
    loading.value = false
  }
}

async function logout() {
  logoutLoading.value = true
  try {
    await apiRequest('auth.php', { method: 'DELETE' })
    await router.replace({ name: 'login' })
  } catch (error) {
    message.value = error.message
    messageType.value = 'danger'
  } finally {
    logoutLoading.value = false
  }
}
</script>

<style scoped>
.request-page {
  min-height: 100vh;
  box-sizing: border-box;
  padding: 0 12px 24px;
  background: #08070d;
  color: #f8f6fc;
}

.request-shell {
  width: min(100%, 1120px);
  min-height: 100vh;
  margin: 0 auto;
  box-sizing: border-box;
  border: 1px solid #30253a;
  border-top: 0;
  border-radius: 0 0 18px 18px;
  background: #0c0a12;
  padding: 0 16px 24px;
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
}

.user-button:disabled,
.form-actions button:disabled {
  cursor: wait;
  opacity: 0.65;
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

.page-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin: 22px 0 18px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #d385ff;
  font-size: 11px;
  font-weight: 700;
}

.page-heading h1 {
  margin: 0;
  font-size: 22px;
}

.description {
  margin: 8px 0 0;
  color: #b9a7ca;
  font-size: 13px;
}

.back-link {
  flex: 0 0 auto;
  border: 1px solid #51405f;
  border-radius: 8px;
  color: #e3d8ed;
  font-size: 12px;
  padding: 9px 12px;
  text-decoration: none;
}

.back-link:hover {
  border-color: #d385ff;
  color: #d385ff;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(220px, 1fr);
  gap: 16px;
  align-items: start;
}

.form-panel,
.info-panel {
  border-radius: 12px;
  background: #191420;
}

.form-panel {
  padding: 20px;
}

.panel-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.panel-icon {
  display: grid;
  width: 38px;
  height: 38px;
  place-items: center;
  border-radius: 10px;
  background: #30203d;
  color: #d385ff;
  font-size: 24px;
}

.panel-heading h2,
.info-panel h2 {
  margin: 0;
  font-size: 15px;
}

.panel-heading p {
  margin: 5px 0 0;
  color: #b9a7ca;
  font-size: 12px;
}

fieldset {
  min-width: 0;
  margin: 0;
  border: 0;
  border-top: 1px solid #34283e;
  padding: 16px 0;
}

legend {
  float: none;
  width: auto;
  margin: 0 0 12px;
  padding: 0;
  color: #e9dcf5;
  font-size: 13px;
  font-weight: 700;
}

label {
  display: block;
  margin: 0 0 7px;
  color: #d9cce4;
  font-size: 12px;
}

input,
select,
textarea {
  width: 100%;
  min-height: 40px;
  box-sizing: border-box;
  margin: 0 0 14px;
  border: 1px solid #403449;
  border-radius: 7px;
  outline: none;
  background: #100d18;
  color: #f8f6fc;
  font: inherit;
  font-size: 13px;
  padding: 9px 11px;
}

input:focus,
select:focus,
textarea:focus {
  border-color: #b65bfa;
  box-shadow: 0 0 0 2px rgb(182 91 250 / 16%);
}

input::placeholder,
textarea::placeholder {
  color: #867692;
}

input[type='file'] {
  padding: 6px;
}

input[type='file']::file-selector-button {
  margin-right: 10px;
  border: 0;
  border-radius: 5px;
  background: #30203d;
  color: #e9dcf5;
  cursor: pointer;
  padding: 6px 9px;
}

textarea {
  min-height: 104px;
  resize: vertical;
}

.date-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.days-summary {
  margin: 0 0 16px;
  border-radius: 7px;
  background: #100d18;
  color: #b9a7ca;
  font-size: 12px;
  padding: 10px 12px;
}

.days-summary strong {
  margin-left: 8px;
  color: #d385ff;
}

.optional,
.field-hint {
  color: #9c8ba9;
  font-size: 11px;
  font-weight: 400;
}

.field-hint {
  margin: -8px 0 0;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #34283e;
  padding-top: 16px;
}

.form-actions button {
  min-height: 40px;
  border-radius: 7px;
  cursor: pointer;
  font: inherit;
  font-size: 13px;
  padding: 0 16px;
}

.reset-button {
  border: 1px solid #51405f;
  background: transparent;
  color: #e3d8ed;
}

.submit-button {
  border: 1px solid #9229e8;
  background: #9229e8;
  color: #fff;
  font-weight: 700;
}

.form-message {
  border-radius: 7px;
  margin: 0 0 16px;
  font-size: 13px;
  padding: 10px 12px;
}

.form-message.success {
  background: #143427;
  color: #51e4a1;
}

.form-message.danger {
  background: #3a211d;
  color: #ff9a88;
}

.side-column {
  display: grid;
  gap: 14px;
}

.info-panel {
  padding: 18px;
}

.info-panel h2 {
  margin-bottom: 12px;
}

.info-panel ul {
  display: grid;
  gap: 11px;
  margin: 0;
  padding-left: 18px;
  color: #c7b9d1;
  font-size: 12px;
  line-height: 1.6;
}

.info-panel li::marker {
  color: #c36bff;
}

.info-panel p {
  margin: 0 0 14px;
  color: #b9a7ca;
  font-size: 12px;
  line-height: 1.6;
}

.pending-badge {
  display: inline-block;
  border-radius: 999px;
  background: #3a211d;
  color: #ff9a45;
  font-size: 11px;
  padding: 5px 10px;
}

@media (max-width: 760px) {
  .request-page {
    padding: 0 8px 16px;
  }

  .request-shell {
    padding-right: 12px;
    padding-left: 12px;
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .date-grid {
    grid-template-columns: 1fr;
    gap: 0;
  }

  .form-panel {
    padding: 15px;
  }

  .form-actions button {
    flex: 1;
    padding: 0 10px;
  }
}
</style>
