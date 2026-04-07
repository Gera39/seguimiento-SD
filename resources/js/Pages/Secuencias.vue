<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Biblioteca personal</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Mis planeaciones didacticas</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Consulta tus borradores, revisa el estado actual y abre cada planeacion para editarla o enviarla a revision.
            </p>
          </div>
          <div class="flex flex-wrap gap-3">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
              {{ plans.length }} planeacion(es) registradas
            </div>
            <Link :href="createUrl" class="rounded-full bg-teal-600 px-5 py-3 text-sm font-semibold text-white">
              Nueva planeacion
            </Link>
          </div>
        </div>

        <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ status }}
        </p>
      </section>

      <section v-if="plans.length" class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
        <article
          v-for="item in plans"
          :key="item.id"
          class="relative rounded-[30px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
          <span class="absolute right-5 top-5 rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(item.statusCode)">
            {{ item.status }}
          </span>

          <div class="pr-20">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ item.folio }}</p>
            <h2 class="mt-3 text-xl font-semibold text-slate-900">{{ item.title }}</h2>
          </div>

          <div class="mt-5 space-y-2 text-sm text-slate-600">
            <p>Materia: {{ item.subject }}</p>
            <p>Grupo: {{ item.group }}</p>
            <p>Actualizada: {{ item.updatedAt }}</p>
          </div>

          <p class="mt-4 text-sm leading-6 text-slate-500">{{ item.description }}</p>
          <p class="mt-3 text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ item.validationSummary }}</p>

          <div class="mt-6 flex items-center justify-between gap-3">
            <Link :href="item.href" class="text-sm font-semibold text-teal-700 hover:text-teal-800">
              Ver detalle
            </Link>
            <Link
              v-if="item.editUrl"
              :href="item.editUrl"
              class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
            >
              Editar
            </Link>
          </div>
        </article>
      </section>

      <section v-else class="rounded-[32px] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
        <h2 class="text-2xl font-semibold text-slate-900">Todavia no hay planeaciones</h2>
        <p class="mt-3 text-sm leading-6 text-slate-500">
          Crea tu primera planeacion para comenzar el flujo de captura, revision y autorizacion.
        </p>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

defineProps<{
  plans: Array<{
    id: number;
    folio: string;
    title: string;
    subject: string;
    group: string;
    updatedAt: string;
    status: string;
    statusCode: string;
    description: string;
    validationSummary: string;
    href: string;
    editUrl: string | null;
  }>;
  createUrl: string;
  status?: string | null;
}>();

const tituloPagina = {
  text: "Mis secuencias",
  links: ["Planeaciones", "Listado personal"],
};

const statusClass = (code: string) => {
  if (code === "AUTHORIZED") return "bg-emerald-100 text-emerald-700";
  if (code === "UNDER_REVIEW" || code === "SUBMITTED") return "bg-amber-100 text-amber-700";
  if (code === "FEEDBACK") return "bg-sky-100 text-sky-700";
  if (code === "REJECTED") return "bg-rose-100 text-rose-700";
  return "bg-slate-100 text-slate-700";
};
</script>
