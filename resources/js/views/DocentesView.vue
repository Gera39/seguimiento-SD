<template>
    <Header :titulo="tituloPagina" />

    <!-- TÍTULO Y BOTÓN -->
    <div class="m-4 flex justify-between items-center">
        <h1 class="text-4xl font-bold flex items-center gap-3">
            <GraduationCap :size="32" />
            Listado de Docentes y Revisores
        </h1>

        <Button 
            class="px-4 py-2 bg-verde-fuerte text-white rounded-lg hover:brightness-95"
            @click="abrirCrear">
            Agregar Docente
        </Button>
    </div>

    <!-- BUSCADOR -->
    <div class="m-4">
        <div class="mb-4 flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none">
                    <Search class="w-5 h-5" />
                </span>
                <Input placeholder="Buscar docente..." class="pl-11 pr-4 w-full rounded-lg" />
            </div>

            <Button class="flex items-center gap-2 px-4 py-2 bg-verde-fuerte text-white rounded-lg shadow hover:brightness-95">
                <Search class="w-4 h-4" />
                Buscar
            </Button>
        </div>

        <!-- TABS -->
        <Tabs default-value="activos">
            <TabsList class="grid w-full grid-cols-2">
                <TabsTrigger value="activos">
                    <GraduationCap /> Docentes Activos
                </TabsTrigger>
                <TabsTrigger value="revisores">
                    <UserPen /> Revisores
                </TabsTrigger>
            </TabsList>

            <!-- TAB DOCENTES -->
            <TabsContent value="activos">
                <div class="rounded-lg border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Apellido</TableHead>
                                <TableHead>Correo Electrónico</TableHead>
                                <TableHead>Teléfono</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow 
                                v-for="docente in docentesActivos" 
                                :key="docente.id">
                                
                                <TableCell class="font-medium">{{ docente.nombre }}</TableCell>
                                <TableCell>{{ docente.apellido }}</TableCell>
                                <TableCell>{{ docente.email }}</TableCell>
                                <TableCell>{{ docente.telefono }}</TableCell>

                                <TableCell class="text-right">
                                    <Button @click="abrirEditar(docente)">
                                        <Pencil />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </TabsContent>

            <!-- TAB REVISORES -->
            <TabsContent value="revisores">
                <div class="rounded-lg border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Apellido</TableHead>
                                <TableHead>Correo Electrónico</TableHead>
                                <TableHead>Teléfono</TableHead>
                                <TableHead class="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow 
                                v-for="revisor in revisores" 
                                :key="revisor.id">
                                
                                <TableCell class="font-medium">{{ revisor.nombre }}</TableCell>
                                <TableCell>{{ revisor.apellido }}</TableCell>
                                <TableCell>{{ revisor.email }}</TableCell>
                                <TableCell>{{ revisor.telefono }}</TableCell>

                                <TableCell class="text-right">
                                    <Button @click="abrirEditar(revisor)">
                                        <Pencil />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </TabsContent>
        </Tabs>
    </div>

    <!-- MODAL CREAR -->
    <div 
        v-if="mostrarCrear"
        class="fixed inset-0 bg-black/40 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl shadow-xl p-8 w-[800px] max-h-[90vh] overflow-y-auto animate-fade">
            <h2 class="text-3xl font-bold mb-2">Crear Nuevo Docente</h2>
            <p class="text-gray-600 mb-6">
                Complete los siguientes campos para registrar un nuevo docente en el sistema.
            </p>

            <div class="p-4 border rounded-xl bg-gray-50 mb-6">
                <h3 class="font-bold mb-4 text-lg">Información Personal</h3>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <div>
                        <label>Nombre(s) *</label>
                        <Input 
                            v-model="nuevoUsuario.nombre"
                            :class="inputError(nuevoUsuario.nombre)"
                            placeholder="Ej: Juan Andrés" />
                    </div>

                    <div>
                        <label>Apellido(s) *</label>
                        <Input 
                            v-model="nuevoUsuario.apellido"
                            :class="inputError(nuevoUsuario.apellido)"
                            placeholder="Ej: Pérez García" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <div>
                        <label>Correo Electrónico Institucional *</label>
                        <Input 
                            v-model="nuevoUsuario.email"
                            :class="inputError(nuevoUsuario.email)"
                            placeholder="ejemplo@uth.edu" />
                    </div>

                    <div>
                        <label>Teléfono *</label>
                        <Input 
                            v-model="nuevoUsuario.telefono"
                            :class="inputError(nuevoUsuario.telefono)"
                            placeholder="555-1234" />
                    </div>
                </div>

                <div>
                    <label class="font-semibold">Rol</label>
                    <div class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium mt-1">
                        Docente
                    </div>
                </div>
            </div>

            <!-- CREDENCIALES -->
            <div class="p-4 border rounded-xl bg-gray-50 mb-4">
                <h3 class="font-bold mb-4 text-lg">Credenciales de Acceso</h3>

                <label class="flex items-center gap-2">
                    <input type="checkbox" v-model="nuevoUsuario.enviarCredenciales" />
                    Enviar credenciales por correo electrónico
                </label>
            </div>

            <div class="flex justify-end gap-3">
                <Button @click="cerrarCrear" variant="outline">Cancelar</Button>
                <Button class="bg-verde-fuerte text-white" @click="crearUsuario">
                    Crear Docente
                </Button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div 
        v-if="mostrarEditar"
        class="fixed inset-0 bg-black/40 flex justify-center items-center z-50">

        <div class="bg-white rounded-xl shadow-xl p-8 w-[900px] max-h-[90vh] overflow-y-auto animate-fade">
            <h2 class="text-3xl font-bold mb-2">Editar Perfil de Usuario</h2>
            <p class="text-gray-600 mb-6">Actualiza la información personal y de contacto del usuario.</p>

            <div class="p-4 border rounded-xl bg-gray-50 mb-6">
                <h3 class="font-bold mb-4 text-lg">Información Personal</h3>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label>Nombre</label>
                        <Input v-model="usuarioEdit.nombre" />
                    </div>
                    <div>
                        <label>Apellido</label>
                        <Input v-model="usuarioEdit.apellido" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label>Nombre de Usuario</label>
                        <Input v-model="usuarioEdit.username" />
                    </div>

                    <div>
                        <label>Correo Electrónico</label>
                        <Input v-model="usuarioEdit.email" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <div>
                        <label>Teléfono</label>
                        <Input v-model="usuarioEdit.telefono" />
                    </div>

                    <div>
                        <label>Cuenta Activa</label>
                        <div><input type="checkbox" v-model="usuarioEdit.activo" /></div>
                    </div>
                </div>

                <div>
                    <label class="font-semibold">Rol</label>
                    <select v-model="usuarioEdit.rol" class="border rounded-lg px-3 py-2 w-full">
                        <option>Docente</option>
                        <option>Revisor</option>
                    </select>
                </div>
            </div>

            <!-- SEGURIDAD -->
            <div class="p-4 border rounded-xl bg-gray-50 mb-6">
                <h3 class="font-bold mb-4 text-lg">Seguridad</h3>

                <Button variant="outline" class="mb-2" @click="mostrarAlerta = true">
                    Restablecer Contraseña
                </Button>

                <p class="text-sm text-gray-500 mb-3">
                    Este botón enviará un correo al usuario con instrucciones para restablecer su contraseña.
                </p>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <Button @click="cerrarEditar" variant="outline">Cancelar</Button>
                <Button class="bg-verde-fuerte text-white" @click="guardarCambios">
                    Guardar Cambios
                </Button>
            </div>
        </div>
    </div>

    <!-- ALERTA PARA RESTABLECER CONTRASEÑA -->
    <div v-if="mostrarAlerta" class="fixed inset-0 bg-black/40 flex justify-center items-center z-[200]">
        <div class="bg-white px-10 py-8 rounded-2xl shadow-xl animate-fade flex flex-col items-center gap-4">
            <Check class="text-verde-fuerte w-10 h-10" />
            <p class="text-lg font-semibold text-center">
                Se ha enviado un correo para restablecer la contraseña del usuario.
            </p>
            <Button class="bg-verde-fuerte text-white" @click="mostrarAlerta = false">
                Aceptar
            </Button>
        </div>
    </div>

    <!-- CARGANDO -->
    <div v-if="loading"
        class="fixed inset-0 flex items-center justify-center bg-black/40 z-[200]">
        
        <div class="bg-white px-10 py-8 rounded-2xl shadow-xl animate-fade flex flex-col items-center gap-4">
            <div class="relative">
                <div class="animate-spin w-12 h-12 border-4 border-gray-300 border-t-verde-fuerte rounded-full"></div>
                <ArrowRight class="absolute inset-0 m-auto text-verde-fuerte animate-pulse" />
            </div>
            <p class="text-lg font-semibold">Procesando...</p>
        </div>
    </div>

    <!-- COMPLETADO -->
    <div v-if="completado"
        class="fixed inset-0 flex items-center justify-center bg-black/40 z-[200]">

        <div class="bg-white px-10 py-8 rounded-2xl shadow-xl animate-bounce-in flex flex-col items-center gap-3">
            <Check class="text-verde-fuerte w-10 h-10" />
            <p class="text-xl font-bold">¡Operación completada!</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import Header from "@/components/Header.vue";
