
<template>

<div>

  <div class="bgc-white bd bdrs-3 p-3 mt-2" v-if="clients">

    <h3 class="c-grey-900">Test OpenID Clients</h3>

    <p>Test your OpenID Connect clients</p>

    <div v-if="!hasTestClient" class="alert alert-warning" role="alert">
      It looks like you do not have a test client installed. <a href="#" @click.prevent="createTestClient">Create one.</a>
    </div>

    <div class="form-group row">
      <div class="col-md-3">
        Client
      </div>
      <div class="col">

        <div class="input-group mb-3">

          <b-form-select v-model="client_id_selected" value-field="client_id" text-field="client_name" :options="clients"
            class="form-control" />
          <div class="input-group-append">
            <a @click="$router.push({ name: 'oidc.client.edit', params: { client_id: requestParameters.client_id }});"
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

          <multiselect id="scopes" v-model="requestParameters.response_type" :options="clientSelected.response_types || []"
            :searchable="false" :close-on-select="true" :show-labels="true" :multiple="true" placeholder="Pick a value">
            <template slot="option" slot-scope="props">{{ props.option }}</template>
          </multiselect>

        </div>

      </div>

      <div class="form-group row">
        <div class="col-md-3">
          Redirect URI
        </div>
        <div class="col">
          <b-form-select v-model="requestParameters.redirect_uri" :options="clientSelected.redirect_uris || []" class="form-control" />
        </div>

      </div>

      <div class="form-group row">

        <label class="col-md-3" for="scopes">Scopes.</label>
        <div class="col">
          <multiselect id="scopes" v-model="requestParameters.scope" :options="provider.scopes_supported" :searchable="false"
            :close-on-select="true" :show-labels="true" :multiple="true" placeholder="Pick a value">
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


<script>

// TODO: query for clients registered.
// Check if 'test client' has been installed. Check by name?? Or by 'redirect_uri'? If not exists => suggest to create it.
// Show list of clients. like existing Test.vue component.
// When client is selected, show options  for selectint available scopes, response_types, redirect_uri-s etc.    

import queryString from 'query-string';
import sha256 from 'hash.js/lib/hash/sha/256';
// import crypto from 'crypto';

function webSafe64(base64) {
    return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,
  
      clients: null,

      clientSelected: null,

      provider: null,

      client_id_selected: null,

      hasTestClient: true,

      requestParameters: {
        client_id: '123'
      }

    }
  },

  computed: {
    // a computed getter
    // optionsClient: function () {
    //   return this.clients != null && this.clients.length > 0 ? this.clients.map( v => {  return {value: v, text: v.client_name }; } ) : [];
    // },

    optionsResponseType(){
      return [{ value: 'a', text: 'This is First option' },{ value: 'b', text: 'asdg' }];
    },

    testUrl(){

      var result = {};

      if(this.requestParameters)
      for (var key in this.requestParameters) {
        if( typeof this.requestParameters[key] === 'string' ){
          result[key] = this.requestParameters[key];
        }else{
          // Array.isArray(this.parameters[key])

          var temp = this.requestParameters[key];

          if(key == 'scope'){
            temp.sort( (a, b) => { return a == 'openid' ? -1 : 1 });
          }else if(key == 'response_type'){
            temp.sort( (a, b) => { 
              return a == 'code' ? -1 : (a == 'id_token' && b != 'code' ? -1 : 1) 
            });
          }

          result[key] = temp.join(' ');

        }
      }
      
      // see https://tools.ietf.org/html/rfc7636
      //TODO: generate random string
      var codeVerifier = 'abc'; //webSafe64(crypto.randomBytes(96).toString('base64'));      
      
      var codeChallenge = webSafe64 ( new Buffer(sha256().update(codeVerifier , 'ascii').digest('hex'), 'hex').toString('base64')  );

      result.code_challenge = codeChallenge;
      result.code_challenge_method = 'S256';
      result.nonce = '12345';

      result.state = Buffer.from(JSON.stringify(result)).toString("base64");;

      return this.provider.authorization_endpoint + '?' + queryString.stringify(result);


    }

  },

  watch: {


    client_id_selected: function(val){

      if(this.clientSelected == null || this.clientSelected.client_id != val){

        for(var client of this.clients){

            if(client.client_id == val){
              this.clientSelected = client;
              break;
            }
          }


      }

      this.$set(this.requestParameters, 'client_id', val);

    },

    requestParameters: { handler: function(val){
      
      localStorage.setItem('test.parameters',JSON.stringify(val));

    }, deep: true}
  },


  mounted(){

    this.loadClients();

      this.$http.get(this.$oidcUrl('.well-known/openid-configuration'), {
        public: true
      }).then(response => {
        
        this.provider = response.data;

      }, response => {
        // error callback
      });
        

  },

  methods: {

    loadClients(){
      
      return new Promise( (resolve, reject) => {
        this.$http.get(this.$oidcUrl('oauth/connect/register')).then(response => {
        this.clients =  Object.values(response.data);


        this.hasTestClient = this.clients.find(e => e.client_name == 'Test Client') !=  undefined;

        this.requestParameters = JSON.parse(localStorage.getItem('test.parameters')) || {};
        this.client_id_selected = this.requestParameters.client_id || null;

        resolve(this.clients);

      }, response => {
        // error callback
        reject(response);

      }
      
      );
      });
    },

    createTestClient(){

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


      this.$http.post(this.$oidcUrl('oauth/connect/register'),

      testClient
      ).then(response => {
        return this.loadClients();
      }).then(response => {
        this.$noty({text: 'We have succesfully saved your new OpenID Client.'});
      }).catch(response => {
        this.$noty({text: 'We could not save your new OpenID Client.'});
      });

    },

    test(){

      var parameters = this.parametersForUrl;
      parameters.state = Buffer.from(JSON.stringify(this.parameters)).toString("base64");;
      parameters.nonce = '12345';

      document.location = this.provider.authorization_endpoint + '?' + queryString.stringify(this.parametersForUrl);

    }
  }


  
}
</script>


<style lang="scss" scoped>

body .btn, body .btn:hover{
  color: white;
}

</style>
