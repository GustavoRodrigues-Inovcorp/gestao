<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/composables/useDarkMode'
import { Input } from '@/Components/ui/input'
import { Label } from '@/Components/ui/label'
import { Button } from '@/Components/ui/button'
import { Shield, Sun, Moon, Mail } from 'lucide-vue-next'

defineProps({
    status: String,
})

const page = usePage()
const empresa = computed(() => page.props.empresa)
const { isDark } = useDarkMode()

const form = useForm({
    email: '',
})

function submit() {
    form.post(route('password.email'))
}
</script>

<template>
    <Head title="Recuperar Password" />

    <div class="min-h-screen bg-background flex">
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
                    Recupera o acesso à tua conta
                </h2>
                <p class="text-primary-foreground/70 text-sm leading-relaxed max-w-sm">
                    Indica o teu email e enviaremos um link seguro para redefinir a password.
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

        <div class="flex-1 flex flex-col">
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
                        Voltar ao login
                    </Link>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center px-8">
                <div class="w-full max-w-sm space-y-6">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold">Recuperar Password</h1>
                        <p class="text-sm text-muted-foreground">Introduz o teu email e enviamos-te o link de redefinição.</p>
                    </div>

                    <div v-if="status" class="flex items-start gap-3 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-200">
                        <Mail class="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{{ status }}</span>
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="space-y-1.5">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nome@empresa.pt"
                                autocomplete="email"
                                :class="form.errors.email ? 'border-destructive' : ''"
                                autofocus
                            />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full"
                        >
                            {{ form.processing ? 'A enviar...' : 'Enviar link de recuperação' }}
                        </Button>
                    </form>

                    <p class="text-center text-xs text-muted-foreground">
                        Lembraste-te da password?
                        <Link href="/login" class="text-foreground font-medium hover:underline">
                            Voltar ao login
                        </Link>
                    </p>
                </div>
            </div>

            <div class="px-8 py-4 text-center text-xs text-muted-foreground">
                © {{ new Date().getFullYear() }} {{ empresa?.nome ?? 'Gestão' }}. Todos os direitos reservados.
            </div>
        </div>
    </div>
</template>
