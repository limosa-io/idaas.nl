<!--
Initiates a OpenID Connect logout
-->
<template>
  <div class="logout-container">
    <template v-if="logged_out">
      <h1>You are now logged out.</h1>
    </template>
  </div>
</template>

<style lang="scss" scoped>
.logout-container {
  text-align: center;
  font-family: Arial;
  display: table-cell;
  vertical-align: middle;
  width: 100vw;
  height: 100vh;
  background-color: #58884e;
  color: white;
}
</style>

<script setup>
import { onMounted, getCurrentInstance, ref } from "vue";
import { useStateStore } from "@/login/components/store.js";
import { useRouter } from 'vue-router4';
const vue = getCurrentInstance();
const state = useStateStore();
const logged_out = ref(false);
const router = useRouter();

onMounted(() => {
  if (window.oauthLogout.valid && window.oauthLogout.post_logout_redirect_uri) {
    if (window.oauthLogout.response_mode == "query") {
      document.location = `${window.oauthLogout.post_logout_redirect_uri}${
        window.oauthLogout.state
          ? "?state=" + decodeURIComponent(window.oauthLogout.state)
          : ""
      }`;
    } else if (window.oauthLogout.response_mode == "web_message") {
      // to parent ...
      parent.postMessage(
        {
          type: "logout_response",
          response: {
            state: window.oauthLogout.state,
          },
        },
        window.oauthLogout.post_logout_redirect_uri
      );
    } else {
      state.error = "invalid_response_mode";
      router.replace({ name: "error.default" });
    }
  } else if (window.oauthLogout.valid) {
    logged_out = true;
  } else {
    state.error = "invalid_logout_url";
    router.replace({ name: "error.default" });
  }
});
</script>

