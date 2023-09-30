<template>

<div class="large-form-items">

  <template v-if="lonely">

  <form v-if="!done" v-on:submit.prevent="onSubmit">

      <div class="form-group">
        <label for="username" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">{{
          $t('login.username') }}</label>
        <input type="text" class="form-control" id="username" :placeholder="$t('login.usernamePlaceholder')" v-model="username">
      </div>

      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

      <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block"
        :class="{'btn-loading': isLoading}" type="submit">
        <span>{{ $t('login.otpEmailButton') }}</span>
      </button>    

  </form>

  <form v-else v-on:submit.prevent="onSubmitOtp">

      <p>{{ $t('login.otpEmailSuccess') }}</p>

      <div class="form-group">
        <label for="otp" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">{{
          $t('login.otp') }}</label>
        <input type="text" class="form-control" id="otp" v-model="otp">
      </div>

      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

      <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block"
        :class="{'btn-loading': isLoading}" type="submit">
        <span>{{ $t('login.otpEmailButtonFinish') }}</span>
      </button>
  </form>
  
  </template>

  <a v-else class="nav-link text-center" href="#" @click.prevent="activate()" active-class="active">
    {{ $t('login.otpEmailLink') }}
  </a>

</div>

</template>

<script>
import Vue from "vue";
import base from "./Base";
import store from '@/login/components/store.js'

import { EventBus } from "../eventBus.js";

export default Vue.extend({
  mixins: [base],

  data() {
    return {
      username: null,
      otp: null,

      remember: false,

      done: false,

      error: null,

      isLoading: false,

      user_id: null
    };
  },

  mounted() {

    EventBus.$on("username", username => {
      this.username = username;
    });

    if (this.lonely && this.authRequest.info.subject != null) {
    // seems like 'watch' is always triggered
    //  this.sendlink();
    }
    
  },

  watch: {
    lonely: function(val){
      if(val){
        //console.log('send from watch');
        //this.sendlink();
      }
    },

    done: function (val){
      store.commit('alert', val ? 'info' : 'none');
    }
  },

  methods: {

    sendlink() {

      this.activate();

      this.isLoading = true;
      this.request({}).then(
        response => {          
          this.done = true;
          this.user_id = response.data.user_id_hashed;
          this.isLoading = false;
        },
        error => {
          this.isLoading = false;
          $this.noty({ text: error.data });
        }
      );

      
    },

    onSubmitOtp(){
      
      this.request({
        otp: this.otp,
        user_id_hashed: this.user_id
      }).then(
        response => {
          console.log(response.data);
        },
        error => {
          this.$noty({ text: error.data.error });
        }
      );


    },

    onSubmit() {

      if (this.isLoading || this.username == null || this.username == "") {
        return;
      }

      this.isLoading = true;

      this.request({
        username: this.username,
        remember: this.remember
      }).then(
        response => {
          this.done = true;
          this.user_id = response.data.user_id_hashed;
          this.isLoading = false;
        },
        error => {
          this.loading = false;
          this.$noty({ text: error.data.error });
        }
      );

      

    }
  }
});
</script>
