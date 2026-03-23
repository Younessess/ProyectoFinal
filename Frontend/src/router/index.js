import { createRouter, createWebHistory } from 'vue-router'
import Dashboard from '../views/Dashboard.vue'
import PlayersList from '../views/PlayersList.vue'

const routes = [
  { path: '/', component: Dashboard },
  { path: '/dashboard', component: Dashboard },
  { path: '/players', component: PlayersList },
  { path: '/matches', component: () => import('../views/Matches.vue') },
  { path: '/seasons', component: () => import('../views/Administration.vue') },
  { path: '/injuries', component: () => import('../views/Injuries.vue') }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router