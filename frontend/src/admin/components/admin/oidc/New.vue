
<template>
  <div>
    <h2 class="c-grey-900 mt-1 mb-3">New client</h2>

    <div class="bgc-white bd bdrs-3 p-3 mt-2">
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

          <!--
    <p id="clientNameHelpBlock" class="form-text text-muted">
      The client name must be unique
    </p>
          -->
        </div>

        <b-form-group label="What type of client would you like to add?">
          <b-form-radio-group id="radios2" v-model="clientType" name="radioSubComponent">
            <b-form-radio value="web">Web Client</b-form-radio>
            <b-form-radio value="native">Native Client (such as a mobile web application)</b-form-radio>
          </b-form-radio-group>
        </b-form-group>

        <div
          v-for="(e, index) in errors.application_type"
          :key="index"
          class="invalid-feedback"
        >{{ e }}</div>

        <button type="submit" class="btn btn-primary mt-1" :disabled="loading">Add Application</button>
      </form>
    </div>
  </div>
</template>


<script>
export default {
  data() {
    return {
      errors: {},

      wasValidated: false,
      loading: false,

      clientName: null,
      clientType: "web"
    };
  },

  methods: {
    onSubmit(event) {
      if (event.target.checkValidity()) {
        this.$http
          .post(this.$oidcUrl("oauth/connect/register"), {
            client_name: this.clientName,
            application_type: this.clientType
          })
          .then(
            response => {
              this.$noty({
                text: "We have succesfully saved your new OpenID Client."
              });
              this.$router.replace({
                name: "oidc.client.edit",
                params: { client_id: response.data.client_id }
              });
            },
            response => {
              this.errors = response.data.errors;
              this.wasValidated = true;
            }
          );
      } else {
        this.wasValidated = true;
      }

      event.preventDefault();
    }
  }
};
</script>
