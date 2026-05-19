<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useMenuPermissions } from '@/composables/useMenuPermissions'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'

const props = defineProps({
    empresa: Object,
})

const { can } = useMenuPermissions('configuracoes')

const form = useForm({
    nome: props.empresa?.nome ?? '',
    morada: props.empresa?.morada ?? '',
    codigo_postal: props.empresa?.codigo_postal ?? '',
    localidade: props.empresa?.localidade ?? '',
    nif: props.empresa?.nif ?? '',
    logotipo: null,
})

function submit() {
    if (!can('update')) return
    form.post('/configuracoes/empresa', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Empresa</h1>
        </template>

        <div class="max-w-2xl">
            <div class="rounded-lg border bg-card p-6 space-y-5">

                <div class="space-y-1">
                    <Label>Logotipo</Label>
                    <div v-if="empresa?.logotipo" class="mb-2">
                        <img :src="`/empresa/logotipo?v=${empresa?.updated_at ?? ''}`" alt="Logo" class="h-12 object-contain" />
                    </div>
                    <Input
                        type="file"
                        accept="image/*"
                        @change="e => form.logotipo = e.target.files[0]"
                    />
                    <p class="text-xs text-muted-foreground">PNG, JPG até 2MB</p>
                </div>

                <div class="space-y-1">
                    <Label>Nome *</Label>
                    <Input v-model="form.nome" />
                    <p v-if="form.errors.nome" class="text-xs text-destructive">{{ form.errors.nome }}</p>
                </div>

                <div class="space-y-1">
                    <Label>Morada</Label>
                    <Input v-model="form.morada" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <Label>Código Postal</Label>
                        <Input v-model="form.codigo_postal" placeholder="0000-000" />
                        <p v-if="form.errors.codigo_postal" class="text-xs text-destructive">{{ form.errors.codigo_postal }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label>Localidade</Label>
                        <Input v-model="form.localidade" />
                    </div>
                </div>
                
                <div class="space-y-1">
                    <Label>NIF</Label>
                    <Input v-model="form.nif" />
                    <p v-if="form.errors.nif" class="text-xs text-destructive">{{ form.errors.nif }}</p>
                </div>

                <div v-if="can('update')" class="flex items-center gap-3 pt-2">
                    <Button @click="submit" :disabled="form.processing">
                        Guardar
                    </Button>
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600">
                        Guardado com sucesso.
                    </span>
                </div>

            </div>
        </div>
    </AppLayout>
</template>