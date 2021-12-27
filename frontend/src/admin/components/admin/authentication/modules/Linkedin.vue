
<template>

<div>

  <b-form-group horizontal :label-cols="3" description="Client Id." label="Client Id" label-for="module.config.client_id">
    <b-form-input id="module.config.client_id" v-model.trim="module.config.client_id"></b-form-input>
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="Client Secret" label="Client Secret" label-for="module.config.client_secret">
    <b-form-input id="module.config.client_secret" v-model.trim="module.config.client_secret"></b-form-input>
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="Create a user (allow registration)" label="Create user" label-for="module.config.create_user">

    <b-form-checkbox id="module.config.create_user" v-model="module.config.create_user" :value="true" :unchecked-value="false">
              {{ module.config.create_user ? 'Enabled' : 'Disabled' }}
      </b-form-checkbox>

  </b-form-group>

  

  

  <p v-if="info">
    Use the following callback url in Facebook: {{ info.callback }}
  </p>

</div>

</template>


<script>
export default {

  props: {
    module: null,
    info: null
  },

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,

      type: null,
      types: []

    }
  },

  mounted(){


    
    

  },

  methods: {
    onSubmit(event){

      event.preventDefault();

    }
  }


  
}
</script>
