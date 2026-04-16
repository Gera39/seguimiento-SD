<template>
  <div class="min-h-screen bg-[linear-gradient(135deg,_#eff6ff_0%,_#f8fafc_45%,_#f0fdfa_100%)]">
    <div class="mx-auto grid min-h-screen max-w-5xl items-center gap-8 px-4 py-10 lg:grid-cols-[0.95fr_1.05fr]">
      <section class="rounded-[36px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(8,145,178,0.3)] backdrop-blur sm:p-8">
        <Head title="Restablecer contrasena" />

        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Nueva contrasena</p>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">Recupera tu acceso</h1>
        <p class="mt-4 text-sm leading-7 text-slate-600">
          El enlace es temporal. Define una contrasena nueva y despues podras volver a iniciar sesion con normalidad.
        </p>

        <div class="mt-8 rounded-[28px] border border-slate-200 bg-slate-50 p-5">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Correo asociado</p>
          <p class="mt-2 text-lg font-semibold text-slate-900">{{ form.email || "Sin correo" }}</p>
        </div>
      </section>

      <section class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <form class="space-y-4" @submit.prevent="submit">
          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Correo</span>
            <input
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
            <p v-if="form.errors.email" class="text-sm text-rose-600">{{ form.errors.email }}</p>
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Nueva contrasena</span>
            <input
              v-model="form.password"
              type="password"
              autocomplete="new-password"
              placeholder="Escribe una contrasena segura"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
            <p v-if="form.errors.password" class="text-sm text-rose-600">{{ form.errors.password }}</p>
          </label>

          <label class="space-y-2">
            <span class="text-sm font-medium text-slate-700">Confirmar contrasena</span>
            <input
              v-model="form.password_confirmation"
              type="password"
              autocomplete="new-password"
              placeholder="Repite la nueva contrasena"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
            />
          </label>

          <button
            type="submit"
            class="h-12 w-full rounded-2xl bg-slate-900 px-4 py-3 text-white transition hover:bg-slate-800"
            :disabled="form.processing"
            :class="{ 'cursor-not-allowed opacity-70': form.processing }"
          >
            {{ form.processing ? "Guardando..." : "Guardar nueva contrasena" }}
          </button>
        </form>
      </section>
    </div>
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
  form.post(route("password.store"), {
    onFinish: () => form.reset("password", "password_confirmation"),
  });
};
</script>
