<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EntidadeForm from '@/Components/Entidades/EntidadeForm.vue'
import { Button } from '@/Components/ui/button'
import { Badge } from '@/Components/ui/badge'
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    entidades: Array,
    paises: Array,
    proximoNumero: Number,
})

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

function defaultForm(overrides = {}) {
    return {
        is_cliente: false,
        is_fornecedor: true,
        nif: '', nome: '', morada: '',
        codigo_postal: '', localidade: '',
        pais_id: null, telefone: '', telemovel: '',
        website: '', email: '', rgpd: false,
        observacoes: '', ativo: true,
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function openEdit(entidade) {
    editing.value = entidade
    Object.assign(editForm, defaultForm(entidade))
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/entidades', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    editForm.put(`/entidades/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(entidade) {
    if (confirm(`Eliminar "${entidade.nome}"?`)) {
        useForm({}).delete(`/entidades/${entidade.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Fornecedores</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Fornecedor
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Fornecedor — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <EntidadeForm :form="createForm" :paises="paises" :is-fornecedor="true" />
                        <DialogFooter class="mt-4">
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nº</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">NIF</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telefone</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telemóvel</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entidades.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum fornecedor registado.
                            </td>
                        </tr>
                        <tr v-for="e in entidades" :key="e.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ e.numero }}</td>
                            <td class="px-4 py-3">{{ e.nif ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ e.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.telefone ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.telemovel ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.email ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="e.ativo ? 'default' : 'secondary'">
                                    {{ e.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(e)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(e)">
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
            <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Fornecedor — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <EntidadeForm :form="editForm" :paises="paises" :is-fornecedor="true" />
                <DialogFooter class="mt-4">
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>