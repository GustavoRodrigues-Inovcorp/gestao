<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
</script>

<template>
    <AppLayout>
        <template #header>
            <slot name="header" />
        </template>

        <slot />
    </AppLayout>
</template>
