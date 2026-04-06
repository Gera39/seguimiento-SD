<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-700">Indicadores</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Reporte de secuencias</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Se quitaron filtros reactivos y navegacion interna. La tabla quedo como referencia visual lista para alimentar con datos reales despues.
            </p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
            Rango demo: Enero a Abril 2026
          </div>
        </div>
      </section>

      <section class="grid gap-4 md:grid-cols-4">
        <div v-for="metric in metrics" :key="metric.label" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">{{ metric.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-4">
          <div v-for="filter in filters" :key="filter.label" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ filter.label }}</p>
            <p class="mt-2 text-sm font-medium text-slate-700">{{ filter.value }}</p>
          </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Clave</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secuencia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Docente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Materia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fecha</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="item in reportRows" :key="item.id">
                <td class="px-4 py-4 text-sm font-medium text-slate-900">{{ item.id }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ item.title }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ item.teacher }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ item.subject }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ item.date }}</td>
                <td class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="item.className">{{ item.status }}</span>
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
  text: "Reporte de secuencias",
  links: ["Secuencias", "Reportes"],
};

const metrics = [
  { label: "Secuencias registradas", value: "186" },
  { label: "Aprobadas", value: "124" },
  { label: "En revision", value: "39" },
  { label: "Rechazadas", value: "23" },
];

const filters = [
  { label: "Docente", value: "Todos" },
  { label: "Estado", value: "General" },
  { label: "Periodo", value: "2026-1" },
  { label: "Programa", value: "Multidisciplinario" },
];

const reportRows = [
  { id: "SD-001", title: "Introduccion a Python", teacher: "Andrea Morales", subject: "Programacion", date: "2026-02-14", status: "Aprobada", className: "bg-emerald-100 text-emerald-700" },
  { id: "SD-018", title: "Metodologia de proyectos", teacher: "Carlos Soto", subject: "Planeacion", date: "2026-02-22", status: "En revision", className: "bg-amber-100 text-amber-700" },
  { id: "SD-031", title: "Taller de presentacion", teacher: "Luisa Vega", subject: "Comunicacion", date: "2026-03-01", status: "Rechazada", className: "bg-rose-100 text-rose-700" },
];
</script>
