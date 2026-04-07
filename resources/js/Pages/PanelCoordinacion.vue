<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-amber-100 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.18),_transparent_25%),linear-gradient(135deg,_#fffbeb_0%,_#ffffff_52%,_#f8fafc_100%)] p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-amber-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-amber-800">
          Rol visible: coordinacion academica
        </span>
        <h1 class="mt-4 text-3xl font-semibold text-slate-900">Panel institucional de coordinacion</h1>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
          Este tablero concentra la supervision del proceso completo. Sirve para observar cumplimiento, detectar retrasos y tomar decisiones institucionales con base en datos visibles.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-4">
          <div v-for="metric in filteredMetrics" :key="metric.label" class="rounded-3xl border border-white/70 bg-white/80 p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ metric.label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ metric.caption }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Filtros ejecutivos</p>
          <div class="mt-6 space-y-4">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Academia</span>
              <select v-model="filters.academy" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-amber-300 focus:ring-4 focus:ring-amber-100">
                <option value="Todas">Todas</option>
                <option v-for="academy in academies" :key="academy" :value="academy">{{ academy }}</option>
              </select>
            </label>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Periodo</span>
              <select v-model="filters.period" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-amber-300 focus:ring-4 focus:ring-amber-100">
                <option value="Todos">Todos</option>
                <option v-for="period in periods" :key="period" :value="period">{{ period }}</option>
              </select>
            </label>

            <button type="button" class="rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-600" @click="applyScenario">
              Simular foco rojo
            </button>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Monitoreo institucional</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Estado por academias</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
              {{ visibleRows.length }} registros visibles
            </span>
          </div>

          <div v-if="message" class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            {{ message }}
          </div>

          <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Academia</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Periodo</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cumplimiento</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white">
                <tr v-for="row in visibleRows" :key="`${row.academy}-${row.period}`">
                  <td class="px-4 py-4 font-medium text-slate-900">{{ row.academy }}</td>
                  <td class="px-4 py-4 text-sm text-slate-600">{{ row.period }}</td>
                  <td class="px-4 py-4 text-sm text-slate-600">{{ row.progress }}</td>
                  <td class="px-4 py-4">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="row.className">{{ row.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Alertas y decisiones</p>
          <div class="mt-6 space-y-3">
            <div v-for="alert in alerts" :key="alert.title" class="rounded-[24px] border p-4" :class="alert.className">
              <p class="font-semibold">{{ alert.title }}</p>
              <p class="mt-2 text-sm leading-6">{{ alert.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Acciones de coordinacion</p>
          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div v-for="action in actions" :key="action.title" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
              <p class="font-semibold text-slate-900">{{ action.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ action.description }}</p>
            </div>
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Panel de coordinacion",
  links: ["Paneles", "Coordinacion academica"],
};

const academies = ["Comunicacion", "Sistemas", "Metodologia", "Ciencias Basicas"];
const periods = ["Abril 2026", "Marzo 2026"];

const filters = reactive({
  academy: "Todas",
  period: "Todos",
});

const message = ref("");

const baseRows = [
  { academy: "Comunicacion", period: "Abril 2026", progress: "68%", status: "Riesgo", className: "bg-amber-100 text-amber-700" },
  { academy: "Sistemas", period: "Abril 2026", progress: "91%", status: "Controlado", className: "bg-emerald-100 text-emerald-700" },
  { academy: "Metodologia", period: "Marzo 2026", progress: "82%", status: "Seguimiento", className: "bg-sky-100 text-sky-700" },
  { academy: "Ciencias Basicas", period: "Abril 2026", progress: "59%", status: "Atencion inmediata", className: "bg-rose-100 text-rose-700" },
];

const filteredMetrics = computed(() => [
  { label: "Cumplimiento", value: filters.academy === "Todas" ? "82%" : "74%", caption: "Avance institucional" },
  { label: "Retrasos", value: filters.period === "Todos" ? "05" : "03", caption: "Focos rojos activos" },
  { label: "Academias", value: filters.academy === "Todas" ? "09" : "01", caption: "Con seguimiento" },
  { label: "Ajustes", value: "07", caption: "Pendientes de cierre" },
]);

const visibleRows = computed(() =>
  baseRows.filter((row) =>
    (filters.academy === "Todas" || row.academy === filters.academy)
    && (filters.period === "Todos" || row.period === filters.period),
  ),
);

const alerts = [
  { title: "Comunicacion acumula retraso", description: "Existen secuencias devueltas con ajustes que aun no se corrigen en tiempo.", className: "border-amber-100 bg-amber-50 text-amber-900" },
  { title: "Ciencias Basicas requiere intervencion", description: "La academia muestra menor cumplimiento y varios dictamenes sin cierre.", className: "border-rose-100 bg-rose-50 text-rose-900" },
];

const actions = [
  { title: "Solicitar seguimiento al revisor", description: "Permite intervenir cuando la cola de revision supera el tiempo esperado." },
  { title: "Convocar academia", description: "Ayuda a responder ante bajo cumplimiento o reincidencia en observaciones." },
  { title: "Emitir reporte institucional", description: "Prepara una salida ejecutiva para reuniones o cortes de seguimiento." },
  { title: "Supervisar cierre del periodo", description: "Facilita validar que no queden secuencias abiertas al finalizar el ciclo." },
];

const applyScenario = () => {
  filters.academy = "Comunicacion";
  filters.period = "Abril 2026";
  message.value = "Se simulo un escenario de foco rojo en la academia de Comunicacion para mostrar como responderia el tablero.";
};
</script>
