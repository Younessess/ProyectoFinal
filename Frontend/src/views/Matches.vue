<script setup>
import { onMounted, ref } from 'vue'
import { useMatchStore } from '../stores/matchStore'

const matchStore = useMatchStore()
const API_BASE = 'http://localhost/futbol-analytics/Backend/public'

const showImportDialog = ref(false)
const importLoading = ref(false)
const importError = ref(null)

const form = ref({
  date: '',
  competition: '',
  opponent: '',
  venue: 'home'
})

const file = ref(null)
const fileName = ref('Ningún archivo seleccionado')

onMounted(() => matchStore.fetchMatches())

function openImportDialog() {
  showImportDialog.value = true
  importError.value = null
  importLoading.value = false
  form.value = {
    date: '',
    competition: '',
    opponent: '',
    venue: 'home'
  }
  file.value = null
}

function closeImportDialog() {
  showImportDialog.value = false
}

function handleFileChange(event) {
  const files = event.target.files
  file.value = files && files[0] ? files[0] : null
  fileName.value = file.value ? file.value.name : 'Ningún archivo seleccionado'
}

async function submitImport() {
  if (!file.value) {
    importError.value = 'Selecciona un archivo Excel antes de continuar.'
    return
  }

  importLoading.value = true
  importError.value = null

  try {
    // 1) Crear el partido
    const matchPayload = {
      id_season: 1, // por ahora, temporada activa fija
      date: form.value.date,
      competition: form.value.competition,
      opponent: form.value.opponent,
      venue: form.value.venue,
      goals_for: 0,
      goals_against: 0
    }

    const createRes = await fetch(`${API_BASE}/matches`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(matchPayload)
    })

    if (!createRes.ok) {
      throw new Error('Error al crear el partido')
    }

    const created = await createRes.json()
    const matchId = created.id_match

    if (!matchId) {
      throw new Error('El servidor no devolvió el ID del partido')
    }

    // 2) Enviar el archivo para importar estadísticas sin procesar
    const formData = new FormData()
    formData.append('file', file.value)

    const importRes = await fetch(`${API_BASE}/matches/${matchId}/import-stats`, {
      method: 'POST',
      body: formData
    })

    if (!importRes.ok) {
      throw new Error('Error al importar el archivo de estadísticas')
    }

    // Refrescar la lista de partidos y cerrar el diálogo
    await matchStore.fetchMatches()
    closeImportDialog()
  } catch (e) {
    importError.value = e.message || 'Ha ocurrido un error al registrar el partido.'
  } finally {
    importLoading.value = false
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
        @click="openImportDialog"
      >
        <span class="text-lg">＋</span>
        REGISTRAR PARTIDO
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
            <td class="p-4 text-sm font-medium">
              {{ new Date(match.date).toLocaleDateString() }}
            </td>
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
                :class="match.goals_for > match.goals_against
                  ? 'text-green-600'
                  : match.goals_for < match.goals_against
                    ? 'text-arenas-red'
                    : 'text-arenas-black'"
              >
                {{ match.goals_for }} - {{ match.goals_against }}
              </span>
            </td>
            <td class="p-4 text-xs text-gray-500 uppercase font-bold">
              {{ match.venue === 'home' ? 'Gobela' : 'Visitante' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Diálogo para registrar partido e importar Excel -->
    <div
      v-if="showImportDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-xl mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">
            Registrar partido e importar Excel
          </h3>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-xl font-bold"
            @click="closeImportDialog"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="submitImport">
          <div class="px-5 py-4 space-y-4">
            <p class="text-xs text-gray-500">
              Primero se registrará el partido con los datos básicos y después se subirá el archivo Excel
              con las estadísticas sin procesar.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">
                  Fecha y hora
                </label>
                <input
                  v-model="form.date"
                  type="datetime-local"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">
                  Competición
                </label>
                <input
                  v-model="form.competition"
                  type="text"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  placeholder="Ej: La Liga"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">
                  Rival
                </label>
                <input
                  v-model="form.opponent"
                  type="text"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  placeholder="Ej: Real Madrid"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-arenas-black mb-1">
                  Campo
                </label>
                <select
                  v-model="form.venue"
                  class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-arenas-red focus:border-arenas-red"
                  required
                >
                  <option value="home">Casa</option>
                  <option value="away">Fuera</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-arenas-black mb-1">
                Archivo Excel del partido
              </label>
              <div class="flex items-center gap-3">
                <label
                  class="inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold bg-arenas-red text-white cursor-pointer hover:opacity-90"
                >
                  Seleccionar archivo
                  <input
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="hidden"
                    @change="handleFileChange"
                  />
                </label>
                <span class="text-xs text-gray-600 truncate max-w-[220px]">
                  {{ fileName }}
                </span>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Se importarán los datos sin procesar; el cálculo de puntuaciones se hará en un paso posterior.
              </p>
            </div>

            <p v-if="importError" class="text-sm text-arenas-red">
              {{ importError }}
            </p>
          </div>

          <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
            <button
              type="button"
              class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100"
              @click="closeImportDialog"
              :disabled="importLoading"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-3 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90 disabled:opacity-60"
              :disabled="importLoading"
            >
              {{ importLoading ? 'Registrando...' : 'Registrar e importar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>