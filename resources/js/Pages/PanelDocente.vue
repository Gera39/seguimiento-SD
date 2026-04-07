<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-emerald-100 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.22),_transparent_25%),linear-gradient(135deg,_#f0fdf4_0%,_#ffffff_52%,_#ecfeff_100%)] p-6 shadow-sm">
        <span class="inline-flex rounded-full bg-emerald-100 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-800">
          Rol visible: docente
        </span>
        <h1 class="mt-4 text-3xl font-semibold text-slate-900">Panel institucional del docente</h1>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
          Aqui el docente puede capturar, actualizar y enviar sus secuencias didacticas. El objetivo es trabajar rapido, con un formato institucional claro y con seguimiento visible de cada envio.
        </p>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/70 bg-white/80 p-4">
            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ metric.label }}</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ metric.caption }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Formulario de captura</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Nueva secuencia didactica</h2>
            </div>
            <div class="flex gap-2">
              <button type="button" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700" @click="fillTemplate">
                Usar ejemplo
              </button>
              <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white" @click="saveDraft">
                Guardar borrador
              </button>
            </div>
          </div>

          <div v-if="message" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ message }}
          </div>

          <form class="mt-6 space-y-5" @submit.prevent="submitReview">
            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Asignatura</span>
                <Input v-model="form.asignatura" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Comunicacion Oral" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Grupo</span>
                <Input v-model="form.grupo" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. 2A" />
              </label>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Unidad o secuencia</span>
                <Input v-model="form.secuencia" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Secuencia 03" />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Periodo</span>
                <Input v-model="form.periodo" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Abril 2026" />
              </label>
            </div>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Competencia u objetivo</span>
              <textarea
                v-model="form.objetivo"
                rows="3"
                class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                placeholder="Describe el aprendizaje esperado o la competencia que busca desarrollar esta secuencia."
              />
            </label>

            <div class="grid gap-4 md:grid-cols-2">
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Actividad principal</span>
                <textarea
                  v-model="form.actividad"
                  rows="4"
                  class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                  placeholder="Ej. Debate guiado, practica en laboratorio, exposicion, etc."
                />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Evidencia y evaluacion</span>
                <textarea
                  v-model="form.evidencia"
                  rows="4"
                  class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                  placeholder="Ej. Lista de cotejo, exposicion oral, producto final, rubrica."
                />
              </label>
            </div>

            <div class="flex flex-wrap gap-3">
              <button type="submit" class="rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                Enviar a revision
              </button>
              <button type="button" class="rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700" @click="clearForm">
                Limpiar formulario
              </button>
            </div>
          </form>
        </article>

        <aside class="space-y-6">
          <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Resumen en vivo</p>
            <div class="mt-5 rounded-[28px] bg-slate-900 p-5 text-white">
              <p class="text-xs uppercase tracking-[0.18em] text-emerald-200">Secuencia actual</p>
              <p class="mt-3 text-2xl font-semibold">{{ form.secuencia || "Sin nombre definido" }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-300">{{ form.asignatura || "Asigna una materia para ver mejor el resumen." }}</p>
            </div>
            <div class="mt-4 space-y-3">
              <div v-for="item in summaryItems" :key="item.label" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ item.label }}</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">{{ item.value }}</p>
              </div>
            </div>
          </article>

          <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Observaciones pendientes</p>
            <div class="mt-5 space-y-3">
              <div v-for="note in notes" :key="note.title" class="rounded-[24px] border border-amber-100 bg-amber-50 p-4">
                <p class="font-semibold text-amber-900">{{ note.title }}</p>
                <p class="mt-2 text-sm leading-6 text-amber-900/90">{{ note.description }}</p>
              </div>
            </div>
          </article>
        </aside>
      </section>

      <section class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Mis secuencias</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Borradores y envios recientes</h2>
          </div>
          <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
            {{ drafts.length }} registros demo
          </span>
        </div>

        <div class="mt-6 overflow-hidden rounded-[28px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secuencia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Asignatura</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Periodo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="draft in drafts" :key="draft.title">
                <td class="px-4 py-4 font-medium text-slate-900">{{ draft.title }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ draft.subject }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ draft.period }}</td>
                <td class="px-4 py-4">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="draft.className">{{ draft.status }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";
import { Input } from "@/components/ui/input";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Panel del docente",
  links: ["Paneles", "Docente"],
};

const metrics = [
  { label: "Borradores", value: "06", caption: "Secuencias en trabajo" },
  { label: "En revision", value: "03", caption: "Esperando dictamen" },
  { label: "Aprobadas", value: "12", caption: "Listas para consulta" },
];

const form = reactive({
  asignatura: "",
  grupo: "",
  secuencia: "",
  periodo: "",
  objetivo: "",
  actividad: "",
  evidencia: "",
});

const message = ref("");

const notes = [
  { title: "Comunicacion Oral", description: "Se recomienda precisar mejor el criterio de evaluacion del producto final." },
  { title: "Programacion I", description: "La revision anterior solicito alinear la evidencia con la competencia declarada." },
];

const drafts = [
  { title: "Secuencia 03", subject: "Comunicacion Oral", period: "Abril 2026", status: "Borrador", className: "bg-slate-100 text-slate-700" },
  { title: "Secuencia 05", subject: "Programacion I", period: "Abril 2026", status: "En revision", className: "bg-sky-100 text-sky-700" },
  { title: "Secuencia 01", subject: "Metodologia", period: "Marzo 2026", status: "Aprobada", className: "bg-emerald-100 text-emerald-700" },
];

const fillTemplate = () => {
  Object.assign(form, {
    asignatura: "Comunicacion Oral",
    grupo: "2A",
    secuencia: "Secuencia 03",
    periodo: "Abril 2026",
    objetivo: "Desarrollar la expresion oral mediante una presentacion estructurada con apoyo argumentativo.",
    actividad: "Exposicion guiada por equipos con apertura, desarrollo, cierre y ronda de preguntas.",
    evidencia: "Rubrica de exposicion oral, participacion en clase y producto de apoyo visual.",
  });
  message.value = "Se cargo un ejemplo institucional para que visualices como se veria el formulario en uso.";
};

const clearForm = () => {
  Object.assign(form, {
    asignatura: "",
    grupo: "",
    secuencia: "",
    periodo: "",
    objetivo: "",
    actividad: "",
    evidencia: "",
  });
  message.value = "El formulario quedo limpio para una nueva captura.";
};

const saveDraft = () => {
  message.value = `Borrador guardado para ${form.secuencia || "una secuencia sin titulo"} en modo diseno.`;
};

const submitReview = () => {
  message.value = `La secuencia ${form.secuencia || "sin titulo"} fue enviada a revision de manera demostrativa.`;
};

const summaryItems = computed(() => [
  { label: "Asignatura", value: form.asignatura || "Sin captura" },
  { label: "Grupo", value: form.grupo || "Sin captura" },
  { label: "Periodo", value: form.periodo || "Sin captura" },
  { label: "Objetivo", value: form.objetivo || "Sin captura" },
]);
</script>
