<template>
  <div>

    <!-- TODO: likely not needed anymore. levels can be set via other settings. However, order must be set somehow ... -->
    <Modal @ok="onSubmitNewLevel" ref="newLevelModal" id="newLevelModal" title="New Scope">

      <form class="needs-validation" v-on:submit="onSubmitNewLevel">

        <div class="form-group">
          <label for="newLevelName">Name</label>
          <input type="name" class="form-control" id="newLevelName" aria-describedby="levelHelp"
            placeholder="For example urn:bronze" v-model="level.level">
          <small id="levelHelp" class="form-text text-muted">Name of the authentication level.</small>
        </div>

      </form>

    </Modal>


    <button class="btn btn-sm btn-primary float-right" @click="add" type="button">
      Add Level
    </button>

    <table class="table table-hover">

      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Level</th>
          <th scope="col">Rank</th>
          <th class="col-md-1" scope="col" style="width: 40px"></th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(level, index) in levels" :key="index">
          <th scope="row">{{ index + 1 }}</th>
          <td>
            <input :class="{ 'is-invalid': errors[level.id] && errors[level.id].errors.value }"
              @change="changedScope(level)" class="form-control" type="text" v-model="level.level" />

            <div v-for="(e, index) in (errors[level.id] ? errors[level.id].errors.value || [] : [])" :key="index"
              class="invalid-feedback">
              {{ e }}
            </div>

          </td>


          <td>
            <input :class="{ 'is-invalid': errors[level.id] && errors[level.id].errors.ranking }"
              @change="changedScope(level)" class="form-control" type="text" v-model="level.ranking" />

            <div v-for="(e, index) in (errors[level.id] ? errors[level.id].errors.ranking || [] : [])" :key="index"
              class="invalid-feedback">
              {{ e }}
            </div>

          </td>

          <td>

            <button type="button" class="btn btn-danger" @click="deleteObject(level)">Delete</button>
          </td>
        </tr>
      </tbody>

    </table>


  </div>
</template>

<script setup>
import { ref, reactive, watch, computed, getCurrentInstance, onMounted } from 'vue'
import { maxios, notify } from '@/admin/helpers.js'
import Modal from '@/admin/components/general/Modal.vue'

const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const level = ref({});
const levels = ref(null);
const changedScopes = ref(new Set());

const vue = getCurrentInstance();

const newLevelModal = ref(null);

function add() {
  newLevelModal.value.show();
}

function changedScope(scope) {
  changedScopes.value.add(scope.id);
}

function onSubmitNewLevel(event) {

  maxios.post('authchain/v2/manage/authlevels',
    level.value
  ).then(response => {

    newLevelModal.value.hide();

    levels.value.push(response.data);
    level.value = {
      'type': 'oidc'
    };

    notify({ text: 'We have succesfully saved your new scope.' });

  }, response => {
    errors.value = response.data.errors;
    wasValidated.value = true;

    notify({ text: 'We could not save this.', type: 'error' });
  });


}

function deleteObject(level) {

  maxios.delete('authchain/v2/manage/authlevel/' + level.id).then(response => {

    levels.value.splice(levels.value.indexOf(level), 1);
    notify({ text: 'Deleted the level' });

  }, response => {
    notify({ text: 'Could not delete this' });
  }
  );
}

onMounted(() => {
  maxios.get('authchain/v2/manage/authlevels').then(response => {
    levels.value = response.data;
  });
});

</script>