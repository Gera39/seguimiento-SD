<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Gestion academica</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">Docentes y revisores</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Esta vista ya esta conectada a la base de datos para dar de alta usuarios, asignar roles activos y delimitar el alcance de los revisores por carrera cuando haga falta.
            </p>
          </div>

          <button
            type="button"
            class="h-12 rounded-2xl bg-slate-900 px-5 text-sm font-semibold text-white"
            @click="showForm = !showForm"
          >
            {{ showForm ? "Ocultar formulario" : "Agregar docente" }}
          </button>
        </div>

        <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
          {{ status }}
        </p>
      </section>

      <section class="grid gap-4 md:grid-cols-3">
        <div v-for="metric in metrics" :key="metric.label" class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">{{ metric.label }}</p>
          <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
        </div>
      </section>

      <section v-if="showForm" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="max-w-5xl">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Nuevo usuario</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Alta y asignacion de roles</h2>
        </div>

        <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Numero de empleado</label>
            <input v-model="form.employee_number" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Opcional" />
            <p v-if="form.errors.employee_number" class="text-sm text-rose-600">{{ form.errors.employee_number }}</p>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Nombre completo</label>
            <input v-model="form.name" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Nombre completo" />
            <p v-if="form.errors.name" class="text-sm text-rose-600">{{ form.errors.name }}</p>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Correo institucional</label>
            <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="correo@dominio.com" />
            <p v-if="form.errors.email" class="text-sm text-rose-600">{{ form.errors.email }}</p>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Contrasena</label>
            <input v-model="form.password" type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Minimo 8 caracteres" />
            <p v-if="form.errors.password" class="text-sm text-rose-600">{{ form.errors.password }}</p>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Confirmar contrasena</label>
            <input v-model="form.password_confirmation" type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Repite la contrasena" />
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Roles</label>
            <div class="grid gap-3 rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <label
                v-for="role in roleOptions"
                :key="role.code"
                class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"
              >
                <input
                  :checked="form.roles.includes(role.code)"
                  type="checkbox"
                  class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900"
                  @change="toggleRole(role.code)"
                />
                <span>
                  <span class="block text-sm font-semibold text-slate-900">{{ role.name }}</span>
                  <span class="mt-1 block text-sm leading-5 text-slate-500">{{ role.description }}</span>
                </span>
              </label>
            </div>
            <p v-if="form.errors.roles" class="text-sm text-rose-600">{{ form.errors.roles }}</p>
            <p v-if="form.errors['roles.0']" class="text-sm text-rose-600">{{ form.errors['roles.0'] }}</p>
          </div>

          <div class="space-y-3">
            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
              <input v-model="form.must_change_password" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900" />
              Forzar cambio de contrasena al entrar
            </label>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
              <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900" />
              Usuario activo
            </label>
          </div>

          <div v-if="form.roles.includes('REVISOR')" class="space-y-2 md:col-span-2">
            <label class="text-sm font-medium text-slate-700">Alcance del revisor</label>
            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm leading-6 text-slate-500">
                Si no eliges ninguna carrera, el revisor podra atender todas las planeaciones. Si eliges una o varias, solo vera esas carreras.
              </p>

              <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <label
                  v-for="career in careerOptions"
                  :key="`create-career-${career.id}`"
                  class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"
                >
                  <input
                    :checked="form.reviewer_career_ids.includes(career.id)"
                    type="checkbox"
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900"
                    @change="toggleReviewerCareer(form, career.id)"
                  />
                  <span>
                    <span class="block text-sm font-semibold text-slate-900">{{ career.short_name || career.name }}</span>
                    <span v-if="career.short_name" class="mt-1 block text-sm leading-5 text-slate-500">{{ career.name }}</span>
                  </span>
                </label>
              </div>
            </div>
            <p v-if="form.errors.reviewer_career_ids" class="text-sm text-rose-600">{{ form.errors.reviewer_career_ids }}</p>
            <p v-if="form.errors['reviewer_career_ids.0']" class="text-sm text-rose-600">{{ form.errors['reviewer_career_ids.0'] }}</p>
          </div>

          <div class="md:col-span-2 flex flex-wrap gap-3">
            <button
              type="submit"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
              :disabled="form.processing"
            >
              {{ form.processing ? "Guardando..." : "Guardar usuario" }}
            </button>

            <button
              type="button"
              class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700"
              @click="resetForm"
            >
              Limpiar
            </button>
          </div>
        </form>
      </section>

      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="max-w-md">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Directorio</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Vista general del personal</h2>
          </div>
          <input
            v-model="search"
            type="text"
            class="h-12 w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700"
            placeholder="Buscar por nombre, correo, numero o rol"
          />
        </div>

        <div class="overflow-hidden rounded-[24px] border border-slate-200">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Roles</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Correo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Numero</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="person in filteredUsers" :key="person.id">
                <td class="px-4 py-4">
                  <p class="font-medium text-slate-900">{{ person.name }}</p>
                  <p class="mt-1 text-xs text-slate-500">Alta: {{ person.created_at ?? "Sin fecha" }}</p>
                </td>
                <td class="px-4 py-4 text-sm text-slate-600">
                  <p>{{ person.roles_label || "Sin rol activo" }}</p>
                  <p v-if="person.reviewer_scope_label" class="mt-1 text-xs text-slate-500">
                    Revisa: {{ person.reviewer_scope_label }}
                  </p>
                </td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ person.email }}</td>
                <td class="px-4 py-4 text-sm text-slate-600">{{ person.employee_number || "Sin numero" }}</td>
                <td class="px-4 py-4">
                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="person.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'"
                  >
                    {{ person.is_active ? "Activo" : "Inactivo" }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-wrap gap-2">
                    <button
                      type="button"
                      class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700"
                      @click="openRoleEditor(person)"
                    >
                      Editar roles
                    </button>
                    <button
                      type="button"
                      class="rounded-xl px-3 py-2 text-xs font-semibold"
                      :class="person.is_active ? 'border border-rose-200 bg-rose-50 text-rose-700' : 'border border-emerald-200 bg-emerald-50 text-emerald-700'"
                      :disabled="statusProcessingId === person.id"
                      @click="toggleUserStatus(person)"
                    >
                      {{ statusProcessingId === person.id ? "Guardando..." : person.is_active ? "Desactivar" : "Activar" }}
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!filteredUsers.length">
                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">
                  No hay usuarios que coincidan con la busqueda.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-if="editingUser" class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Editar roles</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ editingUser.name }}</h2>
            <p class="mt-2 text-sm text-slate-500">{{ editingUser.email }}</p>
          </div>

          <button
            type="button"
            class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700"
            @click="closeRoleEditor"
          >
            Cerrar
          </button>
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="submitRoleUpdate">
          <div class="grid gap-3 rounded-[24px] border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
            <label
              v-for="role in roleOptions"
              :key="`edit-${role.code}`"
              class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"
            >
              <input
                :checked="roleForm.roles.includes(role.code)"
                type="checkbox"
                class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900"
                @change="toggleEditRole(role.code)"
              />
              <span>
                <span class="block text-sm font-semibold text-slate-900">{{ role.name }}</span>
                <span class="mt-1 block text-sm leading-5 text-slate-500">{{ role.description }}</span>
              </span>
            </label>
          </div>

          <p v-if="roleForm.errors.roles" class="text-sm text-rose-600">{{ roleForm.errors.roles }}</p>
          <p v-if="roleForm.errors['roles.0']" class="text-sm text-rose-600">{{ roleForm.errors['roles.0'] }}</p>

          <div v-if="roleForm.roles.includes('REVISOR')" class="space-y-2">
            <label class="text-sm font-medium text-slate-700">Carreras asignadas al revisor</label>
            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm leading-6 text-slate-500">
                Sin carreras seleccionadas el alcance sera global. Si marcas opciones, la cola del revisor quedara filtrada a esas carreras.
              </p>

              <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <label
                  v-for="career in careerOptions"
                  :key="`edit-career-${career.id}`"
                  class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3"
                >
                  <input
                    :checked="roleForm.reviewer_career_ids.includes(career.id)"
                    type="checkbox"
                    class="mt-1 h-4 w-4 rounded border-slate-300 text-slate-900"
                    @change="toggleReviewerCareer(roleForm, career.id)"
                  />
                  <span>
                    <span class="block text-sm font-semibold text-slate-900">{{ career.short_name || career.name }}</span>
                    <span v-if="career.short_name" class="mt-1 block text-sm leading-5 text-slate-500">{{ career.name }}</span>
                  </span>
                </label>
              </div>
            </div>

            <p v-if="roleForm.errors.reviewer_career_ids" class="text-sm text-rose-600">{{ roleForm.errors.reviewer_career_ids }}</p>
            <p v-if="roleForm.errors['reviewer_career_ids.0']" class="text-sm text-rose-600">{{ roleForm.errors['reviewer_career_ids.0'] }}</p>
          </div>

          <div class="flex flex-wrap gap-3">
            <button
              type="submit"
              class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
              :disabled="roleForm.processing"
            >
              {{ roleForm.processing ? "Actualizando..." : "Guardar roles" }}
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

