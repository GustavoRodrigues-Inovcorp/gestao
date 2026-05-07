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
import { Textarea } from '@/Components/ui/textarea'
import { Plus, Pencil, Trash2, Search } from 'lucide-vue-next'

const props = defineProps({
    contactos: Array,
    entidades: Array,
    funcoes: Array,
    proximoNumero: Number,
})

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)
const search = ref('')

const filtered = computed(() => {
    if (!search.value) return props.contactos
    const q = search.value.toLowerCase()
    return props.contactos.filter(c =>
        c.nome?.toLowerCase().includes(q) ||
        c.apelido?.toLowerCase().includes(q) ||
        c.entidade?.toLowerCase().includes(q) ||
        c.email?.toLowerCase().includes(q)
    )
})

function defaultForm(overrides = {}) {
    return {
        entidade_id: null,
        nome: '', apelido: '', funcao_id: null,
        telefone: '', telemovel: '', email: '',
        rgpd: false, observacoes: '', ativo: true,
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function openEdit(contacto) {
    editing.value = contacto
    Object.assign(editForm, defaultForm(contacto))
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/contactos', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    editForm.put(`/contactos/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(contacto) {
    if (confirm(`Eliminar "${contacto.nome} ${contacto.apelido ?? ''}"?`)) {
        useForm({}).delete(`/contactos/${contacto.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Contactos</h1>
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <!-- Pesquisa -->
                <div class="relative max-w-sm w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Pesquisar..." class="pl-9" />
                </div>

                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Contacto
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-lg max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Contacto — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Entidade</Label>
                                <Select v-model="createForm.entidade_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar entidade..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="e in entidades" :key="e.id" :value="e.id">
                                            {{ e.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <Label>Nome *</Label>
                                    <Input v-model="createForm.nome" />
                                    <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                                </div>
                                <div class="space-y-1">
                                    <Label>Apelido</Label>
                                    <Input v-model="createForm.apelido" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label>Função</Label>
                                <Select v-model="createForm.funcao_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar função..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="f in funcoes" :key="f.id" :value="f.id">
                                            {{ f.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <Label>Telefone</Label>
                                    <Input v-model="createForm.telefone" />
                                </div>
                                <div class="space-y-1">
                                    <Label>Telemóvel</Label>
                                    <Input v-model="createForm.telemovel" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <Label>Email</Label>
                                <Input v-model="createForm.email" type="email" />
                                <p v-if="createForm.errors.email" class="text-xs text-destructive">{{ createForm.errors.email }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Consentimento RGPD</Label>
                                <Select v-model="createForm.rgpd">
                                    <SelectTrigger><SelectValue /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="true">Sim</SelectItem>
                                        <SelectItem :value="false">Não</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1">
                                <Label>Observações</Label>
                                <Textarea v-model="createForm.observacoes" rows="3" />
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

            <!-- Tabela -->
            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Apelido</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Função</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Entidade</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telefone</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telemóvel</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum contacto encontrado.
                            </td>
                        </tr>
                        <tr v-for="c in filtered" :key="c.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ c.nome }}</td>
                            <td class="px-4 py-3">{{ c.apelido ?? '—' }}</td>
                            <td class="px-4 py-3">{{ c.funcao ?? '—' }}</td>
                            <td class="px-4 py-3">{{ c.entidade ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ c.telefone ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ c.telemovel ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ c.email ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="c.ativo ? 'default' : 'secondary'">
                                    {{ c.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(c)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(c)">
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
                    <DialogTitle>Editar Contacto — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Entidade</Label>
                        <Select v-model="editForm.entidade_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar entidade..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="e in entidades" :key="e.id" :value="e.id">
                                    {{ e.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Nome *</Label>
                            <Input v-model="editForm.nome" />
                        </div>
                        <div class="space-y-1">
                            <Label>Apelido</Label>
                            <Input v-model="editForm.apelido" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Função</Label>
                        <Select v-model="editForm.funcao_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar função..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="f in funcoes" :key="f.id" :value="f.id">
                                    {{ f.nome }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label>Telefone</Label>
                            <Input v-model="editForm.telefone" />
                        </div>
                        <div class="space-y-1">
                            <Label>Telemóvel</Label>
                            <Input v-model="editForm.telemovel" />
                        </div>
                    </div>
                    <div class="space-y-1">
                        <Label>Email</Label>
                        <Input v-model="editForm.email" type="email" />
                    </div>
                    <div class="space-y-1">
                        <Label>Consentimento RGPD</Label>
                        <Select v-model="editForm.rgpd">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="true">Sim</SelectItem>
                                <SelectItem :value="false">Não</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <Label>Observações</Label>
                        <Textarea v-model="editForm.observacoes" rows="3" />
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