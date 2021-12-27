
<template>

<div class="mt-2">
  <hr v-if="!$store.state.activeModule" class="hr-text mb-0 mt-0" data-content="">
  <slot></slot>
</div>

</template>

<style lang="scss">
body a.nav-link{
 margin: 0px;
 padding-top: 0.2rem;
 padding-bottom: 0.2rem;
}
</style>

