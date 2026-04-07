<script setup lang="ts">
import type { SidebarProps } from "@/components/ui/sidebar";
import { BookOpen, FileBarChart2, Home, Settings2, ShieldCheck, Upload, Users } from "lucide-vue-next";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import { Sidebar, SidebarContent, SidebarFooter, SidebarRail } from "@/components/ui/sidebar";

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: "icon",
});

const menuItems = [
  {
    title: "General",
    icon: Home,
    items: [
      { title: "Dashboard", url: "/dashboard" },
      { title: "Vista de prueba", url: "/prueba" },
    ],
  },
  {
    title: "Secuencias",
    icon: BookOpen,
    items: [
      { title: "Mis secuencias", url: "/mis-secuencias" },
      { title: "Validaciones", url: "/validaciones" },
      { title: "Reporte", url: "/reportes" },
    ],
  },
  {
    title: "Administracion",
    icon: Users,
    items: [
      { title: "Docentes", url: "/docentes" },
      { title: "Importar datos", url: "/importar-datos" },
      { title: "Permisos", url: "/asignacion-permisos" },
    ],
  },
  {
    title: "Revision",
    icon: ShieldCheck,
    items: [
      { title: "Revision visual", url: "/visualizacion-validacion" },
      { title: "Validacion final", url: "/validacion-final" },
      { title: "Ver secuencia", url: "/visualizacion-secuencia" },
    ],
  },
];
</script>

<template>
  <Sidebar v-bind="props" class="border-r border-slate-200 bg-white">
    <SidebarContent>
      <NavMain :items="menuItems" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser />
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>
