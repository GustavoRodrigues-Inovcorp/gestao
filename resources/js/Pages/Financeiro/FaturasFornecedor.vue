<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
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
import { Plus, Pencil, Trash2, FileDown, Send, FileText } from 'lucide-vue-next'

const props = defineProps({
    faturas: Array,
    fornecedores: Array,
    encomendas: Array,
    proximoNumero: Number,
})

const showCreate = ref(false)
const showEdit = ref(false)
const showComprovativo = ref(false)
const editing = ref(null)
const faturaParaComprovativo = ref(null)

function defaultForm(overrides = {}) {
    return {
        data_fatura: new Date().toISOString().split('T')[0],
        data_vencimento: null,
        fornecedor_id: null,
        encomenda_fornecedor_id: null,
        valor_total: '',
        documento: null,
        estado: 'pendente',
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())
const comprativoForm = useForm({ comprovativo: null })

// Filtra encomendas pelo fornecedor selecionado
const encomendasFiltradas = computed(() => {
    const fornId = showCreate.value ? createForm.fornecedor_id : editForm.fornecedor_id
    if (!fornId) return props.encomendas
    return props.encomendas.filter(e => e.fornecedor_id === fornId)
})

function openEdit(fatura) {
    editing.value = fatura
    Object.assign(editForm, defaultForm({
        data_fatura: fatura.data_fatura_raw ?? fatura.data_fatura,
        data_vencimento: fatura.data_vencimento_raw ?? fatura.data_vencimento,
        fornecedor_id: fatura.fornecedor_id,
        encomenda_fornecedor_id: fatura.encomenda_fornecedor_id,
        valor_total: fatura.valor_total,
        estado: fatura.estado,
    }))
    showEdit.value = true
}

function openComprovativo(fatura) {
    faturaParaComprovativo.value = fatura
    comprativoForm.reset()
    showComprovativo.value = true
}

function submitCreate() {
    createForm.post('/financeiro/faturas-fornecedor', {
        forceFormData: true,
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    editForm.post(`/financeiro/faturas-fornecedor/${editing.value.id}`, {
        forceFormData: true,
        onSuccess: () => { showEdit.value = false }
    })
}

function submitComprovativo() {
    comprativoForm.post(`/financeiro/faturas-fornecedor/${faturaParaComprovativo.value.id}/comprovativo`, {
        forceFormData: true,
        onSuccess: () => { showComprovativo.value = false }
    })
}

function destroy(fatura) {
    if (confirm(`Eliminar fatura Nº ${fatura.numero}?`)) {
        useForm({}).delete(`/financeiro/faturas-fornecedor/${fatura.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const estadoBadge = { pendente: 'secondary', paga: 'default' }
const estadoLabel = { pendente: 'Pendente', paga: 'Paga' }
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Financeiro — Faturas Fornecedor</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Fatura
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-lg max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Nova Fatura — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <Label>Data Fatura *</Label>
                                    <Input v-model="createForm.data_fatura" type="date" />
                                    <p v-if="createForm.errors.data_fatura" class="text-xs text-destructive">{{ createForm.errors.data_fatura }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>Data Vencimento</Label>
                                    <Input v-model="createForm.data_vencimento" type="date" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label>Fornecedor *</Label>
                                <Select v-model="createForm.fornecedor_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar fornecedor..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="f in fornecedores" :key="f.id" :value="f.id">
                                            {{ f.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="createForm.errors.fornecedor_id" class="text-xs text-destructive">{{ createForm.errors.fornecedor_id }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Encomenda Fornecedor</Label>
                                <Select v-model="createForm.encomenda_fornecedor_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar encomenda..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="e in encomendasFiltradas" :key="e.id" :value="e.id">
                                            Nº {{ String(e.numero).padStart(5, '0') }} — {{ formatPrice(e.valor_total) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1">
                                <Label>Valor Total *</Label>
                                <Input v-model="createForm.valor_total" type="number" min="0" step="0.01" placeholder="0.00" />
                                <p v-if="createForm.errors.valor_total" class="text-xs text-destructive">{{ createForm.errors.valor_total }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Documento (Fatura)</Label>
                                <Input type="file" @change="e => createForm.documento = e.target.files[0]" />
                            </div>
                            <div class="space-y-1">
                                <Label>Estado</Label>
                                <Select v-model="createForm.estado">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pendente">Pendente de Pagamento</SelectItem>
                                        <SelectItem value="paga">Paga</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Tabela -->
            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nº</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fornecedor</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Encomenda</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Documento</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Valor Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="faturas.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma fatura registada.
                            </td>
                        </tr>
                        <tr v-for="f in faturas" :key="f.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-mono">{{ String(f.numero).padStart(5, '0') }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ f.data_fatura }}</td>
                            <td class="px-4 py-3 font-medium">{{ f.fornecedor }}</td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ f.encomenda_numero ? 'Nº ' + String(f.encomenda_numero).padStart(5, '0') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <a v-if="f.documento" :href="f.documento" target="_blank" class="flex items-center gap-1 text-primary hover:underline text-xs">
                                    <FileText class="w-3.5 h-3.5" /> Ver
                                </a>
                                <span v-else class="text-muted-foreground">—</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatPrice(f.valor_total) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="estadoBadge[f.estado]">{{ estadoLabel[f.estado] }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <Button
                                        v-if="f.estado === 'paga'"
                                        size="icon" variant="ghost"
                                        @click="openComprovativo(f)"
                                        title="Enviar Comprovativo"
                                    >
                                        <Send class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="openEdit(f)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(f)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dialog Editar -->
        <Dialog v-model:open="showEdit">
            <DialogContent class="max-w-lg max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Fatura — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Data Fatura *</Label>
                            <Input v-model="editForm.data_fatura" type="date" />
                        </div>
                        <div class="space-y-1">
                            <Label>Data Vencimento</Label>
                            <Input v-model="editForm.data_vencimento" type="date" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Fornecedor *</Label>
                        <Select v-model="editForm.fornecedor_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar fornecedor..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="f in fornecedores" :key="f.id" :value="f.id">
                                    {{ f.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <Label>Encomenda Fornecedor</Label>
                        <Select v-model="editForm.encomenda_fornecedor_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar encomenda..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="e in encomendasFiltradas" :key="e.id" :value="e.id">
                                    Nº {{ String(e.numero).padStart(5, '0') }} — {{ formatPrice(e.valor_total) }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <Label>Valor Total *</Label>
                        <Input v-model="editForm.valor_total" type="number" min="0" step="0.01" />
                    </div>
                    <div class="space-y-1">
                        <Label>Documento (substituir)</Label>
                        <Input type="file" @change="e => editForm.documento = e.target.files[0]" />
                        <p v-if="editing?.documento" class="text-xs text-muted-foreground">
                            Já tem documento anexo.
                            <a :href="editing.documento" target="_blank" class="text-primary hover:underline">Ver atual</a>
                        </p>
                    </div>
                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="editForm.estado">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="pendente">Pendente de Pagamento</SelectItem>
                                <SelectItem value="paga">Paga</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Dialog Comprovativo -->
        <Dialog v-model:open="showComprovativo">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Enviar Comprovativo de Pagamento</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <p class="text-sm text-muted-foreground">
                        Pretende enviar o comprovativo ao Fornecedor
                        <span class="font-medium text-foreground">{{ faturaParaComprovativo?.fornecedor }}</span>
                        referente à fatura
                        <span class="font-medium text-foreground">Nº {{ faturaParaComprovativo?.numero }}</span>?
                    </p>
                    <div class="space-y-1">
                        <Label>Comprovativo de Pagamento *</Label>
                        <Input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="e => comprativoForm.comprovativo = e.target.files[0]" />
                        <p v-if="comprativoForm.errors.comprovativo" class="text-xs text-destructive">{{ comprativoForm.errors.comprovativo }}</p>
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="showComprovativo = false">Cancelar</Button>
                    <Button @click="submitComprovativo" :disabled="comprativoForm.processing" class="gap-2">
                        <Send class="w-4 h-4" /> Enviar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>