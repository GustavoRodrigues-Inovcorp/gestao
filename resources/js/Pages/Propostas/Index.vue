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
import { Plus, Pencil, Trash2, FileDown, ArrowRight, Search, X } from 'lucide-vue-next'

const props = defineProps({
    propostas: Array,
    clientes: Array,
    artigos: Array,
    fornecedores: Array,
    proximoNumero: Number,
})

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

function defaultForm(overrides = {}) {
    return {
        entidade_id: null,
        validade: null,
        estado: 'rascunho',
        linhas: [],
        ...overrides,
    }
}

function defaultLinha() {
    return {
        artigo_id: null, fornecedor_id: null,
        referencia: '', nome: '',
        quantidade: 1, preco_venda: 0,
        preco_custo: 0, iva: 0,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function addLinha(form) {
    form.linhas.push(defaultLinha())
}

function removeLinha(form, index) {
    form.linhas.splice(index, 1)
}

function onArtigoSelect(form, index, artigoId) {
    const artigo = props.artigos.find(a => a.id === artigoId)
    if (!artigo) return
    form.linhas[index].artigo_id = artigo.id
    form.linhas[index].referencia = artigo.referencia
    form.linhas[index].nome = artigo.nome
    form.linhas[index].preco_venda = artigo.preco
}

function calcSubtotal(linha) {
    return (linha.quantidade * linha.preco_venda).toFixed(2)
}

function totalForm(form) {
    return form.linhas.reduce((acc, l) => acc + (l.quantidade * l.preco_venda), 0).toFixed(2)
}

function openEdit(proposta) {
    editing.value = proposta
    // Carregar linhas da proposta
    fetch(`/propostas/${proposta.id}/linhas`)
        .then(r => r.json())
        .then(linhas => {
            Object.assign(editForm, defaultForm({
                entidade_id: proposta.entidade_id,
                validade: proposta.validade_raw,
                estado: proposta.estado,
                linhas: linhas,
            }))
            showEdit.value = true
        })
}

function submitCreate() {
    createForm.post('/propostas', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    editForm.put(`/propostas/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(proposta) {
    if (confirm(`Eliminar proposta Nº ${proposta.numero}?`)) {
        useForm({}).delete(`/propostas/${proposta.id}`)
    }
}

function converter(proposta) {
    if (confirm(`Converter proposta Nº ${proposta.numero} em encomenda?`)) {
        useForm({}).post(`/propostas/${proposta.id}/converter`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const estadoBadge = {
    rascunho: 'secondary',
    fechado: 'default',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Propostas</h1>
        </template>

        <div class="space-y-4">
            <!-- Cabeçalho com ação principal de criação. -->
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Proposta
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Nova Proposta — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-4 py-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="space-y-1 col-span-1">
                                    <Label>Cliente *</Label>
                                    <Select v-model="createForm.entidade_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecionar cliente..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="c in clientes" :key="c.id" :value="c.id">
                                                {{ c.nome }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="createForm.errors.entidade_id" class="text-xs text-destructive">{{ createForm.errors.entidade_id }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>Validade</Label>
                                    <Input v-model="createForm.validade" type="date" />
                                </div>
                                <div class="space-y-1">
                                    <Label>Estado</Label>
                                    <Select v-model="createForm.estado">
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="rascunho">Rascunho</SelectItem>
                                            <SelectItem value="fechado">Fechado</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <!-- Linhas da proposta. -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label>Artigos</Label>
                                    <Button type="button" size="sm" variant="outline" @click="addLinha(createForm)" class="gap-1">
                                        <Plus class="w-3.5 h-3.5" /> Adicionar Linha
                                    </Button>
                                </div>

                                <div v-if="createForm.linhas.length === 0" class="rounded-md border border-dashed p-6 text-center text-sm text-muted-foreground">
                                    Adiciona pelo menos uma linha.
                                </div>

                                <div v-for="(linha, i) in createForm.linhas" :key="i" class="rounded-md border p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-muted-foreground">Linha {{ i + 1 }}</span>
                                        <Button type="button" size="icon" variant="ghost" class="w-6 h-6 text-destructive" @click="removeLinha(createForm, i)">
                                            <X class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>

                                    <!-- Dados de identificação da linha. -->
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 8px;">
                                        <div class="space-y-1">
                                            <Label class="text-xs">Artigo</Label>
                                            <Select v-model="linha.artigo_id" @update:modelValue="onArtigoSelect(createForm, i, $event)">
                                                <SelectTrigger class="h-8 text-xs">
                                                    <SelectValue placeholder="Pesquisar artigo..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="a in artigos" :key="a.id" :value="a.id">
                                                        {{ a.referencia }} — {{ a.nome }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Fornecedor</Label>
                                            <Select v-model="linha.fornecedor_id">
                                                <SelectTrigger class="h-8 text-xs">
                                                    <SelectValue placeholder="Fornecedor..." />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem v-for="f in fornecedores" :key="f.id" :value="f.id">
                                                        {{ f.nome }}
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Referência</Label>
                                            <Input v-model="linha.referencia" class="h-8 text-xs" />
                                        </div>
                                    </div>

                                    <!-- Quantidade e valores da linha. -->
                                    <div style="display: grid; grid-template-columns: 2fr 0.5fr 1fr 1fr 0.5fr; gap: 8px; align-items: end;">
                                        <div class="space-y-1">
                                            <Label class="text-xs">Nome *</Label>
                                            <Input v-model="linha.nome" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Qtd.</Label>
                                            <Input v-model="linha.quantidade" type="number" min="1" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Preço Venda</Label>
                                            <Input v-model="linha.preco_venda" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Preço Custo</Label>
                                            <Input v-model="linha.preco_custo" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">IVA (%)</Label>
                                            <Input v-model="linha.iva" type="number" min="0" max="100" class="h-8 text-xs" />
                                        </div>
                                    </div>

                                    <div class="flex justify-end text-xs font-medium text-muted-foreground">
                                        Subtotal: {{ formatPrice(calcSubtotal(linha)) }}
                                    </div>
                                </div>

                                <div v-if="createForm.linhas.length > 0" class="flex justify-end text-base font-bold pt-2">
                                    Total: {{ formatPrice(totalForm(createForm)) }}
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Tabela de propostas existentes. -->
            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nº</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Validade</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Valor Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="propostas.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma proposta registada.
                            </td>
                        </tr>
                        <tr v-for="p in propostas" :key="p.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-mono">{{ String(p.numero).padStart(5, '0') }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ p.data ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ p.cliente }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ p.validade ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatPrice(p.valor_total) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="estadoBadge[p.estado]">{{ p.estado }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <Button size="icon" variant="ghost" as="a" :href="`/propostas/${p.id}/pdf`" target="_blank" title="Download PDF">
                                        <FileDown class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="p.estado === 'fechado'" size="icon" variant="ghost" @click="converter(p)" title="Converter em Encomenda">
                                        <ArrowRight class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" @click="openEdit(p)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(p)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>