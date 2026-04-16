<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Administracion</p>
        <h1 class="mt-3 text-3xl font-semibold text-slate-900">Carga masiva de usuarios</h1>
        <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
          El flujo esta ordenado para evitar errores: primero descarga la plantilla, luego revisa reglas, valida tu archivo y solo entonces ejecuta la importacion.
        </p>

        <p v-if="status" class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
          {{ status }}
        </p>
      </section>

      <section class="grid gap-6 xl:grid-cols-[0.96fr_1.04fr]">
        <article class="space-y-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="rounded-[28px] border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Paso 1</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Descargar plantilla</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
              Usa siempre la plantilla actual. Asi te aseguras de que columnas, orden y formato coincidan con la validacion del sistema.
            </p>
            <a
              :href="templateUrl"
              class="mt-5 inline-flex rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
            >
              Descargar plantilla Excel
            </a>
          </div>

          <div class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Paso 2</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Reglas y validaciones</h2>
            <div class="mt-4 space-y-3">
              <div
                v-for="rule in rules"
                :key="rule"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-600"
              >
                {{ rule }}
              </div>
            </div>
          </div>

          <div class="rounded-[28px] border border-sky-200 bg-sky-50 p-5">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Paso 3</p>
            <h2 class="mt-2 text-2xl font-semibold text-slate-900">Validar archivo</h2>
            <p class="mt-3 text-sm leading-6 text-slate-600">
              La validacion analiza cada fila y te dice si se creara o actualizara, junto con cualquier error que debas corregir antes de importar.
            </p>

            <form class="mt-5 space-y-4" @submit.prevent="validateFile">
              <label class="block space-y-2">
                <span class="text-sm font-medium text-slate-700">Archivo Excel (.xlsx)</span>
                <input
                  type="file"
                  accept=".xlsx"
                  class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700"
                  @change="onFileSelected"
                />
                <p v-if="validationForm.errors.file" class="text-sm text-rose-600">{{ validationForm.errors.file }}</p>
              </label>

              <button
                type="submit"
                class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
                :disabled="validationForm.processing"
              >
                {{ validationForm.processing ? "Validando..." : "Validar archivo" }}
              </button>
            </form>
          </div>
        </article>

        <article class="space-y-6 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Paso 4</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Resultado del analisis</h2>
              <p class="mt-2 text-sm leading-6 text-slate-500">
                Solo se habilita la importacion cuando todas las filas pasan validacion.
              </p>
            </div>

            <button
              type="button"
              class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white"
              :disabled="!preview?.can_import || importForm.processing"
              @click="runImport"
            >
              {{ importForm.processing ? "Importando..." : "Ejecutar importacion" }}
            </button>
          </div>

          <template v-if="preview">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <div v-for="metric in summaryMetrics" :key="metric.label" class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm text-slate-500">{{ metric.label }}</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ metric.value }}</p>
              </div>
            </div>

            <div class="rounded-2xl border px-4 py-3 text-sm"
              :class="preview.can_import ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800'">
              <span v-if="preview.can_import">
                El archivo {{ preview.file_name }} paso validacion completa. Ya puedes importarlo.
              </span>
              <span v-else>
                El archivo {{ preview.file_name }} tiene observaciones. Corrige las filas marcadas y vuelve a validar.
              </span>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-slate-200">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fila</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Accion</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Roles</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Resultado</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                  <tr v-for="row in preview.rows" :key="row.row_number">
                    <td class="px-4 py-4 text-sm text-slate-600">{{ row.row_number }}</td>
                    <td class="px-4 py-4">
                      <span class="rounded-full px-3 py-1 text-xs font-semibold"
                        :class="row.action === 'CREAR' ? 'bg-sky-100 text-sky-700' : 'bg-violet-100 text-violet-700'">
                        {{ row.action }}
                      </span>
                    </td>
                    <td class="px-4 py-4">
                      <p class="font-medium text-slate-900">{{ row.name || "Sin nombre" }}</p>
                      <p class="mt-1 text-xs text-slate-500">{{ row.email || "Sin correo" }}</p>
                      <p v-if="row.employee_number" class="mt-1 text-xs text-slate-500">No. empleado: {{ row.employee_number }}</p>
                    </td>
                    <td class="px-4 py-4 text-sm text-slate-600">
                      <p>{{ row.roles_label || "Sin roles" }}</p>
                      <p class="mt-1 text-xs text-slate-500">{{ row.reviewer_scope_label }}</p>
                    </td>
                    <td class="px-4 py-4 text-sm text-slate-600">
                      <p>{{ row.is_active ? "Activo" : "Inactivo" }}</p>
                      <p class="mt-1 text-xs text-slate-500">
                        {{ row.must_change_password ? "Forzar cambio de contrasena" : "Sin forzar cambio" }}
                      </p>
                    </td>
                    <td class="px-4 py-4">
                      <div v-if="row.errors.length" class="space-y-2">
                        <p v-for="error in row.errors" :key="error" class="rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                          {{ error }}
                        </p>
                      </div>
                      <p v-else class="rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                        Lista para importar
                      </p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>

          <div v-else class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-500">
            Aun no has validado ningun archivo. Descarga la plantilla, llena tus filas y sube el Excel para revisar reglas y observaciones.
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

type PreviewRow = {
  row_number: number;
  action: string;
  employee_number: string | null;
  name: string;
  email: string;
  roles_label: string;
  reviewer_scope_label: string;
  must_change_password: boolean;
  is_active: boolean;
  errors: string[];
};

type Preview = {
  file_name: string;
  summary: {
    detected: number;
    valid: number;
    invalid: number;
    to_create: number;
    to_update: number;
  };
  can_import: boolean;
  rows: PreviewRow[];
};

const props = defineProps<{
  status?: string | null;
  templateUrl: string;
  validateUrl: string;
  importUrl: string;
  rules: string[];
  preview?: Preview | null;
}>();

const tituloPagina = {
  text: "Importar usuarios",
  links: ["Administracion", "Carga masiva"],
};

const validationForm = useForm({
  file: null as File | null,
});

const importForm = useForm({});

const summaryMetrics = computed(() => {
  if (!props.preview) {
    return [];
  }

  return [
    { label: "Filas detectadas", value: props.preview.summary.detected },
    { label: "Validas", value: props.preview.summary.valid },
    { label: "Por crear", value: props.preview.summary.to_create },
    { label: "Por actualizar", value: props.preview.summary.to_update },
  ];
});

const onFileSelected = (event: Event) => {
  const input = event.target as HTMLInputElement;
  validationForm.file = input.files?.[0] ?? null;
};

const validateFile = () => {
  validationForm.post(props.validateUrl, {
    forceFormData: true,
    preserveScroll: true,
  });
};

const runImport = () => {
  importForm.post(props.importUrl, {
    preserveScroll: true,
  });
};
</script>
