import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AboutView from '../views/AboutView.vue'
import ServicesView from '../views/ServicesView.vue'
import ProjectsView from '../views/ProjectsView.vue'
import IndustriesView from '../views/IndustriesView.vue'
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
    path: '/about',
    name: 'about',
    component: AboutView,
  },
  {
    path: '/services',
    name: 'services',
    component: ServicesView,
  },
  {
    path: '/projects',
    name: 'projects',
    component: ProjectsView,
  },
  {
    path: '/industries',
    name: 'industries',
    component: IndustriesView,
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
