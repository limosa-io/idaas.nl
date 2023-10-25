
<template>

<div class="container-fluid">

<h4 class="c-grey-900 mt-2">New SAML Service Provider</h4>

<div class="bgc-white bd bdrs-3 p-3 mt-3">
  <p>In most cases you would want to <router-link :to="{name: 'saml.serviceproviders.import'}">import the SAML metadata</router-link> from a service provider.</p>
  <p>If there is no metadata available, you can use this function to import it manually.</p>
<form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">


  <div class="form-group">

    <label for="formGroupExampleInput">Entity Id</label>

    <!-- <p>TODO: Allow importing from XML</p> -->

    <input :class="{'is-invalid': errors.entityid}" v-model="serviceProvider.entityid" required aria-describedby="entityId_Help" type="text" class="form-control" id="formGroupExampleInput" placeholder="">
    
    <div v-for="(e, index) in errors.entityid" class="invalid-feedback" :key="index">
          {{ e }}
    </div>

    <div v-if="!errors.entityid" class="invalid-feedback">
          This is a required field.
    </div>
    
   
  </div>

  <button type="submit" class="btn btn-primary mt-3" :disabled="loading">Add Service Provider</button>

</form>
</div>

</div>

</template>


<script setup>

import {ref, onMounted, getCurrentInstance} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';
import { useRouter } from 'vue-router4';
const vue = getCurrentInstance();

const router = useRouter();
const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const serviceProvider = ref({
  entityid: null
});

function onSubmit(event){

  if(event.target.checkValidity()){

    maxios.post('api/saml/manage/serviceproviders',
    serviceProvider.value
    ).then(response => {

      notify({text: 'We have succesfully saved your new SAML Service Provider.'});
      router.replace({ name: 'saml.serviceproviders.edit', params: { id: response.data.id }});

    }, e => {
      errors.value = e.response.data.errors;
      wasValidated.value = true;
    });
    
  }else{
    wasValidated.value = true;
  }

  event.preventDefault();
}

</script>
