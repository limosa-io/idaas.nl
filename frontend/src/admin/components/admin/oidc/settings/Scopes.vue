<template>
  <div>

    <Modal @ok="onSubmitNewScope" ref="newScopeModal" id="newScopeModal" title="New Scope">

      <form class="needs-validation" :class="{ 'was-validated': wasValidatedNewScope }" v-on:submit="onSubmitNewScope">

        <div class="form-group">
          <label for="newScopeName">Name</label>
          <input type="name" class="form-control" id="newScopeName" aria-describedby="emailHelp"
            placeholder="For example profile or email" v-model="scope.name">
          <small id="emailHelp" class="form-text text-muted">Name of the scope.</small>
        </div>
        <div class="form-group">
          <label for="newScopeDescription">Description</label>
          <textarea required class="form-control" id="newScopeDescription" rows="2"
            v-model="scope.description"></textarea>
        </div>

      </form>

    </Modal>


    <Modal id="scopeInformation" ref="scopeInformationModal" title="Claims for scope" ok-only>
      <p>
        This scope can be used to request the following claims.
      </p>
      <p>
      <ul class="list-group">
        <li class="list-group-item" v-for="(c, index) in claims" :key="index">
          {{ c }}
        </li>
      </ul>

      </p>
    </Modal>

    <form v-if="scopes != null" class="needs-validation" novalidate :class="{ 'was-validated': wasValidated }"
      v-on:submit.prevent="onSubmit">

      <button class="btn btn-sm btn-primary float-right" @click="addScope" type="button">
        Add Scope
      </button>

      <h3 class="c-grey-900">Scopes</h3>

      <p>You may define your own set of scopes. Two scopes are always there: <code class="highlighter-rouge">openid</code>
        and <code class="highlighter-rouge">online_access</code>. Scopes with <code
          class="highlighter-rouge">profile</code>, <code class="highlighter-rouge">email</code>, <code
          class="highlighter-rouge">address</code> and <code class="highlighter-rouge">phone</code> are by default linked
        to a set of attributes. </p>

      <table class="table table-hover">

        <thead>
          <tr>
            <th scope="col" class="text-center" style="width: 20px;">#</th>
            <th class="col-md-" scope="col">Scope</th>
            <th scope="col">Description</th>
            <th class="col-md-1" scope="col" style="width: 50px;"></th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(scope, index) in scopes" :key="index">
            <th class="align-middle text-center" scope="row">
              <span @click="showScopeInformation(scope.name)"
                title="This scope is mapped to a set of claims. Click for more information." pill variant="primary"
                v-if="mapping != null && mapping[scope.name]">{{ index + 1 }}</span><span v-else>{{ index + 1 }}</span>
            </th>
            <td>
              <input :readonly="scope.system" :class="{ 'is-invalid': errors[scope.id] && errors[scope.id].errors.name }"
                @change="changedScope(scope)" class="form-control" type="text" v-model="scope.name" />

              <div v-for="(e, index) in (errors[scope.id] ? errors[scope.id].errors.name || [] : [])"
                class="invalid-feedback" :key="index">
                {{ e }}
              </div>

            </td>
            <td>
              <textarea :readonly="scope.system"
                :class="{ 'is-invalid': errors[scope.id] && errors[scope.id].errors.description }"
                @change="changedScope(scope)" class="form-control" id="exampleFormControlTextarea1" rows="1"
                v-model="scope.description"></textarea>

              <div v-for="(e, index) in (errors[scope.id] ? errors[scope.id].errors.description || [] : [])"
                class="invalid-feedback" :key="index">
                {{ e }}
              </div>

            </td>
            <td>

              <button v-if="!scope.system" type="button" class="btn btn-danger"
                @click="deleteScope(scope)">Delete</button>
            </td>
          </tr>
        </tbody>

      </table>


      <button class="btn btn-md btn-primary" type="submit">
        Save
      </button>


    </form>

  </div>
</template>

<script setup>

import { ref, onMounted, getCurrentInstance } from 'vue';
import { maxios, notify } from '@/admin/helpers.js';
import Modal from '@/admin/components/general/Modal.vue'

const vue = getCurrentInstance();

const errors = ref({});
const wasValidated = ref(false);
const wasValidatedNewScope = ref(false);
const loading = ref(false);
const scope = ref({});
const mapping = ref(null);
const claims = ref([]);
const scopes = ref(null);
const changedScopes = ref(new Set());

const newScopeModal = ref(null);
const scopeInformationModal = ref(null);

onMounted(() => {

  maxios.get('api/oAuthScope').then(response => {

    console.log(response.data);
    scopes.value = response.data;

    // Load after loading the most important things
    maxios.get('api/oAuthScope/mapping').then(response => {

      mapping.value = response.data;

    }, response => {
      // error callback
    });

  }, response => {
    // error callback
  });

});

function addScope() {
  newScopeModal.value.show();
}

function showScopeInformation(scope) {

  claims.value = mapping.value[scope];

  scopeInformationModal.value.show();

}

function changedScope(scope) {
  changedScopes.value.add(scope.id);
}

function onSubmitNewScope(event) {

  maxios.post('api/oAuthScope',
    scope.value
  ).then(response => {

    scopes.value.push(response.data);
    scope.value = {};

    vue.proxy.$refs.newScopeModal.hide();

    notify({ text: 'We have succesfully saved your new scope.' });

  }, response => {
    errors.value = response.data.errors;
    wasValidatedNewScope.value = true;

    notify({ text: 'We could not save this.', type: 'error' });
  });
}

function onSubmit(event) {

  // for (let item of mySet){

  // }

  for (let scope of scopes.value) {
    if (changedScopes.value.has(scope.id)) {

      maxios.put('api/oAuthScope/' + scope.id,
        scope
      ).then(response => {

        errors.value[scope.id] = null;

        changedScopes.value.delete(scope.id);

        notify({ text: 'Saved scope&nbsp;<em>' + scope.name + '</em>' });

      }, e => {

        errors.value[scope.id] = e.response.data;

        notify({ text: 'We could not save scope&nbsp;<em>' + scope.name + '</em>.', type: 'error' });
      });
    }
  }
}

function deleteScope(scope) {

  laxios.delete('api/oAuthScope/' + scope.id).then(response => {
    scopes.value.splice(scopes.value.indexOf(scope), 1);
    notify({ text: 'Deleted the scope' });

  }, response => {
    notify({ text: 'Could not delete this' });
  }
  );

}

</script>