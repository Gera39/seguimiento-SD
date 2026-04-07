<template>
  <div class="mx-auto max-w-md space-y-6 px-4 py-10">
    <Head title="Verificar correo" />

    <div class="space-y-2">
      <h1 class="text-2xl font-semibold text-slate-900">Verificar correo</h1>
      <p class="text-sm text-slate-600">Revisa tu bandeja de entrada y valida tu cuenta.</p>
      <p v-if="status" class="text-sm text-emerald-700">{{ status }}</p>
    </div>

    <form @submit.prevent="submit">
      <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-white">
        Reenviar correo de verificacion
      </button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

defineProps<{
  status?: string | null;
}>();

const form = useForm({});

const submit = () => {
  form.post("/email/verification-notification");
};
</script>
