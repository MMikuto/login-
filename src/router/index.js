import { createRouter, createWebHistory } from 'vue-router'
import { apiRequest } from '../services/authApi'

const routes = [
  {
    path: '/',
    redirect: { name: 'login' }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/login.vue')
  },
  {
    path: '/welcome',
    name: 'welcome',
    component: () => import('../views/WelcomeView.vue'),
    meta: { role: 'user' }
  },
  {
    path: '/leave-request',
    name: 'leave-request',
    component: () => import('../views/LeaveRequestView.vue'),
    meta: { role: 'user' }
  },
  {
    path: '/leave-history',
    name: 'leave-history',
    component: () => import('../views/LeaveHistoryView.vue'),
    meta: { role: 'user' }
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('../views/AdminDashboardView.vue'),
    meta: { role: 'admin' }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: { name: 'login' }
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach(async (to) => {
  if (to.meta.role) {
    try {
      const session = await apiRequest('auth.php')
      if (session.role !== to.meta.role) {
        return { name: session.role === 'admin' ? 'admin' : 'welcome' }
      }
    } catch (error) {
      return { name: 'login' }
    }
  }
})

export default router
