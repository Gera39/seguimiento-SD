<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Visualizacion</p>
          <h1 class="mt-3 text-3xl font-semibold text-slate-900">Detalle de secuencia didactica</h1>
          <p class="mt-3 text-sm leading-6 text-slate-500">
            Esta pagina queda como una ficha de consulta elegante, pensada para mostrar informacion sin accion ni estados complejos.
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Contenido principal</h2>
          <div class="mt-6 space-y-4">
            <div v-for="block in blocks" :key="block.title" class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
              <p class="text-lg font-semibold text-slate-900">{{ block.title }}</p>
              <p class="mt-3 text-sm leading-6 text-slate-500">{{ block.description }}</p>
            </div>
          </div>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Ficha rapida</h2>
          <div class="mt-5 grid gap-3">
            <div v-for="item in details" :key="item.label" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ item.label }}</p>
              <p class="mt-2 text-sm font-medium text-slate-800">{{ item.value }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Recursos relacionados</h2>
          <div class="mt-5 space-y-3">
            <div v-for="resource in resources" :key="resource" class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
              {{ resource }}
            </div>
          </div>
        </article>
      </aside>
    </main>
  </div>
</template>

<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Visualizacion de secuencia",
  links: ["Secuencias", "Detalle"],
};

const blocks = [
  { title: "Contexto y proposito", description: "Describe el escenario academico, la intencion formativa y el enfoque general de la secuencia." },
  { title: "Actividades por etapa", description: "Presenta el recorrido de apertura, desarrollo y cierre con claridad visual." },
  { title: "Evaluacion y evidencias", description: "Resume entregables, instrumentos y criterios para interpretar el avance." },
];

const details = [
  { label: "Clave", value: "SD-028" },
  { label: "Docente", value: "Luisa Vega" },
  { label: "Materia", value: "Metodologia" },
  { label: "Periodo", value: "2026-1" },
];

const resources = [
  "Planeacion semanal en PDF",
  "Rubrica de evaluacion editable",
  "Material de apoyo para estudiantes",
];
</script>
