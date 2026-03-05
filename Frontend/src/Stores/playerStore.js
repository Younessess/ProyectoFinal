import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('playerStore', {
  state: () => ({
    players: [],
    loading: false,
    error: null
  }),
  
  actions: {
    async fetchPlayers() {
      this.loading = true
      try {
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/players')
        if (!response.ok) throw new Error('Error en la API')
        this.players = await response.json()
      } catch (err) {
        this.error = "Error al conectar con el servidor"
      } finally {
        this.loading = false
      }
    }
  },

  getters: {
    // Filtra los jugadores que tienen estado 'injured'
    injuredPlayers: (state) => state.players.filter(p => p.status === 'injured'),
    // Cuenta cuántos hay en total para el resumen
    totalInjured: (state) => state.players.filter(p => p.status === 'injured').length,
    // Obtiene los últimos partidos (esto vendrá de otro store más adelante)
    availableCount: (state) => state.players.filter(p => p.status === 'active').length
  }
})