<script setup>
import { ref } from 'vue'

// Estado para controlar si el menú móvil está abierto
const isOpen = ref(false)

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}
</script>

<template>
  <button 
    @click="toggleMenu"
    class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-black text-white rounded-lg shadow-lg border border-red-600"
  >
    <svg v-if="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
  </button>

  <div 
    v-if="isOpen" 
    @click="isOpen = false" 
    class="lg:hidden fixed inset-0 bg-black/50 z-40 backdrop-blur-sm"
  ></div>

  <aside 
    :class="[
      'fixed inset-y-0 left-0 z-40 w-64 bg-black text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
      isOpen ? 'translate-x-0' : '-translate-x-full'
    ]"
  >
    <div class="p-6 flex flex-col items-center border-b border-gray-800">
      <img src="../assets/logo.png" alt="Logo Arenas Club" 
           class="w-16 h-16 mb-2 rounded-full border-2 border-red-600 shadow-lg bg-white object-contain">
      <h1 class="text-lg font-black text-red-600 uppercase">Arenas Club</h1>
    </div>

    <nav class="flex-1 mt-4 px-4 space-y-1 overflow-y-auto">
      <router-link @click="isOpen = false" to="/dashboard" class="nav-link" active-class="bg-red-600">
        <span>Dashboard</span>
      </router-link>
      <router-link @click="isOpen = false" to="/players" class="nav-link" active-class="bg-red-600">
        <span>Jugadores</span>
      </router-link>
      <router-link @click="isOpen = false" to="/matches" class="nav-link" active-class="bg-red-600">
        <span>Partidos</span>
      </router-link>
      <router-link @click="isOpen = false" to="/injuries" class="nav-link" active-class="bg-red-600">
        <span>Lesiones</span>
      </router-link>
      <router-link @click="isOpen = false" to="/seasons" class="nav-link" active-class="bg-red-600">
        <span>Administración</span>
      </router-link>
    </nav>

    <div class="p-4 bg-zinc-900 border-t border-gray-800">
      <p class="text-xs font-bold">Admin</p>
      <button class="text-[10px] text-gray-500 hover:text-red-500 uppercase mt-1">Cerrar Sesión</button>
    </div>
  </aside>
</template>

<style scoped>
@import "tailwindcss"; /* Para que funcione @apply en v4 [postcss] tailwindcss: ...] */

.nav-link {
  @apply flex items-center p-3 rounded-lg transition-colors hover:bg-red-700 font-bold text-xs uppercase tracking-wider;
}
</style>