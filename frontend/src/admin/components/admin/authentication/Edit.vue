
<template>
<Main :title="`Edit ${module.name}`" v-if="module">

  <template v-slot:header>
    <div v-if="userinfo != null && userinfo.acr.includes(module.id)" class="alert alert-danger" role="alert">You have used this authentication module to log in for this session. Ensure you know what you are doing when editing this module. You risk locking yourself out.</div>
  </template>

  <template v-slot:body>

        <div class="mt-2 mb-3 alert alert-warning d-flex justify-content-between align-items-center" v-if="alert" role="alert">
          {{ alert.text }}
          <button v-if="alert.link" @click="$router.push(alert.link)" type="button" class="btn btn-xs btn-dark align-right">Configure</button>
        </div>

        <form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">

          <b-form-group horizontal :label-cols="3" description="Type of the module." label="Module type" label-for="module.type">
            <b-form-input readonly id="module.type" v-model="module.type"></b-form-input>
          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="Name of the module." label="Module name" label-for="module.name">
            <b-form-input id="module.name" v-model="module.name"></b-form-input>
          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="Whether to remember the module result." label="Remember"
            label-for="module.remember">
            <b-form-select id="module.remember" v-model="module.remember" :options="rememberOptions" />
          </b-form-group>

          <b-form-group horizontal :label-cols="3" description="Remember time in seconds." label="Remember time"
            label-for="module.remember_lifetime">
            <b-form-input id="module.remember_lifetime" v-model="module.remember_lifetime"></b-form-input>
          </b-form-group>

          <div class="form-row mb-3" v-if="!module.system">
            <label for="levels" class="col-sm-3 col-form-label">Authentication Levels</label>

            <div class="col-sm-9 ml-0">

              <multiselect :customLabel="(option,label) => label?option.level:option.id" class="pl-0 ml-0" id="levels"
                v-model="module.levels" :options="options" label="level" track-by="id" :searchable="false"
                :close-on-select="true" :show-labels="false" :multiple="true" placeholder="Pick a value">

                <template slot="option" slot-scope="props">{{ props.option.level }}</template>
              </multiselect>

              <small id="parentHelp" class="form-text text-muted">The authentication levels provided by this module
                upon succesful completion.</small>

            </div>
          </div>

          <b-form-group v-if="!module.system" horizontal :label-cols="3" breakpoint="md" description="Sometimes disable is prefered over delete."
            label="Module state">

            <b-form-checkbox id="enabled" v-model="module.enabled" :value="true" :unchecked-value="false">
              {{ module.enabled ? 'Enabled' : 'Disabled' }}
            </b-form-checkbox>

          </b-form-group>


          <b-form-group horizontal :label-cols="3" breakpoint="md" description="You may choose to hide this module if not explicitly requests, e.g. by requesting one of the attached levels."
            label="Show only when needed">

            <b-form-checkbox id="hide_if_not_requested" v-model="module.hide_if_not_requested" :value="true" :unchecked-value="false">
              {{ module.hide_if_not_requested ? 'Enabled' : 'Disabled' }}
            </b-form-checkbox>

          </b-form-group>

          <component @alert="alert = $event" v-if="module && module.type && Object.keys($options.components).indexOf(module.type) > -1" :module="module"
            :info="info" :wasValidated="wasValidated" :errors="errors" v-bind:is="module.type"></component>
          <div v-else>
            <!-- Now special module settings -->
          </div>


          <div class="form-row">
            <div class="col-md-3">

            </div>
            <div class="col">
              <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </div>

          </div>

        </form>

  </template>

  <template v-slot:footer>
  <div class="card border-danger mb-3 mt-3" v-if="!module.system && userinfo != null && !userinfo.acr.includes(module.id)">
    <div class="card-header">Danger Zone</div>
    <div class="card-body text-danger">
      <p class="card-text">Clicking the button below will delete this module. This cannot be undone.</p>
      <button type="button" class="btn btn-danger" @click="deleteModule(module)">Delete</button>
    </div>
  </div>
</template>

</Main>
  
</template>


<script>

import password from './modules/Password.vue';
import facebook from './modules/Facebook.vue';
import linkedin from './modules/Linkedin.vue';
import passwordless from './modules/Passwordless.vue';
import otpMail from './modules/OtpMail.vue';
import activation from './modules/Activation.vue';
import passwordForgotten from './modules/PasswordForgotten.vue';
import openIDConnect from './modules/OpenIDConnect.vue';
import register from './modules/Register.vue';
// import fido from './modules/Fido.vue';

export default {

  data() {
    return {

      //alert message from childs
      alert: null,

      errors: {},

      rememberOptions: [ {value:null, text: 'never'},{value:'cookie', text: 'cookie'}, {value:'session', text: 'session'}],

      wasValidated: false,
      loading: false,

      type: null,
      types: [],

      module: null,

      info: null,

      showAdvanced: false,

      levels: [],

      options: [],

      value: null,

      userinfo: null

    }
  },

  mounted() {

    this.$http.get(this.$oidcUrl('oauth/userinfo')).then(response => {
      this.userinfo = response.data;
    }, response => {
      this.$router.replace({name: 'error.default'});
    });

    this.$http.get(this.$murl('authchain/v2/manage/modules/' + this.$route.params.module_id)).then(response => {

      this.module = response.data;

      console.log(JSON.stringify(this.module));

      this.module.config = this.module.config || {};
      this.module.public_config = this.module.public_config || {};

      //TODO: config must be object. fix serialization in php
      if(Array.isArray(this.module.config)){
        this.module.config = {};
      }

      if(Array.isArray(this.module.public_config)){
        this.module.public_config = {};
      }

      this.getInfo();

    }, response => {
      // error callback
    });

    this.$http.get(this.$murl('authchain/v2/manage/authlevels')).then(response => {

      this.options = response.data;

    }, response => {
      // error callback
    });

  },

  methods: {

    deleteModule(module) {
      this.$http.delete(this.$murl('authchain/v2/manage/modules/' + module.id)).then(response => {

        this.$noty({
          text: 'We have succesfully DELETED your module.'
        });

        this.$router.go(-1);
      });
    },

    getInfo() {
      this.$http.get(this.$murl('authchain/v2/manage/modules/info/' + this.$route.params.module_id)).then(response => {

        this.info = response.data;
      });

    },

    onSubmit(event) {

      this.$http.put(this.$murl('authchain/v2/manage/modules/' + this.$route.params.module_id),
        this.module
      ).then(response => {

        this.$noty({
          text: 'We have succesfully saved your module.'
        });
        // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});

      }, response => {
        this.errors = response.data.errors;
        this.wasValidated = true;

        this.$noty({
          text: 'We could not save this.',
          type: 'error'
        });
      });

      event.preventDefault();

    }
  },

  components: {
    password,
    facebook,
    linkedin,
    passwordless,
    passwordForgotten,
    activation,
    openIDConnect,
    register,
    otpMail,
    // fido
  }

}
</script>
