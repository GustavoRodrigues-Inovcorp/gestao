<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage()
const empresa = page.props.empresa ?? null
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-background text-foreground">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(59,130,246,0.09),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(15,23,42,0.06),transparent_28%)] dark:bg-[radial-gradient(circle_at_top,rgba(59,130,246,0.12),transparent_30%),radial-gradient(circle_at_bottom_right,rgba(255,255,255,0.03),transparent_24%)]"></div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="hidden flex-col justify-between rounded-[2rem] border border-border/70 bg-primary p-8 text-primary-foreground shadow-2xl shadow-black/10 lg:flex">
                    <div class="flex items-center gap-3">
                        <Link href="/">
                            <div class="h-10 w-10 overflow-hidden rounded-lg">
                                <img v-if="empresa?.logotipo" :src="`/empresa/logotipo?v=${empresa?.updated_at ?? ''}`" alt="Logo" class="w-full h-full object-cover" />
                                <ApplicationLogo v-else class="h-10 w-10 fill-current text-primary-foreground" />
                            </div>
                        </Link>
                        <div>
                            <div class="text-[10px] uppercase tracking-[0.24em] text-primary-foreground/60">{{ empresa?.nome ? 'Workspace' : 'Workspace' }}</div>
                            <div class="font-semibold">{{ empresa?.nome ?? 'Plataforma Empresarial' }}</div>
                        </div>
                    </div>

                    <div class="max-w-sm space-y-4">
                        <h1 class="text-4xl font-semibold leading-tight">
                            Uma experiência de acesso mais limpa, moderna e profissional.
                        </h1>
                        <p class="text-sm leading-relaxed text-primary-foreground/75">
                            Recupera o acesso, valida a tua conta e volta ao trabalho com um fluxo visual consistente em todo o sistema.
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-xs text-primary-foreground/60">
                        <span>Seguro</span>
                        <span>•</span>
                        <span>Rápido</span>
                        <span>•</span>
                        <span>Confiável</span>
                    </div>
                </div>

                <div class="flex flex-col justify-center rounded-[2rem] border border-border/70 bg-card/95 p-5 shadow-2xl shadow-black/5 backdrop-blur-xl sm:p-8">
                    <div class="mb-6 flex items-center justify-between lg:hidden">
                        <Link href="/" class="flex items-center gap-2">
                            <div class="h-9 w-9 overflow-hidden rounded-lg">
                                <img v-if="empresa?.logotipo" :src="`/empresa/logotipo?v=${empresa?.updated_at ?? ''}`" alt="Logo" class="w-full h-full object-cover" />
                                <ApplicationLogo v-else class="h-9 w-9 fill-current text-foreground" />
                            </div>
                        </Link>
                    </div>
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
