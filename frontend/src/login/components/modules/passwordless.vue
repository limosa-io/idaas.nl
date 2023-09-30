<template>

<div class="large-form-items">

  <form v-if="lonely" v-on:submit.prevent="onSubmit">

    <template v-if="!done">


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
        <span>{{ $t('login.passwordlessButton') }}</span>
      </button>

    </template>

    <div v-else class="alert mt-2" role="alert">{{ $t('login.passwordlessSuccess') }}</div>

  </form>

  <a v-else class="nav-link text-center" href="#" @click.prevent="activate()" active-class="active">
    {{ $t('login.passwordlessLink') }}
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
      password: null,

      remember: false,

      done: false,

      error: null,

      isLoading: false
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
          this.isLoading = false;
        },
        error => {
          this.isLoading = false;
          $this.noty({ text: error.data });
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
