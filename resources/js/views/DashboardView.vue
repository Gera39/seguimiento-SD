<template>
  <Header :titulo="tituloPagina"></Header>
  <div class="flex flex-1 flex-col gap-4 p-4 pt-0 bg-muted/70">

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

      <!-- Alertas y Notificaciones (siempre visibles) -->
      <div class="md:col-span-2 rounded-xl gap-4 bg-white p-4">
        <div class="flex items-center justify-between mb-4">
          <h1 class="flex items-center gap-3 text-2xl font-extrabold">
            <BellRingIcon :size="22" /> Alertas y Notificaciones
          </h1>
          <button class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
            Todas las alertas
          </button>
        </div>

        <div>
          <Alert variant="info" class="mb-4">
            <CheckCircle2Icon />
            <AlertTitle>Alert Title</AlertTitle>
            <AlertDescription>Esta es una notificación informativa.</AlertDescription>
          </Alert>
          <Alert variant="destructive">
            <AlertCircleIcon />
            <AlertTitle>Destructive Alert</AlertTitle>
            <AlertDescription>Esta es una notificación importante.</AlertDescription>
          </Alert>
        </div>

        <!-- Resumen de Secuencias (solo director y revisor) -->
        <div v-if="isDirector || isRevisor" class="mt-8 text-center bg-white rounded-xl p-4 shadow">
          <h1 class="flex items-center gap-3 text-2xl font-extrabold mt-4">
            <BookTextIcon :size="22" /> Resumen de Secuencias Didacticas
          </h1>
          <div class="mt-4 grid auto-rows-min gap-4 md:grid-cols-2">
            <button class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <span class="text-5xl">{{ totalSecuencias }}</span>
              Total Secuencias
            </button>
            <button class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <span class="text-5xl">{{ pendientes }}</span>
              Pendientes de Aprobación
            </button>
            <button v-if="isDirector" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <span class="text-5xl">{{ enCurso }}</span>
              En curso
            </button>
            <button v-if="isDirector" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <span class="text-5xl">{{ completados }}</span>
              Completados
            </button>
          </div>
          <button v-if="isDirector" class="w-full mt-4 px-4 py-2 rounded-lg bg-verde-fuerte text-white">Gestionar Secuencias</button>
        </div>
      </div>

      <!-- Acciones Rapidas -->
      <div class="aspect-video rounded-xl bg-white flex flex-col gap-4 p-4">
        <h1 class="flex items-center gap-3 text-2xl font-bold">
          <ZapIcon :size="24" />
          Acciones Rápidas
        </h1>
        <div class="mt-4 grid auto-rows-min gap-4 md:grid-cols-2">

          <!-- Director: solo Aprobar y Reporte -->
          <template v-if="isDirector">
            <button class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm"
                    @click="goTo('/validaciones')">
              <ClipboardCheck :size="45" class="text-green-500" />
              Aprobar
            </button>
            <button class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm"
                    @click="goTo('/reportes')">
              <FileText :size="45" class="text-green-600" />
              Reporte de Secuencias
            </button>
          </template>

          <!-- Revisor: solo Mis Secuencias y Reporte -->
          <template v-else-if="isRevisor">
            <button @click="goTo('/mis-secuencias')" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <CirclePlus :size="45" class="text-blue-500" />
              Mis Secuencias
            </button>
            <button @click="goTo('/reportes')" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <FileText :size="45" class="text-green-600" />
              Reporte de Secuencias
            </button>
          </template>

          <!-- Docente -->
          <template v-else-if="isDocente">
            <button @click="goTo('/mis-secuencias')" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <CirclePlus :size="45" class="text-blue-500" />
              Mis Secuencias
            </button>
            <button @click="goTo('/comentarios')" class="flex flex-col items-center px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm">
              <MessageCircleMore :size="45" class="text-purple-600" />
              Revisar Comentarios
            </button>
          </template>

        </div>

        <!-- Actividades Recientes -->
        <div class="aspect-video rounded-xl mt-4">
          <h1 class="flex items-center gap-3 text-2xl font-bold">
            <Clock :size="24" />
            Actividades Recientes
          </h1>
          <div class="mt-4">
            <Alert variant="default" class="mb-4">
              <CheckCircle2Icon />
              <AlertTitle>Alert Title</AlertTitle>
              <AlertDescription>Esta es una actividad reciente.</AlertDescription>
            </Alert>
            <Alert variant="default" class="mb-4">
              <AlertCircleIcon />
              <AlertTitle>Destructive Alert</AlertTitle>
              <AlertDescription>Esta es una actividad reciente importante.</AlertDescription>
            </Alert>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import Header from "@/components/Header.vue";
import {
  AlertCircleIcon, CheckCircle2Icon, BellRingIcon,
  CirclePlus, Clock,
  ZapIcon, MessageCircleMore, ClipboardCheck, FileText,
  BookTextIcon
} from "lucide-vue-next";
import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/components/ui/alert";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const tituloPagina = { text: "Dashboard", links: ["Analytics", "Reports"] };

const totalSecuencias = 125;
const pendientes = 8;
const enCurso = 97;
const completados = 20;

const router = useRouter();
const auth = useAuthStore();
const role = auth.user?.role;

const isDirector = role === "director";
const isRevisor = role === "revisor";
const isDocente = role === "docente";

function goTo(url: string) {
  router.push(url);
}
</script>
