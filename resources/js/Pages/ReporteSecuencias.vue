<template>
    <Header :titulo="tituloPagina" />

    <h1 class="flex items-center gap-3 m-4 text-2xl font-bold text-gray-700">
        <clipboard-document-check :size="28" />
        Reporte de Secuencias
    </h1>

    <div class="m-4 flex items-center gap-3">
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                <Search class="w-5 h-5" />
            </span>

            <Input
                v-model="filtros.search"
                placeholder="Buscar por docente, materia o estado..."
                class="pl-11 w-full rounded-md border-gray-300"
            />
        </div>

        <Button class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 hover:bg-green-700"
                @click="aplicarFiltros">
            <Search :size="18" /> Buscar
        </Button>

        <Button class="flex items-center gap-2 bg-gray-300 text-gray-700 px-4 py-2 hover:bg-gray-400"
                @click="limpiarFiltros">
            Limpiar
        </Button>
    </div>

    <div class="m-4 flex items-center gap-6">
        <div>
            <span class="font-semibold">Docente</span>
            <Select v-model="filtros.docente">
                <SelectTrigger class="w-[300px]">
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

        <div>
            <span class="font-semibold">Estado</span>
            <Select v-model="filtros.estado">
                <SelectTrigger class="w-[300px]">
                    <SelectValue placeholder="Selecciona un estado" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="Aprobada">Aprobada</SelectItem>
                        <SelectItem value="En revisión">En revisión</SelectItem>
                        <SelectItem value="Rechazada">Rechazada</SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex flex-col">
            <span class="font-semibold">Fecha creación</span>
            <input type="date" v-model="filtros.fecha" class="w-[250px] rounded-md border px-3 py-2" />
        </div>
    </div>

    <div class="m-4 border rounded-md bg-white">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>ID Secuencia</TableHead>
                    <TableHead>Nombre de Secuencia</TableHead>
                    <TableHead>Docente</TableHead>
                    <TableHead>Materia</TableHead>
                    <TableHead>Cuatrimestre</TableHead>
                    <TableHead>Fecha de Creación</TableHead>
                    <TableHead>Estado</TableHead>
                    <TableHead>Acciones</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-for="sec in resultadosPaginados" :key="sec.id">
                    <TableCell>{{ sec.id }}</TableCell>
                    <TableCell>{{ sec.titulo }}</TableCell>
                    <TableCell>{{ sec.docente }}</TableCell>
                    <TableCell>{{ sec.materia }}</TableCell>
                    <TableCell>{{ sec.grado }}</TableCell>
                    <TableCell>{{ sec.fecha }}</TableCell>

                    <TableCell>
                        <span class="px-3 py-1 text-xs font-semibold rounded"
                              :class="badgeEstado(sec.estadoDocente)">
                            {{ sec.estadoDocente }}
                        </span>
                    </TableCell>

                    <TableCell>
                        <Button size="sm" variant="outline" @click="verSecuencia">
                            <Eye :size="16" />
                        </Button>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

        <div class="flex justify-center items-center py-4 gap-4">
            <Button variant="outline" :disabled="paginaActual === 1" @click="paginaActual--">
                Anterior
            </Button>

            <span class="font-semibold">
                Página {{ paginaActual }} / {{ totalPaginas }}
            </span>

            <Button variant="outline" :disabled="paginaActual === totalPaginas" @click="paginaActual++">
                Siguiente
            </Button>
        </div>
    </div>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue'
import { useRouter } from "vue-router"
import { Search, Eye } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref, computed, watch } from 'vue'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const router = useRouter()

function verSecuencia() {
    router.push(`/visualizacion-validacion`)
}

const tituloPagina = {
    text: "Reportes de Secuencias Didácticas",
    links: []
}

const secuencias = ref([
    { id: 'SC001', titulo: 'Programación Python', docente: 'Ana Cortez', materia: 'Informática', grado: '3ro', fecha: '2024-01-15', estadoDocente: 'Aprobada' },
    { id: 'SC002', titulo: 'Desarrollo Frontend React', docente: 'Carlos Ruiz', materia: 'Informática', grado: '4to', fecha: '2024-01-20', estadoDocente: 'En revisión' },
    { id: 'SC003', titulo: 'Bases de Datos SQL', docente: 'Juan Pérez', materia: 'Informática', grado: '2do', fecha: '2024-02-01', estadoDocente: 'Rechazada' }
])

const filtros = ref({
    search: "",
    docente: "",
    estado: "",
    fecha: ""
})

const resultados = ref([...secuencias.value])

function limpiarFiltros() {
    filtros.value = { search: "", docente: "", estado: "", fecha: "" }
    paginaActual.value = 1
    aplicarFiltros()
}

function aplicarFiltros() {
    let data = [...secuencias.value]

    if (filtros.value.search)
        data = data.filter(s =>
            s.docente.toLowerCase().includes(filtros.value.search.toLowerCase()) ||
            s.titulo.toLowerCase().includes(filtros.value.search.toLowerCase()) ||
            s.materia.toLowerCase().includes(filtros.value.search.toLowerCase())
        )

    if (filtros.value.estado)
        data = data.filter(s => s.estadoDocente === filtros.value.estado)

    if (filtros.value.fecha)
        data = data.filter(s => s.fecha === filtros.value.fecha)

    resultados.value = data
}

const paginaActual = ref(1)
const tamanoPagina = 5

const totalPaginas = computed(() =>
    Math.max(1, Math.ceil(resultados.value.length / tamanoPagina))
)

const resultadosPaginados = computed(() => {
    const inicio = (paginaActual.value - 1) * tamanoPagina
    return resultados.value.slice(inicio, inicio + tamanoPagina)
})

watch(resultados, () => (paginaActual.value = 1))

function badgeEstado(estado: string) {
    switch (estado) {
        case 'Aprobada': return "bg-green-100 text-green-700"
        case 'En revisión': return "bg-yellow-100 text-yellow-700"
        case 'Rechazada': return "bg-red-100 text-red-700"
        default: return "bg-gray-100 text-gray-700"
    }
}
</script>
