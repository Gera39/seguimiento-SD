<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Biblioteca personal</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Mis secuencias didacticas</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              La vista ya no ordena, filtra ni navega por codigo. Quedo como escaparate visual para las tarjetas de secuencias.
            </p>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
            Orden sugerido: ultima actualizacion
          </div>
        </div>
      </section>

      <section class="grid gap-5 md:grid-cols-2 2xl:grid-cols-4">
        <article v-for="item in items" :key="item.title" class="relative rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
          <span class="absolute right-5 top-5 rounded-full px-3 py-1 text-xs font-semibold" :class="item.className">
            {{ item.status }}
          </span>

          <div class="pr-20">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ item.code }}</p>
            <h2 class="mt-3 text-xl font-semibold text-slate-900">{{ item.title }}</h2>
          </div>

          <div class="mt-5 space-y-2 text-sm text-slate-600">
            <p>Materia: {{ item.subject }}</p>
            <p>Grupo: {{ item.group }}</p>
            <p>Actualizada: {{ item.updatedAt }}</p>
          </div>

          <p class="mt-4 text-sm leading-6 text-slate-500">{{ item.description }}</p>

          <div class="mt-6 flex items-center justify-between">
            <a :href="item.href" class="text-sm font-semibold text-teal-700 hover:text-teal-800">
              Ver detalle
            </a>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
              Demo
            </span>
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
  text: "Mis secuencias",
  links: ["Secuencias", "Listado personal"],
};

const items = [
  { code: "SD-014", title: "Desarrollo web con componentes", subject: "Aplicaciones web", group: "3A", updatedAt: "20 Mar 2026", status: "Aprobada", className: "bg-emerald-100 text-emerald-700", description: "Tarjeta limpia para mostrar una secuencia consolidada y lista para consulta.", href: "/visualizacion-secuencia" },
  { code: "SD-028", title: "Taller de innovacion educativa", subject: "Metodologia", group: "5B", updatedAt: "18 Mar 2026", status: "En revision", className: "bg-amber-100 text-amber-700", description: "Vista preparada para resaltar secuencias con comentarios pendientes.", href: "/visualizacion-secuencia" },
  { code: "SD-037", title: "Planeacion de evaluaciones", subject: "Gestion academica", group: "4C", updatedAt: "12 Mar 2026", status: "Pendiente", className: "bg-sky-100 text-sky-700", description: "Bloque base listo para conectar acciones futuras desde Laravel.", href: "/visualizacion-secuencia" },
  { code: "SD-041", title: "Estrategias de presentacion oral", subject: "Comunicacion", group: "2D", updatedAt: "05 Mar 2026", status: "Rechazada", className: "bg-rose-100 text-rose-700", description: "Ejemplo visual de una secuencia con observaciones o retroalimentacion.", href: "/visualizacion-secuencia" },
];
</script>
