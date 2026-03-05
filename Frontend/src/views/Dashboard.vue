<script setup>
import { onMounted } from 'vue'
import { usePlayerStore } from '../Stores/playerStore'

const playerStore = usePlayerStore()

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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-green-500">
        <p class="text-gray-500 font-bold uppercase text-xs">Disponibles</p>
        <p class="text-4xl font-black text-gray-800">{{ playerStore.availableCount }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-arenas-red">
        <p class="text-gray-500 font-bold uppercase text-xs">En Enfermería</p>
        <p class="text-4xl font-black text-arenas-red">{{ playerStore.totalInjured }}</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-black">
        <p class="text-gray-500 font-bold uppercase text-xs">Próximo Partido</p>
        <p class="text-xl font-bold text-gray-800">vs SD Leioa</p>
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
                <p class="text-xs text-red-700">Estado: {{ player.status }}</p>
              </div>
              <router-link to="/injuries" class="text-xs font-bold text-white bg-red-600 px-3 py-1 rounded hover:bg-red-700">
                VER FICHA
              </router-link>
            </li>
          </ul>
        </div>
      </div>

      <div class="bg-gray-800 rounded-xl shadow-md p-6 text-white flex flex-col justify-center items-center text-center">
        <img src="../assets/logo.png" alt="Logo Arenas" class="w-24 mb-4 opacity-50">
        <h3 class="text-xl font-bold mb-2 text-red-500">Arenas Club de Getxo</h3>
        <p class="text-sm text-gray-400 uppercase tracking-widest">Análisis de Rendimiento</p>
      </div>
    </div>
  </div>
</template>