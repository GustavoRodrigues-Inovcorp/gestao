<script setup>
import { computed } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import { AppSidebar } from '@/Components/ui/sidebar'
import { useDarkMode } from '@/composables/useDarkMode'
import { Bell, User, LogOut, ChevronDown, Sun, Moon } from 'lucide-vue-next'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const { isDark } = useDarkMode()
</script>

<template>
    <div class="flex h-screen bg-background overflow-hidden">
        <AppSidebar />

        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="h-16 border-b border-border bg-background flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-2">
                    <slot name="header">
                        <h1 class="text-sm font-semibold text-foreground">Dashboard</h1>
                    </slot>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Dark Mode -->
                    <button
                        @click="isDark = !isDark"
                        class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground"
                    >
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>

                    <!-- Notificações -->
                    <button class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground relative">
                        <Bell class="w-4 h-4" />
                    </button>

                    <!-- User Menu -->
                    <DropdownMenu>
                        <DropdownMenuTrigger class="flex items-center gap-2 px-3 py-1.5 rounded-md hover:bg-accent text-sm">
                            <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-xs font-medium">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <span class="text-foreground font-medium hidden md:block">{{ user?.name }}</span>
                            <ChevronDown class="w-3.5 h-3.5 text-muted-foreground" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem as-child>
                                <Link href="/profile" class="flex items-center gap-2 cursor-pointer">
                                    <User class="w-4 h-4" />
                                    Perfil
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" as="button" class="flex items-center gap-2 w-full cursor-pointer text-destructive">
                                    <LogOut class="w-4 h-4" />
                                    Sair
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>