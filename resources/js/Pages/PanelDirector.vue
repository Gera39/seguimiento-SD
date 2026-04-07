<template>
  <div>
    <Header :titulo="tituloPagina" />

    <main class="director-page">
      <section class="sd-card sd-hero">
        <div class="sd-hero__top">
          <div>
            <p class="sd-label">Director</p>
            <h1 class="sd-title">Panel de alta y asignacion de personal</h1>
            <p class="sd-copy">
              Aqui el Director puede crear maestros, definir materia y grado, asignar revisores y decidir
              que materias y que maestros entraran en cada cola de revision.
            </p>
          </div>
          <div class="sd-chip-row">
            <span class="sd-chip">Visibilidad total</span>
            <span class="sd-chip">Control de revision</span>
          </div>
        </div>
      </section>

      <section class="sd-metrics">
        <article v-for="metric in metrics" :key="metric.label" class="sd-card sd-metric">
          <p class="sd-label muted">{{ metric.label }}</p>
          <p class="sd-metric__value">{{ metric.value }}</p>
          <p class="sd-copy">{{ metric.caption }}</p>
        </article>
      </section>

      <section class="sd-grid">
        <article class="sd-card">
          <div class="sd-section">
            <div>
              <p class="sd-label muted">Configuracion</p>
              <h2 class="sd-section__title">Crear perfil</h2>
            </div>
            <span class="sd-pill">Rol + cobertura</span>
          </div>

          <div v-if="message" class="sd-message">
            {{ message }}
          </div>

          <form class="sd-form" @submit.prevent="saveProfile">
            <div class="sd-form__grid">
              <label class="sd-field">
                <span class="sd-label">Nombre</span>
                <input v-model="form.nombre" type="text" class="sd-control" placeholder="Nombre completo" />
              </label>

              <label class="sd-field">
                <span class="sd-label">Correo</span>
                <input v-model="form.correo" type="email" class="sd-control" placeholder="correo@escuela.edu" />
              </label>

              <label class="sd-field">
                <span class="sd-label">Rol</span>
                <select v-model="form.rol" class="sd-control" @change="handleRoleChange">
                  <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
              </label>

              <label class="sd-field">
                <span class="sd-label">{{ form.rol === "Revisor" ? "Materia base" : "Materia a cargo" }}</span>
                <select v-model="form.materia" class="sd-control" :disabled="form.rol === 'Director'">
                  <option value="">Selecciona una materia</option>
                  <option v-for="materia in materias" :key="materia.nombre" :value="materia.nombre">
                    {{ materia.nombre }}
                  </option>
                </select>
              </label>

              <label class="sd-field">
                <span class="sd-label">{{ form.rol === "Director" ? "Cobertura" : "Grado" }}</span>
                <select v-model="form.grado" class="sd-control" :disabled="form.rol === 'Director'">
                  <option value="">{{ form.rol === "Director" ? "Toda la escuela" : "Selecciona un grado" }}</option>
                  <option v-for="grado in grados" :key="grado" :value="grado">{{ grado }}</option>
                </select>
              </label>

              <label class="sd-field">
                <span class="sd-label">Estado inicial</span>
                <select v-model="form.estado" class="sd-control">
                  <option value="Activo">Activo</option>
                  <option value="Pendiente">Pendiente</option>
                </select>
              </label>
            </div>

            <section v-if="isReviewer" class="sd-accordion">
              <button type="button" class="sd-accordion__trigger" @click="reviewOpen = !reviewOpen">
                <span>Configuracion del revisor</span>
                <span>{{ reviewOpen ? "Ocultar" : "Mostrar" }}</span>
              </button>

              <div v-if="reviewOpen" class="sd-accordion__content">
                <div class="sd-form__grid">
                  <div class="sd-field">
                    <span class="sd-label">Materias que revisara</span>
                    <div class="sd-tags">
                      <label
                        v-for="materia in materias"
                        :key="materia.nombre"
                        class="sd-tag"
                        :style="getMateriaStyle(materia.color, form.materiasRevision.includes(materia.nombre))"
                      >
                        <input v-model="form.materiasRevision" type="checkbox" class="sr-only" :value="materia.nombre" />
                        <span>{{ materia.nombre }}</span>
                      </label>
                    </div>
                  </div>

                  <div class="sd-field">
                    <span class="sd-label">Maestros que revisara</span>
                    <div v-if="teacherOptions.length" class="sd-checklist">
                      <label v-for="teacher in teacherOptions" :key="teacher.id" class="sd-check">
                        <input v-model="form.maestrosAsignados" type="checkbox" :value="teacher.id" />
                        <span>{{ teacher.nombre }}</span>
                        <small>{{ teacher.materia || "Sin materia" }} / {{ teacher.grado || "Sin grado" }}</small>
                      </label>
                    </div>
                    <div v-else class="sd-empty sd-empty--inline">
                      Primero crea al menos un maestro para habilitar la asignacion de revision.
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <div class="sd-actions">
              <button type="submit" class="sd-button">Guardar perfil</button>
              <p class="sd-copy">
                Si el rol es Revisor se abre una seccion adicional para seleccionar materias y maestros.
              </p>
            </div>
          </form>
        </article>

        <article class="sd-card">
          <div class="sd-section">
            <div>
              <p class="sd-label muted">Resumen del rol</p>
              <h2 class="sd-section__title">Cobertura visible</h2>
            </div>
          </div>

          <div class="sd-stack">
            <div v-for="role in roleCards" :key="role.nombre" class="sd-mini-card">
              <div class="sd-mini-card__top">
                <p class="sd-row__title">{{ role.nombre }}</p>
                <span class="sd-role" :style="role.style">{{ role.resumen }}</span>
              </div>
              <p class="sd-copy">{{ role.descripcion }}</p>
            </div>
          </div>

          <div class="sd-panel">
            <p class="sd-label muted">Estatus que observa el Director</p>
            <div class="sd-statuses">
              <span
                v-for="status in statusBadges"
                :key="status.label"
                class="sd-status"
                :style="{ backgroundColor: status.background, color: status.color }"
              >
                {{ status.label }}
              </span>
            </div>
          </div>

          <div class="sd-panel">
            <p class="sd-label muted">Seleccion actual</p>
            <div class="sd-summary">
              <div class="sd-row">
                <span>Rol</span>
                <strong>{{ form.rol }}</strong>
              </div>
              <div class="sd-row">
                <span>Materia</span>
                <strong>{{ form.materia || "Sin definir" }}</strong>
              </div>
              <div class="sd-row">
                <span>Grado</span>
                <strong>{{ form.rol === "Director" ? "Toda la escuela" : form.grado || "Sin definir" }}</strong>
              </div>
              <div v-if="isReviewer" class="sd-row">
                <span>Materias de revision</span>
                <strong>{{ form.materiasRevision.length }}</strong>
              </div>
              <div v-if="isReviewer" class="sd-row">
                <span>Maestros asignados</span>
                <strong>{{ form.maestrosAsignados.length }}</strong>
              </div>
            </div>
          </div>
        </article>
      </section>

      <section class="sd-grid">
        <article class="sd-card">
          <div class="sd-section">
            <div>
              <p class="sd-label muted">Personal registrado</p>
              <h2 class="sd-section__title">Vista del Director</h2>
            </div>
            <span class="sd-pill">{{ profiles.length }} perfiles</span>
          </div>

          <div class="sd-table__wrap">
            <table class="sd-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Rol</th>
                  <th>Materia</th>
                  <th>Grado</th>
                  <th>Asignacion</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody v-if="profiles.length">
                <tr v-for="profile in profiles" :key="profile.id">
                  <td>
                    <div class="sd-row__title">{{ profile.nombre }}</div>
                    <div class="sd-copy">{{ profile.correo }}</div>
                  </td>
                  <td><span class="sd-role" :style="getRoleStyle(profile.rol)">{{ profile.rol }}</span></td>
                  <td>{{ profile.materia || "Global" }}</td>
                  <td>{{ profile.grado || (profile.rol === "Director" ? "Toda la escuela" : "Sin definir") }}</td>
                  <td>{{ getAssignmentLabel(profile) }}</td>
                  <td><span class="sd-pill">{{ profile.estado }}</span></td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr>
                  <td colspan="6" class="sd-empty sd-empty--cell">
                    Todavia no hay perfiles creados. Usa el formulario para dar de alta maestros o revisores.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <article class="sd-card">
          <div class="sd-section">
            <div>
              <p class="sd-label muted">Cobertura por materia</p>
              <h2 class="sd-section__title">Mapa rapido</h2>
            </div>
          </div>

          <div class="sd-subjects">
            <div v-for="materia in materias" :key="materia.nombre" class="sd-subject">
              <div class="sd-subject__name">
                <span class="sd-subject__dot" :style="{ backgroundColor: materia.color }" />
                <span>{{ materia.nombre }}</span>
              </div>
              <div class="sd-copy">
                {{ teacherCount(materia.nombre) }} maestro(s) / {{ reviewerCount(materia.nombre) }} revisor(es)
              </div>
            </div>
          </div>

          <div class="sd-panel">
            <p class="sd-label muted">Logica de revision</p>
            <p class="sd-copy">
              Un revisor puede quedar ligado a una o varias materias y tambien a uno o varios maestros.
              Con eso el Director distribuye la cola con mas precision.
            </p>
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

