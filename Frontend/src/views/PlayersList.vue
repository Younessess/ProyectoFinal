<script setup>
import { onMounted } from 'vue'
import { usePlayerStore } from '../Stores/playerStore'

const playerStore = usePlayerStore()

onMounted(() => {
  playerStore.fetchPlayers()
})
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold text-arenas-black mb-4">Lista de Jugadores</h2>
    
    <div v-if="playerStore.loading" class="animate-bounce text-red-600">Cargando equipo...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="player in playerStore.players" :key="player.id_player" 
           class="bg-white p-4 rounded shadow border-l-4"
           :class="player.status === 'active' ? 'border-green-500' : 'border-arenas-red'">
        <p class="font-bold text-lg">{{ player.first_name }} {{ player.last_name }}</p>
        <p class="text-sm text-gray-500 italic">Dorsal: {{ player.squad_number || 'S/D' }}</p>
      </div>
    </div>
  </div>
</template>