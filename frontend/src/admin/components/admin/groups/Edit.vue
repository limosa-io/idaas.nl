<template>
  <MainTemplate title="Edit Group">
    <template v-if="group" v-slot:body>
      <form
        v-if="group"
        class="needs-validation"
        novalidate
        :class="{ 'was-validated': wasValidated }"
        v-on:submit.prevent="onSubmit"
      >
        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="name">Group Name</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:name'
                ]
                  ? true
                  : false,
              }"
              v-model="
                group['urn:ietf:params:scim:schemas:core:2.0:Group'].name
              "
              required
              type="text"
              class="form-control"
              id="name"
              placeholder=""
            />

            <div
              v-if="errors['urn:ietf:params:scim:schemas:core:2.0:Group:name']"
              class="invalid-feedback"
            >
              This is a required field and must be minimal 3 characters long.
            </div>
          </div>
        </div>

        <div class="form-row form-group">
          <div class="col-md-3">
            <label for="displayName">Display Name</label>
          </div>
          <div class="col">
            <input
              :class="{
                'is-invalid': errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:displayName'
                ]
                  ? true
                  : false,
              }"
              v-model="
                group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName
              "
              required
              type="text"
              class="form-control"
              id="displayName"
              placeholder=""
            />

            <div
              v-if="
                errors[
                  'urn:ietf:params:scim:schemas:core:2.0:Group:displayName'
                ]
              "
              class="invalid-feedback"
            >
              This is a required field and must be minimal 3 characters long.
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">
          Save Group
        </button>
      </form>
    </template>

    <template v-slot:footer>
      <Danger body="Clicking the button below will delete this group. This cannot be undone.">
          <button
            type="button"
            class="btn btn-danger"
            @click="deleteObject(group)"
          >
            Delete
          </button>
      </Danger>
    </template></MainTemplate>
</template>


<script setup>
import {ref, onMounted, getCurrentInstance, watch} from 'vue'
import {maxios} from '@/admin/helpers.js'
import { notify } from '../../../helpers';
import {useRouter, useRoute} from 'vue-router4';

const group = ref(null);
const errors = ref({});
const wasValidated = ref(false);
const router = useRouter();
const route = useRoute();

onMounted(() => {
    maxios.get('api/scim/v2/Groups/' + route.params.group_id)
        .then(response => {
            group.value = response.data;
        });
});

function deleteObject(object) {
    maxios.delete('api/scim/v2/Groups/' + object.id)
        .then(response => {
            notify({
                text: 'We have succesfully deleted this group.',
            });

            router.replace({
                name: 'groups.list',
            });
        }, response => {
            // error callback
        });
}

function onSubmit() {
    maxios.put('api/scim/v2/Groups/' + route.params.group_id, JSON.stringify(group.value), {
        headers: {
            'content-type': 'application/scim+json',
        },
    }).then(response => {
        notify({
            text: 'We have succesfully saved this group.',
        });
        errors.value = {};
    }, e => {
        notify({
            text: 'There were some errors during saving.',
        });
        errors.value = e.response.data.errors;
    });
}

</script>
