<template>

<div class="large-form-items">
  <div v-if="lonely" class="panel panel-default">

    <div class="panel-heading">{{ $t('login.hotpTitle') }}</div>

    <div class="panel-body">

      <p>{{ $t('login.hotpDescription') }}</p>

      <form v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label :style="{display: customerstyle.label_display != 'show' ? 'none' : 'block' }" for="otp">{{ $t('login.hotpLabel') }}</label>
          <input type="text" class="form-control" id="otp" placeholder="" v-model="otp">
        </div>

        <div class="form-check">
          <input v-model="remember" type="checkbox" class="form-check-input" id="remember">
          <label class="form-check-label" for="remember">{{ $t('login.hotpTrustDevice') }}</label>
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>

        <button :style="{backgroundColor: (customerstyle.button || {}).backgroundColor}" class="btn btn-primary btn-block"
          type="submit">
          <span>{{ $t('login.hotpButton') }}</span>
        </button>

      </form>

    </div>
  </div>

  <div v-else>

    <a class="nav-link text-center" href="#" @click.prevent="activate()" active-class="active">{{ $('login.hotpLink')
      }}</a>

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
            remember: null,
            error: null
        }
    },

    mounted() {
        

    },

    methods: {
        onSubmit: function (event) {

            this.request({
                    otp: this.otp,
                    remember: this.remember
                }).then(result => {

                }, error => {
                  this.$noty({text: error.data.error});
                });

            // TODO: show errors this.error = response.body.error;

            event.preventDefault();
        }
    }
});

</script>
