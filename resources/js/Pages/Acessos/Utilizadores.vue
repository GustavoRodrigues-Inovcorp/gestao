<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import ViewSheet from '@/Components/ui/sheet/ViewSheet.vue'

import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps({
    utilizadores: Array,
    roles: Array,
})

const { can } = useMenuPermissions('utilizadores')

const showCreate = ref(false)
const showEdit = ref(false)
const editing = ref(null)
const showView = ref(false)
const viewing = ref(null)

const createForm = useForm({
    name: '', email: '', phone: '',
    password: '', password_confirmation: '', role: '',
})

const editForm = useForm({
    name: '', email: '', phone: '',
    active: true, role: '',
})

function openEdit(user) {
    if (!can('update')) return
    editing.value = user
    editForm.name = user.name
    editForm.email = user.email
    editForm.phone = user.phone ?? ''
    editForm.active = user.active
    editForm.role = user.role ?? ''
    showEdit.value = true
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/acessos/utilizadores', {
        onSuccess: () => { showCreate.value = false; createForm.reset() }
    })
}

function submitEdit() {
    if (!can('update')) return
    editForm.put(`/acessos/utilizadores/${editing.value.id}`, {
        onSuccess: () => { showEdit.value = false }
    })
}

function destroy(user) {
    if (!can('delete')) return
    if (confirm(`Eliminar utilizador "${user.name}"?`)) {
        useForm({}).delete(`/acessos/utilizadores/${user.id}`)
    }
}

function openView(entidade) {
    viewing.value = entidade
    showView.value = true
}

function viewFields(u) {
    if (!u) return []
    return [
        { label: 'Nome', value: u.name },
        { label: 'Email', value: u.email },
        { label: 'Telemóvel', value: u.phone },
        { label: 'Grupo de Permissões', value: u.role },
        { label: 'Estado', value: u.active ? 'Ativo' : 'Inativo' },
    ]
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Gestão de Acessos — Utilizadores</h1>
        </template>

        <div class="space-y-4">
            <div v-if="can('create')" class="flex justify-end">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Utilizador
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-lg">
                        <DialogHeader>
                            <DialogTitle>Novo Utilizador</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.name" />
                                <p v-if="createForm.errors.name" class="text-xs text-destructive">{{ createForm.errors.name }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Email *</Label>
                                <Input v-model="createForm.email" type="email" />
                                <p v-if="createForm.errors.email" class="text-xs text-destructive">{{ createForm.errors.email }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Telemóvel</Label>
                                <Input v-model="createForm.phone" />
                            </div>
                            <div class="space-y-1">
                                <Label>Grupo de Permissões</Label>
                                <Select v-model="createForm.role">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecionar grupo..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                            {{ role.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1">
                                <Label>Password *</Label>
                                <Input v-model="createForm.password" type="password" />
                                <p v-if="createForm.errors.password" class="text-xs text-destructive">{{ createForm.errors.password }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Confirmar Password *</Label>
                                <Input v-model="createForm.password_confirmation" type="password" />
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
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Telemóvel</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Grupo</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="utilizadores.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum utilizador registado.
                            </td>
                        </tr>
                        <tr v-for="user in utilizadores" :key="user.id" class="border-b last:border-0 hover:bg-muted/30" @click="openView(user)">
                            <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ user.email }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ user.phone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <Badge variant="outline">{{ user.role ?? '—' }}</Badge>
                            </td>
                            <td class="px-4 py-3">
                                <Badge :variant="user.active ? 'default' : 'secondary'">
                                    {{ user.active ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2" @click.stop>
                                    <Button v-if="can('update')" size="icon" variant="ghost" @click="openEdit(user)">
                                        <Pencil class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(user)">
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
        <Dialog v-if="can('update')" v-model:open="showEdit">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Editar Utilizador</DialogTitle>
                </DialogHeader>
                <div class="space-y-3 py-2">
                    <div class="space-y-1">
                        <Label>Nome *</Label>
                        <Input v-model="editForm.name" />
                    </div>
                    <div class="space-y-1">
                        <Label>Email *</Label>
                        <Input v-model="editForm.email" type="email" />
                    </div>
                    <div class="space-y-1">
                        <Label>Telemóvel</Label>
                        <Input v-model="editForm.phone" />
                    </div>
                    <div class="space-y-1">
                        <Label>Grupo de Permissões</Label>
                        <Select v-model="editForm.role">
                            <SelectTrigger>
                                <SelectValue placeholder="Selecionar grupo..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" v-model="editForm.active" id="active" class="rounded" />
                        <Label for="active">Ativo</Label>
                    </div>
                </div>
                <DialogFooter>
                    <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <ViewSheet
            :open="showView"
            :title="'Utilizador — ' + (viewing?.name ?? '')"
            :fields="viewFields(viewing)"
            :can-edit="can('update')"
            :can-delete="can('delete')"
            @update:open="showView = $event"
            @edit="() => { showView = false; openEdit(viewing) }"
            @delete="() => { showView = false; destroy(viewing) }"
        />
    </AppLayout>
</template>