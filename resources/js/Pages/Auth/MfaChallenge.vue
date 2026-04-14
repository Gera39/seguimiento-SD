<template>
  <div class="min-h-screen bg-[linear-gradient(135deg,_#ecfeff_0%,_#f8fafc_45%,_#eff6ff_100%)]">
    <div class="mx-auto grid min-h-screen max-w-5xl items-center gap-8 px-4 py-10 lg:grid-cols-[0.95fr_1.05fr]">
      <section class="rounded-[36px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(8,145,178,0.28)] backdrop-blur sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Autenticacion multifactor</p>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">Confirma tu acceso</h1>
        <p class="mt-4 text-sm leading-7 text-slate-600">
          Tu cuenta tiene MFA activo. Completa el reto con tu autenticador o con el OTP enviado por correo, segun el metodo principal activo.
        </p>

        <div class="mt-6 rounded-[28px] border border-slate-200 bg-slate-50 p-5">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Metodo principal</p>
          <p class="mt-2 text-lg font-semibold text-slate-900">{{ challenge.label }}</p>
          <p v-if="challenge.destination_masked" class="mt-2 text-sm text-slate-500">Destino: {{ challenge.destination_masked }}</p>
          <p class="mt-2 text-sm text-slate-500">Codigos de recuperacion disponibles: {{ challenge.recovery_codes_remaining }}</p>
        </div>

        <div v-if="status" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ status }}
        </div>
      </section>

      <section class="space-y-6">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">
              {{ challenge.type === "EMAIL_OTP" ? "Codigo OTP" : "Codigo TOTP" }}
            </p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">
              {{ challenge.type === "EMAIL_OTP" ? "Verificacion por correo" : "Aplicacion autenticadora" }}
            </h2>
            <p v-if="challenge.type === 'EMAIL_OTP'" class="mt-2 text-sm leading-6 text-slate-600">
              Ingresa el codigo de 6 digitos enviado a {{ challenge.destination_masked }}.
            </p>
          </div>

          <form class="mt-6 space-y-4" @submit.prevent="submitTotp">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Codigo de 6 digitos</span>
              <input
                v-model="totpForm.code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-slate-900 outline-none transition focus:border-teal-500 focus:ring-4 focus:ring-teal-100"
                placeholder="123456"
              />
              <p v-if="totpForm.errors.code" class="text-sm text-rose-600">{{ totpForm.errors.code }}</p>
            </label>

            <button
              v-if="challenge.type === 'EMAIL_OTP'"
              type="button"
              :disabled="resendForm.processing"
              class="h-12 w-full rounded-2xl border border-slate-300 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              :class="{ 'cursor-not-allowed opacity-70': resendForm.processing }"
              @click="resendOtp"
            >
              {{ resendForm.processing ? "Reenviando..." : "Reenviar OTP" }}
            </button>

            <button
              type="submit"
              :disabled="totpForm.processing"
              class="h-12 w-full rounded-2xl bg-teal-600 text-sm font-semibold text-white transition hover:bg-teal-700"
              :class="{ 'cursor-not-allowed opacity-70': totpForm.processing }"
            >
              {{ totpForm.processing ? "Verificando..." : challenge.type === "EMAIL_OTP" ? "Validar OTP" : "Validar codigo" }}
            </button>
          </form>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Recuperacion</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Usar codigo de respaldo</h2>
          </div>

          <form class="mt-6 space-y-4" @submit.prevent="submitRecoveryCode">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Codigo de recuperacion</span>
              <input
                v-model="recoveryForm.recovery_code"
                type="text"
                autocomplete="off"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-slate-900 outline-none transition focus:border-slate-500 focus:ring-4 focus:ring-slate-100"
                placeholder="ABCD-EFGH"
              />
              <p v-if="recoveryForm.errors.recovery_code" class="text-sm text-rose-600">{{ recoveryForm.errors.recovery_code }}</p>
            </label>

            <button
              type="submit"
              :disabled="recoveryForm.processing"
              class="h-12 w-full rounded-2xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800"
              :class="{ 'cursor-not-allowed opacity-70': recoveryForm.processing }"
            >
              {{ recoveryForm.processing ? "Validando..." : "Usar codigo de recuperacion" }}
            </button>
          </form>
        </article>

        <div class="flex justify-end">
          <Link href="/logout" method="post" as="button" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
            Cerrar sesion
          </Link>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps<{
  challenge: {
    label: string;
    type: string;
    destination_masked?: string | null;
    recovery_codes_remaining: number;
    expires_at?: string | null;
    resend_available_at?: string | null;
  };
  status?: string | null;
}>();

const totpForm = useForm({
  code: "",
});

const recoveryForm = useForm({
  recovery_code: "",
});

const resendForm = useForm({});

const submitTotp = () => {
  totpForm.post("/mfa/challenge", {
    onFinish: () => totpForm.reset("code"),
  });
};

const submitRecoveryCode = () => {
  recoveryForm.post("/mfa/challenge/recovery-code", {
    onFinish: () => recoveryForm.reset("recovery_code"),
  });
};

const resendOtp = () => {
  resendForm.post("/mfa/challenge/resend");
};
</script>
