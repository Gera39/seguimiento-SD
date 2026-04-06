<script setup lang="ts">
import type { SidebarProps } from "@/components/ui/sidebar"
import {
  File,
  Settings2,
  Users,
  Home,
} from "lucide-vue-next"
import NavMain from "@/components/NavMain.vue"
import NavUser from "@/components/NavUser.vue"


import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarRail,
} from "@/components/ui/sidebar"

import { computed } from "vue"
import { storeToRefs } from "pinia"
import { useAuthStore } from "@/stores/auth"

const auth = useAuthStore()
const { user } = storeToRefs(auth)

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: "icon",
})


const menuDirector = [
  {
    title: "Usuarios",
    url: "#",
    icon: Users,
    items: [{ title: "Lista de Docentes", url: "/docentes" }],
  },
  {
    title: "Reportes",
    url: "#",
    icon: File,
    items: [
      { title: "Validación", url: "/validaciones" },
      { title: "Reporte de Secuencias", url: "/reportes" },
    ],
  },
  {
    title: "Configuración",
    url: "#",
    icon: Settings2,
    items: [
      { title: "Importar datos", url: "/importar-datos" },
      { title: "Asignar permisos", url: "/asignacion-permisos" },
    ],
  },
]

const menuRevisor = [
    {
    title: "Secuencias Didácticas",
    url: "#",
    icon: File,
    items: [{ title: "Mis Secuencias", url: "/mis-secuencias" }],
  },
  {
    title: "Reportes",
    url: "#",
    icon: File,
    items: [{ title: "Reporte de Secuencias", url: "/reportes" }],
  },
]

const menuDocente = [
  {
    title: "Secuencias Didácticas",
    url: "#",
    icon: File,
    items: [{ title: "Mis Secuencias", url: "/mis-secuencias" }],
  },
]
const finalMenu = computed(() => {
  const role = user.value?.role

  if (role === "director") return menuDirector
  if (role === "revisor") return menuRevisor
  if (role === "docente") return menuDocente

  return []
})
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarContent>
<div class="mb-1 flex justify-center">
  <router-link
    to="/dashboard"
    class="p-3 rounded hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center justify-center"
  >
    <Home class="w-6 h-6" />
  </router-link>
</div>


      <NavMain :items="finalMenu" />
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>

