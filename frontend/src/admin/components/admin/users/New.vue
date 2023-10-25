
<template>
  <MainTemplate title="New User">
    <template v-slot:body v-if="user">
      <h4 class="c-grey-900 mt-2">Provide some information</h4>

      <p>You must provide a username or e-mail address, or both.</p>

      <form
        v-if="user"
        class="needs-validation"
        novalidate
        :class="{ 'was-validated': wasValidated }"
        v-on:submit="onSubmit"
      >
        <div
          class="form-row form-group"
          v-for="(email, index) in user[
            'urn:ietf:params:scim:schemas:core:2.0:User'
          ].emails"
          :key="index"
        >
          <div class="col-md-3">
            <label for="email">E-mail</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:User:emails.' +
                    index +
                    '.value'
                ]
                  ? true
                  : false,
              }"
              v-model="email.value"
              type="text"
              class="form-control"
              id="email"
              placeholder=""
              aria-describedby="emailHelp"
            />

            <small id="emailHelp" class="form-text text-muted"
              >The e-mailaddress must be unique.</small
            >

            <div
              v-if="
                errors[
                  'urn:ietf:params:scim:schemas:core:2.0:User:emails.' +
                    index +
                    '.value'
                ]
              "
              class="invalid-feedback"
            >
              This must be a valid mail address and unique.
            </div>
          </div>
        </div>

        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="username">User name</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:User:userName'
                ]
                  ? true
                  : false,
              }"
              v-model="
                user['urn:ietf:params:scim:schemas:core:2.0:User'].userName
              "
              type="text"
              class="form-control"
              id="username"
              placeholder=""
            />

            <small id="usernameHelp" class="form-text text-muted"
              >Optional username.</small
            >

            <div
              v-if="
                errors['urn:ietf:params:scim:schemas:core:2.0:User:userName']
              "
              class="invalid-feedback"
            >
              This field must be minimal 3 characters long and unique.
            </div>
          </div>
        </div>

        <p>Optionally, provide a password.</p>

        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="password">Password</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid':
                  errors['urn:ietf:params:scim:schemas:core:2.0:User:password'],
              }"
              v-model="
                user['urn:ietf:params:scim:schemas:core:2.0:User'].password
              "
              type="password"
              class="form-control"
              aria-describedby="passwordHelp"
              id="password"
              placeholder=""
            />

            <small id="passwordHelp" class="form-text text-muted"
              >Choose a secure password.</small
            >

            <div v-if="!errors.type" class="invalid-feedback">
              This is a required field.
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3" :disabled="loading">
          Add User
        </button>
      </form>
    </template>
  </MainTemplate>
</template>


<script setup>
import { ref } from "vue";
import {maxios, notify} from '@/admin/helpers.js';
import { useRouter } from "vue-router4";

const router = useRouter();
const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const user = ref({
  schemas: ["urn:ietf:params:scim:schemas:core:2.0:User"],
  "urn:ietf:params:scim:schemas:core:2.0:User": {
    userName: null,
    password: null,
    active: false,
    emails: [{ value: null }],
  },
});

function onSubmit(event) {
  if (event.target.checkValidity()) {
    loading.value = true;

    maxios
      .post("api/scim/v2/Users", JSON.stringify(user.value), {
        headers: { "content-type": "application/scim+json" },
      })
      .then(
        (response) => {
          notify({ text: "We have succesfully created a new user." });
          router.replace({
            name: "users.edit",
            params: { user_id: response.data.id },
          });
        },
        (e) => {
          notify({ text: "There were some problems." });

          errors.value = e.response.data.errors;
        }
      )
      .finally(() => {
        loading.value = false;
      });
  }

  wasValidated.value = true;

  event.preventDefault();
}

</script>