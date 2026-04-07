<template>
  <div class="mx-auto max-w-md space-y-6 px-4 py-10">
    <Head title="Restablecer contrasena" />

    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-slate-900">Restablecer contrasena</h1>
      <p class="text-sm text-slate-600">Actualiza tu contrasena para recuperar el acceso.</p>
    </div>

    <form class="space-y-4" @submit.prevent="submit">
      <input v-model="form.email" type="email" placeholder="Correo" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <input v-model="form.password" type="password" placeholder="Nueva contrasena" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <input v-model="form.password_confirmation" type="password" placeholder="Confirmar contrasena" class="w-full rounded-xl border border-slate-200 px-4 py-3" />
      <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-white">Guardar contrasena</button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps<{
  token: string;
  email?: string;
}>();

const form = useForm({
  token: props.token,
  email: props.email ?? "",
  password: "",
  password_confirmation: "",
});

const submit = () => {
  form.post("/reset-password", {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>
