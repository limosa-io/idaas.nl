<template>
<div class="container">

  <div class="row mt-sm-3 mt-md-5  justify-content-md-center" v-if="fields && fields.length > 0">

    <div class="col-md-3 p-3 rounded-left" style="background-color: #eeeeee;">
      <h1 class="mt-3">Create your account</h1>
      <p style="font-size: 1.5em;">Access to trials, demos, starter kits, services, and APIs.</p>
    </div>

    <div class="col-md-3 p-3 rounded-right" style="background-color: white;">

      <h2 class="mt-3">Sign up for an account</h2>
      
      <p v-if="authRequest.next && authRequest.next.length > 1" >Already have an account? <a href="#" @click="$router.go(-1); $event.preventDefault();">Sign in</a></p>

      <form class="" v-on:submit.prevent="onSubmit" v-if="user['urn:ietf:params:scim:schemas:core:2.0:User']">

        <template v-for="f in fields" >
        <component :key="f" v-if="$options.components[f]" :customerstyle="customerstyle" :is="f" :user="user" v-on:input="user = $event.target.value" :errors="errors" ></component>
        </template>

        <p>We may use my contact data to keep me informed of products, services and offerings:</p>

        <div class="form-check form-group mb-3">
          <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
          <label class="form-check-label" for="defaultCheck1">
            Default checkbox
          </label>
        </div>
        
        <p>
          More information on our processing can be found in our Privacy Policy. By submitting this form, I acknowledge that
          I have read and understand the Privacy Statement.</p>
        <p>
          I accept the product Terms of Service of this registration form.
        </p>

        <button class="btn btn-primary btn-lg mt-3" type="submit">
          <span>Continue</span>
        </button>

      </form>

    </div>

  </div>
</div>
</template>

<script>

import base from './modules/Base';

import userName from './register/userName.vue';
import emails from './register/emails.vue';
import password from './register/password.vue';
import givenName from './register/givenName.vue';
import familyName from './register/familyName.vue';
import preferredLanguage from './register/preferredLanguage.vue';

export default {

  components: {
    userName,
    emails,
    password,
    givenName,
    familyName,
    preferredLanguage
  },

  mixins: [base],

  data() {

    return {

      errors: {},


      fields: [],

      //SCIM Me endpoint
      url: null,

      user: {
        'schemas': [
          'urn:ietf:params:scim:schemas:core:2.0:User'
        ],
        'urn:ietf:params:scim:schemas:core:2.0:User': {

          password: null,

          emails: [{
            value: null
          }],

          name: {
            givenName: null,
            familyName: null
          }

        }

      }

    }

  },

  mounted(){

    
    this.request({
        init: true
    }).then( response => {
      
      this.fields = response.data.fields;
      this.url = response.data.url;

    } );
    


  },

  methods: {
    onSubmit() {

      this.$http.post(this.url, this.user).then(
        response => { 

          this.request({
              userId: response.body.id
          });

          this.$router.go(-1);

        },
        response => {
          this.errors = response.body.errors || {};
        }
      );

    }
  }


  // After success, return with user id?

};
</script>

<style lang="scss">


</style>
