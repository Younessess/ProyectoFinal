import { defineStore } from 'pinia'

export const useSeasonStore = defineStore('seasonStore', {
  state: () => ({
    seasons: [],
    currentSeasonId: null,
    loading: false
  }),
  actions: {
    async fetchSeasons() {
      this.loading = true
      try {
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/seasons')
        this.seasons = await response.json()
        // Por defecto, seleccionamos la más reciente
        if (this.seasons.length > 0) {
          this.currentSeasonId = this.seasons[0].id_season
        }
      } catch (error) {
        console.error("Error cargando temporadas:", error)
      } finally {
        this.loading = false
      }
    }
  }
})