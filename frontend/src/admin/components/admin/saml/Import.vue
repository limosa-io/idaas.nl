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

<script>
export default {

    data(){
        return {
            loading: false,

            error: null,

            metadata: null,


        }
    },

    methods: {
        onSubmit(){

            this.$http.post(this.$murl('api/saml/manage/importMetadata'),
        {
            metadata: this.metadata
        }
        ).then(response => {

          this.$noty({text: 'We have succesfully imported your new SAML Service Provider.'});
          this.$router.replace({ name: 'saml.serviceproviders.edit', params: { id: response.data.id }});

        }, response => {

          this.error = response.data.error;
          
        });

            

        }
    }

}
</script>

<style>

</style>
