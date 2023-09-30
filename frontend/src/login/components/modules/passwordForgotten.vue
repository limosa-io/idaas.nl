<template>

<div class="">

  <form v-if="lonely && isIncomplete() && getIncompleteModuleState().state == 'confirmed'" v-on:submit.prevent="onSubmitPassword">
    <h1 class="mt-3">{{ $t('login.resetPasswordChangeTitle') }}</h1>
    <p>{{ $t('login.resetPasswordChangeDescription') }}</p>
    <div class="form-group">
      <label for="password" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">{{ $t('login.resetPasswordChangeFirst') }}</label>

      <div class="input-group mb-3">
        <input tabindex="1" autofocus :type="showFirst ? 'text' : 'password'" id="password" class="form-control" placeholder="" v-model="password" />
        <div class="input-group-append">
          <button tabindex="-1" class="btn btn-outline-secondary" type="button" @click="showFirst = !showFirst">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

    </div>

    <div class="form-group">
      <label for="passwordRepeat">{{ $t('login.resetPasswordChangeSecond') }}</label>

      <div class="input-group mb-3">
        <input tabindex="2" :type="showSecond ? 'text' : 'password'" id="passwordRepeat" class="form-control" placeholder="" v-model="passwordRepeat" />
        <div class="input-group-append">
          <button tabindex="-1" class="btn btn-outline-secondary" type="button" @click="showSecond = !showSecond">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div v-if="errorRepeat" class="alert alert-danger" role="alert">
        {{ errorRepeat }}
      </div>

    </div>


    <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block" type="submit">
      <span>{{ $t('login.resetPasswordChangeButton') }}</span>
    </button>

  </form>

  <form v-else-if="lonely" v-on:submit.prevent="onSubmit" class="large-form-items">

    <template v-if="!done">
      
      <div class="form-group">
        <label for="username" :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block'}">Username</label>
        <input type="text" class="form-control" id="username" placeholder="Username or Email" v-model="username">
      </div>

      <div v-if="error" class="alert alert-danger" role="alert">
        {{ error }}
      </div>

      <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block" :class="{'btn-loading': isLoading}" type="submit">
        <span>{{ $t('login.resetPasswordButton') }}</span>
      </button>

    </template>
    <div v-else class="mt-3 mb-3 text-center pre-wrap" role="alert">
      <p>{{ $t('login.resetPasswordChangeSuccess') }}</p>
      
    </div>

  </form>

  <a v-else class="nav-link text-center mt-0 mb-0" href="#" @click.prevent="activate()" active-class="active">{{ $t('login.passwordforgottenLink') }}</a>

</div>

</template>

<script>
import Vue from "vue";
import base from "./Base";
import { library } from '@fortawesome/fontawesome-svg-core'
import {faEye} from '@fortawesome/free-solid-svg-icons'
import store from '@/login/components/store.js'

library.add(faEye);

import { EventBus } from "../eventBus.js";

export default Vue.extend({
  mixins: [base],

  data() {
    return {
      username: null,

      password: null,
      passwordRepeat: null,

      showFirst: false,
      showSecond: false,

      remember: false,

      done: false,

      error: null,
      errorRepeat: null,

      isLoading: false,
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
        console.log('send from watch');
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
        _response => {
          this.isLoading = false;
          this.done = true;
        },
        error => {
          this.isLoading = false;
          $this.noty({ text: error.data });
        }
      );

      
    },

    onSubmitPassword(){

      if(this.password != this.passwordRepeat){
        this.errorRepeat = 'The provided passwords are not equal';
        return;
      }

      this.request({
        password: this.password,
      }).then(
        response => {
          this.$noty({ text: 'Your password has been updated', 'type':'info' });
        },
        error => {
          this.$noty({ text: error.data.error });
        }
      );

    },

    onSubmit() {

      if(this.isLoading){
        return;
      }

      if (this.username == null || this.username == "") {
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