<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[36px] border border-slate-200 bg-[radial-gradient(circle_at_top_left,_rgba(45,212,191,0.22),_transparent_28%),linear-gradient(135deg,_#082f49_0%,_#0f172a_48%,_#134e4a_100%)] p-8 text-white shadow-[0_35px_90px_-45px_rgba(15,23,42,0.85)]">
        <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-teal-100">
          Portal institucional
        </span>
        <h1 class="mt-5 max-w-3xl text-4xl font-semibold tracking-tight">
          Sistema visual para seguimiento de secuencias didacticas
        </h1>
        <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-200">
          Esta portada ya separa el sistema por perfiles institucionales. Desde aqui podemos entrar al panel del docente, del revisor academico o de coordinacion, manteniendo una misma logica de trabajo y una identidad visual comun.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
          <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/10 bg-white/[0.08] p-4 backdrop-blur-sm">
            <p class="text-sm text-slate-300">{{ metric.label }}</p>
            <p class="mt-3 text-3xl font-semibold">{{ metric.value }}</p>
            <p class="mt-2 text-xs uppercase tracking-[0.18em] text-teal-100/80">{{ metric.caption }}</p>
          </div>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1.08fr_0.92fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Ingreso por panel</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Selecciona el perfil que deseas visualizar</h2>

          <div class="mt-6 grid gap-4">
            <a
              v-for="panel in panels"
              :key="panel.title"
              :href="panel.href"
              class="group rounded-[30px] border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white hover:shadow-md"
            >
              <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]" :class="panel.badgeClass">
                    {{ panel.role }}
                  </span>
                  <h3 class="mt-3 text-xl font-semibold text-slate-900">{{ panel.title }}</h3>
                  <p class="mt-2 text-sm leading-6 text-slate-600">{{ panel.description }}</p>
                </div>
                <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold text-white">
                  Abrir
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="item in panel.tags"
                  :key="item"
                  class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200"
                >
                  {{ item }}
                </span>
              </div>
            </a>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Como entender la separacion</p>
          <div class="mt-6 space-y-4">
            <div v-for="step in steps" :key="step.title" class="flex gap-4 rounded-[28px] border border-slate-200 bg-slate-50 p-4">
              <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">
                {{ step.step }}
              </div>
              <div>
                <p class="font-semibold text-slate-900">{{ step.title }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ step.description }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 rounded-[28px] border border-dashed border-teal-200 bg-teal-50 p-5">
            <p class="font-semibold text-teal-900">Ejemplo rapido</p>
            <p class="mt-3 text-sm leading-7 text-teal-900/90">
              El docente captura y envia. El revisor analiza y comenta. La coordinacion supervisa cumplimiento y atrasos. Son tres paneles distintos, pero todos pertenecen al mismo sistema.
            </p>
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Dashboard institucional",
  links: ["Inicio", "Portal de paneles"],
};

const metrics = [
  { label: "Paneles institucionales", value: "03", caption: "Docente, revisor y coordinacion" },
  { label: "Un solo sistema", value: "01", caption: "Misma base y mismo flujo" },
  { label: "Estado actual", value: "Demo", caption: "Seguimos en diseno visual" },
];

const panels = [
  {
    role: "Docente",
    title: "Panel institucional del docente",
    description: "Vista para crear, actualizar, duplicar y enviar secuencias didacticas a revision.",
    href: "/panel-docente",
    badgeClass: "bg-emerald-100 text-emerald-800",
    tags: ["Captura", "Plantillas", "Correcciones"],
  },
  {
    role: "Revisor academico",
    title: "Panel institucional del revisor",
    description: "Vista para analizar secuencias, emitir observaciones y registrar el dictamen academico.",
    href: "/panel-revisor",
    badgeClass: "bg-sky-100 text-sky-800",
    tags: ["Revision", "Rubrica", "Dictamen"],
  },
  {
    role: "Coordinacion academica",
    title: "Panel institucional de coordinacion",
    description: "Vista ejecutiva para monitorear cumplimiento, detectar retrasos y tomar decisiones.",
    href: "/panel-coordinacion",
    badgeClass: "bg-amber-100 text-amber-800",
    tags: ["Indicadores", "Alertas", "Seguimiento"],
  },
];

const steps = [
  { step: "1", title: "Un mismo sistema", description: "Todos los paneles comparten una misma logica institucional, pero cada uno muestra solo lo necesario para su rol." },
  { step: "2", title: "Experiencia separada", description: "El docente no necesita ver el tablero directivo, y la coordinacion no necesita editar secuencias." },
  { step: "3", title: "Escalabilidad futura", description: "Despues podemos conectar permisos reales, base de datos y navegacion contextual sin rehacer el diseno." },
];
</script>
