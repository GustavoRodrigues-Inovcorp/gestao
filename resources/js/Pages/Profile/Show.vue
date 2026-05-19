<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Button } from '@/Components/ui/button'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'

const page = usePage()
const user = page.props.auth.user

// Atualizar perfil
const profileForm = useForm({
    name: user.name,
    email: user.email,
})

function updateProfile() {
    profileForm.patch('/profile', {
        preserveScroll: true,
    })
}

// Alterar password
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

function updatePassword() {
    passwordForm.put('/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    })
}

// 2FA
const twoFactorEnabled = ref(!!user.two_factor_confirmed_at)
const showQrCode = ref(false)
const qrCode = ref(null)
const recoveryCodes = ref([])
const confirmationCode = ref('')
const twoFactorForm = useForm({})

async function enableTwoFactor() {
    await twoFactorForm.post('/user/two-factor-authentication')
    const qrResponse = await fetch('/user/two-factor-qr-code')
    const qrData = await qrResponse.json()
    qrCode.value = qrData.svg
    showQrCode.value = true
}

async function confirmTwoFactor() {
    await useForm({ code: confirmationCode.value }).post('/user/confirmed-two-factor-authentication', {
        onSuccess: () => {
            twoFactorEnabled.value = true
            showQrCode.value = false
        }
    })
}

async function disableTwoFactor() {
    await twoFactorForm.delete('/user/two-factor-authentication', {
        onSuccess: () => {
            twoFactorEnabled.value = false
            showQrCode.value = false
        }
    })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Perfil</h1>
        </template>

        <div class="space-y-6">

            <div class="flex grid gap-6 mt-6 md:grid-cols-2">
                <!-- Dados do Perfil -->
                <div class="rounded-lg border bg-card p-6 space-y-4">
                    <h2 class="font-semibold text-base">Informação do Perfil</h2>
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <Label>Nome</Label>
                            <Input v-model="profileForm.name" />
                            <p v-if="profileForm.errors.name" class="text-xs text-destructive">{{ profileForm.errors.name }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label>Email</Label>
                            <Input v-model="profileForm.email" type="email" />
                            <p v-if="profileForm.errors.email" class="text-xs text-destructive">{{ profileForm.errors.email }}</p>
                        </div>
                    </div>
                    <Button @click="updateProfile" :disabled="profileForm.processing">
                        Guardar
                    </Button>
                </div>

                <!-- Alterar Password -->
                <div class="rounded-lg border bg-card p-6 space-y-4">
                    <h2 class="font-semibold text-base">Alterar Password</h2>
                    <div class="space-y-3">
                        <div class="space-y-1">
                            <Label>Password Atual</Label>
                            <Input v-model="passwordForm.current_password" type="password" />
                        </div>
                        <div class="space-y-1">
                            <Label>Nova Password</Label>
                            <Input v-model="passwordForm.password" type="password" />
                        </div>
                        <div class="space-y-1">
                            <Label>Confirmar Password</Label>
                            <Input v-model="passwordForm.password_confirmation" type="password" />
                        </div>
                    </div>
                    <Button @click="updatePassword" :disabled="passwordForm.processing">
                        Atualizar Password
                    </Button>
                </div>
            </div>

            <!-- 2FA -->
            <div class="rounded-lg border bg-card p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-base">Autenticação de Dois Fatores</h2>
                        <p class="text-sm text-muted-foreground mt-0.5">
                            Adiciona segurança extra à tua conta.
                        </p>
                    </div>
                    <span :class="[
                        'text-xs px-2 py-1 rounded-full font-medium',
                        twoFactorEnabled
                            ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                            : 'bg-muted text-muted-foreground'
                    ]">
                        {{ twoFactorEnabled ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>

                <!-- QR Code -->
                <div v-if="showQrCode" class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        Lê o código QR com a tua app de autenticação (Google Authenticator, Authy, etc.) e introduz o código gerado.
                    </p>
                    <div class="bg-white p-4 rounded-md w-fit" v-html="qrCode"></div>
                    <div class="space-y-1">
                        <Label>Código de Confirmação</Label>
                        <Input v-model="confirmationCode" placeholder="000000" class="w-40" />
                    </div>
                    <Button @click="confirmTwoFactor">Confirmar</Button>
                </div>

                <div v-if="!twoFactorEnabled && !showQrCode">
                    <Button @click="enableTwoFactor" variant="outline">
                        Ativar 2FA
                    </Button>
                </div>

                <div v-if="twoFactorEnabled">
                    <Button @click="disableTwoFactor" variant="destructive">
                        Desativar 2FA
                    </Button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>