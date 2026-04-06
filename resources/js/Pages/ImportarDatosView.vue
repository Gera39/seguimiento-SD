<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Administracion</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Importar datos academicos</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
          Dejamos esta vista solo como experiencia visual, con bloques listos para futura carga masiva desde Laravel.
        </p>
      </section>

      <ImportarComponent />
    </main>
  </div>
</template>

<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";
import ImportarComponent from "@/components/Importar/ImportarComponent.vue";

defineOptions({ layout: AppLayout });

const tituloPagina = {
  text: "Importar datos",
  links: ["Administracion", "Importacion"],
};
</script>
