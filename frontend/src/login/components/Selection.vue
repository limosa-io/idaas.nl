<template>
<div v-if="authRequest != null && authRequest.info != null" :class=" { 
                        'justify-content-center': (customerstyle['container_positionHorizonal'] || 'center') == 'center', 
                        'justify-content-start': customerstyle['container_positionHorizonal'] == 'left', 
                        'justify-content-end': customerstyle['container_positionHorizonal'] == 'right',

                        'align-items-center': (customerstyle['container_positionVertical'] || 'middle') == 'middle',
                        'align-items-start': customerstyle['container_positionVertical'] == 'top',
                        'align-items-end': customerstyle['container_positionVertical'] == 'bottom',

                        }" class="row login-row mb-3">

  <div class="col-md-8 col-lg-5 login-box">

    <div v-if="(customerstyle['client_logo_show'] || 'show') == 'show' && authRequest.info.log" class="w-100 d-flex justify-content-center">

      <div class="logo" :style="{backgroundImage: 'url(' + authRequest.info.log + ')'}">
      </div>

    </div>

    <div class="w-100 login-container mt-3 pb-2" :class="[`alert-${$store.state.alert}`]">

      <template v-if="!$store.state.activeModule">
        <h1 class="text-center mb-2">{{ $t("login.sign_in") }}</h1>
        <p class="text-center" v-if="customerstyle['client_name_show'] == 'show'">

          {{ $t('login.continueText', { application: authRequest.info.nam } )}}
          
        </p>
      </template>

      <div class="pt-2">
      <component :authRequest="authRequest" v-for="(group, key) in next" :class="key" :key="key" :is="key == 'social' ? 'socialGroup' : (key == 'register' ? 'registerGroup' : 'defaultGroup')">

        <!-- TODO: set active-attribute based on $route.params.module -->
        <template v-for="n in group">
          <component :lonely="n.id ==  $store.state.activeModule" v-if="!$store.state.activeModule || n.id ==  $store.state.activeModule"
            :customerstyle="customerstyle" v-bind:key="n.id" v-bind:is="n.type" :module="n" :authRequest="authRequest">
          </component>
        </template>
      </component>
      </div>

      <p class="text-center mb-0">
        <button class="btn btn-link text-muted" @click="cancel">
          <span>{{ $t('login.cancel') }}</span>
        </button>

        <button v-if="$store.state.activeModule && authRequest.next.length > 1" class="btn btn-link text-muted" @click="overview">
          <span>Use another method</span>
        </button>

      </p>

      <!-- TODO: only show if a subject is present! -->
      <p class="text-center mt-0" v-if="authRequest.info.subject == 'yes'">
        <button class="btn btn-link" @click="logout">
          <span>Log in as someone else</span>
        </button>
      </p>

    </div>

    <div class="info text-right mt-2">
      <a target="_blank" class="ml-3" v-if="authRequest.info.ter" :href="authRequest.info.ter">terms</a>
      <a target="_blank" class="ml-3" v-if="authRequest.info.pri" :href="authRequest.info.pri">privacy</a>
    </div>
  </div>
</div>
</template>

<style lang="scss">

.info a {
  color: white;
}

.form-control:focus {
  position: relative;
}

</style>


<script>
import socialGroup from './SocialGroup.vue'
import defaultGroup from './DefaultModuleGroup.vue'
import registerGroup from './RegisterGroup.vue'

import store from './store';

import activation from './modules/activation.vue'
import consent from './modules/consent.vue'
import facebook from './modules/facebook.vue'
import google from './modules/google.vue'
import linkedin from './modules/linkedin.vue'
import openIDConnect from './modules/openIDConnect.vue'
import otpMail from './modules/otpMail.vue'
import password from './modules/password.vue'
import passwordForgotten from './modules/passwordForgotten.vue'
import passwordless from './modules/passwordless.vue'
import register from './modules/register.vue'
import sms from './modules/sms.vue'
import totp from './modules/totp.vue'


import Vue from 'vue';

const modules = {
  socialGroup,
  defaultGroup,
  registerGroup,
  activation,
  consent,
  facebook,
  google,
  linkedin,
  openIDConnect,
  otpMail,
  password,
  passwordForgotten,
  passwordless,
  register,
  sms,
  totp
};

export default {
  props: ['next', 'authRequest', 'customerstyle'],

  components: modules,

  mounted() {

    // By default, no module should be active
    if (this.$route.params.module) {
      store.commit('activeModule', this.$route.params.module);
    } else {
      store.commit('activeModule', null);
    }

    

  },

  beforeRouteUpdate(to, from, next) {

    if (to.params.module) {
      store.commit('activeModule', to.params.module);
    } else {
      store.commit('activeModule', null);
    }

    next();
  },

  methods: {

    cancel() {
      document.location = this.authRequest.info.nok;
    },

    overview() {
      this.$store.commit('activeModule', null);
    },

    logout() {

      this.$get('/api/logout')
        .then(
          response => {

            document.location = this.authRequest.info.ret;

          },
          response => {

          }
        );

    },

  }
}
</script>
