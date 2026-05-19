<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
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
import { Textarea } from '@/Components/ui/textarea'
import { Plus, Pencil, Trash2, X, Search } from 'lucide-vue-next'

const props = defineProps({
    ordens: Array,
    clientes: Array,
    contactos: Array,
    artigos: Array,
    utilizadores: Array,
    proximoNumero: Number,
})

const { can } = useMenuPermissions('ordens_trabalho')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)
const search = ref('')
const showView = ref(false)
const viewing = ref(null)

const filtered = computed(() => {
    if (!search.value) return props.ordens
    const q = search.value.toLowerCase()
    return props.ordens.filter(o =>
        o.entidade?.toLowerCase().includes(q) ||
        o.descricao?.toLowerCase().includes(q) ||
        String(o.numero).includes(q)
    )
})

// Contactos filtrados pela entidade selecionada
const contactosFiltrados = computed(() => {
    const entidadeId = showCreate.value ? createForm.entidade_id : editForm.entidade_id
    if (!entidadeId) return props.contactos
    return props.contactos.filter(c => c.entidade_id === entidadeId)
})

function defaultForm(overrides = {}) {
    return {
        entidade_id: null,
        contacto_id: null,
        data_ordem: new Date().toISOString().split('T')[0],
        descricao: '',
        observacoes: '',
        user_id: null,
        estado: 'aberta',
        data_prevista: null,
        data_conclusao: null,
        linhas: [],
        ...overrides,
    }
}

function defaultLinha() {
    return {
        artigo_id: null,
        descricao: '',
        quantidade: 1,
        unidade: '',
        preco: 0,
    }
}

const createForm = useForm(defaultForm()).transform(data => ({
    ...data,
    data: data.data_ordem,
}))
const editForm = useForm(defaultForm()).transform(data => ({
    ...data,
    data: data.data_ordem,
}))

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
    form.linhas[index].descricao = artigo.nome
    form.linhas[index].preco = artigo.preco
}

function calcSubtotal(linha) {
    return (linha.quantidade * linha.preco).toFixed(2)
}

function totalForm(form) {
    return form.linhas.reduce((acc, l) => acc + (l.quantidade * l.preco), 0).toFixed(2)
}

