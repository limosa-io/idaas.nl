
<template>
    <div class="mt-0">
        <hr v-if="!$store.state.activeModule" class="hr-text" data-content="or">
        <slot></slot>    
    </div>
</template>

<style lang="scss">

</style>

