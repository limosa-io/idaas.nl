<template>
  <div id="checkboxes1" role="group" tabindex="-1" class="bv-no-focus-ring">
    <div v-for="(checkbox, index) in checkboxes" class="custom-control custom-control-inline custom-checkbox">
      <input v-model="value" :id="checkbox.value" type="checkbox" class="custom-control-input" :value="checkbox.value">
      <label :for="checkbox.value" class="custom-control-label"><span>{{ checkbox.text }}</span></label>
    </div>
  </div>
</template>


<script setup>
// :value="modelValue"
// @input="$emit('update:modelValue', $event.target.value)"

import { computed } from 'vue';

const props = defineProps(['modelValue', 'id', 'description', 'options'])
const emit = defineEmits(['update:modelValue'])

const checkboxes = computed(() => {
  // if props.modelValue is array of strings, convert to one with entries like {text: 'foo', value: 'foo'}
  if (Array.isArray(props.options) && typeof props.options[0] === 'string' ) {
    return props.options.map((value) => {
      return { text: value, value: value }
    })
  } else {
    return props.options
  }
});

const value = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

</script>