defineOptions({ layout: AppLayout });

type Role = "Director" | "Maestro" | "Revisor";

interface Profile {
  id: string;
  nombre: string;
  correo: string;
  rol: Role;
  materia: string;
  grado: string;
  estado: string;
  materiasRevision: string[];
  maestrosAsignados: string[];
}

const tituloPagina = {
  text: "Panel del Director",
  links: ["Paneles", "Director"],
};

const roles: Role[] = ["Director", "Maestro", "Revisor"];

const materias = [
  { nombre: "Matem\u00e1ticas", color: "#534AB7" },
  { nombre: "Espa\u00f1ol", color: "#1D9E75" },
  { nombre: "Ciencias", color: "#185FA5" },
  { nombre: "Historia", color: "#D85A30" },
  { nombre: "Geograf\u00eda", color: "#639922" },
  { nombre: "Ed. F\u00edsica", color: "#D4537E" },
];

const grados = ["1\u00b0", "2\u00b0", "3\u00b0", "4\u00b0", "5\u00b0", "6\u00b0"];

const statusBadges = [
  { label: "Borrador", background: "#D3D1C7", color: "#444441" },
  { label: "En revisi\u00f3n", background: "#FAC775", color: "#633806" },
  { label: "Aprobada", background: "#C0DD97", color: "#27500A" },
  { label: "Rechazada", background: "#F7C1C1", color: "#791F1F" },
];

