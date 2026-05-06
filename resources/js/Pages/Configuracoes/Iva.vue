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
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps({ taxas: Array })

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

const createForm = useForm({ nome: '', percentagem: '' })
const editForm = useForm({ nome: '', percentagem: '', ativo: true })

function openEdit(taxa) {
    editing.value = taxa
    editForm.nome = taxa.nome
    editForm.percentagem = taxa.percentagem
    editForm.ativo = taxa.ativo
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/configuracoes/iva', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    editForm.put(`/configuracoes/iva/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(taxa) {
    if (confirm(`Eliminar "${taxa.nome}"?`)) {
        useForm({}).delete(`/configuracoes/iva/${taxa.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Financeiro - IVA</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Taxa
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Nova Taxa de IVA</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" placeholder="ex: IVA Normal" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Percentagem (%) *</Label>
                                <Input v-model="createForm.percentagem" type="number" min="0" max="100" step="0.01" placeholder="23" />
                                <p v-if="createForm.errors.percentagem" class="text-xs text-destructive">{{ createForm.errors.percentagem }}</p>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Percentagem</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="taxas.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhuma taxa registada.
                            </td>
                        </tr>
                        <tr v-for="taxa in taxas" :key="taxa.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3">{{ taxa.nome }}</td>
                            <td class="px-4 py-3">{{ taxa.percentagem }}%</td>
                            <td class="px-4 py-3">
                                <Badge :variant="taxa.ativo ? 'default' : 'secondary'">
                                    {{ taxa.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(taxa)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(taxa)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="showEdit">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Editar Taxa de IVA</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
                    </div>
                    <div class="space-y-1">
                        <Label>Percentagem (%) *</Label>
                        <Input v-model="editForm.percentagem" type="number" min="0" max="100" step="0.01" />
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