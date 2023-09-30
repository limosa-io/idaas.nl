<template>
  <div class="large-form-items">
    <div v-if="lonely" class="text-center">
      <p class="font-weight-bold">
        Insert your security key
      </p>
      <p>
        If your security key has a button, tap it.<br />
        If it doesn't, remove and re-insert it.
      </p>
      <img :src="fido" width="60" class="mb-4"/>
      
    </div>

    <a
      v-else
      class="nav-link text-center"
      href="#"
      @click="init()"
      active-class="active"
    >
      Security Key
    </a>
  </div>
</template>
    
    <script>
import Vue from "vue";
import base from "./Base";
import fido from '/frontend/src/login/assets/fido.svg'

function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode( bytes[ i ] );
    }
    return window.btoa(binary);
}

export default Vue.extend({
  mixins: [base],

  data() {
    return {
      username: null,
      password: null,

      remember: false,

      done: false,

      error: null,

      isLoading: false,
      fido: fido
    };
  },

  mounted() {
    if(this.lonely){
      this.init();
    }
  },

  methods: {
    recursiveBase64StrToArrayBuffer: function (obj) {
      let prefix = "=?BINARY?B?";
      let suffix = "?=";
      if (typeof obj === "object") {
        for (let key in obj) {
          if (typeof obj[key] === "string") {
            let str = obj[key];
            if (
              str.substring(0, prefix.length) === prefix &&
              str.substring(str.length - suffix.length) === suffix
            ) {
              str = str.substring(prefix.length, str.length - suffix.length);

              let binary_string = window.atob(str);
              let len = binary_string.length;
              let bytes = new Uint8Array(len);
              for (let i = 0; i < len; i++) {
                bytes[i] = binary_string.charCodeAt(i);
              }
              obj[key] = bytes.buffer;
            }
          } else {
            this.recursiveBase64StrToArrayBuffer(obj[key]);
          }
        }
      }
    },

    init() {
      var c = null;
      this.request({
        init: true,
      }).then((response) => {
        const args = JSON.parse(JSON.stringify(response.body.arguments));
        c = response.body.challenge;
        this.recursiveBase64StrToArrayBuffer(args);
        return navigator.credentials.get(args);
      }).then(cred => {

        const authenticatorAttestationResponse = {
            id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
            clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
            signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null,
            userHandle: cred.response.userHandle ? arrayBufferToBase64(cred.response.userHandle) : null
        };

        return this.request({
          verify: true,
          response: authenticatorAttestationResponse,
          challenge: c
        })

      }).then(r => {

      });
    },

  },
});
</script>
    