import { GraduationCap, UserPen, Pencil, Search, Check, ArrowRight } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { ref } from "vue";

const tituloPagina = {
    text: "Listado de Docentes y Revisores",
    links: ["Listado de Docentes y Revisores"]
};

const docentesActivos = ref([
    { id: 1, nombre: "Juan", apellido: "Pérez", email: "juan.perez@uth.edu", telefono: "555-0101" },
    { id: 2, nombre: "María", apellido: "García", email: "maria.garcia@uth.edu", telefono: "555-0102" },
    { id: 3, nombre: "Carlos", apellido: "López", email: "carlos.lopez@uth.edu", telefono: "555-0103" },
]);

const revisores = ref([
    { id: 4, nombre: "Ana", apellido: "Martínez", email: "ana.martinez@uth.edu", telefono: "555-0201" },
    { id: 5, nombre: "Pedro", apellido: "Rodríguez", email: "pedro.rodriguez@uth.edu", telefono: "555-0202" },
]);

// Estados globales
const mostrarCrear = ref(false);
const mostrarEditar = ref(false);
const mostrarAlerta = ref(false);
const loading = ref(false);
const completado = ref(false);

// Para activar la animación de errores
const intentoGuardar = ref(false);

// Form Crear
const nuevoUsuario = ref({
    nombre: "",
    apellido: "",
    email: "",
    telefono: "",
    activo: true,
    enviarCredenciales: true
});

