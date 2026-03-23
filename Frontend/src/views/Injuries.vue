<script setup>
import { onMounted } from 'vue'
import { ref } from 'vue'
import { useInjuryStore } from '../stores/injuryStore'
import { usePlayerStore } from '../stores/playerStore'

const injuryStore = useInjuryStore()
const playerStore = usePlayerStore()

const showRegisterDialog = ref(false)
const registerError = ref('')
const isSubmitting = ref(false)

const newInjuryForm = ref({
  id_player: '',
  start_date: new Date().toISOString().split('T')[0],
  injury_type: '',
  severity: 'Leve',
  expected_return_date: '',
  observations: ''
})

function openRegisterDialog() {
  newInjuryForm.value = {
    id_player: '',
    start_date: new Date().toISOString().split('T')[0],
    injury_type: '',
    severity: 'Leve',
    expected_return_date: '',
    observations: ''
  }
  registerError.value = ''
  showRegisterDialog.value = true
  if (playerStore.players.length === 0) {
    playerStore.fetchPlayers()
  }
}

function closeRegisterDialog() {
  showRegisterDialog.value = false
}

async function handleRegisterInjury() {
  if (!newInjuryForm.value.id_player || !newInjuryForm.value.start_date || !newInjuryForm.value.injury_type) {
    registerError.value = 'Por favor, selecciona un jugador y un tipo de lesión.'
    return
  }
  isSubmitting.value = true
  registerError.value = ''
  try {
    await injuryStore.registerInjury(newInjuryForm.value)
    closeRegisterDialog()
  } catch(e) {
    registerError.value = e.message
  } finally {
    isSubmitting.value = false
  }
}

const injuryDetails = ref({
  id_injury: "",
  id_player: "",
  end_date: new Date().toISOString().split('T')[0]
});

const showCloseDialog = ref(false)
const closeError = ref('')
const isClosing = ref(false)

function openCloseDialog(id_injury, id_player){
  injuryDetails.value.id_injury = id_injury;
  injuryDetails.value.id_player = id_player;
  closeError.value = '';
  showCloseDialog.value = true;
}

function cancelCloseDialog() {
  showCloseDialog.value = false;
}

async function confirmCloseInjury() {
  isClosing.value = true
  closeError.value = ''
  try {
    await injuryStore.closeInjury(injuryDetails.value)
    showCloseDialog.value = false
  } catch(e) {
    closeError.value = e.message
  } finally {
    isClosing.value = false
  }
}
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
      <button 
        class="bg-arenas-red text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-red-700 transition shadow-lg"
        @click="openRegisterDialog"
      >
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
                <p class="font-bold text-gray-900">{{ injury.name_player }}</p>
              </td>
              <td class="p-4 text-sm text-gray-600">{{ injury.injury_type }}  : {{ injury.observations }}</td>
              <td class="p-4 text-xs font-mono text-gray-500">{{ injury.start_date }}</td>
              <td class="p-4 text-xs font-mono text-gray-400 italic">
                {{ injury.expected_return_date || 'Sin definir' }}
              </td>
              <td class="p-4 text-center">
                <span 
                  :class="!injury.end_date ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                  class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter"
                >
                  {{ !injury.end_date ? 'Baja Activa' : 'Alta Médica' }}
                </span>
                <button
                type="button"
                class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border border-arenas-black text-arenas-black hover:bg-arenas-black hover:text-white cursor-pointer disabled:opacity-50"
                @click.prevent="openCloseDialog(injury.id_injury, injury.id_player)"
                :disabled="injury.end_date"
                >
                Cerrar lesión
                </button>
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

    <!-- Modal para Registrar Baja Médica -->
    <div
      v-if="showRegisterDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">Registrar nueva baja médica</h3>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-xl font-bold"
            @click="closeRegisterDialog"
          >
            ×
          </button>
        </div>
        <form @submit.prevent="handleRegisterInjury">
          <div class="px-5 py-6 space-y-4">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Jugador afectado *</label>
                <select 
                  v-model="newInjuryForm.id_player" 
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
                  required
                >
                  <option value="" disabled>Selecciona el jugador</option>
                  <option 
                    v-for="player in playerStore.players" 
                    :key="player.id_player" 
                    :value="player.id_player"
                  >
                    {{ player.first_name }} {{ player.last_name }} 
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Fecha de Baja *</label>
                <input 
                  type="date"
                  v-model="newInjuryForm.start_date"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
                  required
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tipo de Lesión *</label>
                <input 
                  type="text"
                  v-model="newInjuryForm.injury_type"
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
                  placeholder="Ej: Rotura Fibrilar..."
                  required
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Gravedad</label>
                <select 
                  v-model="newInjuryForm.severity" 
                  class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
                >
                  <option value="Leve">Leve</option>
                  <option value="Moderada">Moderada</option>
                  <option value="Grave">Grave</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Fecha Prevista de Alta (Opcional)</label>
              <input 
                type="date"
                v-model="newInjuryForm.expected_return_date"
                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red"
              />
            </div>
            
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Observaciones</label>
              <textarea 
                v-model="newInjuryForm.observations"
                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red min-h-[80px]"
                placeholder="Detalles sobre la lesión e intervención..."
              ></textarea>
            </div>

            <p v-if="registerError" class="text-sm text-arenas-red font-semibold mt-2">
              {{ registerError }}
            </p>
          </div>

          <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
            <button
              type="button"
              class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100"
              @click="closeRegisterDialog"
              :disabled="isSubmitting"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-4 py-1.5 rounded-md text-sm font-semibold bg-arenas-red text-white hover:opacity-90 disabled:opacity-60"
              :disabled="isSubmitting"
            >
              {{ isSubmitting ? 'Registrando...' : 'Guardar Baja Médica' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para Confirmar Alta Médica -->
    <div
      v-if="showCloseDialog"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between bg-arenas-black">
          <h3 class="text-lg font-semibold text-white">Confirmar Alta Médica</h3>
          <button
            type="button"
            class="text-white hover:text-gray-200 text-xl font-bold"
            @click="cancelCloseDialog"
          >
            ×
          </button>
        </div>
        <div class="px-5 py-6 space-y-4 text-center">
          <p class="text-sm text-gray-700">
            ¿Estás seguro de que quieres dar el alta médica a este jugador? Esto marcará la lesión como cerrada en el historial.
          </p>
          <div class="text-left mt-4 mx-auto max-w-[200px]">
             <label class="block text-xs font-semibold text-gray-500 mb-1">Fecha de Alta Real</label>
             <input type="date" v-model="injuryDetails.end_date" class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-arenas-red" />
          </div>

          <p v-if="closeError" class="text-sm text-arenas-red font-semibold mt-2">
            {{ closeError }}
          </p>
        </div>

        <div class="px-5 py-3 border-t bg-gray-50 flex justify-end gap-2">
          <button
            type="button"
            class="px-3 py-1.5 rounded-md text-sm font-semibold text-arenas-black hover:bg-gray-100"
            @click="cancelCloseDialog"
            :disabled="isClosing"
          >
            Cancelar
          </button>
          <button
            type="button"
            class="px-4 py-1.5 rounded-md text-sm font-semibold bg-green-600 text-white hover:bg-green-700 disabled:opacity-60"
            @click="confirmCloseInjury"
            :disabled="isClosing"
          >
            {{ isClosing ? 'Cerrando...' : 'Confirmar Alta' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>