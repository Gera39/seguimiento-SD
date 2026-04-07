<template>
  <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/85 backdrop-blur">
    <div class="flex min-h-16 items-center justify-between gap-4 px-4">
      <div class="flex min-w-0 items-center gap-3">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="h-5" />

        <div class="min-w-0">
          <p class="truncate text-base font-semibold text-slate-900">{{ titulo.text }}</p>

          <div v-if="normalizedLinks.length" class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
            <template v-for="(link, index) in normalizedLinks" :key="`${link.label}-${index}`">
              <span v-if="index > 0">/</span>
              <a v-if="link.href" :href="link.href" class="hover:text-slate-900">
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
