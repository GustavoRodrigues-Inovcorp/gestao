<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/composables/useDarkMode'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import { Shield, Sun, Moon, Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'

const page = usePage()
const empresa = computed(() => page.props.empresa)
const { isDark } = useDarkMode()
const showPassword = ref(false)

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Iniciar Sessão" />

    <div class="min-h-screen bg-background flex">

        <!-- Lado esquerdo — Decorativo -->
        <div class="hidden lg:flex lg:w-1/2 bg-primary flex-col justify-between p-12">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary-foreground/10 flex items-center justify-center overflow-hidden">
                    <img
                        v-if="empresa?.logotipo"
                        :src="'/empresa/logotipo'"
                        alt="Logo"
                        class="w-full h-full object-cover"
                    />
                    <Shield v-else class="w-5 h-5 text-primary-foreground" />
                </div>
                <span class="text-primary-foreground font-semibold">{{ empresa?.nome ?? 'Gestão' }}</span>
            </div>

            <div class="space-y-4">
                <h2 class="text-3xl font-bold text-primary-foreground leading-tight">
                    Plataforma de Gestão Empresarial
                </h2>
                <p class="text-primary-foreground/70 text-sm leading-relaxed max-w-sm">
                    Gere clientes, propostas, encomendas, financeiro e muito mais — tudo num só lugar, de forma simples e segura.
                </p>
            </div>

            <div class="flex items-center gap-6 text-primary-foreground/50 text-xs">
                <span>Seguro</span>
                <span>•</span>
                <span>Fiável</span>
                <span>•</span>
                <span>Eficiente</span>
            </div>
        </div>

        <!-- Lado direito — Formulário -->
        <div class="flex-1 flex flex-col">

            <!-- Topbar -->
            <div class="flex items-center justify-between px-8 py-4">
                <!-- Logo mobile -->
                <div class="flex items-center gap-2 lg:hidden">
                    <div class="w-7 h-7 rounded-md bg-primary flex items-center justify-center overflow-hidden">
                        <img
                            v-if="empresa?.logotipo"
                            :src="'/empresa/logotipo'"
                            alt="Logo"
                            class="w-full h-full object-cover"
                        />
                        <Shield v-else class="w-4 h-4 text-primary-foreground" />
                    </div>
                    <span class="font-semibold text-sm">{{ empresa?.nome ?? 'Gestão' }}</span>
                </div>
                <div class="lg:ml-auto flex items-center gap-3">
                    <button
                        @click="isDark = !isDark"
                        class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>
                    <Link
                        href="/register"
                        class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                    >
                        Criar conta
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="flex-1 flex items-center justify-center px-8">
                <div class="w-full max-w-sm space-y-6">

                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold">Iniciar Sessão</h1>
                        <p class="text-sm text-muted-foreground">Introduz as tuas credenciais para continuar.</p>
                    </div>

                    <div v-if="status" class="text-sm text-green-600 bg-green-50 dark:bg-green-900/20 rounded-md px-3 py-2">
                        {{ status }}
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nome@empresa.pt"
                                autocomplete="email"
                                :class="form.errors.email ? 'border-destructive' : ''"
                                @keyup.enter="submit"
                            />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="password">Password</Label>
                                <Link
                                    v-if="canResetPassword"
                                    href="/forgot-password"
                                    class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    Esqueceste a password?
                                </Link>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    autocomplete="current-password"
                                    :class="form.errors.password ? 'border-destructive pr-10' : 'pr-10'"
                                    @keyup.enter="submit"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    <Eye v-if="!showPassword" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                id="remember"
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded border-input"
                            />
                            <Label for="remember" class="text-sm font-normal cursor-pointer">Manter sessão iniciada</Label>
                        </div>

                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            {{ form.processing ? 'A entrar...' : 'Iniciar Sessão' }}
                        </Button>
                    </div>

                    <p class="text-center text-xs text-muted-foreground">
                        Não tens conta?
                        <Link href="/register" class="text-foreground font-medium hover:underline">
                            Regista-te aqui
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 text-center text-xs text-muted-foreground">
                © {{ new Date().getFullYear() }} {{ empresa?.nome ?? 'Gestão' }}. Todos os direitos reservados.
            </div>
        </div>
    </div>
</template>