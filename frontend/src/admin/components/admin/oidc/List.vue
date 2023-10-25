
<template>
  <MainTemplate title="Applications">
    <template v-slot:header>
      <popup-demo-application ref="popupdemo" />

        <a
          class="btn btn-md btn-secondary float-right mr-2"
          :href="getOidcUrl('/.well-known/openid-configuration')"
          target="_blank"
          >OpenID Configuration</a
        >
        <a
          class="btn btn-md btn-secondary float-right mr-2"
          :href="getOidcUrl('/.well-known/jwks.json')"
          target="_blank"
          >JWK</a
        >

      <MenuButton to="/applications/oidc/add" class="mr-2" >Add Application</MenuButton>

      <button
        @click="createTestClient"
        class="btn btn-md btn-secondary float-right mr-2"
      >
        Add Test Application
      </button>
      
      <router-link
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
  </MainTemplate>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import {useRouter} from 'vue-router4'

import { getDecodedAccesstoken, getOidcUrl } from "@/admin/helpers.js";
import PopupDemoApplication from "./demo/PopupDemoApplication.vue";

const router = useRouter();
const clients = ref(null);
const search = ref(null);
const currentClientId = ref(null);

const popupdemo = ref(null);

onMounted(() => {
  currentClientId.value = getDecodedAccesstoken().aud;

  axios.get("https://login.notidaas.nl/oauth/connect/register", {headers: {
    'Authorization': 'Bearer ' + window.sessionStorage.getItem('access_token')
  }}).then(
    (response) => {
      clients.value = response.data;
    },
    (response) => {
      // error callback
    }
  );
});

const clients_filtered = computed(() => {
  return clients.value != null
    ? clients.value.filter((value) => {
        return (
          search.value == null ||
          value.client_id.includes(search.value) ||
          value.client_name.toLowerCase().includes(search.value.toLowerCase())
        );
      })
    : [];
});

function edit(client) {  
  router.push({
    name: "oidc.client.edit",
    params: { client_id: client.client_id },
  });
}

function createTestClient() {
  popupdemo.value.show();
}
</script>
