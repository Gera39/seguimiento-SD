<template>
  <div class="min-h-screen bg-[linear-gradient(135deg,_#eff6ff_0%,_#f8fafc_45%,_#ecfeff_100%)]">
    <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-8 px-4 py-10 lg:grid-cols-[1.05fr_0.95fr]">
      <section class="overflow-hidden rounded-[36px] border border-white/70 bg-slate-950 text-white shadow-[0_40px_120px_-40px_rgba(15,23,42,0.85)]">
        <div class="flex h-full flex-col justify-between gap-10 p-8 sm:p-10">
          <div class="space-y-6">
            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm text-cyan-100">
              Seguimiento SD
            </div>
            <div class="space-y-4">
              <h1 class="max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">
                Una base visual clara para el sistema academico.
              </h1>
              <p class="max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
                Dejamos esta pantalla solo con diseno, estructura y tono visual. Mas adelante podremos conectar el login real con Laravel sin volver a ensuciar la interfaz.
              </p>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <article class="rounded-3xl border border-white/10 bg-white/[0.08] p-5">
              <p class="text-sm text-slate-300">Estado</p>
              <p class="mt-2 text-2xl font-semibold">Listo para backend</p>
            </article>
            <article class="rounded-3xl border border-white/10 bg-white/[0.08] p-5">
              <p class="text-sm text-slate-300">Objetivo</p>
              <p class="mt-2 text-2xl font-semibold">Solo diseno limpio</p>
            </article>
          </div>
        </div>
      </section>

      <section class="rounded-[36px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(8,145,178,0.3)] backdrop-blur sm:p-8">
        <div class="space-y-6">
          <div class="space-y-2 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-teal-700">
              Acceso visual
            </p>
            <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
              Iniciar sesion
            </h2>
            <p class="text-sm leading-6 text-slate-500">
              Interfaz base lista para integrarse despues con autenticacion real.
            </p>
          </div>

          <LoginForm />
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
import LoginForm from "@/components/LoginForm.vue";
</script>
