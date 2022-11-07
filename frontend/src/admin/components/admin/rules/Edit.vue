
<template>
  <div class="container-fluid">
    <h4 class="c-grey-900 mt-1 mb-3">Rules</h4>

    <div class="row" v-if="action">
      <div class="col-md-12">
        <div class="bgc-white bd bdrs-3 p-3 mt-2 codeblock">
          <form
            class="needs-validation"
            novalidate
            :class="{'was-validated': wasValidated}"
            v-on:submit.prevent="onSubmit"
          >
            <div class="form-group">
              <label for="url">Display Name</label>
              <input
                type="text"
                v-model.trim="action.display_name"
                class="form-control"
                id="display_name"
                aria-describedby="display_name_help"
              />
              <small
                id="display_name_help"
                class="form-text text-muted"
              >Selected email templates, users and groups are provided as variables to the rule.</small>
            </div>

            <codemirror v-if="action" id="codemirror" v-model="action.code" :options="cmOptions"></codemirror>

            <div class="alert alert-danger" role="alert" v-if="error">{{ error }}</div>

            <div class="form-group mt-3">
              <label for="url" style="z-index: 999999">Variables</label>

              <multiselect
                v-model="action.variables"
                label="name"
                track-by="id"
                placeholder="Type to search"
                :options="variableOptions"
                :multiple="true"
                :searchable="true"
                :loading="isLoading"
                :internal-search="false"
                :clear-on-select="true"
                :close-on-select="false"
                :options-limit="300"
                group-values="options"
                group-label="group"
                :group-select="false"
                :max-height="600"
                :show-no-results="false"
                :hide-selected="true"
                @search-change="asyncFind"
              >
                <template slot="tag" slot-scope="{option, remove}">
                  <span
                    v-b-tooltip.hover
                    :title="`available as ${getVariableName(option)}`"
                    class="multiselect__tag"
                  >
                    <font-awesome-icon :icon="getIcon(option)" />
                    <span class="pl-2">{{ option.name }}</span>
                    <i
                      aria-hidden="true"
                      @click="remove(option)"
                      tabindex="1"
                      class="multiselect__tag-icon"
                    ></i>
                  </span>
                </template>

                <span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
              </multiselect>

              <small id="display_name_help" class="form-text text-muted">
                Use https to ensure the confidentially of the transmitted
                data.
              </small>
            </div>

            <button class="btn btn-primary mt-3">Save</button>
          </form>
        </div>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-12">
        <div class="bgc-white bd bdrs-3 p-3 mt-3">
          <div class="row">
            <div class="col-md-6">
              <p>
                <strong>Input:</strong>
              </p>
              <codemirror id="inputuser" v-model="input" :options="cmOptions"></codemirror>
            </div>

            <div class="col-md-6">
              <p>
                <strong>Output:</strong>
              </p>
              <codemirror id="inputuser" v-model="output" :options="cmOptions"></codemirror>
            </div>
          </div>

          <button class="btn btn-success mt-3 mr-2" type="button" @click="test">Run test</button>
        </div>
      </div>
    </div>

    <div class="card border-danger mb-3 mt-3" v-if="action">
      <div class="card-header">Danger Zone</div>
      <div class="card-body text-danger">
        <p class="card-text">Clicking the button below will delete this rule. This cannot be undone.</p>
        <button type="button" class="btn btn-danger" @click="deleteRule(action)">Delete</button>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.codeblock {
  min-height: 100%;

  form {
    min-height: 100%;
  }
}

.scrollbars {
  height: 15rem;
  overflow-y: scroll;
}

.CodeMirror {
  z-index: 0;
}

#inputuser .CodeMirror {
  height: 200px;
}
</style>


<script>
import Vue from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faEnvelope, faUser, faUsers } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

library.add(faEnvelope, faUser, faUsers);

Vue.component("font-awesome-icon", FontAwesomeIcon);

//import faGoogle from '@fortawesome/fontawesome-free-brands/faGoogle'

//fontawesome.library.add(faGoogle)

