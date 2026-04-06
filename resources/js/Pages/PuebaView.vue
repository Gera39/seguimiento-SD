<template>

    <AppLayout>
        <Header :titulo="tituloPagina" />
        CONTENIDOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO
    </AppLayout>
    
</template>
<script setup lang="ts">

import Header from "@/components/Header.vue";
import AppLayout from "@/layouts/AppLayout.vue";
const tituloPagina = {
    text:"AUI VAMOSSSSSSSS ",
    links:["DASHBOARD", "ANLITICAs"]
}

</script>