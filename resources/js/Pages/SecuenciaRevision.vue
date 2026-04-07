<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Revision academica</p>
          <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ plan.title }}</h1>
          <p class="mt-3 text-sm leading-6 text-slate-500">
            Docente: {{ plan.teacher }} · Carrera: {{ plan.career }} · Estado: {{ plan.status }}
          </p>

          <div class="mt-5 flex flex-wrap gap-3">
            <button
              v-if="plan.canStartReview"
              type="button"
              class="rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white"
              :disabled="startReviewForm.processing"
              @click="startReview"
            >
              {{ startReviewForm.processing ? "Marcando..." : "Marcar en revision" }}
            </button>
          </div>

          <p v-if="status" class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            {{ status }}
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Resumen del contenido</h2>
          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Objetivo general</p>
              <p class="mt-3 text-sm leading-6 text-slate-700">{{ plan.generalObjective }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Cobertura</p>
              <p class="mt-3 text-sm leading-6 text-slate-700">{{ plan.units }} unidades · {{ plan.modules }} modulos</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Retroalimentacion</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Emitir observaciones</h2>
            </div>
            <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="addComment">
              Agregar comentario
            </button>
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitFeedback">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Comentario general</span>
              <textarea v-model="feedbackForm.general_comments" rows="4" class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" />
            </label>

            <div class="space-y-4">
              <div v-for="(comment, index) in feedbackForm.review_comments" :key="index" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                <div class="grid gap-4 md:grid-cols-3">
                  <select v-model="comment.entity_type" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <option value="PLAN">Plan</option>
                    <option value="UNIT">Unidad</option>
                    <option value="MODULE">Modulo</option>
                    <option value="EVALUATION">Evaluacion</option>
                  </select>
                  <select v-model="comment.severity_code" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    <option value="INFO">Info</option>
                    <option value="WARNING">Advertencia</option>
                    <option value="REQUIRED">Requerido</option>
                  </select>
                  <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold text-rose-600" @click="removeComment(index)">
                    Eliminar
                  </button>
                </div>
                <textarea v-model="comment.comment_text" rows="3" class="mt-4 w-full rounded-[24px] border border-slate-200 bg-white px-4 py-3" />
              </div>
            </div>

            <button
              type="submit"
              class="rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white"
              :disabled="feedbackForm.processing || !plan.canFeedback"
            >
              {{ feedbackForm.processing ? "Enviando..." : "Enviar retroalimentacion" }}
            </button>
          </form>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Observaciones previas</h2>
          <div class="mt-5 space-y-3">
            <div v-if="plan.comments.length" v-for="comment in plan.comments" :key="`${comment.entity_type}-${comment.comment_text}`" class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
              <p class="font-semibold text-amber-800">{{ comment.entity_type }} · {{ comment.severity_code }}</p>
              <p class="mt-1 text-sm leading-6 text-amber-700">{{ comment.comment_text }}</p>
            </div>
            <div v-else class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
              Aun no hay observaciones registradas para esta planeacion.
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Siguiente paso</h2>
          <Link :href="plan.detailUrl" class="mt-5 block rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
            Volver al detalle
          </Link>
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

const props = defineProps<{
  plan: {
    title: string;
    teacher: string;
    career: string;
    status: string;
    generalObjective: string;
    units: number;
    modules: number;
    canStartReview: boolean;
    canFeedback: boolean;
    startReviewUrl: string;
    feedbackUrl: string;
    detailUrl: string;
    comments: Array<{ entity_type: string; severity_code: string; comment_text: string }>;
  };
  status?: string | null;
}>();

const tituloPagina = {
  text: "Revision de secuencia",
  links: ["Planeaciones", "Revision"],
};

const startReviewForm = useForm({});
const feedbackForm = useForm({
  general_comments: "",
  review_comments: [
    {
      entity_type: "PLAN",
      entity_id: null,
      severity_code: "REQUIRED",
      comment_text: "",
    },
  ],
});

const startReview = () => {
  startReviewForm.post(props.plan.startReviewUrl);
};

const addComment = () => {
  feedbackForm.review_comments.push({
    entity_type: "PLAN",
    entity_id: null,
    severity_code: "REQUIRED",
    comment_text: "",
  });
};

const removeComment = (index: number) => {
  feedbackForm.review_comments.splice(index, 1);
  if (!feedbackForm.review_comments.length) addComment();
};

const submitFeedback = () => {
  feedbackForm.post(props.plan.feedbackUrl);
};
</script>
