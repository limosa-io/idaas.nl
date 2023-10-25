<template>

<div>
  <!-- v-if="importX509" -->
  <Modal @ok="onSubmitImportX509" ref="importmodal" id="importX509" title="Import X509" >

    <form novalidate :class="{'was-validated': wasValidated}" class="needs-validation" v-on:submit.prevent="onSubmitImportX509">

      <div class="form-group row">
        <label for="x509" class="col-sm-2 col-form-label">Certificate</label>
        <div class="col-sm-10">
          <textarea :class="{'is-invalid': errors['x509']}" required class="form-control" rows="4" id="x509" v-model="importX509.x509"></textarea>

          <div v-for="(e ,index) in errors['x509']" class="invalid-feedback" :key="index">
            {{ e }}
          </div>

        </div>
      </div>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">Private Key</label>
        <div class="col-sm-10">
          <textarea :class="{'is-invalid': errors['private_key']}" class="form-control" rows="4" v-model="importX509.private_key"></textarea>
          <div v-for="(e, index) in errors['private_key']" class="invalid-feedback" :key="index">
            {{ e }}
          </div>
        </div>
      </div>

    </form>

  </Modal>

  <button @click="createGenerated" class="btn btn-md btn-primary mb-3 float-right" type="button">
    Generate new keypair
  </button>

  <button @click="show();" class="btn btn-md btn-primary mb-3 mr-2 float-right" type="button">
    Import X509
  </button>

  <p>Configure the keypair used for signing access tokens and id tokens. The public key is published on <a :href="getOidcUrl('/.well-known/jwks.json')">.well-known/jwks.json</a>.</p>

  <table class="table table-hover table-striped">

    <thead>
      <tr>
        <th  style="width: 400px;">kid</th>
        <th>Created</th>
        <th style="width: 40px;">Active</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(record, index) in records" :key="index">
        <th scope="row">{{ record.id }}</th>
        <td>{{ record.created_at }}</td>
        <td>
          <button v-if="!record.active" class="btn btn-success btn-block" @click="activate(record)">Activate</button>
          <button v-else class="btn btn-danger btn-block" @click="deactivate(record)">Deactivate</button>
        </td>
      </tr>
    </tbody>
  </table>

</div>

</template>

<script setup>

import { ref, onMounted } from 'vue'
import { maxios, notify, getOidcUrl } from '@/admin/helpers.js'
import Modal from '@/admin/components/general/Modal.vue'

const errors = ref({});
const wasValidated = ref(false);
const records = ref(null);
const importX509 = ref({
  x509: null, private_key: null
});
const importmodal = ref(null);

onMounted(async () => {
  const response = await maxios.get("api/openidKey");
  records.value = response.data;
});

function show(){
  importmodal.value.show();
}

function reset(){
  importX509.value = {
    x509: null, private_key: null
  };
}

function activate(key) {

  key.active = true;

  maxios.put("api/openidKey/" + key.id, key).then(
    (response) => {
      key = response;
    },
    (e) => {
      // error callback
      notify({
        text: e.response.data.error,
        type: "error",
      });

      key.active = false;
    }
  );
}

function deactivate(key) {

  key.active = false;

  maxios.put("api/openidKey/" + key.id, key).then(
    (response) => {
      key = response;
    },
    (e) => {
      // error callback

      notify({
        text: e.response.data.error,
        type: "error",
      });

      key.active = true;
    }
  );
}

function createGenerated() {
  maxios.post("api/openidKey/createGenerated", {}).then(
    (response) => {
      records.value.push(response.data);
    }
  );
}

function onSubmitImportX509() {
  maxios.post("api/openidKey", importX509.value).then(
    (response) => {
      records.value.push(response.data);

      wasValidated.value = false;

      importX509.value = null;
      importmodal.value.hide();
    },
    (e) => {
      errors.value = e.response.data.errors;
      wasValidated.value = true;
    }
  );
}

</script>