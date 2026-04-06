<template>
  <div class="min-h-screen flex items-center justify-center bg-linear-to-br from-[#F3F4F6] to-[#E5E7EB] p-4">
    <div class="w-full max-w-md">
      <div class="flex justify-center mb-6">
        <div class="w-25 h-25 rounded-2xl flex items-center justify-center shadow-lg">
          
        </div>
      </div>
      <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-8">
        <div class="text-center space-y-2">
          <h1 class="text-3xl font-bold text-[#111827]">Iniciar Sesión</h1>
          <p class="text-sm text-gray-500">Accede a tu cuenta para continuar</p>
        </div>
        <LogingForm />

        <!-- =========================SE COMENTA POR SI SE OCUPAN PRONOT CON GOOGLE O GITHUB ======================= -->
        <!-- <div className="flex items-center space-x-3">
            <div className="flex-1 h-px bg-gray-200"></div>
            <span className="text-sm text-gray-400">O</span>
            <div className="flex-1 h-px bg-gray-200"></div>
          </div> -->

        <!-- <div className="grid grid-cols-2 gap-3">
            <button className="flex items-center justify-center space-x-2 px-4 py-2 rounded-lg border-2 border-gray-200 hover:border-[#14B8A6] text-gray-700 hover:text-[#14B8A6] transition-colors font-medium">
              <span>Google</span>
            </button>
            <button className="flex items-center justify-center space-x-2 px-4 py-2 rounded-lg border-2 border-gray-200 hover:border-[#14B8A6] text-gray-700 hover:text-[#14B8A6] transition-colors font-medium">
              <span>GitHub</span>
            </button>
          </div> -->
        <!-- =========================================================================================== -->

      </div>

      <p class="text-center text-xs text-gray-500 mt-6">
        © 2025 . Todos los derechos reservados.
      </p>
    </div>
  </div>

</template>
<script setup lang="ts">
import LogingForm from '@/components/LoginForm.vue';
import { useAuthStore } from '@/stores/auth';
import { onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';

const auth = useAuthStore();
const router = useRouter();
const { isAuthenticated } = storeToRefs(auth);

onMounted(async () => {
  await auth.hydrate();
  if (isAuthenticated.value) {
    router.replace('/dashboard');
  }
});

watch(
  isAuthenticated,
  (isAuthed) => {
    if (isAuthed) {
      router.replace('/dashboard');
    }
  }
);
</script>
<style scoped></style>