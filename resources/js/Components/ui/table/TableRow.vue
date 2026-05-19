<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: {
    type: [Boolean, null, String, Object, Array],
    required: false,
    skipCheck: true,
  },
});
</script>

<template>
  <tr
    :class="
      cn(
        'border-b border-border/70 transition-colors hover:bg-muted/40 data-[state=selected]:bg-muted/70',
        props.class,
      )
    "
  >
    <slot />
  </tr>
</template>
