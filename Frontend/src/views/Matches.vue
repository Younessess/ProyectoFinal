<script setup>
import { onMounted } from 'vue'
import { useMatchStore } from '../stores/matchStore'

const matchStore = useMatchStore()
onMounted(() => matchStore.fetchMatches())
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black text-gray-800 uppercase border-l-4 border-red-600 pl-3">Gestión de Partidos</h2>
      <button class="bg-black text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-bold text-sm">
        + REGISTRAR PARTIDO
      </button>
    </div>

    <div class="bg-white shadow-md rounded-xl overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-900 text-white text-xs uppercase">
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
            <td class="p-4"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">{{ match.competition }}</span></td>
            <td class="p-4 text-center font-bold">
              <span v-if="match.venue === 'home'">Arenas Club vs {{ match.opponent }}</span>
              <span v-else>{{ match.opponent }} vs Arenas Club</span>
            </td>
            <td class="p-4 text-center">
              <span class="text-lg font-black" :class="match.goals_for > match.goals_against ? 'text-green-600' : 'text-red-600'">
                {{ match.goals_for }} - {{ match.goals_against }}
              </span>
            </td>
            <td class="p-4 text-xs text-gray-500 uppercase font-bold">{{ match.venue === 'home' ? 'Gobela' : 'Visitante' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>