<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="grid gap-6 p-4 md:p-6 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Revision visual</p>
          <h1 class="mt-3 text-3xl font-semibold text-slate-900">Secuencia en analisis</h1>
          <p class="mt-3 text-sm leading-6 text-slate-500">
            Pantalla simplificada para mostrar una revision paso a paso sin formularios ni navegacion por codigo.
          </p>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Bloques revisados</h2>
          <div class="mt-6 space-y-4">
            <div v-for="section in sections" :key="section.title" class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
              <div class="flex items-center justify-between gap-3">
                <p class="font-semibold text-slate-900">{{ section.title }}</p>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="section.className">
                  {{ section.status }}
                </span>
              </div>
              <p class="mt-3 text-sm leading-6 text-slate-500">{{ section.description }}</p>
            </div>
          </div>
        </article>
      </section>

      <aside class="space-y-6">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Observaciones</h2>
          <div class="mt-5 space-y-3">
            <div v-for="note in notes" :key="note.title" class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
              <p class="font-semibold text-amber-800">{{ note.title }}</p>
              <p class="mt-1 text-sm leading-6 text-amber-700">{{ note.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <h2 class="text-2xl font-semibold text-slate-900">Acciones sugeridas</h2>
          <div class="mt-5 grid gap-3">
            <a href="/validacion-final" class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white">
              Continuar a validacion final
            </a>
            <a href="/reportes" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
              Volver al reporte general
            </a>
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
  text: "Revision de secuencia",
  links: ["Secuencias", "Revision visual"],
};

const sections = [
  { title: "Portada y datos base", status: "Correcto", className: "bg-emerald-100 text-emerald-700", description: "La informacion general se presenta completa y ordenada para su lectura." },
  { title: "Objetivos y competencias", status: "Ajustar", className: "bg-amber-100 text-amber-700", description: "Conviene resumir mejor los objetivos para hacerlos medibles." },
  { title: "Evidencias y entregables", status: "Pendiente", className: "bg-sky-100 text-sky-700", description: "Falta describir con mas claridad el criterio para evaluar productos finales." },
];

const notes = [
  { title: "Alinear indicadores", description: "Las evidencias pueden amarrarse mejor con los resultados de aprendizaje." },
  { title: "Reducir texto largo", description: "Hay bloques extensos que se pueden convertir en puntos de apoyo mas claros." },
];
</script>
