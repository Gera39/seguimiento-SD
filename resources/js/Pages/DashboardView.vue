<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="space-y-6 p-4 md:p-6">
      <!-- <section class="grid gap-6 xl:grid-cols-[1.12fr_0.88fr]">

        <article class="rounded-[36px] border border-emerald-100 bg-[linear-gradient(180deg,_#ffffff_0%,_#f0fdfa_100%)] p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Como se usa</p>
          <div class="mt-6 space-y-4">
            <div v-for="step in steps" :key="step.title" class="flex gap-4 rounded-3xl border border-emerald-100 bg-white/80 p-4">
              <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">
                {{ step.number }}
              </div>
              <div>
                <p class="font-semibold text-slate-900">{{ step.title }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ step.description }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 rounded-3xl border border-dashed border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900">
            Este prototipo sirve para definir la experiencia correcta antes de conectar base de datos, validaciones reales y reportes.
          </div>
        </article>
      </section> -->

      <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Plantillas rapidas</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Comienza con un formato sugerido</h2>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
              {{ templates.length }} opciones
            </span>
          </div>

          <div class="mt-6 grid gap-4">
            <button
              v-for="template in templates"
              :key="template.title"
              type="button"
              class="group rounded-[28px] border border-slate-200 bg-slate-50 p-5 text-left transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-white hover:shadow-md"
              @click="applyTemplate(template)"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <p class="text-base font-semibold text-slate-900">{{ template.title }}</p>
                  <p class="mt-2 text-sm leading-6 text-slate-500">{{ template.description }}</p>
                </div>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                  Usar
                </span>
              </div>
              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="tag in template.tags"
                  :key="tag"
                  class="rounded-full bg-slate-900 px-3 py-1 text-xs font-medium text-white/90"
                >
                  {{ tag }}
                </span>
              </div>
            </button>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Formulario rapido</p>
              <h2 class="mt-2 text-2xl font-semibold text-slate-900">Captura guiada en una sola vista</h2>
            </div>

            <div class="flex gap-2">
              <Button type="button" variant="outline" class="rounded-full" @click="resetForm">
                Limpiar
              </Button>
              <Button type="button" class="rounded-full bg-slate-900 text-white hover:bg-slate-800" @click="simulateSave">
                Guardar demo
              </Button>
            </div>
          </div>

          <div v-if="saveMessage" class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ saveMessage }}
          </div>

          <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.92fr]">
            <form class="space-y-5" @submit.prevent="simulateSave">
              <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Docente</span>
                  <Input v-model="form.docente" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Maria Hernandez" />
                </label>

                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Asignatura</span>
                  <Input v-model="form.asignatura" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Matematicas I" />
                </label>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Secuencia</span>
                  <Input v-model="form.secuencia" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Secuencia 03" />
                </label>

                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Semana o bloque</span>
                  <Input v-model="form.periodo" class="h-11 rounded-2xl border-slate-200 bg-slate-50" placeholder="Ej. Semana 2" />
                </label>
              </div>

              <div class="grid gap-4 md:grid-cols-2">
                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Tipo de registro</span>
                  <select v-model="form.tipo" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
                    <option value="">Selecciona una opcion</option>
                    <option v-for="option in typeOptions" :key="option" :value="option">{{ option }}</option>
                  </select>
                </label>

                <label class="space-y-2">
                  <span class="text-sm font-medium text-slate-700">Estado</span>
                  <select v-model="form.estado" class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
                    <option value="">Selecciona una opcion</option>
                    <option v-for="option in statusOptions" :key="option" :value="option">{{ option }}</option>
                  </select>
                </label>
              </div>

              <label class="space-y-2">
                <span class="text-sm font-medium text-slate-700">Observaciones breves</span>
                <textarea
                  v-model="form.observaciones"
                  rows="4"
                  class="w-full rounded-[24px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                  placeholder="Describe rapido que se entrego, que falta o que observacion importante debe quedar visible."
                />
              </label>

              <div class="rounded-[28px] border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">Prioridad visual</p>
                <div class="mt-3 flex flex-wrap gap-2">
                  <button
                    v-for="priority in priorityOptions"
                    :key="priority"
                    type="button"
                    class="rounded-full px-4 py-2 text-sm font-medium transition"
                    :class="form.prioridad === priority ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'"
                    @click="form.prioridad = priority"
                  >
                    {{ priority }}
                  </button>
                </div>
              </div>
            </form>

            <div class="space-y-4">
              <div class="rounded-[28px] border border-slate-200 bg-[linear-gradient(180deg,_#ffffff_0%,_#f8fafc_100%)] p-5">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Resumen en vivo</p>
                <div class="mt-5 space-y-4">
                  <div class="rounded-3xl bg-slate-900 p-5 text-white">
                    <p class="text-xs uppercase tracking-[0.2em] text-teal-200">Registro actual</p>
                    <p class="mt-2 text-2xl font-semibold">{{ summaryTitle }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-300">{{ summaryDescription }}</p>
                  </div>

                  <div class="grid gap-3 sm:grid-cols-2">
                    <div v-for="item in summaryItems" :key="item.label" class="rounded-3xl border border-slate-200 bg-white p-4">
                      <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ item.label }}</p>
                      <p class="mt-2 text-sm font-semibold text-slate-900">{{ item.value }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-[28px] border border-amber-100 bg-amber-50 p-5">
                <p class="text-sm font-semibold text-amber-900">Por que esta propuesta ayuda</p>
                <ul class="mt-3 space-y-2 text-sm leading-6 text-amber-900/90">
                  <li>Reduce campos innecesarios y prioriza lo que se captura todos los dias.</li>
                  <li>Hace visible el estado sin abrir otra pantalla.</li>
                  <li>Permite convertir plantillas en registros rapidos con pocos clics.</li>
                </ul>
              </div>

              <div class="rounded-[28px] border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-sm font-semibold text-emerald-900">Siguiente evolucion sugerida</p>
                <p class="mt-2 text-sm leading-6 text-emerald-900/90">
                  Conectar este formulario a Laravel para guardar por docente, filtrar por estado y generar reportes por periodo.
                </p>
              </div>
            </div>
          </div>
        </article>
      </section>

      <section class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Alcance recomendado</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-900">Que debe tener un sistema serio de secuencias didacticas</h2>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            Primero todo en una vista, despues por modulos y permisos.
          </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
          <article v-for="module in coreModules" :key="module.title" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-semibold text-slate-900">{{ module.title }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ module.description }}</p>
            <div class="mt-4 flex flex-wrap gap-2">
              <span
                v-for="item in module.items"
                :key="item"
                class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200"
              >
                {{ item }}
              </span>
            </div>
          </article>
        </div>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Vista unificada de roles</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Maestro y revisor dentro del mismo sistema</h2>

          <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div v-for="role in roleViews" :key="role.title" class="rounded-[28px] border p-5" :class="role.className">
              <p class="text-sm font-semibold uppercase tracking-[0.18em]">{{ role.title }}</p>
              <p class="mt-3 text-sm leading-6">{{ role.description }}</p>
              <div class="mt-4 space-y-2 text-sm">
                <div v-for="task in role.tasks" :key="task" class="rounded-2xl bg-white/80 px-3 py-2">
                  {{ task }}
                </div>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Flujo recomendado</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Estados que conviene manejar desde el inicio</h2>

          <div class="mt-6 space-y-3">
            <div v-for="stage in workflowStages" :key="stage.title" class="flex gap-4 rounded-[26px] border border-slate-200 bg-slate-50 p-4">
              <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-sm font-semibold text-white">
                {{ stage.step }}
              </div>
              <div>
                <p class="font-semibold text-slate-900">{{ stage.title }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">{{ stage.description }}</p>
              </div>
            </div>
          </div>
        </article>
      </section>

      <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Datos clave</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Campos que no deberian faltar</h2>

          <div class="mt-6 grid gap-3 md:grid-cols-2">
            <div v-for="field in requiredFields" :key="field.title" class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
              <p class="font-semibold text-slate-900">{{ field.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ field.description }}</p>
            </div>
          </div>
        </article>

        <article class="rounded-[36px] border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Tablero directivo</p>
          <h2 class="mt-2 text-2xl font-semibold text-slate-900">Indicadores utiles para coordinacion</h2>

          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div v-for="kpi in managementKpis" :key="kpi.title" class="rounded-[28px] border border-slate-200 bg-slate-50 p-5">
              <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ kpi.title }}</p>
              <p class="mt-3 text-2xl font-semibold text-slate-900">{{ kpi.value }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-600">{{ kpi.description }}</p>
            </div>
          </div>
        </article>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Header from "@/components/Header.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";

defineOptions({ layout: AppLayout });

type TemplateConfig = {
  title: string;
  description: string;
  tags: string[];
  values: {
    docente: string;
    asignatura: string;
    secuencia: string;
    periodo: string;
    tipo: string;
    estado: string;
    observaciones: string;
    prioridad: string;
  };
};

const tituloPagina = {
  text: "Captura rapida",
  links: ["Inicio", "Formulario principal"],
};

const metrics = [
  { label: "Menos pasos", value: "01", caption: "una sola pantalla" },
  { label: "Plantillas utiles", value: "03", caption: "listas para usar" },
  { label: "Lectura inmediata", value: "100%", caption: "resumen en vivo" },
];

const steps = [
  { number: "1", title: "Elige una plantilla", description: "Puedes iniciar vacio o tomar un formato sugerido segun el tipo de seguimiento." },
  { number: "2", title: "Llena solo lo esencial", description: "Docente, asignatura, secuencia, estado y una observacion breve. Sin pantallas extras." },
  { number: "3", title: "Confirma el resumen", description: "Antes de guardar, el sistema te muestra una ficha clara de lo que acabas de capturar." },
];

const coreModules = [
  {
    title: "Registro de secuencias",
    description: "Alta, edicion, versionado y reutilizacion de plantillas para que el docente no rehaga todo desde cero.",
    items: ["Plantillas", "Duplicar", "Versiones", "Adjuntos"],
  },
  {
    title: "Revision academica",
    description: "Bandeja del revisor con rubricas, comentarios, solicitud de ajustes y trazabilidad de decisiones.",
    items: ["Rubrica", "Observaciones", "Historial", "Firma de revision"],
  },
  {
    title: "Seguimiento operativo",
    description: "Estados, fechas compromiso, responsables y alertas para que nadie pierda una entrega o una correccion.",
    items: ["Estados", "Semaforos", "Vencimientos", "Recordatorios"],
  },
  {
    title: "Consulta y reportes",
    description: "Filtros por docente, carrera, materia, periodo y porcentaje de cumplimiento para coordinacion.",
    items: ["Filtros", "Exportacion", "Indicadores", "Auditoria"],
  },
];

const roleViews = [
  {
    title: "Maestro",
    description: "Debe poder crear una secuencia con apoyo visual, guardar borradores, enviar a revision y responder observaciones.",
    className: "border-emerald-100 bg-emerald-50 text-emerald-900",
    tasks: ["Crear desde plantilla", "Guardar borrador", "Enviar a revision", "Atender correcciones"],
  },
  {
    title: "Revisor",
    description: "Debe recibir una cola clara de trabajo, revisar por criterios, devolver comentarios y aprobar o rechazar con evidencia.",
    className: "border-sky-100 bg-sky-50 text-sky-900",
    tasks: ["Tomar revision", "Evaluar por criterios", "Solicitar ajustes", "Cerrar dictamen"],
  },
];

const workflowStages = [
  { step: "1", title: "Borrador", description: "El docente inicia o reutiliza una plantilla y captura los datos base de la secuencia." },
  { step: "2", title: "Enviado a revision", description: "La secuencia queda congelada para revision y el sistema registra fecha y responsable." },
  { step: "3", title: "En revision", description: "El revisor analiza por bloques, deja observaciones y marca cumplimiento por criterio." },
  { step: "4", title: "Requiere ajustes", description: "Vuelve al docente con comentarios puntuales y fecha limite de correccion." },
  { step: "5", title: "Aprobada o cerrada", description: "Se conserva evidencia del dictamen, historial de cambios y fecha de cierre." },
];

const requiredFields = [
  { title: "Identificacion academica", description: "Periodo, carrera, asignatura, grupo, docente y clave interna de la secuencia." },
  { title: "Planeacion didactica", description: "Competencias, objetivos, aprendizaje esperado, tema y estrategia metodologica." },
  { title: "Desarrollo de actividades", description: "Inicio, desarrollo, cierre, tiempos, recursos y evidencias de aprendizaje." },
  { title: "Evaluacion", description: "Criterios, instrumentos, ponderacion, retroalimentacion y evidencias adjuntas." },
  { title: "Control del proceso", description: "Estado, version, fechas, revisor asignado, observaciones y bitacora de cambios." },
  { title: "Soporte documental", description: "Archivos, enlaces, formatos institucionales y aprobaciones relevantes." },
];

const managementKpis = [
  { title: "Secuencias en borrador", value: "24", description: "Permite ver carga pendiente del lado docente." },
  { title: "Pendientes de revision", value: "11", description: "Mide la cola real que tiene coordinacion o revision academica." },
  { title: "Con ajustes solicitados", value: "07", description: "Sirve para detectar donde se atora mas el proceso." },
  { title: "Cumplimiento mensual", value: "82%", description: "Ayuda a seguimiento por periodo, carrera o academia." },
];

const templates: TemplateConfig[] = [
  {
    title: "Seguimiento semanal",
    description: "Ideal para registrar avance ordinario por semana o bloque con observaciones cortas.",
    tags: ["Semanal", "Rapido", "Uso diario"],
    values: {
      docente: "Maria Hernandez",
      asignatura: "Matematicas I",
      secuencia: "Secuencia 03",
      periodo: "Semana 2",
      tipo: "Seguimiento semanal",
      estado: "En revision",
      observaciones: "Se capturo la secuencia y queda pendiente validar evidencias del bloque.",
      prioridad: "Media",
    },
  },
  {
    title: "Entrega para revision",
    description: "Pensado para cuando el docente ya envio material y debe pasar al flujo de validacion.",
    tags: ["Entrega", "Revision", "Coordinacion"],
    values: {
      docente: "Carlos Lopez",
      asignatura: "Programacion",
      secuencia: "Secuencia 05",
      periodo: "Bloque A",
      tipo: "Entrega de docente",
      estado: "Pendiente",
      observaciones: "La entrega llego completa, falta asignar revisor para su validacion.",
      prioridad: "Alta",
    },
  },
  {
    title: "Correccion o ajuste",
    description: "Sirve para dejar evidencia breve de cambios solicitados antes del cierre final.",
    tags: ["Correccion", "Ajustes", "Cierre"],
    values: {
      docente: "Ana Ruiz",
      asignatura: "Comunicacion",
      secuencia: "Secuencia 02",
      periodo: "Semana 4",
      tipo: "Correccion",
      estado: "Requiere ajustes",
      observaciones: "Se detecto que faltan referencias y se solicito actualizar el apartado final.",
      prioridad: "Alta",
    },
  },
];

const typeOptions = ["Seguimiento semanal", "Entrega de docente", "Correccion", "Validacion final"];
const statusOptions = ["Pendiente", "En revision", "Requiere ajustes", "Completa"];
const priorityOptions = ["Baja", "Media", "Alta"];

const defaultForm = () => ({
  docente: "",
  asignatura: "",
  secuencia: "",
  periodo: "",
  tipo: "",
  estado: "",
  observaciones: "",
  prioridad: "Media",
});

const form = reactive(defaultForm());
const saveMessage = ref("");

const applyTemplate = (template: TemplateConfig) => {
  Object.assign(form, template.values);
  saveMessage.value = `Plantilla aplicada: ${template.title}. Ya puedes ajustar cualquier campo antes de guardar.`;
};

const resetForm = () => {
  Object.assign(form, defaultForm());
  saveMessage.value = "Formulario limpio. Puedes empezar desde cero o volver a usar una plantilla.";
};

const simulateSave = () => {
  const target = form.docente || "registro sin docente";
  saveMessage.value = `Guardado demo completado para ${target}. Este paso aun es visual y despues lo conectamos al backend.`;
};

const summaryTitle = computed(() => {
  if (form.secuencia && form.asignatura) {
    return `${form.secuencia} - ${form.asignatura}`;
  }

  return "Aun no has definido la secuencia";
});

const summaryDescription = computed(() => {
  if (form.observaciones) {
    return form.observaciones;
  }

  return "Usa una plantilla o llena el formulario para ver aqui un resumen breve del registro.";
});

const summaryItems = computed(() => [
  { label: "Docente", value: form.docente || "Sin capturar" },
  { label: "Periodo", value: form.periodo || "Sin capturar" },
  { label: "Tipo", value: form.tipo || "Sin capturar" },
  { label: "Estado", value: form.estado || "Sin capturar" },
  { label: "Prioridad", value: form.prioridad || "Sin capturar" },
  { label: "Enfoque", value: form.estado === "Completa" ? "Lista para cierre" : "Seguimiento activo" },
]);
</script>
