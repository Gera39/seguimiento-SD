<script setup lang="ts">
import {
  BadgeCheck,
  Bell,
  ChevronsUpDown,
  LogOut,
} from "lucide-vue-next"

import {
  Avatar,
  AvatarFallback,
  AvatarImage,
} from '@/components/ui/avatar'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar'

import { computed } from "vue"
import { storeToRefs } from "pinia"
import { useAuthStore } from "@/stores/auth"
import { useRouter } from "vue-router"

const { isMobile } = useSidebar()

const auth = useAuthStore()
const router = useRouter()
const { user } = storeToRefs(auth)

const displayName = computed(() => user.value?.name ?? "Invitado")
const displayEmail = computed(() => user.value?.email ?? "Inicia sesión para continuar")
const avatarUrl = computed(() => user.value?.avatarUrl ?? "")
const avatarFallback = computed(() => {
  const name = user.value?.name?.trim()
  if (!name) return "UTH"

  const [first = "", second = ""] = name.split(/\s+/)
  const initials = `${first.charAt(0)}${second.charAt(0)}`.toUpperCase()
  return initials || "UTH"
})
const canLogout = computed(() => Boolean(user.value))

const handleLogout = async () => {
  try {
    if (canLogout.value) {
      await auth.logout()
    }
  } finally {
    router.push("/login")
  }
}
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem>
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <SidebarMenuButton
            size="lg"
            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
          >
            <Avatar class="h-8 w-8 rounded-lg">
              <AvatarImage :src="avatarUrl" :alt="displayName" />
              <AvatarFallback class="rounded-lg">
                {{ avatarFallback }}
              </AvatarFallback>
            </Avatar>

            <div class="grid flex-1 text-left text-sm leading-tight">
              <span class="truncate font-medium">{{ displayName }}</span>
              <span class="truncate text-xs">{{ displayEmail }}</span>
            </div>

            <ChevronsUpDown class="ml-auto size-4" />
          </SidebarMenuButton>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
          :side="isMobile ? 'bottom' : 'right'"
          align="end"
          :side-offset="4"
        >
          <DropdownMenuLabel class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
              <Avatar class="h-8 w-8 rounded-lg">
                <AvatarImage :src="avatarUrl" :alt="displayName" />
                <AvatarFallback class="rounded-lg">{{ avatarFallback }}</AvatarFallback>
              </Avatar>

              <div class="grid flex-1">
                <span class="truncate font-semibold">{{ displayName }}</span>
                <span class="truncate text-xs">{{ displayEmail }}</span>
              </div>
            </div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuGroup>
            <DropdownMenuItem>
              <BadgeCheck />
              Cuenta UTH
            </DropdownMenuItem>
            <DropdownMenuItem>
              <Bell />
              Notificaciones
            </DropdownMenuItem>
          </DropdownMenuGroup>
          <DropdownMenuSeparator />
          <DropdownMenuItem @click="handleLogout" :disabled="!canLogout">
            <LogOut />
            Cerrar sesión
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
