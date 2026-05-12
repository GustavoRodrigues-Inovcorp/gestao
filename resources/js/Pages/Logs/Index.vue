<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Input } from '@/Components/ui/input'
import { Search } from 'lucide-vue-next'

const props = defineProps({ logs: Array })

const search = ref('')

const filtered = computed(() => {
    if (!search.value) return props.logs
    const q = search.value.toLowerCase()
    return props.logs.filter(l =>
        l.utilizador?.toLowerCase().includes(q) ||
        l.acao?.toLowerCase().includes(q) ||
        l.menu?.toLowerCase().includes(q) ||
        l.ip?.toLowerCase().includes(q)
    )
})
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold">Configurações — Logs</h1>
        </template>

        <div class="space-y-4">
            <div class="relative max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                <Input v-model="search" placeholder="Pesquisar logs..." class="pl-9" />
            </div>

            <div class="rounded-lg border bg-card">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Data</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Hora</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Utilizador</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Menu</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Ação</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">IP</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filtered.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                Nenhum log encontrado.
                            </td>
                        </tr>
                        <tr v-for="log in filtered" :key="log.id" class="border-b last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ log.data }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ log.hora }}</td>
                            <td class="px-4 py-3 font-medium">{{ log.utilizador }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ log.menu }}</td>
                            <td class="px-4 py-3">{{ log.acao }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ log.ip }}</td>
                            <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ log.dispositivo }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>