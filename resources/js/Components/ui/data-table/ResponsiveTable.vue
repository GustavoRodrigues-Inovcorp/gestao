<script setup>
defineProps({
    headers: Array, // [{ label, class }]
})
</script>

<template>
    <div class="w-full overflow-x-auto rounded-2xl border border-border/70 bg-card/95 shadow-sm">
        <table class="w-full text-sm min-w-max">
            <thead class="border-b border-border/70 bg-muted/60">
                <tr>
                    <th
                        v-for="header in headers"
                        :key="header.label"
                        :class="['whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-muted-foreground/80', header.class]"
                    >
                        {{ header.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <slot />
            </tbody>
        </table>
    </div>
</template>