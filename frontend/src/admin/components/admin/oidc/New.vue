
<template>
  <MainTemplate title="New client">

    <template v-slot:body>
      <form
        class="needs-validation"
        novalidate
        :class="{'was-validated': wasValidated}"
        v-on:submit="onSubmit"
      >
        <div class="form-group">
          <label for="formGroupExampleInput">Client Name</label>
          <input
            :class="{'is-invalid': errors.client_name}"
            v-model="clientName"
            required
            aria-describedby="clientNameHelpBlock"
            type="text"
            class="form-control"
            id="formGroupExampleInput"
            placeholder
          />

          <div
            v-for="(e, index) in errors.client_name"
            :key="index"
            class="invalid-feedback"
          >{{ e }}</div>

          <div v-if="!errors.client_name" class="invalid-feedback">This is a required field.</div>

        </div>

        <FormGroup label="What type of client would you like to add?">
          <FormRadioGroup v-model="clientType" name="radioSubComponent">
            <FormRadioButton value="web">Web Client</FormRadioButton>
            <FormRadioButton value="native">Native Client (such as a mobile web application)</FormRadioButton>
          </FormRadioGroup>
        </FormGroup>

        <div
          v-for="(e, index) in errors.application_type"
          :key="index"
          class="invalid-feedback"
        >{{ e }}</div>

        <button type="submit" class="btn btn-primary mt-1" :disabled="loading">Add Application</button>
      </form>
    </template>
  </MainTemplate>
</template>


<script setup>
import {ref, onMounted} from "vue";
import {laxios} from '@/admin/helpers.js'
import { notify } from "../../../helpers";
import { useRouter } from "vue-router4";

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const clientName = ref(null);
const clientType = ref("web");
const router = useRouter();

function onSubmit(event) {
  if (event.target.checkValidity()) {
    loading.value = true;
    laxios
      .post("oauth/connect/register", {
        client_name: clientName.value,
        application_type: clientType.value
      })
      .then(
        response => {
          notify({
            text: "We have succesfully saved your new OpenID Client."
          });
          router.replace({
            name: "oidc.client.edit",
            params: { client_id: response.data.client_id }
          });
        },
        e => {
          errors.value = e.response.data.errors;
          wasValidated.value = true;
        }
      ).finally(() => {
        loading.value = false;
      });
  } else {
    wasValidated.value = true;
  }

  event.preventDefault();
}

</script>
