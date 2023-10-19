
<template>

<div>

  <h3 class="c-grey-900">Sessions</h3>

  <p>A list of all sessions. Basically this means rememeberd log ins. One person may have multiple sessions.</p>

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

      <label class="sr-only" for="query">Keyword</label>
      <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="query" v-model="query"
        placeholder="">

      <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

      <button v-if="checked && checked.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted
        selected</button>

    </form>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th scope="col">Subject</th>
        <th scope="col">Module</th>
        <th scope="col">User</th>
        <th scope="col">Expires at</th>
        <th scope="col">Type</th>
        <th style="width: 30px; text-align: center;">
          <input @click.stop="selectAll" class="" type="checkbox">
        </th>
      </tr>
    </thead>
    <tbody>

      <tr v-for="moduleResult of moduleResults" :key="moduleResult.id">
        <td>{{ moduleResult.subject  ? moduleResult.subject.identifier : null }}.</td>
        <td>{{ moduleResult.module ? moduleResult.module.name : null  }}</td>
        <td>{{ moduleResult.user ? (moduleResult.user.email || moduleResult.user.name) : null  }}</td>
        <td>{{ moduleResult.expires_at }}</td>
        <td>{{ moduleResult.session ? 'Session' : 'Persistent' }}</td>
        <td @click.stop class="text-center"><input :value="moduleResult.id" v-model="checked" @click.stop class="" type="checkbox"></td>
      </tr>

      <tr v-if="moduleResults && moduleResults.length == 0">
        <td colspan="6" class="p-4 pl-0">There are no active sessions</td>
      </tr>


    </tbody>
  </table>


  <b-pagination v-if="pagination.total > pagination.per_page" @input="changePage" size="md" :total-rows="pagination.total"
      v-model="currentPage" :per-page="pagination.per_page" class=""></b-pagination>

</div>

</template>


<script setup>

import {ref, watch, getCurrentInstance, onMounted} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';

const vue = getCurrentInstance();

const itemsPerPage = ref(100);
const checked = ref([]);
const query = ref(null);
const currentPage = ref(1);
const totalResults = ref(null);
const startIndex = ref(null);
const pagination = ref({});
const moduleResults = ref([]);

onMounted(() => {
  changePage();
});

watch(itemsPerPage, (val) => {
  currentPage.value = 1;
  changePage();
});

function selectAll(){

  for (var moduleResult of moduleResults.value) {
      checked.value.push(moduleResult.id);

      checked.value = Array.from(new Set(checked.value));
    }

}

function deleteSelected(){

  let promises = [];

  for(var c of checked.value){
    
    promises.push(
      maxios.delete('api/moduleResults/' + c)
    );
  }

  Promise.all(promises).then(e => {
    notify({
      text: 'We have succesfully deleted the selected sessions.'
    });
    checked.value = [];
    changePage();
  });

}

function onSubmit() {
  currentPage.value = 1;
  changePage();
}

function changePage() {

  maxios.get('api/moduleResults', {
    params: {
      query: query.value,
      page: currentPage.value,
      size: itemsPerPage.value
    }
  }).then(response => {
    moduleResults.value = response.data.data;
    pagination.value = response.data
  }, response => {
    // error callback

    totalResults.value = response.data.totalResults;
    startIndex.value = parseInt(response.data.startIndex);

  });

}
</script>
