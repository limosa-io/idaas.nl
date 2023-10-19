
<template>
  <form class="needs-validation" novalidate :class="{ 'was-validated': wasValidated }" v-on:submit="onSubmit">

    <h3 class="c-grey-900">Fallback</h3>

    <div class="form-group">

      <p>test</p>

    </div>


  </form>
</template>


<script setup>

import { ref, defineProps } from 'vue';

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const type = ref(null);
const types = ref([]);

function onSubmit(event) {
  event.preventDefault();
}
</script>
