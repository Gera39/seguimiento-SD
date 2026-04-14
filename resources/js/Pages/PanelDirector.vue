<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-emerald-100 bg-[radial-gradient(circle_at_top_right,_rgba(16,185,129,0.15),_transparent_28%),linear-gradient(140deg,_#f0fdf4_0%,_#ffffff_52%,_#ecfeff_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">{{ headline.eyebrow }}</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ headline.title }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
              {{ headline.copy }}
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-white/70 bg-white/90 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Universo visible</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">{{ summary.totalVisible }}</p>
            </div>
            <div class="rounded-2xl border border-white/70 bg-white/90 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Filtrado</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">{{ summary.filtered }}</p>
            </div>
          </div>
        </div>

        <div v-if="status" class="mt-5 rounded-2xl border border-emerald-200 bg-white/90 px-4 py-3 text-sm text-emerald-800">
          {{ status }}
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/70 bg-white/90 p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ metric.label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ metric.caption }}</p>
          </article>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Filtros ejecutivos</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Prioriza cierres y revisa el pipeline academico</h2>
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
              placeholder="Folio, asignatura, docente o carrera"
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
            <button type="submit" class="h-12 rounded-full bg-emerald-600 px-5 text-sm font-semibold text-white hover:bg-emerald-700">
              Aplicar
            </button>
          </div>
        </form>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Cola de direccion</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Planeaciones para seguimiento y cierre</h2>
          </div>
          <p class="text-sm text-slate-500">{{ summary.filtered }} registros listados.</p>
        </div>

        <div v-if="plans.length" class="mt-6 grid gap-4">
          <article v-for="plan in plans" :key="plan.id" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
              <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                  <p class="text-xl font-semibold text-slate-900">{{ plan.subject }}</p>
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(plan.statusCode)">
                    {{ plan.statusName }}
                  </span>
                </div>
                <p class="text-sm text-slate-500">{{ plan.folio }} · {{ plan.teacher }}</p>
                <p class="text-sm text-slate-500">{{ plan.career }} · Grupo {{ plan.group }} · {{ plan.period }}</p>
              </div>

              <div class="flex flex-wrap gap-3">
                <Link :href="plan.primaryAction.url" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                  {{ plan.primaryAction.label }}
                </Link>
                <Link :href="plan.detailUrl" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                  Ver detalle
                </Link>
              </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-3">
              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Seguimiento</p>
                <p class="mt-2 text-sm text-slate-600">Movimiento: {{ plan.submittedAt || plan.updatedAt || "Sin fecha" }}</p>
                <p class="mt-1 text-sm text-slate-600">Ronda: {{ plan.reviewRound }}</p>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Observaciones</p>
                <p class="mt-2 text-sm text-slate-600">{{ plan.openComments }} comentarios abiertos.</p>
                <p class="mt-1 text-sm text-slate-600">Accion sugerida: {{ plan.primaryAction.label }}</p>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Ultimo dictamen</p>
                <p v-if="plan.latestReview" class="mt-2 text-sm text-slate-600">
                  {{ plan.latestReview.stage }} · {{ plan.latestReview.reviewedAt || "Sin fecha" }}
                </p>
                <p class="mt-1 text-sm leading-6 text-slate-600">
                  {{ plan.latestReview?.generalComments || "Aun no se ha capturado comentario general para esta planeacion." }}
                </p>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
          <p class="text-lg font-semibold text-slate-900">No hay planeaciones para este corte.</p>
          <p class="mt-2 text-sm text-slate-500">Ajusta los filtros o espera nuevas revisiones tecnicas para autorizar.</p>
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
  text: "Validacion final",
  links: ["Planeaciones", "Direccion academica"],
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
