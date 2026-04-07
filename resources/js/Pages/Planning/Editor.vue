<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-700">Planeacion didactica</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-900">
              {{ mode === "edit" ? "Editar borrador" : "Nueva planeacion" }}
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
              Captura la estructura completa de la planeacion con unidades, modulos, criterios y referencias.
            </p>
          </div>

          <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              <p class="font-semibold text-slate-900">Avance acumulado</p>
              <p class="mt-1">{{ totalProgress.toFixed(2) }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              <p class="font-semibold text-slate-900">Evaluacion acumulada</p>
              <p class="mt-1">{{ totalEvaluation.toFixed(2) }}%</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              <p class="font-semibold text-slate-900">Horas por unidad</p>
              <p class="mt-1">{{ totalUnitHours.toFixed(2) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              <p class="font-semibold text-slate-900">Horas por modulos</p>
              <p class="mt-1">{{ totalModuleHours.toFixed(2) }}</p>
            </div>
          </div>
        </div>
      </section>

      <form class="space-y-6" @submit.prevent="saveDraft">
        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Asignacion docente</span>
              <select v-model="form.teacher_subject_assignment_id" class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4">
                <option :value="null">Selecciona una asignacion</option>
                <option v-for="assignment in assignments" :key="assignment.id" :value="assignment.id">
                  {{ assignment.label }}
                </option>
              </select>
              <p v-if="form.errors.teacher_subject_assignment_id" class="text-sm text-rose-600">
                {{ form.errors.teacher_subject_assignment_id }}
              </p>
            </label>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
              <p class="font-semibold text-slate-900">Estado actual</p>
              <p class="mt-1">{{ plan?.status ?? "Borrador nuevo" }}</p>
            </div>
          </div>

          <div class="mt-4 grid gap-4">
            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Objetivo general</span>
              <textarea v-model="form.general_objective" rows="3" class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" />
              <p v-if="form.errors.general_objective" class="text-sm text-rose-600">{{ form.errors.general_objective }}</p>
            </label>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Intencion del curso</span>
              <textarea v-model="form.course_intent" rows="2" class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" />
            </label>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Notas metodologicas</span>
              <textarea v-model="form.methodology_notes" rows="2" class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" />
            </label>

            <label class="space-y-2">
              <span class="text-sm font-medium text-slate-700">Observaciones generales</span>
              <textarea v-model="form.general_observations" rows="2" class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" />
            </label>
          </div>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Unidades</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Estructura del curso</h2>
            </div>
            <button type="button" class="rounded-full bg-teal-600 px-4 py-2 text-sm font-semibold text-white" @click="addUnit">
              Agregar unidad
            </button>
          </div>

          <div class="mt-6 space-y-6">
            <article v-for="(unit, unitIndex) in form.units" :key="`unit-${unitIndex}`" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-900">Unidad {{ unit.unit_number || unitIndex + 1 }}</h3>
                <button type="button" class="text-sm font-semibold text-rose-600" @click="removeUnit(unitIndex)">Eliminar unidad</button>
              </div>

              <div class="mt-4 grid gap-4 md:grid-cols-3">
                <input v-model.number="unit.unit_number" type="number" min="1" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Numero de unidad" />
                <input v-model.number="unit.planned_hours" type="number" min="0" step="0.01" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Horas planeadas" />
                <input v-model.number="unit.progress_percentage" type="number" min="0" max="100" step="0.01" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="% avance" />
              </div>

              <div class="mt-4 grid gap-4">
                <input v-model="unit.title" type="text" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Titulo de la unidad" />
                <textarea v-model="unit.learning_objective" rows="2" class="rounded-[24px] border border-slate-200 bg-white px-4 py-3" placeholder="Objetivo de aprendizaje" />
              </div>

              <div class="mt-4 flex items-center justify-between gap-3">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Modulos</p>
                <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="addModule(unitIndex)">
                  Agregar modulo
                </button>
              </div>

              <div class="mt-4 space-y-4">
                <div v-for="(module, moduleIndex) in unit.modules" :key="`module-${unitIndex}-${moduleIndex}`" class="rounded-3xl border border-slate-200 bg-white p-4">
                  <div class="flex items-center justify-between gap-3">
                    <p class="font-semibold text-slate-900">Modulo {{ module.module_number || moduleIndex + 1 }}</p>
                    <button type="button" class="text-sm font-semibold text-rose-600" @click="removeModule(unitIndex, moduleIndex)">Eliminar modulo</button>
                  </div>

                  <div class="mt-4 grid gap-4 md:grid-cols-4">
                    <input v-model.number="module.module_number" type="number" min="1" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="No." />
                    <input v-model.number="module.theoretical_hours" type="number" min="0" step="0.01" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Horas teoricas" />
                    <input v-model.number="module.practical_hours" type="number" min="0" step="0.01" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Horas practicas" />
                    <select v-model="module.delivery_mode" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                      <option value="PRESENTIAL">Presencial</option>
                      <option value="VIRTUAL">Virtual</option>
                      <option value="HYBRID">Hibrido</option>
                    </select>
                  </div>

                  <div class="mt-4 grid gap-4">
                    <input v-model="module.title" type="text" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Titulo del modulo" />
                    <textarea v-model="module.topic_description" rows="2" class="rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3" placeholder="Descripcion o tema" />
                  </div>
                </div>
              </div>
            </article>
          </div>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Evaluacion</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Criterios</h2>
            </div>
            <button type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white" @click="addCriterion">
              Agregar criterio
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <div v-for="(criterion, criterionIndex) in form.evaluation_criteria" :key="`criterion-${criterionIndex}`" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <div class="flex items-center justify-between gap-3">
                <p class="font-semibold text-slate-900">Criterio {{ criterionIndex + 1 }}</p>
                <button type="button" class="text-sm font-semibold text-rose-600" @click="removeCriterion(criterionIndex)">Eliminar</button>
              </div>

              <div class="mt-4 grid gap-4 md:grid-cols-4">
                <select v-model.number="criterion.unit_number" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                  <option :value="null">Aplicacion general</option>
                  <option v-for="unit in form.units" :key="unit.unit_number" :value="unit.unit_number">
                    Unidad {{ unit.unit_number || "-" }}
                  </option>
                </select>
                <select v-model="criterion.criterion_type_code" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                  <option value="">Tipo</option>
                  <option v-for="type in criterionTypes" :key="type.code" :value="type.code">{{ type.name }}</option>
                </select>
                <input v-model.number="criterion.weight_percentage" type="number" min="0" max="100" step="0.01" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="% peso" />
                <input v-model.number="criterion.minimum_score" type="number" min="0" max="100" step="0.01" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Minimo" />
              </div>

              <div class="mt-4 grid gap-4">
                <input v-model="criterion.criterion_name" type="text" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Nombre del criterio" />
                <textarea v-model="criterion.description" rows="2" class="rounded-[24px] border border-slate-200 bg-white px-4 py-3" placeholder="Descripcion del criterio" />
                <textarea v-model="criterion.evidence_description" rows="2" class="rounded-[24px] border border-slate-200 bg-white px-4 py-3" placeholder="Evidencia esperada" />
                <textarea v-model="criterion.instrument_description" rows="2" class="rounded-[24px] border border-slate-200 bg-white px-4 py-3" placeholder="Instrumento de evaluacion" />
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Referencias</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Bibliografia y recursos</h2>
            </div>
            <button type="button" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="addReference">
              Agregar referencia
            </button>
          </div>

          <div class="mt-6 space-y-4">
            <div v-for="(reference, referenceIndex) in form.references" :key="`reference-${referenceIndex}`" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <div class="grid gap-4 md:grid-cols-[0.28fr_1fr_auto]">
                <select v-model="reference.reference_type" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                  <option value="BIBLIOGRAPHY">Bibliografia</option>
                  <option value="WEBGRAPHY">Webgrafia</option>
                  <option value="RESOURCE">Recurso</option>
                </select>
                <input v-model="reference.citation" type="text" class="rounded-2xl border border-slate-200 bg-white px-4 py-3" placeholder="Cita o recurso" />
                <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold text-rose-600" @click="removeReference(referenceIndex)">
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </section>

        <section class="flex flex-wrap items-center justify-between gap-3 rounded-[32px] border border-slate-200 bg-white p-6 shadow-sm">
          <Link :href="backUrl" class="rounded-full border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">
            Volver
          </Link>

          <div class="flex flex-wrap gap-3">
            <button
              v-if="deleteUrl"
              type="button"
              class="rounded-full border border-rose-200 px-5 py-3 text-sm font-semibold text-rose-700"
              @click="removePlan"
            >
              Eliminar
            </button>
            <button type="submit" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white" :disabled="form.processing">
              {{ form.processing ? "Guardando..." : "Guardar borrador" }}
            </button>
            <button
              v-if="submitUrl"
              type="button"
              class="rounded-full bg-teal-600 px-5 py-3 text-sm font-semibold text-white"
              :disabled="form.processing || totalsInvalid"
              @click="saveAndSubmit"
            >
              Guardar y enviar a revision
            </button>
          </div>
        </section>
      </form>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";

defineOptions({ layout: AppLayout });

type AssignmentOption = {
  id: number;
  label: string;
};

type CriterionTypeOption = {
  code: string;
  name: string;
};

type ModuleForm = {
  module_number: number;
  title: string;
  topic_description: string;
  theoretical_hours: number;
  practical_hours: number;
  learning_activity: string;
  teaching_activity: string;
  resources: string;
  assessment_activity: string;
  delivery_mode: string;
  scheduled_date: string | null;
};

type UnitForm = {
  unit_number: number;
  title: string;
  learning_objective: string;
  planned_hours: number;
  progress_percentage: number;
  start_week: number | null;
  end_week: number | null;
  teaching_strategy: string;
  learning_evidence: string;
  assessment_strategy: string;
  modules: ModuleForm[];
};

type CriterionForm = {
  unit_number: number | null;
  criterion_type_code: string;
  criterion_name: string;
  description: string;
  evidence_description: string;
  instrument_description: string;
  weight_percentage: number;
  minimum_score: number | null;
  sort_order: number;
};

type ReferenceForm = {
  reference_type: string;
  citation: string;
  sort_order: number;
};

type PlanForm = {
  teacher_subject_assignment_id: number | null;
  general_objective: string;
  course_intent: string;
  methodology_notes: string;
  general_observations: string;
  units: UnitForm[];
  evaluation_criteria: CriterionForm[];
  references: ReferenceForm[];
};

const props = defineProps<{
  mode: "create" | "edit";
  plan: (PlanForm & { id: number; folio: string; status?: string }) | null;
  assignments: AssignmentOption[];
  criterionTypes: CriterionTypeOption[];
  storeUrl?: string;
  updateUrl?: string;
  deleteUrl?: string | null;
  submitUrl?: string | null;
  backUrl: string;
}>();

const tituloPagina = {
  text: props.mode === "edit" ? "Editar planeacion" : "Nueva planeacion",
  links: ["Planeaciones", props.mode === "edit" ? "Edicion" : "Nueva"],
};

const makeModule = (): ModuleForm => ({
  module_number: 1,
  title: "",
  topic_description: "",
  theoretical_hours: 0,
  practical_hours: 0,
  learning_activity: "",
  teaching_activity: "",
  resources: "",
  assessment_activity: "",
  delivery_mode: "PRESENTIAL",
  scheduled_date: null,
});

const makeUnit = (unitNumber = 1): UnitForm => ({
  unit_number: unitNumber,
  title: "",
  learning_objective: "",
  planned_hours: 0,
  progress_percentage: 0,
  start_week: null,
  end_week: null,
  teaching_strategy: "",
  learning_evidence: "",
  assessment_strategy: "",
  modules: [makeModule()],
});

const makeCriterion = (sortOrder = 1): CriterionForm => ({
  unit_number: null,
  criterion_type_code: "",
  criterion_name: "",
  description: "",
  evidence_description: "",
  instrument_description: "",
  weight_percentage: 0,
  minimum_score: null,
  sort_order: sortOrder,
});

const makeReference = (sortOrder = 1): ReferenceForm => ({
  reference_type: "BIBLIOGRAPHY",
  citation: "",
  sort_order: sortOrder,
});

const initialData: PlanForm = props.plan
  ? JSON.parse(JSON.stringify(props.plan))
  : {
      teacher_subject_assignment_id: props.assignments[0]?.id ?? null,
      general_objective: "",
      course_intent: "",
      methodology_notes: "",
      general_observations: "",
      units: [makeUnit()],
      evaluation_criteria: [makeCriterion()],
      references: [makeReference()],
    };

const form = useForm<PlanForm>(initialData);

const totalProgress = computed(() =>
  form.units.reduce((sum, unit) => sum + Number(unit.progress_percentage || 0), 0),
);

const totalEvaluation = computed(() =>
  form.evaluation_criteria.reduce((sum, criterion) => sum + Number(criterion.weight_percentage || 0), 0),
);

const totalUnitHours = computed(() =>
  form.units.reduce((sum, unit) => sum + Number(unit.planned_hours || 0), 0),
);

const totalModuleHours = computed(() =>
  form.units.reduce(
    (sum, unit) =>
      sum +
      unit.modules.reduce(
        (moduleSum, module) => moduleSum + Number(module.theoretical_hours || 0) + Number(module.practical_hours || 0),
        0,
      ),
    0,
  ),
);

const totalsInvalid = computed(() =>
  totalProgress.value > 100 || totalEvaluation.value > 100 || totalUnitHours.value !== totalModuleHours.value,
);

const addUnit = () => {
  form.units.push(makeUnit(form.units.length + 1));
};

const removeUnit = (index: number) => {
  form.units.splice(index, 1);
  if (!form.units.length) addUnit();
};

const addModule = (unitIndex: number) => {
  form.units[unitIndex].modules.push(makeModule());
};

const removeModule = (unitIndex: number, moduleIndex: number) => {
  form.units[unitIndex].modules.splice(moduleIndex, 1);
  if (!form.units[unitIndex].modules.length) addModule(unitIndex);
};

const addCriterion = () => {
  form.evaluation_criteria.push(makeCriterion(form.evaluation_criteria.length + 1));
};

const removeCriterion = (index: number) => {
  form.evaluation_criteria.splice(index, 1);
  if (!form.evaluation_criteria.length) addCriterion();
};

const addReference = () => {
  form.references.push(makeReference(form.references.length + 1));
};

const removeReference = (index: number) => {
  form.references.splice(index, 1);
};

const saveDraft = () => {
  if (props.mode === "edit" && props.updateUrl) {
    form.put(props.updateUrl);
    return;
  }

  if (props.storeUrl) {
    form.post(props.storeUrl);
  }
};

const saveAndSubmit = () => {
  const afterSave = () => {
    if (props.submitUrl) {
      router.post(props.submitUrl);
    }
  };

  if (props.mode === "edit" && props.updateUrl) {
    form.put(props.updateUrl, { onSuccess: afterSave });
    return;
  }

  if (props.storeUrl) {
    form.post(props.storeUrl, { onSuccess: afterSave });
  }
};

const removePlan = () => {
  if (props.deleteUrl) {
    form.delete(props.deleteUrl);
  }
};
</script>
