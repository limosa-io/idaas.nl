
<template>
  <MainTemplate title="Rules">
    <template v-slot:body v-if="action">
      <form class="needs-validation" novalidate :class="{ 'was-validated': wasValidated }" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label for="url">Display Name</label>
          <input type="text" v-model.trim="action.display_name" class="form-control" id="display_name"
            aria-describedby="display_name_help" />
          <small id="display_name_help" class="form-text text-muted">Selected email templates, users and groups are
            provided as
            variables to the rule.</small>
        </div>

        <textarea v-if="action" v-model="action.code"></textarea>

        <div class="alert alert-danger" role="alert" v-if="error">
          {{ error }}
        </div>

        <div class="form-group mt-3">
          <label for="url" style="z-index: 999999">Variables</label>

          <multiselect v-model="action.variables" label="name" track-by="id" placeholder="Type to search"
            :options="variableOptions" :multiple="true" :searchable="true" :loading="isLoading" :internal-search="false"
            :clear-on-select="true" :close-on-select="false" :options-limit="300" group-values="options"
            group-label="group" :group-select="false" :max-height="600" :show-no-results="false" :hide-selected="true"
            @search-change="asyncFind">
            <template slot="tag" slot-scope="{ option, remove }">
              <span v-b-tooltip.hover :title="`available as ${getVariableName(option)}`" class="multiselect__tag">
                <FontAwesomeIcon :icon="getIcon(option)" />
                <span class="pl-2">{{ option.name }}</span>
                <i aria-hidden="true" @click="remove(option)" tabindex="1" class="multiselect__tag-icon"></i>
              </span>
            </template>

            <span slot="noResult">Oops! No elements found. Consider changing the search
              query.</span>
          </multiselect>
        </div>

        <button class="btn btn-primary mt-3">Save</button>
      </form>

      <div class="row mt-3">
        <div class="col-md-12">
          <div class="bgc-white bd bdrs-3 p-3 mt-3">
            <div class="row">
              <div class="col-md-6">
                <p>
                  <strong>Input:</strong>
                </p>
                <textarea id="inputuser" v-model="input"></textarea>
              </div>

              <div class="col-md-6">
                <p>
                  <strong>Output:</strong>
                </p>
                <textarea id="inputuser" v-model="output"></textarea>
              </div>
            </div>

            <button class="btn btn-success mt-3 mr-2" type="button" @click="test">
              Run test
            </button>
          </div>
        </div>
      </div>
    </template>

    <template v-slot:footer>
      <Danger body="Clicking the button below will delete this rule. This cannot be undone.">
        <button type="button" class="btn btn-danger" @click="deleteRule(action)">
          Delete
        </button>
      </Danger>
    </template>
  </MainTemplate>
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


<script setup>
import { getCurrentInstance, onMounted, ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faEnvelope, faUser, faUsers } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { maxios, notify } from '@/admin/helpers.js'
import { useRouter } from "vue-router4";

library.add(faEnvelope, faUser, faUsers);

const vue = getCurrentInstance();
const action = ref(null);
const wasValidated = ref(false);
const variableOptions = ref([]);
const isLoading = ref(false);
const error = ref(null);
const output = ref(null);
const input = ref(null);
const router = useRouter();

onMounted(() => {
  maxios.get(`api/cloudFunctions/${vue.proxy.$route.params.rule_id}`).then((response) => {
    action.value = response.data;
    action.value.variables = !action.value.variables || !Array.isArray(action.value.variables) ? [] : action.value.variables;

    return action.value;
  }).then((action) => {
    if (action.type == "attribute") {
      maxios.get(`api/scim/v2/Subjects?sortBy=meta.created&count=1&filter=identifier+eq+"${$oidc.user.sub}"`).then((response) => {
        input.value = JSON.stringify({
          subject: response.data.Resources[0],
          attributes: ["email", "phone"],
          scopes: ["openid", "email"],
        }, null, 2);
      });
    } else {

      maxios
        .get(
          `api/scim/v2/Users?sortBy=meta.created&count=1&sortOrder=ascending"`
        )
        .then((response) => {
          let user = response.data.Resources[0];
          let after = JSON.parse(JSON.stringify(user));
          after["urn:ietf:params:scim:schemas:core:2.0:User"]["emails"] = [
            {
              value: "new-email@non-existing.dom",
            },
          ];

          input.value = JSON.stringify(
            {
              before: user,
              after: after,
              action: "replace",
              me: false,
            },
            null,
            2
          );
        });
    }
  });
});

function lowerFirst(s) {
  return s.charAt(0).toLowerCase() + s.substring(1);
}

function getIcon(option) {
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
}

function getVariableName(option) {
  return (
    "variables." +
    lowerFirst(
      `${option.type}${action.value.variables
        .filter((v) => v.type == option.type)
        .findIndex((e) => e.id == option.id)}`
    )
  );
}

function asyncFind(query) {
  isLoading.value = true;

  Promise.all([
    maxios.get(`api/mail_template`).then((response) => {
      return {
        group: "Email Templates",
        options: response.data
          .filter((r) => {
            return r.name.toLowerCase().includes(query.toLowerCase());
          })
          .map((r) => {
            return {
              name: r.name,
              type: "EmailTemplate",
              id: r.id,
            };
          }),
      };
    }),

    maxios
      .get(
        `api/scim/v2/Users?sortBy=id&sortOrder=desc&count=30&filter=${encodeURIComponent(
          'emails.value co "' + query + '"'
        )}`
      )
      .then((response) => {
        return response.data.Resources;
      })
      .then((users) => {
        return {
          group: "Users",
          options: users.map((r) => {
            return {
              name: (
                r["urn:ietf:params:scim:schemas:core:2.0:User"].emails || []
              ).filter((e) => e.primary)[0].value,
              id: r.id,
              type: "User",
            };
          }),
        };
      })
      .catch((e) => {
        // ignore?
      }),

    maxios
      .get(
        `api/scim/v2/Groups?filter=${encodeURIComponent(
          'name co "' + query + '"'
        )}`
      )
      .then((response) => {
        return response.data.Resources;
      })
      .then((users) => {
        return {
          group: "Groups",
          options: users.map((r) => {
            return {
              name: r["urn:ietf:params:scim:schemas:core:2.0:Group"].name,
              id: r.id,
              type: "Group",
            };
          }),
        };
      })
      .catch((e) => {
        // ignore?
      }),
  ]).then((r) => {
    variableOptions.value = r;

    isLoading.value = false;
  });
}

function deleteRule(rule) {
  maxios
    .delete(`api/cloudFunctions/${rule.id}`)
    .then((r) => {
      notify({
        text: "We have succesfully DELETED your rule.",
      });

      router.replace("/rules");
    });
}

function onSubmit() {
  let promise = null;

  if (!action.value.id) {
    promise = maxios.post(
      `api/cloudFunctions`,
      action.value
    );
  } else {
    promise = maxios.put(
      `api/cloudFunctions/${action.value.id}`,
      action.value
    );
  }

  promise
    .then((result) => {
      action.value = result.data;
      error.value = null;

      notify({
        text: "We have succesfully saved your rule.",
      });
    })
    .catch((result) => {
      error.value = result.data.error;
    });
}

function test() {
  maxios
    .post(
      `api/cloudFunctions/invoke/${encodeURIComponent(action.value.id)}`,
      JSON.parse(input.value)
    )
    .then((response) => {
      output.value = JSON.stringify(response.data, null, 2);
      error.value = null;
    })
    .catch((response) => {
      error.value = response.data.error;
    });
}

</script>