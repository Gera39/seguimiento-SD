<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-sky-100 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.2),_transparent_25%),linear-gradient(135deg,_#eff6ff_0%,_#ffffff_50%,_#f0fdfa_100%)] p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-sky-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-sky-800">
          Rol visible: revisor academico
        </span>
        <h1 class="mt-4 text-3xl font-semibold text-slate-900">Panel institucional del revisor</h1>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
          Este modulo permite revisar secuencias por prioridad, emitir observaciones por bloque y dejar un dictamen academico claro antes del cierre.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/70 bg-white/80 p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ metric.label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ metric.caption }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Bandeja de revision</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Secuencias asignadas</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
              {{ queue.length }} elementos
            </span>
          </div>

          <div class="mt-6 space-y-3">
            <button
              v-for="item in queue"
              :key="item.title"
              type="button"
              class="w-full rounded-[26px] border border-slate-200 bg-slate-50 p-4 text-left transition hover:bg-white"
              @click="selected = item"
            >
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-900">{{ item.title }}</p>
                  <p class="mt-1 text-sm text-slate-500">{{ item.teacher }} · {{ item.career }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="item.className">
                  {{ item.status }}
                </span>
              </div>
            </button>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Dictamen academico</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ selected.title }}</h2>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ selected.teacher }} · {{ selected.career }} · {{ selected.period }}</p>
            </div>
            <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" @click="loadSuggestion">
              Cargar observacion ejemplo
            </button>
          </div>

          <div v-if="message" class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            {{ message }}
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="saveReview">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Bloque revisado</span>
                <select v-model="review.block" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                  <option value="">Selecciona un bloque</option>
                  <option v-for="block in blocks" :key="block" :value="block">{{ block }}</option>
                </select>
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Dictamen</span>
                <select v-model="review.decision" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100">
                  <option value="">Selecciona una opcion</option>
                  <option v-for="decision in decisions" :key="decision" :value="decision">{{ decision }}</option>
                </select>
              </label>
            </div>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Observacion academica</span>
              <textarea
                v-model="review.comment"
                rows="5"
                class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
                placeholder="Describe con claridad lo que cumple, lo que falta o el ajuste que debe realizar el docente."
              />
            </label>

            <div class="flex flex-wrap gap-3">
              <button type="submit" class="rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">
                Guardar dictamen
              </button>
              <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700" @click="sendBack">
                Marcar requiere ajustes
              </button>
            </div>
          </form>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Criterios institucionales</p>
          <div class="mt-6 space-y-3">
            <div v-for="criterion in criteria" :key="criterion.title" class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <p class="font-semibold text-slate-900">{{ criterion.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ criterion.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Historial reciente</p>
          <div class="mt-6 space-y-3">
            <div v-for="entry in history" :key="entry.title" class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <p class="font-semibold text-slate-900">{{ entry.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ entry.description }}</p>
            </div>
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Panel del revisor",
  links: ["Paneles", "Revisor academico"],
};

const metrics = [
  { label: "Pendientes", value: "11", caption: "Entradas por revisar" },
  { label: "En analisis", value: "04", caption: "Revision activa" },
  { label: "Cerradas", value: "18", caption: "Dictamen emitido" },
];

const queue = [
  { title: "Secuencia 05 - Programacion I", teacher: "Laura Perez", career: "Sistemas", period: "Abril 2026", status: "Pendiente", className: "bg-amber-100 text-amber-700" },
  { title: "Secuencia 03 - Comunicacion Oral", teacher: "Miguel Soto", career: "Lenguas", period: "Abril 2026", status: "En analisis", className: "bg-sky-100 text-sky-700" },
  { title: "Secuencia 02 - Metodologia", teacher: "Carla Diaz", career: "Educacion", period: "Marzo 2026", status: "Lista para cierre", className: "bg-emerald-100 text-emerald-700" },
];

const selected = ref(queue[0]);
const message = ref("");

const review = reactive({
  block: "",
  decision: "",
  comment: "",
});

const blocks = ["Datos generales", "Objetivo", "Actividades", "Evidencias", "Evaluacion"];
const decisions = ["Aprobado", "Requiere ajustes", "Observacion menor"];

const criteria = [
  { title: "Congruencia didactica", description: "Debe existir relacion clara entre objetivo, actividades y evidencias." },
  { title: "Instrumento de evaluacion", description: "La forma de evaluar debe corresponder al producto o desempeno solicitado." },
  { title: "Claridad metodologica", description: "Las actividades deben ser comprensibles, secuenciadas y viables." },
];

const history = [
  { title: "Programacion I", description: "Se devolvio por falta de alineacion entre evidencias y criterio de evaluacion." },
  { title: "Comunicacion Oral", description: "Se aprobo con observacion menor en la redaccion del objetivo especifico." },
  { title: "Metodologia", description: "Quedo lista para validacion final despues de segunda revision." },
];

const loadSuggestion = () => {
  Object.assign(review, {
    block: "Evaluacion",
    decision: "Requiere ajustes",
    comment: "Se recomienda precisar mejor la rubrica y alinearla con la evidencia descrita en el cierre de la secuencia.",
  });
  message.value = `Se cargo un ejemplo de revision para ${selected.value.title}.`;
};

const saveReview = () => {
  message.value = `Dictamen guardado para ${selected.value.title} con estado ${review.decision || "sin definir"}.`;
};

const sendBack = () => {
  review.decision = "Requiere ajustes";
  message.value = `La secuencia ${selected.value.title} se marco como requiere ajustes en esta demo.`;
};
</script>