// Form Editar
const usuarioEdit = ref({
    id: 0,
    nombre: "",
    apellido: "",
    email: "",
    telefono: "",
    username: "",
    rol: "Docente",
    activo: true
});

// Estilos de error SOLO cuando se intenta guardar
function inputError(value: string) {
    return intentoGuardar.value && !value ? "border-red-500 ring-red-300 ring-2" : "";
}

// Abrir modales
function abrirCrear() { intentoGuardar.value = false; mostrarCrear.value = true; }
function cerrarCrear() { mostrarCrear.value = false; }

function abrirEditar(usuario: any) {
    usuarioEdit.value = { 
        ...usuario, 
        username: usuario.nombre.toLowerCase(), 
        rol: usuario.rol || "Docente",
        activo: true 
    };
    mostrarEditar.value = true;
}

function cerrarEditar() { mostrarEditar.value = false; }

// Simulación de acciones
function procesarAccion(callback: Function) {
    loading.value = true;

    setTimeout(() => {
        loading.value = false;
        completado.value = true;

        setTimeout(() => {
            completado.value = false;
            callback();
        }, 1000);

    }, 1200);
}

// Crear
function crearUsuario() {
    intentoGuardar.value = true;

    if (!nuevoUsuario.value.nombre || 
        !nuevoUsuario.value.apellido ||
        !nuevoUsuario.value.email ||
        !nuevoUsuario.value.telefono) {
        return;
    }

    procesarAccion(() => {
        const nuevo = {
            id: Date.now(),
            nombre: nuevoUsuario.value.nombre,
            apellido: nuevoUsuario.value.apellido,
            email: nuevoUsuario.value.email,
            telefono: nuevoUsuario.value.telefono
        };

        docentesActivos.value.push(nuevo);
        mostrarCrear.value = false;

        nuevoUsuario.value = {
            nombre: "",
            apellido: "",
            email: "",
            telefono: "",
            activo: true,
            enviarCredenciales: true
        };
        intentoGuardar.value = false;
    });
}

// Guardar cambios
function guardarCambios() {
    procesarAccion(() => {
        const lista = usuarioEdit.value.rol === "Revisor" ? revisores : docentesActivos;
        const index = lista.value.findIndex((u: any) => u.id === usuarioEdit.value.id);

        if (index !== -1) lista.value[index] = { ...usuarioEdit.value };

        mostrarEditar.value = false;
    });
}
</script>

<style>
.animate-fade {
    animation: fadeIn 0.2s ease-in-out;
}
.animate-bounce-in {
    animation: bounceIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: scale(.97); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes bounceIn {
    0% { transform: scale(0.6); opacity: 0; }
    60% { transform: scale(1.05); opacity: 1; }
    100% { transform: scale(1); }
}
</style>
