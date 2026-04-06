<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <article class="overflow-hidden rounded-[32px] border border-slate-200 bg-[linear-gradient(135deg,_#0f172a_0%,_#111827_45%,_#164e63_100%)] p-8 text-white shadow-[0_30px_90px_-40px_rgba(15,23,42,0.8)]">
          <p class="inline-flex rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm text-cyan-100">
            Panel principal
          </p>
          <h1 class="mt-5 max-w-2xl text-4xl font-semibold tracking-tight">
            Seguimiento claro de secuencias, revisiones y avance academico.
          </h1>
          <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">
            Esta version ya no depende de stores ni permisos. Quedo como dashboard base con tarjetas y secciones listas para alimentar despues desde controladores o consultas Laravel.
          </p>

          <div class="mt-8 grid gap-4 sm:grid-cols-3">
            <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-white/10 bg-white/[0.08] p-4">
              <p class="text-sm text-slate-300">{{ metric.label }}</p>
              <p class="mt-3 text-3xl font-semibold">{{ metric.value }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Acciones rapidas</p>
          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <a
              v-for="action in actions"
              :key="action.title"
              :href="action.href"
              class="rounded-3xl border border-slate-200 bg-slate-50 p-5 transition hover:-translate-y-0.5 hover:bg-white hover:shadow-md"
            >
              <p class="text-base font-semibold text-slate-900">{{ action.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-500">{{ action.description }}</p>
            </a>
          </div>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Alertas</p>
          <div class="mt-6 space-y-4">
            <div v-for="alert in alerts" :key="alert.title" class="rounded-3xl border px-5 py-4" :class="alert.className">
              <p class="text-sm font-semibold">{{ alert.title }}</p>
              <p class="mt-2 text-sm leading-6">{{ alert.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Actividad reciente</p>
          <div class="mt-6 space-y-4">
            <div v-for="item in activity" :key="item.title" class="flex items-start gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <div class="mt-1 size-3 rounded-full bg-emerald-500" />
              <div>
                <p class="font-semibold text-slate-900">{{ item.title }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ item.description }}</p>
                <p class="mt-2 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ item.time }}</p>
              </div>
            </div>
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
  text: "Dashboard",
  links: ["Inicio", "Panel principal"],
};

const metrics = [
  { label: "Secuencias activas", value: "128" },
  { label: "Pendientes de revision", value: "26" },
  { label: "Publicadas este mes", value: "14" },
];

const actions = [
  { title: "Abrir validaciones", description: "Revisa entregas pendientes y observa el estado general del flujo.", href: "/validaciones" },
  { title: "Ir a reportes", description: "Consulta indicadores, estados y tendencias visuales de secuencias.", href: "/reportes" },
  { title: "Ver docentes", description: "Explora la lista visual de docentes y revisores activos.", href: "/docentes" },
  { title: "Importar datos", description: "Accede al modulo demo de importacion masiva.", href: "/importar-datos" },
];

const alerts = [
  {
    title: "Revision semanal pendiente",
    description: "Hay entregas nuevas esperando validacion del coordinador academico.",
    className: "border-amber-100 bg-amber-50 text-amber-800",
  },
  {
    title: "Carga de datos lista",
    description: "El modulo de importacion ya quedo preparado visualmente para futura conexion con backend.",
    className: "border-emerald-100 bg-emerald-50 text-emerald-800",
  },
];

const activity = [
  { title: "Secuencia de Programacion revisada", description: "Se marco como lista para validacion final en la vista demo.", time: "Hace 12 minutos" },
  { title: "Nueva plantilla de importacion", description: "Se actualizo el bloque visual para soportar usuarios y asignaturas.", time: "Hace 1 hora" },
  { title: "Dashboard simplificado", description: "Se eliminaron dependencias heredadas de autenticacion y router.", time: "Hoy" },
];
</script>
