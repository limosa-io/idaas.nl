<template>
    
    <div class="container-fluid">

<h4 class="c-grey-900 mt-2">Import from metadata</h4>

<div class="bgc-white bd bdrs-3 p-3 mt-3">

    <p>Copy and paste your SAML service provider metadata in the box below.</p>

    <form class="needs-validation" novalidate v-on:submit.prevent="onSubmit">

    <textarea v-model="metadata" class="form-control" :class="{'is-invalid': error != null}" rows="10">

    </textarea>

    <div class="invalid-feedback">
        {{ error }}
    </div>

    <button type="submit" class="btn btn-primary mt-3" :disabled="loading">Import</button>

    </form>



</div>
    </div>
</template>

<script setup>

import {ref, getCurrentInstance} from 'vue';
import {maxios, notify} from '@/admin/helpers.js';
import { useRouter } from 'vue-router4';

const router = useRouter();
const loading = ref(false);
const error = ref(null);
const metadata = ref(null);

function onSubmit(){

    loading.value = true;
    error.value = null;

    maxios.post('api/saml/manage/importMetadata', {
        metadata: metadata.value
    }).then(response => {

        notify({text: 'We have succesfully imported your new SAML Service Provider.'});
        router.replace({ name: 'saml.serviceproviders.edit', params: { id: response.data.id }});

    }, e => {
        error.value = e.response.data.error;
        loading.value = false;
        
    });

}

</script>

<style>

</style>
