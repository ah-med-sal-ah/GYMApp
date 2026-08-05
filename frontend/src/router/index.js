import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAdminAuthStore } from '@/stores/admin'

const routes = [
  // Gym auth
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { guestOnly: 'gym' },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/Register.vue'),
    meta: { guestOnly: 'gym' },
  },

  // Gym app
  {
    path: '/',
    component: () => import('@/layouts/GymLayout.vue'),
    meta: { requiresAuth: 'gym' },
    children: [
      { path: '', redirect: { name: 'dashboard' } },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/views/Dashboard.vue'),
      },
      {
        path: 'clients',
        name: 'clients.index',
        component: () => import('@/views/clients/ClientList.vue'),
      },
      {
        path: 'clients/create',
        name: 'clients.create',
        component: () => import('@/views/clients/ClientForm.vue'),
      },
      {
        path: 'clients/:id',
        name: 'clients.show',
        component: () => import('@/views/clients/ClientDetails.vue'),
        props: true,
      },
      {
        path: 'clients/:id/edit',
        name: 'clients.edit',
        component: () => import('@/views/clients/ClientForm.vue'),
        props: true,
      },
      {
        path: 'coaches',
        name: 'coaches.index',
        component: () => import('@/views/coaches/CoachList.vue'),
      },
      {
        path: 'coaches/create',
        name: 'coaches.create',
        component: () => import('@/views/coaches/CoachForm.vue'),
      },
      {
        path: 'coaches/:id',
        name: 'coaches.show',
        component: () => import('@/views/coaches/CoachDetails.vue'),
        props: true,
      },
      {
        path: 'coaches/:id/edit',
        name: 'coaches.edit',
        component: () => import('@/views/coaches/CoachForm.vue'),
        props: true,
      },
      {
        path: 'sports',
        name: 'sports.index',
        component: () => import('@/views/sports/SportList.vue'),
      },
      {
        path: 'sports/create',
        name: 'sports.create',
        component: () => import('@/views/sports/SportForm.vue'),
      },
      {
        path: 'sports/:id',
        name: 'sports.show',
        component: () => import('@/views/sports/SportDetails.vue'),
        props: true,
      },
      {
        path: 'sports/:id/edit',
        name: 'sports.edit',
        component: () => import('@/views/sports/SportForm.vue'),
        props: true,
      },
      {
        path: 'expenses',
        name: 'expenses.index',
        component: () => import('@/views/expenses/ExpenseList.vue'),
      },
      {
        path: 'expenses/create',
        name: 'expenses.create',
        component: () => import('@/views/expenses/ExpenseForm.vue'),
      },
      {
        path: 'expenses/:id',
        name: 'expenses.show',
        component: () => import('@/views/expenses/ExpenseDetails.vue'),
        props: true,
      },
      {
        path: 'expenses/:id/edit',
        name: 'expenses.edit',
        component: () => import('@/views/expenses/ExpenseForm.vue'),
        props: true,
      },
      {
        path: 'profile',
        name: 'profile',
        component: () => import('@/views/profile/Profile.vue'),
      },
    ],
  },

  // Admin auth
  {
    path: '/admin/login',
    name: 'admin.login',
    component: () => import('@/views/admin/AdminLogin.vue'),
    meta: { guestOnly: 'admin' },
  },

  // Admin app
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: 'admin' },
    children: [
      { path: '', redirect: { name: 'admin.users.index' } },
      {
        path: 'users',
        name: 'admin.users.index',
        component: () => import('@/views/admin/AdminUsers.vue'),
      },
      {
        path: 'users/:id',
        name: 'admin.users.show',
        component: () => import('@/views/admin/AdminUserDetails.vue'),
        props: true,
      },
      {
        path: 'profile',
        name: 'admin.profile',
        component: () => import('@/views/admin/AdminProfile.vue'),
      },
    ],
  },

  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
  const requiresAuth = to.matched.find((record) => record.meta.requiresAuth)?.meta.requiresAuth
  const guestOnly = to.matched.find((record) => record.meta.guestOnly)?.meta.guestOnly

  if (requiresAuth === 'gym') {
    const auth = useAuthStore()
    if (!auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (requiresAuth === 'admin') {
    const adminAuth = useAdminAuthStore()
    if (!adminAuth.isAuthenticated) return { name: 'admin.login', query: { redirect: to.fullPath } }
  }

  if (guestOnly === 'gym') {
    const auth = useAuthStore()
    if (auth.isAuthenticated) return { name: 'dashboard' }
  }

  if (guestOnly === 'admin') {
    const adminAuth = useAdminAuthStore()
    if (adminAuth.isAuthenticated) return { name: 'admin.users.index' }
  }

  return true
})

export default router
