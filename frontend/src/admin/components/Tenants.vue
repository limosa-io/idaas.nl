
<template>
<div>

  <Modal v-model="showModal" id="newTenant" title="New tenant" @ok="onSubmit">
    <form @submit.prevent="onSubmit">
      <div class="form-group">
        <label for="subdomain">Create your tenant on your subdomain</label>
        <input :class="{'is-invalid': errors['subdomain']}" v-model="subdomain" type="text" class="form-control" id="subdomain"
          aria-describedby="subdomainHelp" placeholder="subdomain">
        <small id="subdomainHelp" class="form-text text-muted">Choose a simple name.</small>

        <div v-for="(e,index) in errors.subdomain" class="invalid-feedback" :key="index">
          {{ e }}
        </div>

      </div>
    </form>
  </Modal>

  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col">Domain</th>
        <th scope="col">Created</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(tenant,index) of tenants" :key="index">
        <td>{{ tenant.subdomain }}<span class="text-muted">.ice.test</span></td>
        <td>{{ tenant.created_at }}</td>
      </tr>
      <button class="btn btn-primary mt-2" @click="showModal = true">New Tenant</button>
    </tbody>
  </table>

</div>

</template>

<script setup>

import {ref, onMounted} from 'vue';
import { maxios } from '@/admin/helpers.js';
import Modal from '@/admin/components/general/Modal.vue';

const tenants = ref(null);
const subdomain = ref(null);
const showModal = ref(false);
const errors = ref({});

onMounted(() => {

  maxios.get('/api/tenants', {
    headers: {
      'Authorization': 'Bearer ' + window.sessionStorage.getItem('access_token')
    }
  }).then(response => {
    tenants.value = response.data;
  });

});

function getAccessToken() {
  return window.sessionStorage.getItem('access_token');
}

function onSubmit() {
  maxios.post('/api/tenants', {
    subdomain: subdomain.value
  }, {
    headers: {
      'Authorization': 'Bearer ' + getAccessToken()
    }
  }).then(response => {
    
  }, response => {
    errors.value = response.data.errors;
  })
}
</script>

