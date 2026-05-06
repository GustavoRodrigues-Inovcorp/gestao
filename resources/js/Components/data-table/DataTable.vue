<script setup>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table'

const props = defineProps({
    columns: {
        type: Array,
        default: () => [],
    },
    data: {
        type: Array,
        default: () => [],
    },
    emptyText: {
        type: String,
        default: 'Sem registos.',
    },
})
</script>

<template>
    <div class="rounded-lg border bg-card">
        <Table>
            <TableHeader class="border-b bg-muted/50">
                <TableRow>
                    <TableHead
                        v-for="column in columns"
                        :key="column.key"
                        :class="column.class"
                    >
                        {{ column.label }}
                    </TableHead>
                    <TableHead v-if="$slots.actions" class="text-right">Ações</TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <TableRow v-if="data.length === 0">
                    <TableCell :colspan="columns.length + ($slots.actions ? 1 : 0)" class="py-8 text-center text-muted-foreground">
                        {{ emptyText }}
                    </TableCell>
                </TableRow>

                <TableRow
                    v-for="row in data"
                    :key="row.id"
                    class="border-b last:border-0 hover:bg-muted/30"
                >
                    <TableCell
                        v-for="column in columns"
                        :key="`${row.id}-${column.key}`"
                        :class="column.cellClass"
                    >
                        {{ row[column.key] ?? '—' }}
                    </TableCell>

                    <TableCell v-if="$slots.actions" class="text-right">
                        <slot name="actions" :row="row" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
