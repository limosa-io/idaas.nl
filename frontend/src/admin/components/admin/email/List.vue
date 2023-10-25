
<template>
  <div class="bgc-white bd bdrs-3 p-3 mt-2">

    <Modal @ok="onSubmitNewTemplate" ref="newModel" title="New E-mail Template">
      <form class="needs-validation" @submit.prevent="onSubmitNewTemplate">

        <div class="form-row mb-3">

          <div class="col-md-3">
            <label for="emailTemplate.name">Name</label>
          </div>
          <div class="col">
            <input id="emailTemplate.name" type="text" class="form-control" v-model="emailTemplate.name" />
          </div>
        </div>

        <div v-for="(e, index) in errors" class="alert alert-danger" role="alert" :key="index">
          {{ e[0] }}
        </div>

      </form>

    </Modal>

    <!-- <h4 class="c-grey-900 mt-2">E-mail templates</h4> -->

    <button @click="newModel.show();" type="button" class="btn btn-primary btn-sm float-right">Add E-mail
      Template</button>
    <p>Manage your e-mail templates.</p>

    <table class="table table-hover table-striped">

      <thead>
        <tr>
          <th scope="col">Name</th>
        </tr>
      </thead>

      <tbody>

        <tr :key="index" v-for="(emailTemplate, index) in objects" @click="edit(emailTemplate)"
          :class="{ 'table-active': emailTemplate.default }" 
          :title="emailTemplate.default ? 'This is the default email template and cannot be deleted.' : null">
          <td>{{ emailTemplate.name }}</td>
        </tr>

      </tbody>
    </table>
  </div>
</template>

<script setup>

import { ref, onMounted } from 'vue';
import { maxios } from "@/admin/helpers.js";

import Modal from '@/admin/components/general/Modal.vue';
import { notify } from '../../../helpers';
import { useRouter } from 'vue-router4';

const router = useRouter();
const objects = ref([]);
const errors = ref([]);
const emailTemplate = ref({
  name: null,
  body: 'This is for most clients',
  body_plain: 'Text only email is used as a fallback mechannism for old clients',
  subject: 'New Template'

});

const newModel = ref(null);

function edit(object) {

  router.push({ name: 'emails.edit', params: { object_id: object.id } });

}

function onSubmitNewTemplate() {

  maxios.post('api/mail_template', emailTemplate.value).then(response => {

    objects.value.push(response.data);

    notify({ text: 'Succesfully created a new emailtemplate' });

    router.push({ name: 'emails.edit', params: { object_id: response.data.id } });

    newModel.hide();

  }, responseError => {

    errors.value = responseError.response.data.errors;

  });
}

onMounted(() => {
  maxios.get('api/mail_template').then(response => {
    objects.value = response.data;
  });
});

</script>
