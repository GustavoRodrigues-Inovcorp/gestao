<script setup>
import { watch } from 'vue'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'

const props = defineProps({
    form: Object,
    paises: Array,
    isCliente: Boolean,
    isFornecedor: Boolean,
})

// Formata código postal automaticamente
watch(() => props.form.codigo_postal, (val) => {
    if (!val) return
    const digits = val.replace(/\D/g, '')
    if (digits.length > 4) {
        props.form.codigo_postal = digits.slice(0, 4) + '-' + digits.slice(4, 7)
    }
})

async function checkNif() {
    if (!props.form.nif) return
    const params = new URLSearchParams({ nif: props.form.nif })
    if (props.form.id) params.append('id', props.form.id)
    const res = await fetch(`/entidades/check-nif?${params}`)
    const data = await res.json()
    if (data.exists) {
        props.form.setError('nif', 'Este NIF já existe.')
    } else {
        props.form.clearErrors('nif')
    }
}
</script>

<template>
    <div class="space-y-4">
        <!-- Tipo -->
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <input type="checkbox" :checked="form.is_cliente" @change="form.is_cliente = $event.target.checked" id="is_cliente" class="rounded" />
                <Label for="is_cliente">Cliente</Label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" :checked="form.is_fornecedor" @change="form.is_fornecedor = $event.target.checked" id="is_fornecedor" class="rounded" />
                <Label for="is_fornecedor">Fornecedor</Label>
            </div>
        </div>

        <!-- NIF -->
        <div class="space-y-1">
            <Label>NIF</Label>
            <Input v-model="form.nif" @blur="checkNif" />
            <p v-if="form.errors.nif" class="text-xs text-destructive">{{ form.errors.nif }}</p>
        </div>

        <!-- Nome -->
        <div class="space-y-1">
            <Label>Nome *</Label>
            <Input v-model="form.nome" />
            <p v-if="form.errors.nome" class="text-xs text-destructive">{{ form.errors.nome }}</p>
        </div>

        <!-- Morada -->
        <div class="space-y-1">
            <Label>Morada</Label>
            <Input v-model="form.morada" />
        </div>

        <!-- Código Postal + Localidade -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <Label>Código Postal</Label>
                <Input v-model="form.codigo_postal" placeholder="0000-000" maxlength="8" />
                <p v-if="form.errors.codigo_postal" class="text-xs text-destructive">{{ form.errors.codigo_postal }}</p>
            </div>
            <div class="space-y-1">
                <Label>Localidade</Label>
                <Input v-model="form.localidade" />
            </div>
        </div>

        <!-- País -->
        <div class="space-y-1">
            <Label>País</Label>
            <Select v-model="form.pais_id">
                <SelectTrigger>
                    <SelectValue placeholder="Selecionar país..." />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="pais in paises" :key="pais.id" :value="pais.id">
                        {{ pais.nome }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Telefone + Telemóvel -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <Label>Telefone</Label>
                <Input v-model="form.telefone" />
            </div>
            <div class="space-y-1">
                <Label>Telemóvel</Label>
                <Input v-model="form.telemovel" />
            </div>
        </div>

        <!-- Website + Email -->
        <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
                <Label>Website</Label>
                <Input v-model="form.website" type="url" placeholder="https://" />
                <p v-if="form.errors.website" class="text-xs text-destructive">{{ form.errors.website }}</p>
            </div>
            <div class="space-y-1">
                <Label>Email</Label>
                <Input v-model="form.email" type="email" />
                <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
            </div>
        </div>

        <!-- RGPD -->
        <div class="space-y-1">
            <Label>Consentimento RGPD</Label>
            <Select v-model="form.rgpd">
                <SelectTrigger>
                    <SelectValue placeholder="Selecionar..." />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="true">Sim</SelectItem>
                    <SelectItem :value="false">Não</SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Observações -->
        <div class="space-y-1">
            <Label>Observações</Label>
            <Textarea v-model="form.observacoes" rows="3" />
        </div>

        <!-- Estado -->
        <div class="space-y-1">
            <Label>Estado</Label>
            <Select v-model="form.ativo">
                <SelectTrigger>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem :value="true">Ativo</SelectItem>
                    <SelectItem :value="false">Inativo</SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>