function openEdit(ordem) {
    if (!can('update')) return
    editing.value = ordem
    fetch(`/ordens-trabalho/${ordem.id}/linhas`)
        .then(r => r.json())
        .then(linhas => {
            Object.assign(editForm, defaultForm({
                entidade_id: ordem.entidade_id,
                contacto_id: ordem.contacto_id,
                data_ordem: ordem.data_raw,
                descricao: ordem.descricao,
                observacoes: ordem.observacoes,
                user_id: ordem.user_id,
                estado: ordem.estado,
                data_prevista: ordem.data_prevista_raw,
                data_conclusao: ordem.data_conclusao_raw,
                linhas: linhas,
            }))
            showEdit.value = true
        })
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/ordens-trabalho', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/ordens-trabalho/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(ordem) {
    if (!can('delete')) return
    if (confirm(`Eliminar Ordem de Trabalho Nº ${ordem.numero}?`)) {
        useForm({}).delete(`/ordens-trabalho/${ordem.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

const estadoConfig = {
    aberta:       { label: 'Aberta',       variant: 'outline' },
    em_progresso: { label: 'Em Progresso', variant: 'default' },
    concluida:    { label: 'Concluída',    variant: 'secondary' },
    cancelada:    { label: 'Cancelada',    variant: 'destructive' },
}

function openView(entidade) {
    viewing.value = entidade
    showView.value = true
}

function viewFields(o) {
    if (!o) return []
    return [
        { label: 'Número', value: String(o.numero).padStart(5, '0') },
        { label: 'Data', value: o.data },
        { label: 'Cliente', value: o.entidade },
        { label: 'Contacto', value: o.contacto },
        { label: 'Responsável', value: o.user },
        { label: 'Data Prevista', value: o.data_prevista },
        { label: 'Data Conclusão', value: o.data_conclusao },
        { label: 'Descrição', value: o.descricao },
        { label: 'Observações', value: o.observacoes },
        { label: 'Total', value: o.total, type: 'currency' },
        { label: 'Estado', value: o.estado },
    ]
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Ordens de Trabalho</h1>
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="relative max-w-sm w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Pesquisar..." class="pl-9" />
                </div>

                <Dialog v-if="can('create')" v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Ordem
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Nova Ordem de Trabalho — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-4 py-2">
                            <!-- Cabeçalho -->
                            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
                                <div class="space-y-1">
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
                                    <Label>Contacto</Label>
                                    <Select v-model="createForm.contacto_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Contacto..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem :value="null">Nenhum</SelectItem>
                                            <SelectItem v-for="c in contactosFiltrados" :key="c.id" :value="c.id">
                                                {{ c.nome }} {{ c.apelido ?? '' }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-1">
                                    <Label>Data *</Label>
                                    <Input v-model="createForm.data_ordem" type="date" />
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 12px;">
                                <div class="space-y-1">
                                    <Label>Estado</Label>
                                    <Select v-model="createForm.estado">
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="aberta">Aberta</SelectItem>
                                            <SelectItem value="em_progresso">Em Progresso</SelectItem>
                                            <SelectItem value="concluida">Concluída</SelectItem>
                                            <SelectItem value="cancelada">Cancelada</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-1">
                                    <Label>Responsável</Label>
                                    <Select v-model="createForm.user_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Utilizador..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="u in utilizadores" :key="u.id" :value="u.id">
                                                {{ u.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-1">
                                    <Label>Data Prevista</Label>
                                    <Input v-model="createForm.data_prevista" type="date" />
                                </div>
                                <div class="space-y-1">
                                    <Label>Data Conclusão</Label>
                                    <Input v-model="createForm.data_conclusao" type="date" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label>Descrição</Label>
                                <Textarea v-model="createForm.descricao" rows="2" />
                            </div>
                            <div class="space-y-1">
                                <Label>Observações</Label>
                                <Textarea v-model="createForm.observacoes" rows="2" />
                            </div>

                            <!-- Linhas -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <Label>Materiais / Serviços</Label>
                                    <Button type="button" size="sm" variant="outline" @click="addLinha(createForm)" class="gap-1">
                                        <Plus class="w-3.5 h-3.5" /> Adicionar Linha
                                    </Button>
                                </div>

                                <div v-if="createForm.linhas.length === 0" class="rounded-md border border-dashed p-4 text-center text-sm text-muted-foreground">
                                    Sem linhas adicionadas.
                                </div>

                                <div v-for="(linha, i) in createForm.linhas" :key="i" class="rounded-md border p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-medium text-muted-foreground">Linha {{ i + 1 }}</span>
                                        <Button type="button" size="icon" variant="ghost" class="w-6 h-6 text-destructive" @click="removeLinha(createForm, i)">
                                            <X class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 2fr 2fr; gap: 8px;">
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
                                            <Label class="text-xs">Descrição *</Label>
                                            <Input v-model="linha.descricao" class="h-8 text-xs" />
                                        </div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; align-items: end;">
                                        <div class="space-y-1">
                                            <Label class="text-xs">Quantidade</Label>
                                            <Input v-model="linha.quantidade" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Unidade</Label>
                                            <Input v-model="linha.unidade" class="h-8 text-xs" placeholder="un, h, kg..." />
                                        </div>
                                        <div class="space-y-1">
                                            <Label class="text-xs">Preço Unit.</Label>
                                            <Input v-model="linha.preco" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                        </div>
                                        <div class="flex items-end pb-1">
                                            <span class="text-xs font-medium text-muted-foreground">
                                                = {{ formatPrice(calcSubtotal(linha)) }}
                                            </span>
                                        </div>
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

            <!-- Tabela -->
            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nº</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Contacto</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Responsável</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data Prevista</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma ordem de trabalho encontrada.
                            </td>
                        </tr>
                        <tr v-for="o in filtered" :key="o.id" class="border-b last:border-0 hover:bg-muted/30" @click="openView(o)">
                            <td class="px-4 py-3 font-mono">{{ String(o.numero).padStart(5, '0') }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ o.data }}</td>
                            <td class="px-4 py-3 font-medium">{{ o.entidade }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ o.contacto ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ o.user }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ o.data_prevista ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ formatPrice(o.total) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="estadoConfig[o.estado]?.variant">
                                    {{ estadoConfig[o.estado]?.label }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1" @click.stop>
                                    <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(o)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(o)">
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
                    <DialogTitle>Editar Ordem de Trabalho — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-2">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
                        <div class="space-y-1">
                            <Label>Cliente *</Label>
                            <Select v-model="editForm.entidade_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecionar cliente..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="c in clientes" :key="c.id" :value="c.id">
                                        {{ c.nome }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Contacto</Label>
                            <Select v-model="editForm.contacto_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Contacto..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Nenhum</SelectItem>
                                    <SelectItem v-for="c in contactosFiltrados" :key="c.id" :value="c.id">
                                        {{ c.nome }} {{ c.apelido ?? '' }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Data *</Label>
                            <Input v-model="editForm.data_ordem" type="date" />
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 12px;">
                        <div class="space-y-1">
                            <Label>Estado</Label>
                            <Select v-model="editForm.estado">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="aberta">Aberta</SelectItem>
                                    <SelectItem value="em_progresso">Em Progresso</SelectItem>
                                    <SelectItem value="concluida">Concluída</SelectItem>
                                    <SelectItem value="cancelada">Cancelada</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Responsável</Label>
                            <Select v-model="editForm.user_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Utilizador..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="u in utilizadores" :key="u.id" :value="u.id">
                                        {{ u.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-1">
                            <Label>Data Prevista</Label>
                            <Input v-model="editForm.data_prevista" type="date" />
                        </div>
                        <div class="space-y-1">
                            <Label>Data Conclusão</Label>
                            <Input v-model="editForm.data_conclusao" type="date" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Descrição</Label>
                        <Textarea v-model="editForm.descricao" rows="2" />
                    </div>
                    <div class="space-y-1">
                        <Label>Observações</Label>
                        <Textarea v-model="editForm.observacoes" rows="2" />
                    </div>

                    <!-- Linhas -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label>Materiais / Serviços</Label>
                            <Button type="button" size="sm" variant="outline" @click="addLinha(editForm)" class="gap-1">
                                <Plus class="w-3.5 h-3.5" /> Adicionar Linha
                            </Button>
                        </div>

                        <div v-if="editForm.linhas.length === 0" class="rounded-md border border-dashed p-4 text-center text-sm text-muted-foreground">
                            Sem linhas adicionadas.
                        </div>

                        <div v-for="(linha, i) in editForm.linhas" :key="i" class="rounded-md border p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-muted-foreground">Linha {{ i + 1 }}</span>
                                <Button type="button" size="icon" variant="ghost" class="w-6 h-6 text-destructive" @click="removeLinha(editForm, i)">
                                    <X class="w-3.5 h-3.5" />
                                </Button>
                            </div>
                            <div style="display: grid; grid-template-columns: 2fr 2fr; gap: 8px;">
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
                                    <Label class="text-xs">Descrição *</Label>
                                    <Input v-model="linha.descricao" class="h-8 text-xs" />
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 8px; align-items: end;">
                                <div class="space-y-1">
                                    <Label class="text-xs">Quantidade</Label>
                                    <Input v-model="linha.quantidade" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-xs">Unidade</Label>
                                    <Input v-model="linha.unidade" class="h-8 text-xs" placeholder="un, h, kg..." />
                                </div>
                                <div class="space-y-1">
                                    <Label class="text-xs">Preço Unit.</Label>
                                    <Input v-model="linha.preco" type="number" min="0" step="0.01" class="h-8 text-xs" />
                                </div>
                                <div class="flex items-end pb-1">
                                    <span class="text-xs font-medium text-muted-foreground">
                                        = {{ formatPrice(calcSubtotal(linha)) }}
                                    </span>
                                </div>
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
            :title="'Ordem de Trabalho — ' + (viewing?.numero ?? '')"
            :fields="viewFields(viewing)"
            :can-edit="can('update')"
            :can-delete="can('delete')"
            @update:open="showView = $event"
            @edit="() => { showView = false; openEdit(viewing) }"
            @delete="() => { showView = false; destroy(viewing) }"
        />
    </AppLayout>
</template>