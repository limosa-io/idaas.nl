<template>

  <div class="logged-out-container">
    <h1>You are now logged out</h1>
    <p>If you was logged on other devices as well, you're now logged out there too.</p>
    <p><router-link :to="{ name: 'initlogin' }" class="btn btn-link" :exact="true" active-class="active">
      <span>Login again</span>
    </router-link></p>
  </div>

</template>

<style lang="scss" scoped>
.logged-out-container{
    text-align: center;
    font-family: Arial;
    display: table-cell;
    vertical-align: middle;
    width: 100vw;
    height: 100vh;
    background-color: #2e72d9;
    color: white;

    a, a:hover { 
      color: white;
    }
}
</style>

<script setup>

import { onMounted } from 'vue';

onMounted(() => {
  window.sessionStorage.clear();
  window.localStorage.clear();
});

</script>

<style>
#app {
  
}

body{
  text-align: left;
}
</style>
