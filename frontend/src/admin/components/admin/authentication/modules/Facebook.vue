
<template>
  <div>

    <FormGroup horizontal :label-cols="3" description="Client Id." label="Client Id" label-for="module.config.client_id">
      <FormInput id="module.config.client_id" v-model.trim="module.config.client_id"></FormInput>
    </FormGroup>

    <FormGroup horizontal :label-cols="3" description="Client Secret" label="Client Secret"
      label-for="module.config.client_secret">
      <FormInput id="module.config.client_secret" v-model.trim="module.config.client_secret"></FormInput>
    </FormGroup>

    <FormGroup horizontal :label-cols="3" description="Create a user (allow registration)" label="Create user"
      label-for="module.config.create_user">
      <FormCheckbox id="module.config.create_user" v-model="module.config.create_user" :value="true">
        {{ module.config.create_user ? 'Enabled' : 'Disabled' }}
      </FormCheckbox>
    </FormGroup>

    <p v-if="info">
      Use the following callback url in Facebook: {{ info.callback }}
    </p>

  </div>
</template>


<script setup>

import { defineProps } from 'vue';

const props = defineProps(['module', 'info']);

</script>
