<template>
  <header class="app-header">
    <div class="app-header__inner">
      <div class="flex min-w-0 items-center gap-3">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="h-5" />

        <div class="min-w-0">
          <p class="app-header__title">{{ titulo.text }}</p>

          <div v-if="normalizedLinks.length" class="app-header__links">
            <template v-for="(link, index) in normalizedLinks" :key="`${link.label}-${index}`">
              <span v-if="index > 0">/</span>
              <a v-if="link.href" :href="link.href" class="app-header__link">
                {{ link.label }}
              </a>
              <span v-else>{{ link.label }}</span>
            </template>
          </div>
        </div>
      </div>

    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { Separator } from "@/components/ui/separator";
import SidebarTrigger from "@/components/ui/sidebar/SidebarTrigger.vue";

const props = defineProps<{
  titulo: {
    text: string;
    links?: Array<string | { label: string; href?: string }>;
  };
}>();

const normalizedLinks = computed(() =>
  (props.titulo.links ?? []).map((link) =>
    typeof link === "string" ? { label: link } : link,
  ),
);
</script>

<style scoped>
.app-header {
  position: sticky;
  top: 0;
  z-index: 20;
  border-bottom: 0.5px solid var(--color-border-tertiary);
  background: var(--color-background-primary);
}

.app-header__inner {
  display: flex;
  min-height: 60px;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 0 16px;
}

.app-header__title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 13px;
  font-weight: 500;
  color: var(--color-text-primary);
}

.app-header__links {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
  font-size: 12px;
  font-weight: 400;
  color: var(--color-text-secondary);
}

.app-header__link {
  color: inherit;
}
</style>
