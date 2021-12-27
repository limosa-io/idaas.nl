
<template>

<div>
  <h4 class="c-grey-900 mt-2 mb-3">Authentication Module</h4>
  <div class="row">
    <div class="col-md-12">
      <div v-if="module" class="bgc-white bd bdrs-3 p-3 mt-2">
        <h4 class="c-grey-900 mt-2">{{ module.name }} {{ module.type }}</h4>
      </div>
    </div>
  </div>

  <div class="card border-danger mb-3 mt-3">
    <div class="card-header">Danger Zone</div>
    <div class="card-body text-danger">
      <p class="card-text">Clicking the button below will delete this module. This cannot be undone.</p>
      <button type="button" class="btn btn-danger" @click="deleteModule(module)">Delete</button>
    </div>
  </div>
</div>

</template>


<script>

import password from './modules/Password.vue';
import facebook from './modules/Facebook.vue';

export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,

      type: null,
      types: [],

      module: null,

      showAdvanced: false

    }
  },

  mounted(){

    this.$http.get(this.$murl('authchain/v2/manage/modules/' + this.$route.params.module_id)).then(response => {
      
      var keys = Object.keys(this.$options.components);

      this.module = response.data;

      this.module.config = this.module.config || {};

    }, response => {
      // error callback
    });

  },

  methods: {

    deleteModule(module){
      this.$http.delete(this.$murl('authchain/v2/manage/modules/' + module.id)).then(response => {

        this.$noty({text: 'We have succesfully DELETED your module.'});

        this.$router.go(-1);
      });
    },

    onSubmit(event){

      this.$http.put(this.$murl('authchain/v2/manage/modules/' + this.$route.params.module_id),
        this.module
        ).then(response => {
            
            this.$noty({text: 'We have succesfully saved your module.'});
          // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({text: 'We could not save this.', type: 'error'});
        });
        
      event.preventDefault();

    }
  },

  components : {
    'password': password,
    'facebook': facebook
  }


  
}
</script>
