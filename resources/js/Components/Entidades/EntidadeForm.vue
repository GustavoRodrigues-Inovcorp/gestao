<script setup>
import { ref, watch } from 'vue'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import {
    Select, SelectContent, SelectItem,
    SelectTrigger, SelectValue,
} from '@/Components/ui/select'
import { Textarea } from '@/Components/ui/textarea'
import { Search, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    form: Object,
    paises: Array,
    isCliente: Boolean,
    isFornecedor: Boolean,
})

const viesLoading = ref(false)
const viesError = ref('')
const viesSuccess = ref('')

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

async function lookupVies() {
    if (!props.form.nif || props.form.nif.length < 9) {
        viesError.value = 'Introduz um NIF válido primeiro.'
        return
    }

    viesError.value = ''
    viesSuccess.value = ''
    viesLoading.value = true

    try {
        // Extrai código do país (primeiras 2 letras se existirem, senão usa PT)
        const nif = props.form.nif.trim()
        const countryMatch = nif.match(/^([A-Za-z]{2})/)
        const countryCode = countryMatch ? countryMatch[1].toUpperCase() : 'PT'
        const vatNumber = nif.replace(/^[A-Za-z]{2}/, '')

        const params = new URLSearchParams({ country_code: countryCode, vat_number: vatNumber })
        const res = await fetch(`/vies/lookup?${params}`)
        const data = await res.json()

        if (data.error) {
            viesError.value = data.error
            return
        }

        if (data.nome) props.form.nome = data.nome
        if (data.morada) props.form.morada = data.morada
        if (data.localidade) props.form.localidade = data.localidade

        viesSuccess.value = 'Dados preenchidos automaticamente.'
    } catch (e) {
        viesError.value = 'Erro ao consultar o VIES.'
    } finally {
        viesLoading.value = false
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

        <!-- NIF + VIES -->
        <div class="space-y-1">
            <Label>NIF</Label>
            <div class="flex gap-2">
                <Input v-model="form.nif" @blur="checkNif" class="flex-1" placeholder="ex: PT501234567" />
                <Button type="button" variant="outline" size="sm" @click="lookupVies" :disabled="viesLoading" class="gap-2 shrink-0">
                    <Loader2 v-if="viesLoading" class="w-4 h-4 animate-spin" />
                    <Search v-else class="w-4 h-4" />
                    VIES
                </Button>
            </div>
            <p v-if="form.errors.nif" class="text-xs text-destructive">{{ form.errors.nif }}</p>
            <p v-if="viesError" class="text-xs text-destructive">{{ viesError }}</p>
            <p v-if="viesSuccess" class="text-xs text-green-600">{{ viesSuccess }}</p>
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
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                    <SelectItem :value="true">Ativo</SelectItem>
                    <SelectItem :value="false">Inativo</SelectItem>
                </SelectContent>
            </Select>
        </div>
    </div>
</template>