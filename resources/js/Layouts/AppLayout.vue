<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset class="app-shell">
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

<style scoped>
.app-shell {
  background: var(--color-background-primary);
}
</style>
