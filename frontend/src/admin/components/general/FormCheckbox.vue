<template>
    <div class="custom-control custom-checkbox">
        <input :id="id" type="checkbox" v-model="value" class="custom-control-input">
        <label class="custom-control-label">
            <slot>{{ value }}</slot>
        </label>
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
        return props.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})

</script>