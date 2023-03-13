
<template>
  <!--
TODO: Allow marking client as 'trusted' => should lead to auto consenting scopes
TODO: Allow marking scopes as 'required', client will fail if no consented
TODO: Consider adding list of allowed 'audiences'
  -->

  <Main v-if="client" :title="`Edit ${client.client_name}`">
    <template v-slot:header>
      <b-modal hide-footer size="lg" ref="pickerModal" id="pickerModal" title="Select an image">
        <Picker v-on:picked="picked" :picker="true" />
      </b-modal>

      <div
        v-if="client.client_id == currentClientId"
        class="alert alert-danger"
        role="alert"
      >The management APIs depend on this client for functioning. Only edit the properties if you know what you are doing.</div>
    </template>

    <template v-slot:body>
      <form
        :class="{'was-validated': wasValidated}"
        v-if="client"
        class="needs-validation"
        novalidate
        v-on:submit.prevent="onSubmit"
      >
        <b-form-group label="Application Type" horizontal :label-cols="3">
          <b-form-radio-group
            aria-describedby="application_type_help"
            id="radios2"
            v-model="client.application_type"
            name="radioSubComponent"
          >
            <b-form-radio value="web">Web Client</b-form-radio>
            <b-form-radio value="native">Native Client (such as a mobile web application)</b-form-radio>
          </b-form-radio-group>

          <small id="application_type_help" class="form-text text-muted">
            Web clients require
            <code class="highlighter-rouge">https</code> redirection urls. Native clients allow custom app schemas.
          </small>
        </b-form-group>

        <b-form-group label="Public" horizontal :label-cols="3">
          <b-form-radio-group
            aria-describedby="client_public_help"
            id="client_public"
            v-model="client.public"
            name="radios3Sub"
          >
            <b-form-radio value="public">Public</b-form-radio>
            <b-form-radio value="confidential">Confidential</b-form-radio>
          </b-form-radio-group>

          <small id="client_public_help" class="form-text text-muted">
            Confidential clients require a
            <code class="highlighter-rouge">client_secret</code> for exchanging an authorization code. Public clients do
            not.
          </small>
        </b-form-group>

        <div class="form-group form-row">
          <label class="col-3" for="client_id">Client Id</label>
          <div class="col">
            <input
              v-model="client.client_id"
              type="text"
              class="form-control"
              id="client_id"
              aria-describedby="client_idHelp"
              readonly
            />
            <small id="client_idHelp" class="form-text text-muted">Unique Client Identifier</small>
          </div>
        </div>

        <div class="form-group form-row">
          <label class="col-3" for="client_name">Client Name</label>

          <div class="col">
            <input
              required
              v-model="client.client_name"
              type="text"
              class="form-control"
              id="client_name"
              placeholder="The name of the client"
              aria-describedby="client_nameHelp"
            />
            <small
              id="client_nameHelp"
              class="form-text text-muted"
            >Name of the Client to be presented to the End-User.</small>
            <template v-if="errors && errors.client_name">
              <div v-for="e in errors.client_name" :key="e" class="invalid-feedback">{{ e }}</div>
            </template>

            <div v-if="!errors.client_name" class="invalid-feedback">This is a required field.</div>
          </div>
        </div>

        <transition name="fade">
          <div
            class="form-group form-row"
            v-if="client.public == 'confidential'"
            horizontal
            :label-cols="3"
          >
            <label for="client_secret" class="col-3">Client Secret</label>

            <div class="col">
              <div class="input-group mb-3">
                <input
                  v-if="passwordType == 'text'"
                  v-model="client.secret"
                  readonly
                  type="text"
                  tabindex="-1"
                  class="form-control mr-0"
                  id="client_secret"
                  aria-describedby="client_secretHelp"
                />
                <input
                  v-else
                  type="text"
                  readonly
                  tabindex="-1"
                  class="form-control mr-0"
                  id="client_secret_fake"
                  aria-describedby="client_secretHelp"
                  placeholder="••••••••••••••••••••••••"
                />

                <div class="input-group-append">
                  <button
                    tabindex="-1"
                    @click="togglePassword"
                    class="btn btn-outline-secondary"
                    type="button"
                  >
                    <i class="c-teal-500 ti-eye"></i>
                  </button>
                </div>
              </div>

              <small id="client_secretHelp" class="form-text text-muted">
                This value is used by Confidential Clients to
                authenticate to the Token Endpoint
              </small>
            </div>
          </div>
        </transition>

        <b-form-group
          horizontal
          :label-cols="3"
          breakpoint="md"
          description="Trusted clients do not need consent from the user"
          label="Trusted"
        >
          <b-form-checkbox
            id="trusted"
            v-model="client.trusted"
            :value="true"
            :unchecked-value="false"
          >{{ client.trusted ? 'Enabled' : 'Disabled' }}</b-form-checkbox>
        </b-form-group>

        <b-form-group label="PKCE supported" horizontal :label-cols="3">
          <b-form-radio-group
            aria-describedby="pkce_help"
            id="pkce"
            v-model="client.code_challenge_methods_supported"
            name="pkce"
          >
            <b-form-radio :value="null">No</b-form-radio>
            <b-form-radio :value="['S256','plain']">Yes</b-form-radio>
          </b-form-radio-group>

          <small id="pkce_help" class="form-text text-muted">
            For native clients - such as mobile applications - you do
            want to enable
            <code
              class="highlighter-rouge"
            >PKCE</code>.
          </small>
        </b-form-group>

        <div class="form-group form-row">
          <label class="col-3" for="redirect_uris">Redirect URIs</label>

          <div class="col">
            <multiselect
              class="form-control"
              :class="{'is-invalid': errors['redirect_uris.1']}"
              :customLabel="e => e"
              v-model="client.redirect_uris"
              tag-placeholder="Add"
              placeholder="add a url"
              :multiple="true"
              :taggable="true"
              @tag="(searchQuery, id) => { client.redirect_uris.push(searchQuery); if(!show_post_logout_redirect_uris){ client.post_logout_redirect_uris = client.redirect_uris; } }"
              :close-on-select="false"
              :options="[]"
            ></multiselect>

                      <small
            id="pkce_help"
            class="form-text text-muted"
          >List of allowed redirection uris. <template v-if="!show_post_logout_redirect_uris">By default, this list also determined the allowed post logout redirect uris. <a href="#" @click.prevent="show_post_logout_redirect_uris = true">Configure different logout redirect uris.</a></template></small>


            <template v-for="(value, key) in errors">
              <div v-if="key.match(/redirect/)" :key="key" class="invalid-feedback">{{ value[0] }}</div>
            </template>
          </div>
        </div>

        <div class="form-group form-row" v-show="show_post_logout_redirect_uris">
          <label class="col-3" for="post_logout_redirect_uris">Logout redirect URIs</label>

          <div class="col">
            <multiselect
              class="form-control"
              :class="{'is-invalid': errors['post_logout_redirect_uris.1']}"
              :customLabel="e => e"
              v-model="client.post_logout_redirect_uris"
              tag-placeholder="Add"
              placeholder="add a url"
              :multiple="true"
              :taggable="true"
              @tag="(searchQuery, id) => { client.post_logout_redirect_uris.push(searchQuery); }"
              :close-on-select="false"
              :options="[]"
            ></multiselect>

                                  <small
            id="pkce_help"
            class="form-text text-muted"
          >List of allowed post logout redirection uris. You may choose to <a href="#" v-show="show_post_logout_redirect_uris" @click.prevent="show_post_logout_redirect_uris = false; client.post_logout_redirect_uris = client.redirect_uris;">use the list of redirect uris.</a></small>

            <template v-for="(value, key) in errors">
              <div
                v-if="key.match(/post_logout_redirect_uris/)"
                :key="key"
                class="invalid-feedback"
              >{{ value[0] }}</div>
            </template>
          </div>
        </div>

        <b-form-group label="Grant Types" horizontal :label-cols="3">
          <b-form-checkbox-group
            id="checkboxes1"
            name="flavour1"
            v-model="client.grant_types"
            :options="[{text: 'Authorization Code',value:'authorization_code'},{text: 'Implicit',value:'implicit'},{text: 'Refresh Token',value:'refresh_token'},{text: 'Client Credentials',value:'client_credentials'}]"
          ></b-form-checkbox-group>

          <small
            id="pkce_help"
            class="form-text text-muted"
          >List of the OAuth 2.0 Grant Types that the Client is declaring that it will restrict itself to using.</small>
        </b-form-group>

        <b-form-group label="Response Types" horizontal :label-cols="3">
          <b-form-checkbox-group
            id="response_types"
            name="response_types"
            v-model="client.response_types"
            :options="['code','token','id_token']"
          ></b-form-checkbox-group>

          <small
            id="pkce_help"
            class="form-text text-muted"
          >List of the OAuth 2.0 response_type values that the Client is declaring that it will restrict itself to using itself to using.</small>
        </b-form-group>

        <div class="form-group form-row">
          <label class="col-3" for="logo_uri">Logo URI</label>

          <div class="col">

            <div class="input-group">
              <input
                type="url"
                placeholder="Logo URI"
                class="form-control"
                id="container.backgroundImage"
                v-model="client.logo_uri"
              />
              <div class="input-group-append">
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="showPicker(m => client.logo_uri=m.url)"
                >
                  <i class="ti-upload"></i>
                </button>
              </div>
            </div>

            <small id="pkce_help" class="form-text text-muted">URL that references a logo for the Client application. Visible on the login screen.</small>

          </div>
        </div>

        <div class="form-group form-row">
          <label class="col-3" for="client_uri">URL of the home page of the Client.</label>

          <div class="col">
            <input
              v-model="client.client_uri"
              type="url"
              class="form-control"
              id="client_uri"
              placeholder="Client URI"
            />
          </div>
        </div>

        <!--
        <div class="form-group form-row">
          <label class="col-3" for="policy_uri">
            URL that the Relying Party Client provides to the End-User to read about
            the how the profile data will be used.
          </label>

          <div class="col">
            <input
              v-model="client.policy_uri"
              type="url"
              class="form-control"
              id="policy_uri"
              placeholder="Policy URI"
            />
          </div>
        </div>
        -->

        <!--
        <div class="form-group form-row">
          <label class="col-3" for="tos_uri">
            URL that the Relying Party Client provides to the End-User to read about the
            Relying Party's terms of service.
          </label>

          <div class="col">
            <input
              v-model="client.tos_uri"
              type="url"
              class="form-control"
              id="tos_uri"
              placeholder="Terms of service URI"
            />

            <div v-if="!errors.tos_uri" class="invalid-feedback">This must be a valid url.</div>
          </div>
        </div>
        -->

        <!--
        <div class="form-group form-row">
          <label class="col-3" for="initiate_login_uri">
            URI using the https scheme that a third party can use to initiate
            a login by the RP.
          </label>
          <div class="col">
            <input
              v-model="client.initiate_login_uri"
              type="url"
              class="form-control"
              id="initiate_login_uri"
              placeholder="Initiate login"
            />
          </div>
        </div>
        -->

        <h6 class="c-grey-900">Authentication Settings</h6>

        <div class="form-group form-row">
          <label for="default_prompt" class="col-3">Default prompt settings.</label>

          <div class="col">
            <div class="input-group">
              <select
                :class="{'is-invalid': errors.default_prompt}"
                v-model="client.default_prompt"
                class="form-control"
                id="default_prompt"
              >
                <option></option>
                <option>login</option>
                <option>none</option>
              </select>
              <div class="input-group-append">
                <b-form-select
                  v-model="client.default_prompt_allow_override"
                  :options="[ {value: true, text:'Allow Override'}, {value: false, text:'Do NOT Allow Override'} ]"
                  class="custom-select"
                />
              </div>
              <template v-if="errors.default_prompt">
                <div
                  v-for="(e,index) in errors.default_prompt"
                  class="invalid-feedback"
                  :key="index"
                >{{ e }}</div>
              </template>
            </div>
          </div>
        </div>

        <div class="form-group form-row">
          <label for="default_acr_values" class="col-3">Default authentication context required.</label>
          <!-- <input v-model="client.default_acr_values" type="number" class="form-control" id="default_acr_values" placeholder=""> -->

          <div class="col">
            <div class="input-group">
              <div class="col p-0">
                <multiselect
                  id="default_acr_values"
                  v-model="client.default_acr_values"
                  :options="options"
                  :customLabel="(option,label) => label?option.level:option.id"
                  label="level"
                  track-by="id"
                  :searchable="false"
                  :close-on-select="true"
                  :show-labels="false"
                  :multiple="true"
                  placeholder="Pick a value"
                >
                  <template slot="option" slot-scope="props">{{ props.option.level }}</template>
                </multiselect>
              </div>
              <div class="input-group-append">
                <b-form-select
                  v-model="client.default_acr_values_allow_override"
                  :options="[ {value: true, text:'Allow Override'}, {value: false, text:'Do NOT Allow Override'} ]"
                  class="custom-select"
                />
              </div>
            </div>
          </div>
        </div>

        <h6 class="c-grey-900">Client privileges</h6>

        <p>
          A client itself may get assigned roles. This allows a client to call an API on behalf of itself, after
          obtaining an access token via a client_credentials grant.
        </p>

        <div class="form-row">
          <div class="col-md-3">Roles</div>
          <div class="col">
            <multiselect
              id="roles"
              v-model="client.roles"
              track-by="value"
              :customLabel="( option,label) => (option.display)"
              :options="roles || []"
              :searchable="false"
              :close-on-select="true"
              :show-labels="true"
              :multiple="true"
              placeholder="Pick a value"
            ></multiselect>
          </div>
        </div>

        <h6 class="c-grey-900">Restrictions</h6>

        <p>Restrict access to this application based on assigned groups.</p>

        <div class="form-row">
          <div class="col-md-3">Groups</div>
          <div class="col">
            <multiselect
              id="roles"
              v-if="groups && groups.length > 0"
              v-model="client.groups"
              track-by="value"
              :customLabel="( {name} ) => (name)"
              :options="groups || []"
              :searchable="false"
              :close-on-select="true"
              :show-labels="true"
              :multiple="true"
              placeholder="Pick a value"
            ></multiselect>
            <p v-else>
              You don't have any groups configured.
              <router-link tag="a" class to="/groups">Create a group.</router-link>
            </p>
          </div>
        </div>


        <h6 class="c-grey-900">User Interface</h6>

        <p>Each application may be connected to another user interface.</p>

        <div class="form-row">
          <div class="col-md-3">User Interface</div>
          <div class="col">

            <b-form-select
                  v-model="client.user_interface"
                  :options="options_user_interfaces"
                  value-field="id"
                  text-field="url"
                  class="custom-select"
                />

          </div>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" @click="$router.go(-1)" class="btn btn-secondary ml-1">Back</button>
      </form>

    </template>

    <template v-slot:footer>
      <div class="card border-danger mb-3 mt-3" v-if="client && client.client_id != currentClientId">
      <div class="card-header">Danger Zone</div>
      <div class="card-body text-danger">
        <p
          class="card-text"
        >Clicking the button below will delete this application. This cannot be undone.</p>
        <button type="button" class="btn btn-danger" @click="deleteObject(client)">Delete</button>
      </div>
    </div>
    </template>


  </Main>
