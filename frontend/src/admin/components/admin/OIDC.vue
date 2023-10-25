
<template>
  <router-view></router-view>
</template>


<script setup>

import {ref, onMounted} from 'vue';
import {laxios} from '@/admin/helpers.js'

const clients = ref(null);

onMounted(() => {
  laxios.get('oauth/connect/register').then(response => {
    clients.value = response.data;
  });
});
</script>