const profiles = ref<Profile[]>([]);
const reviewOpen = ref(false);
const message = ref("");

const form = reactive({
  nombre: "",
  correo: "",
  rol: "Maestro" as Role,
  materia: "",
  grado: "",
  estado: "Activo",
  materiasRevision: [] as string[],
  maestrosAsignados: [] as string[],
});

const isReviewer = computed(() => form.rol === "Revisor");

const teacherOptions = computed(() =>
  profiles.value.filter((profile) => profile.rol === "Maestro"),
);

const metrics = computed(() => [
  { label: "Personal total", value: pad(profiles.value.length), caption: "Perfiles visibles en este panel" },
  { label: "Maestros", value: pad(teacherOptions.value.length), caption: "Responsables de secuencias" },
  { label: "Revisores", value: pad(profiles.value.filter((item) => item.rol === "Revisor").length), caption: "Colas configuradas" },
  { label: "Materias cubiertas", value: pad(new Set(profiles.value.map((item) => item.materia).filter(Boolean)).size), caption: "Areas con responsable" },
]);

const roleCards = [
  {
    nombre: "Director",
    resumen: "Visibilidad total",
    descripcion: "Ve toda la escuela, aprueba o rechaza y supervisa reportes globales.",
    style: getRoleStyle("Director"),
  },
  {
    nombre: "Maestro",
    resumen: "Gestiona secuencias",
    descripcion: "Trabaja sus propias secuencias, corrige y vuelve a enviar cuando haga falta.",
    style: getRoleStyle("Maestro"),
  },
  {
    nombre: "Revisor",
    resumen: "Dictamina con comentario",
    descripcion: "Recibe la cola asignada y emite una decision con observacion obligatoria.",
    style: getRoleStyle("Revisor"),
  },
];

