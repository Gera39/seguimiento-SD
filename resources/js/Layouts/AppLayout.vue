<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset class="bg-slate-100/80">
      <div class="min-h-screen">
        <slot />
      </div>
    </SidebarInset>
  </SidebarProvider>
</template>

<script setup lang="ts">
import AppSidebar from "@/components/AppSidebar.vue";
import { SidebarInset, SidebarProvider } from "@/components/ui/sidebar";
</script>
