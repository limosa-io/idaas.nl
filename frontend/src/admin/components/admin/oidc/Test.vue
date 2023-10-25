
<template>
  <div>

    <div class="bgc-white bd bdrs-3 p-3 mt-2" v-if="clients">

      <h3 class="c-grey-900">Test OpenID Clients</h3>

      <p>Test your OpenID Connect clients</p>

      <div v-if="!hasTestClient" class="alert alert-warning" role="alert">
        It looks like you do not have a test client installed. <a href="#" @click.prevent="createTestClient">Create
          one.</a>
      </div>

      <div class="form-group row">
        <div class="col-md-3">
          Client
        </div>
        <div class="col">

          <div class="input-group mb-3">

            <FormSelect v-model="client_id_selected" value-field="client_id" text-field="client_name" :options="clients"
              class="form-control" />
            <div class="input-group-append">
              <a @click="router.push({ name: 'oidc.client.edit', params: { client_id: requestParameters.client_id } });"
                v-if="requestParameters.client_id" class="btn btn-outline-primary btn-primary" type="button">Edit</a>
            </div>

          </div>

        </div>

      </div>

      <template v-if="clientSelected">

        <div class="form-group row">
          <div class="col-md-3">
            Response Type
          </div>
          <div class="col">

            <multiselect id="scopes" v-model="requestParameters.response_type"
              :options="clientSelected.response_types || []" :searchable="false" :close-on-select="true"
              :show-labels="true" :multiple="true" placeholder="Pick a value">
              <template slot="option" slot-scope="props">{{ props.option }}</template>
            </multiselect>

          </div>

        </div>

        <div class="form-group row">
          <div class="col-md-3">
            Redirect URI
          </div>
          <div class="col">
            <FormSelect v-model="requestParameters.redirect_uri" :options="clientSelected.redirect_uris || []"
              class="form-control" />
          </div>

        </div>

        <div class="form-group row">

          <label class="col-md-3" for="scopes">Scopes.</label>
          <div class="col">
            <multiselect id="scopes" v-model="requestParameters.scope" :options="provider.scopes_supported"
              :searchable="false" :close-on-select="true" :show-labels="true" :multiple="true" placeholder="Pick a value">
              <template slot="option" slot-scope="props">{{ props.option }}</template>
            </multiselect>
          </div>

        </div>

        <div class="form-group row">

          <label class="col-md-3" for="scopes">ACR Values.</label>
          <div class="col">
            <multiselect id="acr_values" v-model="requestParameters.acr_values" :options="provider.acr_values_supported"
              :searchable="false" :close-on-select="true" :show-labels="true" :multiple="true" placeholder="Pick a value">
              <template slot="option" slot-scope="props">{{ props.option }}</template>
            </multiselect>
          </div>
        </div>

        <div class="row">
          <div class="col-3">

          </div>
          <div class="col">
            <a class="btn btn-primary" role="button" :href="testUrl">Test</a>
            <p>Instead of clicking the above test-button, you might want to right-click on it and click "Open Link in
              Ingognito Window" (or similar).</p>
          </div>
        </div>


      </template>

    </div>

  </div>
</template>


<script setup>
// TODO: query for clients registered.
// Check if 'test client' has been installed. Check by name?? Or by 'redirect_uri'? If not exists => suggest to create it.
// Show list of clients. like existing Test.vue component.
// When client is selected, show options  for selectint available scopes, response_types, redirect_uri-s etc.    

import { useRouter } from 'vue-router4'
import queryString from 'query-string';
import sha256 from 'hash.js/lib/hash/sha/256';
import { notify } from '../../../helpers';
// import crypto from 'crypto';
import { ref, reactive, watch, computed, onMounted } from 'vue';

import { maxios, laxios } from '@/admin/helpers.js'

