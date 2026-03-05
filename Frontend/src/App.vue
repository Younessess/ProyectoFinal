<script setup>
import Sidebar from './components/Sidebar.vue' // Importamos el componente
</script>

<template>
  <div class="flex h-screen bg-gray-100">
    
    <Sidebar />

    <main class="flex-1 overflow-y-auto p-8">
      <router-view></router-view>
    </main>
    
  </div>
</template>
<style scoped></style>