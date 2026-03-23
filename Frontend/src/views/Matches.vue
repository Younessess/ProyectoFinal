<script setup>
import { onMounted, ref } from 'vue'
import { useMatchStore } from '../stores/matchStore'
import {useSeasonStore} from '../stores/seasonStore'
import NewMatch from '@/components/NewMatch.vue'
const matchStore = useMatchStore()
const seasonStore = useSeasonStore()

const showCreateDialog = ref(false)

onMounted(() => matchStore.fetchMatches())

function openCreateDialog() {
  showCreateDialog.value = true
}

function closeCreateDialog() {
  showCreateDialog.value = false
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

    <NewMatch 
      v-model="showCreateDialog" 
      @created="matchStore.fetchMatches" 
    />
  </div>
</template>