<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePlayerStore } from '../stores/playerStore'

const props = defineProps({
  modelValue: Boolean
})
const emit = defineEmits(['update:modelValue'])

const playerStore = usePlayerStore()

// Cada entrada tendrá un id temporal, un jugador seleccionado, un archivo y un estado de carga
const uploadRows = ref([
  { id: Date.now(), id_player: '', file: null, status: 'pending', error: null }
])

const isProcessing = ref(false)

// Limpiar modal al abrir
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    uploadRows.value = [
      { id: Date.now(), id_player: '', file: null, status: 'pending', error: null }
    ]
    isProcessing.value = false
    
    // Asegurarnos de tener los jugadores cargados para el select
    if (playerStore.players.length === 0) {
      playerStore.fetchPlayers()
    }
  }
})

function close() {
  if (isProcessing.value) return // Prevenir cierre si está procesando
  emit('update:modelValue', false)
}

function addRow() {
  uploadRows.value.push({
    id: Date.now(),
    id_player: '',
    file: null,
    status: 'pending',
    error: null
  })
}

function removeRow(index) {
  uploadRows.value.splice(index, 1)
  if (uploadRows.value.length === 0) {
    addRow() // Siempre dejar al menos una fila
  }
}

function onFileChange(event, index) {
  const file = event.target.files[0]
  if (file) {
    uploadRows.value[index].file = file
    uploadRows.value[index].status = 'pending'
    uploadRows.value[index].error = null
  }
}

async function processUploads() {
  // Validar que todas las filas tengan datos
  const invalidRow = uploadRows.value.find(row => row.status === 'pending' && (!row.id_player || !row.file))
  if (invalidRow) {
    alert("Por favor, selecciona un jugador y un archivo Excel en todas las filas antes de procesar.")
    return
  }

  isProcessing.value = true

  for (let i = 0; i < uploadRows.value.length; i++) {
    const row = uploadRows.value[i]
    if (row.status === 'success') continue // Evita reprocesar si hubo error en otros y reintentan

    row.status = 'uploading'
    row.error = null

    const formData = new FormData()
    formData.append('file', row.file)
    formData.append('id_player', row.id_player)

    try {
      // API de Python (API ficticia / no conectada aún)
      const response = await fetch(`http://localhost:5000/api/import-player-stats`, {
        method: 'POST',
        body: formData
      })
      
      if (!response.ok) {
        throw new Error('Error del servidor')
      }
      
      row.status = 'success'
    } catch (e) {
      row.status = 'error'
      row.error = e.message || "No se pudo conectar con la API de Python"
    }
  }

  isProcessing.value = false
}

// Verifica si todas las filas que se intentaron procesar terminaron en success
const allSucceeded = () => {
  return uploadRows.value.length > 0 && uploadRows.value.every(r => r.status === 'success')
}

</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col overflow-hidden">
      <!-- Cabecera -->
      <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
        <h3 class="text-lg font-semibold text-white">Importar Estadísticas Múltiples</h3>
        <button 
          v-if="!isProcessing"
          type="button" 
          class="text-white hover:text-gray-200 text-2xl font-bold leading-none" 
          @click="close"
        >
          ×
        </button>
      </div>

      <!-- Contenido / Filas -->
      <div class="p-5 overflow-y-auto space-y-4 bg-gray-50 flex-1">
        <p class="text-sm text-gray-700">
          Selecciona el jugador y adjunta su Excel de estadísticas. Pulsa "+" para añadir más archivos simultáneamente.
        </p>

        <div 
          v-for="(row, index) in uploadRows" 
          :key="row.id" 
          class="flex flex-col md:flex-row gap-3 bg-white p-4 rounded-lg shadow-sm border items-start md:items-center"
          :class="{ 'border-green-500': row.status === 'success', 'border-red-500': row.status === 'error' }"
        >
          <!-- Selector de Jugador -->
          <div class="flex-1 w-full relative">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Jugador</label>
            <select 
              v-model="row.id_player" 
              class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
              :disabled="row.status === 'success' || isProcessing"
            >
              <option value="" disabled>Selecciona un jugador...</option>
              <option 
                v-for="player in playerStore.players" 
                :key="player.id_player" 
                :value="player.id_player"
              >
                {{ player.first_name }} {{ player.last_name }} ({{ player.status }})
              </option>
            </select>
          </div>

          <!-- Selector de Archivo -->
          <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Archivo Excel</label>
            <input 
              type="file" 
              accept=".xlsx, .xls, .csv" 
              class="w-full border rounded-md px-3 py-1.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:bg-gray-100 hover:file:bg-gray-200"
              @change="(e) => onFileChange(e, index)"
              :disabled="row.status === 'success' || isProcessing"
            />
          </div>

          <!-- Estado / Botón Eliminar -->
          <div class="flex items-center gap-2 mt-4 md:mt-0 md:self-end h-9">
            <button 
              type="button" 
              class="text-red-500 hover:text-red-700 font-bold px-2"
              @click="removeRow(index)"
              v-if="!isProcessing && row.status !== 'success'"
              title="Eliminar fila"
            >
              ✕
            </button>
            
            <span v-if="row.status === 'uploading'" class="text-blue-500 text-xs font-bold animate-pulse">Enviando...</span>
            <span v-if="row.status === 'success'" class="text-green-600 text-xs font-bold">✔ OK</span>
            <span v-if="row.status === 'error'" class="text-red-600 text-xs font-bold" :title="row.error">✖ Error</span>
          </div>

          <!-- Mensaje de error (si ocupa toda la fila) -->
          <p v-if="row.error" class="text-xs text-red-500 w-full mt-2">
            API Python Error: {{ row.error }}
          </p>
        </div>

        <button 
          v-if="!allSucceeded() && !isProcessing"
          type="button" 
          @click="addRow"
          class="mt-2 text-sm font-bold text-arenas-black hover:text-arenas-red flex items-center gap-1"
        >
          <span class="text-lg">+</span> Añadir fila
        </button>

      </div>

      <!-- Pie del Modal -->
      <div class="px-5 py-4 border-t bg-white flex justify-end gap-3">
        <button 
          type="button" 
          class="px-4 py-2 rounded-md text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200"
          @click="close"
          :disabled="isProcessing"
        >
          {{ allSucceeded() ? 'Cerrar' : 'Cancelar' }}
        </button>

        <button 
          v-if="!allSucceeded()"
          type="button" 
          class="px-6 py-2 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90 disabled:opacity-50 flex items-center gap-2"
          @click="processUploads"
          :disabled="isProcessing"
        >
          <span v-if="isProcessing" class="animate-spin text-lg leading-none">↻</span>
          {{ isProcessing ? 'Conectando a API Python...' : 'Procesar Todos' }}
        </button>
      </div>
    </div>
  </div>
</template>
