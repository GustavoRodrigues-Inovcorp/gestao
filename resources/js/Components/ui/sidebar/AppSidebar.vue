<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
    Users, Truck, Contact, FileText, Calendar,
    ShoppingCart, ClipboardList, Wrench, Landmark,
    FolderOpen, Shield, Settings, ChevronDown,
    ChevronRight, Building2, Menu, X
} from 'lucide-vue-next'

const page = usePage()
const currentUrl = computed(() => page.url)
const collapsed = ref(false)

const navigation = [
    { name: 'Clientes', href: '/clientes', icon: Users },
    { name: 'Fornecedores', href: '/fornecedores', icon: Truck },
    { name: 'Contactos', href: '/contactos', icon: Contact },
    { name: 'Propostas', href: '/propostas', icon: FileText },
    { name: 'Calendário', href: '/calendario', icon: Calendar },
    { name: 'Encomendas - Clientes', href: '/encomendas/clientes', icon: ShoppingCart },
    { name: 'Encomendas - Fornecedores', href: '/encomendas/fornecedores', icon: ClipboardList },
    { name: 'Ordens de Trabalho', href: '/ordens-trabalho', icon: Wrench },
    {
        name: 'Financeiro',
        icon: Landmark,
        children: [
            { name: 'Contas Bancárias', href: '/financeiro/contas-bancarias' },
            { name: 'Conta Corrente Clientes', href: '/financeiro/conta-corrente-clientes' },
            { name: 'Faturas Fornecedores', href: '/financeiro/faturas-fornecedor' },
        ]
    },
    { name: 'Arquivo Digital', href: '/arquivo-digital', icon: FolderOpen },
    {
        name: 'Gestão de Acessos',
        icon: Shield,
        children: [
            { name: 'Utilizadores', href: '/acessos/utilizadores' },
            { name: 'Permissões', href: '/acessos/permissoes' },
        ]
    },
    {
        name: 'Configurações',
        icon: Settings,
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
            'flex flex-col h-screen bg-sidebar border-r border-sidebar-border transition-all duration-300',
            collapsed ? 'w-16' : 'w-64'
        ]"
    >
        <!-- Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-sidebar-border">
            <div v-if="!collapsed" class="flex items-center gap-2 min-w-0">
                <div class="w-7 h-7 rounded-md bg-primary flex items-center justify-center shrink-0">
                    <Building2 class="w-4 h-4 text-primary-foreground" />
                </div>
                <span class="font-semibold text-sidebar-foreground text-sm truncate">Gestão</span>
            </div>

            <button
                @click="collapsed = !collapsed"
                class="p-1.5 rounded-md hover:bg-sidebar-accent text-sidebar-foreground shrink-0"
                :class="collapsed ? 'mx-auto' : ''"
            >
                <X v-if="!collapsed" class="w-4 h-4" />
                <Menu v-else class="w-4 h-4" />
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-0.5">
            <template v-for="item in navigation" :key="item.name">

                <!-- Item com filhos -->
                <div v-if="item.children">
                    <button
                        @click="toggleMenu(item.name)"
                        :class="[
                            'w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                            'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
                        ]"
                    >
                        <component :is="item.icon" class="w-4 h-4 shrink-0" />
                        <span v-if="!collapsed" class="flex-1 text-left truncate">{{ item.name }}</span>
                        <ChevronDown
                            v-if="!collapsed"
                            :class="['w-3.5 h-3.5 transition-transform', openMenus.includes(item.name) ? 'rotate-180' : '']"
                        />
                    </button>

                    <div v-if="!collapsed && openMenus.includes(item.name)" class="ml-4 mt-0.5 space-y-0.5 border-l border-sidebar-border pl-3">
                        <Link
                            v-for="child in item.children"
                            :key="child.href"
                            :href="child.href"
                            :class="[
                                'block px-3 py-1.5 rounded-md text-sm transition-colors',
                                isActive(child.href)
                                    ? 'bg-sidebar-primary text-sidebar-primary-foreground font-medium'
                                    : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
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
                        'flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors',
                        isActive(item.href)
                            ? 'bg-sidebar-primary text-sidebar-primary-foreground font-medium'
                            : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" />
                    <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                </Link>

            </template>
        </nav>
    </aside>
</template>