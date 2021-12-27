<template>

<div class="register-container">

  <a v-if="!lonely" class="nav-link text-center" href="#" @click.prevent="goToRegister()" active-class="active">
    {{ $t('login.registerLink') }}
  </a>

  <div v-else>

    <form class="" v-on:submit.prevent="onSubmit" v-if="user['urn:ietf:params:scim:schemas:core:2.0:User']">

      <h1>{{ $t('login.registerTitle') }}</h1>
      <p>{{ $t('login.registerDescription') }}</p>

      <template  v-for="f in fields">
        <component :key="f" v-if="$options.components[f]" :is="f" :customerstyle="customerstyle" :user="user" v-on:input="user = $event.target.value"
        :errors="errors"></component>
      </template>

      <p>{{ $t('login.registerBeforeApproval') }}</p>

      <div class="form-check form-group mb-3" v-if="module.config && module.config.approval">
        <input class="form-check-input" type="checkbox" v-model="termsApproved" value="" id="defaultCheck1">
        <label class="form-check-label" for="defaultCheck1">
          <!-- <p>{{ $t('login.registerApproveOne') }}</p> -->

          <i18n v-if="module.config.terms_of_service != null && module.config.privacy_policy == null" path="login.registerApproveOne" tag="p">
            <a :href="module.config.terms_of_service" target="_blank">{{ $t('login.termsOfService') }}</a>
          </i18n>

          <i18n v-else-if="module.config.terms_of_service" path="login.registerApproveTwo" tag="p">
            <a :href="module.config.terms_of_service" target="_blank">{{ $t('login.termsOfService') }}</a>
            <a :href="module.config.privacy_policy" target="_blank">{{ $t('login.privacyPolicy') }}</a>
          </i18n>

        </label>        

      </div>

      <p>{{ $t('login.registerAfterApproval') }}</p>

      <button class="btn btn-primary btn-lg btn-block mt-3" type="submit" :class="{'btn-loading': isLoading}">
        <span>{{ $t('login.registerButton') }}</span>
      </button>

    </form>

  </div>

</div>

</template>

<style lang="scss" scoped>

body .login-box .login-container .large-form-items .register-container div.form-group label {
  display: block;
}

</style>

<script>
import Vue from 'vue'
import base from './Base';

import userName from '../register/userName.vue';
import emails from '../register/emails.vue';
import password from '../register/password.vue';
import givenName from '../register/givenName.vue';
import familyName from '../register/familyName.vue';
import preferredLanguage from '../register/preferredLanguage.vue';

export default Vue.extend({

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

      isLoading: false,

      termsApproved: false,

      user: {
        'schemas': [
          'urn:ietf:params:scim:schemas:core:2.0:User'
        ],
        'urn:ietf:params:scim:schemas:core:2.0:User': {

          // userName: null,
          // password: null,

          
        }

      }

    };
  },

  watch: {

    lonely: function(val){
      
      if(val){
        
        this.goToRegisterNow();
        
      }

    }

  },

  methods: {
    
    goToRegister(){
       this.goToRegisterNow();
    },

    goToRegisterNow(){
       //this.$router.push({ name: 'login.register', params: {hash: this.$route.params.hash, module: this.module.id } });

       this.activate();

       this.$ice(this.$route.params.module, this.authRequest, {
            init: true
        }).then( response => {
          
          this.fields = response.data.fields;

          for(var f of this.fields){
            if(f == 'userName'){
              this.user['urn:ietf:params:scim:schemas:core:2.0:User'].userName = null;
            }

            if(f == 'emails'){
              this.user['urn:ietf:params:scim:schemas:core:2.0:User'].emails = [{
                value: null
              }];
            }

            if(f == 'password'){
              this.user['urn:ietf:params:scim:schemas:core:2.0:User'].password = null;
            }

            if(f == 'givenName' || f == 'familyName'){
              this.user['urn:ietf:params:scim:schemas:core:2.0:User'].name = {
                givenName: null,
                familyName: null
              };
            }
            
          }

          this.url = response.data.url;

        } );

    },

    onSubmit() {
      
      if(this.module.config.approval && !this.termsApproved){
        this.$noty({
          text: "Please agree with the terms and conditions before continuing."
        });

        return false
      }

      if(this.isLoading){
        return false;
      }

      this.isLoading = true;
      this.$http.post(this.url, this.user).then(
        response => {          
          this.$ice(this.module, this.authRequest, {
            'proof-of-creation': response.headers.get('x-scim-proof-of-creation')
          }).then(r => {
            this.isLoading = false;
            this.overview();
          });

        },
        response => {
          this.isLoading = false;
          this.errors = response.body.errors;
        }
      );

    }
  },

  mounted() {

    if(this.lonely){
      this.goToRegisterNow();
    }

    

    

    // userName,
    // emails,
    // password,
    // givenName,
    // familyName,
    // preferredLanguage

    // if active > replace route?? to something like /login/[hash]/register

  }

});

</script>
