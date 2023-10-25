
<template>
  <div>

    <FormGroup horizontal :label-cols="3"
      description="Available template parameters are <code class=&quot;highlighter-rouge&quot;>otp</code>, <code class=&quot;highlighter-rouge&quot;>subject</code> and if present <code class=&quot;highlighter-rouge&quot;>user</code>."
      label="Email template" label-for="module.config.template_id">
      <FormSelect id="module.config.template_id" aria-describedby="parentHelp" v-if="templates" value-field="id"
        text-field="name" v-model="module.config.template_id" :options="templates" />
    </FormGroup>

  </div>
</template>

<script setup>
import { maxios } from '@/admin/helpers.js'
import { ref, onMounted, defineProps } from 'vue';
const props = defineProps(['module', 'info']);
const templates = ref({});

onMounted(() => {

  maxios.get('api/mail_template').then(response => {

    templates.value = response.data;

  });

});

</script>
