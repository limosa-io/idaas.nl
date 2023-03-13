
<template>
  <Main title="Applications">
    <template v-slot:header>
      <popup-demo-application ref="popupdemo" />

      <b-dropdown text right class="float-right">
        <a
          class="dropdown-item"
          :href="$oidcUrl('.well-known/openid-configuration')"
          target="_blank"
          >OpenID Configuration</a
        >
        <a
          class="dropdown-item"
          :href="$oidcUrl('.well-known/jwks.json')"
          target="_blank"
          >JWK</a
        >
      </b-dropdown>

      <router-link
        tag="button"
        class="btn btn-md btn-primary float-right mr-2"
        to="/applications/oidc/add"
        >Add Application</router-link
      >

      <button
        @click="createTestClient"
        class="btn btn-md btn-secondary float-right mr-2"
      >
        Add Test Application
      </button>

      <router-link
        tag="button"
        class="btn btn-md btn-secondary float-right mr-2"
        to="/applications/oidc/settings"
        >Settings</router-link
      >
    </template>

    <template v-slot:body>
      <div class="d-flex flex-row justify-content-between">
        <div>Manage your OpenID Connect applications.</div>

        <form class="form-inline" v-on:submit.prevent="onSubmit">
          <label class="sr-only" for="search.email">E-mail</label>
          <input
            type="search"
            class="form-control form-control-sm mb-2 mr-sm-2"
            id="search"
            v-model="search"
            placeholder=""
          />
          <button type="submit" class="btn btn-primary mb-2 btn-sm">
            Search
          </button>
        </form>
      </div>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Application Name</th>
            <th scope="col">Type</th>
            <th scope="col" class="d-none d-lg-block">Redirect URIs</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(client, index) in clients_filtered"
            @click="edit(client)"
            :key="index"
          >
            <th scope="row" class="pt-4">{{ index + 1 }}</th>
            <td class="pt-4">
              {{ client.client_name }}
              <span
                class="badge badge-pill badge-info d-none d-lg-inline"
                v-b-tooltip.hover
                title="The management user interface you are now using, makes use of this client."
                v-if="client.client_id == currentClientId"
                >in use</span
              >
            </td>
            <td class="pt-4">{{ client.application_type }}</td>
            <td class="d-none d-lg-block">
              <ul
                v-for="(uri, index) in client.redirect_uris"
                class="list-group"
                :key="index"
              >
                <li class="list-group-item">{{ uri }}</li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </Main>
</template>

<script>
import Vue from "vue";

// /home/arie/git/ice-complete/ice/frontend/src/admin/helpers.js
// /home/arie/git/ice-complete/ice/frontend/src/admin/components/general/Main.vue
import { getDecodedAccesstoken } from "@/admin/helpers.js";
import PopupDemoApplication from "./demo/PopupDemoApplication.vue";
import Main from "@/admin/components/general/Main.vue";

export default {
  components: {
    PopupDemoApplication,
    Main,
  },

  data() {
    return {
      clients: null,

      search: null,

      currentClientId: null,
    };
  },

  computed: {
    clients_filtered: function () {
      return this.clients
        ? this.clients.filter((value) => {
            console.log(value.client_id);
            return (
              this.search == null ||
              value.client_id.includes(this.search) ||
              value.client_name
                .toLowerCase()
                .includes(this.search.toLowerCase())
            );
          })
        : [];
    },
  },

  methods: {
    edit: function (client) {
      this.$router.push({
        name: "oidc.client.edit",
        params: { client_id: client.client_id },
      });
    },

    createTestClient() {
      this.$refs.popupdemo.show();
    },
  },

  mounted() {
    this.currentClientId = getDecodedAccesstoken().aud;

    Vue.http.get(Vue.oidcUrl("oauth/connect/register")).then(
      (response) => {
        this.clients = response.data;
      },
      (response) => {
        // error callback
      }
    );
  },
};
</script>
