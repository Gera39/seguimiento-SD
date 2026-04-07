<template>
  <div class="mx-auto max-w-md space-y-6 px-4 py-10">
    <Head title="Registro" />

    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-slate-900">Crear cuenta</h1>
      <p class="text-sm text-slate-600">Registro inicial para acceso al sistema.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
      <input v-model="form.name" type="text" placeholder="Nombre completo" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <input v-model="form.email" type="email" placeholder="Correo" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <input v-model="form.password" type="password" placeholder="Contrasena" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <input v-model="form.password_confirmation" type="password" placeholder="Confirmar contrasena" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-white">Registrarme</button>
    </form>

    <Link href="/login" class="text-sm text-slate-600 underline">Ya tengo cuenta</Link>
  </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post("/register", {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>
