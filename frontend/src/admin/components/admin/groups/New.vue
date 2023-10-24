
<template>
  <MainTemplate title="New Group">
    <template v-slot:body>
      <h4 class="c-grey-900 mt-2">Provide the required details</h4>

      <form
        class="needs-validation"
        novalidate
        :class="{ 'was-validated': wasValidated }"
        v-on:submit="onSubmit"
      >
        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="name">Group Name</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:name'
                ]
                  ? true
                  : false,
              }"
              v-model="
                group['urn:ietf:params:scim:schemas:core:2.0:Group'].name
              "
              required
              type="text"
              class="form-control"
              id="name"
              placeholder=""
            />

            <div
              v-if="errors['urn:ietf:params:scim:schemas:core:2.0:Group:name']"
              class="invalid-feedback"
            >
              This is a required field and must be minimal 3 characters long.
            </div>
          </div>
        </div>

        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="displayName">Display Name</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:displayName'
                ]
                  ? true
                  : false,
              }"
              v-model="
                group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName
              "
              type="text"
              class="form-control"
              id="displayName"
              placeholder=""
            />

            <div
              v-if="
                errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:displayName'
                ]
              "
              class="invalid-feedback"
            >
              This is a required field and must be minimal 3 characters long.
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3" :disabled="loading">
          Add Group
        </button>
      </form>
    </template>
  </MainTemplate>
</template>


<script setup>

import { ref, onMounted, getCurrentInstance } from "vue";
import {maxios} from '@/admin/helpers.js'
import { notify } from "../../../helpers";
import { useRoute, useRouter } from "vue-router4";

const router = useRouter();

const vue = getCurrentInstance();

const errors = ref({});
const wasValidated = ref(false); 
const loading = ref(false);
const group = ref({
  schemas: ["urn:ietf:params:scim:schemas:core:2.0:Group"],
  "urn:ietf:params:scim:schemas:core:2.0:Group": {
    name: null,
    displayName: null,
  },
});

function onSubmit(event) {
  if (event.target.checkValidity()) {
    loading.value = true;

    maxios
      .post("api/scim/v2/Groups", JSON.stringify(group.value), {
        headers: { "content-type": "application/scim+json" },
      })
      .then(
        (response) => {
          notify({ text: "We have succesfully created a new group." });
          router.replace({
            name: "groups.edit",
            params: { group_id: response.data.id },
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