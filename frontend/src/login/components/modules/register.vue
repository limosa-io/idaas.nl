<template>
  <div class="register-container">
    <a
      v-if="!props.lonely"
      class="nav-link text-center"
      href="#"
      @click.prevent="goToRegister()"
      active-class="active"
    >
      {{ $t("login.registerLink") }}
    </a>

    <div v-else>
      <form
        class=""
        v-on:submit.prevent="onSubmit"
        v-if="user['urn:ietf:params:scim:schemas:core:2.0:User']"
      >
        <h1>{{ $t("login.registerTitle") }}</h1>
        <p>{{ $t("login.registerDescription") }}</p>

          <component
          v-for="f in fields"
            :key="f"
            :is="modules[f]"
            :customerstyle="customerstyle"
            :user="user"
            v-on:input="user = $event.target.value"
            :errors="errors"
          ></component>

        <p>{{ $t("login.registerBeforeApproval") }}</p>

        <div
          class="form-check form-group mb-3"
          v-if="module.config && module.config.approval"
        >
          <input
            class="form-check-input"
            type="checkbox"
            v-model="termsApproved"
            value=""
            id="defaultCheck1"
          />
          <label class="form-check-label" for="defaultCheck1">
            <!-- <p>{{ $t('login.registerApproveOne') }}</p> -->

            <i18n-t
              v-if="
                module.config.terms_of_service != null &&
                module.config.privacy_policy == null
              "
              keypath="login.registerApproveOne"
              tag="p"
            >
              <a :href="module.config.terms_of_service" target="_blank">{{
                $t("login.termsOfService")
              }}</a>
            </i18n-t>

            <i18n-t
              v-else-if="module.config.terms_of_service"
              keypath="login.registerApproveTwo"
              tag="p"
            >
              <a :href="module.config.terms_of_service" target="_blank">{{
                $t("login.termsOfService")
              }}</a>
              <a :href="module.config.privacy_policy" target="_blank">{{
                $t("login.privacyPolicy")
              }}</a>
            </i18n-t>
          </label>
        </div>

        <p>{{ $t("login.registerAfterApproval") }}</p>

        <button
          class="btn btn-primary btn-lg btn-block mt-3"
          type="submit"
          :class="{ 'btn-loading': isLoading }"
        >
          <span>{{ $t("login.registerButton") }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<style lang="scss" scoped>
body
  .login-box
  .login-container
  .large-form-items
  .register-container
  div.form-group
  label {
  display: block;
}
</style>

<script setup>
import { onMounted, watch, defineProps, ref } from "vue";

import userName from "../register/userName.vue";
import emails from "../register/emails.vue";
import password from "../register/password.vue";
import givenName from "../register/givenName.vue";
import familyName from "../register/familyName.vue";
import preferredLanguage from "../register/preferredLanguage.vue";
import { baseProps, activate, request, overview } from "./composable";
import { useStateStore } from "../store";
import axios from "axios";

const state = useStateStore();

const modules = {
  'userName' : userName,
  'emails' : emails,
  'password' : password,
  'givenName' : givenName,
  'familyName' : familyName,
  'preferredLanguage' : preferredLanguage,
};

const props = defineProps(baseProps);

const errors = ref({});
const fields = ref([]);
const isLoading = ref(false);
const termsApproved = ref(false);
const url = ref(null);
const user = ref({
  schemas: ["urn:ietf:params:scim:schemas:core:2.0:User"],
  "urn:ietf:params:scim:schemas:core:2.0:User": {},
});

watch(()=>props.lonely, (val) => {
  if (val) {
    goToRegisterNow();
  }
});

function goToRegister() {
  goToRegisterNow();
}


function goToRegisterNow() {
  //router.push({ name: 'login.register', params: {hash: route.params.hash, module: this.module.id } });

  activate(props.module);

  request(props.module, props.authRequest, {
    init: true,
  }).then((response) => {
    fields.value = response.data.fields;

    for (var f of fields.value) {
      if (f == "userName") {
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].userName =
          null;
      }

      if (f == "emails") {
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].emails = [
          {
            value: null,
          },
        ];
      }

      if (f == "password") {
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].password =
          null;
      }

      if (f == "givenName" || f == "familyName") {
        user.value["urn:ietf:params:scim:schemas:core:2.0:User"].name = {
          givenName: null,
          familyName: null,
        };
      }
    }

    url.value = response.data.url;
  });
}

function onSubmit() {
  if (props.module.config.approval && !termsApproved.value) {
    state.error("Please agree with the terms and conditions before continuing.");

    return false;
  }

  if (isLoading.value) {
    return false;
  }

  isLoading.value = true;

  axios.post(url.value, user.value).then(
    (response) => {
      request(props.module, props.authRequest, {
        "proof-of-creation": response.headers.get("x-scim-proof-of-creation"),
      }).then((r) => {
        isLoading.value = false;
        overview();
      });
    },
    (response) => {
      console.log(response);
      isLoading.value = false;
      errors.value = response.body.errors;
    }
  );
}

onMounted(() => {
  if (props.lonely) {
    goToRegisterNow();
  }
});
</script>
