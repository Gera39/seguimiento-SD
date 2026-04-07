<template>
  <div class="mx-auto max-w-5xl space-y-8 px-4 py-10">
    <Head title="Perfil" />

    <section class="space-y-4 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h1 class="text-3xl font-semibold text-slate-900">Perfil y seguridad</h1>
          <p class="text-sm text-slate-600">Actualiza tus datos de acceso y administra MFA para tu cuenta.</p>
        </div>
        <div v-if="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ status }}
        </div>
      </div>

      <form class="grid gap-4 md:grid-cols-2" @submit.prevent="updateProfile">
        <label class="space-y-2">
          <span class="text-sm font-medium text-slate-700">Nombre</span>
          <input v-model="profileForm.name" type="text" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4" />
          <p v-if="profileForm.errors.name" class="text-sm text-rose-600">{{ profileForm.errors.name }}</p>
        </label>

        <label class="space-y-2">
          <span class="text-sm font-medium text-slate-700">Correo</span>
          <input v-model="profileForm.email" type="email" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4" />
          <p v-if="profileForm.errors.email" class="text-sm text-rose-600">{{ profileForm.errors.email }}</p>
        </label>

        <div class="md:col-span-2">
          <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">
            Guardar cambios
          </button>
        </div>
      </form>
    </section>

    <section class="grid gap-8 xl:grid-cols-[1.08fr_0.92fr]">
      <article class="space-y-4 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Seguridad avanzada</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Autenticacion multifactor</h2>
          <p class="mt-2 text-sm leading-6 text-slate-600">
            Protege tu cuenta con un segundo factor basado en codigos temporales y codigos de recuperacion.
          </p>
        </div>

        <div v-if="mfaRecoveryCodes.length" class="rounded-[28px] border border-amber-200 bg-amber-50 p-5">
          <p class="text-sm font-semibold text-amber-900">Guarda estos codigos de recuperacion</p>
          <p class="mt-2 text-sm leading-6 text-amber-800">Solo se muestran una vez. Te serviran si pierdes acceso a tu autenticador.</p>
          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div v-for="code in mfaRecoveryCodes" :key="code" class="rounded-2xl border border-amber-200 bg-white px-4 py-3 font-mono text-sm font-semibold text-slate-900">
              {{ code }}
            </div>
          </div>
        </div>

        <div v-if="!mfa.enabled && !mfa.pending" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
          <p class="text-lg font-semibold text-slate-900">MFA no esta activo</p>
          <p class="mt-2 text-sm leading-6 text-slate-600">Genera una clave TOTP para vincular tu cuenta con Google Authenticator, Microsoft Authenticator o una app compatible.</p>

          <button
            type="button"
            class="mt-5 rounded-2xl bg-teal-600 px-5 py-3 text-sm font-semibold text-white"
            :disabled="enableMfaForm.processing"
            @click="enableMfa"
          >
            {{ enableMfaForm.processing ? "Preparando..." : "Activar MFA" }}
          </button>
        </div>

        <div v-if="mfa.pending" class="rounded-[28px] border border-teal-200 bg-teal-50 p-5">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-lg font-semibold text-slate-900">Configuracion pendiente</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">
                Registra esta clave en tu autenticador y confirma un codigo de 6 digitos para finalizar.
              </p>
            </div>
            <button type="button" class="text-sm font-semibold text-slate-600" @click="cancelPendingMfa">
              Cancelar
            </button>
          </div>

          <div class="mt-5 grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-teal-200 bg-white px-4 py-4">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Clave secreta</p>
              <p class="mt-2 font-mono text-lg font-semibold text-slate-900">{{ mfa.pending.formatted_secret }}</p>
            </div>

            <div class="rounded-2xl border border-teal-200 bg-white px-4 py-4">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">URI otpauth</p>
              <p class="mt-2 break-all text-sm leading-6 text-slate-600">{{ mfa.pending.otpauth_uri }}</p>
            </div>
          </div>

          <form class="mt-5 space-y-3" @submit.prevent="confirmMfa">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Codigo de verificacion</span>
              <input
                v-model="confirmMfaForm.code"
                type="text"
                inputmode="numeric"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4"
                placeholder="123456"
              />
              <p v-if="confirmMfaForm.errors.code" class="text-sm text-rose-600">{{ confirmMfaForm.errors.code }}</p>
            </label>

            <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white" :disabled="confirmMfaForm.processing">
              {{ confirmMfaForm.processing ? "Confirmando..." : "Confirmar MFA" }}
            </button>
          </form>
        </div>

        <div v-if="mfa.enabled && mfa.method" class="space-y-4 rounded-[28px] border border-emerald-200 bg-emerald-50 p-5">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-lg font-semibold text-slate-900">MFA activo</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">Metodo principal: {{ mfa.method.label }}</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700">
              {{ mfa.method.recovery_codes_remaining }} codigos disponibles
            </span>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-emerald-200 bg-white px-4 py-4">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Activado</p>
              <p class="mt-2 text-sm text-slate-600">{{ mfa.method.confirmed_at || "Sin fecha" }}</p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-white px-4 py-4">
              <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Ultimo uso</p>
              <p class="mt-2 text-sm text-slate-600">{{ mfa.method.last_used_at || "Aun no registrado" }}</p>
            </div>
          </div>

          <form class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4" @submit.prevent="regenerateCodes">
            <p class="text-sm font-semibold text-slate-900">Regenerar codigos de recuperacion</p>
            <input
              v-model="recoveryCodesForm.password"
              type="password"
              placeholder="Confirma tu contrasena"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
            />
            <p v-if="recoveryCodesForm.errors.password" class="text-sm text-rose-600">{{ recoveryCodesForm.errors.password }}</p>
            <button type="submit" class="rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700" :disabled="recoveryCodesForm.processing">
              {{ recoveryCodesForm.processing ? "Regenerando..." : "Generar nuevos codigos" }}
            </button>
          </form>

          <form class="space-y-3 rounded-2xl border border-rose-200 bg-white p-4" @submit.prevent="disableMfa">
            <p class="text-sm font-semibold text-slate-900">Desactivar MFA</p>
            <input
              v-model="disableMfaForm.password"
              type="password"
              placeholder="Confirma tu contrasena"
              class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
            />
            <p v-if="disableMfaForm.errors.password" class="text-sm text-rose-600">{{ disableMfaForm.errors.password }}</p>
            <button type="submit" class="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white" :disabled="disableMfaForm.processing">
              {{ disableMfaForm.processing ? "Desactivando..." : "Desactivar MFA" }}
            </button>
          </form>
        </div>
      </article>

      <article class="space-y-4 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div>
          <h2 class="text-xl font-semibold text-slate-900">Eliminar cuenta</h2>
          <p class="text-sm text-slate-600">Esta accion cerrara tu acceso de forma permanente.</p>
        </div>

        <form class="space-y-4" @submit.prevent="deleteAccount">
          <input v-model="deleteForm.password" type="password" placeholder="Confirma tu contrasena" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4" />
          <p v-if="deleteForm.errors.password" class="text-sm text-rose-600">{{ deleteForm.errors.password }}</p>
          <button type="submit" class="rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white">Eliminar cuenta</button>
        </form>
      </article>
    </section>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";

