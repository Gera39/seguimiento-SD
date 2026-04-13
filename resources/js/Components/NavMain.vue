<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import type { LucideIcon } from "lucide-vue-next";
import { ChevronRight } from "lucide-vue-next";
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/ui/collapsible";
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from "@/components/ui/sidebar";

defineProps<{
  items: {
    title: string;
    icon?: LucideIcon;
    items?: {
      title: string;
      url: string;
    }[];
  }[];
}>();

const page = usePage();

const isActive = (url: string) => {
  if (url === "/dashboard") {
    return page.url === url;
  }

  return page.url === url || page.url.startsWith(`${url}/`);
};
</script>

<template>
  <SidebarGroup class="px-2 py-3">
    <SidebarGroupLabel class="px-2 text-[11px] uppercase tracking-[0.22em] text-slate-400">
      Navegacion
    </SidebarGroupLabel>

    <SidebarMenu>
      <Collapsible
        v-for="item in items"
        :key="item.title"
        as-child
        default-open
        class="group/collapsible"
      >
        <SidebarMenuItem>
          <CollapsibleTrigger as-child>
            <SidebarMenuButton class="rounded-xl text-slate-700 hover:bg-slate-100">
              <component :is="item.icon" v-if="item.icon" class="size-4" />
              <span>{{ item.title }}</span>
              <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
            </SidebarMenuButton>
          </CollapsibleTrigger>

          <CollapsibleContent>
            <SidebarMenuSub>
              <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                <SidebarMenuSubButton as-child>
                  <Link
                    :href="subItem.url"
                    class="text-slate-600 transition-colors hover:text-slate-950"
                    :class="isActive(subItem.url) ? 'font-semibold text-slate-950' : ''"
                  >
                    <span>{{ subItem.title }}</span>
                  </Link>
                </SidebarMenuSubButton>
              </SidebarMenuSubItem>
            </SidebarMenuSub>
          </CollapsibleContent>
        </SidebarMenuItem>
      </Collapsible>
    </SidebarMenu>
  </SidebarGroup>
</template>
