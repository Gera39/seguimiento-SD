<template>
  <div class="min-h-screen bg-[linear-gradient(135deg,_#f0fdfa_0%,_#f8fafc_45%,_#eff6ff_100%)]">
    <div class="mx-auto grid min-h-screen max-w-5xl items-center gap-8 px-4 py-10 lg:grid-cols-[0.95fr_1.05fr]">
      <section class="rounded-[36px] border border-white/80 bg-slate-950 p-6 text-white shadow-[0_30px_90px_-35px_rgba(15,23,42,0.7)] sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-cyan-200">Recuperacion de acceso</p>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight">Restablece tu contrasena</h1>
        <p class="mt-4 text-sm leading-7 text-slate-300">
          Enviaremos un enlace seguro a tu correo para que puedas definir una nueva contrasena y volver a entrar.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
          <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Paso 1</p>
            <p class="mt-2 font-semibold">Confirma tu correo</p>
          </div>
          <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Paso 2</p>
            <p class="mt-2 font-semibold">Abre el enlace</p>
          </div>
          <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Paso 3</p>
            <p class="mt-2 font-semibold">Crea una nueva clave</p>
          </div>
        </div>
      </section>

      <section class="rounded-[36px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(8,145,178,0.3)] backdrop-blur sm:p-8">
        <div class="space-y-6">
          <Head title="Recuperar contrasena" />

          <Link :href="route('login')" class="inline-flex text-sm font-semibold text-slate-500 transition hover:text-slate-900">
            Volver al login
          </Link>

          <div class="space-y-2">
            <h2 class="text-2xl font-semibold text-slate-900">Solicitar enlace</h2>
            <p class="text-sm text-slate-600">Usa el correo con el que entras al sistema.</p>
          </div>

          <div v-if="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ status }}
          </div>

          <form class="space-y-4" @submit.prevent="submit">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Correo</span>
              <input
                v-model="form.email"
                type="email"
                autocomplete="email"
                placeholder="nombre@uth.edu.mx"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
              />
              <p v-if="form.errors.email" class="text-sm text-rose-600">{{ form.errors.email }}</p>
            </label>

            <button
              type="submit"
              class="h-12 w-full rounded-2xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-700"
              :disabled="form.processing"
              :class="{ 'cursor-not-allowed opacity-70': form.processing }"
            >
              {{ form.processing ? "Enviando enlace..." : "Enviar enlace de recuperacion" }}
            </button>
          </form>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps<{
  status?: string | null;
}>();

const form = useForm({
  email: "",
});

const submit = () => {
  form.post(route("password.email"));
};
</script>
