<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Textarea } from '@/Components/ui/textarea'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog'
import {
    Clock3,
    Crown,
    ShieldAlert,
    Sparkles,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next'

const props = defineProps({
    planos: { type: Array, default: () => [] },
    subscricao: { type: Object, default: null },
    limites: { type: Object, default: () => ({}) },
})

const cicloSelecionado = ref(props.subscricao?.ciclo ?? 'mensal')
const showCancelar = ref(false)

const upgradeForm = useForm({
    plano_id: null,
    ciclo: cicloSelecionado.value,
})

const cancelarForm = useForm({
    motivo: '',
})

function money(value) {
    return Number(value || 0).toLocaleString('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    })
}

function planPrice(plano, ciclo = cicloSelecionado.value) {
    return ciclo === 'anual' ? Number(plano.preco_anual || 0) : Number(plano.preco_mensal || 0)
}

const planoAtual = computed(() => props.subscricao?.plano ?? null)
const limitesVisiveis = computed(() => props.limites || {})

function isCurrentPlan(plano) {
    return planoAtual.value?.id === plano.id
}

function isPendingPlan(plano) {
    return props.subscricao?.plano_pendente?.id === plano.id
}

function isUpgrade(plano) {
    if (!planoAtual.value) return true
    return planPrice(plano) >= planPrice(planoAtual.value)
}

function limiteLabel(item) {
    return item?.limite === -1 ? 'Ilimitado' : String(item?.limite ?? '-')
}

function usagePercent(item) {
    if (!item || item.limite === -1 || !item.limite) return 0
    return Math.min(100, Math.round((Number(item.atual || 0) / Number(item.limite)) * 100))
}

function submitPlano(plano) {
    if (isCurrentPlan(plano)) return

    upgradeForm.plano_id = plano.id
    upgradeForm.ciclo = cicloSelecionado.value
    upgradeForm.post('/upgrade')
}

function iniciarTrial() {
    useForm({}).post('/trial')
}

function cancelarSubscricao() {
    cancelarForm.post('/cancelar', {
        onSuccess: () => {
            showCancelar.value = false
            cancelarForm.reset()
        },
    })
}

const trialAExpirar = computed(() => {
    if (!props.subscricao) return false
    return props.subscricao.estado === 'trial' && Number(props.subscricao.dias_trial || 0) <= 3
})

const estadoLabel = {
    trial: 'Trial',
    ativa: 'Ativa',
    cancelada: 'Cancelada',
    expirada: 'Expirada',
    pendente: 'Pendente',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Planos e Subscrição</h1>
        </template>

        <div class="space-y-6">
            <div class="rounded-xl border bg-card p-4 md:p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-2">
                        <p class="text-xs uppercase tracking-[0.18em] text-muted-foreground">Subscrição atual</p>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-semibold">
                                {{ planoAtual?.nome ?? 'Sem plano ativo' }}
                            </h2>
                            <Badge v-if="subscricao" variant="outline">
                                {{ estadoLabel[subscricao.estado] ?? subscricao.estado }}
                            </Badge>
                        </div>

                        <p v-if="subscricao" class="text-sm text-muted-foreground">
                            Ciclo {{ subscricao.ciclo }} ·
                            {{ money(subscricao.preco) }}
                            <span v-if="subscricao.proximo_ciclo"> · Renova em {{ subscricao.proximo_ciclo }}</span>
                        </p>
                        <p v-else class="text-sm text-muted-foreground">
                            Sem subscrição ativa. Podes iniciar trial ou escolher um plano.
                        </p>

                        <p v-if="subscricao?.cancelamento_agendado" class="text-sm text-amber-600 flex items-center gap-2">
                            <Clock3 class="w-4 h-4" />
                            Cancelamento agendado para o fim do ciclo.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            v-if="!subscricao"
                            size="sm"
                            class="gap-2"
                            @click="iniciarTrial"
                        >
                            <Sparkles class="w-4 h-4" />
                            Iniciar Trial
                        </Button>

                        <Dialog v-if="subscricao && !subscricao.cancelamento_agendado" v-model:open="showCancelar">
                            <DialogTrigger as-child>
                                <Button size="sm" variant="outline" class="gap-2">
                                    <ShieldAlert class="w-4 h-4" />
                                    Cancelar no fim do ciclo
                                </Button>
                            </DialogTrigger>
                            <DialogContent class="max-w-lg">
                                <DialogHeader>
                                    <DialogTitle>Cancelar subscrição</DialogTitle>
                                    <DialogDescription>
                                        O acesso premium fica ativo ate ao fim do ciclo atual.
                                    </DialogDescription>
                                </DialogHeader>

                                <div class="space-y-2 py-2">
                                    <p class="text-sm text-muted-foreground">Motivo (opcional)</p>
                                    <Textarea
                                        v-model="cancelarForm.motivo"
                                        rows="4"
                                        placeholder="Ajuda-nos a melhorar."
                                    />
                                    <p v-if="cancelarForm.errors.motivo" class="text-xs text-destructive">
                                        {{ cancelarForm.errors.motivo }}
                                    </p>
                                </div>

                                <DialogFooter>
                                    <Button variant="outline" @click="showCancelar = false">Fechar</Button>
                                    <Button
                                        variant="destructive"
                                        :disabled="cancelarForm.processing"
                                        @click="cancelarSubscricao"
                                    >
                                        Confirmar cancelamento
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div
                    v-if="trialAExpirar"
                    class="mt-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                >
                    O teu trial termina em {{ subscricao.dias_trial }} dia(s). Escolhe um plano para manteres o acesso sem interrupcoes.
                </div>

                <div
                    v-if="subscricao?.plano_pendente"
                    class="mt-4 rounded-lg border border-blue-300 bg-blue-50 px-4 py-3 text-sm text-blue-900"
                >
                    Downgrade agendado para o plano {{ subscricao.plano_pendente.nome }} no proximo ciclo.
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 md:p-5 space-y-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <h3 class="text-sm font-semibold">Planos disponíveis</h3>
                    <div class="inline-flex rounded-lg border p-1 bg-muted/40">
                        <button
                            type="button"
                            class="px-3 py-1.5 text-sm rounded-md transition-colors"
                            :class="cicloSelecionado === 'mensal' ? 'bg-background shadow text-foreground' : 'text-muted-foreground'"
                            @click="cicloSelecionado = 'mensal'"
                        >
                            Mensal
                        </button>
                        <button
                            type="button"
                            class="px-3 py-1.5 text-sm rounded-md transition-colors"
                            :class="cicloSelecionado === 'anual' ? 'bg-background shadow text-foreground' : 'text-muted-foreground'"
                            @click="cicloSelecionado = 'anual'"
                        >
                            Anual
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div
                        v-for="plano in planos"
                        :key="plano.id"
                        class="rounded-xl border p-4 flex flex-col gap-4"
                        :class="isCurrentPlan(plano) ? 'border-primary/60 bg-primary/5' : ''"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="font-semibold">{{ plano.nome }}</h4>
                            <Badge v-if="isCurrentPlan(plano)">Atual</Badge>
                            <Badge v-else-if="isPendingPlan(plano)" variant="secondary">Pendente</Badge>
                        </div>

                        <p class="text-3xl font-bold leading-none">
                            {{ money(planPrice(plano)) }}
                            <span class="text-sm font-normal text-muted-foreground">/{{ cicloSelecionado === 'anual' ? 'ano' : 'mês' }}</span>
                        </p>

                        <p class="text-sm text-muted-foreground min-h-[40px]">{{ plano.descricao }}</p>

                        <ul class="space-y-1.5 text-sm">
                            <li class="flex items-center justify-between">
                                <span>Utilizadores</span>
                                <span class="font-medium">{{ plano.max_utilizadores === -1 ? 'Ilimitado' : plano.max_utilizadores }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>Clientes</span>
                                <span class="font-medium">{{ plano.max_clientes === -1 ? 'Ilimitado' : plano.max_clientes }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>Documentos</span>
                                <span class="font-medium">{{ plano.max_documentos === -1 ? 'Ilimitado' : plano.max_documentos }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span>Trial</span>
                                <span class="font-medium">{{ plano.trial_dias || 0 }} dias</span>
                            </li>
                        </ul>

                        <div class="pt-1">
                            <Button
                                class="w-full gap-2"
                                :variant="isCurrentPlan(plano) ? 'secondary' : 'default'"
                                :disabled="upgradeForm.processing || isCurrentPlan(plano)"
                                @click="submitPlano(plano)"
                            >
                                <TrendingUp v-if="!isCurrentPlan(plano) && isUpgrade(plano)" class="w-4 h-4" />
                                <TrendingDown v-else-if="!isCurrentPlan(plano)" class="w-4 h-4" />
                                <Crown v-else class="w-4 h-4" />
                                <span v-if="isCurrentPlan(plano)">Plano atual</span>
                                <span v-else-if="isUpgrade(plano)">Upgrade</span>
                                <span v-else>Agendar downgrade</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card p-4 md:p-5 space-y-4">
                <h3 class="text-sm font-semibold">Limites e utilização</h3>
                <div v-if="Object.keys(limitesVisiveis || {}).length === 0" class="text-sm text-muted-foreground">
                    Sem dados de limites para o plano atual.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="(item, key) in limitesVisiveis"
                        :key="key"
                        class="space-y-1"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span class="capitalize">{{ key }}</span>
                            <span>
                                {{ item?.atual ?? 0 }} / {{ limiteLabel(item) }}
                            </span>
                        </div>
                        <div class="h-2 rounded bg-muted overflow-hidden">
                            <div
                                class="h-full transition-all"
                                :class="item?.ok ? 'bg-emerald-500' : 'bg-red-500'"
                                :style="{ width: `${usagePercent(item)}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
