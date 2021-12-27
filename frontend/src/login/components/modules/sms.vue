<template>

<div class="panel panel-default">
  <div class="panel-heading">{{ $('login.smsTitle') }}</div>

  <div class="panel-body">

    <form v-on:submit.prevent="onSubmit">
      <div class="form-group">
        <label for="formGroupExampleInput" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">OTP</label>
        <input type="text" class="form-control" id="formGroupExampleInput" placeholder="Example input"  v-model="otp">
      </div>

      <button class="btn btn-primary" type="submit">
        <span>{{ $('login.smsButton') }}</span>
      </button>
    </form>

  </div>
</div>

</template>

<script>

import Vue from 'vue'
import base from './Base';
import { EventBus } from '../eventBus.js';

const URL_MODULE_NAME = "NAME_OF_THE_MODULE";

export default Vue.extend({

  mixins: [base],

  data(){
    return {
      otp: null,
    };
  },

  methods: {
    onSubmit(){
      
      this.$http
        .post(this.authRequest.info.api.replace(URL_MODULE_NAME, this.module.id), {
            otp: this.otp
          }, {
          headers: {
            "X-AuthRequest": this.authRequest.jti
          }
        })
        .then(
          response => {

            EventBus.$emit('newAuthRequest', response.headers.get("X-AuthRequest"));
            
            // response.body, response.status

          },
          response => {
            this.error = response.body.error;
            EventBus.$emit('newAuthRequest', response.headers.get("X-AuthRequest"));
          }
        );

      // this.$emit('process', { 
      //   username: this.username,
      //   password: this.password
      // });

    }
  }
});
</script>
