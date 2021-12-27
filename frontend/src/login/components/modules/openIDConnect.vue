<template>

<button class="btn btn-secondary btn-lg btn-block mt-1" :style="{'background-color': (module.config ? module.config.button_color : null) || '#e8711c'}" :class="{'btn-loading': loading}" @click="init" style="border-color: rgba(0,0,0,0.2);">
  <span>{{ $t(module.config ? module.config.button_text : 'login.oidcButton') }}</span>
</button>

</template>

<script>
import Vue from 'vue'
import base from './Base';

import {
  EventBus
} from "../eventBus.js";

export default Vue.extend({

  group: 'social',

  mixins: [base],

  data() {
    return {
      loading: false
    }
  },

  mounted() {

    this.loading = false;

    if (this.lonely && !this.isIncomplete()) {
      this.init();
    } else if (this.isIncomplete()) {

      this.$noty({
        text: 'Could not authenticate using the chosen method. Please choose a different method.'
      });

      //Not sure why 'next tick' is needed here. Without it, it doesn't work.
      this.$nextTick(() => {
        this.deactivate();
      })

    }

    EventBus.$on("username", username => {

    });

  },

  watch: {

    lonely: function (val) {
      this.loading = false;

      if (val && !this.isIncomplete()) {
        this.init();
      }
    }

  },

  methods: {
    init() {

      if ((new Date() - this.lastCalled) < 500) {
        return;
      }

      this.lastCalled = new Date();
      this.loading = true;
      
      this.$ice(this.module, this.authRequest, {
          init: true,
        })
        .then(
          response => {
            window.top.location = response.data.url;

          },
          response => {

          }
        );

    }
  }
});
</script>
