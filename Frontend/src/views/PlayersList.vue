<script setup>
import { onMounted, ref } from 'vue'
import { usePlayerStore } from '../stores/playerStore'

const playerStore = usePlayerStore()

const showDialog = ref(false)
const dialogLoading = ref(false)
const dialogError = ref(null)
const selectedPlayer = ref(null)
const playerDetails = ref(null)

const showDeleteDialog = ref(false)
const deleteLoading = ref(false)
const deleteError = ref(null)
const playerToDelete = ref(null)

const showEditDialog = ref(false)
const editLoading = ref(false)
const editError = ref(null)
const editPlayerForm = ref({
  first_name: '',
  last_name: '',
  nickname: '',
  usual_position: '',
  status: 'active'
})

const API_BASE = 'http://localhost/futbol-analytics/Backend/public'

onMounted(() => {
  playerStore.fetchPlayers()
})

async function openPlayerDialog(player) {
  selectedPlayer.value = player
  dialogError.value = null
  playerDetails.value = null
  showDialog.value = true
  dialogLoading.value = true

  try {
    const response = await fetch(`${API_BASE}/players/${player.id_player}/details`)
    if (!response.ok) {
      throw new Error('Error al cargar la ficha del jugador')
    }
    playerDetails.value = await response.json()
  } catch (e) {
    dialogError.value = 'No se ha podido cargar la ficha del jugador.'
  } finally {
    dialogLoading.value = false
  }
}

function closeDialog() {
  showDialog.value = false
}

function openDeleteDialog(player) {
  playerToDelete.value = player
  deleteError.value = null
  showDeleteDialog.value = true
}

function closeDeleteDialog() {
  showDeleteDialog.value = false
}

async function confirmDelete() {
  if (!playerToDelete.value) return
  deleteLoading.value = true
  deleteError.value = null

  try {
    const response = await fetch(`${API_BASE}/players/${playerToDelete.value.id_player}`, {
      method: 'DELETE'
    })
    if (!response.ok) {
      throw new Error('Error al borrar el jugador')
    }

    // Eliminar el jugador del store sin recargar toda la lista
    playerStore.players = playerStore.players.filter(
      (p) => p.id_player !== playerToDelete.value.id_player
    )

    // Si el jugador eliminado estaba abierto en la ficha, la cerramos
    if (selectedPlayer.value && selectedPlayer.value.id_player === playerToDelete.value.id_player) {
      closeDialog()
    }

    closeDeleteDialog()
  } catch (e) {
    deleteError.value = 'No se ha podido borrar el jugador.'
  } finally {
    deleteLoading.value = false
  }
}

function openEditDialog(player) {
  selectedPlayer.value = player
  editError.value = null
  editPlayerForm.value = {
    first_name: player.first_name || '',
    last_name: player.last_name || '',
    nickname: player.nickname || '',
    usual_position: player.usual_position || '',
    status: player.status || 'active'
  }
  showEditDialog.value = true
}

function closeEditDialog() {
  showEditDialog.value = false
}

