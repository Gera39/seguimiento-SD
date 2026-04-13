<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Visualizacion</p>
              <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ plan.subject }}</h1>
              <p class="mt-3 text-sm leading-6 text-slate-500">{{ plan.general_objective }}</p>
            </div>
            <span class="rounded-full px-4 py-2 text-sm font-semibold" :class="statusClass(plan.status.code)">
              {{ plan.status.name }}
            </span>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <Link v-if="plan.actions.edit" :href="plan.actions.edit" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">
              Editar borrador
            </Link>
            <button
              v-if="plan.actions.submit"
              type="button"
              class="rounded-full bg-teal-600 px-5 py-3 text-sm font-semibold text-white"
              :disabled="submitForm.processing"
              @click="submitPlan"
            >
              {{ submitForm.processing ? "Enviando..." : "Enviar a revision" }}
            </button>
            <Link v-if="plan.actions.review" :href="plan.actions.review" class="rounded-full border border-sky-200 px-5 py-3 text-sm font-semibold text-sky-700">
              Abrir revision
            </Link>
            <Link v-if="plan.actions.final" :href="plan.actions.final" class="rounded-full border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-700">
              Validacion final
            </Link>
            <a
              v-if="plan.actions.export_word"
              :href="plan.actions.export_word"
              class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700"
            >
              Exportar Word
            </a>
          </div>

          <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ status }}
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Unidades y modulos</h2>
          <div class="mt-6 space-y-4">
            <div v-for="unit in plan.units" :key="unit.unit_number" class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Unidad {{ unit.unit_number }}</p>
                  <p class="mt-2 text-lg font-semibold text-slate-900">{{ unit.title }}</p>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">
                  {{ unit.progress_percentage }}% / {{ unit.planned_hours }} h
                </span>
              </div>

              <p class="mt-3 text-sm leading-6 text-slate-500">{{ unit.learning_objective }}</p>

              <div class="mt-4 grid gap-3">
                <div v-for="module in unit.modules" :key="module.module_number" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                  <p class="font-semibold text-slate-900">Modulo {{ module.module_number }} · {{ module.title }}</p>
                  <p class="mt-2 text-sm leading-6 text-slate-500">{{ module.topic_description }}</p>
                  <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">
                    {{ module.theoretical_hours }} h teoricas / {{ module.practical_hours }} h practicas
                  </p>
                </div>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Criterios de evaluacion</h2>
          <div class="mt-5 space-y-3">
            <div v-for="criterion in plan.evaluation_criteria" :key="criterion.criterion_name" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-center justify-between gap-3">
                <p class="font-semibold text-slate-900">{{ criterion.criterion_name }}</p>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">{{ criterion.weight_percentage }}%</span>
              </div>
              <p class="mt-2 text-sm text-slate-500">{{ criterion.criterion_type }}</p>
              <p class="mt-3 text-sm leading-6 text-slate-600">{{ criterion.description }}</p>
              <p class="mt-2 text-sm text-slate-500">Evidencia: {{ criterion.evidence_description }}</p>
              <p class="mt-1 text-sm text-slate-500">Instrumento: {{ criterion.instrument_description }}</p>
            </div>
          </div>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Ficha rapida</h2>
          <div class="mt-5 grid gap-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Folio</p>
              <p class="mt-2 text-sm font-medium text-slate-800">{{ plan.folio }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Docente</p>
              <p class="mt-2 text-sm font-medium text-slate-800">{{ plan.teacher }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Grupo</p>
              <p class="mt-2 text-sm font-medium text-slate-800">{{ plan.group }} · {{ plan.period }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Resumen</p>
              <p class="mt-2 text-sm font-medium text-slate-800">
                {{ plan.summary.units }} unidades · {{ plan.summary.modules }} modulos
              </p>
            </div>
          </div>
        </article>

        <article v-if="plan.review_comments.length" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Seguimiento de observaciones</h2>
          <div class="mt-5 space-y-4">
            <div v-for="comment in plan.review_comments" :key="comment.id" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-semibold text-slate-900">{{ comment.field_label }}</p>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="commentStatusClass(comment.comment_status_code)">
                  {{ commentStatusLabel(comment.comment_status_code) }}
                </span>
              </div>

              <p class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ comment.severity_code }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-700">{{ comment.comment_text }}</p>

              <div class="mt-4 space-y-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Antes</p>
                  <p class="mt-2 text-sm leading-6 text-amber-900">{{ comment.observed_value_snapshot || "Sin captura" }}</p>
                </div>

                <div v-if="comment.updated_value_snapshot" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                  <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Despues</p>
                  <p class="mt-2 text-sm leading-6 text-emerald-900">{{ comment.updated_value_snapshot }}</p>
                </div>
              </div>

              <div v-if="comment.teacher_response" class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">Respuesta del docente</p>
                <p class="mt-2 text-sm leading-6 text-sky-900">{{ comment.teacher_response }}</p>
                <p v-if="comment.teacher_responded_at" class="mt-2 text-xs text-sky-700">
                  Respondido el {{ comment.teacher_responded_at }}
                </p>
              </div>

              <p v-if="comment.validated_at && comment.comment_status_code === 'RESOLVED'" class="mt-3 text-xs text-emerald-700">
                Validado por revisor el {{ comment.validated_at }}
              </p>

              <form
                v-if="comment.respond_url && plan.status.editable"
                class="mt-4 space-y-3"
                @submit.prevent="submitCommentResponse(comment)"
              >
                <label class="block space-y-2">
                  <span class="text-sm font-medium text-slate-700">Que cambio realizaste</span>
                  <textarea
                    v-model="commentResponses[comment.id]"
                    rows="3"
                    class="w-full rounded-[24px] border border-slate-200 bg-white px-4 py-3"
                    placeholder="Describe como atendiste esta observacion"
                  />
                </label>

                <button
                  type="submit"
                  class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
                  :disabled="commentForm.processing || !commentResponses[comment.id]?.trim()"
                >
                  {{ commentForm.processing ? "Guardando..." : "Marcar observacion como atendida" }}
                </button>
              </form>
            </div>
          </div>
        </article>

        <article v-if="plan.latest_validation" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Ultima validacion</h2>
          <div class="mt-5 space-y-3">
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Contexto: {{ plan.latest_validation.context }}
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Avance: {{ plan.latest_validation.progress }}%
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Evaluacion: {{ plan.latest_validation.evaluation }}%
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              Horas: {{ plan.latest_validation.unit_hours }} / {{ plan.latest_validation.module_hours }}
            </div>
          </div>
        </article>

        <article v-if="plan.references.length" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Referencias</h2>
          <div class="mt-5 space-y-3">
            <div v-for="reference in plan.references" :key="reference.citation" class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              {{ reference.reference_type }} · {{ reference.citation }}
            </div>
          </div>
        </article>
      </aside>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

type ReviewComment = {
  id: number;
  field_label: string;
  severity_code: string;
  comment_text: string;
  observed_value_snapshot: string | null;
  teacher_response: string | null;
  updated_value_snapshot: string | null;
  comment_status_code: string;
  teacher_responded_at: string | null;
  validated_at: string | null;
  respond_url: string | null;
};

const props = defineProps<{
  plan: {
    folio: string;
    teacher: string;
    subject: string;
    group: string;
    period: string;
    general_objective: string;
    status: { code: string; name: string; editable: boolean };
    summary: { units: number; modules: number; progress: number; evaluation: number };
    units: Array<{
      unit_number: number;
      title: string;
      learning_objective: string;
      planned_hours: number;
      progress_percentage: number;
      modules: Array<{
        module_number: number;
        title: string;
        topic_description: string;
        theoretical_hours: number;
        practical_hours: number;
      }>;
    }>;
    evaluation_criteria: Array<{
      criterion_name: string;
      criterion_type: string;
      weight_percentage: number;
      description: string;
      evidence_description: string;
      instrument_description: string;
    }>;
    references: Array<{ reference_type: string; citation: string }>;
    latest_validation: null | {
      context: string;
      progress: number;
      evaluation: number;
      unit_hours: number;
      module_hours: number;
    };
    review_comments: ReviewComment[];
    actions: {
      edit: string | null;
      submit: string | null;
      export_word: string | null;
      review: string | null;
      final: string | null;
    };
  };
  status?: string | null;
}>();

const tituloPagina = {
  text: "Visualizacion de secuencia",
  links: ["Planeaciones", "Detalle"],
};

const submitForm = useForm({});
const commentForm = useForm({
  teacher_response: "",
});
const commentResponses = Object.fromEntries(
  props.plan.review_comments.map((comment) => [comment.id, comment.teacher_response ?? ""]),
) as Record<number, string>;

const submitPlan = () => {
  if (props.plan.actions.submit) {
    submitForm.post(props.plan.actions.submit);
  }
};

const submitCommentResponse = (comment: ReviewComment) => {
  if (!comment.respond_url) {
    return;
  }

  commentForm.teacher_response = commentResponses[comment.id] ?? "";

  commentForm.patch(comment.respond_url, {
    preserveScroll: true,
  });
};

const statusClass = (code: string) => {
  if (code === "AUTHORIZED") return "bg-emerald-100 text-emerald-700";
  if (code === "UNDER_REVIEW" || code === "SUBMITTED") return "bg-amber-100 text-amber-700";
  if (code === "FEEDBACK") return "bg-sky-100 text-sky-700";
  if (code === "REJECTED") return "bg-rose-100 text-rose-700";
  return "bg-slate-100 text-slate-700";
};

const commentStatusLabel = (code: string) => {
  if (code === "ADDRESSED") return "Atendido";
  if (code === "RESOLVED") return "Validado";
  if (code === "REOPENED") return "Reabierto";
  return "Abierto";
};

const commentStatusClass = (code: string) => {
  if (code === "ADDRESSED") return "bg-sky-100 text-sky-700";
  if (code === "RESOLVED") return "bg-emerald-100 text-emerald-700";
  if (code === "REOPENED") return "bg-rose-100 text-rose-700";
  return "bg-amber-100 text-amber-700";
};
</script>
