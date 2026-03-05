import { defineStore } from 'pinia'

export const useMatchStore = defineStore('matchStore', {
  state: () => ({
    matches: [],
    loading: false
  }),
  actions: {
    async fetchMatches() {
      this.loading = true
      try {
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/matches')
        this.matches = await response.json()
      } catch (error) {
        console.error("Error cargando partidos:", error)
      } finally {
        this.loading = false
      }
    }
  },
  getters: {
    lastMatch: (state) => state.matches[0] || null,
    homeMatches: (state) => state.matches.filter(m => m.venue === 'home')
  }
})