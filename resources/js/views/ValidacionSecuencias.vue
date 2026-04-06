<template>
    <Header :titulo="tituloPagina" />
    <h1 class="flex items-center gap-3 m-4 text-2xl font-bold text-gray-700">
       <Notebook :size="28" />
        Validación Final de Secuencias Didácticas
    </h1>


    <div class="m-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[250px]">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                <Search class="w-5 h-5" />
            </span>
            <Input
                v-model="filtros.search"
                placeholder="Buscar por docente, materia o estado..."
                class="pl-11 pr-4 w-full rounded-md border-gray-300"
            />
        </div>

        <div class="flex items-center gap-2">
            <Button
                class="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-[#0F766E]"
                @click="aplicarFiltros">
                <Search :size="18" /> Buscar
            </Button>

            <Button
                class="flex items-center gap-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
                @click="limpiarFiltros">
                Limpiar
            </Button>
        </div>
    </div>
    <div class="m-4 flex flex-wrap items-end gap-6">
        <div class="flex flex-col min-w-[200px]">
            <span class="font-semibold">Docente</span>
            <Select v-model="filtros.docente">
                <SelectTrigger class="w-full">
                    <SelectValue placeholder="Selecciona un docente" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="Matemáticas">Matemáticas</SelectItem>
                        <SelectItem value="Historia">Historia</SelectItem>
                        <SelectItem value="Física">Física</SelectItem>
                        <SelectItem value="Literatura">Literatura</SelectItem>
                        <SelectItem value="Química">Química</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex flex-col min-w-[200px]">
            <span class="font-semibold">Estado</span>
            <Select v-model="filtros.estado">
                <SelectTrigger class="w-full">
                    <SelectValue placeholder="Selecciona un estado" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="Entregado">Entregado</SelectItem>
                        <SelectItem value="No Entregado">No Entregado</SelectItem>
                        <SelectItem value="Validado">Validado</SelectItem>
                        <SelectItem value="No Validado">No Validado</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex flex-col min-w-[200px]">
            <span class="font-semibold">Fecha creación</span>
            <input
                type="date"
                v-model="filtros.fecha"
                class="w-full rounded-md border px-3 py-2"
            />
        </div>
    </div>


    <div class="m-4 bg-white border rounded-md">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead># Sec.</TableHead>
                    <TableHead>Nombre</TableHead>
                    <TableHead>Docente</TableHead>
                    <TableHead>Cuatrimestre</TableHead>
                    <TableHead>Fecha</TableHead>
                    <TableHead>Estado Docente</TableHead>
                    <TableHead>Estado Validador</TableHead>
                    <TableHead>Acciones</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow
                    v-for="sec in resultadosPaginados"
                    :key="sec.id">
                    <TableCell>{{ sec.id }}</TableCell>
                    <TableCell>{{ sec.titulo }}</TableCell>
                    <TableCell>{{ sec.docente }}</TableCell>
                    <TableCell>{{ sec.grado }}</TableCell>
                    <TableCell>{{ sec.fecha }}</TableCell>

                    <TableCell>
                        <span
                            class="px-3 py-1 rounded text-xs font-semibold"
                            :class="estadoDocenteClass(sec.estadoDocente)"
                        >
                            {{ sec.estadoDocente }}
                        </span>
                    </TableCell>

                    <TableCell>
                        <span
                            class="px-3 py-1 rounded text-xs font-semibold"
                            :class="estadoValidadorClass(sec.estadoValidador)"
                        >
                            {{ sec.estadoValidador }}
                        </span>
                    </TableCell>

                    <TableCell>
                        <div class="flex gap-2">
                            <Button size="sm" variant="outline" @click="verSecuencia()">
                                <Eye :size="16" />
                            </Button>

                            <Button size="sm" class="bg-[#0F766E] text-white hover:bg-[#0d5f59]"
                                @click="confirmarValidacion(sec)">
                                <Check :size="16" />
                            </Button>

                            <Button size="sm" class="bg-red-600 text-white hover:bg-red-700"
                                @click="confirmarRechazo(sec)">
                                <X :size="16" />
                            </Button>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div class="flex justify-center items-center py-4 gap-4">
            <Button
                variant="outline"
                :disabled="paginaActual === 1"
                @click="paginaActual--">
                Anterior
            </Button>

            <span class="font-semibold">
                Página {{ paginaActual }} / {{ totalPaginas }}
            </span>

            <Button
                variant="outline"
                :disabled="paginaActual === totalPaginas"
                @click="paginaActual++">
                Siguiente
            </Button>
        </div>
    </div>

    <transition name="fade">
        <div v-if="modal.visible" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl w-[350px] animate-pop">
                <h2 class="text-xl font-bold mb-2 text-center">{{ modal.titulo }}</h2>
                <p class="text-gray-600 text-center mb-4">{{ modal.mensaje }}</p>

                <div class="flex justify-center gap-3">
                    <Button class="bg-gray-300 hover:bg-gray-400 text-gray-700"
                        @click="cerrarModal">
                        Cancelar
                    </Button>

                    <Button class="bg-blue-600 hover:bg-blue-700 text-white"
                        @click="modal.accionConfirmar">
                        Confirmar
                    </Button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue'