type RoleOption = {
  code: string;
  name: string;
  description: string | null;
};

type CareerOption = {
  id: number;
  name: string;
  short_name: string | null;
};

type ManagedUser = {
  id: number;
  employee_number: string | null;
  name: string;
  email: string;
  is_active: boolean;
  roles: Array<{ code: string; name: string }>;
  roles_label: string;
  reviewer_career_ids: number[];
  reviewer_scope_label: string | null;
  created_at: string | null;
  update_status_url: string;
  update_roles_url: string;
};

const props = defineProps<{
  metrics: Array<{ label: string; value: number }>;
  users: ManagedUser[];
  roleOptions: RoleOption[];
  careerOptions: CareerOption[];
  status?: string | null;
}>();

const tituloPagina = {
  text: "Docentes y revisores",
  links: ["Administracion", "Personal academico"],
};

const showForm = ref(false);
const search = ref("");
const editingUser = ref<ManagedUser | null>(null);
const statusProcessingId = ref<number | null>(null);

const form = useForm({
  employee_number: "",
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  roles: [] as string[],
  reviewer_career_ids: [] as number[],
  must_change_password: false,
  is_active: true,
});

const roleForm = useForm({
  roles: [] as string[],
  reviewer_career_ids: [] as number[],
});

const filteredUsers = computed(() => {
  const term = search.value.trim().toLowerCase();

  if (!term) {
    return props.users;
  }

  return props.users.filter((user) =>
    [user.name, user.email, user.employee_number ?? "", user.roles_label]
      .join(" ")
      .toLowerCase()
      .includes(term),
  );
});

