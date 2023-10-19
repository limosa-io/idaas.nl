
<template>

<div>

  <FormGroup horizontal :label-cols="3" description="URL to your terms of service document" label="Terms of Service" label-for="module.config.terms_of_service">
    <FormInput type="url" id="module.config.terms_of_service" v-model="module.config.terms_of_service" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="URL to your privacy policy" label="Privacy Policy" label-for="module.config.privacy_policy">
    <FormInput type="url" id="module.config.privacy_policy" v-model="module.config.privacy_policy" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="Ask the user to agree with the Terms of Service and/or Privacy Policy."
    label="Ask approval?" label-for="module.config.approval">

    <FormCheckbox id="module.config.approval" v-model="module.config.approval" :value="true" :unchecked-value="false">
      {{ module.config.approval ? 'Enabled' : 'Disabled' }}
    </FormCheckbox>

  </FormGroup>

</div>

</template>

<script setup>

import { ref, defineProps } from 'vue';
import {maxios} from "@/admin/helpers.js";

const props = defineProps(['module', 'info']);

const errors = ref({});
const wasValidated = ref(false);  
const loading = ref(false);
const type = ref(null);
const types = ref([]);
const templates = ref({});
const isEnabled = ref(true);

onMounted(() => {

  maxios.get('api/settings?namespace=registration').then(response => {

    isEnabled.value = response.data.allow;

    if(!response.data.allow){
      // FIXME: this method does not exist
      this.$emit('alert',{
        text: 'You must enable registration before you can use this module.',
        link: '/registration'
      })
    }

  });

});
</script>
