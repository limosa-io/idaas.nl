
<template>
<div>
  <h3 class="c-grey-900">Add a new authentication module</h3>

  <div class="list-group">
    <a href="#" v-for="(value,t) in types" :key="t" @click.prevent="createModule(t)" class="list-group-item list-group-item-action">{{ value }}</a>
  </div>

</div>
</template>


<script setup>

import { ref, defineProps, onMounted } from 'vue';
import {maxios} from "@/admin/helpers.js";
import { notify } from '../../../helpers';
import { useRouter } from 'vue-router4';

const router = useRouter();
const errors = ref({});
const wasValidated = ref(false);
const loading = ref(false);
const type = ref(null);
const types = ref([]);
const module = ref({
  type: null,
  name: null,
  enabled: false,
  skippable: true
});

onMounted(() => {

  maxios.get('authchain/v2/manage/types').then(response => {

    types.value = response.data;

  });
});

function createModule(t) {

  maxios.post('authchain/v2/manage/modules', {
    type: t,
    enabled: false,
    skippable: true,
    hide_if_not_requested: false
  }).then(response => {

    notify({
      text: 'We have succesfully saved your new module.'
    });

    router.replace({
      name: 'authentication.edit',
      params: {
        module_id: response.data.id
      }
    });

  }, response => {
    
    
  });

}

</script>
