<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import ViewSheet from '@/Components/ui/sheet/ViewSheet.vue'

import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Plus, Trash2, Download, Search, FileText, FileImage, File } from 'lucide-vue-next'

const props = defineProps({
    ficheiros: Array,
    entidades: Array,
})

const { can } = useMenuPermissions('arquivo_digital')

const showCreate = ref(false)
const search = ref('')
const showView = ref(false)
const viewing = ref(null)

const filtered = computed(() => {
    if (!search.value) return props.ficheiros
    const q = search.value.toLowerCase()
    return props.ficheiros.filter(f =>
        f.nome?.toLowerCase().includes(q) ||
        f.entidade?.toLowerCase().includes(q) ||
        f.descricao?.toLowerCase().includes(q)
    )
})

const createForm = useForm({
    nome: '',
    ficheiro: null,
    entidade_id: null,
    descricao: '',
})

function onFileChange(e) {
    const file = e.target.files[0]
    if (file) {
        createForm.ficheiro = file
        if (!createForm.nome) {
            createForm.nome = file.name
        }
    }
}

function submitCreate() {
    if (!can('create')) return
    createForm.post('/arquivo-digital', {
        forceFormData: true,
        onSuccess: () => {
            showCreate.value = false
            createForm.reset()
        }
    })
}

function destroy(ficheiro) {
    if (!can('delete')) return
    if (confirm(`Eliminar "${ficheiro.nome}"?`)) {
        useForm({}).delete(`/arquivo-digital/${ficheiro.id}`)
    }
}

function fileIcon(mime) {
    if (!mime) return File
    if (mime.startsWith('image/')) return FileImage
    if (mime.includes('pdf') || mime.includes('word') || mime.includes('text')) return FileText
    return File
}

function fileIconColor(mime) {
    if (!mime) return 'text-muted-foreground'
    if (mime.startsWith('image/')) return 'text-blue-500'
    if (mime.includes('pdf')) return 'text-red-500'
    return 'text-muted-foreground'
}

function openView(entidade) {
    viewing.value = entidade
    showView.value = true
}

function viewFields(f) {
    if (!f) return []
    return [
        { label: 'Nome', value: f.nome },
        { label: 'Entidade', value: f.entidade },
        { label: 'Carregado por', value: f.user },
        { label: 'Data', value: f.data },
        { label: 'Tamanho', value: f.tamanho },
        { label: 'Descrição', value: f.descricao },
    ]
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Arquivo Digital</h1>
        </template>

        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="relative max-w-sm w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Pesquisar ficheiros..." class="pl-9" />
                </div>

                <Dialog v-if="can('create')" v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Carregar Ficheiro
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-md">
                        <DialogHeader>
                            <DialogTitle>Carregar Ficheiro</DialogTitle>
                        </DialogHeader>
                        <p v-if="createForm.errors.limite" class="rounded-md border border-destructive/30 bg-destructive/5 px-3 py-2 text-sm text-destructive">
                            {{ createForm.errors.limite }}
                        </p>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Ficheiro *</Label>
                                <Input type="file" @change="onFileChange" />
                                <p v-if="createForm.errors.ficheiro" class="text-xs text-destructive">{{ createForm.errors.ficheiro }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Nome *</Label>
                                <Input v-model="createForm.nome" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                            <div class="space-y-1">
                                <Label>Entidade</Label>
                                <Select v-model="createForm.entidade_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Associar a entidade..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem :value="null">Nenhuma</SelectItem>
                                        <SelectItem v-for="e in entidades" :key="e.id" :value="e.id">
                                            {{ e.nome }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-1">
                                <Label>Descrição</Label>
                                <Textarea v-model="createForm.descricao" rows="2" />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Carregar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="w-full overflow-x-auto rounded-lg border bg-card">
                <table class="w-full text-sm min-w-max">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Ficheiro</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Entidade</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Carregado por</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tamanho</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum ficheiro encontrado.
                            </td>
                        </tr>
                        <tr v-for="f in filtered" :key="f.id" class="border-b last:border-0 hover:bg-muted/30" @click="openView(f)">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <component :is="fileIcon(f.tipo_mime)" :class="['w-4 h-4', fileIconColor(f.tipo_mime)]" />
                                    <div>
                                        <p class="font-medium">{{ f.nome }}</p>
                                        <p v-if="f.descricao" class="text-xs text-muted-foreground truncate max-w-xs">{{ f.descricao }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ f.entidade ?? '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ f.user }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ f.data }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ f.tamanho }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1" @click.stop>
                                    <Button size="icon" variant="ghost" as="a" :href="f.url" title="Download">
                                        <Download class="w-4 h-4" />
                                    </Button>
                                    <Button v-if="can('delete')" size="icon" variant="ghost" class="text-destructive hover:text-destructive" @click="destroy(f)">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <ViewSheet
            :open="showView"
            :title="'Ficheiro — ' + (viewing?.nome ?? '')"
            :fields="viewFields(viewing)"
            :can-edit="can('update')"
            :can-delete="can('delete')"
            @update:open="showView = $event"
            @edit="() => { showView = false; openEdit(viewing) }"
            @delete="() => { showView = false; destroy(viewing) }"
        />
    </AppLayout>
</template>