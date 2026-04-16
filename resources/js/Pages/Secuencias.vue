<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Biblioteca personal</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Mis planeaciones didacticas</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Gestiona tus borradores, ubica planeaciones por estado y localiza rapidamente cualquier secuencia por folio, asignatura, carrera o periodo.
            </p>
          </div>
          <div class="flex flex-wrap gap-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
              {{ summary.filtered }} de {{ summary.totalVisible }} visibles
            </div>
            <Link :href="createUrl" class="rounded-full bg-teal-600 px-5 py-3 text-sm font-semibold text-white">
              Nueva planeacion
            </Link>
          </div>
        </div>

        <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ status }}
        </p>
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
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Ruta visible</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Que sigue con cada planeacion</h2>
          </div>
          <p class="max-w-2xl text-sm leading-6 text-slate-500">
            Aqui ya no se queda todo en borrador: desde esta pantalla puedes identificar el estado, editar, atender feedback y entrar al detalle para enviarla a revision.
          </p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">1. Borrador</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Se puede editar libremente y despues enviar a revision.</p>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">2. Enviada</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Queda bloqueada mientras el revisor la toma o la analiza.</p>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">3. Feedback</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Regresa con observaciones para corregir y reenviar.</p>
          </article>
          <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">4. Autorizada</p>
            <p class="mt-3 text-sm leading-6 text-slate-600">Ya termino el circuito institucional y puedes revisar su historial.</p>
          </article>
        </div>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Filtros avanzados</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Busqueda y seguimiento de estados</h2>
          </div>
          <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="resetFilters">
            Limpiar filtros
          </button>
        </div>

        <form class="mt-6 grid gap-4 xl:grid-cols-[1.2fr_0.9fr_0.9fr_0.9fr_0.9fr_0.9fr_auto]" @submit.prevent="applyFilters">
          <label class="space-y-2 xl:col-span-2">
            <span class="text-sm font-medium text-slate-700">Busqueda avanzada</span>
            <input
              v-model="localFilters.search"
              type="text"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
              placeholder="Folio, asignatura, grupo, carrera, periodo u objetivo"
            />
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Estado</span>
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

          <div class="flex items-end">
            <button type="submit" class="h-12 rounded-full bg-slate-900 px-5 text-sm font-semibold text-white">
              Aplicar
            </button>
          </div>
        </form>
      </section>

      <section v-if="plans.length" class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
        <article
          v-for="item in plans"
          :key="item.id"
          class="relative rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
          <span class="absolute right-5 top-5 rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(item.statusCode)">
            {{ item.status }}
          </span>

          <div class="pr-20">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ item.folio }}</p>
            <h2 class="mt-3 text-xl font-semibold text-slate-900">{{ item.title }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ item.career }} · {{ item.period }}</p>
          </div>

          <div class="mt-5 grid gap-2 text-sm text-slate-600">
            <p>Materia: {{ item.subject }}</p>
            <p>Grupo: {{ item.group }}</p>
            <p>Ronda: {{ item.reviewRound || 0 }}</p>
            <p>Observaciones abiertas: {{ item.openComments }}</p>
            <p>Ultima actualizacion: {{ item.updatedAt }}</p>
            <p v-if="item.submittedAt">Enviada: {{ item.submittedAt }}</p>
          </div>

          <p class="mt-4 text-sm leading-6 text-slate-500">{{ item.description }}</p>
          <p class="mt-3 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ item.validationSummary }}</p>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ item.stateHint }}</p>
          <p v-if="item.lastStatusChangeAt" class="mt-2 text-xs text-slate-400">Cambio de estado: {{ item.lastStatusChangeAt }}</p>

          <div class="mt-6 flex flex-wrap gap-3">
            <button
              v-if="item.submitUrl"
              type="button"
              class="rounded-full bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700"
              @click="submitPlan(item)"
            >
              Enviar a revision
            </button>
            <Link
              v-if="item.editUrl"
              :href="item.editUrl"
              class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700"
            >
              {{ item.statusCode === "FEEDBACK" ? "Atender feedback" : "Editar borrador" }}
            </Link>
            <Link :href="item.href" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
              Ver detalle
            </Link>
          </div>
        </article>
      </section>

      <section v-else class="rounded-[32px] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">No hay planeaciones para estos criterios</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Ajusta la busqueda o limpia los filtros para volver a ver toda tu biblioteca.
        </p>
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

const props = defineProps<{
  baseUrl: string;
  filters: {
    search: string;
    status: string;
    career: number | null;
    period: number | null;
    date_from: string;
    date_to: string;
    sort: string;
  };
  metrics: Array<{
    label: string;
    value: number;
    caption: string;
  }>;
  summary: {
    totalVisible: number;
    filtered: number;
  };
  statusOptions: StatusOption[];
  careerOptions: Option[];
  periodOptions: Option[];
  sortOptions: Array<{ code: string; name: string }>;
  plans: Array<{
    id: number;
    folio: string;
    title: string;
    subject: string;
    career: string;
    period: string;
    group: string;
    updatedAt: string;
    submittedAt: string | null;
    status: string;
    statusCode: string;
    description: string;
    validationSummary: string;
    stateHint: string;
    openComments: number;
    reviewRound: number;
    lastStatusChangeAt: string | null;
    href: string;
    editUrl: string | null;
    submitUrl: string | null;
  }>;
  createUrl: string;
  status?: string | null;
}>();

const tituloPagina = {
  text: "Mis secuencias",
  links: ["Planeaciones", "Listado personal"],
};

const localFilters = reactive({
  search: props.filters.search ?? "",
  status: props.filters.status ?? "",
  career: props.filters.career ?? null,
  period: props.filters.period ?? null,
  date_from: props.filters.date_from ?? "",
  date_to: props.filters.date_to ?? "",
  sort: props.filters.sort ?? "updated",
});

const applyFilters = () => {
  router.get(
    props.baseUrl,
    {
      ...(localFilters.search ? { search: localFilters.search } : {}),
      ...(localFilters.status ? { status: localFilters.status } : {}),
      ...(localFilters.career ? { career: localFilters.career } : {}),
      ...(localFilters.period ? { period: localFilters.period } : {}),
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
  localFilters.date_from = "";
  localFilters.date_to = "";
  localFilters.sort = "updated";

  router.get(props.baseUrl, {}, { preserveScroll: true, preserveState: true, replace: true });
};

const submitPlan = (plan: { submitUrl: string | null }) => {
  if (!plan.submitUrl) {
    return;
  }

  router.post(plan.submitUrl, {}, { preserveScroll: true });
};

const statusClass = (code: string) => {
  if (code === "AUTHORIZED") return "bg-emerald-100 text-emerald-700";
  if (code === "UNDER_REVIEW" || code === "SUBMITTED") return "bg-amber-100 text-amber-700";
  if (code === "FEEDBACK") return "bg-sky-100 text-sky-700";
  if (code === "REJECTED") return "bg-rose-100 text-rose-700";
  return "bg-slate-100 text-slate-700";
};
</script>