export default {
  components: {
    codemirror: resolve =>
      import(
        /* webpackChunkName: "vue-codemirror" */ "../../lib/codemirror.js"
      ).then(m => {
        resolve(m.default.codemirror);
      })
  },

  mounted() {
    this.$http
      .get(this.$murl("api/cloudFunctions/" + this.$route.params.rule_id))
      .then(
        response => {
          this.action = response.data;
          this.action.variables =
            !this.action.variables || !Array.isArray(this.action.variables)
              ? []
              : this.action.variables;

          return this.action;
        }
      ).then( action => {

        if(action.type == 'attribute'){

          this.$http.get(this.$oidcUrl('oauth/userinfo')).then(response => {

            return this.$http.get(this.$murl(
              `api/scim/v2/Subjects?sortBy=meta.created&count=1&filter=identifier+eq+"${response.data.sub}"`
            ));

          }).then(response => {
            this.input = JSON.stringify(
              {
                subject: response.data.Resources[0],
                attributes: [
                  'email',
                  'phone'
                ],
                scopes: ['openid','email']
              },
              null,
              2
            );
          });

        }else{
          // TODO: grap last created user ??
          // before, after (change email, user creation, user activation)

          this.$http.get(this.$murl(
              `api/scim/v2/Users?sortBy=meta.created&count=1&sortOrder=ascending"`
          )).then(response => {
            let user = response.data.Resources[0];
            let after = JSON.parse(JSON.stringify(user));
            after['urn:ietf:params:scim:schemas:core:2.0:User']['emails'] = [
              {
                value: 'new-email@non-existing.dom'
              }
            ];

            this.input = JSON.stringify(
              {
                before: user,
                after: after,
                action: 'replace',
                me: false
              },
              null,
              2
            );
          });
        }
        
      })

      

  },

  methods: {
    lowerFirst(s) {
      return s.charAt(0).toLowerCase() + s.substring(1);
    },

    getIcon(option) {
      let result = "null";

      switch (option.type) {
        case "EmailTemplate":
          result = "envelope";
          break;
        case "User":
          result = "user";
          break;
        case "Group":
          result = "users";
          break;
      }

      return result;
    },

    getVariableName(option) {
      return (
        "variables." +
        this.lowerFirst(
          `${option.type}${this.action.variables
            .filter(v => v.type == option.type)
            .findIndex(e => e.id == option.id)}`
        )
      );
    },

    asyncFind(query) {
      this.isLoading = true;

      Promise.all([
        this.$http.get(this.$murl("api/mail_template")).then(response => {
          return {
            group: "Email Templates",
            options: response.data
              .filter(r => {
                return r.name.toLowerCase().includes(query.toLowerCase());
              })
              .map(r => {
                return {
                  name: r.name,
                  type: "EmailTemplate",
                  id: r.id
                };
              })
          };
        }),

        this.$http
          .get(
            this.$murl(
              `api/scim/v2/Users?sortBy=id&sortOrder=desc&count=30&filter=${encodeURIComponent(
                'emails.value co "' + query + '"'
              )}`
            )
          )
          .then(response => {
            return response.data.Resources;
          })
          .then(users => {
            return {
              group: "Users",
              options: users.map(r => {
                return {
                  name: (
                    r["urn:ietf:params:scim:schemas:core:2.0:User"].emails || []
                  ).filter(e => e.primary)[0].value,
                  id: r.id,
                  type: "User"
                };
              })
            };
          })
          .catch(e => {
            // ignore?
          }),

        this.$http
          .get(
            this.$murl(
              `api/scim/v2/Groups?filter=${encodeURIComponent(
                'name co "' + query + '"'
              )}`
            )
          )
          .then(response => {
            return response.data.Resources;
          })
          .then(users => {
            return {
              group: "Groups",
              options: users.map(r => {
                return {
                  name: r["urn:ietf:params:scim:schemas:core:2.0:Group"].name,
                  id: r.id,
                  type: "Group"
                };
              })
            };
          })
          .catch(e => {
            // ignore?
          })
      ]).then(r => {
        this.variableOptions = r;

        this.isLoading = false;
      });
    },

    deleteRule(rule) {
      this.$http.delete(this.$murl(`api/cloudFunctions/${rule.id}`)).then(r => {
        this.$noty({
          text: "We have succesfully DELETED your rule."
        });

        this.$router.replace("/rules");
      });
    },

    onSubmit() {
      let promise = null;

      if (!this.action.id) {
        promise = this.$http.post(
          this.$murl("api/cloudFunctions"),
          this.action
        );
      } else {
        promise = this.$http.put(
          this.$murl(`api/cloudFunctions/${this.action.id}`),
          this.action
        );
      }

      promise
        .then(result => {
          this.action = result.data;
          this.error = null;

          this.$noty({
            text: "We have succesfully saved your rule."
          });
        })
        .catch(result => {
          this.error = result.data.error;
        });
    },

    test() {
      this.$http
        .post(
          this.$murl(
            `api/cloudFunctions/invoke/${encodeURIComponent(this.action.id)}`
          ),
          JSON.parse(this.input)
        )
        .then(response => {
          this.output = JSON.stringify(response.data, null, 2);
          this.error = null;
        })
        .catch(response => {
          this.error = response.data.error;
        });
    }
  },

  data() {
    return {
      action: null,
      wasValidated: false,

      variableOptions: [],
      isLoading: false,

      error: null,

      output: null,

      input: '',

      cmOptions: {
        // codemirror options
        tabSize: 4,
        mode: "text/javascript",
        theme: "lucario",
        lineNumbers: true,
        line: true
      }
    };
  }
};
</script>