<script setup>
import { onMounted } from 'vue'
import { useInjuryStore } from '../stores/injuryStore'

const injuryStore = useInjuryStore()

onMounted(() => {
  injuryStore.fetchInjuries()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <h2 class="text-2xl font-black text-gray-800 uppercase border-l-4 border-arenas-red pl-3">
        Control de Enfermería
      </h2>
      <button class="bg-arenas-red text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-red-700 transition shadow-lg">
        + REGISTRAR BAJA MÉDICA
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-arenas-black text-white text-xs uppercase tracking-widest">
            <tr>
              <th class="p-4">Jugador</th>
              <th class="p-4">Tipo de Lesión</th>
              <th class="p-4">Fecha Baja</th>
              <th class="p-4">Previsto Alta</th>
              <th class="p-4 text-center">Estado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="injuryStore.loading" class="animate-pulse">
              <td colspan="5" class="p-8 text-center text-gray-400">Consultando archivos médicos...</td>
            </tr>
            
            <tr v-for="injury in injuryStore.injuries" :key="injury.id_injury" class="hover:bg-gray-50 transition">
              <td class="p-4">
                <p class="font-bold text-gray-900">{{ injury.id_player }} {{ injury.name_player }}</p>
              </td>
              <td class="p-4 text-sm text-gray-600">{{ injury.injury_type }}  : {{ injury.observations }}</td>
              <td class="p-4 text-xs font-mono text-gray-500">{{ injury.start_date }}</td>
              <td class="p-4 text-xs font-mono text-gray-400 italic">
                {{ injury.expected_return_date || 'Sin definir' }}
              </td>
              <td class="p-4 text-center">
                <span 
                  :class="injury.is_active ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                  class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter"
                >
                  {{ injury.is_active ? 'Baja Activa' : 'Alta Médica' }}
                </span>
              </td>
            </tr>

            <tr v-if="!injuryStore.loading && injuryStore.injuries.length === 0">
              <td colspan="5" class="p-10 text-center text-gray-400 italic">
                No hay registros de lesiones en la temporada actual.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>