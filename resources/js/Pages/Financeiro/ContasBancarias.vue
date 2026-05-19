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

const props = defineProps({ contas: Array })

const { can } = useMenuPermissions('financeiro')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

function defaultForm(overrides = {}) {
    return {
        banco: '', iban: '', swift: '',
        titular: '', saldo: 0, ativo: true,
        ...overrides,
    }
}

const createForm = useForm(defaultForm())
const editForm = useForm(defaultForm())

function openEdit(conta) {
    if (!can('update')) return
    editing.value = conta
    Object.assign(editForm, defaultForm(conta))
    showEdit.value = true
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/financeiro/contas-bancarias', {
        onSuccess: () => { showCreate.value = false; Object.assign(createForm, defaultForm()) }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/financeiro/contas-bancarias/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(conta) {
    if (!can('delete')) return
    if (confirm(`Eliminar conta "${conta.banco} - ${conta.iban}"?`)) {
        useForm({}).delete(`/financeiro/contas-bancarias/${conta.id}`)
    }
}

function formatPrice(val) {
    return Number(val).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' })
}

function formatIban(iban) {
    return iban.replace(/(.{4})/g, '$1 ').trim()
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Financeiro — Contas Bancárias</h1>
        </template>

        <div class="space-y-4">
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Nova Conta
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-md">
                        <DialogHeader>
                            <DialogTitle>Nova Conta Bancária</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Banco *</Label>
                                <Input v-model="createForm.banco" placeholder="ex: Caixa Geral de Depósitos" />
                                <p v-if="createForm.errors.banco" class="text-xs text-destructive">{{ createForm.errors.banco }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Titular *</Label>
                                <Input v-model="createForm.titular" />
                                <p v-if="createForm.errors.titular" class="text-xs text-destructive">{{ createForm.errors.titular }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>IBAN *</Label>
                                <Input v-model="createForm.iban" placeholder="PT50..." class="font-mono" />
                                <p v-if="createForm.errors.iban" class="text-xs text-destructive">{{ createForm.errors.iban }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>SWIFT / BIC</Label>
                                <Input v-model="createForm.swift" class="font-mono" />
                            </div>
                            <div class="space-y-1">
                                <Label>Saldo Inicial</Label>
                                <Input v-model="createForm.saldo" type="number" step="0.01" />
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" v-model="createForm.ativo" id="ativo_create" class="rounded" />
                                <Label for="ativo_create">Ativa</Label>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Guardar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Cards das contas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-if="contas.length === 0" class="col-span-3 rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                    Nenhuma conta bancária registada.
                </div>
                <div v-for="conta in contas" :key="conta.id" class="rounded-lg border bg-card p-5 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold">{{ conta.banco }}</p>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ conta.titular }}</p>
                        </div>
                        <Badge :variant="conta.ativo ? 'default' : 'secondary'">
                            {{ conta.ativo ? 'Ativa' : 'Inativa' }}
                        </Badge>
                    </div>
                    <div>
                        <p class="font-mono text-xs text-muted-foreground">{{ formatIban(conta.iban) }}</p>
                        <p v-if="conta.swift" class="font-mono text-xs text-muted-foreground">{{ conta.swift }}</p>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t">
                        <div>
                            <p class="text-xs text-muted-foreground">Saldo</p>
                            <p class="text-lg font-bold" :class="conta.saldo >= 0 ? 'text-green-600' : 'text-destructive'">
                                {{ formatPrice(conta.saldo) }}
                            </p>
                        </div>
                        <div class="flex gap-1">
                            <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(conta)">
                                <Pencil class="w-4 h-4" />
                            </Button>
                            <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(conta)">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog Editar -->
        <Dialog v-if="can('update')" v-model:open="showEdit">
            <DialogContent class="max-w-md">
                <DialogHeader>
                    <DialogTitle>Editar Conta Bancária</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Banco *</Label>
                        <Input v-model="editForm.banco" />
                    </div>
                    <div class="space-y-1">
                        <Label>Titular *</Label>
                        <Input v-model="editForm.titular" />
                    </div>
                    <div class="space-y-1">
                        <Label>IBAN *</Label>
                        <Input v-model="editForm.iban" class="font-mono" />
                        <p v-if="editForm.errors.iban" class="text-xs text-destructive">{{ editForm.errors.iban }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>SWIFT / BIC</Label>
                        <Input v-model="editForm.swift" class="font-mono" />
                    </div>
                    <div class="space-y-1">
                        <Label>Saldo</Label>
                        <Input v-model="editForm.saldo" type="number" step="0.01" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="editForm.ativo" id="ativo_edit" class="rounded" />
                        <Label for="ativo_edit">Ativa</Label>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>