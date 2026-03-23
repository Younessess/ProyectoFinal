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
    },
    async closeInjury(injuryData) {
      this.loading = true;
      try {
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/injuries/close', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id_injury: injuryData.id_injury,
            end_date: injuryData.end_date,
            id_player: injuryData.id_player
          })
        });
    
        // Fetch no salta al catch automáticamente si hay un error 400 o 500
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Error en el servidor');
        }
    
        const data = await response.json();
        console.log(data.msg);
        
        // Refrescar la lista de lesiones 
        await this.fetchInjuries();
        
        return data;
    
      } catch (error) {
        console.error('Error cerrando lesión:', error.message);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async registerInjury(injuryData) {
      this.loading = true;
      try {
        const response = await fetch('http://localhost/futbol-analytics/Backend/public/injuries', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(injuryData)
        });
        
        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.error || 'Error al registrar lesión');
        }
        
        // Refresh the list automatically
        await this.fetchInjuries();
        
        return await response.json();
      } catch (error) {
        console.error('Error en registerInjury:', error.message);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  },

  // 3. LOS GETTERS: Datos "cocinados" o filtrados
  getters: {
    // Devuelve solo las lesiones que siguen activas (jugadores en la enfermería)
    activeInjuries: (state) => state.injuries.filter(i => !i.end_date),
    
    // Cuenta cuántos lesionados hay para poner el "globito" en el Sidebar
    totalInjuredCount: (state) => state.injuries.filter(i => !i.end_date).length
  }
})