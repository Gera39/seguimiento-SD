<template>
    <Header :titulo="tituloPagina" />
    <h1 class="flex m-4 items-center gap-3 mt-4 text-4xl font-extrabold ">
        <Notebook :size="32" /> Importar Datos de Docentes y Revisores
    </h1>

    <p class="m-4 text-muted-foreground">Selecciona el tipo de datos que desea importar y seleccion el archivo
        correspondiente</p>

    <div>
        <Tabs default-value="usuarios">
            <TabsList class="m-4 grid grid-cols-2 w-full max-w-4xl mx-auto">
                <TabsTrigger value="usuarios">
                    Importar Usuarios
                </TabsTrigger>
                <TabsTrigger value="asignaturas">
                    Importar Asignaturas
                </TabsTrigger>
            </TabsList>
            <TabsContent value="usuarios" class="m-4">
               <ImportarComponent />
            </TabsContent>
            <TabsContent value="asignaturas" class="m-4">
                <ImportarComponent />
            </TabsContent>
        </Tabs>
    </div>
</template>
<script setup lang="ts">
import Header from '@/components/Header.vue';
import ImportarComponent from '@/components/Importar/ImportarComponent.vue';
import { Notebook } from 'lucide-vue-next';
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs';
const tituloPagina = {
    text: "Importar Datos de Docentes y Revisores",
    links: []
};
</script>