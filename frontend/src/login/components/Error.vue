<template>

<div class="error-container">

  <template v-if="state.error == 'invalid_logout_url'">
    <h1>We could not log you out everywhere.</h1>
    <p>Please contact support.</p>
    <p class="ligher">The provided <em>post_logout_redirect_uri</em> was invalid.</p>
  </template>
  <template v-else>
    <h1>We could not authenticate you</h1>
    <p>Please contact support.</p>
    <p class="ligher">{{ error }}</p>
  </template>

</div>

</template>

<style lang="scss" scoped>
.error-container{
    text-align: center;
    font-family: Arial;
    display: table-cell;
    vertical-align: middle;
    width: 100vw;
    height: 100vh;
    background-color: #eb413e;
    color: white;
}
</style>

<script setup>
import {onMounted} from 'vue'
import {useStateStore} from './store'
const state = useStateStore();

const error = window.error;

onMounted(() => {
  console.error(state.error);
});

</script>

