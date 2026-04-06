<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Gestion academica</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Docentes y revisores</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Esta pantalla quedo limpia y enfocada solo en diseno. Conserva una tabla clara y resumenes para integrar datos reales despues.
            </p>
          </div>

          <button type="button" class="h-12 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white">
            Agregar docente
          </button>
        </div>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <div v-for="metric in metrics" :key="metric.label" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">{{ metric.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="max-w-md">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Directorio</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Vista general del personal</h2>
          </div>
          <div class="h-12 w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm leading-[3rem] text-slate-400">
            Buscar docente o revisor
          </div>
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rol</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Correo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Telefono</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="person in people" :key="person.email">
                <td class="px-4 py-4">
                  <p class="font-medium text-slate-900">{{ person.name }}</p>
                </td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ person.role }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ person.email }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ person.phone }}</td>
                <td class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="person.statusClass">
                    {{ person.status }}
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
  text: "Docentes y revisores",
  links: ["Administracion", "Personal academico"],
};

const metrics = [
  { label: "Docentes activos", value: "48" },
  { label: "Revisores asignados", value: "12" },
  { label: "Altas del mes", value: "6" },
];

const people = [
  { name: "Andrea Morales", role: "Docente", email: "andrea.morales@uth.edu.mx", phone: "555-0101", status: "Activa", statusClass: "bg-emerald-100 text-emerald-700" },
  { name: "Carlos Soto", role: "Revisor", email: "carlos.soto@uth.edu.mx", phone: "555-0134", status: "Asignado", statusClass: "bg-sky-100 text-sky-700" },
  { name: "Luisa Vega", role: "Docente", email: "luisa.vega@uth.edu.mx", phone: "555-0198", status: "Activa", statusClass: "bg-emerald-100 text-emerald-700" },
  { name: "Miguel Neri", role: "Revisor", email: "miguel.neri@uth.edu.mx", phone: "555-0210", status: "En revision", statusClass: "bg-amber-100 text-amber-700" },
];
</script>
