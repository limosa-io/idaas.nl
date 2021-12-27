<template>
  <div class="container-fluid">
    <h4 class="c-grey-900 mt-1 mb-3">Internationalization</h4>

    <div class="row">
      <div class="col-md-12">
        <div class="bgc-white bd bdrs-3 pl-3 pr-3 pb-3 pt-2 mt-2">
          <div class="row">
            <div class="col-md-12">
              <table class="table mt-3">
                <thead>
                  <tr>
                    <th scope="col">Key</th>
                    <th scope="col">Translation</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(c, index) in filteredLabels" :key="index">
                    <td>{{ c.key }}</td>

                    <td>
                      <div class="row" v-if="customizations[c.key]">
                        <div class="col-11">
                          <textarea rows="1" v-model="customizations[c.key]" class="form-control" />
                        </div>
                        <div class="col-1">
                          <a href="#" @click.prevent="$delete(customizations, c.key)">Reset</a>
                        </div>
                      </div>
                      <span v-else @click="createCustomization(c)">{{ c.value }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>

              <button type="button" class="btn btn-primary" @click="save">Save</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      customizations: [],

      filteredLabels: [],
      selectedKey: null
    };
  },

  methods: {
    createCustomization(c) {
      this.$set(this.customizations, c.key, c.value);
    },

    save() {
      this.$http
        .put(
          this.$murl(
            `api/language/customizations/${this.$route.params.locale}`
          ),
          this.customizations
        )
        .then(response => {});
    },

    flatten(object, prefix = "") {
      return Object.keys(object).reduce(
        (prev, element) =>
          object[element] &&
          typeof object[element] === "object" &&
          !Array.isArray(object[element])
            ? {
                ...prev,
                ...this.flatten(object[element], `${prefix}${element}.`)
              }
            : {
                ...prev,
                ...{
                  [`${prefix}${element}`]: object[element]
                }
              },
        {}
      );
    }
  },

  mounted() {
    this.$http
      .get(this.$murl(`api/language/defaults/${this.$route.params.locale}`))
      .then(response => {
        this.filteredLabels = Object.entries(this.flatten(response.data)).map(
          ([key, value]) => ({
            key,
            value
          })
        );
      });

    this.$http
      .get(
        this.$murl(`api/language/customizations/${this.$route.params.locale}`)
      )
      .then(response => {
        this.customizations = response.data;
      });
  }
};
</script>

<style scoped lang="scss">
.label-option {
  span {
    display: block;
    font-style: italic;
    color: rgb(111, 111, 111);
    line-height: 2rem;
  }
}
</style>