function webSafe64(base64) {
  return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

const router = useRouter();
const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const clients = ref(null);
const clientSelected = ref(null);
const provider = ref(null);
const client_id_selected = ref(null);
const hasTestClient = ref(true);
const requestParameters = ref({
  client_id: '123'
});

const optionsResponseType = computed(() => {
  return [{ value: 'a', text: 'This is First option' }, { value: 'b', text: 'asdg' }];
});

const testUrl = computed(() => {

  var result = {};

  if (requestParameters.value)
    for (var key in requestParameters.value) {
      if (typeof requestParameters.value[key] === 'string') {
        result[key] = requestParameters.value[key];
      } else {
        var temp = requestParameters.value[key];

        if (key == 'scope') {
          temp.sort((a, b) => { return a == 'openid' ? -1 : 1 });
        } else if (key == 'response_type') {
          temp.sort((a, b) => {
            return a == 'code' ? -1 : (a == 'id_token' && b != 'code' ? -1 : 1)
          });
        }

        result[key] = temp.join(' ');

      }
    }
});

// see https://tools.ietf.org/html/rfc7636

watch(client_id_selected, (val) => {
  if (clientSelected.value == null || clientSelected.value.client_id != val) {

    for (var client of clients.value) {

      if (client.client_id == val) {
        clientSelected.value = client;
        break;
      }
    }
  }

  requestParameters.value.client_id = val;
});

watch(requestParameters, (val) => {
  localStorage.setItem('test.parameters', JSON.stringify(val));
}, { deep: true });


onMounted(() => {
  laxios.get('/.well-known/openid-configuration', {
    public: true
  }).then(response => {

    provider.value = response.data;

  }, response => {
    // error callback
  });
});

function loadClients() {

  return new Promise((resolve, reject) => {
    laxios.get('oauth/connect/register').then(response => {
      clients.value = Object.values(response.data);

      hasTestClient.value = clients.value.find(e => e.client_name == 'Test Client') != undefined;

      requestParameters.value = JSON.parse(localStorage.getItem('test.parameters')) || {};

      client_id_selected.value = requestParameters.value.client_id || null;

    }).catch(response => {
      // error callback
      reject(response);
    });
  });
}

function create_test_client() {
  let testClient = {
    "secret": "veS37VvsKG5lim9pE7PrmqCicTEQOPz2RcBW61jP",
    "redirect_uris": [window.location.protocol + "//" + window.location.host + "/tester"],
    "post_logout_redirect_uris": [window.location.protocol + "//" + window.location.host + "/tester/logout"],
    "response_types": ["token", "code", "id_token"],
    "grant_types": ["authorization_code", "implicit", "refresh_token"],
    "code_challenge_methods_supported": ["S256", "plain"],
    "application_type": "web",
    "public": "confidential",
    "contacts": null,
    "logo_uri": "https:\/\/upload.wikimedia.org\/wikipedia\/commons\/1\/11\/Test-Logo.svg",
    "client_uri": null,
    "policy_uri": null,
    "tos_uri": null,
    "token_endpoint_auth_method": "client_secret_post",
    "jwks_uri": null,
    "jwks": null,
    "default_max_age": null,
    "default_prompt": null,
    "default_prompt_allow_override": true,
    "default_acr_values_allow_override": true,
    "require_auth_time": null,
    "initiate_login_uri": null,
    "trusted": false,
    "client_name": "Test Client"
  };


  laxios.post('oauth/connect/register',

    testClient.value
  ).then(response => {
    return loadClients();
  }).then(response => {
    notify({ text: 'We have succesfully saved your new OpenID Client.' });
  }).catch(response => {
    notify({ text: 'We could not save your new OpenID Client.' });
  });
}

function test() {

  var parameters = parametersForUrl.value;
  parameters.state = Buffer.from(JSON.stringify(parameters.value)).toString("base64");;
  parameters.nonce = '12345';

  document.location = provider.value.authorization_endpoint + '?' + queryString.stringify(parametersForUrl.value);

}

</script>


<style lang="scss" scoped>
body .btn,
body .btn:hover {
  color: white;
}
</style>
