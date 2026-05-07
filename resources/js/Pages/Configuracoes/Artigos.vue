<script setup>
import { ref } from 'vue'
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
import { Textarea } from '@/Components/ui/textarea'
import { Plus, Pencil, Trash2, ImageIcon } from 'lucide-vue-next'

const props = defineProps({
    artigos: Array,
    taxas_iva: Array,
})

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

function defaultForm(overrides = {}) {
    return {
        referencia: '', nome: '', descricao: '',
        preco: '', iva_id: null, foto: null,
        observacoes: '', ativo: true,
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function openEdit(artigo) {
    editing.value = artigo
    Object.assign(editForm, defaultForm({ ...artigo, foto: null }))
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/configuracoes/artigos', {
        forceFormData: true,
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    editForm.post(`/configuracoes/artigos/${editing.value.id}`, {
        forceFormData: true,
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(artigo) {
    if (confirm(`Eliminar "${artigo.nome}"?`)) {
        useForm({}).delete(`/configuracoes/artigos/${artigo.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Artigos</h1>
        </template>

        <div class="space-y-4">
            <!-- Ação principal: criar um novo artigo. -->
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Artigo
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-lg max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Artigo</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Referência *</Label>
                                <Input v-model="createForm.referencia" />
                                <p v-if="createForm.errors.referencia" class="text-xs text-destructive">{{ createForm.errors.referencia }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Descrição</Label>
                                <Textarea v-model="createForm.descricao" rows="3" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <Label>Preço *</Label>
                                    <Input v-model="createForm.preco" type="number" min="0" step="0.01" placeholder="0.00" />
                                    <p v-if="createForm.errors.preco" class="text-xs text-destructive">{{ createForm.errors.preco }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>IVA</Label>
                                    <Select v-model="createForm.iva_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecionar..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="t in taxas_iva" :key="t.id" :value="t.id">
                                                {{ t.nome }} ({{ t.percentagem }}%)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label>Foto</Label>
                                <Input type="file" accept="image/*" @change="e => createForm.foto = e.target.files[0]" />
                            </div>
                            <div class="space-y-1">
                                <Label>Observações</Label>
                                <Textarea v-model="createForm.observacoes" rows="2" />
                            </div>
                            <div class="space-y-1">
                                <Label>Estado</Label>
                                <Select v-model="createForm.ativo">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="true">Ativo</SelectItem>
                                        <SelectItem :value="false">Inativo</SelectItem>
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

            <!-- Tabela de artigos com fotografia, preço e estado. -->
            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Foto</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Referência</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Descrição</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Preço</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">IVA</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="artigos.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum artigo registado.
                            </td>
                        </tr>
                        <tr v-for="a in artigos" :key="a.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <img v-if="a.foto" :src="a.foto" alt="Foto" class="w-10 h-10 object-cover rounded-md border" />
                                <div v-else class="w-10 h-10 rounded-md border bg-muted flex items-center justify-center">
                                    <ImageIcon class="w-4 h-4 text-muted-foreground" />
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ a.referencia }}</td>
                            <td class="px-4 py-3 font-medium">{{ a.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground max-w-xs truncate">{{ a.descricao ?? '—' }}</td>
                            <td class="px-4 py-3">{{ formatPrice(a.preco) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ a.iva ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="a.ativo ? 'default' : 'secondary'">
                                    {{ a.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(a)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(a)">
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
                    <DialogTitle>Editar Artigo</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Referência *</Label>
                        <Input v-model="editForm.referencia" />
                    </div>
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
                    </div>
                    <div class="space-y-1">
                        <Label>Descrição</Label>
                        <Textarea v-model="editForm.descricao" rows="3" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Preço *</Label>
                            <Input v-model="editForm.preco" type="number" min="0" step="0.01" />
                        </div>
                        <div class="space-y-1">
                            <Label>IVA</Label>
                            <Select v-model="editForm.iva_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Selecionar..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in taxas_iva" :key="t.id" :value="t.id">
                                        {{ t.nome }} ({{ t.percentagem }}%)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Foto</Label>
                        <div v-if="editing?.foto" class="mb-2">
                            <img :src="editing.foto" alt="Foto atual" class="w-16 h-16 object-cover rounded-md border" />
                        </div>
                        <Input type="file" accept="image/*" @change="e => editForm.foto = e.target.files[0]" />
                    </div>
                    <div class="space-y-1">
                        <Label>Observações</Label>
                        <Textarea v-model="editForm.observacoes" rows="2" />
                    </div>
                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="editForm.ativo">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="true">Ativo</SelectItem>
                                <SelectItem :value="false">Inativo</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>