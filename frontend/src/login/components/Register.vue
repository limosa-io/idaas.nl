<template>
  <div class="container">
    <div
      class="row mt-sm-3 mt-md-5 justify-content-md-center"
      v-if="fields && fields.length > 0"
    >
      <div class="col-md-3 p-3 rounded-left" style="background-color: #eeeeee">
        <h1 class="mt-3">Create your account</h1>
        <p style="font-size: 1.5em">
          Access to trials, demos, starter kits, services, and APIs.
        </p>
      </div>

      <div class="col-md-3 p-3 rounded-right" style="background-color: white">
        <h2 class="mt-3">Sign up for an account</h2>

        <p v-if="props.authRequest.next && props.authRequest.next.length > 1">
          Already have an account?
          <a
            href="#"
            @click="
              router.go(-1);
              $event.preventDefault();
            "
            >Sign in</a
          >
        </p>

        <form
          class=""
          v-on:submit.prevent="onSubmit"
          v-if="user['urn:ietf:params:scim:schemas:core:2.0:User']"
        >
          <template v-for="f in fields">
            <component
              :key="f"
              v-if="$options.components[f]"
              :customerstyle="customerstyle"
              :is="modules[f]"
              :user="user"
              v-on:input="user = $event.target.value"
              :errors="errors"
            ></component>
          </template>

          <p>
            We may use my contact data to keep me informed of products, services
            and offerings:
          </p>

          <div class="form-check form-group mb-3">
            <input
              class="form-check-input"
              type="checkbox"
              value=""
              id="defaultCheck1"
            />
            <label class="form-check-label" for="defaultCheck1">
              Default checkbox
            </label>
          </div>

          <p>
            More information on our processing can be found in our Privacy
            Policy. By submitting this form, I acknowledge that I have read and
            understand the Privacy Statement.
          </p>
          <p>
            I accept the product Terms of Service of this registration form.
          </p>

          <button class="btn btn-primary btn-lg mt-3" type="submit">
            <span>Continue</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, getCurrentInstance, defineProps } from "vue";
import {baseProps, request} from "./modules/composable";
import axios from "axios";

import userName from "./register/userName.vue";
import emails from "./register/emails.vue";
import password from "./register/password.vue";
import givenName from "./register/givenName.vue";
import familyName from "./register/familyName.vue";
import preferredLanguage from "./register/preferredLanguage.vue";
import { useRouter } from "vue-router4";

const router = useRouter()
const vue = getCurrentInstance();
const props = defineProps(baseProps);

const modules = {
  "userName": userName,
  "emails": emails,
  "password": password,
  "givenName": givenName,
  "familyName": familyName,
  "preferredLanguage": preferredLanguage,
};

const errors = ref({});
const fields = ref([]);
const url = ref(null);
const user = ref({
  schemas: ["urn:ietf:params:scim:schemas:core:2.0:User"],
  "urn:ietf:params:scim:schemas:core:2.0:User": {
    password: null,

    emails: [
      {
        value: null,
      },
    ],

    name: {
      givenName: null,
      familyName: null,
    },
  },
});

onMounted(() => {
  request({
    init: true,
  }).then((response) => {
    fields.value = response.data.fields;
    url.value = response.data.url;
  });
});

function onSubmit() {
  axios.post(this.url, this.user).then(
    (response) => {
      request({
        userId: response.data.id,
      });

      useRouter().go(-1);
    },
    (e) => {
      errors.value = e.response.data.errors || {};
    }
  );
}
</script>

