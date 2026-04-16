<template>
  <div class="space-y-5">
    <div v-if="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      {{ status }}
    </div>

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
        <label class="inline-flex items-center gap-2 text-slate-600">
          <input v-model="form.remember" type="checkbox" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
          Mantener sesion
        </label>

        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="font-semibold text-teal-700 transition hover:text-teal-800 hover:underline"
        >
          Olvide mi contrasena
        </Link>
      </div>

      <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
        La verificacion en dos pasos es opcional. Si la tienes activa, despues del login te pediremos tu codigo TOTP, OTP por correo o uno de tus codigos de recuperacion.
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
import { Link, useForm } from "@inertiajs/vue3";

defineProps<{
  canResetPassword: boolean;
  status?: string | null;
}>();

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
