<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.12),_transparent_30%),linear-gradient(160deg,_#f8fafc_0%,_#ecfeff_50%,_#f0fdf4_100%)]">
    <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-8 px-4 py-10 lg:grid-cols-[1.02fr_0.98fr]">
      <section class="rounded-[34px] border border-white/70 bg-slate-950 p-8 text-white shadow-[0_35px_110px_-45px_rgba(15,23,42,0.9)] sm:p-10">
        <div class="space-y-6">
          <p class="inline-flex rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm text-emerald-100">
            Recuperacion de acceso
          </p>
          <h1 class="max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">
            Una vista elegante para el flujo de restablecimiento.
          </h1>
          <p class="max-w-2xl text-base leading-7 text-slate-300 sm:text-lg">
            Esta pantalla se quedo solo como demo visual. Mantiene la estructura del proceso sin llamadas ni validaciones complejas.
          </p>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2">
          <div class="rounded-3xl border border-white/10 bg-white/[0.08] p-5">
            <p class="text-sm text-slate-300">Paso 1</p>
            <p class="mt-2 text-lg font-semibold">Confirmar correo</p>
          </div>
          <div class="rounded-3xl border border-white/10 bg-white/[0.08] p-5">
            <p class="text-sm text-slate-300">Paso 2</p>
            <p class="mt-2 text-lg font-semibold">Crear nueva contrasena</p>
          </div>
        </div>
      </section>

      <section class="rounded-[34px] border border-white/80 bg-white/92 p-6 shadow-[0_30px_90px_-35px_rgba(16,185,129,0.35)] backdrop-blur sm:p-8">
        <div class="space-y-6">
          <a href="/login" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900">
            Volver al login
          </a>

          <div class="space-y-3">
            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-700">
              Restablecer acceso
            </p>
            <h2 class="text-3xl font-semibold tracking-tight text-slate-900">
              Define una nueva contrasena
            </h2>
            <p class="text-sm leading-6 text-slate-500">
              La pantalla ya quedo limpia para conectarla despues con el endpoint real de Laravel.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-700">Token demo</p>
              <p class="mt-2 text-sm font-semibold text-slate-800">RST-UTH-2026</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-sky-700">Seguridad</p>
              <p class="mt-2 text-sm font-semibold text-slate-800">Nivel alto</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
              <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-700">Estado</p>
              <p class="mt-2 text-sm font-semibold text-slate-800">Solo demo</p>
            </div>
          </div>

          <form class="space-y-5 rounded-[28px] border border-slate-200 bg-slate-50/90 p-5">
            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Nueva contrasena</label>
              <input
                type="password"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                placeholder="Escribe una contrasena segura"
              />
            </div>

            <div class="space-y-2">
              <label class="text-sm font-medium text-slate-700">Confirmar contrasena</label>
              <input
                type="password"
                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                placeholder="Repite tu nueva contrasena"
              />
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-4">
              <p class="text-sm font-semibold text-slate-800">Reglas visibles</p>
              <div class="mt-4 grid gap-3">
                <div v-for="rule in rules" :key="rule" class="rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600">
                  {{ rule }}
                </div>
              </div>
            </div>

            <button type="button" class="h-12 w-full rounded-2xl bg-emerald-600 font-semibold text-white transition hover:bg-emerald-700">
              Guardar nueva contrasena
            </button>
          </form>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
const rules = [
  "Minimo 8 caracteres",
  "Al menos una mayuscula",
  "Al menos un numero",
  "Confirmacion identica",
];
</script>