function handleRoleChange() {
  reviewOpen.value = form.rol === "Revisor";

  if (form.rol === "Director") {
    form.materia = "";
    form.grado = "";
  }

  if (form.rol !== "Revisor") {
    form.materiasRevision = [];
    form.maestrosAsignados = [];
  }
}

function saveProfile() {
  if (!form.nombre || !form.correo) {
    message.value = "Completa nombre y correo para guardar el perfil.";
    return;
  }

  if (form.rol !== "Director" && (!form.materia || !form.grado)) {
    message.value = "Selecciona materia y grado para maestros o revisores.";
    return;
  }

  if (form.rol === "Revisor" && !form.materiasRevision.length) {
    message.value = "Selecciona al menos una materia de revision.";
    return;
  }

  profiles.value.unshift({
    id: `perfil-${String(profiles.value.length + 1).padStart(3, "0")}`,
    nombre: form.nombre,
    correo: form.correo,
    rol: form.rol,
    materia: form.materia,
    grado: form.grado,
    estado: form.estado,
    materiasRevision: [...form.materiasRevision],
    maestrosAsignados: [...form.maestrosAsignados],
  });

  message.value = `${form.nombre} quedo registrado como ${form.rol}.`;

  Object.assign(form, {
    nombre: "",
    correo: "",
    rol: "Maestro" as Role,
    materia: "",
    grado: "",
    estado: "Activo",
    materiasRevision: [],
    maestrosAsignados: [],
  });

  reviewOpen.value = false;
}

function getAssignmentLabel(profile: Profile) {
  if (profile.rol === "Director") return "Escuela completa";
  if (profile.rol === "Revisor") return `${profile.materiasRevision.length} materia(s) / ${profile.maestrosAsignados.length} maestro(s)`;
  return `${profile.materia} / ${profile.grado}`;
}

function teacherCount(materia: string) {
  return profiles.value.filter((item) => item.rol === "Maestro" && item.materia === materia).length;
}

function reviewerCount(materia: string) {
  return profiles.value.filter((item) => item.rol === "Revisor" && item.materiasRevision.includes(materia)).length;
}

function pad(value: number) {
  return String(value).padStart(2, "0");
}

function getRoleStyle(role: Role) {
  if (role === "Director") return { backgroundColor: "rgba(24, 95, 165, 0.12)", color: "#185FA5" };
  if (role === "Revisor") return { backgroundColor: "rgba(212, 83, 126, 0.12)", color: "#D4537E" };
  return { backgroundColor: "rgba(29, 158, 117, 0.12)", color: "#1D9E75" };
}

function getMateriaStyle(color: string, selected: boolean) {
  return {
    borderColor: selected ? color : "var(--color-border-tertiary)",
    backgroundColor: selected ? `${color}14` : "transparent",
    color,
  };
}
</script>

<style scoped>
.director-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 16px;
  background: transparent;
}

.sd-card,
.sd-panel,
.sd-mini-card,
.sd-accordion {
  border: 0.5px solid var(--color-border-tertiary);
  border-radius: 12px;
  background: var(--color-background-secondary);
}

.sd-hero,
.sd-form,
.sd-stack,
.sd-table__wrap,
.sd-subjects,
.sd-panel {
  padding: 18px;
}

.sd-hero__top,
.sd-section,
.sd-actions,
.sd-mini-card__top,
.sd-row,
.sd-subject,
.sd-subject__name {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.sd-metrics {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.sd-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr);
  gap: 16px;
}

