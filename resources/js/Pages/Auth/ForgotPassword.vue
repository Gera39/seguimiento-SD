<template>
  <div class="mx-auto max-w-md space-y-6 px-4 py-10">
    <Head title="Recuperar contrasena" />

    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-slate-900">Recuperar contrasena</h1>
      <p class="text-sm text-slate-600">Enviaremos un enlace para restablecer tu acceso.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
      <input v-model="form.email" type="email" placeholder="Correo" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-white">Enviar enlace</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
  email: "",
});

const submit = () => {
  form.post("/forgot-password");
};
</script>
