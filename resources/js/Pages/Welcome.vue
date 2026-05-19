<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { useDarkMode } from '@/composables/useDarkMode'
import { Sun, Moon, Shield, BarChart3, Users, FileText, Calendar, Package } from 'lucide-vue-next'
const { isDark } = useDarkMode()
const page = usePage()
const empresa = page.props.empresa ?? null

const features = [
    { icon: Users, title: 'Clientes & Fornecedores', desc: 'Gestão completa de entidades com integração VIES.' },
    { icon: FileText, title: 'Propostas & Encomendas', desc: 'Do orçamento à encomenda com um clique, com geração de PDF.' },
    { icon: BarChart3, title: 'Financeiro', desc: 'Controlo de faturas, contas bancárias e conta corrente.' },
    { icon: Calendar, title: 'Calendário', desc: 'Agendamento de atividades com equipa e clientes.' },
    { icon: Package, title: 'Arquivo Digital', desc: 'Todos os documentos organizados e seguros.' },
    { icon: Shield, title: 'Gestão de Acessos', desc: 'Controlo total de permissões por utilizador.' },
]
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">

        <!-- Navbar -->
        <nav class="fixed top-0 left-0 right-0 z-50 border-b border-border bg-background/80 backdrop-blur-sm">
            <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center overflow-hidden">
                        <img
                            v-if="empresa?.logotipo"
                            :src="`/empresa/logotipo?v=${empresa?.updated_at ?? ''}`"
                            alt="Logo"
                            class="w-full h-full object-cover"
                        />
                        <Shield v-else class="w-4 h-4 text-primary-foreground" />
                    </div>
                    <span class="font-semibold text-sm">{{ empresa?.nome ?? 'Base' }}</span>
                </div>

                <!-- Ações -->
                <div class="flex items-center gap-3">
                    <button
                        @click="isDark = !isDark"
                        class="p-2 rounded-md hover:bg-accent text-muted-foreground hover:text-foreground transition-colors"
                    >
                        <Sun v-if="isDark" class="w-4 h-4" />
                        <Moon v-else class="w-4 h-4" />
                    </button>
                    <Link
                        href="/login"
                        class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground text-sm font-medium px-4 py-2 hover:bg-primary/90 transition-colors"
                    >
                        Iniciar Sessão
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Hero -->
        <section class="pt-32 pb-20 px-6">
            <div class="max-w-3xl mx-auto text-center space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border bg-muted text-muted-foreground text-xs font-medium">
                    <Shield class="w-3.5 h-3.5" />
                    Plataforma de Gestão Empresarial
                </div>

                <h1 class="text-4xl md:text-5xl font-bold tracking-tight">
                    Bem-vindo à
                    <span class="text-primary"> {{ empresa?.nome ?? 'Base' }}</span>
                </h1>

                <p class="text-lg text-muted-foreground max-w-xl mx-auto leading-relaxed">
                    Gere clientes, propostas, encomendas, financeiro e muito mais — tudo num só lugar, de forma simples e segura.
                </p>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <Link
                        href="/login"
                        class="inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground text-sm font-medium px-6 py-2.5 hover:bg-primary/90 transition-colors shadow-sm"
                    >
                        Iniciar Sessão
                    </Link>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="pb-24 px-6">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="rounded-xl border bg-card p-5 space-y-3 hover:shadow-md transition-shadow"
                    >
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                            <component :is="feature.icon" class="w-4 h-4 text-primary" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm">{{ feature.title }}</h3>
                            <p class="text-xs text-muted-foreground mt-1 leading-relaxed">{{ feature.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border py-6 px-6">
                <div class="max-w-6xl mx-auto flex items-center justify-center text-xs text-muted-foreground">
                <span>© {{ new Date().getFullYear() }} {{ empresa?.nome ?? 'Base' }}. Todos os direitos reservados.</span>
                </div>
        </footer>

    </div>
</template>