.sd-form__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.sd-field,
.sd-summary,
.sd-checklist {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sd-label,
.sd-pill,
.sd-chip,
.sd-status,
.sd-role {
  font-size: 12px;
  font-weight: 500;
}

.muted,
.sd-copy,
.sd-table th,
.sd-table td {
  color: var(--color-text-secondary);
}

.sd-title,
.sd-section__title,
.sd-metric__value,
.sd-row__title {
  color: var(--color-text-primary);
  font-weight: 500;
}

.sd-title,
.sd-metric__value {
  font-size: 26px;
  line-height: 1.1;
}

.sd-section {
  padding: 18px 18px 0;
}

.sd-section__title {
  font-size: 18px;
  line-height: 1.2;
}

.sd-copy,
.sd-control,
.sd-table td,
.sd-table th,
.sd-check,
.sd-subject,
.sd-tag {
  font-size: 13px;
  font-weight: 400;
  line-height: 1.5;
}

.sd-chip-row,
.sd-statuses,
.sd-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.sd-chip,
.sd-pill,
.sd-status,
.sd-role,
.sd-tag {
  display: inline-flex;
  align-items: center;
  min-height: 28px;
  padding: 0 10px;
  border-radius: 8px;
  border: 0.5px solid var(--color-border-tertiary);
}

.sd-chip,
.sd-pill {
  color: var(--color-text-secondary);
  background: transparent;
}

.sd-control,
.sd-button,
.sd-accordion__trigger,
.sd-check,
.sd-table {
  border: 0.5px solid var(--color-border-secondary);
  border-radius: 8px;
}

.sd-control {
  min-height: 40px;
  padding: 0 12px;
  background: transparent;
  color: var(--color-text-primary);
}

.sd-control:disabled {
  color: var(--color-text-tertiary);
  background: rgba(68, 68, 65, 0.03);
}

.sd-accordion {
  margin-top: 16px;
}

.sd-accordion__trigger {
  width: 100%;
  min-height: 40px;
  padding: 0 14px;
  background: transparent;
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
}

.sd-accordion__content {
  padding: 14px;
}

.sd-tag {
  cursor: pointer;
}

.sd-check {
  display: grid;
  grid-template-columns: 16px 1fr auto;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
}

.sd-check small {
  font-size: 12px;
  color: var(--color-text-tertiary);
}

.sd-actions {
  margin-top: 16px;
}

.sd-button {
  min-height: 40px;
  padding: 0 14px;
  background: #185FA5;
  border-color: #185FA5;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 500;
}

.sd-message,
.sd-empty {
  padding: 12px;
  border: 0.5px solid var(--color-border-tertiary);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 400;
  color: var(--color-text-secondary);
}

.sd-message {
  margin: 16px 18px 0;
}

.sd-empty--inline {
  margin: 0;
}

.sd-empty--cell {
  text-align: left;
}

.sd-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sd-mini-card {
  padding: 14px;
}

.sd-summary {
  margin-top: 10px;
}

.sd-row strong {
  color: var(--color-text-primary);
  font-weight: 500;
}

.sd-table {
  width: 100%;
  border-collapse: collapse;
}

.sd-table th,
.sd-table td {
  padding: 12px;
  border-bottom: 0.5px solid var(--color-border-tertiary);
  text-align: left;
  vertical-align: top;
}

.sd-table th {
  font-size: 12px;
  font-weight: 500;
}

.sd-table tbody tr:last-child td {
  border-bottom: none;
}

.sd-subjects {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.sd-subject {
  padding: 14px 18px;
  border-bottom: 0.5px solid var(--color-border-tertiary);
}

.sd-subject:last-child {
  border-bottom: none;
}

.sd-subject__dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  margin-top: 4px;
}

@media (max-width: 1100px) {
  .sd-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 860px) {
  .sd-metrics,
  .sd-form__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .sd-hero__top,
  .sd-section,
  .sd-actions {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .director-page {
    padding: 12px;
  }

  .sd-metrics,
  .sd-form__grid {
    grid-template-columns: 1fr;
  }

  .sd-check {
    grid-template-columns: 16px 1fr;
  }

  .sd-check small {
    grid-column: 2;
  }

  .sd-table {
    display: block;
    overflow-x: auto;
  }
}
</style>
