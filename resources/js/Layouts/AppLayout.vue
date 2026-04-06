<template>
    <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
        <router-view />
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from "@/components/AppSidebar.vue"
import {
  SidebarInset,
  SidebarProvider,
} from "@/components/ui/sidebar"
</script>

<script lang="ts">
export const description
  = "A sidebar that collapses to icons."
export const iframeHeight = "800px"
export const containerClass = "w-full h-full"
</script>