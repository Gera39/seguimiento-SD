<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.05fr_0.95fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Cierre de proceso</p>
          <h1 class="mt-3 text-3xl font-semibold text-slate-900">{{ plan.title }}</h1>
          <p class="mt-3 text-sm leading-6 text-slate-500">
            Docente: {{ plan.teacher }} · Carrera: {{ plan.career }} · Estado actual: {{ plan.status }}
          </p>

          <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ status }}
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Resumen ejecutivo</h2>
          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Avance total</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ plan.progress ?? 0 }}%</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Evaluacion total</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ plan.evaluation ?? 0 }}%</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Horas por unidad</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ plan.unitHours ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">Horas por modulos</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ plan.moduleHours ?? 0 }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Retroalimentacion tecnica previa</h2>
          <div class="mt-5 space-y-3">
            <div
              v-for="feedback in plan.feedbackSummary"
              :key="`${feedback.reviewed_at}-${feedback.general_comments}`"
              class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
            >
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ feedback.reviewed_at }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ feedback.general_comments }}</p>
            </div>
            <div v-if="!plan.feedbackSummary.length" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
              No hay retroalimentacion tecnica registrada.
            </div>
          </div>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Autorizacion final</h2>
          <div class="mt-5 rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Validacion institucional</p>
            <p class="mt-3 text-sm leading-6 text-emerald-900">
              Solo se puede autorizar si las horas cuadran y los porcentajes de avance y evaluacion llegan a 100%.
            </p>
          </div>

          <form class="mt-5 space-y-4" @submit.prevent="authorizePlan">
            <textarea
              v-model="authorizeForm.general_comments"
              rows="4"
              class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3"
              placeholder="Comentario final opcional"
            />

            <button
              type="submit"
              class="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white"
              :disabled="authorizeForm.processing || !plan.canAuthorize"
            >
              {{ authorizeForm.processing ? "Autorizando..." : "Autorizar planeacion" }}
            </button>
          </form>
        </article>
      </aside>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const props = defineProps<{
  plan: {
    title: string;
    teacher: string;
    career: string;
    status: string;
    progress: number | null;
    evaluation: number | null;
    unitHours: number | null;
    moduleHours: number | null;
    canAuthorize: boolean;
    authorizeUrl: string;
    feedbackSummary: Array<{ reviewed_at: string | null; general_comments: string | null }>;
  };
  status?: string | null;
}>();

const tituloPagina = {
  text: "Validacion final",
  links: ["Planeaciones", "Cierre"],
};

const authorizeForm = useForm({
  general_comments: "",
});

const authorizePlan = () => {
  authorizeForm.post(props.plan.authorizeUrl);
};
</script>
