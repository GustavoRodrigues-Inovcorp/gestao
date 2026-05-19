<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Badge } from '@/Components/ui/badge'
import { Link } from '@inertiajs/vue3'
import {
    Users, Truck, FileText, ShoppingCart,
    Wrench, AlertCircle, TrendingUp, Clock
} from 'lucide-vue-next'

const props = defineProps({
    stats: Object,
    propostas_recentes: Array,
    ordens_abertas: Array,
})

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const allCards = [
    { key: 'clientes',           label: 'Clientes',          icon: Users,        color: 'text-blue-500',   bg: 'bg-blue-500/10' },
    { key: 'fornecedores',       label: 'Fornecedores',       icon: Truck,        color: 'text-purple-500', bg: 'bg-purple-500/10' },
    { key: 'propostas',          label: 'Propostas',          icon: FileText,     color: 'text-amber-500',  bg: 'bg-amber-500/10' },
    { key: 'encomendas',         label: 'Encomendas',         icon: ShoppingCart, color: 'text-green-500',  bg: 'bg-green-500/10' },
    { key: 'ordens',             label: 'Ordens Abertas',     icon: Wrench,       color: 'text-orange-500', bg: 'bg-orange-500/10' },
    { key: 'faturas_pendentes',  label: 'Faturas Pendentes',  icon: AlertCircle,  color: 'text-red-500',    bg: 'bg-red-500/10' },
]

// Só mostra cards que existem nas stats passadas pelo controller
const statCards = allCards.filter(c => props.stats[c.key] !== undefined)

const estadoBadge = {
    rascunho: 'secondary',
    fechado:  'default',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Dashboard</h1>
        </template>

        <div class="space-y-6">

            <!-- Stats -->
            <div v-if="statCards.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.key"
                    class="rounded-lg border bg-card p-4 space-y-3"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">{{ stat.label }}</span>
                        <div :class="['w-7 h-7 rounded-md flex items-center justify-center', stat.bg]">
                            <component :is="stat.icon" :class="['w-3.5 h-3.5', stat.color]" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold">{{ props.stats[stat.key] }}</p>
                </div>
            </div>

            <!-- Tabelas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                <!-- Propostas Recentes -->
                <div v-if="propostas_recentes.length > 0" class="rounded-lg border bg-card">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <div class="flex items-center gap-2">
                            <TrendingUp class="w-4 h-4 text-muted-foreground" />
                            <h2 class="text-sm font-semibold">Propostas Recentes</h2>
                        </div>
                        <Link href="/propostas" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="p in propostas_recentes"
                            :key="p.numero"
                            class="flex items-center justify-between px-4 py-3 hover:bg-muted/30 transition-colors"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ p.cliente }}</p>
                                <p class="text-xs text-muted-foreground">Nº {{ String(p.numero).padStart(5, '0') }} · {{ p.data }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-sm font-medium">{{ formatPrice(p.valor_total) }}</span>
                                <Badge :variant="estadoBadge[p.estado]" class="text-xs">{{ p.estado }}</Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ordens Abertas -->
                <div v-if="ordens_abertas.length > 0" class="rounded-lg border bg-card">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <div class="flex items-center gap-2">
                            <Clock class="w-4 h-4 text-muted-foreground" />
                            <h2 class="text-sm font-semibold">Ordens de Trabalho Abertas</h2>
                        </div>
                        <Link href="/ordens-trabalho" class="text-xs text-muted-foreground hover:text-foreground transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                    <div class="divide-y">
                        <div
                            v-for="o in ordens_abertas"
                            :key="o.numero"
                            class="flex items-center justify-between px-4 py-3 hover:bg-muted/30 transition-colors"
                        >
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ o.cliente }}</p>
                                <p class="text-xs text-muted-foreground">Nº {{ String(o.numero).padStart(5, '0') }} · {{ o.data }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p v-if="o.prevista" class="text-xs text-muted-foreground">Prevista: {{ o.prevista }}</p>
                                <Badge variant="outline" class="text-xs">Aberta</Badge>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sem acesso a nada -->
            <div v-if="statCards.length === 0 && propostas_recentes.length === 0 && ordens_abertas.length === 0"
                class="rounded-lg border bg-card p-12 text-center text-muted-foreground">
                <p class="text-sm">Sem dados disponíveis para o teu perfil.</p>
            </div>

        </div>
    </AppLayout>
</template>