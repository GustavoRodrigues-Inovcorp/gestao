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

const props = defineProps({
    roles: Array,
    menus: Array,
    menuLabels: Object,
})

const actions = ['create', 'read', 'update', 'delete']
const actionLabels = { create: 'Criar', read: 'Ver', update: 'Editar', delete: 'Eliminar' }

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)

const createForm = useForm({ name: '', permissions: [] })
const editForm = useForm({ name: '', permissions: [], ativo: true })

function permKey(menu, action) {
    return `${menu}.${action}`
}

function togglePerm(form, menu, action) {
    const key = permKey(menu, action)
    const idx = form.permissions.indexOf(key)
    if (idx === -1) form.permissions.push(key)
    else form.permissions.splice(idx, 1)
}

function hasAll(form, menu) {
    return actions.every(a => form.permissions.includes(permKey(menu, a)))
}

function toggleAll(form, menu) {
    if (hasAll(form, menu)) {
        actions.forEach(a => {
            const idx = form.permissions.indexOf(permKey(menu, a))
            if (idx !== -1) form.permissions.splice(idx, 1)
        })
    } else {
        actions.forEach(a => {
            const key = permKey(menu, a)
            if (!form.permissions.includes(key)) form.permissions.push(key)
        })
    }
}

function openEdit(role) {
    editing.value = role
    editForm.name = role.name
    editForm.permissions = [...role.permissions]
    editForm.ativo = role.ativo
    showEdit.value = true
}

function submitCreate() {
    createForm.post('/acessos/permissoes', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    editForm.put(`/acessos/permissoes/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(role) {
    if (confirm(`Eliminar grupo "${role.name}"?`)) {
        useForm({}).delete(`/acessos/permissoes/${role.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Gestão de Acessos — Permissões</h1>
        </template>

        <div class="space-y-4">
            <div class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Grupo
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Novo Grupo de Permissões</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-4 py-2">
                            <div class="space-y-1">
                                <Label>Nome do Grupo *</Label>
                                <Input v-model="createForm.name" placeholder="ex: Administrador" />
                                <p v-if="createForm.errors.name" class="text-xs text-destructive">{{ createForm.errors.name }}</p>
                            </div>
                            <div class="rounded-md border overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50 border-b">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-medium text-muted-foreground">Menu</th>
                                            <th v-for="a in actions" :key="a" class="px-3 py-2 text-center font-medium text-muted-foreground">
                                                {{ actionLabels[a] }}
                                            </th>
                                            <th class="px-3 py-2 text-center font-medium text-muted-foreground">Todos</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="menu in menus" :key="menu" class="border-b last:border-0 hover:bg-muted/20">
                                            <td class="px-3 py-2 font-medium">{{ menuLabels[menu] }}</td>
                                            <td v-for="a in actions" :key="a" class="px-3 py-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    :checked="createForm.permissions.includes(permKey(menu, a))"
                                                    @change="togglePerm(createForm, menu, a)"
                                                    class="rounded"
                                                />
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    :checked="hasAll(createForm, menu)"
                                                    @change="toggleAll(createForm, menu)"
                                                    class="rounded"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome do Grupo</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Utilizadores</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="roles.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum grupo registado.
                            </td>
                        </tr>
                        <tr v-for="role in roles" :key="role.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium">{{ role.name }}</td>
                            <td class="px-4 py-3">{{ role.users_count }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="role.ativo ? 'default' : 'secondary'">
                                    {{ role.ativo ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button size="icon" variant="ghost" @click="openEdit(role)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(role)">
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
            <DialogContent class="max-w-2xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Editar Grupo</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label>Nome do Grupo *</Label>
                        <Input v-model="editForm.name" />
                    </div>
                    <div class="rounded-md border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Menu</th>
                                    <th v-for="a in actions" :key="a" class="px-3 py-2 text-center font-medium text-muted-foreground">
                                        {{ actionLabels[a] }}
                                    </th>
                                    <th class="px-3 py-2 text-center font-medium text-muted-foreground">Todos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="menu in menus" :key="menu" class="border-b last:border-0 hover:bg-muted/20">
                                    <td class="px-3 py-2 font-medium">{{ menuLabels[menu] }}</td>
                                    <td v-for="a in actions" :key="a" class="px-3 py-2 text-center">
                                        <input
                                            type="checkbox"
                                            :checked="editForm.permissions.includes(permKey(menu, a))"
                                            @change="togglePerm(editForm, menu, a)"
                                            class="rounded"
                                        />
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <input
                                            type="checkbox"
                                            :checked="hasAll(editForm, menu)"
                                            @change="toggleAll(editForm, menu)"
                                            class="rounded"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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