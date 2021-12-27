
<template>


<div class="container-fluid" v-if="serviceprovider">
  
  <h4 class="c-grey-900 mt-2">Edit <em>{{ serviceprovider.entityid }}</em></h4>
<div class="bgc-white bd bdrs-3 p-3 mt-3">
<form :class="{'was-validated': wasValidated}" v-if="serviceprovider" class="needs-validation" novalidate v-on:submit.prevent="onSubmit">

<!-- <p>TODO: NameId format list configureren. Attribute mapping mogelijk maken.</p> -->

  <b-form-group horizontal :label-cols="3" description="Unique identifier of this service provider." label="Entity ID" label-for="serviceprovider.entityid">
    <b-form-input id="serviceprovider.entityid" v-model.trim="serviceprovider.entityid"></b-form-input>
  </b-form-group>
  
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

  <b-form-group horizontal :label-cols="3" breakpoint="md" description="Whether the service providers wants to receive signed authentication responses." label="Sign authentication responses">

    <b-form-checkbox id="wantSignedAuthnResponse" v-model="serviceprovider.wantSignedAuthnResponse" :value="true" :unchecked-value="false">
      {{ serviceprovider.wantSignedAuthnResponse ? 'Enabled' : 'Disabled' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal :label-cols="3" breakpoint="md" description="Whether the SAML Assertions should be signed" label="Sign Assertions">

    <b-form-checkbox id="wantSignedAssertions" v-model="serviceprovider.wantSignedAssertions" :value="true" :unchecked-value="false">
      {{ serviceprovider.wantSignedAssertions ? 'Enabled' : 'Disabled' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal :label-cols="3" breakpoint="md" description="Whether logout responses must be signed." label="Sign logout responses">

    <b-form-checkbox id="wantSignedLogoutResponse" v-model="serviceprovider.wantSignedLogoutResponse" :value="true" :unchecked-value="false">
      {{ serviceprovider.wantSignedLogoutResponse ? 'Enabled' : 'Disabled' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal :label-cols="3" breakpoint="md" description="Whether logout requests must be signed." label="Sign logout requests">

    <b-form-checkbox id="wantSignedLogoutRequest" v-model="serviceprovider.wantSignedLogoutRequest" :value="true" :unchecked-value="false">
      {{ serviceprovider.wantSignedLogoutRequest ? 'Enabled' : 'Disabled' }}
    </b-form-checkbox>

  </b-form-group>

  <button type="submit" class="btn btn-primary">Save</button>
  <button type="button" @click="$router.go(-1)" class="btn btn-secondary ml-1">Back</button>

</form>
</div>

<div class="card border-danger mb-3 mt-3" v-if="serviceprovider">
    <div class="card-header">Danger Zone</div>
    <div class="card-body text-danger">
      <p class="card-text">Clicking the button below will delete this SAML service provider. This cannot be undone.</p>
      <button type="button" class="btn btn-danger" @click="deleteObject(serviceprovider)">Delete</button>
    </div>
  </div>

</div>

</template>

<script>
export default {

  data() {
    return {

      errors: {},

      wasValidated: false,
      loading: false,

      passwordType: 'password',

      serviceprovider: null,

      redirect_uris_string: null

    }
  },

  mounted() {

    this.$http.get(this.$murl('api/saml/manage/serviceproviders/' + encodeURIComponent(this.$route.params.id))).then(response => {

      this.serviceprovider = response.data;

      //this.redirect_uris_string = (this.client && this.client.redirect_uris) ? this.client.redirect_uris.join('\n') : '';

      this.wasValidated = false;



    }, response => {

      this.errors = response.data.errors;
      this.wasValidated = true;


    });

  },

  watch: {

  },

  methods: {


    deleteObject(object){

      this.$http.delete(this.$murl('api/saml/manage/serviceproviders/' + encodeURIComponent(this.$route.params.id))).then(response => {

        this.$noty({
            text: 'We have succesfully deleted this SAML service provider.'
          });

        this.$router.replace({ name: 'saml.serviceproviders.list' });

      }).catch(e => {
        console.error(e);
      })

    },

    onSubmit(event) {

      if (event.target.checkValidity()) {

        this.$http.put(this.$murl('api/saml/manage/serviceproviders/' + encodeURIComponent(this.$route.params.id)),
          this.serviceprovider
        ).then(response => {

          this.$noty({
            text: 'We have succesfully saved your new SAML service provider settings.'
          });
          // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({
            text: 'We could not save this.',
            type: 'error'
          });
        });

        //this.loading = true;
      } else {
        this.wasValidated = true;
        this.$noty({
          text: 'We could not save this.',
          type: 'error'
        });
      }

      event.preventDefault();

    }

  }

}

</script>
