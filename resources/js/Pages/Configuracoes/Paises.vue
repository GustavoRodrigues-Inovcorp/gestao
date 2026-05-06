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

const props = defineProps({ paises: Array })

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

const createForm = useForm({ nome: '', codigo: '' })
const editForm = useForm({ nome: '', codigo: '', ativo: true })

function openEdit(pais) {
    editing.value = pais
    editForm.nome = pais.nome
    editForm.codigo = pais.codigo
    editForm.ativo = pais.ativo
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/configuracoes/paises', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    editForm.put(`/configuracoes/paises/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(pais) {
    if (confirm(`Eliminar "${pais.nome}"?`)) {
        useForm({}).delete(`/configuracoes/paises/${pais.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Países</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo País
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Novo País</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Código (ex: PT) *</Label>
                                <Input v-model="createForm.codigo" maxlength="3" class="uppercase" />
                                <p v-if="createForm.errors.codigo" class="text-xs text-destructive">{{ createForm.errors.codigo }}</p>
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
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Código</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="paises.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum país registado.
                            </td>
                        </tr>
                        <tr v-for="pais in paises" :key="pais.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3">{{ pais.nome }}</td>
                            <td class="px-4 py-3 uppercase">{{ pais.codigo }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="pais.ativo ? 'default' : 'secondary'">
                                    {{ pais.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(pais)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(pais)">
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
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Editar País</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.nome" />
                    </div>
                    <div class="space-y-1">
                        <Label>Código *</Label>
                        <Input v-model="editForm.codigo" maxlength="3" class="uppercase" />
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