<template>
  <router-link tag="button" class="btn btn-md btn-primary float-right" :to="to">
    <slot></slot>
  </router-link>
</template>

<script>
export default {
  props: ["to"],
};
</script>

<style>
</style>