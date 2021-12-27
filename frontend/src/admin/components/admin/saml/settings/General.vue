<template>

<form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">

  <h3 class="c-grey-900">Settings</h3>

  <b-form-group horizontal label="Wants signed authentication requests">
    
     <b-form-checkbox id="sign.authnrequest"
                     v-model="provider['sign.authnrequest']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['sign.authnrequest'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal label="Sign redirect SAML responses">
    
     <b-form-checkbox id="redirect.sign"
                     v-model="provider['redirect.sign']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['redirect.sign'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal label="Enable SSO Post binding">
    
     <b-form-checkbox id="ssoHttpPostEnabled"
                     v-model="provider['ssoHttpPostEnabled']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['ssoHttpPostEnabled'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal label="Enable SSO Redirect binding">
    
     <b-form-checkbox id="ssoHttpRedirectEnabled"
                     v-model="provider['ssoHttpRedirectEnabled']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['ssoHttpRedirectEnabled'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal label="Enable SLO POST binding">
    
     <b-form-checkbox id="sloHttpPostEnabled"
                     v-model="provider['sloHttpPostEnabled']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['sloHttpPostEnabled'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

  </b-form-group>

  <b-form-group horizontal label="Enable SLO Redirect binding">
    
     <b-form-checkbox id="sloHttpRedirectEnabled"
                     v-model="provider['sloHttpRedirectEnabled']"
                     :value="true"
                     :unchecked-value="false">
      {{ provider['sloHttpRedirectEnabled'] ? 'Yes' : 'No' }}
    </b-form-checkbox>

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

      provider: {},

      selected: false

    }
  },

  mounted() {

    // api/saml/manage/identityprovider
    this.$http.get(this.$murl('api/saml/manage/identityprovider')).then(response => {
      
      this.provider = response.data;

    }, response => {
      // error callback
      this.errors = reponse.data.errors;
    });

  },

  methods: {
    onSubmit(event) {

      this.$http.put(this.$murl('api/saml/manage/identityprovider'), this.provider).then(response => {

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
