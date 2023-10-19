<template>
  <select :id="id" class="mb-3 custom-select">
    <option v-for="(option, index) in props.options" :key="option[props.valueField]" :value="option[props.valueField]">
      {{ option[textField] }}
    </option>
  </select>
</template>


<script setup>

import { onBeforeMount, onMounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    required: true
  },
  id: {
    type: String,
    required: true
  },
  options: {
    type: Array,
    required: true
  },
  valueField: {
    type: String,
    required: true,
    default: 'value'
  },
  textField: {
    type: String,
    required: true,
    default: 'text'
  }
})

const emit = defineEmits(['update:modelValue'])

onBeforeMount(() => {
  console.log('onBeforeMount');
  console.log(props.options);
});


</script>