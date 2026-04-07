<template>
  <div class="space-y-5">
    <form class="space-y-4" @submit.prevent="submit">
      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Correo institucional</label>
        <input
          v-model="form.email"
          type="email"
          class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
          placeholder="nombre@uth.edu.mx"
          autocomplete="email"
        />
        <p v-if="form.errors.email" class="text-sm text-rose-600">
          {{ form.errors.email }}
        </p>
      </div>

      <div class="space-y-2">
        <label class="text-sm font-medium text-slate-700">Contrasena</label>
        <input
          v-model="form.password"
          type="password"
          class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
          placeholder="Escribe tu contrasena"
          autocomplete="current-password"
        />
        <p v-if="form.errors.password" class="text-sm text-rose-600">
          {{ form.errors.password }}
        </p>
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
        type="submit"
        :disabled="form.processing"
        class="h-12 w-full rounded-2xl bg-teal-600 text-sm font-semibold text-white transition hover:bg-teal-700"
        :class="{ 'cursor-not-allowed opacity-70': form.processing }"
      >
        {{ form.processing ? "Validando acceso..." : "Entrar al sistema" }}
      </button>
    </form>

  </div>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form.post("/login", {
    onFinish: () => form.reset("password"),
  });
};
</script>
