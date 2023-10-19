
<template>

<div>

  <FormGroup horizontal :label-cols="3" description="The client_id." label="Client ID" label-for="module.config.client_id">
    <FormInput type="text" id="module.config.client_id" v-model="module.config.client_id" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="The client_secret." label="Client secret" label-for="module.config.client_secret">
    <FormInput type="text" id="module.config.client_secret" v-model="module.config.client_secret" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="list of the OAuth 2.0 &quot;scope&quot; values that this authorization server supports" label="scopes" label-for="module.config.scopes">
    <FormInput type="text" id="module.config.scopes" v-model="module.config.scopes" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="Userinfo endpoint." label="Userinfo endpoint" label-for="module.config.userinfo_endpoint">
    <FormInput type="text" id="module.config.userinfo_endpoint" v-model="module.config.userinfo_endpoint" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="URL of the authorization server's authorization endpoint." label="Authorization Endpoint"
    label-for="module.config.authorization_endpoint">
    <FormInput type="text" id="module.config.authorization_endpoint" v-model="module.config.authorization_endpoint" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="URL of the authorization server's token endpoint." label="Token Endpoint" label-for="module.config.token_endpoint">
    <FormInput type="text" id="module.config.token_endpoint" v-model="module.config.token_endpoint" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="Color of the login button." label="Color" label-for="module.config.button_color">
    <FormInput type="text" id="module.config.button_color" v-model="module.config.button_color" />
  </FormGroup>

  <FormGroup horizontal :label-cols="3" description="Label for the login button" label="Label" label-for="module.config.button_text">
    <FormInput type="text" id="module.config.button_text" v-model="module.config.button_text" />
  </FormGroup>

  <p v-if="info">
    Use the following redirect_uri: {{ info.callback }}
  </p>

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

onMounted(() => {

  maxios.get('api/mail_template').then(response => {

    templates.value = response.data;

  });

});

function onSubmit(event) {
  event.preventDefault();
}
</script>
