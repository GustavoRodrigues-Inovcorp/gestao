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
    <div class="relative flex h-screen overflow-hidden bg-background text-foreground">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.08),transparent_32%),radial-gradient(circle_at_bottom_right,rgba(15,23,42,0.06),transparent_28%)] dark:bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.12),transparent_28%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.03),transparent_24%)]"></div>

        <AppSidebar />

        <div class="relative z-10 flex min-w-0 flex-1 flex-col overflow-hidden">
            <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between border-b border-border/60 bg-background/80 px-4 shadow-sm backdrop-blur-xl sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <slot name="header">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] uppercase tracking-[0.22em] text-muted-foreground">Workspace</span>
                            <h1 class="text-sm font-semibold text-foreground">Dashboard</h1>
                        </div>
                    </slot>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="isDark = !isDark"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-border/70 bg-background/70 text-muted-foreground shadow-sm transition-colors hover:bg-accent hover:text-foreground"
                    >
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>

                    <DropdownMenu>
                        <DropdownMenuTrigger class="flex items-center gap-2 rounded-full border border-border/70 bg-background/70 px-2.5 py-1.5 text-sm shadow-sm transition-colors hover:bg-accent/80">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-semibold text-primary-foreground shadow-sm">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <span class="hidden max-w-40 truncate font-medium text-foreground md:block">{{ user?.name }}</span>
                            <ChevronDown class="h-3.5 w-3.5 text-muted-foreground" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-52 rounded-xl border border-border/70 bg-popover p-1 shadow-xl backdrop-blur-xl">
                            <DropdownMenuItem as-child>
                                <Link href="/profile" class="flex cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2">
                                    <User class="h-4 w-4" />
                                    Perfil
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" as="button" class="flex w-full cursor-pointer items-center gap-2 rounded-lg px-2.5 py-2 text-destructive">
                                    <LogOut class="h-4 w-4" />
                                    Sair
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto px-4 py-5 sm:px-6 lg:px-8">
                <div class="mx-auto flex w-full max-w-[1600px] flex-col gap-6">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>