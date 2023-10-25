<template>
  <div class="large-form-items">
    <div v-if="props.lonely" class="text-center">
      <p class="font-weight-bold">Insert your security key</p>
      <p>
        If your security key has a button, tap it.<br />
        If it doesn't, remove and re-insert it.
      </p>
      <img :src="fidoImage" width="60" class="mb-4" />
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
    
    <script setup>
import fidoImage from "/frontend/src/login/assets/fido.svg";
import { onMounted, defineProps, ref } from "vue";
import { baseProps, request } from "./composable";

const props = defineProps(baseProps);

function arrayBufferToBase64(buffer) {
  let binary = "";
  let bytes = new Uint8Array(buffer);
  let len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}

onMounted(() => {
  if (props.lonely) {
    init();
  }
});

function recursiveBase64StrToArrayBuffer(obj) {
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
        recursiveBase64StrToArrayBuffer(obj[key]);
      }
    }
  }
}

function init() {
  var c = null;
  request(props.module, props.authRequest, {
    init: true,
  })
    .then((response) => {
      const args = JSON.parse(JSON.stringify(response.data.arguments));
      c = response.data.challenge;
      recursiveBase64StrToArrayBuffer(args);
      return navigator.credentials.get(args);
    })
    .then((cred) => {
      const authenticatorAttestationResponse = {
        id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
        clientDataJSON: cred.response.clientDataJSON
          ? arrayBufferToBase64(cred.response.clientDataJSON)
          : null,
        authenticatorData: cred.response.authenticatorData
          ? arrayBufferToBase64(cred.response.authenticatorData)
          : null,
        signature: cred.response.signature
          ? arrayBufferToBase64(cred.response.signature)
          : null,
        userHandle: cred.response.userHandle
          ? arrayBufferToBase64(cred.response.userHandle)
          : null,
      };

      return request(
      props.module, props.authRequest,  
      {
        verify: true,
        response: authenticatorAttestationResponse,
        challenge: c,
      });
    })
    .then((r) => {
      // TODO: implement ..?
    }).catch(e => {
      console.error('error!!');
      console.error(e);
    });
}
</script>
    