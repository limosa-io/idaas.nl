<template>
    <div class="form-group form-row">
        
            <div class="col-3">
                <label :for="id">{{ label }}</label>
            </div>

            <div class="col">
                <slot></slot>
            </div>
        
    </div>
</template>

<script setup>

import { defineProps } from 'vue';

const props = defineProps({
    id: {
        type: String,
        required: false,
    },
    label: {
        type: String,
        required: true,
    }
})

</script>

<style>
.form-group {
    margin-bottom: 1rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: normal;
}

.error {
    color: red;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
</style>
