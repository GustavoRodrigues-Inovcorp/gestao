<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/composables/useDarkMode'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import { Shield, Sun, Moon, Eye, EyeOff } from 'lucide-vue-next'

const page = usePage()
const empresa = computed(() => page.props.empresa)
const { isDark } = useDarkMode()
const showPassword = ref(false)
const showConfirm = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Criar Conta" />

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
                    Cria a tua conta
                </h2>
                <p class="text-primary-foreground/70 text-sm leading-relaxed max-w-sm">
                    Junta-te à plataforma e começa a gerir o teu negócio de forma mais eficiente.
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
                        href="/login"
                        class="text-sm text-muted-foreground hover:text-foreground transition-colors"
                    >
                        Já tens conta?
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="flex-1 flex items-center justify-center px-8">
                <div class="w-full max-w-sm space-y-6">

                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold">Criar Conta</h1>
                        <p class="text-sm text-muted-foreground">Preenche os dados para criar a tua conta.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <Label for="name">Nome</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="O teu nome"
                                autocomplete="name"
                                :class="form.errors.name ? 'border-destructive' : ''"
                            />
                            <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nome@empresa.pt"
                                autocomplete="email"
                                :class="form.errors.email ? 'border-destructive' : ''"
                            />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label for="password">Password</Label>
                            <div class="relative">
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    :class="form.errors.password ? 'border-destructive pr-10' : 'pr-10'"
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

                        <div class="space-y-1.5">
                            <Label for="password_confirmation">Confirmar Password</Label>
                            <div class="relative">
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    autocomplete="new-password"
                                    class="pr-10"
                                />
                                <button
                                    type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    <Eye v-if="!showConfirm" class="w-4 h-4" />
                                    <EyeOff v-else class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <Button
                            @click="submit"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            {{ form.processing ? 'A criar conta...' : 'Criar Conta' }}
                        </Button>
                    </div>

                    <p class="text-center text-xs text-muted-foreground">
                        Já tens conta?
                        <Link href="/login" class="text-foreground font-medium hover:underline">
                            Inicia sessão aqui
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