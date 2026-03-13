import { defineStore } from 'pinia'
export const useMatchStore = defineStore('matchStore', {
  state: () => ({
    matches: [],
    loading: false,
    API_BASE: "http://localhost/futbol-analytics/Backend/public"
  }),
  actions: {
    async fetchMatches() {
      this.loading = true
      try {
        const response = await fetch(`${this.API_BASE}/matches`)
        this.matches = await response.json()
      } catch (error) {
        console.error("Error cargando partidos:", error)
      } finally {
        this.loading = false
      }
    },
    async addMatch(matchData) {
      try {
        const response = await fetch(`${this.API_BASE}/matches`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          // Enviamos el objeto form (date, opponent, goals_for, etc.)
          body: JSON.stringify(matchData),
        });
    
        if (!response.ok) {
          throw new Error('Error al guardar el partido en el servidor');
        }
    
        const result = await response.json();
        if (result.status === 'success') {
          await this.fetchMatches(); 
          return result;
        }
      } catch (error) {
        console.error("Error en addMatch:", error);
        throw error;
      }
    }
  },
  getters: {
    lastMatch: (state) => state.matches[0] || null,
    homeMatches: (state) => state.matches.filter(m => m.venue === 'home')
  }
})