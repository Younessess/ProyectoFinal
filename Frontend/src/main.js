import { createApp } from 'vue'
import { createPinia } from 'pinia' // Importamos el creador de Pinia
import './style.css' 
import App from './App.vue'
import router from './router' // Importamos tu configuración de rutas

// Importamos el CSS donde pusiste el "truco" de @tailwind
import './style.css' 

const app = createApp(App)
const pinia = createPinia() // Creamos la instancia de Pinia

// Registramos los complementos en la aplicación
app.use(pinia)   // Activamos la gestión de estado global
app.use(router)  // Activamos el sistema de navegación

app.mount('#app') // Montamos la app en el DOM
