<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DataTable from '@/Components/data-table/DataTable.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Textarea } from '@/Components/ui/textarea'
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    contactos: Array,
    entidades: Array,
    funcoes: Array,
    proximoNumero: Number,
})

const columns = [
    { key: 'nome', label: 'Nome', cellClass: 'font-medium' },
    { key: 'apelido', label: 'Apelido' },
    { key: 'funcao_nome', label: 'Função' },
    { key: 'entidade_nome', label: 'Entidade' },
    { key: 'telefone', label: 'Telefone' },
    { key: 'telemovel', label: 'Telemóvel' },
    { key: 'email', label: 'Email' },
]

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

function defaultForm(overrides = {}) {
    return {
        entidade_id: null,
        nome: '',
        apelido: '',
        funcao_id: null,
        telefone: '',
        telemovel: '',
        email: '',
        rgpd: false,
        observacoes: '',
        ativo: true,
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function openEdit(contacto) {
    editing.value = contacto
    Object.assign(editForm, defaultForm({
        entidade_id: contacto.entidade_id,
        nome: contacto.nome,
        apelido: contacto.apelido,
        funcao_id: contacto.funcao_id,
        telefone: contacto.telefone,
        telemovel: contacto.telemovel,
        email: contacto.email,
        rgpd: !!contacto.rgpd,
        observacoes: contacto.observacoes,
        ativo: !!contacto.ativo,
    }))
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/contactos', {
        onSuccess: () => {
            showCreate.value = false
            Object.assign(createForm, defaultForm())
        },
    })
}

function submitEdit() {
    editForm.put(`/contactos/${editing.value.id}`, {
        onSuccess: () => {
            showEdit.value = false
        },
    })
}

function destroy(contacto) {
    const nomeCompleto = `${contacto.nome} ${contacto.apelido || ''}`.trim()
    if (confirm(`Eliminar "${nomeCompleto}"?`)) {
        useForm({}).delete(`/contactos/${contacto.id}`)
    }
}

function mappedRows() {
    return props.contactos.map((c) => ({
        ...c,
        funcao_nome: c.funcao?.nome ?? '—',
        entidade_nome: c.entidade?.nome ?? '—',
    }))
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Contactos</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Contacto
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Contacto — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1 col-span-2">
                                <Label>Número</Label>
                                <Input :model-value="String(proximoNumero)" disabled />
                            </div>

                            <div class="space-y-1">
                                <Label>Entidade *</Label>
                                <Select v-model="createForm.entidade_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar entidade..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="entidade in entidades" :key="entidade.id" :value="entidade.id">
                                            {{ entidade.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="createForm.errors.entidade_id" class="text-xs text-destructive">{{ createForm.errors.entidade_id }}</p>
                            </div>

                            <div class="space-y-1">
                                <Label>Função</Label>
                                <Select v-model="createForm.funcao_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar função..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="funcao in funcoes" :key="funcao.id" :value="funcao.id">
                                            {{ funcao.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>

                            <div class="space-y-1">
                                <Label>Apelido</Label>
                                <Input v-model="createForm.apelido" />
                            </div>

                            <div class="space-y-1">
                                <Label>Telefone</Label>
                                <Input v-model="createForm.telefone" />
                            </div>

                            <div class="space-y-1">
                                <Label>Telemóvel</Label>
                                <Input v-model="createForm.telemovel" />
                            </div>

                            <div class="space-y-1 col-span-2">
                                <Label>Email</Label>
                                <Input v-model="createForm.email" type="email" />
                                <p v-if="createForm.errors.email" class="text-xs text-destructive">{{ createForm.errors.email }}</p>
                            </div>

                            <div class="space-y-1">
                                <Label>Consentimento RGPD</Label>
                                <Select v-model="createForm.rgpd">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="true">Sim</SelectItem>
                                        <SelectItem :value="false">Não</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-1">
                                <Label>Estado</Label>
                                <Select v-model="createForm.ativo">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="true">Ativo</SelectItem>
                                        <SelectItem :value="false">Inativo</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-1 col-span-2">
                                <Label>Observações</Label>
                                <Textarea v-model="createForm.observacoes" rows="3" />
                            </div>
                        </div>

                        <DialogFooter class="mt-4">
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <DataTable
                :columns="columns"
                :data="mappedRows()"
                empty-text="Nenhum contacto registado."
            >
                <template #actions="{ row }">
                    <div class="flex justify-end gap-2">
                        <Button size="icon" variant="ghost" @click="openEdit(row)">
                            <Pencil class="w-4 h-4" />
                        </Button>
                        <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(row)">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </template>
            </DataTable>
        </div>

        <Dialog v-model:open="showEdit">
            <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Contacto — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1 col-span-2">
                        <Label>Número</Label>
                        <Input :model-value="String(editing?.numero ?? '')" disabled />
                    </div>

                    <div class="space-y-1">
                        <Label>Entidade *</Label>
                        <Select v-model="editForm.entidade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar entidade..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="entidade in entidades" :key="entidade.id" :value="entidade.id">
                                    {{ entidade.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="editForm.errors.entidade_id" class="text-xs text-destructive">{{ editForm.errors.entidade_id }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label>Função</Label>
                        <Select v-model="editForm.funcao_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar função..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="funcao in funcoes" :key="funcao.id" :value="funcao.id">
                                    {{ funcao.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
                        <p v-if="editForm.errors.nome" class="text-xs text-destructive">{{ editForm.errors.nome }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label>Apelido</Label>
                        <Input v-model="editForm.apelido" />
                    </div>

                    <div class="space-y-1">
                        <Label>Telefone</Label>
                        <Input v-model="editForm.telefone" />
                    </div>

                    <div class="space-y-1">
                        <Label>Telemóvel</Label>
                        <Input v-model="editForm.telemovel" />
                    </div>

                    <div class="space-y-1 col-span-2">
                        <Label>Email</Label>
                        <Input v-model="editForm.email" type="email" />
                        <p v-if="editForm.errors.email" class="text-xs text-destructive">{{ editForm.errors.email }}</p>
                    </div>

                    <div class="space-y-1">
                        <Label>Consentimento RGPD</Label>
                        <Select v-model="editForm.rgpd">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="true">Sim</SelectItem>
                                <SelectItem :value="false">Não</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <Label>Estado</Label>
                        <Select v-model="editForm.ativo">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="true">Ativo</SelectItem>
                                <SelectItem :value="false">Inativo</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1 col-span-2">
                        <Label>Observações</Label>
                        <Textarea v-model="editForm.observacoes" rows="3" />
                    </div>
                </div>

                <DialogFooter class="mt-4">
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
