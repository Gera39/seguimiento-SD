<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.05fr_0.95fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Cierre de proceso</p>
          <h1 class="mt-3 text-3xl font-semibold text-slate-900">Validacion final</h1>
          <p class="mt-3 text-sm leading-6 text-slate-500">
            Esta vista resume la decision final de una secuencia. Conserva jerarquia visual y deja fuera la logica operativa.
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Resumen ejecutivo</h2>
          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div v-for="item in summary" :key="item.label" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm text-slate-500">{{ item.label }}</p>
              <p class="mt-3 text-xl font-semibold text-slate-900">{{ item.value }}</p>
            </div>
          </div>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Decision sugerida</h2>
          <div class="mt-5 rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Resultado</p>
            <p class="mt-3 text-2xl font-semibold text-emerald-900">Aprobada con ajustes menores</p>
            <p class="mt-3 text-sm leading-6 text-emerald-800">
              La estructura general es solida. Solo faltan detalles de redaccion y claridad en un bloque de evaluacion.
            </p>
          </div>

          <div class="mt-5 space-y-3">
            <div v-for="point in checkpoints" :key="point" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              {{ point }}
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Acciones visuales</h2>
          <div class="mt-5 grid gap-3">
            <button type="button" class="h-12 rounded-2xl bg-emerald-600 text-sm font-semibold text-white">
              Aprobar secuencia
            </button>
            <button type="button" class="h-12 rounded-2xl bg-rose-600 text-sm font-semibold text-white">
              Regresar con observaciones
            </button>
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
  text: "Validacion final",
  links: ["Secuencias", "Cierre"],
};

const summary = [
  { label: "Secuencia", value: "Innovacion educativa" },
  { label: "Docente", value: "Andrea Morales" },
  { label: "Revision previa", value: "Completa" },
  { label: "Nivel de cumplimiento", value: "92%" },
];

const checkpoints = [
  "Objetivos claros y consistentes con la materia.",
  "Secuencia de actividades con progresion adecuada.",
  "Rubrica de evaluacion visible y comprensible.",
];
</script>
