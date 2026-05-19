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
  <thead :class="cn('bg-muted/60 [&_tr]:border-b [&_tr]:border-border/70', props.class)">
    <slot />
  </thead>
</template>
