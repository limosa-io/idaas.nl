<template>

<form v-on:submit.prevent="onSubmit" class="mb-3">
  <div class="form-group">
    <label :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}" for="usernameInput">{{ $t('login.username') }}</label>
    <input type="text" class="form-control" id="usernameInput" :placeholder="$t('login.usernamePlaceholder')" v-model="username">
  </div>

  <div class="form-group">
    <label :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}" for="passwordInput" class="mt-2">{{ $t('login.password') }}</label>
    <input type="password" class="form-control" id="passwordInput" :placeholder="$t('login.passwordPlaceholder')" v-model="password">
  </div>

  <div class="form-check">
    <input v-model="remember" type="checkbox" class="form-check-input" id="remember">
    <label class="form-check-label" for="remember">{{ $t('login.remember') }}</label>
  </div>

  <div v-if="error" class="alert alert-danger" role="alert">
    {{ error }}
  </div>

  <button :style="{backgroundColor: customerstyle['button_backgroundColor'] || '#FF0000'  }" class="btn btn-primary btn-block"
    type="submit">
    <span>{{ $t('login.loginButton') }}</span>
  </button>

</form>

</template>

<script>
import Vue from "vue";
import base from "./Base";

import {
  EventBus
} from "../eventBus.js";

export default Vue.extend({
  mixins: [base],

  data() {
    return {
      username: null,
      password: null,

      remember: true,

      error: null

    };
  },

  watch: {
    username: function (val) {
      EventBus.$emit("username", val);
    }
  },

  mounted() {

    if(this.authRequest.info.hint){
      this.username = this.authRequest.info.hint;
    }
    
  },

  methods: {
    onSubmit() {

      if(this.loading){
        return;
      }

      
      this.request({
        username: this.username,
        password: this.password,
        remember: this.remember
      }).then(
        response => {
          // do nothing special
        },

        response => {

          if(response.status == 422){
            this.$noty({
              text: "The provided username or password is incorrect."
            });
          }
        }
      );
    }
  }
});

</script>
