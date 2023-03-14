<template>
  <Main title="Rules">
    <template v-slot:body>
      <h4 class="c-grey-900 mt-2">
        User Event Rules
        <button
          @click.prevent="addRule('user_event')"
          type="button"
          class="btn btn-primary btn-sm float-right"
        >
          Add Rule
        </button>
      </h4>

      <p>
        An <code class="highlighter-rouge">user event rule</code> allow
        extensive customization. Interact with your provisioning system,
        analytics environment or CRM system.
      </p>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">Name</th>

            <th scope="col" style="width: 40px"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(cloudFunction, index) in cloudFunctions.filter(
              (c) => c.type == 'user_event' && !c.is_sequence
            )"
            :key="index"
          >
            <td>
              {{ cloudFunction.display_name || "No Name" }}
              <small class="form-text text-muted">{{
                cloudFunction.type
              }}</small>
            </td>
            <td>
              <button
                class="btn btn-dark btn-block"
                @click="edit(cloudFunction)"
              >
                Edit
              </button>
            </td>
          </tr>

          <tr
            v-if="
              cloudFunctions.filter((c) => c.type == 'user_event').length == 0
            "
          >
            <td colspan="2">No rules configured</td>
          </tr>
        </tbody>
      </table>

      <hr class="mt-5 mb-5" />

      <h4 class="c-grey-900 mt-2">
        Attribute Rules
        <button
          @click.prevent="addRule('attribute')"
          type="button"
          class="btn btn-primary btn-sm float-right"
        >
          Add Rule
        </button>
      </h4>

      <p>
        An <code class="highlighter-rouge">attribute rule</code> is used to
        populate attributes in <code class="highlighter-rouge">id_token</code>,
        <code class="highlighter-rouge">userinfo</code> and
        <code class="highlighter-rouge">SAML Assertion</code> responses.
      </p>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col">Name</th>

            <th scope="col" style="width: 40px"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(cloudFunction, index) in cloudFunctions.filter(
              (c) => c.type == 'attribute' && !c.is_sequence
            )"
            :key="index"
          >
            <td>
              {{ cloudFunction.display_name || "No Name" }}
              <small class="form-text text-muted">{{
                cloudFunction.type
              }}</small>
            </td>
            <td>
              <button
                class="btn btn-dark btn-block"
                @click="edit(cloudFunction)"
              >
                Edit
              </button>
            </td>
          </tr>

          <tr
            v-if="
              cloudFunctions.filter((c) => c.type == 'attribute').length == 0
            "
          >
            <td colspan="2">No rules configured</td>
          </tr>
        </tbody>
      </table>
    </template>
  </Main>
</template>

<script>
export default {
  data() {
    return {
      cloudFunctions: null,

      rule: {
        display_name: null,
        code: "",
        active: true,
        type: null,
      },

      errors: [],
    };
  },

  methods: {
    edit: function (rule) {
      this.$router.push({ name: "rules.edit", params: { rule_id: rule.id } });
    },

    loadRules() {
      this.$http.get(this.$murl("api/cloudFunctions")).then(
        (response) => {
          this.cloudFunctions = response.data;
        },
        (response) => {
          // error callback
        }
      );
    },

    addRule(type) {
      this.$http
        .post(this.$murl("api/cloudFunctions"), {
          display_name:
            (type == "attribute" ? "Attribute Rule" : "User Event Rule") +
            " - " +
            new Date().toLocaleDateString(),
          code: "",
          active: true,
          type: type,
        })
        .then(
          (response) => {
            this.loadRules();
          },
          (response) => {
            // error callback
            this.errors = response.data.errors;
          }
        );
    },
  },

  mounted() {
    this.loadRules();
  },
};
</script>

<style>
</style>
