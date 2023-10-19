<template>
  <div :id="id" role="radiogroup" tabindex="-1" class="bv-no-focus-ring">
    <slot></slot>
  </div>
</template>


<script setup>
// :value="modelValue"
// @input="$emit('update:modelValue', $event.target.value)"

import { computed } from 'vue';

const props = defineProps(['modelValue', 'id', 'description'])
const emit = defineEmits(['update:modelValue'])

const value = computed({
  get() {
    console.log('get: ' + props.modelValue);
    return props.modelValue
  },
  set(value) {
    console.log('set: ' + value);
    emit('update:modelValue', value)
  }
})

</script>