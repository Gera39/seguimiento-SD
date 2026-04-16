<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-sky-100 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.14),_transparent_30%),linear-gradient(145deg,_#f8fafc_0%,_#ffffff_45%,_#ecfeff_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">{{ headline.eyebrow }}</p>
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
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Estados activos</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Gestion y seguimiento del pipeline</h2>
          </div>
          <p class="text-sm text-slate-500">La distribucion responde a los registros actuales de la base de datos.</p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
          <article v-for="item in statusBreakdown" :key="item.code" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ item.name }}</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ item.count }}</p>
          </article>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Filtros avanzados</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Busqueda avanzada y priorizacion operativa</h2>
          </div>
          <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="resetFilters">
            Limpiar filtros
          </button>
        </div>

        <form class="mt-6 grid gap-4 xl:grid-cols-4" @submit.prevent="applyFilters">
          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Busqueda general</span>
            <input
              v-model="localFilters.search"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Folio, docente, asignatura, carrera, periodo o comentario"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Docente</span>
            <input
              v-model="localFilters.teacher"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Nombre del docente"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Asignatura</span>
            <input
              v-model="localFilters.subject"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Nombre de asignatura"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Grupo</span>
            <input
              v-model="localFilters.group"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Codigo del grupo"
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

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Ronda de revision</span>
            <input
              v-model="localFilters.review_round"
              type="number"
              min="1"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Ej. 1"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Desde</span>
            <input v-model="localFilters.date_from" type="date" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4" />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Hasta</span>
            <input v-model="localFilters.date_to" type="date" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4" />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Orden</span>
            <select v-model="localFilters.sort" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4">
              <option v-for="option in sortOptions" :key="option.code" :value="option.code">{{ option.name }}</option>
            </select>
          </label>

          <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <input v-model="localFilters.has_open_comments" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-sky-600" />
            Solo con observaciones abiertas
          </label>

          <div class="flex items-end">
            <button type="submit" class="h-12 rounded-full bg-sky-600 px-5 text-sm font-semibold text-white hover:bg-sky-700">
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
                <Link :href="plan.primaryAction.url" class="rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                  {{ plan.primaryAction.label }}
                </Link>
                <Link :href="plan.detailUrl" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                  Ver detalle
                </Link>
              </div>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-4">
              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Seguimiento</p>
                <p class="mt-2 text-sm text-slate-600">Movimiento: {{ plan.submittedAt || plan.updatedAt || "Sin fecha" }}</p>
                <p class="mt-1 text-sm text-slate-600">Ronda: {{ plan.reviewRound }}</p>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Observaciones</p>
                <p class="mt-2 text-sm text-slate-600">{{ plan.openComments }} comentarios abiertos.</p>
                <p class="mt-1 text-sm text-slate-600">Ultimo cambio: {{ plan.latestStatusChangeAt || "Sin registro" }}</p>
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

              <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Accion sugerida</p>
                <p class="mt-2 text-sm text-slate-600">{{ plan.primaryAction.label }}</p>
                <p class="mt-1 text-sm text-slate-600">Estado institucional: {{ plan.statusName }}</p>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
          <p class="text-lg font-semibold text-slate-900">No hay planeaciones para los filtros seleccionados.</p>
          <p class="mt-2 text-sm text-slate-500">Prueba con otra combinacion o limpia la busqueda avanzada.</p>
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
  latestStatusChangeAt: string | null;
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
    teacher: string;
    subject: string;
    group: string;
    review_round: number | null;
    has_open_comments: boolean;
    date_from: string;
    date_to: string;
    sort: string;
  };
  statusOptions: StatusOption[];
  careerOptions: Option[];
  periodOptions: Option[];
  sortOptions: Array<{ code: string; name: string }>;
  metrics: Array<{
    label: string;
    value: number;
    caption: string;
  }>;
  summary: {
    totalVisible: number;
    filtered: number;
  };
  statusBreakdown: Array<{
    code: string;
    name: string;
    count: number;
  }>;
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
  teacher: props.filters.teacher ?? "",
  subject: props.filters.subject ?? "",
  group: props.filters.group ?? "",
  review_round: props.filters.review_round ?? null,
  has_open_comments: props.filters.has_open_comments ?? false,
  date_from: props.filters.date_from ?? "",
  date_to: props.filters.date_to ?? "",
  sort: props.filters.sort ?? "priority",
});

const applyFilters = () => {
  router.get(
    props.baseUrl,
    {
      ...(localFilters.search ? { search: localFilters.search } : {}),
      ...(localFilters.status ? { status: localFilters.status } : {}),
      ...(localFilters.career ? { career: localFilters.career } : {}),
      ...(localFilters.period ? { period: localFilters.period } : {}),
      ...(localFilters.teacher ? { teacher: localFilters.teacher } : {}),
      ...(localFilters.subject ? { subject: localFilters.subject } : {}),
      ...(localFilters.group ? { group: localFilters.group } : {}),
      ...(localFilters.review_round ? { review_round: localFilters.review_round } : {}),
      ...(localFilters.has_open_comments ? { has_open_comments: 1 } : {}),
      ...(localFilters.date_from ? { date_from: localFilters.date_from } : {}),
      ...(localFilters.date_to ? { date_to: localFilters.date_to } : {}),
      ...(localFilters.sort ? { sort: localFilters.sort } : {}),
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
  localFilters.teacher = "";
  localFilters.subject = "";
  localFilters.group = "";
  localFilters.review_round = null;
  localFilters.has_open_comments = false;
  localFilters.date_from = "";
  localFilters.date_to = "";
  localFilters.sort = "priority";

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
