<template>
  <MainTemplate title="Internationalization">
    <template v-slot:body>
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
                  <textarea
                    rows="1"
                    v-model="customizations[c.key]"
                    class="form-control"
                  />
                </div>
                <div class="col-1">
                  <a href="#" @click.prevent="$delete(customizations, c.key)"
                    >Reset</a
                  >
                </div>
              </div>
              <span v-else @click="createCustomization(c)">{{ c.value }}</span>
            </td>
          </tr>
        </tbody>
      </table>

      <button type="button" class="btn btn-primary" @click="save">Save</button>
    </template>
  </MainTemplate>
</template>

<script setup>

import { ref, onMounted } from "vue";
import {maxios} from '@/admin/helpers.js'

const customization = ref({});
const filteredLabels = ref([]);
const selectedKey = ref(null);

onMounted(() => {
  maxios
    .get(`api/language/defaults/${$route.params.locale}`)
    .then((response) => {
      filteredLabels.value = Object.entries(flatten(response.data)).map(
        ([key, value]) => ({
          key,
          value,
        })
      );
    });

  maxios
    .get(`api/language/customizations/${$route.params.locale}`)
    .then((response) => {
      customization.value = response.data;
    });
});

function createCustomization(c) {
  customization.value[c.key] = c.value;
}

function save() {
  maxios
    .put(
      `api/language/customizations/${$route.params.locale}`,
      customization.value
    )
    .then((response) => {});
}
function flatten(object, prefix = "") {
  return Object.keys(object).reduce(
    (prev, element) =>
      object[element] &&
      typeof object[element] === "object" &&
      !Array.isArray(object[element])
        ? {
            ...prev,
            ...flatten(object[element], `${prefix}${element}.`),
          }
        : {
            ...prev,
            ...{
              [`${prefix}${element}`]: object[element],
            },
          },
    {}
  );
}
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
