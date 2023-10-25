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
import {  computed } from 'vue';

const props = defineProps(['modelValue', 'id', 'description'])
const emit = defineEmits(['update:modelValue'])

const value = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

</script>