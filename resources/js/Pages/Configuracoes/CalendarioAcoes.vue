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

const props = defineProps({ acoes: Array })

const { can } = useMenuPermissions('configuracoes')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

const createForm = useForm({ nome: '' })
const editForm = useForm({ nome: '', ativo: true })

function openEdit(acao) {
    if (!can('update')) return
    editing.value = acao
    editForm.nome = acao.nome
    editForm.ativo = acao.ativo
    showEdit.value = true
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/configuracoes/calendario-acoes', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/configuracoes/calendario-acoes/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(acao) {
    if (!can('delete')) return
    if (confirm(`Eliminar "${acao.nome}"?`)) {
        useForm({}).delete(`/configuracoes/calendario-acoes/${acao.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Calendário - Ações</h1>
        </template>

        <div class="space-y-4">
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Ação
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Nova Ação</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
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
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="acoes.length === 0">
                            <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma ação registada.
                            </td>
                        </tr>
                        <tr v-for="acao in acoes" :key="acao.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3">{{ acao.nome }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="acao.ativo ? 'default' : 'secondary'">
                                    {{ acao.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(acao)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(acao)">
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
                    <DialogTitle>Editar Ação</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
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