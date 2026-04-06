<template>
  <div class="flex flex-col gap-4">

    <!-- Selección de archivo -->
    <span>Selecciona el archivo de usuarios</span>
    <input 
      type="file" 
      accept=".csv,.xlsx"
      class="border rounded-lg p-2 w-md"
      @change="onFileChange"
    />

    <span class="text-muted-foreground">
      Formatos aceptados: xlsx, csv
    </span>

    <button
      class="w-1/3 px-4 py-2 rounded-lg bg-verde-fuerte text-white hover:brightness-95"
      :disabled="!archivo"
      @click="simularImportacion"
    >
      Importar Usuarios
    </button>
  </div>

  <!-- Plantilla -->
  <div class="mt-4">
    ¿No tienes una plantilla?
    <a href="#" class="text-verde-fuerte flex gap-3">
      Descárgala aquí
      <HardDriveDownload />
    </a>
  </div>

  <div class="my-6">
    <hr aria-hidden="true" class="h-px border-2" />
  </div>

  <!-- Sección de resultados -->
  <div class="grid gap-6 md:grid-cols-2">

    <!-- Estado -->
    <div>
      <h3 class="text-2xl m-4 font-bold">Estado de Importación</h3>

      <div class="border-2 rounded-lg p-4 m-4">
        <ul>
          <li class="p-2">Usuarios</li>
          <li class="p-2">
            Total en archivo:
            <span class="text-verde-fuerte">{{ estado.total }}</span>
          </li>

          <li class="p-2">
            Agrupados:
            <span class="text-blue-500">{{ estado.correctos }}</span>
          </li>

          <li class="p-2">
            Con Error:
            <span class="text-red-500">{{ estado.errores }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Errores -->
    <div>
      <h3 class="text-2xl m-4 font-bold">Detalles de Errores</h3>

      <p class="text-muted-foreground m-4">
        Se encontraron los siguientes errores durante la importación
      </p>

      <Alert variant="destructive" class="p-4" v-for="(err, index) in errores" :key="index">
        <AlertTitle>Fila {{ err.fila }}:</AlertTitle>
        <AlertDescription>
          {{ err.mensaje }}
          <Button class="bg-red-600 hover:bg-red-700 ml-3">
            Descargar informe completo de errores
          </Button>
        </AlertDescription>
      </Alert>

      <p v-if="errores.length === 0" class="m-4 text-muted-foreground">
        No se encontraron errores.
      </p>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { HardDriveDownload } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";

const archivo = ref<File | null>(null);

const estado = ref({
  total: 0,
  correctos: 0,
  errores: 0,
});

const errores = ref<{ fila: number; mensaje: string }[]>([]);

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  archivo.value = target.files?.[0] ?? null;
}

/* Simular importación */
function simularImportacion() {
  if (!archivo.value) return;

  // --- Simulación estática ---
  estado.value = {
    total: 150,
    correctos: 120,
    errores: 30,
  };

  errores.value = [
    { fila: 5, mensaje: "Correo inválido." },
    { fila: 20, mensaje: "Nombre faltante." },
    { fila: 48, mensaje: "Carrera no válida." },
  ];
}
</script>
