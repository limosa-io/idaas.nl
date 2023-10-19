<template>

<div>

  <h3 class="c-grey-900">Subjects</h3>

  <p>Subjects are linked to live sessions and issues access tokens.</p>

  <div class="d-flex flex-row justify-content-between">
    <div>
      <form class="form-inline">
        <label class="" for="entries">Show</label>
        <select class="form-control form-control-sm ml-2 mr-2" id="entries" v-model="itemsPerPage">
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        entries
      </form>

    </div>

    <form class="form-inline" v-on:submit.prevent="onSubmit">

      <label class="sr-only" for="search.email">E-mail</label>
      <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="search.email" v-model="search.email"
        placeholder="">

      <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

      <button v-if="checked && checked.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted
        selected</button>
    </form>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th scope="col">Identifier</th>
        <th scope="col">User</th>
        <th scope="col">Created</th>
        <th scope="col">Details</th>
        <th style="width: 30px; text-align: center;">
          <input @click.stop="selectAll" class="" type="checkbox">
        </th>
      </tr>
    </thead>
    <tbody>

      <tr v-for="(subject,index) of resources" @click="edit(user)" :key="index">
        <td style="width: 20%">{{ subject['urn:ietf:params:scim:schemas:subjects'].identifier }}</td>
        <td class="">{{ subject['urn:ietf:params:scim:schemas:subjects'].user ? subject['urn:ietf:params:scim:schemas:subjects'].user.email : '' }}</td>
        <td>
          {{ subject.meta.created }}
        </td>
        <td class="details">
          <pre class="mb-0">{{ beautify(subject['urn:ietf:params:scim:schemas:subjects'].subject) }}</pre>
        </td>
        <td @click.stop class="text-center"><input :value="subject.id" v-model="checked" @click.stop class="" type="checkbox"></td>
      </tr>

      <tr v-if="!resources || resources.length == 0">
        <td class="p-4 pl-0" colspan="4">There are no subjects</td>
      </tr>
    </tbody>
  </table>

  <div class="d-flex flex-row align-items-center justify-content-between">

    <div>Showing {{ startIndex }} to {{ (startIndex+parseInt(itemsPerPage)-1) }} of {{ totalResults }} entries</div>

    <b-pagination v-if="totalResults > itemsPerPage" @input="changePage" size="md" :total-rows="totalResults" v-model="currentPage"
      :per-page="itemsPerPage" class=""></b-pagination>

  </div>

</div>

</template>

<script setup>
import { ref, getCurrentInstance, onMounted, watch } from "vue";
import {maxios, notify} from '@/admin/helpers.js';

const vue = getCurrentInstance();
const loaded = ref(false);
const currentPage = ref(1);
const startIndex = ref(1);
const itemsPerPage = ref(20);
const resources = ref([]);
const totalResults = ref(null);
const checked = ref([]);
const search = ref({
  email: null,
  group: null
});
const filter = ref(null);

watch(itemsPerPage, (val) => {
  changePage(currentPage.value);
});

function selectAll() {
  for (var resource of resources) {
    checked.push(resource.id);

    checked = Array.from(new Set(checked));
  }
}

function onSubmit() {
  changePage(currentPage.value);
}

function deleteSelected() {
  let promises = [];

  for (var c of checked) {
    promises.push(maxios.delete("api/scim/v2/Subjects/" + c));
  }

  Promise.all(promises).then(
    e => {
      notify({
        text: "We have succesfully deleted the selected subject."
      });
      checked = [];
      changePage(currentPage.value);
    },
    e => {}
  );
}

function beautify(subject) {
  return JSON.stringify(subject, undefined, 3);
}

function changePage(page){
  maxios
    .get(
      
        `api/scim/v2/Subjects?sortBy=meta.created&sortOrder=descending&count=${itemsPerPage.value}&startIndex=` +
        ((page || 1) - 1) * itemsPerPage.value +
        (filter.value ? "&filter=" + filter.value : "")
      
    )
    .then(
      response => {
        resources.value = response.data.Resources;
        totalResults.value = response.data.totalResults;
        startIndex.value = parseInt(response.data.startIndex);
      }
    );
}

</script>

<style lang="scss" scoped>
.details{
  max-width: 300px;
}
</style>