import { useRouter } from "vue-router"
import { Notebook, Search, Eye, Check, X } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref, computed, watch } from 'vue'

import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from "@/components/ui/table"
import { Button } from '@/components/ui/button'
import {
    Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue
} from '@/components/ui/select'

const router = useRouter()

function verSecuencia() {
    router.push(`/validacion-final`)
}

const tituloPagina = {
    text: "Validación de Secuencias Didácticas",
    links: []
}

const secuencias = ref([
    { id: 'SEC-001', titulo: 'Introducción a las Matemáticas', docente: 'Juan Pérez', grado: '1er Grado', fecha: '2024-01-15', estadoDocente: 'Entregado', estadoValidador: 'Validado' },
    { id: 'SEC-002', titulo: 'Historia de México', docente: 'María García', grado: '2do Grado', fecha: '2024-02-10', estadoDocente: 'No Entregado', estadoValidador: 'No Validado' },
    { id: 'SEC-003', titulo: 'Física Cuántica Básica', docente: 'Carlos López', grado: '3er Grado', fecha: '2024-03-05', estadoDocente: 'Entregado', estadoValidador: 'No Validado' },
    { id: 'SEC-004', titulo: 'Literatura Contemporánea', docente: 'Ana Martínez', grado: '2do Grado', fecha: '2024-03-20', estadoDocente: 'No Entregado', estadoValidador: 'Validado' }
])

const filtros = ref({
    search: "",
    docente: "",
    estado: "",
    fecha: ""
})

const resultados = ref([...secuencias.value])

function aplicarFiltros() {
    let data = [...secuencias.value]

    if (filtros.value.search) {
        const s = filtros.value.search.toLowerCase()
        data = data.filter(item =>
            item.docente.toLowerCase().includes(s) ||
            item.titulo.toLowerCase().includes(s) ||
            item.grado.toLowerCase().includes(s)
        )
    }

    if (filtros.value.estado) {
        data = data.filter(item =>
            item.estadoDocente === filtros.value.estado ||
            item.estadoValidador === filtros.value.estado
        )
    }

    if (filtros.value.fecha) {
        data = data.filter(item => item.fecha === filtros.value.fecha)
    }

    if (filtros.value.docente) {
        data = data.filter(item => item.titulo.includes(filtros.value.docente))
    }

    resultados.value = data
}

const limpiarFiltros = () => {
    filtros.value = { search: "", docente: "", estado: "", fecha: "" }
    resultados.value = [...secuencias.value]
    paginaActual.value = 1
}

const paginaActual = ref(1)
const tamanoPagina = ref(5)

const totalPaginas = computed(() =>
    Math.max(1, Math.ceil(resultados.value.length / tamanoPagina.value))
)

const resultadosPaginados = computed(() => {
    const i = (paginaActual.value - 1) * tamanoPagina.value
    return resultados.value.slice(i, i + tamanoPagina.value)
})

watch(resultados, () => {
    paginaActual.value = 1
})

function estadoDocenteClass(estado: string) {
    if (estado === "Entregado")
        return "bg-[#DCFCE7] text-[#0F766E]"
    if (estado === "No Entregado")
        return "bg-red-100 text-red-700"
    return "bg-gray-100 text-gray-600"
}

function estadoValidadorClass(estado: string) {
    if (estado === "Validado")
        return "bg-[#DBEAFE] text-blue-700"
    if (estado === "No Validado")
        return "bg-gray-200 text-gray-700"
    return "bg-gray-100 text-gray-600"
}
const modal = ref({
    visible: false,
    titulo: "",
    mensaje: "",
    accionConfirmar: () => {}
})

function mostrarModal(titulo: string, mensaje: string, accion: Function) {
    modal.value = {
        visible: true,
        titulo,
        mensaje,
        accionConfirmar: () => {
            accion()
            cerrarModal()
        }
    }
}

function cerrarModal() {
    modal.value.visible = false
}

function confirmarValidacion(sec: any) {
    mostrarModal(
        "¿Validar secuencia?",
        `La secuencia "${sec.titulo}" será marcada como VALIDADA.`,
        () => {
            sec.estadoValidador = "Validado"
        }
    )
}

function confirmarRechazo(sec: any) {
    mostrarModal(
        "¿Rechazar secuencia?",
        `La secuencia "${sec.titulo}" será marcada como NO VALIDADA.`,
        () => {
            sec.estadoValidador = "No Validado"
        }
    )
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes pop {
    0% { transform: scale(0.7); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.animate-pop {
    animation: pop .2s ease-out;
}
</style>
