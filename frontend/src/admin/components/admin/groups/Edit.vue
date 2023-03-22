<template>
  <Main title="Edit Group">
    <template v-if="group" v-slot:body>
      <form
        v-if="group"
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
              required
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
          Save Group
        </button>
      </form>
    </template>

    <template v-slot:footer>
      <Danger body="Clicking the button below will delete this group. This cannot be undone.">
          <button
            type="button"
            class="btn btn-danger"
            @click="deleteObject(group)"
          >
            Delete
          </button>
      </Danger>
    </template></Main
  >
</template>
</Main>

</template>

<script>
import Vue from "vue";

export default {
  data() {
    return {
      group: null,
      errors: {},

      wasValidated: false,
    };
  },

  mounted() {
    this.$http
      .get(this.$murl("api/scim/v2/Groups/" + this.$route.params.group_id))
      .then((response) => {
        this.group = response.data;
      });
  },

  methods: {
    deleteObject(object) {
      this.$http.delete(this.$murl("api/scim/v2/Groups/" + object.id)).then(
        (response) => {
          this.$noty({
            text: "We have succesfully deleted this group.",
          });

          this.$router.replace({
            name: "groups.list",
          });
        },
        (response) => {
          // error callback
        }
      );
    },

    onSubmit(event) {
      this.$http
        .put(
          this.$murl("api/scim/v2/Groups/" + this.$route.params.group_id),
          JSON.stringify(this.group),
          {
            headers: {
              "content-type": "application/scim+json",
            },
          }
        )
        .then(
          (response) => {
            this.$noty({
              text: "We have succesfully saved this group.",
            });
            this.errors = {};
          },
          (response) => {
            this.$noty({
              text: "There were some errors during saving.",
            });
            this.errors = response.data.errors;
          }
        );

      event.preventDefault();
    },
  },

  components: {},
};
</script>
