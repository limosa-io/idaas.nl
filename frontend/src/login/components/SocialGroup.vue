
<template>
    <div class="mt-0">
        <hr v-if="!state.activeModule" class="hr-text" data-content="or">
        <slot></slot>    
    </div>
</template>

<style lang="scss">

</style>

<script setup>


import { useStateStore } from "./store";


const state = useStateStore();


</script>
