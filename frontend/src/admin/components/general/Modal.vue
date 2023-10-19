<template>
    <div class="overlay" v-if="showBoolean" @click="hide" >
        <div class="overlay-content" @click.stop>
            <h2 v-if="props.title">{{  props.title  }}</h2>
            <slot>

            </slot>
            <button class="btn btn btn-md btn-primary float-right" @click="emit('ok')">ok</button>
        </div>
    </div>
</template>

<script setup>

import { defineEmits, ref, defineExpose } from 'vue';

const emit = defineEmits(['inFocus', 'submit'])

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Modal'
    }
})

const showBoolean = ref(props.show)

const show = function () {
    showBoolean.value = true;
}

const hide = function() {
    showBoolean.value = false;
}

defineExpose({ show, hide });

</script>

<style>
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.overlay-content {
    max-height: 90%;
    overflow-y: auto;
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    width: 90%;
    max-width: 800px;
}
</style>
