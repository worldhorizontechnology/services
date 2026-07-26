import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CapabilitiesView from '../views/CapabilitiesView.vue'
import IndustriesView from '../views/IndustriesView.vue'
import PartnershipsView from '../views/PartnershipsView.vue'
import ContactView from '../views/ContactView.vue'

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

export default router
