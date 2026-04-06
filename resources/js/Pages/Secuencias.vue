<template>
  <Header :titulo="tituloPagina" />

  <main class="w-full p-10">


    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Mis Secuencias Didácticas</h1>

      <div class="flex items-center gap-3">

        <span class="text-sm text-gray-700 font-medium">Ordenar por:</span>

        <select
          v-model="orden"
          class="border rounded-lg px-3 py-2 text-sm focus:ring-teal-600 focus:border-teal-600 bg-white"
        >
          <option value="modificacion">Última Modificación</option>
          <option value="nombre">Nombre</option>
          <option value="estado">Estado</option>
        </select>

        

      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

      <div
        v-for="s in secuenciasOrdenadas"
        :key="s.id"
        class="border rounded-xl bg-white p-5 shadow-sm hover:shadow-md transition relative min-h-[250px]"
      >

        <span
          class="absolute top-4 right-4 px-2 py-1 text-xs font-semibold rounded"
          :class="badgeColor(s.estado)"
        >
          {{ s.estado }}
        </span>


        <h2 class="font-semibold text-lg leading-tight mb-4 pr-20 pt-2">
          {{ s.nombre }}
        </h2>


        <div class="text-sm text-gray-700 space-y-2">

          <p class="flex items-center gap-2">
            <Book class="w-4 h-4 text-gray-600" />
            <span><strong>Materia:</strong> {{ s.materia }}</span>
          </p>

          <p class="flex items-center gap-2">
            <Users class="w-4 h-4 text-gray-600" />
            <span>{{ s.grupo }}</span>
          </p>

          <p class="flex items-center gap-2">
            <Calendar class="w-4 h-4 text-gray-600" />
            <span>Última Modificación: {{ s.modificacion }}</span>
          </p>

        </div>

        <p class="text-xs text-gray-500 mt-3 leading-snug">
          {{ s.descripcion }}
        </p>

        <div class="flex justify-between items-center mt-5">
          <button
            class="px-4 py-1 text-sm border rounded-lg bg-gray-50 hover:bg-gray-100"
            @click="verDetalles(s.id)"
          >
            Ver Detalles
          </button>

          <button class="p-1 hover:bg-gray-200 rounded">
            <MoreVertical class="w-5 h-5 text-gray-600" />
          </button>
        </div>

      </div>

    </div>

    <div class="flex justify-between items-center mt-10">

      <button class="border px-3 py-1 rounded text-sm hover:bg-gray-100">
        Anterior
      </button>

      <div class="flex gap-2">
        <button class="border px-3 py-1 rounded bg-teal-700 text-white text-sm">1</button>
        <button class="border px-3 py-1 rounded text-sm">2</button>
        <button class="border px-3 py-1 rounded text-sm">3</button>
        <button class="border px-3 py-1 rounded text-sm">…</button>
        <button class="border px-3 py-1 rounded text-sm">10</button>
      </div>

      <button class="border px-3 py-1 rounded text-sm hover:bg-gray-100">
        Siguiente
      </button>
    </div>


    <div class="flex justify-end mt-6">
      <div class="flex items-center gap-2">
        <span class="text-sm text-gray-700">Mostrar</span>

        <select class="border rounded-lg px-3 py-1 text-sm">
          <option>10 por página</option>
          <option>20 por página</option>
          <option>50 por página</option>
        </select>
      </div>
    </div>

  </main>
</template>

<script setup>
import Header from "@/components/Header.vue"
import { ref, computed } from "vue"
import { useRouter } from "vue-router"

import {
  Book,
  Users,
  Calendar,
  User,
  MoreVertical,
  LayoutGrid,
  List
} from "lucide-vue-next"

const router = useRouter()

const tituloPagina = {
  text: "Mis Secuencias Didácticas",
  links: []
}

const orden = ref("modificacion")

const secuencias = ref([
  {
    id: 1,
    nombre: "Desarrollo Web con React y Node.js",
    materia: "Desarrollo de Aplicaciones Web",
    grupo: "3A",
    modificacion: "20/03/2025",
    estado: "Aprobada",
    descripcion: "Descripción breve..."
  },
  {
    id: 2,
    nombre: "Introducción a la Inteligencia Artificial",
    materia: "Sistemas Inteligentes",
    grupo: "7D",
    modificacion: "18/03/2025",
    estado: "Pendiente",
    descripcion: "Actividad práctica..."
  },
  {
    id: 3,
    nombre: "Seguridad en Redes y Criptografía",
    materia: "Redes y Seguridad",
    grupo: "10A",
    modificacion: "10/02/2025",
    estado: "En Revisión",
    descripcion: "Descripción breve..."
  },
  {
    id: 4,
    nombre: "Bases de Datos NoSQL",
    materia: "Bases de Datos",
    grupo: "4C",
    modificacion: "22/03/2025",
    estado: "Rechazada",
    descripcion: "Descripción breve..."
  }
])

const secuenciasOrdenadas = computed(() => {
  if (orden.value === "nombre") {
    return [...secuencias.value].sort((a, b) => a.nombre.localeCompare(b.nombre))
  }
  if (orden.value === "estado") {
    return [...secuencias.value].sort((a, b) => a.estado.localeCompare(b.estado))
  }
  return secuencias.value
})

function badgeColor(estado) {
  return {
    "Aprobada": "bg-green-100 text-green-800 border border-green-300",
    "Pendiente": "bg-yellow-100 text-yellow-800 border border-yellow-300",
    "En Revisión": "bg-blue-100 text-blue-800 border border-blue-300",
    "Rechazada": "bg-red-100 text-red-800 border border-red-300"
  }[estado]
}

function verDetalles() {
  router.push(`/visualizacion-secuencia`)
}
</script>
