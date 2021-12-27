<template>

<form v-if="provider" class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">

  <h3 class="c-grey-900">Settings</h3>

  <b-form-group label-cols-md="3" label="Supported Response Types">
    <b-form-checkbox-group id="checkboxes1" name="flavour1" v-model="provider.response_types_supported" :options="[{text: 'code',value:'code'},{text: 'code id_token',value:'code id_token'},{text: 'id_token',value:'id_token'},{text: 'token id_token',value:'token id_token'}]">
    </b-form-checkbox-group>
  </b-form-group>

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

  <b-form-group :label-cols-md="3" description="Maximum life of the access token" label="Access Token Lifetime" label-for="provider.liftime_access_token">

    <b-form-input type="number" :class="{'is-invalid': errors['liftime_access_token']}" id="provider.liftime_access_token" v-model="provider.liftime_access_token"></b-form-input>

    <div v-for="e in (errors.liftime_access_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </b-form-group>


  <b-form-group :label-cols-md="3" description="Maximum life of the ID Token" label="ID Token Lifetime" label-for="provider.liftime_id_token">
    <b-form-input type="number" :class="{'is-invalid': errors['liftime_id_token']}" id="provider.liftime_id_token" v-model="provider.liftime_id_token"></b-form-input>

    <div v-for="e in (errors.liftime_id_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </b-form-group>


  <b-form-group :label-cols-md="3" description="Maximum life of the Refresh Token" label="Refresh Token Lifetime" label-for="provider.liftime_refresh_token">
    <b-form-input type="number" :class="{'is-invalid': errors['liftime_refresh_token']}" id="provider.liftime_refresh_token"
      v-model="provider.liftime_refresh_token"></b-form-input>

    <div v-for="e in (errors.liftime_refresh_token || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>


  </b-form-group>
  
  <b-form-group :label-cols-md="3" description="Allows you to populate the OpenID Connect profile-claim with a custom url. You may use the {userid} variable." label="Profile URL" label-for="provider.profile_url_template">
    <b-form-input type="text" :class="{'is-invalid': errors['profile_url_template']}" id="provider.profile_url_template"
      v-model="provider.profile_url_template"></b-form-input>

    <div v-for="e in (errors.profile_url_template || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>


  </b-form-group>
  
  <b-form-group :label-cols-md="3" description="What url should be used for initialization" label="Init URL" label-for="provider.init_url">
    <b-form-input type="text" :class="{'is-invalid': errors['init_url']}" id="provider.profile_url_template"
      v-model="provider.init_url"></b-form-input>

    <div v-for="e in (errors.init_url || [])" :key="e" class="invalid-feedback">
      {{ e }}
    </div>

  </b-form-group>

  <button type="submit" class="btn btn-primary mt-3" :disabled="loading">Save Settings</button>

</form>

</template>

<script>

export default {

  data() {
    return {

      errors: {},

      wasValidated: false,
      loading: false,

      provider: null,

      selected: false,

    }
  },

  mounted() {

    // oidc/provider

    this.$http.get(this.$murl('oauth/oidc/provider')).then(response => {
      
      this.provider = response.data;

    }, response => {
      // error callback
      this.errors = reponse.data.errors;
    });

  },

  methods: {
    onSubmit(event) {

      this.$http.put(this.$murl('oauth/oidc/provider'), this.provider).then(response => {

        this.$noty({
          text: 'We have succesfully saved your provider settings.'
        });

        this.errors = {};
        //this.provider = response.data;
        this.wasValidated = false;

      }, response => {
        // error callback
        this.errors = response.data.errors;
        //this.$noty({text: 'We could not save this.', type: 'error'});

        this.$noty({
          text: 'We could not save this.',
          type: 'error'
        });

        this.wasValidated = true;

      });



      event.preventDefault();

    }
  }

}

</script>