const toggleRole = (roleCode: string) => {
  if (form.roles.includes(roleCode)) {
    form.roles = form.roles.filter((currentRole) => currentRole !== roleCode);

    if (roleCode === "REVISOR") {
      form.reviewer_career_ids = [];
    }

    return;
  }

  form.roles = [...form.roles, roleCode];
};

const toggleReviewerCareer = (
  targetForm: { reviewer_career_ids: number[] },
  careerId: number,
) => {
  if (targetForm.reviewer_career_ids.includes(careerId)) {
    targetForm.reviewer_career_ids = targetForm.reviewer_career_ids.filter((currentCareerId) => currentCareerId !== careerId);
    return;
  }

  targetForm.reviewer_career_ids = [...targetForm.reviewer_career_ids, careerId];
};

const resetForm = () => {
  form.reset();
  form.clearErrors();
  form.is_active = true;
  form.must_change_password = false;
  form.roles = [];
  form.reviewer_career_ids = [];
};

const openRoleEditor = (user: ManagedUser) => {
  editingUser.value = user;
  roleForm.clearErrors();
  roleForm.roles = user.roles.map((role) => role.code);
  roleForm.reviewer_career_ids = [...user.reviewer_career_ids];
};

const closeRoleEditor = () => {
  editingUser.value = null;
  roleForm.reset();
  roleForm.clearErrors();
  roleForm.roles = [];
  roleForm.reviewer_career_ids = [];
};

const toggleEditRole = (roleCode: string) => {
  if (roleForm.roles.includes(roleCode)) {
    roleForm.roles = roleForm.roles.filter((currentRole) => currentRole !== roleCode);

    if (roleCode === "REVISOR") {
      roleForm.reviewer_career_ids = [];
    }

    return;
  }

  roleForm.roles = [...roleForm.roles, roleCode];
};

const submit = () => {
  form.post("/docentes", {
    preserveScroll: true,
    onSuccess: () => {
      resetForm();
      showForm.value = false;
    },
    onError: () => {
      showForm.value = true;
    },
  });
};

const submitRoleUpdate = () => {
  if (!editingUser.value) {
    return;
  }

  roleForm.patch(editingUser.value.update_roles_url, {
    preserveScroll: true,
    onSuccess: () => {
      closeRoleEditor();
    },
  });
};

const toggleUserStatus = (user: ManagedUser) => {
  const nextState = !user.is_active;
  const confirmed = window.confirm(
    nextState
      ? `Se activara la cuenta de ${user.name}.`
      : `Se desactivara la cuenta de ${user.name} y ya no podra iniciar sesion.`,
  );

  if (!confirmed) {
    return;
  }

  statusProcessingId.value = user.id;

  router.patch(user.update_status_url, {
    is_active: nextState,
  }, {
    preserveScroll: true,
    onFinish: () => {
      statusProcessingId.value = null;
    },
  });
};
</script>
