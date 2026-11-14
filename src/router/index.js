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
    meta: { requiresAuth: true }
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
  if (to.meta.requiresAuth) {
    try {
      await apiRequest('auth.php')
    } catch (error) {
      return { name: 'login' }
    }
  }
})

export default router
