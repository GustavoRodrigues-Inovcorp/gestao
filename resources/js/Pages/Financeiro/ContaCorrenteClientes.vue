<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Plus, Trash2, Search, TrendingUp, TrendingDown } from 'lucide-vue-next'

const props = defineProps({
    movimentos: Array,
    clientes: Array,
})

const { can } = useMenuPermissions('financeiro')

const showCreate = ref(false)
const search = ref('')
const filtroCliente = ref('')

const filtered = computed(() => {
    let result = props.movimentos
    if (filtroCliente.value && filtroCliente.value !== 'todos') {
        result = result.filter(m => String(m.entidade_id) === filtroCliente.value)
    }
    if (search.value) {
        const q = search.value.toLowerCase()
        result = result.filter(m =>
            m.descricao?.toLowerCase().includes(q) ||
            m.entidade?.toLowerCase().includes(q) ||
            m.referencia?.toLowerCase().includes(q)
        )
    }
    return result
})

const saldoTotal = computed(() => {
    if (!filtroCliente.value || filtroCliente.value === 'todos') return null
    const movs = filtered.value
    if (!movs.length) return 0
    return movs[0].saldo
})

function defaultForm() {
    return {
        entidade_id: '',
        data_movimento: new Date().toISOString().split('T')[0], // renomeado de 'data'
        descricao: '',
        debito: 0,
        credito: 0,
        tipo: 'outro',
        referencia: '',
    }
}

const createForm = useForm(defaultForm())

function submitCreate() {
    if (!can('create')) return
    createForm.post('/financeiro/conta-corrente-clientes', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function destroy(movimento) {
    if (!can('delete')) return
    if (confirm('Eliminar este movimento?')) {
        useForm({}).delete(`/financeiro/conta-corrente-clientes/${movimento.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const tipoLabel = {
    fatura: 'Fatura',
    pagamento: 'Pagamento',
    nota_credito: 'Nota Crédito',
    outro: 'Outro',
}

const tipoBadge = {
    fatura: 'default',
    pagamento: 'secondary',
    nota_credito: 'outline',
    outro: 'outline',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Financeiro — Conta Corrente Clientes</h1>
        </template>

        <div class="space-y-4">
            <!-- Filtros + Ações -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="relative max-w-xs w-full">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Pesquisar..." class="pl-9" />
                    </div>
                    <div class="w-52">
                        <Select v-model="filtroCliente">
                            <SelectTrigger class="h-9">
                                <SelectValue placeholder="Filtrar por cliente..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">Todos os clientes</SelectItem>
                                <SelectItem v-for="c in clientes" :key="c.id" :value="String(c.id)">
                                    {{ c.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Saldo do cliente filtrado -->
                    <div v-if="filtroCliente && saldoTotal !== null" class="flex items-center gap-2 px-4 py-2 rounded-lg border bg-card">
                        <component :is="saldoTotal >= 0 ? TrendingUp : TrendingDown"
                            :class="['w-4 h-4', saldoTotal >= 0 ? 'text-green-600' : 'text-destructive']" />
                        <span class="text-sm font-medium">Saldo:</span>
                        <span :class="['font-bold', saldoTotal >= 0 ? 'text-green-600' : 'text-destructive']">
                            {{ formatPrice(saldoTotal) }}
                        </span>
                    </div>

                    <Dialog v-if="can('create')" v-model:open="showCreate">
                        <DialogTrigger as-child>
                            <Button size="sm" class="gap-2">
                                <Plus class="w-4 h-4" /> Novo Movimento
                            </Button>
                        </DialogTrigger>
                        <DialogContent class="max-w-md">
                            <DialogHeader>
                                <DialogTitle>Novo Movimento</DialogTitle>
                            </DialogHeader>
                            <div class="space-y-3 py-2">
                                <div class="space-y-1">
                                    <Label>Cliente *</Label>
                                    <Select v-model="createForm.entidade_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecionar cliente..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="c in clientes" :key="c.id" :value="String(c.id)">
                                                {{ c.nome }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="createForm.errors.entidade_id" class="text-xs text-destructive">{{ createForm.errors.entidade_id }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>Data *</Label>
                                    <input
                                        v-model="createForm.data_movimento"
                                        type="date"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                        @mousedown.stop
                                    />
                                </div>
                                <div class="space-y-1">
                                    <Label>Descrição *</Label>
                                    <Input v-model="createForm.descricao" />
                                    <p v-if="createForm.errors.descricao" class="text-xs text-destructive">{{ createForm.errors.descricao }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>Tipo *</Label>
                                    <Select v-model="createForm.tipo">
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="fatura">Fatura</SelectItem>
                                            <SelectItem value="pagamento">Pagamento</SelectItem>
                                            <SelectItem value="nota_credito">Nota de Crédito</SelectItem>
                                            <SelectItem value="outro">Outro</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div class="space-y-1">
                                        <Label>Débito</Label>
                                        <Input v-model="createForm.debito" type="number" min="0" step="0.01" />
                                    </div>
                                    <div class="space-y-1">
                                        <Label>Crédito</Label>
                                        <Input v-model="createForm.credito" type="number" min="0" step="0.01" />
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <Label>Referência</Label>
                                    <Input v-model="createForm.referencia" placeholder="ex: FAT-001" />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Tabela -->
            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Descrição</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Referência</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Débito</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Crédito</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Saldo</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum movimento encontrado.
                            </td>
                        </tr>
                        <tr v-for="m in filtered" :key="m.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ m.data }}</td>
                            <td class="px-4 py-3 font-medium">{{ m.entidade }}</td>
                            <td class="px-4 py-3">{{ m.descricao }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="tipoBadge[m.tipo]">{{ tipoLabel[m.tipo] }}</Badge>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ m.referencia ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <span v-if="m.debito > 0" class="text-destructive font-medium">
                                    {{ formatPrice(m.debito) }}
                                </span>
                                <span v-else class="text-muted-foreground">—</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span v-if="m.credito > 0" class="text-green-600 font-medium">
                                    {{ formatPrice(m.credito) }}
                                </span>
                                <span v-else class="text-muted-foreground">—</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium" :class="m.saldo >= 0 ? 'text-green-600' : 'text-destructive'">
                                {{ formatPrice(m.saldo) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(m)">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>