<template>
  <div class="relative min-h-screen overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(244,114,182,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(251,191,36,0.16),_transparent_28%),linear-gradient(135deg,_#fff7ed_0%,_#fff1f2_42%,_#fdf2f8_100%)]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-12 top-12 h-56 w-56 rounded-full bg-rose-200/55 blur-3xl" />
      <div class="absolute right-0 top-1/4 h-72 w-72 rounded-full bg-amber-200/45 blur-3xl" />
      <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-fuchsia-200/35 blur-3xl" />
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
      <div class="grid w-full gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <section class="overflow-hidden rounded-[34px] border border-white/70 bg-slate-950 text-white shadow-[0_35px_100px_-35px_rgba(15,23,42,0.8)]">
          <div class="flex h-full flex-col justify-between gap-10 p-8 sm:p-10">
            <div class="space-y-6">
              <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm text-rose-100 backdrop-blur">
                <Sparkles class="size-4" />
                Nueva contrasena
              </div>

              <div class="space-y-4">
                <h1 class="max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">
                  Crea una clave nueva con una vista elegante y clara.
                </h1>
                <p class="max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                  Esta pantalla esta enfocada solo en el paso final: definir tu nueva contrasena
                  y confirmarla con validaciones visuales faciles de entender.
                </p>
              </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
              <article class="rounded-3xl border border-white/10 bg-white/[0.08] p-5 backdrop-blur-sm">
                <div class="mb-4 inline-flex rounded-2xl bg-rose-400/15 p-3 text-rose-100">
                  <ShieldCheck class="size-5" />
                </div>
                <h2 class="text-lg font-semibold">Seguridad visible</h2>
                <p class="mt-2 text-sm leading-6 text-slate-300">
                  El formulario te muestra de inmediato si la contrasena cumple las reglas.
                </p>
              </article>

              <article class="rounded-3xl border border-white/10 bg-white/[0.08] p-5 backdrop-blur-sm">
                <div class="mb-4 inline-flex rounded-2xl bg-amber-400/15 p-3 text-amber-100">
                  <KeyRound class="size-5" />
                </div>
                <h2 class="text-lg font-semibold">Paso final</h2>
                <p class="mt-2 text-sm leading-6 text-slate-300">
                  Solo necesitas escribir tu nueva clave y confirmarla para terminar.
                </p>
              </article>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.08] p-5 text-sm text-slate-200 backdrop-blur-sm">
              <p class="font-semibold text-white">Vista demo</p>
              <p class="mt-2 leading-6">
                Este formulario esta listo para lucir bien y validar datos en frontend. Si luego
                quieres, lo conectamos al endpoint real de restablecimiento.
              </p>
            </div>
          </div>
        </section>

        <section class="rounded-[34px] border border-white/80 bg-white/88 p-6 shadow-[0_30px_90px_-35px_rgba(225,29,72,0.35)] backdrop-blur sm:p-8">
          <div class="space-y-6">
            <RouterLink
              to="/login"
              class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-800"
            >
              <ArrowLeft class="size-4" />
              Volver al login
            </RouterLink>

            <div class="space-y-3">
              <p class="text-sm font-semibold uppercase tracking-[0.28em] text-rose-600">
                Restablecer acceso
              </p>
              <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
                Escribe tu nueva contrasena
              </h2>
              <p class="text-sm leading-6 text-slate-500">
                Completa los campos y revisa abajo las condiciones minimas para una clave segura.
              </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-rose-600">
                  Token demo
                </p>
                <p class="mt-2 text-sm font-semibold text-slate-800">
                  {{ recoveryToken }}
                </p>
              </div>

              <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-600">
                  Estado
                </p>
                <p class="mt-2 text-sm font-semibold text-slate-800">
                  {{ strengthLabel }}
                </p>
              </div>

              <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-600">
                  Avance
                </p>
                <p class="mt-2 text-sm font-semibold text-slate-800">
                  {{ completedRequirements }}/4 reglas
                </p>
              </div>
            </div>

            <form
              class="space-y-5 rounded-[28px] border border-rose-100 bg-gradient-to-b from-white to-rose-50/60 p-5 shadow-sm"
              @submit.prevent="handleSubmit"
            >
              <div class="space-y-2">
                <label for="new-password" class="text-sm font-medium text-slate-700">
                  Nueva contrasena
                </label>
                <div class="relative">
                  <LockKeyhole class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                  <Input
                    id="new-password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Escribe una contrasena segura"
                    class="h-12 rounded-2xl border-rose-100 bg-white pl-11 pr-12"
                  />
                  <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
                    @click="showPassword = !showPassword"
                  >
                    <EyeOff v-if="showPassword" class="size-4" />
                    <Eye v-else class="size-4" />
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <label for="confirm-password" class="text-sm font-medium text-slate-700">
                  Confirmar contrasena
                </label>
                <div class="relative">
                  <ShieldCheck class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                  <Input
                    id="confirm-password"
                    v-model="form.confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="Repite tu nueva contrasena"
                    class="h-12 rounded-2xl border-rose-100 bg-white pl-11 pr-12"
                  />
                  <button
                    type="button"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <EyeOff v-if="showConfirmPassword" class="size-4" />
                    <Eye v-else class="size-4" />
                  </button>
                </div>
              </div>

              <div class="rounded-3xl border border-slate-200 bg-white/85 p-4">
                <div class="mb-4 flex items-center justify-between gap-3">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">Condiciones de la contrasena</p>
                    <p class="text-xs text-slate-500">Se marcan automaticamente conforme escribes.</p>
                  </div>
                  <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200">
                    <div
                      class="h-full rounded-full transition-all duration-300"
                      :class="strengthBarClass"
                      :style="{ width: `${strengthWidth}%` }"
                    />
                  </div>
                </div>

                <div class="grid gap-3">
                  <div
                    v-for="requirement in requirements"
                    :key="requirement.label"
                    class="flex items-center gap-3 rounded-2xl px-3 py-2 transition"
                    :class="requirement.valid ? 'bg-emerald-50 text-emerald-800' : 'bg-slate-50 text-slate-500'"
                  >
                    <CircleCheckBig class="size-4" :class="requirement.valid ? 'text-emerald-600' : 'text-slate-300'" />
                    <span class="text-sm">{{ requirement.label }}</span>
                  </div>
                </div>
              </div>

              <div
                v-if="feedback"
                class="rounded-2xl border px-4 py-3 text-sm"
                :class="feedback.type === 'success'
                  ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                  : 'border-red-200 bg-red-50 text-red-700'"
                role="status"
                aria-live="polite"
              >
                {{ feedback.text }}
              </div>

              <Button
                type="submit"
                size="lg"
                class="h-12 w-full rounded-2xl bg-rose-600 text-white hover:bg-rose-700"
              >
                Guardar nueva contrasena
              </Button>
            </form>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import {
  ArrowLeft,
  CircleCheckBig,
  Eye,
  EyeOff,
  KeyRound,
  LockKeyhole,
  ShieldCheck,
  Sparkles,
} from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { RouterLink } from "vue-router";

