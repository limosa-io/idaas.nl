
<script setup>
/**
 * Inits the log in process. 
 * 
 * The stored state is checked in CompleteLogin
 */

import { onBeforeMount } from 'vue';

onBeforeMount(() => {

  var oauth = {
    clientId: window.manageClient.clientId,
    authorize: window.manageClient.authorize,
    redirectUri: window.manageClient.redirectUri,
  };

  // FIXME: should use from-route
  // if (from.name != 'logout') {
  //   window.localStorage.setItem('goto', from.path);
  // } else {
    window.localStorage.removeItem('goto');
  // }


  var state = Math.random().toString(36).substring(2);
  var nonce = Math.random().toString(36).substring(2);

  window.localStorage.setItem('state', state);

  // TODO: make it configurable whether to prompt for login or not
  var url = oauth.authorize + '?response_type=code&client_id=' + window.encodeURIComponent(oauth.clientId) + '&redirect_uri=' + window.encodeURIComponent(oauth.redirectUri) + '&scope=openid+applications:manage&state=' + window.encodeURIComponent(state) + '&nonce=' + nonce;

  document.location = url;

});

</script>
