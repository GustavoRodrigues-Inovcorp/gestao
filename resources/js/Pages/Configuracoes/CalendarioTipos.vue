<script setup>
import { ref } from 'vue'
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
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps({ tipos: Array })

const { can } = useMenuPermissions('configuracoes')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

const createForm = useForm({ nome: '', cor: '#3b82f6' })
const editForm = useForm({ nome: '', cor: '#3b82f6', ativo: true })

function openEdit(tipo) {
    if (!can('update')) return
    editing.value = tipo
    editForm.nome = tipo.nome
    editForm.cor = tipo.cor
    editForm.ativo = tipo.ativo
    showEdit.value = true
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/configuracoes/calendario-tipos', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/configuracoes/calendario-tipos/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(tipo) {
    if (!can('delete')) return
    if (confirm(`Eliminar "${tipo.nome}"?`)) {
        useForm({}).delete(`/configuracoes/calendario-tipos/${tipo.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Calendário - Tipos</h1>
        </template>

        <div class="space-y-4">
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Tipo
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Novo Tipo</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Cor</Label>
                                <div class="flex items-center gap-3">
                                    <input type="color" v-model="createForm.cor" class="w-10 h-10 rounded cursor-pointer border" />
                                    <Input v-model="createForm.cor" class="font-mono" maxlength="7" />
                                </div>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cor</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tipos.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum tipo registado.
                            </td>
                        </tr>
                        <tr v-for="tipo in tipos" :key="tipo.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3">{{ tipo.nome }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full border" :style="{ backgroundColor: tipo.cor }"></div>
                                    <span class="font-mono text-xs text-muted-foreground">{{ tipo.cor }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="tipo.ativo ? 'default' : 'secondary'">
                                    {{ tipo.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(tipo)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(tipo)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-if="can('update')" v-model:open="showEdit">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Editar Tipo</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
                    </div>
                    <div class="space-y-1">
                        <Label>Cor</Label>
                        <div class="flex items-center gap-3">
                            <input type="color" v-model="editForm.cor" class="w-10 h-10 rounded cursor-pointer border" />
                            <Input v-model="editForm.cor" class="font-mono" maxlength="7" />
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="editForm.ativo" id="ativo" class="rounded" />
                        <Label for="ativo">Ativo</Label>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>