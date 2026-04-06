<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12">
        <div class="flex items-center gap-2 px-4">
            <SidebarTrigger class="-ml-1" />
            <Separator orientation="vertical" class="mr-2 h-4" />
            <Breadcrumb>
                <BreadcrumbList>
                    <BreadcrumbItem class="hidden md:block">
                        <BreadcrumbLink href="#">
                            {{ titulo.text }}
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <BreadcrumbSeparator class="hidden md:block" />

                    <li v-for="(c, i) in titulo.links" :key="c ?? i" class="flex items-center">
                        <BreadcrumbItem>
                            <BreadcrumbPage>
                                <router-link v-if="c" :to="c">{{ c }}</router-link>
                                <span v-else>{{ c }}</span>
                            </BreadcrumbPage>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator class="hidden md:block" />
                    </li>
                </BreadcrumbList>
            </Breadcrumb>
        </div>
    </header>
  
</template>

<script setup lang="ts">
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from "@/components/ui/breadcrumb";
import { Separator } from "@/components/ui/separator";
import SidebarTrigger from "@/components/ui/sidebar/SidebarTrigger.vue";

defineProps<{
    titulo: {
        text: string,
        links?: string[]
    },
}>();
</script>