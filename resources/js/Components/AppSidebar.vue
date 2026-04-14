<script setup lang="ts">
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import type { SidebarProps } from "@/components/ui/sidebar";
import { BookOpen, ClipboardCheck, FileBarChart2, ShieldCheck, Users } from "lucide-vue-next";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import { Sidebar, SidebarContent, SidebarFooter, SidebarRail } from "@/components/ui/sidebar";

const props = withDefaults(defineProps<SidebarProps>(), {
  collapsible: "icon",
});

const page = usePage<{
  auth: {
    user: null | {
      roles?: {
        isAdmin?: boolean;
        isDirectivo?: boolean;
        isDocente?: boolean;
        isRevisor?: boolean;
      };
    };
  };
}>();

type MenuGroup = {
  title: string;
  icon: typeof Users;
  items: Array<{
    title: string;
    url: string;
  }>;
};

const menuItems = computed(() => {
  const roles = page.props.auth.user?.roles;
  const items: MenuGroup[] = [];

  if (roles?.isDocente) {
    items.push({
      title: "Planeaciones",
      icon: BookOpen,
      items: [{ title: "Mis planeaciones", url: "/mis-secuencias" }],
    });
  }

  if (roles?.isRevisor) {
    items.push({
      title: "Revision",
      icon: ClipboardCheck,
      items: [
        { title: "Resumen de revision", url: "/panel-revisor" },
        { title: "Bandeja de validacion", url: "/validaciones" },
      ],
    });
  }

  if (roles?.isDirectivo) {
    items.push({
      title: "Direccion",
      icon: ShieldCheck,
      items: [
        { title: "Validacion final", url: "/panel-director" },
        { title: "Reportes", url: "/reportes" },
      ],
    });
  }

  if (roles?.isAdmin || roles?.isDirectivo) {
    items.push({
      title: "Administracion",
      icon: Users,
      items: [
        { title: "Docentes", url: "/docentes" },
        { title: "Importar datos", url: "/importar-datos" },
        { title: "Permisos", url: "/asignacion-permisos" },
      ],
    });
  }

  if (roles?.isAdmin && !roles?.isDirectivo) {
    items.push({
      title: "Reportes",
      icon: FileBarChart2,
      items: [{ title: "Reporte general", url: "/reportes" }],
    });
  }

  if (items.length === 0) {
    items.push({
      title: "Cuenta",
      icon: Users,
      items: [{ title: "Perfil", url: "/profile" }],
    });
  }

  return items;
});
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
