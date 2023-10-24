
<template>


<div class="container-fluid" v-if="serviceprovider">
  
  <h4 class="c-grey-900 mt-2">Edit <em>{{ serviceprovider.entityid }}</em></h4>
<div class="bgc-white bd bdrs-3 p-3 mt-3">
<form :class="{'was-validated': wasValidated}" v-if="serviceprovider" class="needs-validation" novalidate v-on:submit.prevent="onSubmit">

<!-- <p>TODO: NameId format list configureren. Attribute mapping mogelijk maken.</p> -->

  <FormGroup horizontal :label-cols="3" description="Unique identifier of this service provider." label="Entity ID" label-for="serviceprovider.entityid">
    <FormInput id="serviceprovider.entityid" v-model.trim="serviceprovider.entityid"></FormInput>
  </FormGroup>
  
  <fieldset id="assertionConsumerService" role="group" class="b-form-group form-group">
    <div class="form-row">
      <legend id="assertionConsumerService_label" class="col-md-3 col-form-label">Assertion Consumer Service</legend>

      <div role="group" aria-labelledby="assertionConsumerService_label" class="col-md-9">
       
       <table class="table">
        <thead>
          <tr>
            <th scope="col">Binding</th>
            <th scope="col">Location</th>
            <th scope="col">Index</th>
            <th class="pr-0" scope="col"></th>
          </tr>
        </thead>
        <tbody>
          <template v-if="serviceprovider.AssertionConsumerService && serviceprovider.AssertionConsumerService.length > 0">
            <tr v-for="(service, index) in serviceprovider.AssertionConsumerService" :key="index">
              <td scope="row"> <input type="text" required v-model="service.Binding" class="form-control" /></td>
              <td><input type="url" required v-model="service.Location" class="form-control" /></td>
              <td><input type="text" required v-model="service.index" class="form-control" /></td>
              <td class="pr-0"><button type="button" class="btn btn-sm btn-danger float-right" @click="serviceprovider.AssertionConsumerService.splice(serviceprovider.AssertionConsumerService.indexOf(service), 1);">Delete</button></td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="4">No endpoints defined</td>
          </tr>

        </tbody>
      </table>

      <button type="button" class="btn btn-sm btn-primary float-right" @click="serviceprovider.AssertionConsumerService ? serviceprovider.AssertionConsumerService.push({}) : serviceprovider.AssertionConsumerService = [{}]">Add</button>

      <small class="form-text text-muted">Specify the locations to which the response message must be returned to the requester</small>

      </div>
    </div>
  </fieldset>


  <fieldset id="singleLogoutService" role="group" class="b-form-group form-group">
    <div class="form-row">
      <legend id="singleLogoutService_label" class="col-md-3 col-form-label">Single logout service</legend>
      <div role="group" aria-labelledby="singleLogoutService_label" class="col-md-9">
       
       <table class="table">
        <thead>
          <tr>
            <th scope="col">Binding</th>
            <th scope="col">Location</th>
            <th scope="col">Index</th>
            <th class="pr-0" scope="col"></th>
          </tr>
        </thead>

        <tbody>
          <template v-if="serviceprovider.SingleLogoutService && serviceprovider.SingleLogoutService.length >0 ">
            <tr v-for="(service, index) in serviceprovider.SingleLogoutService" :key="index">
              <td scope="row"> <input type="text" required v-model="service.Binding" class="form-control" /></td>
              <td><input type="url" required v-model="service.Location" class="form-control" /></td>
              <td><input type="text" required v-model="service.index" class="form-control" /></td>
              <td class="pr-0"><button type="button" class="btn btn-danger float-right" @click="serviceprovider.SingleLogoutService.splice(serviceprovider.SingleLogoutService.indexOf(service), 1);">Delete</button></td>
            </tr>
          </template>
          <tr v-else>
            <td colspan="4">No endpoints defined</td>
          </tr>
        </tbody>

      </table>

      <button type="button" class="btn btn-sm btn-primary float-right" @click="serviceprovider.SingleLogoutService ? serviceprovider.SingleLogoutService.push({}) : serviceprovider.SingleLogoutService = [{}]">Add</button>

      <small class="form-text text-muted">Specify the locations to which the logout message must be returned to the requester</small>

      </div>
    </div>
  </fieldset>

  <FormGroup horizontal :label-cols="3" breakpoint="md" description="Whether the service providers wants to receive signed authentication responses." label="Sign authentication responses">

    <FormCheckbox id="wantSignedAuthnResponse" v-model="serviceprovider.wantSignedAuthnResponse" :value="true" >
      {{ serviceprovider.wantSignedAuthnResponse ? 'Enabled' : 'Disabled' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal :label-cols="3" breakpoint="md" description="Whether the SAML Assertions should be signed" label="Sign Assertions">

    <FormCheckbox id="wantSignedAssertions" v-model="serviceprovider.wantSignedAssertions" :value="true" >
      {{ serviceprovider.wantSignedAssertions ? 'Enabled' : 'Disabled' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal :label-cols="3" breakpoint="md" description="Whether logout responses must be signed." label="Sign logout responses">

    <FormCheckbox id="wantSignedLogoutResponse" v-model="serviceprovider.wantSignedLogoutResponse" :value="true" >
      {{ serviceprovider.wantSignedLogoutResponse ? 'Enabled' : 'Disabled' }}
    </FormCheckbox>

  </FormGroup>

  <FormGroup horizontal :label-cols="3" breakpoint="md" description="Whether logout requests must be signed." label="Sign logout requests">

    <FormCheckbox id="wantSignedLogoutRequest" v-model="serviceprovider.wantSignedLogoutRequest" :value="true" >
      {{ serviceprovider.wantSignedLogoutRequest ? 'Enabled' : 'Disabled' }}
    </FormCheckbox>

  </FormGroup>


  <h6 class="c-grey-900">Restrictions</h6>

        <p>Restrict access to this application based on assigned groups.</p>

        <div class="form-row">
          <div class="col-md-3">Groups</div>
          <div class="col">
            <multiselect
              id="roles"
              v-if="groups && groups.length > 0"
              v-model="serviceprovider.groups"
              track-by="value"
              :customLabel="( {name} ) => (name)"
              :options="groups || []"
              :searchable="false"
              :close-on-select="true"
              :show-labels="true"
              :multiple="true"
              placeholder="Pick a value"
            ></multiselect>
            <p v-else>
              You don't have any groups configured.
              <router-link tag="a" class to="/groups">Create a group.</router-link>
            </p>
          </div>
        </div>

  <button type="submit" class="btn btn-primary">Save</button>
  <button type="button" @click="router.go(-1)" class="btn btn-secondary ml-1">Back</button>

</form>
</div>

  <Danger body="Clicking the button below will delete this SAML service provider. This cannot be undone." v-if="serviceprovider">
      <button type="button" class="btn btn-danger" @click="deleteObject(serviceprovider)">Delete</button>
  </Danger>

</div>

</template>

<script setup>

import { ref, onMounted } from 'vue'
import {maxios, notify} from '@/admin/helpers.js'
import { useRouter, useRoute } from 'vue-router4';

const router = useRouter();
const route = useRoute();

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const serviceprovider = ref(null);
const redirect_uris_string = ref(null);
const groups = ref([]);

onMounted(async () => {
  const response = await maxios.get("api/saml/manage/serviceproviders/" + encodeURIComponent(route.params.id));
  serviceprovider.value = response.data;
  redirect_uris_string.value = (serviceprovider.value && serviceprovider.value.redirect_uris) ? serviceprovider.value.redirect_uris.join('\n') : '';
  wasValidated.value = false;

  const response2 = await maxios.get("api/scim/v2/Groups");
  for (var v of response2.data.Resources) {
    groups.value.push({
      value: v.id,
      name: v["urn:ietf:params:scim:schemas:core:2.0:Group"].name
    });
  }
});

function deleteObject(object){

  maxios.delete('api/saml/manage/serviceproviders/' + encodeURIComponent(route.params.id)).then(response => {

    notify({
        text: 'We have succesfully deleted this SAML service provider.'
      });

    router.replace({ name: 'saml.serviceproviders.list' });

  }).catch(e => {
    console.error(e);
  })

}

function onSubmit(event) {

  if (event.target.checkValidity()) {

    maxios.put('api/saml/manage/serviceproviders/' + encodeURIComponent(route.params.id),
      serviceprovider.value
    ).then(response => {

      notify({
        text: 'We have succesfully saved your new SAML service provider settings.'
      });

    }, e => {
      errors.value = e.response.data.errors;
      wasValidated.value = true;

      notify({
        text: 'We could not save this.',
        type: 'error'
      });
    });

  } else {
    wasValidated.value = true;
    notify({
      text: 'We could not save this.',
      type: 'error'
    });
  }

  event.preventDefault();

}

</script>