async function saveEdit() {
  if (!selectedPlayer.value) return
  editLoading.value = true
  editError.value = null

  try {
    const response = await fetch(`${API_BASE}/players/${selectedPlayer.value.id_player}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(editPlayerForm.value)
    })

    if (!response.ok) {
      throw new Error('Error al actualizar el jugador')
    }

    // Actualizar en el store
    playerStore.players = playerStore.players.map((p) =>
      p.id_player === selectedPlayer.value.id_player
        ? {
            ...p,
            ...editPlayerForm.value
          }
        : p
    )

    // Actualizar en los datos de detalle si están cargados
    if (playerDetails.value && playerDetails.value.player) {
      playerDetails.value.player = {
        ...playerDetails.value.player,
        ...editPlayerForm.value
      }
    }

    // Actualizar referencia seleccionada
    selectedPlayer.value = {
      ...selectedPlayer.value,
      ...editPlayerForm.value
    }

    closeEditDialog()
  } catch (e) {
    editError.value = 'No se han podido guardar los cambios.'
  } finally {
    editLoading.value = false
  }
}
</script>

<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold text-arenas-black mb-4 border-b-2 border-arenas-red inline-block pb-1">Lista de Jugadores</h2>
    
    <div v-if="playerStore.loading" class="animate-bounce text-arenas-red">Cargando equipo...</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="player in playerStore.players"
        :key="player.id_player"
        class="bg-white p-4 rounded shadow border-l-4 hover:shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer"
        :class="player.status === 'active' ? 'border-arenas-black' : 'border-arenas-red'"
        @click="openPlayerDialog(player)"
      >
        <div class="flex items-start justify-between mb-1">
          <div>
            <p class="font-bold text-lg text-arenas-black">
              {{ player.first_name }} {{ player.last_name }}
            </p>
            <p class="text-sm text-gray-500 italic">
              Dorsal: <span class="font-semibold">{{ player.squad_number || 'S/D' }}</span>
            </p>
          </div>
          <div class="flex flex-col items-end gap-1">
            <span
              class="px-2 py-0.5 rounded-full text-xs font-semibold"
              :class="player.status === 'active'
                ? 'bg-arenas-black text-white'
                : 'bg-arenas-red text-white'"
            >
              {{ player.status === 'active' ? 'Activo' : 'Lesionado / Baja' }}
            </span>
            <button
              type="button"
              class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border border-arenas-black text-arenas-black hover:bg-arenas-black hover:text-white"
              @click.stop="openEditDialog(player)"
            >
              Editar
            </button>
            <button
              type="button"
              class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-arenas-red text-white hover:opacity-90"
              @click.stop="openDeleteDialog(player)"
            >
              Borrar
            </button>
          </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">
          Click en la tarjeta para ver la ficha completa.
        </p>
      </div>
    </div>

    <!-- Diálogo de ficha de jugador -->
    <div
      v-if="showDialog"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Cabecera -->
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-arenas-black to-arenas-red">
          <div>
            <h3 class="text-xl font-bold text-white">
              {{ selectedPlayer?.first_name }} {{ selectedPlayer?.last_name }}
            </h3>
            <p class="text-sm text-white/80">
              Dorsal {{ playerDetails?.player?.squad_number ?? selectedPlayer?.squad_number ?? 'S/D' }}
              · Posición habitual:
              <span class="font-semibold">
                {{ playerDetails?.player?.usual_position ?? selectedPlayer?.usual_position ?? '—' }}
              </span>
            </p>
          </div>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-xl font-bold"
            @click="closeDialog"
          >
            ×
          </button>
        </div>

        <!-- Contenido -->
        <div class="p-6 space-y-6 overflow-y-auto">
          <!-- Estados de carga / error -->
          <div v-if="dialogLoading" class="text-center py-6 text-sm text-gray-500">
            Cargando ficha del jugador...
          </div>

          <div v-else-if="dialogError" class="text-center py-6 text-sm text-arenas-red">
            {{ dialogError }}
          </div>

          <template v-else-if="playerDetails">
            <!-- Datos básicos -->
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-arenas-red">
                <h4 class="text-sm font-semibold text-arenas-black mb-2">Datos básicos</h4>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Nombre:</span>
                  {{ playerDetails.player.first_name }} {{ playerDetails.player.last_name }}
                </p>
                <p class="text-sm text-gray-600" v-if="playerDetails.player.nickname">
                  <span class="font-medium">Alias:</span>
                  {{ playerDetails.player.nickname }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Posición habitual:</span>
                  {{ playerDetails.player.usual_position || '—' }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Estado:</span>
                  {{ playerDetails.player.status }}
                </p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-arenas-black mb-2">Temporada actual</h4>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Temporada:</span>
                  {{ playerDetails.player.season_name || '—' }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">Dorsal:</span>
                  {{ playerDetails.player.squad_number ?? 'S/D' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                  Dorsal asociado a la temporada activa.
                </p>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-arenas-black mb-2">Lesión activa</h4>
                <div v-if="playerDetails.injuries.active" class="space-y-1 text-sm text-gray-600">
                  <p>
                    <span class="font-medium">Tipo:</span>
                    {{ playerDetails.injuries.active.injury_type }}
                  </p>
                  <p>
                    <span class="font-medium">Severidad:</span>
                    {{ playerDetails.injuries.active.severity }}
                  </p>
                  <p>
                    <span class="font-medium">Inicio:</span>
                    {{ playerDetails.injuries.active.start_date }}
                  </p>
                  <p>
                    <span class="font-medium">Retorno previsto:</span>
                    {{ playerDetails.injuries.active.expected_return_date || '—' }}
                  </p>
                </div>
                <p v-else class="text-sm text-gray-500 italic">
                  No hay lesión activa registrada.
                </p>
              </div>
            </section>

            <!-- Historial de partidos -->
            <section>
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-semibold text-arenas-black">
                  Historial de partidos jugados
                </h4>
                <span class="text-xs text-gray-500">
                  {{ playerDetails.matches.length }} partidos
                </span>
              </div>

              <div v-if="playerDetails.matches.length" class="overflow-x-auto rounded-lg border">
                <table class="min-w-full text-left text-xs">
                  <thead class="bg-gray-100 text-gray-700 uppercase tracking-wide">
                    <tr>
                      <th class="px-3 py-2">Fecha</th>
                      <th class="px-3 py-2">Competición</th>
                      <th class="px-3 py-2">Rival</th>
                      <th class="px-3 py-2">Lugar</th>
                      <th class="px-3 py-2 text-right">Minutos</th>
                      <th class="px-3 py-2 text-right">Puntuación</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr
                      v-for="match in playerDetails.matches"
                      :key="match.id_match"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-3 py-2 text-gray-700">{{ match.date }}</td>
                      <td class="px-3 py-2 text-gray-700">{{ match.competition }}</td>
                      <td class="px-3 py-2 text-gray-700">{{ match.opponent }}</td>
                      <td class="px-3 py-2 text-gray-700 capitalize">{{ match.venue }}</td>
                      <td class="px-3 py-2 text-right text-gray-700">
                        {{ match.minutes_played ?? '-' }}
                      </td>
                      <td class="px-3 py-2 text-right font-semibold text-gray-900">
                        {{ match.final_score != null ? match.final_score.toFixed(2) : '-' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-sm text-gray-500 italic">
                Aún no hay partidos registrados para este jugador.
              </p>
            </section>

            <!-- Puntuación por posición evaluada -->
            <section>
              <h4 class="text-sm font-semibold text-arenas-black mb-2">
                Puntuación por posición evaluada
              </h4>

              <div v-if="playerDetails.scores_by_position.length" class="overflow-x-auto rounded-lg border">
                <table class="min-w-full text-left text-xs">
                  <thead class="bg-gray-100 text-gray-700 uppercase tracking-wide">
                    <tr>
                      <th class="px-3 py-2">Posición</th>
                      <th class="px-3 py-2 text-right">Partidos</th>
                      <th class="px-3 py-2 text-right">Punt. final</th>
                      <th class="px-3 py-2 text-right">Ataque</th>
                      <th class="px-3 py-2 text-right">Construcción</th>
                      <th class="px-3 py-2 text-right">Defensa</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100">
                    <tr
                      v-for="score in playerDetails.scores_by_position"
                      :key="score.evaluated_position"
                      class="hover:bg-gray-50"
                    >
                      <td class="px-3 py-2 text-gray-700">
                        {{ score.evaluated_position }}
                      </td>
                      <td class="px-3 py-2 text-right text-gray-700">
                        {{ score.matches_count }}
                      </td>
                      <td class="px-3 py-2 text-right font-semibold text-gray-900">
                        {{ score.avg_final_score.toFixed(2) }}
                      </td>
                      <td class="px-3 py-2 text-right text-gray-700">
                        {{ score.avg_attack_score.toFixed(2) }}
                      </td>
                      <td class="px-3 py-2 text-right text-gray-700">
                        {{ score.avg_build_up_score.toFixed(2) }}
                      </td>
                      <td class="px-3 py-2 text-right text-gray-700">
                        {{ score.avg_defense_score.toFixed(2) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-else class="text-sm text-gray-500 italic">
                Aún no hay puntuaciones calculadas para este jugador.
              </p>
            </section>

            <!-- Historial de lesiones -->
            <section>
              <h4 class="text-sm font-semibold text-arenas-black mb-2">
                Historial de lesiones
              </h4>

              <div v-if="playerDetails.injuries.history.length" class="space-y-2 text-sm text-gray-600">
                <div
                  v-for="injury in playerDetails.injuries.history"
                  :key="injury.id_injury"
                  class="border rounded-lg px-3 py-2 bg-gray-50"
                >
                  <p class="font-medium">
                    {{ injury.injury_type }} · {{ injury.severity }}
                  </p>
                  <p>
                    Desde {{ injury.start_date }}
                    <span v-if="injury.end_date">hasta {{ injury.end_date }}</span>
                    <span v-else>(en curso)</span>
                  </p>
                  <p v-if="injury.expected_return_date">
                    Retorno previsto: {{ injury.expected_return_date }}
                  </p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500 italic">
                No hay historial de lesiones registrado para este jugador.
              </p>
            </section>
          </template>
        </div>

        <!-- Pie del diálogo -->
        <div class="px-6 py-3 border-t bg-gray-50 flex justify-end">
          <button
            type="button"
            class="px-4 py-2 rounded-md bg-arenas-red text-white text-sm font-semibold hover:opacity-90 transition"
            @click="closeDialog"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>

    <!-- Diálogo de confirmación de borrado -->
    <div
      v-if="showDeleteDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">
            Confirmar borrado
          </h3>
        </div>
        <div class="px-5 py-4 space-y-3">
          <p class="text-sm text-gray-700">
            ¿Seguro que quieres borrar al jugador
            <span class="font-semibold">
              {{ playerToDelete?.first_name }} {{ playerToDelete?.last_name }}
            </span>
            ?
          </p>
          <p class="text-xs text-gray-500">
            Esta acción eliminará al jugador de la base de datos. Asegúrate de que no lo necesitas
            para futuras estadísticas.
          </p>

          <p v-if="deleteError" class="text-sm text-arenas-red">
            {{ deleteError }}
          </p>
        </div>
        <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100"
            @click="closeDeleteDialog"
            :disabled="deleteLoading"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="px-3 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90 disabled:opacity-60"
            @click="confirmDelete"
            :disabled="deleteLoading"
          >
            {{ deleteLoading ? 'Borrando...' : 'Borrar jugador' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Diálogo de edición de jugador -->
    <div
      v-if="showEditDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">
            Editar jugador
          </h3>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-xl font-bold"
            @click="closeEditDialog"
          >
            ×
          </button>
        </div>
        <form @submit.prevent="saveEdit">
          <div class="px-5 py-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">Nombre</label>
                <input
                  v-model="editPlayerForm.first_name"
                  type="text"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">Apellidos</label>
                <input
                  v-model="editPlayerForm.last_name"
                  type="text"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  required
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">Alias (opcional)</label>
              <input
                v-model="editPlayerForm.nickname"
                type="text"
                class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">
                  Posición habitual
                </label>
                <input
                  v-model="editPlayerForm.usual_position"
                  type="text"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  placeholder="Ej: CB, CM, RW..."
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">Estado</label>
                <select
                  v-model="editPlayerForm.status"
                  class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                >
                  <option value="active">Activo</option>
                  <option value="injured">Lesionado</option>
                  <option value="retired">Baja</option>
                </select>
              </div>
            </div>

            <p v-if="editError" class="text-sm text-arenas-red">
              {{ editError }}
            </p>
          </div>

          <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
            <button
              type="button"
              class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100"
              @click="closeEditDialog"
              :disabled="editLoading"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-3 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90 disabled:opacity-60"
              :disabled="editLoading"
            >
              {{ editLoading ? 'Guardando...' : 'Guardar cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>