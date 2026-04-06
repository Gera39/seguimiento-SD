<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-amber-700">Revision academica</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Validacion de secuencias</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
          Esta vista ya quedo reducida a una maqueta clara de revision. Mantiene prioridades visuales, estados y acciones sugeridas.
        </p>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <div v-for="column in queue" :key="column.title" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-center justify-between">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ column.title }}</p>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ column.count }}</span>
          </div>
          <div class="mt-4 space-y-3">
            <div v-for="card in column.items" :key="card.name" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="font-semibold text-slate-900">{{ card.name }}</p>
              <p class="mt-1 text-sm text-slate-500">{{ card.meta }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="overflow-hidden rounded-[24px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secuencia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Docente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Entrega</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Accion sugerida</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="row in rows" :key="row.sequence">
                <td class="px-4 py-4 font-medium text-slate-900">{{ row.sequence }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ row.teacher }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ row.date }}</td>
                <td class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="row.className">{{ row.status }}</span>
                </td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ row.action }}</td>
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
  text: "Validacion de secuencias",
  links: ["Secuencias", "Revision"],
};

const queue = [
  { title: "Pendientes", count: "08", items: [{ name: "Pensamiento critico", meta: "3 archivos por revisar" }, { name: "Algoritmos basicos", meta: "2 observaciones previas" }] },
  { title: "En analisis", count: "04", items: [{ name: "Diseno de interfaces", meta: "Revision compartida" }, { name: "Estadistica aplicada", meta: "Validacion curricular" }] },
  { title: "Listas para cierre", count: "03", items: [{ name: "Bases de datos", meta: "Cumple criterios" }, { name: "Innovacion educativa", meta: "Esperando decision final" }] },
];

const rows = [
  { sequence: "Diseno de interfaces", teacher: "Carlos Soto", date: "2026-03-18", status: "En analisis", className: "bg-sky-100 text-sky-700", action: "Abrir revision visual" },
  { sequence: "Pensamiento critico", teacher: "Luisa Vega", date: "2026-03-20", status: "Pendiente", className: "bg-amber-100 text-amber-700", action: "Comparar evidencias" },
  { sequence: "Innovacion educativa", teacher: "Andrea Morales", date: "2026-03-22", status: "Lista para cierre", className: "bg-emerald-100 text-emerald-700", action: "Enviar a validacion final" },
];
</script>