</template>

<script>
import Vue from "vue";
import { getDecodedAccesstoken } from "@/admin/helpers.js";
import Picker from "../userinterface/Picker.vue";
import Main from "@/admin/components/general/Main.vue";

export default {
  components: {
    Picker,
    Main
  },

  data() {
    return {
      callback: null,

      errors: {},

      wasValidated: false,
      loading: false,

      roles: [],
      groups: [],
      options: [],
      
      options_user_interfaces: [],

      passwordType: "password",

      client: null,
      currentClientId: null,

      show_post_logout_redirect_uris: false
    };
  },

  mounted() {
    this.currentClientId = getDecodedAccesstoken().aud;

    this.$http
      .get(
        this.$oidcUrl(
          "oauth/connect/register/" +
            encodeURIComponent(this.$route.params.client_id)
        )
      )
      .then(
        response => {
          this.client = response.data;

          if (!this.client.redirect_uris) {
            this.client.redirect_uris = [];
          }

          if (!this.client.post_logout_redirect_uris) {
            this.client.post_logout_redirect_uris = [];
          }

          if(JSON.stringify(this.client.redirect_uris) != JSON.stringify(this.client.post_logout_redirect_uris)){
            this.show_post_logout_redirect_uris = true;
          }

          this.wasValidated = false;
        },
        response => {
          // error callback
        }
      );

    this.$http.get(this.$murl("authchain/v2/manage/authlevels")).then(
      response => {
        this.options = response.data;
      }
    );

    this.$http.get(this.$murl("api/uiServers")).then(
      response => {
        this.options_user_interfaces = response.data;
        this.options_user_interfaces.push({
          url: 'Built in UI',
          id: null
        });
      }
    );

    this.$http.get(this.$murl("api/scim/v2/Roles")).then(response => {
      for (var v of response.data.Resources) {
        this.roles.push({
          value: v.value,
          display: v.tenant + " - " + v.display
        });
      }
    });

    this.$http.get(this.$murl("api/scim/v2/Groups")).then(response => {
      for (var v of response.data.Resources) {
        this.groups.push({
          value: v.id,
          name: v["urn:ietf:params:scim:schemas:core:2.0:Group"].name
        });
      }
    });
  },

  watch: {
    
  },

  methods: {

    picked(m) {
      this.callback(m);
      this.$refs.pickerModal.hide();
    },

    showPicker(callback) {
      this.callback = callback;
      this.$refs.pickerModal.show();
    },

    togglePassword() {
      this.passwordType = this.passwordType == "password" ? "text" : "password";
    },

    deleteObject(o) {
      this.$http
        .delete(
          this.$oidcUrl(
            "oauth/connect/register/" +
              encodeURIComponent(this.client.client_id)
          )
        )
        .then(response => {
          this.$noty({
            text: "Successfully deleted the client."
          });

          this.$router.replace({
            name: "oidc.clients.list"
          });
        });
    },

    onSubmit(event) {
      if (event.target.checkValidity()) {
        this.$http
          .put(
            this.$oidcUrl(
              "oauth/connect/register/" +
                encodeURIComponent(this.client.client_id)
            ),
            this.client
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

        //this.loading = true;
      } else {
        this.wasValidated = true;
        this.$noty({
          text: "We could not save this.",
          type: "error"
        });
      }

      event.preventDefault();
    }
  }
};
</script>


<style lang="scss" scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
