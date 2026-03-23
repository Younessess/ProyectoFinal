<script setup>
import { ref, watch } from 'vue'
import { useMatchStore } from '../stores/matchStore'
import { useSeasonStore } from '../stores/seasonStore'

const props = defineProps({
  modelValue: Boolean // Para controlar la visibilidad con v-model
})

const emit = defineEmits(['update:modelValue', 'created'])

const matchStore = useMatchStore()
const seasonStore = useSeasonStore()

const isSubmitting = ref(false)
const errorMessage = ref(null)

const form = ref({
  id_season: seasonStore.currentSeasonId,
  date: '',
  competition: '',
  opponent: '',
  venue: 'home',
  goals_for: 0,
  goals_against: 0
})

// Reiniciar el formulario cada vez que se abre el diálogo
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    errorMessage.value = null
    form.value = {
      id_season: seasonStore.currentSeasonId,
      date: '',
      competition: '',
      opponent: '',
      venue: 'home',
      goals_for: 0,
      goals_against: 0
    }
  }
})

function close() {
  emit('update:modelValue', false)
}

async function saveMatch() {
  isSubmitting.value = true
  errorMessage.value = null
  
  try {
    await matchStore.addMatch(form.value)
    emit('created') // Avisar al padre que se creó un partido
    close()
  } catch (error) {
    errorMessage.value = "Error al guardar el marcador del partido"
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">
      <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
        <h3 class="text-lg font-semibold text-white">Registrar nuevo encuentro</h3>
        <button type="button" class="text-white hover:text-gray-200 text-xl font-bold" @click="close">×</button>
      </div>

      <form @submit.prevent="saveMatch">
        <div class="px-5 py-4 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">Fecha</label>
              <input v-model="form.date" type="date" class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red" required />
            </div>
            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">Competición</label>
              <input v-model="form.competition" type="text" class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red" required />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">Equipo Rival</label>
              <input v-model="form.opponent" type="text" class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red" required />
            </div>
            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">Sede</label>
              <select v-model="form.venue" class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-arenas-red">
                <option value="home">Casa (Gobela)</option>
                <option value="away">Visitante</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            <div>
              <label class="block text-xs font-bold text-arenas-red uppercase mb-1">Goles Arenas</label>
              <input v-model.number="form.goals_for" type="number" min="0" class="w-full border rounded-md px-3 py-2 text-center font-bold text-lg focus:ring-2 focus:ring-arenas-red" required />
            </div>
            <div>
              <label class="block text-xs font-bold text-arenas-black uppercase mb-1">Goles Rival</label>
              <input v-model.number="form.goals_against" type="number" min="0" class="w-full border rounded-md px-3 py-2 text-center font-bold text-lg focus:ring-2 focus:ring-arenas-black" required />
            </div>
          </div>

          <p v-if="errorMessage" class="text-sm text-arenas-red">{{ errorMessage }}</p>
        </div>

        <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
          <button type="button" class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100" @click="close">
            Cancelar
          </button>
          <button type="submit" class="px-4 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90" :disabled="isSubmitting">
            {{ isSubmitting ? 'Guardando...' : 'Crear Partido' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>