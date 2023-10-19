<template>
  <button @click="navigate(to)" class="btn btn-md btn-primary float-right mr-2">
    <slot></slot>
  </button>
</template>

<script setup>

import { defineProps, getCurrentInstance } from "vue";
import { useRouter } from "vue-router4";

const vue = getCurrentInstance();
const router = useRouter();

const props = defineProps({
  to: {
    type: String,
    required: true,
  },
});

function navigate(to){
  router.push(to);
}

</script>
