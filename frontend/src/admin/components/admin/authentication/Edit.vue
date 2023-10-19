
<template>
  <MainTemplate :title="`Edit ${module.name}`" v-if="module">

    <template v-slot:header>
      <div v-if="userinfo != null && userinfo.acr.includes(module.id)" class="alert alert-danger" role="alert">You have
        used this authentication module to log in for this session. Ensure you know what you are doing when editing this
        module. You risk locking yourself out.</div>
    </template>

    <template v-slot:body>

      <div class="mt-2 mb-3 alert alert-warning d-flex justify-content-between align-items-center" v-if="alert"
        role="alert">
        {{ alert.text }}
        <button v-if="alert.link" @click="router.push(alert.link)" type="button"
          class="btn btn-xs btn-dark align-right">Configure</button>
      </div>

      <form class="needs-validation" novalidate :class="{ 'was-validated': wasValidated }" v-on:submit="onSubmit">

        <FormGroup horizontal :label-cols="3" description="Type of the module." label="Module type"
          label-for="module.type">
          <FormInput readonly id="module.type" v-model="module.type"></FormInput>
        </FormGroup>

        <FormGroup horizontal :label-cols="3" description="Name of the module." label="Module name"
          label-for="module.name">
          <FormInput id="module.name" v-model="module.name"></FormInput>
        </FormGroup>

        <FormGroup id="test" description="Whether to remember the module result." label="Remember"
          label-for="module.remember">
          <FormSelect id="module.remember" v-model="module.remember" :options="rememberOptions"></FormSelect>
        </FormGroup>

        <FormGroup horizontal :label-cols="3" description="Remember time in seconds." label="Remember time"
          label-for="module.remember_lifetime">
          <FormInput id="module.remember_lifetime" v-model="module.remember_lifetime"></FormInput>
        </FormGroup>

        <div class="form-row mb-3" v-if="!module.system">
          <label for="levels" class="col-sm-3 col-form-label">Authentication Levels</label>

          <div class="col-sm-9 ml-0">

            <multiselect :customLabel="(option, label) => label ? option.level : option.id" class="pl-0 ml-0" id="levels"
              v-model="module.levels" :options="options" label="level" track-by="id" :searchable="false"
              :close-on-select="true" :show-labels="false" :multiple="true" placeholder="Pick a value">

              <template slot="option" slot-scope="props">{{ props.option.level }}</template>
            </multiselect>

            <small id="parentHelp" class="form-text text-muted">The authentication levels provided by this module
              upon succesful completion.</small>

          </div>
        </div>

        <FormGroup v-if="!module.system" horizontal :label-cols="3" breakpoint="md"
          description="Sometimes disable is prefered over delete." label="Module state">

          <FormCheckbox id="enabled" v-model="module.enabled" :value="true" :unchecked-value="false">
            {{ module.enabled ? 'Enabled' : 'Disabled' }}
          </FormCheckbox>

        </FormGroup>


        <FormGroup horizontal :label-cols="3" breakpoint="md"
          description="You may choose to hide this module if not explicitly requests, e.g. by requesting one of the attached levels."
          label="Show only when needed">

          <FormCheckbox id="hide_if_not_requested" v-model="module.hide_if_not_requested" :value="true"
            :unchecked-value="false">
            {{ module.hide_if_not_requested ? 'Enabled' : 'Disabled' }}
          </FormCheckbox>

        </FormGroup>

<!--         
        <p>{{  module.type }}</p>
        <component @alert="alert = $event"
          v-if="module && module.type && Object.keys($options.components).indexOf(module.type) > -1" :module="module"
          :info="info" :wasValidated="wasValidated" :errors="errors" v-bind:is="modules[module.type]"></component>
        <div v-else>
        </div> -->


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

      <Danger v-if="!module.system && userinfo != null && !userinfo.acr.includes(module.id)"
        body="Clicking the button below will delete this module. This cannot be undone">
        <button type="button" class="btn btn-danger" @click="deleteModule(module)">Delete</button>
      </Danger>

    </template>

  </MainTemplate>
</template>


<script setup>

import { ref, defineProps, onMounted } from 'vue';
import { maxios, laxios } from "@/admin/helpers.js";
import password from './modules/Password.vue';
import facebook from './modules/Facebook.vue';
import linkedin from './modules/Linkedin.vue';
import fido from './modules/Fido.vue'
import passwordless from './modules/Passwordless.vue';
import otpMail from './modules/OtpMail.vue';
import activation from './modules/Activation.vue';
import passwordForgotten from './modules/PasswordForgotten.vue';
import openIDConnect from './modules/OpenIDConnect.vue';
import register from './modules/Register.vue';
import Danger from "@/admin/components/general/Danger.vue";
import { notify } from '../../../helpers';
import { useRoute, useRouter } from 'vue-router4';

const route = useRoute();
const router = useRouter();
const modules = {
  'password': password,
  'facebook': facebook,
  'linkedin': linkedin,
  'fido': fido,
  'passwordless': passwordless,
  'otp_mail': otpMail,
  'activation': activation,
  'passwordForgotten': passwordForgotten,
  'openIDConnect': openIDConnect,
  'register': register
};

const alert = ref(null);
const errors = ref({});
const rememberOptions = ref([{ value: null, text: 'never' }, { value: 'cookie', text: 'cookie' }, { value: 'session', text: 'session' }]);
const wasValidated = ref(false);
const loading = ref(false);
const type = ref(null);
const types = ref([]);
const module = ref(null);
const info = ref(null);
const showAdvanced = ref(false);
const levels = ref([]);
const options = ref([]);
const value = ref(null);
const userinfo = ref(null);

onMounted(() => {


  laxios.get('oauth/userinfo').then(response => {
    userinfo.value = response.data;
  }, response => {
    // FIXME: fix router
    router.replace({ name: 'error.default' });
  });

  maxios.get('authchain/v2/manage/modules/' + route.params.module_id).then(response => {

    module.value = response.data;
    
    module.value.config = module.value.config || {};
    module.value.public_config = module.value.public_config || {};

    //TODO: config must be object. fix serialization in php
    if (Array.isArray(module.value.config)) {
      module.value.config = {};
    }

    if (Array.isArray(module.value.public_config)) {
      module.value.public_config = {};
    }

    getInfo();

  }, response => {
    // error callback
  });

  maxios.get('authchain/v2/manage/authlevels').then(response => {

    options.value = response.data;

  }, response => {
    // error callback
  });

});

function deleteModule(module) {
  maxios.delete('authchain/v2/manage/modules/' + module.id).then(response => {

    notify({
      text: 'We have succesfully DELETED your module.'
    });

    router.go(-1);
  });
}

function getInfo() {
  maxios.get('authchain/v2/manage/modules/info/' + route.params.module_id).then(response => {

    info.value = response.data;
  });

}

function onSubmit(event) {

  maxios.put('authchain/v2/manage/modules/' + route.params.module_id,
    module.value
  ).then(response => {

    notify({
      text: 'We have succesfully saved your module.'
    });

  }, response => {
    errors.value = response.data.errors;
    wasValidated.value = true;

    notify({
      text: 'We could not save this.',
      type: 'error'
    });
  });

  event.preventDefault();

}

</script>
