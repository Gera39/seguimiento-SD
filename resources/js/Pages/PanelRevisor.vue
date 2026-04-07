<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-sky-100 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.16),_transparent_28%),linear-gradient(135deg,_#eff6ff_0%,_#ffffff_55%,_#f0fdfa_100%)] p-6 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
          <div>
            <span class="inline-flex rounded-full bg-sky-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-sky-800">
              Rol visible: revisor academico
            </span>
            <h1 class="mt-4 text-3xl font-semibold text-slate-900">Panel operativo de revision</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
              Supervisa las solicitudes nuevas, entra a la revision tecnica y consulta los ultimos dictamenes emitidos desde una sola vista.
            </p>
          </div>

          <Link :href="queueUrl" class="inline-flex rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">
            Abrir bandeja completa
          </Link>
        </div>

        <div v-if="status" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ status }}
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/70 bg-white/85 p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ metric.label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ metric.caption }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Bandeja prioritaria</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Solicitudes activas</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ queuePreview.length }} visibles</span>
          </div>

          <div v-if="queuePreview.length" class="mt-6 space-y-4">
            <article v-for="plan in queuePreview" :key="plan.id" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="text-lg font-semibold text-slate-900">{{ plan.subject }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ plan.folio }} · {{ plan.teacher }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ plan.career }} · Grupo {{ plan.group }} · {{ plan.period }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(plan.statusCode)">
                  {{ plan.statusName }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                <span>{{ plan.submittedAt || plan.updatedAt || "Sin movimiento" }}</span>
                <span>{{ plan.openComments }} observaciones abiertas</span>
                <span>Ronda {{ plan.reviewRound }}</span>
              </div>

              <div class="mt-5 flex flex-wrap gap-3">
                <Link :href="plan.primaryAction.url" class="rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white">
                  {{ plan.primaryAction.label }}
                </Link>
                <Link :href="plan.detailUrl" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                  Ver detalle
                </Link>
              </div>
            </article>
          </div>

          <div v-else class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
            <p class="text-lg font-semibold text-slate-900">No hay solicitudes activas por ahora.</p>
            <p class="mt-2 text-sm text-slate-500">Cuando un docente envie o retome una planeacion, aparecera aqui.</p>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Ultimos resultados</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Dictamenes recientes</h2>
            </div>
          </div>

          <div v-if="recentOutcomes.length" class="mt-6 space-y-4">
            <article v-for="plan in recentOutcomes" :key="plan.id" class="rounded-[28px] border border-slate-200 bg-white p-5">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-900">{{ plan.subject }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ plan.teacher }} · {{ plan.period }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(plan.statusCode)">
                  {{ plan.statusName }}
                </span>
              </div>

              <div v-if="plan.latestReview" class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                  {{ plan.latestReview.stage }} · {{ plan.latestReview.reviewedAt || "Sin fecha" }}
                </p>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ plan.latestReview.generalComments || "Sin comentario general capturado." }}</p>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <Link :href="plan.detailUrl" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">
                  Ver detalle
                </Link>
              </div>
            </article>
          </div>

          <div v-else class="mt-6 rounded-[28px] border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
            <p class="text-lg font-semibold text-slate-900">Todavia no hay resultados recientes.</p>
            <p class="mt-2 text-sm text-slate-500">Los dictamenes tecnicos y cierres autorizados apareceran en esta seccion.</p>
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

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

defineProps<{
  metrics: Array<{
    label: string;
    value: number;
    caption: string;
  }>;
  queuePreview: QueuePlan[];
  recentOutcomes: QueuePlan[];
  queueUrl: string;
  status?: string | null;
}>();

const tituloPagina = {
  text: "Panel del revisor",
  links: ["Paneles", "Revisor academico"],
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
