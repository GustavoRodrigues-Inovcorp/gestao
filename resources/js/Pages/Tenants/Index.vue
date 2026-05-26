<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Badge } from '@/Components/ui/badge'
import {
    Dialog, DialogContent, DialogHeader,
    DialogTitle, DialogTrigger, DialogFooter,
} from '@/Components/ui/dialog'
import { Plus, Building2, Check, Trash2, ArrowRight, Pencil } from 'lucide-vue-next'

const props = defineProps({ tenants: Array })
const page = usePage()
const tenantAtual = computed(() => page.props.tenant_atual)
const isAdmin = computed(() => Boolean(page.props.auth?.is_admin) || (page.props.auth?.roles ?? []).includes('admin') || (page.props.auth?.roles ?? []).includes('Administrador'))

const showCreate = ref(false)
const showEdit = ref(false)
const tenantEmEdicao = ref(null)
const createForm = useForm({ nome: '' })
const editForm = useForm({ nome: '', slug: '', estado: 'ativo', logotipo: null })

function submitCreate() {
    createForm.post('/tenants', {
        onSuccess: () => {
            showCreate.value = false
            createForm.reset()
            router.visit('/onboarding')
        }
    })
}

function openEdit(tenant) {
    tenantEmEdicao.value = tenant
    editForm.nome = tenant.nome
    editForm.slug = tenant.slug
    editForm.estado = tenant.estado ?? 'ativo'
    editForm.logotipo = null
    showEdit.value = true
}

function submitEdit() {
    if (!tenantEmEdicao.value) return

    editForm.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(`/tenants/${tenantEmEdicao.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false
            tenantEmEdicao.value = null
        }
    })
}

function switchTenant(id) {
    router.post(`/tenants/${id}/switch`)
}

function destroy(tenant) {
    if (confirm(`Eliminar workspace "${tenant.nome}"? Esta ação é irreversível.`)) {
        router.delete(`/tenants/${tenant.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Workspaces</h1>
        </template>

        <div class="w-full max-w-none space-y-4">
            <div v-if="tenantAtual" class="rounded-lg border bg-card p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-muted-foreground">Workspace ativo</p>
                        <h2 class="text-lg font-semibold">{{ tenantAtual.nome }}</h2>
                        <p class="text-sm text-muted-foreground">Slug: {{ tenantAtual.slug }}</p>
                    </div>
                    <Badge variant="default">Ativo</Badge>
                </div>
                <p class="mt-3 text-sm text-muted-foreground">
                    A configuração inicial e o branding serão tratados no onboarding do tenant.
                </p>
            </div>

            <div class="flex justify-end" v-if="isAdmin">
                <Dialog v-model:open="showCreate">
                    <DialogTrigger as-child>
                        <Button size="sm" class="gap-2">
                            <Plus class="w-4 h-4" /> Novo Workspace
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-w-md">
                        <DialogHeader>
                            <DialogTitle>Criar Workspace</DialogTitle>
                        </DialogHeader>
                        <div class="space-y-3 py-2">
                            <div class="space-y-1">
                                <Label>Nome do Workspace *</Label>
                                <Input v-model="createForm.nome" placeholder="ex: Empresa Lda" />
                                <p v-if="createForm.errors.nome" class="text-xs text-destructive">{{ createForm.errors.nome }}</p>
                            </div>
                        </div>
                        <DialogFooter>
                            <Button @click="submitCreate" :disabled="createForm.processing">Criar</Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <Dialog v-model:open="showEdit">
                <DialogContent class="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Editar Workspace</DialogTitle>
                    </DialogHeader>
                    <div class="space-y-3 py-2">
                        <div class="space-y-1">
                            <Label>Nome do Workspace *</Label>
                            <Input v-model="editForm.nome" placeholder="ex: Empresa Lda" />
                            <p v-if="editForm.errors.nome" class="text-xs text-destructive">{{ editForm.errors.nome }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Slug *</Label>
                            <Input v-model="editForm.slug" placeholder="ex: empresa-lda" />
                            <p v-if="editForm.errors.slug" class="text-xs text-destructive">{{ editForm.errors.slug }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Estado *</Label>
                            <select v-model="editForm.estado" class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm ring-offset-background">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="suspenso">Suspenso</option>
                            </select>
                            <p v-if="editForm.errors.estado" class="text-xs text-destructive">{{ editForm.errors.estado }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Logotipo</Label>
                            <Input type="file" accept="image/*" @change="(event) => { editForm.logotipo = event.target.files?.[0] ?? null }" />
                            <p class="text-xs text-muted-foreground">
                                {{ tenantEmEdicao?.tem_logotipo ? 'Já existe um logotipo definido. Substituir irá atualizar o branding atual.' : 'Opcional. Formatos de imagem até 2MB.' }}
                            </p>
                            <p v-if="editForm.errors.logotipo" class="text-xs text-destructive">{{ editForm.errors.logotipo }}</p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="showEdit = false">Cancelar</Button>
                        <Button @click="submitEdit" :disabled="editForm.processing">Guardar</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <div class="space-y-3">
                <div
                    v-for="tenant in tenants"
                    :key="tenant.id"
                    :class="[
                        'w-full rounded-lg border bg-card p-4 flex items-center justify-between gap-4 transition-colors',
                        tenant.ativo ? 'border-primary/50 bg-primary/5' : ''
                    ]"
                >
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', tenant.ativo ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground']">
                            <Building2 class="w-5 h-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-sm">{{ tenant.nome }}</p>
                                <Check v-if="tenant.ativo" class="w-4 h-4 text-primary" />
                            </div>
                            <p class="text-xs text-muted-foreground">{{ tenant.slug }}</p>
                        </div>
                    </div>

                    <!-- Dentro de cada card de tenant -->
                    <div class="flex items-center gap-2 flex-wrap justify-end">
                        <Button
                            v-if="!tenant.ativo"
                            size="sm"
                            variant="outline"
                            @click="switchTenant(tenant.id)"
                            class="gap-1 h-8"
                        >
                            <ArrowRight class="w-3.5 h-3.5" />
                            Entrar
                        </Button>

                        <span v-else class="flex items-center gap-1 text-xs text-primary font-medium px-2">
                            <Check class="w-3.5 h-3.5" />
                            Ativo
                        </span>

                        <!-- Onboarding se ainda não completado -->
                        <Button
                            v-if="tenant.ativo && !tenant.onboarding_completo"
                            size="sm"
                            variant="ghost"
                            as="a"
                            href="/onboarding"
                            class="gap-1 h-8 text-muted-foreground"
                        >
                            Configurar
                        </Button>

                        <Button
                            v-if="isAdmin"
                            size="icon"
                            variant="ghost"
                            class="h-8 w-8 text-muted-foreground hover:text-foreground"
                            @click="openEdit(tenant)"
                        >
                            <Pencil class="w-4 h-4" />
                        </Button>

                        <Button
                            v-if="isAdmin"
                            size="icon"
                            variant="ghost"
                            class="h-8 w-8 text-destructive hover:text-destructive"
                            @click="destroy(tenant)"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </div>

                <div v-if="tenants.length === 0" class="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
                    <Building2 class="w-8 h-8 mx-auto mb-3 opacity-40" />
                    <p class="text-sm">Sem workspaces. Cria o primeiro!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
