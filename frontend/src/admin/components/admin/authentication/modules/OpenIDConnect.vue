
<template>

<div>

  <b-form-group horizontal :label-cols="3" description="The client_id." label="Client ID" label-for="module.config.client_id">
    <b-form-input type="text" id="module.config.client_id" v-model="module.config.client_id" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="The client_secret." label="Client secret" label-for="module.config.client_secret">
    <b-form-input type="text" id="module.config.client_secret" v-model="module.config.client_secret" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="Scopes." label="scopes" label-for="module.config.scopes">
    <b-form-input type="text" id="module.config.scopes" v-model="module.config.scopes" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="Userinfo endpoint." label="userinfo" label-for="module.config.userinfo_endpoint">
    <b-form-input type="text" id="module.config.userinfo_endpoint" v-model="module.config.userinfo_endpoint" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="authorization_endpoint." label="authorization_endpoint"
    label-for="module.config.authorization_endpoint">
    <b-form-input type="text" id="module.config.authorization_endpoint" v-model="module.config.authorization_endpoint" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="token_endpoint." label="token_endpoint" label-for="module.config.token_endpoint">
    <b-form-input type="text" id="module.config.token_endpoint" v-model="module.config.token_endpoint" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="color." label="color" label-for="module.config.button_color">
    <b-form-input type="text" id="module.config.button_color" v-model="module.config.button_color" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="text." label="text" label-for="module.config.button_text">
    <b-form-input type="text" id="module.config.button_text" v-model="module.config.button_text" />
  </b-form-group>

  <p v-if="info">
    Use the following redirect_uri: {{ info.callback }}
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
      types: [],

      templates: {},

    }
  },

  mounted(){


    this.$http.get(this.$murl('api/mail_template')).then(response => {

      this.templates = response.data;

    }, response => {
      // error callback
    });
    
    

  },

  methods: {
    onSubmit(event){

      event.preventDefault();

    }
  }


  
}
</script>
