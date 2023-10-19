<template>
  <div class="card border-danger mb-3 mt-3">
    <div class="card-header">{{ header }}</div>
    <div class="card-body text-danger">
      <p class="card-text">
        {{ body }}
      </p>
      <slot></slot>
    </div>
  </div>
</template>

<script setup>

import { defineProps } from "vue";

const props = defineProps({
  header: {
    type: String,
    default: "Danger Zone",
  },
  body: {},
});

</script>