<template>
  <div id="app">
    <template v-if="access_token == null">
      <div class="login">
        <div class="d-flex align-content-stretch flex-wrap">
          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>

          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>

          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>
          <div class="col-3"></div>
        </div>

        <button class="login" @click="login">LOGIN</button>
      </div>
    </template>
    <template v-else>
      <div class="logged-in">
        <div class="row d-flex justify-content-center">
          <div class="col-8 p-3">
            <p>Hi {{ greeting }}, you have been successfully logged in.</p>
            <h2 class="mt-2">Access Token</h2>

            <div class="code">{{ access_token }}</div>
            <h2 class="mt-2">ID Token</h2>
            <div class="code">{{ id_token }}</div>
            <button class="btn btn-link ml-0 pl-0" @click="logout">Log me out</button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import Idaas from "idaas-core";
import "../node_modules/bootstrap/dist/css/bootstrap.min.css";

let client_id = "{{ client_id }}";
let tenant = "{{ tenant }}";

if (client_id.includes("{{ client_id")) {
  window.alert(
    "Looks like this application has not been deployed correctly. The client_id is not set"
  );
}
if (tenant.includes("{{ tenant")) {
  window.alert(
    "Looks like this application has not been deployed correctly. Tenant is not set"
  );
}

let idaas = new Idaas({
  tenant: tenant,
  client_id: client_id,
  redirect_uri: document.location.href,
  post_logout_redirect_uri: document.location.href,
  state_in: "query",
  style: {
    container_backgroundColor: "rgba(0,0,0,0.5)",
    container_positionHorizonal: "right"
  }
});
//TODO: if Logged in, show "Hello John ... your are now logged in. Go ahead. Show id_token, access_token, refresh_token"
export default {
  name: "App",
  components: {},

  mounted() {},

  data() {
    return {
      in_iframe: false,
      url: null,
      current: null,

      id_token: null,
      access_token: null,
      refresh_token: null
    };
  },

  methods: {
    login() {
      idaas
        .login({
          acr_values: []
        })
        .then(response => {
          console.log(response);
          this.access_token = response.access_token;
          this.id_token = response.id_token;
        })
        .catch(response => {
          console.log("catched!");
          console.log(response);
          console.log(response.error);
        });
    },

    logout() {
      idaas.logout().then(data => {
        this.access_token = null;
        this.id_token = null;
        console.log("now logged out. Yes.");
      });
    }
  }
};
</script>

<style lang="scss">
@import url("https://blokkfontcom-losgordos.netdna-ssl.com/v2/blokkfont.css");

#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: left;
  color: #2c3e50;
  margin: 0px;
  height: 100vh;
  width: 100vw;
}

div.code {
  width: 100%;
  max-width: 100%;
  overflow-wrap: break-word;
  word-wrap: break-word;
  padding: 10px;
  background-color: rgb(63, 63, 63);
  color: white;
  font-family: Courier;

  -ms-word-break: break-all;
  /* This is the dangerous one in WebKit, as it breaks things wherever */
  word-break: break-all;
  /* Instead use this non-standard one: */
  word-break: break-word;
}

.login {
  button {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 250px;
    height: 80px;
    font-weight: bold;
    box-shadow: 10px 10px 73px 28px rgba(0, 0, 0, 0.75);
    background-color: rgb(26, 26, 26);
    border-radius: 10px;
    margin-left: -100px;
    margin-top: -20px;
    border-style: solid;
    border-width: 2px;
    border-color: black;
    color: white;
  }

  .d-flex {
    height: 100vh;
  }

  .d-flex {
    > div {
      &:nth-child(1) {
        background-color: #1abc9c;
      }
      &:nth-child(2) {
        background-color: #2ecc71;
      }
      &:nth-child(3) {
        background-color: #3498db;
      }
      &:nth-child(4) {
        background-color: #9b59b6;
      }

      &:nth-child(5) {
        background-color: #16a085;
      }
      &:nth-child(6) {
        background-color: #27ae60;
      }
      &:nth-child(7) {
        background-color: #2980b9;
      }
      &:nth-child(8) {
        background-color: #8e44ad;
      }

      &:nth-child(9) {
        background-color: #f1c40f;
      }
      &:nth-child(10) {
        background-color: #e67e22;
      }
      &:nth-child(11) {
        background-color: #e74c3c;
      }
      &:nth-child(12) {
        background-color: #ecf0f1;
      }
    }
  }
}

#app .logged-in {
  h2 {
    font-size: 18pt;
  }
}
</style>
