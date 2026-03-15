<script setup>
import { onMounted, ref } from 'vue'
import { useMatchStore } from '../stores/matchStore'
import {useSeasonStore} from '../stores/seasonStore'

const matchStore = useMatchStore()
const seasonStore = useSeasonStore()

const showCreateDialog = ref(false)
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

onMounted(() => matchStore.fetchMatches())

function openCreateDialog() {
  showCreateDialog.value = true
  errorMessage.value = null
  isSubmitting.value = false
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

function closeCreateDialog() {
  showCreateDialog.value = false
}

async function saveMatch() {
  isSubmitting.value = true
  errorMessage.value = null
  
  try {
    await matchStore.addMatch(form.value)
    closeCreateDialog()
  } catch (error) {
    errorMessage.value = "Error al guardar el marcador del partido"
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="space-y-6 p-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black text-arenas-black uppercase border-l-4 border-arenas-red pl-3">
        Gestión de Partidos
      </h2>
      <button
        class="bg-arenas-red text-white px-4 py-2 rounded-lg hover:opacity-90 transition font-bold text-sm flex items-center gap-2"
        type="button"
        @click="openCreateDialog"
      >
        <span class="text-lg">＋</span>
        NUEVO PARTIDO
      </button>
    </div>

    <div class="bg-white shadow-md rounded-xl overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-arenas-black text-white text-xs uppercase">
          <tr>
            <th class="p-4">Fecha</th>
            <th class="p-4">Competición</th>
            <th class="p-4 text-center">Encuentro</th>
            <th class="p-4 text-center">Resultado</th>
            <th class="p-4">Campo</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="match in matchStore.matches" :key="match.id_match" class="hover:bg-gray-50">
            <td class="p-4 text-sm font-medium">{{ new Date(match.date).toLocaleDateString() }}</td>
            <td class="p-4">
              <span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold text-arenas-black">
                {{ match.competition }}
              </span>
            </td>
            <td class="p-4 text-center font-bold text-arenas-black">
              <span v-if="match.venue === 'home'">Arenas Club vs {{ match.opponent }}</span>
              <span v-else>{{ match.opponent }} vs Arenas Club</span>
            </td>
            <td class="p-4 text-center">
              <span
                class="text-lg font-black"
                :class="match.goals_for > match.goals_against ? 'text-green-600' : match.goals_for < match.goals_against ? 'text-arenas-red' : 'text-arenas-black'"
              >
                {{ match.result}}
              </span>
            </td>
            <td class="p-4 text-xs text-gray-500 uppercase font-bold">
              {{ match.venue === 'home' ? 'Gobela' : 'Visitante' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showCreateDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">Registrar nuevo encuentro</h3>
          <button type="button" class="text-white hover:text-gray-200 text-xl font-bold" @click="closeCreateDialog">×</button>
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
                <input 
                  v-model.number="form.goals_for" 
                  type="number" 
                  min="0"
                  class="w-full border rounded-md px-3 py-2 text-center font-bold text-lg focus:ring-2 focus:ring-arenas-red" 
                  required 
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-arenas-black uppercase mb-1">Goles Rival</label>
                <input 
                  v-model.number="form.goals_against" 
                  type="number" 
                  min="0"
                  class="w-full border rounded-md px-3 py-2 text-center font-bold text-lg focus:ring-2 focus:ring-arenas-black" 
                  required 
                />
              </div>
            </div>

            <p v-if="errorMessage" class="text-sm text-arenas-red">{{ errorMessage }}</p>
          </div>

          <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
            <button type="button" class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100" @click="closeCreateDialog">
              Cancelar
            </button>
            <button type="submit" class="px-4 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90" :disabled="isSubmitting">
              {{ isSubmitting ? 'Guardando...' : 'Crear Partido' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>