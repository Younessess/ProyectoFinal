<script setup>
import { onMounted, ref } from 'vue'
import { usePlayerStore } from '../stores/playerStore'
import NewMatch from '@/components/NewMatch.vue'
import ImportStatsModal from '@/components/ImportStatsModal.vue'

const playerStore = usePlayerStore()
const showCreateDialog = ref(false)
const showImportStatsDialog = ref(false)

onMounted(() => {
  // Si la lista está vacía, la cargamos al entrar al Dashboard
  if (playerStore.players.length === 0) {
    playerStore.fetchPlayers()
  }
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-3xl font-black text-gray-800 uppercase">Panel de Control</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-green-500">
        <p class="text-gray-500 font-bold uppercase text-xs">Disponibles</p>
        <p class="text-4xl font-black text-gray-800">{{ playerStore.availableCount }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-arenas-red">
        <p class="text-gray-500 font-bold uppercase text-xs">En Enfermería</p>
        <p class="text-4xl font-black text-arenas-red">{{ playerStore.totalInjured }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-black p-4">
          <h2 class="text-white font-bold flex items-center">
            <span class="mr-2">🚨</span> AVISOS DE DISPONIBILIDAD
          </h2>
        </div>
        <div class="p-4">
          <div v-if="playerStore.totalInjured === 0" class="text-gray-400 italic">
            No hay jugadores lesionados actualmente.
          </div>
          <ul v-else class="space-y-3">
            <li v-for="player in playerStore.injuredPlayers" :key="player.id_player" 
                class="flex justify-between items-center p-3 bg-red-50 border border-red-100 rounded-lg">
              <div>
                <p class="font-bold text-red-900">{{ player.first_name }} {{ player.last_name }}</p>
                <p class="text-xs text-red-700">Estado: lesionado</p>
              </div>
              <router-link to="/injuries" class="text-xs font-bold text-white bg-red-600 px-3 py-1 rounded hover:bg-red-700">
                VER FICHA
              </router-link>
            </li>
          </ul>
        </div>
      </div>

      <div class="rounded-xl shadow-md p-6 text-white flex flex-col justify-center items-center text-center gap-6">
        <button @click="showCreateDialog = true" class="bg-black text-white text-xl px-6 py-3 rounded-2xl  hover:bg-red-700 cursor-pointer">Nuevo Partido</button>
        <button @click="showImportStatsDialog = true" class="bg-black text-white text-xl px-6 py-3 rounded-2xl  hover:bg-red-700 cursor-pointer">Nuevas Estadísticas</button>
      </div>
    </div>

    <NewMatch v-model="showCreateDialog" />
    <ImportStatsModal v-model="showImportStatsDialog" />
  </div>
</template>