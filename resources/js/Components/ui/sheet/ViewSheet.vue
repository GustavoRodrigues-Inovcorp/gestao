<script setup>
import { Button } from '@/Components/ui/button'
import { Pencil, Trash2 } from 'lucide-vue-next'

defineProps({
    open: Boolean,
    title: String,
    fields: Array, // [{ label, value, type? }]
    canEdit: { type: Boolean, default: true },
    canDelete: { type: Boolean, default: true },
})

const emit = defineEmits(['update:open', 'edit', 'delete'])
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fixed inset-0 z-[9998]">
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/65 backdrop-blur-[2px]"
                aria-label="Fechar visualização"
                @click="emit('update:open', false)"
            />

            <aside class="absolute right-0 top-0 z-[9999] flex h-full w-[420px] flex-col overflow-hidden border-l border-border/70 bg-card/95 shadow-2xl backdrop-blur-xl sm:w-[480px]">
                <header class="shrink-0 border-b border-border/70 bg-gradient-to-b from-primary/8 to-transparent px-6 py-5">
                    <div class="mb-4 h-1.5 w-14 rounded-full bg-primary/70"></div>
                    <h2 class="text-xl font-semibold tracking-tight text-foreground">{{ title }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Detalhes do registo selecionado</p>
                </header>

                <div class="flex-1 overflow-y-auto px-6 py-5">
                    <div class="space-y-3">
                        <div
                            v-for="field in fields"
                            :key="field.label"
                            class="rounded-xl border border-border/70 bg-muted/30 px-4 py-3 transition-colors hover:bg-muted/50"
                        >
                            <div class="text-[11px] font-semibold uppercase tracking-[0.12em] text-muted-foreground">
                                {{ field.label }}
                            </div>

                            <div class="mt-1.5">
                                <span v-if="field.type === 'badge'" class="inline-flex">
                                    <slot :name="'badge-' + field.key" :value="field.value">
                                        <span class="text-sm font-medium text-foreground">{{ field.value ?? '—' }}</span>
                                    </slot>
                                </span>
                                <span v-else-if="field.type === 'currency'" class="text-sm font-medium text-foreground">
                                    {{ field.value !== undefined ? Number(field.value).toLocaleString('pt-PT', { style: 'currency', currency: 'EUR' }) : '—' }}
                                </span>
                                <span v-else-if="field.type === 'link' && field.value" class="inline-flex">
                                    <a
                                        :href="field.value"
                                        target="_blank"
                                        class="text-sm font-medium text-primary hover:bg-primary/12 hover:underline"
                                    >
                                        {{ field.value }}
                                    </a>
                                </span>
                                <span v-else class="text-sm font-medium text-foreground">{{ field.value ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="!fields?.length" class="rounded-xl border border-dashed border-border/70 bg-muted/20 px-4 py-8 text-center text-sm text-muted-foreground">
                        Sem informações para mostrar.
                    </div>
                </div>

                <footer class="shrink-0 border-t border-border/70 bg-card/95 px-6 py-4">
                    <div class="flex gap-2">
                        <Button v-if="canEdit" @click="emit('edit')" variant="default" size="sm" class="gap-2">
                            <Pencil class="w-4 h-4" /> Editar
                        </Button>
                        <Button v-if="canDelete" @click="emit('delete')" variant="destructive" size="sm" class="gap-2">
                            <Trash2 class="w-4 h-4" /> Eliminar
                        </Button>
                    </div>
                </footer>
            </aside>
        </div>
    </Teleport>
</template>