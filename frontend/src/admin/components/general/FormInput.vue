<template>
    <input class="form-control" 
    :type="props.type"
    v-model="value"
    :id="props.id"  />
</template>


<script setup>
import {  computed } from 'vue';


const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    id: {
        type: String,
        required: true
    },
    type: {
        type: String,
        required: false,
        default: 'text'
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