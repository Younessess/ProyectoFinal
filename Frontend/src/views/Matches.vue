<script setup>
import { onMounted, ref } from 'vue'
import { useMatchStore } from '../stores/matchStore'
import {useSeasonStore} from '../stores/seasonStore'
import NewMatch from '@/components/NewMatch.vue'
const matchStore = useMatchStore()
const seasonStore = useSeasonStore()

const showCreateDialog = ref(false)

const showMatchDialog = ref(false)
const matchDetailsLoading = ref(false)
const matchDetailsError = ref(null)
const selectedMatchDetails = ref(null)
const showAllPlayers = ref(false)

const API_BASE = 'http://localhost/futbol-analytics/Backend/public'

onMounted(() => matchStore.fetchMatches())

function openCreateDialog() {
  showCreateDialog.value = true
}

function closeCreateDialog() {
  showCreateDialog.value = false
}

async function openMatchDialog(match) {
  selectedMatchDetails.value = null
  matchDetailsError.value = null
  showAllPlayers.value = false
  showMatchDialog.value = true
  matchDetailsLoading.value = true

  try {
    const response = await fetch(`${API_BASE}/matches/${match.id_match}/details`)
    if (!response.ok) {
      throw new Error('Error al cargar los detalles del partido')
    }
    const data = await response.json()
    // Asegurar que si los datos llegan nulos se maneje
    if (!data || !data.match) {
      throw new Error('No existen datos para este partido')
    }
    selectedMatchDetails.value = data
  } catch (e) {
    matchDetailsError.value = 'No se han podido cargar los detalles del partido.'
  } finally {
    matchDetailsLoading.value = false
  }
}

