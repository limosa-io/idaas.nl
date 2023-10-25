<template>

<div>


<p>Not implemented</p>


</div>
</template>

<script setup>

import { ref, reactive, watch, computed, getCurrentInstance, onMounted } from 'vue'
import { maxios} from '@/admin/helpers.js'
import { notify } from '../../../../helpers';

const errors = ref(null);
const wasValidated = ref(false);
const loading = ref(false);
const scope = ref({})
const scopes = ref(null);
const changedScopes = ref(new Set());

const newScopeModal = ref(null);

onMounted(async () => {
  const response = await maxios.get("api/oAuthScope");
  scopes.value = response.data;
});

function addScope(){
  newScopeModal.value.show();
}

function changedScope(scope){
  changedScopes.value.add(scope.id);
}

function onSubmitNewScope(event){
  maxios.post("api/oAuthScope", scope.value).then(
    (response) => {
      scopes.value.push(response.data);
      scope.value = {};

      newScopeModal.value.hide();

      notify({
        text: "We have succesfully saved your new scope.",
      });
    },
    (response) => {
      errors.value = response.data.errors;
      wasValidated.value = true;

      notify({
        text: "We could not save this.",
        type: "error",
      });
    }
  );

  event.preventDefault();
}

function deleteScope(scope){
  maxios.delete("api/oAuthScope/" + scope.id).then(
    (response) => {
      scopes.value.splice(scopes.value.indexOf(scope), 1);
      notify({
        text: "Deleted the scope",
      });
    },
    (response) => {
      notify({
        text: "Could not delete this",
      });
    }
  );
}

function onSubmit(event) {
  for (let scope of scopes.value) {
    if (changedScopes.value.has(scope.id)) {
      maxios.put("api/oAuthScope/" + scope.id, scope).then(
        (response) => {
          errors.value[scope.id] = null;

          changedScopes.value.delete(scope.id);

          notify({
            text: "Saved scope&nbsp;<em>" + scope.name + "</em>",
          });
        },
        (response) => {
          errors.value[scope.id] = response.data;

          notify({
            text: "We could not save scope&nbsp;<em>" + scope.name + "</em>.",
            type: "error",
          });
        }
      );
    }
  }

  event.preventDefault();
}
</script>