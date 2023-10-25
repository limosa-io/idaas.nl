<template>

<form v-if="provider" class="needs-validation" novalidate :class="{'was-validated': wasValidated}" @submit.prevent="onSubmit">

  <h3 class="c-grey-900">Settings</h3>

  <FormGroup label-cols-md="3" label="Supported Response Types">
    <FormCheckbox-group id="checkboxes1" name="flavour1" v-model="provider.response_types_supported" :options="[{text: 'code',value:'code'},{text: 'code id_token',value:'code id_token'},{text: 'id_token',value:'id_token'},{text: 'token id_token',value:'token id_token'}]">
    </FormCheckbox-group>
  </FormGroup>

  <div class="form-row mb-3">

    <div class="col-md-3">
      <label for="acr_values_supported">Authentication Context References</label>
    </div>

    <div class="col">

      <multiselect class="form-control" :class="{'is-invalid': errors['acr_values_supported']}" id="acr_values_supported" :customLabel="e => e" v-model="provider.acr_values_supported" tag-placeholder="Add" placeholder="add a url" :multiple="true" :taggable="true" @tag="(searchQuery, id) => { provider.acr_values_supported.push(searchQuery); }" :close-on-select="false" :options="[]"></multiselect>

      <div v-for="e in (errors.acr_values_supported || [])" :key="e" class="invalid-feedback">
        {{ e }}
      </div>

      <small id="emailHelp" class="form-text text-muted">ACRs allow you to set-up step up authentication. You might also use this to help to user with selecting the correct
        authentication module.</small>

    </div>

  </div>

  <FormGroup :label-cols-md="3" description="Maximum life of the access token" label="Access Token Lifetime" label-for="provider.liftime_access_token">

    <FormInput type="number" :class="{'is-invalid': errors['liftime_access_token']}" id="provider.liftime_access_token" v-model="provider.liftime_access_token"></FormInput>

    <div v-for="e in (errors.liftime_access_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </FormGroup>


  <FormGroup :label-cols-md="3" description="Maximum life of the ID Token" label="ID Token Lifetime" label-for="provider.liftime_id_token">
    <FormInput type="number" :class="{'is-invalid': errors['liftime_id_token']}" id="provider.liftime_id_token" v-model="provider.liftime_id_token"></FormInput>

    <div v-for="e in (errors.liftime_id_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </FormGroup>


  <FormGroup :label-cols-md="3" description="Maximum life of the Refresh Token" label="Refresh Token Lifetime" label-for="provider.liftime_refresh_token">
    <FormInput type="number" :class="{'is-invalid': errors['liftime_refresh_token']}" id="provider.liftime_refresh_token"
      v-model="provider.liftime_refresh_token"></FormInput>

    <div v-for="e in (errors.liftime_refresh_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>


  </FormGroup>
  
  <FormGroup :label-cols-md="3" description="Allows you to populate the OpenID Connect profile-claim with a custom url. You may use the {userid} variable." label="Profile URL" label-for="provider.profile_url_template">
    <FormInput type="text" :class="{'is-invalid': errors['profile_url_template']}" id="provider.profile_url_template"
      v-model="provider.profile_url_template"></FormInput>

    <div v-for="e in (errors.profile_url_template || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>


  </FormGroup>
  
  <FormGroup :label-cols-md="3" description="What url should be used for initialization" label="Init URL" label-for="provider.init_url">
    <FormInput type="text" :class="{'is-invalid': errors['init_url']}" id="provider.profile_url_template"
      v-model="provider.init_url"></FormInput>

    <div v-for="e in (errors.init_url || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </FormGroup>

  <button type="submit" class="btn btn-primary mt-3" :disabled="loading">Save Settings</button>

</form>

</template>

<script setup>

import {ref, onMounted, getCurrentInstance} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const provider = ref(null);
const selected = ref(false);

onMounted( () => {
  maxios.get('oauth/oidc/provider').then(response => {
    provider.value = response.data;
  }, response => {
    // error callback
    errors.value = reponse.data.errors;
  });
})

function onSubmit() {

  maxios.put('oauth/oidc/provider', provider.value).then(response => {

    notify({
      text: 'We have succesfully saved your provider settings.'
    });

    errors.value = {};
    //provider.value = response.data;
    wasValidated.value = false;

  }, response => {
    // error callback
    errors.value = response.data.errors;
    //notify({text: 'We could not save this.', type: 'error'});

    notify({
      text: 'We could not save this.',
      type: 'error'
    });

    wasValidated.value = true;

  });

}

</script>
