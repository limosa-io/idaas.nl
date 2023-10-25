<template>

<div class="d-flex justify-content-center">
  <div class="col-6">

    <h1>Test Result</h1>

    <div class="container">

      <div class="row">
        <div class="col-2">
          access_token
        </div>
        <div class="col-10">
          {{ access_token }}
        </div>

      </div>

      <div class="row">
        <div class="col-2">
          id_token
        </div>
        <div class="col-10">
          {{ id_token || 'no id_token received' }}
        </div>

      </div>

      <div class="row">
        <div class="col-2">
          userinfo
        </div>
        <div class="col-10">
          <pre><code>{{ userinfo }}</code></pre>
        </div>

      </div>
    </div>

  </div>
</div>
</template>

<style lang="scss" scoped>

.row{

  .col-2 {
    background-color: rgb(156, 220, 255);
    border-right-width: 1px;
    border-right-color: black;
    border-right-style: solid;
    padding-top: 1rem;
    padding-bottom: 1rem;
  }
  
}

.row:nth-child(odd){
  background-color: rgb(225, 225, 225);
}

.col-10 {
  word-wrap: break-word;
  padding-top: 1rem;
  padding-bottom: 1rem;
}


</style>


<script>

import queryString from 'query-string';
import { notify } from '../helpers';

const authorizationServer = window.location.protocol + "//" + window.location.host;

export default {

  data() {
    return {

      provider: null,

      userinfo: null,
      access_token: null,
      refresh_token: null,
      id_token: null

    };
  },

  watch: {
    provider: function () {
      this.process();
    }
  },

  methods: {

    fetchUserinfo(token){

     return new Promise( (resolve, reject) => {

       this.$http.get(this.provider.userinfo_endpoint, {
        public: true,
        headers: {
          'Authorization': 'Bearer ' + token
        }
      }).then(response => {

        resolve(response);

      }, response => {
        reject(response);
      });

     })  

    },

    introspect(token){

      //Not possible, needs authentication

    },

    processToken(parsed, state){
      
      this.access_token = parsed.access_token;

      this.fetchUserinfo(parsed.access_token).then( response => {
        this.userinfo = response.body;
      });

    },

    processCode(parsed, state){
      this.$http.post(this.provider.token_endpoint, {
          'code': parsed.code,
          'grant_type': 'authorization_code',
          'redirect_uri': state.redirect_uri,
          'client_id': state.client_id,
        }, {
          headers: {
            'Content-Type': 'application/json'
          }
        }).then(response => {
          
          this.id_token = response.body.id_token;
          this.refresh_token = response.body.refresh_token;

          this.processToken( {
            access_token: response.body.access_token
          }, state);

        }, response => {
          notify({
            text: 'We could not save this.',
            type: 'error'
          });
        });
    },

    process: function () {
      const parsedHash = queryString.parse(location.hash);
      const parsedSearch = queryString.parse(location.search);

      let parsed = parsedHash.state ? parsedHash : parsedSearch;

      let state = JSON.parse(Buffer.from('' + parsed.state, 'base64').toString('utf8'));

      
      if (state.response_type.match(/(^|\s)(code)(\s|$)/)) {
        
        this.processCode(parsed, state);

      }else if (state.response_type.match(/^|\token\s|$/)) {
        
        this.processToken(parsed, state);

      }

    }

  },

  mounted() {
    

    this.$http.get(this.$oidcUrl('.well-known/openid-configuration'), {
      public: true
    }).then(response => {

      this.provider = response.data;

    }, response => {
      // error callback
    });

  }

};
</script>