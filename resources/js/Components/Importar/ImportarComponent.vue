<template>
  <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
    <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
      <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Importacion visual</p>
      <h3 class="mt-3 text-2xl font-semibold text-slate-900">Zona preparada para carga de archivos</h3>
      <p class="mt-3 text-sm leading-6 text-slate-500">
        Este bloque quedo solo como referencia visual. Despues podremos conectar la subida real con validacion y procesamiento en Laravel.
      </p>

      <div class="mt-6 rounded-[28px] border-2 border-dashed border-emerald-200 bg-emerald-50/60 p-8 text-center">
        <p class="text-lg font-semibold text-emerald-800">Arrastra aqui tu archivo</p>
        <p class="mt-2 text-sm text-emerald-700">Formatos esperados: CSV, XLSX</p>
        <button type="button" class="mt-5 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
          Seleccionar archivo
        </button>
      </div>

      <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
        Plantilla sugerida: columnas de nombre, correo, telefono, rol y programa academico.
      </div>
    </section>

    <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
      <h3 class="text-2xl font-semibold text-slate-900">Resumen demo de importacion</h3>

      <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div v-for="metric in metrics" :key="metric.label" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
          <p class="text-sm text-slate-500">{{ metric.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
        </div>
      </div>

      <div class="mt-6 space-y-3">
        <div v-for="item in errors" :key="item.row" class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
          <p class="text-sm font-semibold text-amber-800">Fila {{ item.row }}</p>
          <p class="mt-1 text-sm text-amber-700">{{ item.message }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
const metrics = [
  { label: "Registros detectados", value: "154" },
  { label: "Importados correctamente", value: "142" },
  { label: "Con observaciones", value: "12" },
];

const errors = [
  { row: 5, message: "Correo institucional incompleto." },
  { row: 18, message: "Rol no reconocido en el catalogo." },
  { row: 27, message: "Programa academico vacio." },
];
</script>
