import { defineStore } from 'pinia'

export const useInjuryStore = defineStore('injuryStore', {
  // 1. EL ESTADO: Donde se guardan los datos físicamente
  state: () => ({
    injuries: [],    // Lista completa de lesiones (historial)
    loading: false,  // Para mostrar un spinner mientras carga
    error: null      // Para guardar mensajes de error si la API falla
  }),

  // 2. LAS ACCIONES: Funciones para hablar con el Backend (PHP)
  actions: {
    async fetchInjuries() {
      this.loading = true
      this.error = null
      try {
        // Llamada a tu API en XAMPP
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/injuries/active')
        
        if (!response.ok) throw new Error('Error al obtener el historial médico')
        
        const data = await response.json()
        this.injuries = data
      } catch (err) {
        this.error = err.message
        console.error("Error en injuryStore:", err)
      } finally {
        this.loading = false
      }
    }
  },

  // 3. LOS GETTERS: Datos "cocinados" o filtrados
  getters: {
    // Devuelve solo las lesiones que siguen activas (jugadores en la enfermería)
    activeInjuries: (state) => state.injuries.filter(i => i.is_active === 1),
    
    // Cuenta cuántos lesionados hay para poner el "globito" en el Sidebar
    totalInjuredCount: (state) => state.injuries.filter(i => i.is_active === 1).length
  }
})