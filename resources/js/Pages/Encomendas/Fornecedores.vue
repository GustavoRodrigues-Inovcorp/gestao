<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import ViewSheet from '@/Components/ui/sheet/ViewSheet.vue'

import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Plus, Pencil, Trash2, FileDown, X } from 'lucide-vue-next'

const props = defineProps({
    encomendas: Array,
    fornecedores: Array,
    artigos: Array,
    proximoNumero: Number,
})

const page = usePage()
const permissions = computed(() => page.props.auth?.permissions ?? [])

function can(action) {
    return permissions.value.includes(`encomendas_fornecedores.${action}`)
}

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)
const showView = ref(false)
const viewing = ref(null)

function defaultForm(overrides = {}) {
    return {
        fornecedor_id: null,
        estado: 'rascunho',
        validade: null,
        linhas: [],
        ...overrides,
    }
}

function defaultLinha() {
    return {
        artigo_id: null,
        referencia: '', nome: '',
        quantidade: 1, preco_custo: 0, iva: 0,
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
    form.linhas[index].preco_custo = artigo.preco
}

function calcSubtotal(linha) {
    return (linha.quantidade * linha.preco_custo).toFixed(2)
}

function totalForm(form) {
    return form.linhas.reduce((acc, l) => acc + (l.quantidade * l.preco_custo), 0).toFixed(2)
}

function openEdit(encomenda) {
    if (!can('update')) return
    editing.value = encomenda
    fetch(`/encomendas-fornecedor/${encomenda.id}/linhas`)
        .then(r => r.json())
        .then(linhas => {
            Object.assign(editForm, defaultForm({
                fornecedor_id: encomenda.fornecedor_id,
                estado: encomenda.estado,
                linhas: linhas,
            }))
            showEdit.value = true
        })
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/encomendas-fornecedor', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/encomendas-fornecedor/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(encomenda) {
    if (!can('delete')) return
    if (confirm(`Eliminar encomenda Nº ${encomenda.numero}?`)) {
        useForm({}).delete(`/encomendas-fornecedor/${encomenda.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const estadoBadge = { rascunho: 'secondary', fechado: 'default' }

function openView(entidade) {
    viewing.value = entidade
    showView.value = true
}

function viewFields(e) {
    if (!e) return []
    return [
        { label: 'Número', value: String(e.numero).padStart(5, '0') },
        { label: 'Data', value: e.data },
        { label: 'Fornecedor', value: e.fornecedor },
        { label: 'Valor Total', value: e.valor_total, type: 'currency' },
        { label: 'Estado', value: e.estado },
    ]
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Encomendas — Fornecedores</h1>
        </template>

        <div class="space-y-4">
            <!-- Ação principal: criar uma encomenda de fornecedor. -->
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Encomenda
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Nova Encomenda Fornecedor — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-4 py-2">
                            <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 16px;">
                                <div class="space-y-1">
                                    <Label>Validade</Label>
                                    <input
                                        v-model="createForm.validade"
                                        type="date"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                        @mousedown.stop
                                    />
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

                            <!-- Linhas da encomenda de fornecedor. -->
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
                                    <!-- Identificação da linha. -->
                                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px;">
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
                                            <Label class="text-xs">Referência</Label>
                                            <Input v-model="linha.referencia" class="h-8 text-xs" />
                                        </div>
                                    </div>
                                    <!-- Quantidades e custo da linha. -->
                                    <div style="display: grid; grid-template-columns: 2fr 0.5fr 1fr 0.5fr; gap: 8px; align-items: end;">
                                        <div class="space-y-1">
                                            <Label class="text-xs">Nome *</Label>
                                            <Input v-model="linha.nome" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Qtd.</Label>
                                            <Input v-model="linha.quantidade" type="number" min="1" class="h-8 text-xs" />
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

            <!-- Tabela das encomendas de fornecedor. -->
            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nº</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Validade</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fornecedor</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Valor Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="encomendas.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma encomenda registada.
                            </td>
                        </tr>
                        <tr v-for="e in encomendas" :key="e.id" class="border-b last:border-0 hover:bg-muted/30" @click="openView(e)">
                            <td class="px-4 py-3 text-muted-foreground">{{ e.data ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ String(e.numero).padStart(5, '0') }}</td>
                            <td class="px-4 py-3 font-medium">{{ e.validade ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ e.fornecedor }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatPrice(e.valor_total) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="estadoBadge[e.estado]">{{ e.estado }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1" @click.stop>
                                    <Button size="icon" variant="ghost" as="a" :href="`/encomendas-fornecedor/${e.id}/pdf`" target="_blank" title="Download PDF">
                                        <FileDown class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(e)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(e)">
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
        <Dialog v-if="can('update')" v-model:open="showEdit">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Encomenda Fornecedor — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-2">
                    <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 16px;">
                        <div class="space-y-1">
                            <Label>Validade</Label>
                            <input
                                v-model="editForm.validade"
                                type="date"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                @mousedown.stop
                            />
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
                            <Label>Estado</Label>
                            <Select v-model="editForm.estado">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="rascunho">Rascunho</SelectItem>
                                    <SelectItem value="fechado">Fechado</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Artigos</Label>
                            <Button type="button" size="sm" variant="outline" @click="addLinha(editForm)" class="gap-1">
                                <Plus class="w-3.5 h-3.5" /> Adicionar Linha
                            </Button>
                        </div>

                        <div v-for="(linha, i) in editForm.linhas" :key="i" class="rounded-md border p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-muted-foreground">Linha {{ i + 1 }}</span>
                                <Button type="button" size="icon" variant="ghost" class="w-6 h-6 text-destructive" @click="removeLinha(editForm, i)">
                                    <X class="w-3.5 h-3.5" />
                                </Button>
                            </div>
                            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 8px;">
                                <div class="space-y-1">
                                    <Label class="text-xs">Artigo</Label>
                                    <Select v-model="linha.artigo_id" @update:modelValue="onArtigoSelect(editForm, i, $event)">
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
                                    <Label class="text-xs">Referência</Label>
                                    <Input v-model="linha.referencia" class="h-8 text-xs" />
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 2fr 0.5fr 1fr 0.5fr; gap: 8px; align-items: end;">
                                <div class="space-y-1">
                                    <Label class="text-xs">Nome *</Label>
                                    <Input v-model="linha.nome" class="h-8 text-xs" />
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-xs">Qtd.</Label>
                                    <Input v-model="linha.quantidade" type="number" min="1" class="h-8 text-xs" />
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

                        <div v-if="editForm.linhas.length > 0" class="flex justify-end text-base font-bold pt-2">
                            Total: {{ formatPrice(totalForm(editForm)) }}
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <ViewSheet
            :open="showView"
            :title="'Encomenda — ' + (viewing?.numero ?? '')"
            :fields="viewFields(viewing)"
            :can-edit="can('update')"
            :can-delete="can('delete')"
            @update:open="showView = $event"
            @edit="() => { showView = false; openEdit(viewing) }"
            @delete="() => { showView = false; destroy(viewing) }"
        />
    </AppLayout>
</template>