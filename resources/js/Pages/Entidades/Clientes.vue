<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import EntidadeForm from '@/Components/Entidades/EntidadeForm.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
import { Button } from '@/Components/ui/button'
import ViewSheet from '@/Components/ui/sheet/ViewSheet.vue'
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

const { can } = useMenuPermissions('clientes')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)
const showView = ref(false)
const viewing = ref(null)

function defaultForm(overrides = {}) {
    return {
        is_cliente: true,
        is_fornecedor: false,
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
    if (!can('update')) return
    editing.value = entidade
    Object.assign(editForm, defaultForm(entidade))
    showEdit.value = true
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/entidades', {
        onSuccess: () => { showCreate.value = false; createForm.reset(); Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/entidades/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(entidade) {
    if (!can('delete')) return
    if (confirm(`Eliminar "${entidade.nome}"?`)) {
        useForm({}).delete(`/entidades/${entidade.id}`)
    }
}

function openView(entidade) {
    viewing.value = entidade
    showView.value = true
}

function viewFields(e) {
    if (!e) return []
    return [
        { label: 'Número', value: e.numero },
        { label: 'NIF', value: e.nif },
        { label: 'Nome', value: e.nome },
        { label: 'Morada', value: e.morada },
        { label: 'Código Postal', value: e.codigo_postal },
        { label: 'Localidade', value: e.localidade },
        { label: 'País', value: e.pais?.nome },
        { label: 'Telefone', value: e.telefone },
        { label: 'Telemóvel', value: e.telemovel },
        { label: 'Website', value: e.website, type: 'link' },
        { label: 'Email', value: e.email },
        { label: 'RGPD', value: e.rgpd ? 'Sim' : 'Não' },
        { label: 'Observações', value: e.observacoes },
        { label: 'Estado', value: e.ativo ? 'Ativo' : 'Inativo' },
    ]
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Clientes</h1>
        </template>

        <div class="space-y-4">
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Cliente
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Cliente — Nº {{ proximoNumero }}</DialogTitle>
                        </DialogHeader>
                        <p v-if="createForm.errors.limite" class="rounded-md border border-destructive/30 bg-destructive/5 px-3 py-2 text-sm text-destructive">
                            {{ createForm.errors.limite }}
                        </p>
                        <EntidadeForm :form="createForm" :paises="paises" :is-cliente="true" />
                        <DialogFooter class="mt-4">
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">NIF</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telefone</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telemóvel</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Website</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="entidades.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum cliente registado.
                            </td>
                        </tr>
                        <tr v-for="e in entidades" :key="e.id" class="border-b last:border-0 hover:bg-muted/30" @click="openView(e)">
                            <td class="px-4 py-3 font-muted-foreground">{{ e.nif }}</td>
                            <td class="px-4 py-3 font-medium">{{ e.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.telefone ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.telemovel ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.website ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ e.email ?? '—' }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex justify-end gap-2">
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

        <Dialog v-if="can('update')" v-model:open="showEdit">
            <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Cliente — Nº {{ editing?.numero }}</DialogTitle>
                </DialogHeader>
                <EntidadeForm :form="editForm" :paises="paises" :is-cliente="true" />
                <DialogFooter class="mt-4">
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <ViewSheet
            :open="showView"
            :title="'Cliente — ' + (viewing?.nome ?? '')"
            :fields="viewFields(viewing)"
            :can-edit="can('update')"
            :can-delete="can('delete')"
            @update:open="showView = $event"
            @edit="() => { showView = false; openEdit(viewing) }"
            @delete="() => { showView = false; destroy(viewing) }"
        />
    </AppLayout>
</template>