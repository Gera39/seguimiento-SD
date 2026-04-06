<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-violet-700">Configuracion</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Asignacion de permisos</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
          Esta vista ahora funciona como maqueta limpia para permisos y perfiles. Ya no contiene modales, filtros ni acciones simuladas.
        </p>
      </section>

      <section class="grid gap-4 lg:grid-cols-3">
        <div v-for="role in roles" :key="role.name" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">{{ role.label }}</p>
          <p class="mt-3 text-2xl font-semibold text-slate-900">{{ role.name }}</p>
          <p class="mt-2 text-sm leading-6 text-slate-500">{{ role.description }}</p>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-2">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Matriz visual</p>
          <h2 class="text-2xl font-semibold text-slate-900">Permisos por rol</h2>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Permiso</th>
                <th v-for="role in matrixRoles" :key="role" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                  {{ role }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="permission in permissions" :key="permission.name">
                <td class="px-4 py-4 font-medium text-slate-900">{{ permission.name }}</td>
                <td v-for="value in permission.values" :key="value.label" class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="value.enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                    {{ value.label }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Asignacion de permisos",
  links: ["Configuracion", "Roles"],
};

const roles = [
  { label: "Perfil 01", name: "Docente", description: "Crea, edita y consulta sus propias secuencias." },
  { label: "Perfil 02", name: "Revisor", description: "Analiza entregas, deja comentarios y emite observaciones." },
  { label: "Perfil 03", name: "Director", description: "Visualiza avance global y decide validaciones finales." },
];

const matrixRoles = ["Docente", "Revisor", "Director"];

const permissions = [
  {
    name: "Crear secuencias",
    values: [
      { label: "Activo", enabled: true },
      { label: "Activo", enabled: true },
      { label: "Activo", enabled: true },
    ],
  },
  {
    name: "Validar entregas",
    values: [
      { label: "Sin acceso", enabled: false },
      { label: "Activo", enabled: true },
      { label: "Activo", enabled: true },
    ],
  },
  {
    name: "Gestionar usuarios",
    values: [
      { label: "Sin acceso", enabled: false },
      { label: "Sin acceso", enabled: false },
      { label: "Activo", enabled: true },
    ],
  },
];
</script>
