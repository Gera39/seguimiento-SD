<template>
  <div class="mx-auto max-w-md space-y-6 px-4 py-10">
    <Head title="Confirmar contrasena" />

    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-slate-900">Confirmar contrasena</h1>
      <p class="text-sm text-slate-600">Necesitamos validar tu identidad antes de continuar.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
      <input v-model="form.password" type="password" placeholder="Contrasena actual" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-white">Confirmar</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
  password: "",
});

const submit = () => {
  form.post("/confirm-password", {
    onFinish: () => form.reset("password"),
  });
};
</script>
