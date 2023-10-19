<template>
  <div>
    <textarea class="form-control" 
    type="text"
    v-model="value"
    :id="props.id"></textarea>
    <small v-if="props.description" tabindex="-1" class="form-text text-muted">{{ props.description }}</small>
  </div>
</template>


<script setup>
    // :value="modelValue"
    // @input="$emit('update:modelValue', $event.target.value)"

import {  computed } from 'vue';

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