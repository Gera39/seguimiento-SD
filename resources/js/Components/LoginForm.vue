<template>
  <div>
  <form @submit.prevent="handleLogin" class="space-y-4">
    <div>
      <label class="text-sm font-medium">Correo</label>
      <input
        v-model="email"
        type="email"
        class="w-full border rounded-lg p-2 mt-1"
        placeholder="correo@demo.com"
        autocomplete="email"
        required
      />
    </div>

    <div>
      <label class="text-sm font-medium">Contraseña</label>
      <input
        v-model="password"
        type="password"
        class="w-full border rounded-lg p-2 mt-1"
        placeholder="••••••"
        autocomplete="current-password"
        required
      />
    </div>

    <div
      v-if="storeError"
      class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      role="alert"
      aria-live="polite"
    >
      {{ storeError }}
    </div>
    <div
      v-else-if="storeMessage"
      :class="['rounded-lg border px-3 py-2 text-sm', feedbackPanelClass]"
      role="status"
      aria-live="polite"
    >
      {{ storeMessage }}
    </div>

    <div class="flex items-center justify-between gap-3 text-sm">
      <span class="text-slate-500">Si no recuerdas tu acceso, recuperalo aqui.</span>
      <RouterLink
        to="/recuperar-contrasena"
        class="font-semibold text-teal-700 transition hover:text-teal-800 hover:underline"
      >
        Olvide mi contrasena
      </RouterLink>
    </div>

    <button
      type="submit"
      class="w-full bg-teal-600 text-white py-2 rounded-lg hover:bg-teal-700 disabled:opacity-60 disabled:cursor-not-allowed"
      :disabled="isSubmitting"
    >
      {{ isSubmitting ? "Ingresando..." : "Ingresar" }}
    </button>
  </form>
   <ModalVFA
      v-model:open="showOtpModal"
      :email="modalEmail"
      :loading="otpLoading"
      :error="otpError"
      :dismissible="true"
      @submit="handleSubmit"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { storeToRefs } from "pinia";
import ModalVFA from "@/components/ModalVFA.vue";
import { useAuthStore } from "@/stores/auth";
import { RouterLink } from "vue-router";
import type { LoginCredentials, VerifyAuth } from "@/types/auth";

const email = ref("");
const password = ref("");

const auth = useAuthStore();
const {
  isLoading,
  error: storeError,
  message: storeMessage,
  success: storeSuccess,
} = storeToRefs(auth);

const isSubmitting = computed(() => isLoading.value);
const showOtpModal = ref(false);
const submittedEmail = ref("");
const otpLoading = ref(false);
const otpError = ref<string | null>(null);

const modalEmail = computed(() => submittedEmail.value || email.value);
const handleSubmit = async  (credentials: VerifyAuth) => {
  const ok = await auth.verify2Auth(credentials);
}  

const feedbackPanelClass = computed(() => {
  if (storeSuccess.value === true) return "border-emerald-200 bg-emerald-50 text-emerald-700";
  if (storeSuccess.value === false) return "border-amber-200 bg-amber-50 text-amber-700";
  return "border-slate-200 bg-slate-50 text-slate-600";
});

const handleLogin = async () => {
  showOtpModal.value = false;
  otpError.value = null;

  const credentials: LoginCredentials = {
    sitio_web: window.location.origin,
    correo: email.value,
    password: password.value,
  };

  const ok = await auth.login(credentials);
  if (ok) {
    submittedEmail.value = email.value;
    showOtpModal.value = true;
  }
};
</script>
