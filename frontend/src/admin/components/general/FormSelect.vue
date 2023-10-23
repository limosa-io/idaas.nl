<template>
  <select :id="elementId" class="mb-3 custom-select" v-model="value">
    <option v-for="option in options" :key="option[props.valueField]" :value="option[props.valueField]">
      {{ option[textField] }}
    </option>
  </select>
</template>


<script setup>
import { computed } from 'vue';

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

// if props.options is an array of strings, map to list with entries like { value: '...', text: '...' }
const options = computed(() => {
  if (typeof props.options[0] === 'string') {
    return props.options.map(option => {
      return {
        value: option,
        text: option
      }
    })
  }
  return props.options
})


const elementId = props.id ? props.id : 'select-' + Math.random().toString(36).substr(2, 9);

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
});


</script>