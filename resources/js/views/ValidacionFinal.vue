<template>
  <Header :titulo="tituloPagina" />

  <div class="flex">
    <main class="flex-1 p-8">
      <h1 class="text-3xl font-bold mb-6">Validación de Secuencias</h1>

      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-2 bg-white shadow p-3 rounded h-[750px] overflow-auto">
          <h3 class="font-semibold mb-3 text-center">Páginas</h3>
          <div
            v-for="n in 8"
            :key="n"
            class="mb-4 cursor-pointer border rounded overflow-hidden hover:ring-2 hover:ring-teal-600 animate-fade-in transition-transform transform hover:scale-105"
            @click="paginaActual = n"
          >
            <div class="h-32 bg-gray-200 flex items-center justify-center text-gray-400 text-sm">
              Página {{ n }}
            </div>
          </div>
        </div>

        <div class="col-span-7 bg-white rounded shadow p-4">
          <div class="flex items-center gap-3 mb-4">
            <select class="border rounded px-3 py-2 w-60" v-model="archivoSeleccionado">
              <option value="Cuatrho_Semestre_2023.pdf">Cuatrho_Semestre_2023.pdf</option>
              <option value="Secuencia_2.pdf">Secuencia_2.pdf</option>
            </select>
            <a :href="rutaPDF" download class="px-3 py-2 bg-teal-700 text-white rounded hover:bg-teal-800 transition">
              Descargar PDF
            </a>
          </div>

          <div class="flex items-center gap-3 mb-4">
            <button class="px-2 py-1 border rounded">➖</button>
            <button class="px-2 py-1 border rounded">100%</button>
            <button class="px-2 py-1 border rounded">➕</button>
          </div>

          <div class="border h-[550px] flex items-center justify-center text-gray-400 relative">
            <div class="absolute top-3 left-3 font-bold">Página {{ paginaActual }}</div>
            <svg class="w-full h-full absolute top-0 left-0 opacity-40">
              <line x1="0" y1="0" x2="100%" y2="100%" stroke="gray" stroke-width="2" />
              <line x1="100%" y1="0" x2="0" y2="100%" stroke="gray" stroke-width="2" />
            </svg>
            <span>Vista previa del PDF (Mock)</span>
          </div>
        </div>

        <div class="col-span-3 bg-white rounded shadow p-4">
          <h2 class="font-bold text-lg mb-3">Detalles de la Secuencia</h2>

          <div class="mb-3">
            <p class="font-semibold">Título:</p>
            <p>Introducción a la Física Cuántica para Bachillerato</p>
          </div>

          <div class="mb-3">
            <p class="font-semibold">Autor:</p>
            <p>Mtro. Juan Pérez</p>
          </div>

          <div class="mb-3">
            <p class="font-semibold">Fecha:</p>
            <p>12/08/2025</p>
          </div>

          <div class="mb-3">
            <span class="px-3 py-1 text-sm rounded bg-yellow-400 text-black font-semibold">En Revisión</span>
          </div>

          <h3 class="font-semibold mt-4 mb-2">Comentario:</h3>
          <textarea
            v-model="comentario"
            class="w-full border rounded p-2 h-24"
            placeholder="Escribe tu comentario…"
          ></textarea>
          <button
            @click="agregarComentario"
            class="bg-teal-700 text-white px-4 py-2 rounded mt-2 hover:bg-teal-800 w-full transition"
          >
            Agregar Comentario
          </button>

          <h3 class="font-semibold mt-4 mb-2">Historial de Comentarios</h3>
          <div class="border rounded p-2 h-40 overflow-auto text-sm">
            <div v-for="(c, i) in historial" :key="i" class="mb-3 border-b pb-2">
              <div class="font-semibold">{{ c.fecha }}</div>
              <p>{{ c.texto }}</p>
            </div>
          </div>

          <div class="flex flex-col gap-2 mt-6">
            <button class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 w-full transition">Cancelar</button>
            <button
              @click="abrirModalValidar = true"
              class="px-4 py-2 bg-teal-700 text-white rounded hover:bg-teal-800 w-full transition"
            >
              Validar Secuencia
            </button>
            <button
              @click="abrirModalDenegar = true"
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 w-full transition"
            >
              Denegar Secuencia
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>

  <div v-if="abrirModalValidar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 animate-pop">
      <h2 class="text-lg font-bold mb-4">Confirmación</h2>
      <p class="mb-6">¿Seguro que deseas validar esta secuencia?</p>
      <div class="flex justify-end gap-3">
        <button @click="abrirModalValidar = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
          Cancelar
        </button>
        <button @click="validarSecuencia" class="px-4 py-2 bg-teal-700 text-white rounded hover:bg-teal-800 transition">
          Validar
        </button>
      </div>
    </div>
  </div>

  <div v-if="abrirModalDenegar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow-lg w-96 animate-pop">
      <h2 class="text-lg font-bold mb-4">Confirmación</h2>
      <p class="mb-6">¿Seguro que deseas denegar esta secuencia?</p>
      <div class="flex justify-end gap-3">
        <button @click="abrirModalDenegar = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">
          Cancelar
        </button>
        <button @click="denegarSecuencia" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
          Denegar
        </button>
      </div>
    </div>
  </div>

  <transition name="fade">
    <div v-if="mostrarMensaje" class="fixed bottom-6 right-6 p-4 rounded shadow-lg text-center animate-pop" :class="mensajeColor">
      {{ mensajeTexto }}
    </div>
  </transition>
</template>

<script setup>
import Header from "@/components/Header.vue"
import { ref, computed } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()
const tituloPagina = { text: "Validación de Secuencias", links: [] }

const comentario = ref("")
const paginaActual = ref(1)
const archivoSeleccionado = ref("Cuatrho_Semestre_2023.pdf")
const historial = ref([
  { fecha: "24/11/2025", texto: "Revisar objetivos de aprendizaje." },
  { fecha: "24/11/2025", texto: "Corregir formato APA." }
])

const abrirModalValidar = ref(false)
const abrirModalDenegar = ref(false)

const mostrarMensaje = ref(false)
const mensajeTexto = ref("")
const mensajeColor = ref("")

const rutaPDF = computed(() => `/pdfs/${archivoSeleccionado.value}`)

function agregarComentario() {
  if (!comentario.value.trim()) return
  historial.value.unshift({
    fecha: new Date().toLocaleDateString(),
    texto: comentario.value
  })
  comentario.value = ""
}

function validarSecuencia() {
  abrirModalValidar.value = false
  mensajeTexto.value = "✔ Secuencia validada correctamente… "
  mensajeColor.value = "bg-green-100 text-green-700 border border-green-300"
  mostrarMensaje.value = true
  setTimeout(() => {
    mostrarMensaje.value = false
    router.push("/validaciones")
  }, 1500)
}

function denegarSecuencia() {
  abrirModalDenegar.value = false
  mensajeTexto.value = "✖ Secuencia denegada… redirigiendo a Reportes"
  mensajeColor.value = "bg-red-100 text-red-700 border border-red-300"
  mostrarMensaje.value = true
  setTimeout(() => {
    mostrarMensaje.value = false
    router.push("/reportes")
  }, 1500)
}
</script>

<style scoped>
@keyframes pop {
  0% { transform: scale(0.7); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}
.animate-pop { animation: pop 0.2s ease-out; }

@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(5px); }
  100% { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fadeIn 0.3s ease-out; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
