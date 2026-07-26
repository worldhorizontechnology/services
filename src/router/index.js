import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CapabilitiesView from '../views/CapabilitiesView.vue'
import IndustriesView from '../views/IndustriesView.vue'
import PartnershipsView from '../views/PartnershipsView.vue'
import ContactView from '../views/ContactView.vue'

const getInitialRoute = () => {
  const params = new URLSearchParams(window.location.search)
  const fallbackPath = params.get('p')

  if (fallbackPath) {
    const normalizedPath = fallbackPath.startsWith('/') ? fallbackPath : `/${fallbackPath}`
    return normalizedPath
  }

  return window.location.pathname.replace(new RegExp(`^${import.meta.env.BASE_URL}`), '/') || '/'
}

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/capabilities',
    name: 'capabilities',
    component: CapabilitiesView,
  },
  {
    path: '/industries',
    name: 'industries',
    component: IndustriesView,
  },
  {
    path: '/partnerships',
    name: 'partnerships',
    component: PartnershipsView,
  },
  {
    path: '/contact',
    name: 'contact',
    component: ContactView,
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  if (to.matched.length === 0 && window.location.search.includes('p=')) {
    const fallbackPath = getInitialRoute()
    return next(fallbackPath)
  }

  next()
})

export default router
