
<template>
<div v-if="isError" class="error-container" :class="{'error-access-denied': isAccessDenied}">
  <template v-if="isAccessDenied">
      <h1>You canceled log in</h1>
    <p>It seems like you've cancelled the log in process. <router-link to="/">Retry</router-link></p>
  </template>
  <template v-else>
    <h1>Could not log in</h1>
    <p>There were some problems with getting you logged in. <router-link to="/">Retry</router-link>.</p>
  </template>
</div>
</template>

<style lang="scss">
.error-container {
    text-align: center;
    font-family: Arial;
    display: table-cell;
    vertical-align: middle;
    width: 100vw;
    height: 100vh;
    background-color: #d9342e;
    color: white;

    &.error-access-denied{
      background-color: #ffa51e;
    }

    a, a:hover {
      color: white;
      text-decoration: underline;
    }
}
</style>

<script setup>

import {ref, onMounted, getCurrentInstance} from 'vue';
import {laxios} from '@/admin/helpers.js';
import { useRouter } from 'vue-router4';

const vue = getCurrentInstance();
const router = useRouter();

/**
 * Obtains the access token from the fragment parameters.
 * 
 * Check the state, as set in InitLogin
 */
import queryString from 'query-string';

const isError = ref(false);
const isAccessDenied = ref(false);

onMounted(() => {

  const parsed = Object.assign({}, queryString.parse(location.hash), queryString.parse(location.search));

  const error = parsed.error;

  if (error) {

    isError.value = true;

    if (error == 'access_denied') {

      isAccessDenied.value = true;

    } else {
      //unexpected
    }
  } else if (window.localStorage.getItem('state') == null) {

    isError.value = true;
    console.error('No state was set!');

  } else if (parsed.state != window.localStorage.getItem('state')) {

    isError.value = true;
    console.error('The returned state is different than the requested state. Perhaps because you\'ve started multiple logins');

  } else {

    let code = parsed.code || parsed.code;

    laxios.post('token', {
      client_id: window.manageClient.clientId,
      grant_type: 'authorization_code',
      code: code,
      redirect_uri: window.manageClient.redirectUri
    }, {
      public: true
    }).then(response => {
      
      window.sessionStorage.setItem('access_token', response.data.access_token);
      window.sessionStorage.setItem('refresh_token', response.data.refresh_token);

      window.localStorage.removeItem('state');

      let goto = window.localStorage.getItem('goto') || '/';
      window.localStorage.removeItem('goto');

      router.push(goto);

    }).catch(response => {
      console.log(response);
      isError.value = true;
      console.error('Could not exchange code for an access token');
    });
  }
});

</script>
