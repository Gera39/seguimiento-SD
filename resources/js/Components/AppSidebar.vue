<script setup lang="ts">
import type { SidebarProps } from "@/components/ui/sidebar";
import { BookOpen, BriefcaseBusiness, FileBarChart2, Home, ShieldCheck, Upload, Users } from "lucide-vue-next";
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
    title: "Paneles",
    icon: BriefcaseBusiness,
    items: [
      { title: "Panel docente", url: "/panel-docente" },
      { title: "Panel revisor", url: "/panel-revisor" },
      { title: "Panel director", url: "/panel-director" },
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
      { title: "Panel revisor", url: "/panel-revisor" },
      { title: "Panel director", url: "/panel-director" },
      { title: "Mis secuencias", url: "/mis-secuencias" },
    ],
  },
];
</script>

<template>
  <Sidebar
    v-bind="props"
    class="border-r bg-white"
    style="--sidebar-width: 200px; border-right: 0.5px solid var(--color-border-tertiary); background: var(--color-background-secondary);"
  >
    <SidebarContent>
      <NavMain :items="menuItems" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser />
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>
