<template>

<div>

  <h1>{{ $t('login.consentText', { application: authRequest.info.nam } )}}</h1>

  <div>

    <div class="list-group mb-2">

      <ul class="list-group">
        <li class="list-group-item" v-for="s of this.authRequest.info.sco" :key="s">
          {{ $t(scopeDescription[s] ? scopeDescription[s] : s) }}</li>
      </ul>

    </div>

    <button :style="{backgroundColor: customerstyle['button_backgroundColor']}" class="btn btn-primary btn-block" @click="init">
      <span>{{ $t('login.consentAllow') }}</span>
    </button>
  </div>

</div>

</template>

<script>

import Vue from 'vue'
import base from './Base';
import {
  EventBus
} from '../eventBus.js';

export default Vue.extend({

  mixins: [base],

  mounted() {

    this.request({
        init: true,
      })
      .then(
        response => {
          this.scopeDescription = response.data;
        }
      );

  },

  data() {
    return {
      scopeDescription: {
      },

      scopeIcons: {
        openid: 'shoe-prints',
        email: 'at',

      }
    };
  },

  methods: {
    init() {
      this.request({});
    }
  }
});

</script>

<style lang="scss" scoped>

.list-group-item{
    height: 62px;
    padding-top: 20px;
    border-left-style: none;
    border-right-style: none;
    border-radius: 0px;

    svg {
        margin-right: 10px;
    }
}

</style>
