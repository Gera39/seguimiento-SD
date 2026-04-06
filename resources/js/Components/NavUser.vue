<script setup lang="ts">
import { BadgeCheck, Bell, ChevronsUpDown, Palette } from "lucide-vue-next";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from "@/components/ui/sidebar";

const { isMobile } = useSidebar();

const profile = {
  name: "Equipo Academico",
  email: "diseno.demo@uth.local",
  avatarUrl: "",
  fallback: "SD",
};
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="rounded-2xl border border-slate-200 bg-white data-[state=open]:bg-slate-100"
          >
            <Avatar class="h-8 w-8 rounded-lg">
              <AvatarImage :src="profile.avatarUrl" :alt="profile.name" />
              <AvatarFallback class="rounded-lg bg-slate-900 text-white">
                {{ profile.fallback }}
              </AvatarFallback>
            </Avatar>

            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium text-slate-900">{{ profile.name }}</span>
              <span class="truncate text-xs text-slate-500">{{ profile.email }}</span>
            </div>

            <ChevronsUpDown class="ml-auto size-4 text-slate-400" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-2xl"
          :side="isMobile ? 'bottom' : 'right'"
          align="end"
          :side-offset="6"
        >
          <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-3 px-3 py-3 text-sm">
              <Avatar class="h-9 w-9 rounded-xl">
                <AvatarImage :src="profile.avatarUrl" :alt="profile.name" />
                <AvatarFallback class="rounded-xl bg-slate-900 text-white">
                  {{ profile.fallback }}
                </AvatarFallback>
              </Avatar>

              <div class="grid flex-1">
                <span class="truncate font-semibold">{{ profile.name }}</span>
                <span class="truncate text-xs text-slate-500">{{ profile.email }}</span>
              </div>
            </div>
          </DropdownMenuLabel>

          <DropdownMenuSeparator />

          <DropdownMenuGroup>
            <DropdownMenuItem>
              <BadgeCheck />
              Perfil demo
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Bell />
              Notificaciones visuales
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Palette />
              Modo diseno
            </DropdownMenuItem>
          </DropdownMenuGroup>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