const props = defineProps<{
  mustVerifyEmail?: boolean;
  status?: string | null;
  mfa: {
    enabled: boolean;
    method: null | {
      id: number;
      label: string;
      confirmed_at: string | null;
      last_used_at: string | null;
      recovery_codes_remaining: number;
    };
    pending: null | {
      id: number;
      label: string;
      secret: string;
      formatted_secret: string;
      otpauth_uri: string;
    };
  };
  mfaRecoveryCodes: string[];
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
}>();

const profileForm = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
});

const deleteForm = useForm({
  password: "",
});

const enableMfaForm = useForm({});

const confirmMfaForm = useForm({
  code: "",
});

const disableMfaForm = useForm({
  password: "",
});

const recoveryCodesForm = useForm({
  password: "",
});

const updateProfile = () => {
  profileForm.patch("/profile");
};

const enableMfa = () => {
  enableMfaForm.post("/profile/mfa");
};

const confirmMfa = () => {
  confirmMfaForm.post("/profile/mfa/confirm", {
    onFinish: () => confirmMfaForm.reset("code"),
  });
};

const cancelPendingMfa = () => {
  enableMfaForm.delete("/profile/mfa/pending");
};

const disableMfa = () => {
  disableMfaForm.delete("/profile/mfa", {
    onFinish: () => disableMfaForm.reset("password"),
  });
};

const regenerateCodes = () => {
  recoveryCodesForm.post("/profile/mfa/recovery-codes", {
    onFinish: () => recoveryCodesForm.reset("password"),
  });
};

const deleteAccount = () => {
  deleteForm.delete("/profile", {
    onFinish: () => deleteForm.reset("password"),
  });
};
</script>
