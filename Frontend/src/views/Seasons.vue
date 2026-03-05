<script setup>
import { onMounted } from 'vue'
import { useSeasonStore } from '../stores/seasonStore'

const seasonStore = useSeasonStore()
onMounted(() => seasonStore.fetchSeasons())
</script>

<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-black text-gray-800 uppercase border-l-4 border-arenas-red pl-3">
        Administración de Temporadas
      </h2>
      <button class="bg-arenas-black text-white px-4 py-2 rounded shadow hover:bg-red-700 transition font-bold">
        + NUEVA TEMPORADA
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="season in seasonStore.seasons" :key="season.id_season" 
           class="bg-white rounded-xl shadow-sm border p-6 transition-all hover:shadow-md"
           :class="season.id_season === seasonStore.currentSeasonId ? 'ring-2 ring-red-500' : ''">
        
        <div class="flex justify-between items-start mb-4">
          <span class="text-xs font-black px-2 py-1 rounded bg-gray-100 uppercase text-gray-500">
            ID: #{{ season.id_season }}
          </span>
          <span v-if="season.id_season === seasonStore.currentSeasonId" 
                class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-full uppercase">
            Actual
          </span>
        </div>

        <h3 class="text-2xl font-black text-gray-800 mb-2">Temporada {{ season.name }}</h3>
        
        <div class="flex items-center text-sm text-gray-500 space-x-2">
          <span>📅 {{ new Date(season.start_date).getFullYear() }}</span>
          <span>-</span>
          <span>{{ new Date(season.end_date).getFullYear() }}</span>
        </div>

        <div class="mt-6 flex space-x-2">
          <button class="flex-1 bg-gray-50 text-gray-700 text-xs font-bold py-2 rounded border hover:bg-gray-100">
            EDITAR
          </button>
          <button class="flex-1 bg-arenas-red text-white text-xs font-bold py-2 rounded hover:bg-red-700">
            SELECCIONAR
          </button>
        </div>
      </div>
    </div>
  </div>
</template>