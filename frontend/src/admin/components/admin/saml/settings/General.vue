<template>

<form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit.prevent="onSubmit">

  <h3 class="c-grey-900">Settings</h3>

  <FormGroup horizontal label="Wants signed authentication requests">
    
     <FormCheckbox id="sign.authnrequest"
                     v-model="provider['sign.authnrequest']"
                     :value="true"
                     >
      {{ provider['sign.authnrequest'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal label="Sign redirect SAML responses">
    
     <FormCheckbox id="redirect.sign"
                     v-model="provider['redirect.sign']"
                     :value="true"
                     >
      {{ provider['redirect.sign'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal label="Enable SSO Post binding">
    
     <FormCheckbox id="ssoHttpPostEnabled"
                     v-model="provider['ssoHttpPostEnabled']"
                     :value="true"
                     >
      {{ provider['ssoHttpPostEnabled'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal label="Enable SSO Redirect binding">
    
     <FormCheckbox id="ssoHttpRedirectEnabled"
                     v-model="provider['ssoHttpRedirectEnabled']"
                     :value="true"
                     >
      {{ provider['ssoHttpRedirectEnabled'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal label="Enable SLO POST binding">
    
     <FormCheckbox id="sloHttpPostEnabled"
                     v-model="provider['sloHttpPostEnabled']"
                     :value="true"
                     >
      {{ provider['sloHttpPostEnabled'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal label="Enable SLO Redirect binding">
    
     <FormCheckbox id="sloHttpRedirectEnabled"
                     v-model="provider['sloHttpRedirectEnabled']"
                     :value="true"
                     >
      {{ provider['sloHttpRedirectEnabled'] ? 'Yes' : 'No' }}
    </FormCheckbox>

  </FormGroup>

  

  <button type="submit" class="btn btn-primary mt-3" :disabled="loading">Save Settings</button>

</form>

</template>

<script setup>
import {ref, getCurrentInstance, onMounted} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(true);
const provider = ref({});

onMounted(async () => {
  const response = await maxios.get("api/saml/manage/identityprovider");
  provider.value = response.data;
  loading.value = false;
});

function onSubmit(event){

  if(event.target.checkValidity()){

    maxios.put('api/saml/manage/identityprovider',
    provider.value
    ).then(response => {

      notify({text: 'We have succesfully saved your provider settings.'});

    }, e => {
      errors.value = e.response.data.errors;
      wasValidated.value = true;
    });

  }else{
    wasValidated.value = true;
  }
}

</script>
