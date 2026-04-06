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
      <div class="border-b border-slate-200 px-4 py-5">
        <a href="/dashboard" class="flex items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-white">
          <div class="flex size-11 items-center justify-center rounded-2xl bg-emerald-500/20 text-emerald-200">
            <FileBarChart2 class="size-5" />
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold">Seguimiento SD</p>
            <p class="text-xs text-slate-300">Vista visual en preparacion</p>
          </div>
        </a>
      </div>

      <NavMain :items="menuItems" />

      <div class="mt-auto px-4 pb-4">
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900">
          <div class="mb-3 flex items-center gap-2 font-semibold">
            <Upload class="size-4" />
            Demo limpia
          </div>
          <p class="leading-6">
            El proyecto ya esta preparado para mostrar diseno sin depender de auth, router propio o store heredado.
          </p>
        </div>
      </div>
    </SidebarContent>

    <SidebarFooter>
      <NavUser />
    </SidebarFooter>

    <SidebarRail />
  </Sidebar>
</template>