function closeMatchDialog() {
  showMatchDialog.value = false
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
          <tr 
            v-for="match in matchStore.matches" 
            :key="match.id_match" 
            class="hover:bg-gray-50 cursor-pointer transition-colors"
            @click="openMatchDialog(match)"
          >
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

    <!-- Diálogo de Detalles del Partido -->
    <div
      v-if="showMatchDialog"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Cabecera -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-arenas-black to-gray-800">
          <div>
            <h3 class="text-xl font-bold text-white">
              Detalles del Partido
            </h3>
            <p v-if="selectedMatchDetails?.match" class="text-sm text-white/80">
              {{ selectedMatchDetails.match.competition }} · {{ new Date(selectedMatchDetails.match.date).toLocaleDateString() }}
            </p>
          </div>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-2xl font-bold leading-none"
            @click="closeMatchDialog"
          >
            &times;
          </button>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6 overflow-y-auto bg-gray-50">
          
          <div v-if="matchDetailsLoading" class="text-center py-6 text-sm text-gray-500 animate-pulse">
            Cargando detalles del partido...
          </div>

          <div v-else-if="matchDetailsError" class="text-center py-6 text-sm text-arenas-red">
            {{ matchDetailsError }}
          </div>

          <template v-else-if="selectedMatchDetails && selectedMatchDetails.match">
            <!-- Marcador resúmen -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-center gap-8">
              <div class="text-center flex-1">
                <span class="block text-2xl font-black text-arenas-black" :class="{'text-arenas-red': selectedMatchDetails.match.venue === 'home'}">
                  {{ selectedMatchDetails.match.venue === 'home' ? 'Arenas Club' : selectedMatchDetails.match.opponent }}
                </span>
                <span class="text-sm text-gray-500 uppercase">{{ selectedMatchDetails.match.venue === 'home' ? 'Local' : 'Visitante' }}</span>
              </div>
              <div class="text-center bg-gray-100 px-6 py-3 rounded-lg flex items-center justify-center gap-4">
                <span class="text-4xl font-black text-gray-800">
                  {{ selectedMatchDetails.match.venue === 'home' ? selectedMatchDetails.match.goals_for : selectedMatchDetails.match.goals_against }}
                </span>
                <span class="text-xl text-gray-400 font-bold">-</span>
                <span class="text-4xl font-black text-gray-800">
                  {{ selectedMatchDetails.match.venue === 'home' ? selectedMatchDetails.match.goals_against : selectedMatchDetails.match.goals_for }}
                </span>
              </div>
              <div class="text-center flex-1">
                <span class="block text-2xl font-black text-arenas-black" :class="{'text-arenas-red': selectedMatchDetails.match.venue === 'away'}">
                  {{ selectedMatchDetails.match.venue === 'away' ? 'Arenas Club' : selectedMatchDetails.match.opponent }}
                </span>
                <span class="text-sm text-gray-500 uppercase">{{ selectedMatchDetails.match.venue === 'away' ? 'Local' : 'Visitante' }}</span>
              </div>
            </div>

            <!-- Mejores Jugadores (Top 3) -->
            <div v-if="selectedMatchDetails.players && selectedMatchDetails.players.length > 0">
              <div class="flex items-center justify-between mb-4 mt-2">
                <h4 class="text-lg font-black text-arenas-black uppercase border-l-4 border-arenas-red pl-3">
                  Top 3 Jugadores del Partido
                </h4>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Mostramos solo los 3 primeros -->
                <div 
                  v-for="(player, index) in selectedMatchDetails.players.slice(0, 3)" 
                  :key="player.id_player"
                  class="bg-white rounded-xl shadow border border-gray-100 p-5 relative overflow-hidden transition-transform transform hover:-translate-y-1"
                >
                  <!-- Medalla top -->
                  <div class="absolute -right-6 -top-6 w-20 h-20 rounded-full flex items-center justify-center font-bold text-2xl"
                    :class="{
                      'bg-yellow-400/20 text-yellow-600': index === 0,
                      'bg-gray-400/20 text-gray-600': index === 1,
                      'bg-amber-700/20 text-amber-800': index === 2
                    }">
                    <span class="absolute bottom-4 left-4">#{{ index + 1 }}</span>
                  </div>

                  <div class="pr-8">
                    <p class="font-bold text-xl text-arenas-black">{{ player.first_name }} {{ player.last_name }}</p>
                    <p class="text-sm text-gray-500 mb-3">{{ player.evaluated_position || player.usual_position }}</p>
                    
                    <div class="flex items-end gap-2 mb-4">
                      <span class="text-4xl font-black text-green-600">{{ player.final_score ? player.final_score.toFixed(2) : '-' }}</span>
                      <span class="text-xs text-gray-400 font-bold uppercase mb-1">Puntos</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                       <div class="bg-gray-50 p-2 rounded">
                          <span class="block text-gray-400 font-bold mb-1">Ataque</span>
                          <span class="font-semibold text-gray-800">{{ player.attack_score ? player.attack_score.toFixed(1) : '-' }}</span>
                       </div>
                       <div class="bg-gray-50 p-2 rounded">
                          <span class="block text-gray-400 font-bold mb-1">Const</span>
                          <span class="font-semibold text-gray-800">{{ player.build_up_score ? player.build_up_score.toFixed(1) : '-' }}</span>
                       </div>
                       <div class="bg-gray-50 p-2 rounded">
                          <span class="block text-gray-400 font-bold mb-1">Defensa</span>
                          <span class="font-semibold text-gray-800">{{ player.defense_score ? player.defense_score.toFixed(1) : '-' }}</span>
                       </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botón Ver todos los jugadores -->
              <div class="text-center mt-6" v-if="selectedMatchDetails.players.length > 3">
                <button 
                  type="button" 
                  class="bg-gray-800 text-white px-6 py-2 rounded-full text-sm font-bold hover:bg-gray-700 transition shadow"
                  @click="showAllPlayers = !showAllPlayers"
                >
                  {{ showAllPlayers ? 'Ocultar listado completo' : 'Ver todos los jugadores (' + selectedMatchDetails.players.length + ')' }}
                </button>
              </div>

              <!-- Lista total de jugadores -->
              <div v-if="showAllPlayers" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
                <table class="w-full text-left text-sm">
                  <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                    <tr>
                      <th class="p-4">#</th>
                      <th class="p-4">Jugador</th>
                      <th class="p-4">Posición</th>
                      <th class="p-4 text-right">Mins</th>
                      <th class="p-4 text-right">Ata</th>
                      <th class="p-4 text-right">Con</th>
                      <th class="p-4 text-right">Def</th>
                      <th class="p-4 text-right text-arenas-black font-black">Punt. Final</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="(player, index) in selectedMatchDetails.players" :key="player.id_player" class="hover:bg-gray-50">
                      <td class="p-4 font-black" :class="{
                        'text-yellow-500': index === 0,
                        'text-gray-400': index === 1,
                        'text-amber-600': index === 2,
                        'text-gray-300': index > 2
                      }">{{ index + 1 }}</td>
                      <td class="p-4 font-semibold text-gray-800">{{ player.first_name }} {{ player.last_name }}</td>
                      <td class="p-4 text-gray-500">{{ player.evaluated_position || player.usual_position }}</td>
                      <td class="p-4 text-right text-gray-600">{{ player.minutes_played || '-' }}</td>
                      <td class="p-4 text-right text-gray-600">{{ player.attack_score ? player.attack_score.toFixed(2) : '-' }}</td>
                      <td class="p-4 text-right text-gray-600">{{ player.build_up_score ? player.build_up_score.toFixed(2) : '-' }}</td>
                      <td class="p-4 text-right text-gray-600">{{ player.defense_score ? player.defense_score.toFixed(2) : '-' }}</td>
                      <td class="p-4 text-right font-black text-green-600 text-base shadow-sm bg-green-50/30">
                        {{ player.final_score ? player.final_score.toFixed(2) : '-' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div v-else class="text-center py-6">
              <div class="bg-gray-100 text-gray-500 p-4 rounded-xl border border-gray-200 mx-auto max-w-sm">
                No hay valoraciones ni estadísticas registradas para este partido.
              </div>
            </div>
          </template>
        </div>

        <!-- Pie -->
        <div class="px-6 py-4 border-t bg-white flex justify-end">
          <button
            type="button"
            class="px-5 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold hover:bg-gray-200 transition"
            @click="closeMatchDialog"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>