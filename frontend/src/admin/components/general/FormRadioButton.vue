<template>
  <div class="custom-control custom-control-inline custom-radio">
    <input :id="props.value" type="radio" name="radioSubComponent" class="custom-control-input" :value="props.value" v-model="value">
    <label class="custom-control-label" :for="props.value"><slot></slot></label>
  </div>
</template>


<script setup>
// :value="modelValue"
// @input="$emit('update:modelValue', $event.target.value)"

import { computed } from 'vue';


const props = defineProps({
    modelValue: {
        required: true
    },
    id: {
        type: String,
        required: false,
    },
    description: {
        type: String,
        required: false,
        default: ''
    },
    value: {
        required: true
    }
})

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