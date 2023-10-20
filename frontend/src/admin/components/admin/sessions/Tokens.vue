<template>

<div>

  <h3 class="c-grey-900">Access Tokens</h3>

  <p>Access tokens are used to protect APIs</p>

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
        <th scope="col">Client</th>
        <th scope="col">User</th>
        <th scope="col">Subject</th>
        <th scope="col">Expiration</th>
        <th scope="col">Scopes</th>
        <th style="width: 30px; text-align: center;">
          <input @click.stop="selectAll" class="" type="checkbox">
        </th>
      </tr>
    </thead>
    <tbody>

      <tr v-for="token of tokens" :key="token.id">
        <td>{{ token.client.client_name }}</td>
        <td>{{ token.subject && token.subject.user ? token.subject.user.email : null }}</td>
        <td>{{ token.subject ? token.subject.identifier : null }}</td>
        <td>{{ token.expires_at }}</td>
        <td> <span v-for="(scope,index) of token.scopes" :key="index">{{ scope }} </span></td>
        <td @click.stop class="text-center"><input :value="token.id" v-model="checked" @click.stop class="" type="checkbox"></td>
      </tr>

    </tbody>
  </table>

  <div class="d-flex flex-row align-items-center justify-content-between">

    <div>Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries</div>
    <Pagination v-if="pagination.total > pagination.per_page" @input="changePage" size="md" :total-rows="pagination.total"
      v-model="currentPage" :per-page="pagination.per_page" class=""></Pagination>
  </div>

</div>

</template>

<script setup>

import {ref, watch, onMounted, getCurrentInstance} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';

const tokens = ref([]);
const checked = ref([]);
const itemsPerPage = ref(20);
const query = ref(null);
const currentPage = ref(1);
const totalResults = ref(null);
const startIndex = ref(null);
const pagination = ref({});
const vue = getCurrentInstance();

onMounted(() => {
  changePage();
});

watch(itemsPerPage, (val) => {
  currentPage.value = 1;
  changePage();
});

function selectAll(){

  for (var token of tokens.value) {
      checked.value.push(token.id);

      checked.value = Array.from(new Set(checked.value));
    }

}

function deleteSelected(){

  let promises = [];

  for(var c of checked.value){

    promises.push(

      maxios.post('api/tokens/revoke', {
        token: c
      })

    );
  }

  Promise.all(promises).then(e => {
    notify({
      text: 'We have succesfully revoked the selected tokens.'
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

  maxios.get('api/tokens', {
    params: {
      query: query.value,
      page: currentPage.value,
      size: itemsPerPage.value
    }
  }).then(response => {
    tokens.value = response.data.data
    pagination.value = response.data
  }, response => {
    // error callback

    totalResults.value = response.data.totalResults;
      startIndex.value = parseInt(response.data.startIndex);

  });

}

</script>

