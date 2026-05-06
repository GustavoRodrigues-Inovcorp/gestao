<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
    <AppLayout>
        <template #header>
            <h1 class="text-sm font-semibold text-foreground">Dashboard</h1>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-lg border bg-card p-5">
                <p class="text-sm text-muted-foreground">Clientes</p>
                <p class="text-2xl font-bold mt-1">0</p>
            </div>
            <div class="rounded-lg border bg-card p-5">
                <p class="text-sm text-muted-foreground">Fornecedores</p>
                <p class="text-2xl font-bold mt-1">0</p>
            </div>
            <div class="rounded-lg border bg-card p-5">
                <p class="text-sm text-muted-foreground">Propostas</p>
                <p class="text-2xl font-bold mt-1">0</p>
            </div>
            <div class="rounded-lg border bg-card p-5">
                <p class="text-sm text-muted-foreground">Encomendas</p>
                <p class="text-2xl font-bold mt-1">0</p>
            </div>
        </div>
    </AppLayout>
</template>