type FeedbackState = {
  type: "success" | "error";
  text: string;
};

const recoveryToken = "RST-2026-UTH-DEMO";
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const feedback = ref<FeedbackState | null>(null);

const form = reactive({
  password: "",
  confirmPassword: "",
});

const requirements = computed(() => [
  {
    label: "Minimo 8 caracteres",
    valid: form.password.length >= 8,
  },
  {
    label: "Al menos una letra mayuscula",
    valid: /[A-Z]/.test(form.password),
  },
  {
    label: "Al menos un numero",
    valid: /\d/.test(form.password),
  },
  {
    label: "Las dos contrasenas coinciden",
    valid: form.password.length > 0 && form.password === form.confirmPassword,
  },
]);

const completedRequirements = computed(
  () => requirements.value.filter((requirement) => requirement.valid).length
);

const strengthWidth = computed(() => (completedRequirements.value / requirements.value.length) * 100);

const strengthLabel = computed(() => {
  if (completedRequirements.value <= 1) return "Muy basica";
  if (completedRequirements.value === 2) return "Aceptable";
  if (completedRequirements.value === 3) return "Buena";
  return "Fuerte";
});

const strengthBarClass = computed(() => {
  if (completedRequirements.value <= 1) return "bg-red-400";
  if (completedRequirements.value === 2) return "bg-amber-400";
  if (completedRequirements.value === 3) return "bg-sky-500";
  return "bg-emerald-500";
});

const handleSubmit = () => {
  const allValid = requirements.value.every((requirement) => requirement.valid);

  if (!form.password || !form.confirmPassword) {
    feedback.value = {
      type: "error",
      text: "Completa ambos campos antes de continuar.",
    };
    return;
  }

  if (!allValid) {
    feedback.value = {
      type: "error",
      text: "La contrasena aun no cumple todas las condiciones.",
    };
    return;
  }

  feedback.value = {
    type: "success",
    text: "Nueva contrasena aceptada en modo demo. La vista ya esta lista para conectar con backend cuando quieras.",
  };
};
</script>
