<template>
  <div class="container-fluid">
    <h4 class="c-grey-900 mt-1 mb-3">UI Servers</h4>

    <div class="bgc-white bd bdrs-3 p-3 mt-2">
      <Modal @ok="onSubmit" ref="newModal" title="New UI">
        <form class="needs-validation" @submit.prevent="onSubmit">
          <div class="form-row mb-3">
            <div class="col-md-3">
              <label for="ui.url">Url</label>
            </div>
            <div class="col">
              <input id="ui.url" type="url" class="form-control" v-model="ui.url" />
            </div>
          </div>

          <div v-for="(e, index) in errors" class="alert alert-danger" role="alert" :key="index">{{ e[0] }}</div>
        </form>
      </Modal>

      <button
        @click="newModal.show()"
        type="button"
        class="btn btn-primary btn-sm float-right">Add Server</button>
      <p>Manage your UIs. You can connect each application to its own UI. By default, applications will make use of the default login ui.</p>

      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th scope="col">Url</th>
            <th scope="col">Name</th>
            <th scope="col" style="width: 40px;"></th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>{{ getOidcUrl('') }}</td>
            <td>Built in UI</td>
            <td>
              <button
                class="btn btn-primary btn-block"
                @click="router.push({name: 'userinterface.design'});"
              >Edit</button>
            </td>
          </tr>
          <tr v-for="(ui,index) in objects" :key="index">
            <td>{{ ui.url }}</td>
            <td>{{ ui.name }}</td>
            <td>
              <button class="btn btn-danger btn-block" @click="deleteUi(ui)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>

import {ref, onMounted} from 'vue';
import {maxios, getOidcUrl} from '@/admin/helpers.js';
import Modal from '@/admin/components/general/Modal.vue';
import { useRouter } from 'vue-router4';

const router = useRouter();
const objects = ref(null);
const ui = ref({
  url: null
});
const newModal = ref(null);
const errors= ref([]);

onMounted(() => {
  load();
});

function deleteUi(ui){
  maxios.delete('/api/uiServers/' + ui.id).then(response => {
    load();
  });
}

function load(){
  maxios.get('/api/uiServers').then(response => {
    objects.value = response.data;
  });
}

function onSubmit(){
  maxios.post('/api/uiServers', ui.value).then(response => {
    newModal.value.hide();
    load();
  }).catch(error => {
    errors.value = error.response.data.errors;
  });

}

</script>

<style lang="scss" scoped>
table tr td {
  vertical-align: middle;
}
</style>
