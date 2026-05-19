<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard, Users, Truck, Contact, FileText, Calendar,
    ShoppingCart, ClipboardList, Wrench, Landmark,
    FolderOpen, Shield, Settings, ChevronDown,
    ChevronRight, Building2, Menu, X
} from 'lucide-vue-next'

const page = usePage()
const empresa = computed(() => page.props.empresa)
const currentUrl = computed(() => page.url)
const permissions = computed(() => page.props.auth?.permissions ?? [])
const collapsed = ref(false)
const logoUrl = computed(() => {
    if (!empresa.value?.logotipo) return ''
    return `/empresa/logotipo?v=${empresa.value?.updated_at ?? ''}`
})

function can(menuKey) {
    return permissions.value.includes(`${menuKey}.read`)
}

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { name: 'Clientes', href: '/clientes', icon: Users, permission: 'clientes' },
    { name: 'Fornecedores', href: '/fornecedores', icon: Truck, permission: 'fornecedores' },
    { name: 'Contactos', href: '/contactos', icon: Contact, permission: 'contactos' },
    { name: 'Propostas', href: '/propostas', icon: FileText, permission: 'propostas' },
    { name: 'Calendário', href: '/calendario', icon: Calendar, permission: 'calendario' },
    { name: 'Encomendas - Clientes', href: '/encomendas/clientes', icon: ShoppingCart, permission: 'encomendas_clientes' },
    { name: 'Encomendas - Fornecedores', href: '/encomendas/fornecedores', icon: ClipboardList, permission: 'encomendas_fornecedores' },
    { name: 'Ordens de Trabalho', href: '/ordens-trabalho', icon: Wrench, permission: 'ordens_trabalho' },
    {
        name: 'Financeiro',
        icon: Landmark,
        permission: 'financeiro',
        children: [
            { name: 'Contas Bancárias', href: '/financeiro/contas-bancarias' },
            { name: 'Conta Corrente Clientes', href: '/financeiro/conta-corrente-clientes' },
            { name: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedor' },
        ]
    },
    { name: 'Arquivo Digital', href: '/arquivo-digital', icon: FolderOpen, permission: 'arquivo_digital' },
    {
        name: 'Gestão de Acessos',
        icon: Shield,
        permission: 'utilizadores',
        children: [
            { name: 'Utilizadores', href: '/acessos/utilizadores' },
            { name: 'Permissões', href: '/acessos/permissoes' },
        ]
    },
    {
        name: 'Configurações',
        icon: Settings,
        permission: 'configuracoes',
        children: [
            { name: 'Países', href: '/configuracoes/paises' },
            { name: 'Contactos - Funções', href: '/configuracoes/funcoes' },
            { name: 'Calendário - Tipos', href: '/configuracoes/calendario-tipos' },
            { name: 'Calendário - Ações', href: '/configuracoes/calendario-acoes' },
            { name: 'Artigos', href: '/configuracoes/artigos' },
            { name: 'Financeiro - IVA', href: '/configuracoes/iva' },
            { name: 'Logs', href: '/configuracoes/logs' },
            { name: 'Empresa', href: '/configuracoes/empresa' },
        ]
    },
]

const visibleNavigation = computed(() => navigation.filter(item => !item.permission || can(item.permission)))

const openMenus = ref(['Financeiro', 'Gestão de Acessos', 'Configurações'])

function toggleMenu(name) {
    if (openMenus.value.includes(name)) {
        openMenus.value = openMenus.value.filter(m => m !== name)
    } else {
        openMenus.value.push(name)
    }
}

function isActive(href) {
    return currentUrl.value.startsWith(href)
}
</script>

<template>
    <aside
        :class="[
            'relative flex h-screen flex-col overflow-hidden border-r border-sidebar-border/80 bg-sidebar/95 shadow-[0_20px_60px_rgba(0,0,0,0.12)] backdrop-blur-xl transition-all duration-300',
            collapsed ? 'w-16' : 'w-64'
        ]"
    >
        <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-gradient-to-b from-sidebar-accent/50 to-transparent"></div>

        <!-- Header -->
        <div class="relative flex h-16 items-center justify-between border-b border-sidebar-border/70 px-4">
            <div class="flex min-w-0 items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary">
                    <img
                        v-if="empresa?.logotipo"
                        :src="logoUrl"
                        alt="Logo"
                        class="w-full h-full object-cover"
                    />
                    <Building2 v-else class="h-4 w-4 text-primary-foreground"/>
                </div>
                <div v-if="!collapsed" class="min-w-0">
                    <div class="text-[10px] uppercase tracking-[0.24em] text-sidebar-foreground/45">Workspace</div>
                    <span class="block truncate text-sm font-semibold text-sidebar-foreground">{{ empresa?.nome ?? 'Base' }}</span>
                </div>
            </div>
            <button
                @click="collapsed = !collapsed"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-sidebar-border/70 bg-sidebar/80 text-sidebar-foreground shadow-sm transition-colors hover:bg-sidebar-accent"
                :class="collapsed ? 'mx-auto' : ''"
            >
                <X v-if="!collapsed" class="h-4 w-4" />
                <Menu v-else class="h-4 w-4" />
            </button>
        </div>

        <!-- Navigation -->
        <nav class="relative flex-1 space-y-0.5 overflow-y-auto px-3 py-4">
            <template v-for="item in visibleNavigation" :key="item.name">

                <!-- Item com filhos -->
                <div v-if="item.children">
                    <button
                        @click="toggleMenu(item.name)"
                        :class="[
                            'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all',
                            'text-sidebar-foreground hover:bg-sidebar-accent/80 hover:text-sidebar-accent-foreground'
                        ]"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed" class="flex-1 text-left truncate">{{ item.name }}</span>
                        <ChevronDown
                            v-if="!collapsed"
                            :class="['h-3.5 w-3.5 transition-transform', openMenus.includes(item.name) ? 'rotate-180' : '']"
                        />
                    </button>

                    <div v-if="!collapsed && openMenus.includes(item.name)" class="ml-4 mt-1 space-y-1 border-l border-sidebar-border/80 pl-3">
                        <Link
                            v-for="child in item.children"
                            :key="child.href"
                            :href="child.href"
                            :class="[
                                'block rounded-lg px-3 py-2 text-sm transition-all',
                                isActive(child.href)
                                    ? 'bg-sidebar-primary text-sidebar-primary-foreground font-medium shadow-sm shadow-black/10'
                                    : 'text-sidebar-foreground hover:bg-sidebar-accent/80 hover:text-sidebar-accent-foreground'
                            ]"
                        >
                            {{ child.name }}
                        </Link>
                    </div>
                </div>

                <!-- Item simples -->
                <Link
                    v-else
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all',
                        isActive(item.href)
                            ? 'bg-sidebar-primary text-sidebar-primary-foreground font-medium shadow-sm shadow-black/10 ring-1 ring-white/10'
                            : 'text-sidebar-foreground hover:bg-sidebar-accent/80 hover:text-sidebar-accent-foreground'
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4 shrink-0" />
                    <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                </Link>

            </template>
        </nav>
    </aside>
</template>