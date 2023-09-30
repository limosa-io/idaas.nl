<template>

<div class="large-form-items">

<form v-if="lonely" v-on:submit.prevent="onSubmit">

  <template v-if="!done">

    <div class="form-group">
      <label for="username" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">{{ $t('login.username') }}</label>
      <input type="text" class="form-control" id="username" :placeholder="$t('login.usernamePlaceholder')" v-model="username">
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>
  

  <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block" type="submit">
    <span>{{ $t('login.activationButton') }}</span>
  </button>

  </template>
  <div v-else class="alert alert-info mt-2" role="alert">
    {{ $t('login.activationMailSend') }}
  </div>

</form>

<a v-else class="nav-link text-center" href="#" @click.prevent="activate()" active-class="active">{{ $t('login.activationLink') }}</a>

</div>


</template>

<script>
import Vue from "vue";
import base from "./Base";

import { EventBus } from "../eventBus.js";

export default Vue.extend({
  mixins: [base],

  data() {
    return {
      username: null,

      done: false,

      error: null
    };
  },

  mounted() {

    EventBus.$on("username", username => {
      this.username = username;
    });

    if(this.authRequest.info.hint){
      this.username = this.authRequest.info.hint;
    }

    if (this.lonely && (this.authRequest.info.subject != null || this.authRequest.info.hint != null) ) {
    // seems like 'watch' is always triggered
      this.autoSubmit();
    }
    
  },

  watch: {
    lonely: function(val){
      if(val){
      }
    }
  },

  methods: {

    autoSubmit(){

      this.request({}).then(
        response => {
          this.done = true;
        },
        error => {
          this.$noty({ text: error.data.error });
          this.done = false;
        }
      );

    },

    onSubmit() {

      if (this.username == null || this.username == "") {
        return;
      }

      this.request({
        username: this.username
      }).then(
        response => {
          this.done = true;
        },
        error => {
          this.$noty({ text: error.data.error });
          this.done = false;
        }
      );

      

    }
  }
});
</script>
