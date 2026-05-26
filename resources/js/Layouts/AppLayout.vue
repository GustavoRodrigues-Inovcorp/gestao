<script setup>
import { computed, watchEffect, onMounted, nextTick } from 'vue'
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import { AppSidebar } from '@/Components/ui/sidebar'
import { useDarkMode } from '@/composables/useDarkMode'
import { Sun, Moon, User, LogOut, ChevronDown, Plus, Building2, Check, Landmark } from 'lucide-vue-next'
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem,
    DropdownMenuSeparator, DropdownMenuTrigger, DropdownMenuLabel,
} from '@/Components/ui/dropdown-menu'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const { isDark } = useDarkMode()
const tenants = computed(() => page.props.tenants ?? [])
const tenantAtual = computed(() => page.props.tenant_atual)
const workspaceBrand = computed(() => page.props.empresa?.nome ?? tenantAtual.value?.nome ?? 'Workspace')

function switchTenant(tenantId) {
    router.post(`/tenants/${tenantId}/switch`)
}

async function updateBranding() {
    // Aguarda que outras atualizações do DOM/Inertia terminem
    await nextTick()

    // Atualiza title: substitui o último segmento após ' - ' pelo workspaceBrand
    const current = document.title || ''
    if (current.includes(' - ')) {
        const parts = current.split(' - ')
        parts[parts.length - 1] = workspaceBrand.value
        document.title = parts.join(' - ')
    } else {
        document.title = workspaceBrand.value
    }

    // Atualiza favicon (mantém comportamento anterior)
    const empresa = page.props.empresa
    const logoUrl = empresa?.logotipo ? `/empresa/logotipo?v=${empresa.updated_at ?? ''}` : '/favicon.ico'
    let link = document.querySelector("link[rel*='icon']")
    if (!link) {
        link = document.createElement('link')
        link.setAttribute('rel', 'icon')
        document.head.appendChild(link)
    }
    if (link.getAttribute('href') !== logoUrl) {
        link.setAttribute('href', logoUrl)
    }
}

onMounted(() => {
    updateBranding()
})

watchEffect(() => {
    // watchEffect irá reagir a todas as dependências lidas dentro de updateBranding
    updateBranding()
})
</script>

<template>
    <div class="flex h-screen bg-background overflow-hidden">
        <Head>
            <meta name="workspace-name" :content="workspaceBrand" />
        </Head>
        <AppSidebar />

        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="h-16 border-b border-border bg-background flex items-center justify-between px-6 shrink-0">
                <div class="flex items-center gap-2">
                    <slot name="header">
                        <h1 class="text-sm font-semibold text-foreground">Dashboard</h1>
                    </slot>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Seletor de Tenant — mostra nome do tenant ativo -->
                    <DropdownMenu>
                        <DropdownMenuTrigger class="flex items-center gap-2 px-3 py-1.5 rounded-md border hover:bg-accent text-sm transition-colors">
                            <Building2 class="w-4 h-4 text-muted-foreground" />
                            <span class="font-medium max-w-[140px] truncate">{{ workspaceBrand }}</span>
                            <ChevronDown class="w-3.5 h-3.5 text-muted-foreground" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel class="text-xs text-muted-foreground font-normal">
                                Workspaces
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                v-for="tenant in tenants"
                                :key="tenant.id"
                                @click="!tenant.ativo && switchTenant(tenant.id)"
                                :class="tenant.ativo ? 'cursor-default opacity-60' : 'cursor-pointer'"
                            >
                                <div class="flex items-center gap-2 w-full">
                                    <Building2 class="w-4 h-4 shrink-0" />
                                    <span class="flex-1 truncate text-sm">{{ tenant.nome }}</span>
                                    <Check v-if="tenant.ativo" class="w-3.5 h-3.5 text-primary shrink-0" />
                                </div>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/tenants" class="flex items-center gap-2 cursor-pointer text-sm">
                                    Gerir Workspaces
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Dark Mode -->
                    <button @click="isDark = !isDark" class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors">
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>

                    <!-- User Menu -->
                    <DropdownMenu>
                        <DropdownMenuTrigger class="flex items-center gap-2 px-2 py-1.5 rounded-md hover:bg-accent text-sm transition-colors">
                            <div class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-xs font-semibold shrink-0">
                                {{ user?.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <span class="text-foreground font-medium hidden md:block max-w-[120px] truncate">{{ user?.name }}</span>
                            <ChevronDown class="w-3.5 h-3.5 text-muted-foreground" />
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuLabel class="font-normal">
                                <p class="text-xs text-muted-foreground truncate">{{ user?.email }}</p>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/profile" class="flex items-center gap-2 cursor-pointer">
                                    <User class="w-4 h-4" />
                                    Perfil
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link href="/billing" class="flex items-center gap-2 cursor-pointer">
                                    <Landmark class="w-4 h-4" />
                                    Planos e Subscrição
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link href="/logout" method="post" as="button" class="flex items-center gap-2 w-full cursor-pointer text-destructive hover:text-destructive">
                                    <LogOut class="w-4 h-4" />
                                    Terminar Sessão
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