
<template>
  <div class="container-fluid">
    <h4 class="c-grey-900 mt-1 mb-3">Registration</h4>

    <div class="row" v-if="loaded">
      <div class="col-md-12">
        <div class="bgc-white bd bdrs-3 p-3 mt-2">
          <form
            class="needs-validation"
            novalidate
            :class="{'was-validated': wasValidated}"
            v-on:submit.prevent="onSubmit"
          >
            <p>User self-registrations happens via SCIM using the special Me-endpoint. The registration module can be part of the authentication chain.</p>

            <FormGroup
              horizontal
              :label-cols="3"
              breakpoint="md"
              description="Enable this options if users should have the possibility to self-register."
              label="Allow self-registration"
              id="registration_allowed"
            >
              <FormCheckbox
                id="registration_allowed"
                v-model="registration.allow"
                :value="true"
              >{{ registration.allow ? 'Enabled' : 'Disabled' }}</FormCheckbox>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              breakpoint="md"
              id="registration.allow_active"
              description="In most cases, you want to create users inactive."
              label="Register users active"
            >
              <FormCheckbox
                id="registration.allow_active"
                v-model="registration.allow_active"
                :value="true"
                :unchecked-value="false"
              >{{ registration.allow_active ? 'Enabled' : 'Disabled' }}</FormCheckbox>
            </FormGroup>

            <FormGroup
              horizontal
              :label-cols="3"
              breakpoint="md"
              description="Automatically activate users when this authentication level is reached."
              label="Activate users for this level"
            >
              <FormSelect
                id="registration.level_active"
                v-model="registration.level_active"
                :options="levels"
                text-field="level"
                value-field="id"
                class="mb-3"
              />
            </FormGroup>

            <div class="form-row">
              <label for="levels" class="col-md-3 col-form-label">Attributes for create</label>
              <div class="col-md-9">
                <multiselect
                  id="attributes_create"
                  v-model="registration.attributes_create"
                  :options="scimAttributes"
                  :searchable="true"
                  :close-on-select="true"
                  :show-labels="false"
                  :multiple="true"
                  placeholder="Pick a value"
                ></multiselect>

                <small
                  id="attributes_update.help"
                  class="form-text text-muted"
                >The list of attributes allowed for SCIM create requests on the /Me endpoint. The same list is used to present the registration form.</small>
              </div>
            </div>

            <div class="form-row mt-3">
              <label for="levels" class="col-md-3 col-form-label">Attributes after active login</label>
              <div class="col-md-9">
                <multiselect
                  id="attributes_update"
                  aria-describedby="attributes_update.help"
                  v-model="registration.attributes_update"
                  :options="scimAttributes"
                  :searchable="true"
                  :close-on-select="true"
                  :show-labels="false"
                  :multiple="true"
                  placeholder="Pick a value"
                ></multiselect>

                <small
                  id="attributes_update.help"
                  class="form-text text-muted"
                >You might want to prevent users from changing their username. Or you might want to allow managing extra attributes.</small>
              </div>
            </div>

            <div class="form-row mt-3">
              <label for="levels" class="col-md-3 col-form-label"></label>
              <div class="col-md-9">
                <button type="submit" class="btn btn-primary">Save Changes</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, getCurrentInstance } from "vue";
import { maxios } from "@/admin/helpers.js";
import { notify } from "../../helpers";

const vue = getCurrentInstance();
const loaded = ref(false);
const wasValidated = ref(false);
const levels = ref([]);
const errors = ref(null);
const registration = ref({
  allow: null
});
const scimAttributes = ref([
  "userName",
  "name",
  "displayName",
  "nickName",
  "profileUrl",
  "userType",
  "preferredLanguage",
  "locale",
  "timezone",
  "active",
  "password",
  "emails",
  "phoneNumbers",
  "ims",
  "photos",
  "addresses",
  "links",
  "otpSecret"
]);

onMounted(() => {
  maxios.get("api/settings/bulk?namespace=registration").then(
    response => {
      registration.value = response.data;
      loaded.value = true;
    },
    response => {
      notify({
        text: "We could not load the registration settings.",
        type: "error"
      });
    }
  );

  maxios.get('authchain/v2/manage/authlevels').then(
    response => {
      levels.value = response.data;
    },
    response => {
      notify({
        text: "We could not load the authentication levels.",
        type: "error"
      });
    }
  );
});

function onSubmit(event) {
  if (event.target.checkValidity()) {
    maxios
      .put("api/settings/bulk?namespace=registration", registration.value)
      .then(
        response => {
          notify({
            text: "We have succesfully saved your new registration settings."
          });
          errors.value = null;
        },
        response => {
          errors.value = response.data.errors;
          wasValidated.value = true;

          notify({
            text: "We could not save this.",
            type: "error"
          });
        }
      );
  } else {
    wasValidated.value = true;
    notify({
      text: "We could not save this.",
      type: "error"
    });
  }
}

</script>
