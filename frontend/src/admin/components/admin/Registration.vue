
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

            <b-form-group
              horizontal
              :label-cols="3"
              breakpoint="md"
              description="Enable this options if users should have the possibility to self-register."
              label="Allow self-registration"
            >
              <b-form-checkbox
                id="registration_allowed"
                v-model="registration.allow"
                :value="true"
                :unchecked-value="false"
              >{{ registration.allow ? 'Enabled' : 'Disabled' }}</b-form-checkbox>
            </b-form-group>

            <b-form-group
              horizontal
              :label-cols="3"
              breakpoint="md"
              description="In most cases, you want to create users inactive."
              label="Register users active"
            >
              <b-form-checkbox
                id="registration.allow_active"
                v-model="registration.allow_active"
                :value="true"
                :unchecked-value="false"
              >{{ registration.allow_active ? 'Enabled' : 'Disabled' }}</b-form-checkbox>
            </b-form-group>

            <b-form-group
              horizontal
              :label-cols="3"
              breakpoint="md"
              description="Automatically activate users when this authentication level is reached."
              label="Activate users for this level"
            >
              <b-form-select
                id="registration.level_active"
                v-model="registration.level_active"
                :options="levels"
                text-field="level"
                value-field="id"
                class="mb-3"
              />
            </b-form-group>

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

            <!--
          <div class="form-row mt-3">
            <label for="levels" class="col-md-3 col-form-label">Advanced!</label>
            <div class="col-md-9">

              <codemirror id="codemirror" v-model="registration.script" :options="cmOptions"></codemirror>

            </div>
          </div>
            -->

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


<script>
export default {
  components: {
    codemirror: resolve =>
      import(
        /* webpackChunkName: "vue-codemirror" */ "../lib/codemirror.js"
      ).then(m => {
        resolve(m.default.codemirror);
      })
  },

  mounted() {
    this.$http
      .get(this.$murl("api/settings?namespace=registration"))
      .then(response => {
        this.registration = Object.assign(
          {
            allow: null,
            allow_active: null,
            level_active: null,
            attributes_create: [],
            attributes_update: []
          },
          response.data
        );
        this.wasValidated = false;
        this.loaded = true;
      });

    this.$http.get(this.$murl("authchain/v2/manage/authlevels")).then(
      response => {
        this.levels = response.data;
      },
      response => {
        // error callback
      }
    );
  },

  data() {
    return {
      loaded: false,

      cmOptions: {
        // codemirror options
        tabSize: 4,
        mode: "text/html",
        theme: "lucario",
        lineNumbers: true,
        line: true
        // more codemirror options,
      },

      errors: {},

      levels: [],

      wasValidated: false,

      scimAttributes: [
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
      ],

      registration: {
        // selfRegistrationEnabled: true

        allow: true
      }
    };
  },

  methods: {
    onSubmit(event) {
      if (event.target.checkValidity()) {
        this.$http
          .put(
            this.$murl("api/settings/bulk?namespace=registration"),
            this.registration
          )
          .then(
            response => {
              this.$noty({
                text:
                  "We have succesfully saved your new OpenID Client settings."
              });
              this.errors = null;
              // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});
            },
            response => {
              this.errors = response.data.errors;
              this.wasValidated = true;

              this.$noty({
                text: "We could not save this.",
                type: "error"
              });
            }
          );
      } else {
        this.wasValidated = true;
        this.$noty({
          text: "We could not save this.",
          type: "error"
        });
      }
    }
  }
};
</script>
