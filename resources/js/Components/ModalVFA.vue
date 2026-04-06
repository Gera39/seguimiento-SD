<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4"
      >
        <section class="w-full max-w-md rounded-[28px] border border-slate-200 bg-white p-8 shadow-2xl">
          <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
              <path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round" />
              <circle cx="12" cy="12" r="9" />
            </svg>
          </div>

          <div class="mt-5 text-center">
            <h2 class="text-2xl font-semibold text-slate-900">{{ title }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
              {{ subtitle }} <span class="font-medium text-slate-900">{{ email }}</span>
            </p>
          </div>

          <div class="mt-6 grid grid-cols-6 gap-3">
            <div
              v-for="item in 6"
              :key="item"
              class="flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-lg font-semibold text-slate-400"
            >
              •
            </div>
          </div>

          <div
            v-if="error"
            class="mt-5 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
          >
            {{ error }}
          </div>

          <button
            type="button"
            class="mt-6 h-12 w-full rounded-2xl bg-emerald-600 font-semibold text-white transition hover:bg-emerald-700"
          >
            Confirmar codigo
          </button>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    open: boolean;
    email: string;
    error?: string | null;
    title?: string;
    subtitle?: string;
  }>(),
  {
    error: null,
    title: "Verificacion visual",
    subtitle: "Ingresa el codigo enviado a",
  },
);
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 180ms ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
