<template>
  <div class="min-h-screen bg-[linear-gradient(135deg,_#eff6ff_0%,_#f8fafc_45%,_#ecfeff_100%)]">
    <Head title="Iniciar sesion" />

    <div class="mx-auto grid min-h-screen max-w-4xl items-center gap-8 px-4 py-10 lg:grid-cols-1">

      <section class="rounded-[36px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(8,145,178,0.3)] backdrop-blur sm:p-8">
        <div class="space-y-6">
          <div class="space-y-2 text-center">
            <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
              Iniciar sesion
            </h2>
            <p class="mx-auto max-w-xl text-sm leading-6 text-slate-600">
              Accede con tu correo y contrasena. Si tu cuenta tiene 2FA activado, te pediremos un segundo codigo para completar el acceso.
            </p>
          </div>

          <LoginForm :can-reset-password="canResetPassword" :status="status" />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import LoginForm from "@/components/LoginForm.vue";

defineProps<{
  canResetPassword: boolean;
  status?: string | null;
}>();
</script>
