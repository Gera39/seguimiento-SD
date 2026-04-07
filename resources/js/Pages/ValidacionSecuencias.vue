<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-amber-700">{{ headline.eyebrow }}</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ headline.title }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              {{ headline.copy }}
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Visibles</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">{{ summary.totalVisible }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Resultado actual</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">{{ summary.filtered }}</p>
            </div>
          </div>
        </div>
      </section>

      <section v-if="status" class="rounded-[28px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
        {{ status }}
      </section>

      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article v-for="metric in metrics" :key="metric.label" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ metric.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
          <p class="mt-2 text-sm leading-6 text-slate-500">{{ metric.caption }}</p>
        </article>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Filtros</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Ubica planeaciones por estatus o contexto academico</h2>
          </div>
          <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="resetFilters">
            Limpiar filtros
          </button>
        </div>

        <form class="mt-6 grid gap-4 xl:grid-cols-[1.2fr_0.9fr_0.9fr_0.9fr_auto]" @submit.prevent="applyFilters">
          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Busqueda</span>
            <input
              v-model="localFilters.search"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Folio, docente, asignatura, grupo o periodo"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Estatus</span>
            <select v-model="localFilters.status" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4">
              <option value="">Todos</option>
              <option v-for="option in statusOptions" :key="option.code" :value="option.code">{{ option.name }}</option>
            </select>
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Carrera</span>
            <select v-model="localFilters.career" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4">
              <option :value="null">Todas</option>
              <option v-for="option in careerOptions" :key="option.id" :value="option.id">{{ option.name }}</option>
            </select>
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Periodo</span>
            <select v-model="localFilters.period" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4">
              <option :value="null">Todos</option>
              <option v-for="option in periodOptions" :key="option.id" :value="option.id">{{ option.name }}</option>
            </select>
          </label>

          <div class="flex items-end">
            <button type="submit" class="h-12 rounded-full bg-slate-900 px-5 text-sm font-semibold text-white">
              Aplicar
            </button>
          </div>
        </form>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Bandeja</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Planeaciones encontradas</h2>
          </div>
          <p class="text-sm text-slate-500">{{ summary.filtered }} registros con los filtros activos.</p>
        </div>

        <div v-if="plans.length" class="mt-6 overflow-hidden rounded-[24px] border border-slate-200">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Planeacion</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Docente</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Contexto</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estatus</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ultimo movimiento</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Accion</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200 bg-white">
                <tr v-for="plan in plans" :key="plan.id">
                  <td class="px-4 py-4 align-top">
                    <p class="font-semibold text-slate-900">{{ plan.subject }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ plan.folio }}</p>
                    <p v-if="plan.latestReview?.generalComments" class="mt-2 text-sm leading-6 text-slate-500">
                      {{ plan.latestReview.generalComments }}
                    </p>
                  </td>
                  <td class="px-4 py-4 align-top text-sm text-slate-600">
                    <p>{{ plan.teacher }}</p>
                    <p class="mt-1 text-slate-500">Grupo {{ plan.group }}</p>
                  </td>
                  <td class="px-4 py-4 align-top text-sm text-slate-600">
                    <p>{{ plan.career }}</p>
                    <p class="mt-1 text-slate-500">{{ plan.period }}</p>
                  </td>
                  <td class="px-4 py-4 align-top">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(plan.statusCode)">
                      {{ plan.statusName }}
                    </span>
                    <p class="mt-2 text-xs uppercase tracking-[0.18em] text-slate-400">Ronda {{ plan.reviewRound }}</p>
                  </td>
                  <td class="px-4 py-4 align-top text-sm text-slate-600">
                    <p>{{ plan.submittedAt || plan.updatedAt || "Sin fecha" }}</p>
                    <p class="mt-1 text-slate-500">
                      {{ plan.openComments }} observaciones abiertas
                    </p>
                  </td>
                  <td class="px-4 py-4 align-top">
                    <div class="flex flex-col gap-2">
                      <Link :href="plan.primaryAction.url" class="rounded-full bg-amber-500 px-4 py-2 text-center text-sm font-semibold text-white">
                        {{ plan.primaryAction.label }}
                      </Link>
                      <Link :href="plan.detailUrl" class="rounded-full border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">
                        Ver detalle
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
          <p class="text-lg font-semibold text-slate-900">No hay planeaciones para los filtros seleccionados.</p>
          <p class="mt-2 text-sm text-slate-500">Prueba con otro estatus, una carrera distinta o elimina la busqueda.</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

type Option = {
  id: number;
  name: string;
};

type StatusOption = {
  code: string;
  name: string;
};

type QueuePlan = {
  id: number;
  folio: string;
  subject: string;
  teacher: string;
  career: string;
  group: string;
  period: string;
  statusCode: string;
  statusName: string;
  submittedAt: string | null;
  updatedAt: string | null;
  reviewRound: number;
  openComments: number;
  detailUrl: string;
  reviewUrl: string;
  finalUrl: string;
  primaryAction: {
    label: string;
    url: string;
  };
  latestReview?: {
    stage: string;
    reviewer: string;
    reviewedAt: string | null;
    generalComments: string;
  } | null;
};

const props = defineProps<{
  headline: {
    eyebrow: string;
    title: string;
    copy: string;
  };
  baseUrl: string;
  filters: {
    search: string;
    status: string;
    career: number | null;
    period: number | null;
  };
  statusOptions: StatusOption[];
  careerOptions: Option[];
  periodOptions: Option[];
  metrics: Array<{
    label: string;
    value: number;
    caption: string;
  }>;
  summary: {
    totalVisible: number;
    filtered: number;
  };
  plans: QueuePlan[];
  status?: string | null;
}>();

const tituloPagina = {
  text: "Validacion de secuencias",
  links: ["Secuencias", "Revision"],
};

const localFilters = reactive({
  search: props.filters.search ?? "",
  status: props.filters.status ?? "",
  career: props.filters.career ?? null,
  period: props.filters.period ?? null,
});

const applyFilters = () => {
  router.get(
    props.baseUrl,
    {
      ...(localFilters.search ? { search: localFilters.search } : {}),
      ...(localFilters.status ? { status: localFilters.status } : {}),
      ...(localFilters.career ? { career: localFilters.career } : {}),
      ...(localFilters.period ? { period: localFilters.period } : {}),
    },
    {
      preserveScroll: true,
      preserveState: true,
      replace: true,
    },
  );
};

const resetFilters = () => {
  localFilters.search = "";
  localFilters.status = "";
  localFilters.career = null;
  localFilters.period = null;

  router.get(props.baseUrl, {}, { preserveScroll: true, preserveState: true, replace: true });
};

const statusClass = (statusCode: string) => {
  if (statusCode === "SUBMITTED") {
    return "bg-amber-100 text-amber-700";
  }

  if (statusCode === "UNDER_REVIEW") {
    return "bg-sky-100 text-sky-700";
  }

  if (statusCode === "FEEDBACK") {
    return "bg-rose-100 text-rose-700";
  }

  if (statusCode === "AUTHORIZED") {
    return "bg-emerald-100 text-emerald-700";
  }

  return "bg-slate-100 text-slate-700";
};
</script>
