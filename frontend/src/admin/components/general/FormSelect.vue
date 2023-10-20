<template>
  <select :id="elementId" class="mb-3 custom-select">
    <option v-for="(option, index) in props.options" :key="option[props.valueField]" :value="option[props.valueField]">
      {{ option[textField] }}
    </option>
  </select>
</template>


<script setup>

import { onBeforeMount, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    required: true
  },
  id: {
    type: String,
  },
  options: {
    type: Array,
    required: true
  },
  valueField: {
    type: String,
    required: false,
    default: 'value'
  },
  textField: {
    type: String,
    required: false,
    default: 'text'
  }
})

const elementId = props.id ? props.id : 'select-' + Math.random().toString(36).substr(2, 9);

const emit = defineEmits(['update:modelValue'])

</script>