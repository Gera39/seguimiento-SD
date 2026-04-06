<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-fuchsia-700">Laboratorio</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Vista de prueba</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
          Este espacio queda como sandbox visual para probar bloques, tipografias, tarjetas y estados antes de conectarlos con datos reales.
        </p>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <div v-for="card in cards" :key="card.title" class="rounded-[28px] border p-5 shadow-sm" :class="card.className">
          <p class="text-sm font-semibold uppercase tracking-[0.18em]">{{ card.eyebrow }}</p>
          <h2 class="mt-3 text-2xl font-semibold">{{ card.title }}</h2>
          <p class="mt-3 text-sm leading-6">{{ card.description }}</p>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Vista de prueba",
  links: ["Laboratorio", "UI"],
};

const cards = [
  { eyebrow: "Bloque A", title: "Tarjeta base", description: "Sirve para validar composicion, ritmo y espaciado general.", className: "border-slate-200 bg-white text-slate-700" },
  { eyebrow: "Bloque B", title: "Acento de color", description: "Permite probar atmosferas distintas sin tocar logica de la aplicacion.", className: "border-sky-100 bg-sky-50 text-sky-900" },
  { eyebrow: "Bloque C", title: "Estado destacado", description: "Ayuda a ensayar mensajes clave antes de conectarlos con backend.", className: "border-emerald-100 bg-emerald-50 text-emerald-900" },
];
</script>
