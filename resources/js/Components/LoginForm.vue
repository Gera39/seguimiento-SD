<template>
  <div class="space-y-5">
    <form class="space-y-4">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Correo institucional</label>
        <input
          v-model="email"
          type="email"
          class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
          placeholder="nombre@uth.edu.mx"
          autocomplete="email"
        />
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Contrasena</label>
        <input
          v-model="password"
          type="password"
          class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
          placeholder="Escribe tu contrasena"
          autocomplete="current-password"
        />
      </div>

      <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Vista demo de acceso. El formulario ya esta limpio y listo para conectarlo despues con Laravel.
      </div>

      <div class="flex items-center justify-between gap-3 text-sm">
        <span class="text-slate-500">Si no recuerdas tu acceso, recuperalo aqui.</span>
        <a
          href="/recuperar-contrasena"
          class="font-semibold text-teal-700 transition hover:text-teal-800 hover:underline"
        >
          Olvide mi contrasena
        </a>
      </div>

      <button
        type="button"
        class="h-12 w-full rounded-2xl bg-teal-600 text-sm font-semibold text-white transition hover:bg-teal-700"
      >
        Entrar al sistema
      </button>
    </form>

    <div class="grid gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
      <div class="flex items-center justify-between">
        <span>Estado visual</span>
        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Listo</span>
      </div>
      <p>
        Se eliminaron validaciones, store y modal de autenticacion para dejar solo la interfaz base.
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

const email = ref("");
const password = ref("");
</script>
