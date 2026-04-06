<template>
  <Header :titulo="tituloPagina" />

  <div class="flex">
    <main class="flex-1 p-8">
      <h1 class="text-3xl font-bold mb-6">Asignación de Permisos</h1>

      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="font-semibold mb-4">Asignación de Permisos</h2>
        <p class="text-sm text-gray-500 mb-6">Selecciona el docente y el rol que deseas asignar.</p>

        <!-- Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div>
            <label class="font-medium">Buscar Docente :</label>
            <input
              v-model="filtroDocente"
              type="text"
              class="border w-full rounded-lg p-2 mt-1"
              placeholder="Buscar…"
            />
          </div>

          <div>
            <label class="font-medium">Filtrar por Rol:</label>
            <select v-model="rolSeleccionado" class="border w-full rounded-lg p-2 mt-1">
              <option value="">Todos</option>
              <option>Docente</option>
              <option>Revisor</option>
            </select>
          </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="border rounded-lg p-4 mb-6">
          <h3 class="font-semibold mb-3">Asignados al Rol:</h3>

          <div
            v-for="u in usuariosFiltrados"
            :key="u.nombre"
            class="flex items-center justify-between border-b py-2"
          >
            <span>
              {{ u.nombre }} —
              <span class="text-teal-700">{{ u.rol }}</span>
            </span>
            <Pencil class="text-blue-500 cursor-pointer" :size="18" @click="abrirModal(u)" />
          </div>

          <p v-if="usuariosFiltrados.length === 0" class="text-gray-500 text-sm">
            No se encontraron docentes.
          </p>
        </div>

        <!-- Permisos -->
        <div class="border rounded-lg p-4 mb-6">
          <h3 class="font-semibold mb-3">Permisos:</h3>

          <div
            v-for="p in permisos"
            :key="p.label"
            @click="p.activo = !p.activo"
            class="flex items-center gap-3 border-b py-2 cursor-pointer select-none"
          >
            <input type="checkbox" v-model="p.activo" @click.stop />
            <span>{{ p.label }}</span>
          </div>
        </div>

        <button
          @click="guardarCambios"
          class="bg-teal-700 text-white px-6 py-2 rounded-lg hover:bg-teal-800"
        >
          Guardar Cambios
        </button>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div v-if="modalAbierto" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[999]">
    <div class="bg-white w-96 p-6 rounded-xl shadow-lg animate-fadeIn">
      <h2 class="text-xl font-semibold mb-4">Editar Rol</h2>

      <p class="mb-3">Usuario: <strong>{{ usuarioActual?.nombre }}</strong></p>

      <label class="font-medium">Seleccionar Rol:</label>
      <select v-model="rolTemporal" class="border w-full rounded-lg p-2 mt-1 mb-6">
        <option value="">Seleccione</option>
        <option value="Docente">Docente</option>
        <option value="Revisor">Revisor</option>
      </select>

      <div class="flex justify-end gap-3">
        <button @click="cerrarModal" class="px-4 py-2 border rounded-lg hover:bg-gray-100">
          Cancelar
        </button>

        <button
          @click="guardarEdicion"
          class="px-4 py-2 bg-teal-700 text-white rounded-lg hover:bg-teal-800"
        >
          Guardar
        </button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <transition name="pop-fade">
    <div v-if="showToast" class="fixed inset-0 flex items-center justify-center z-[1000]">
      <div class="bg-white border shadow-lg rounded-lg px-8 py-6 flex flex-col items-center gap-4">
        <div v-if="guardando" class="flex items-center gap-2 text-gray-700">
          <svg
            class="animate-spin h-8 w-8 text-teal-700"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v8H4z"
            ></path>
          </svg>
          <span class="text-lg font-medium">Guardando...</span>
        </div>

        <div v-else class="flex flex-col items-center text-green-600 gap-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-12 w-12"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="3"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          <span class="font-semibold text-lg">¡Cambios guardados!</span>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import Header from "@/components/Header.vue";
import { Pencil } from "lucide-vue-next";
import { ref, computed } from "vue";

interface Usuario {
  nombre: string;
  rol: "Docente" | "Revisor";
}

const tituloPagina = { text: "Asignación de Permisos", links: [] };

const usuarios = ref<Usuario[]>([
  { nombre: "Carlos Ramírez", rol: "Docente" },
  { nombre: "María Fernández", rol: "Revisor" },
  { nombre: "Julio Pérez", rol: "Docente" },
  { nombre: "Ana Torres", rol: "Revisor" },
  { nombre: "José León", rol: "Docente" }
]);

const filtroDocente = ref("");
const rolSeleccionado = ref("");

const permisos = ref([
  { label: "Validar Secuencias Didácticas", activo: false },
  { label: "Editar Secuencias Didácticas", activo: false },
  { label: "Ver Secuencias Didácticas", activo: false },
  { label: "Crear Secuencias Didácticas", activo: false }
]);

const modalAbierto = ref(false);
const usuarioActual = ref<Usuario | null>(null);
const rolTemporal = ref<"Docente" | "Revisor" | "">("");

const showToast = ref(false);
const guardando = ref(false);

const usuariosFiltrados = computed(() =>
  usuarios.value.filter(u => {
    const matchNombre = u.nombre.toLowerCase().includes(filtroDocente.value.toLowerCase());
    const matchRol = rolSeleccionado.value ? u.rol === rolSeleccionado.value : true;
    return matchNombre && matchRol;
  })
);

const abrirModal = (u: Usuario) => {
  usuarioActual.value = u;
  rolTemporal.value = u.rol;
  modalAbierto.value = true;
};

const cerrarModal = () => {
  modalAbierto.value = false;
  usuarioActual.value = null;
  rolTemporal.value = "";
};

const guardarEdicion = () => {
  if (!usuarioActual.value || !rolTemporal.value) return;
  usuarioActual.value.rol = rolTemporal.value;
  cerrarModal();
  mostrarToast();
};

const guardarCambios = () => {
  permisos.value.forEach(p => (p.activo = false));
  mostrarToast();
};

const mostrarToast = () => {
  showToast.value = true;
  guardando.value = true;

  setTimeout(() => {
    guardando.value = false;
    setTimeout(() => (showToast.value = false), 1200);
  }, 1200);
};
</script>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.93); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn { animation: fadeIn 0.18s ease-out; }

.pop-fade-enter-active { animation: popIn 0.5s ease forwards; }
.pop-fade-leave-active { animation: popOut 0.5s ease forwards; }

@keyframes popIn {
  0% { transform: scale(0.5) translateY(20px); opacity: 0; }
  50% { transform: scale(1.1) translateY(-10px); opacity: 1; }
  100% { transform: scale(1) translateY(0); opacity: 1; }
}

@keyframes popOut {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(0.5) translateY(-20px); opacity: 0; }
}
</style>
