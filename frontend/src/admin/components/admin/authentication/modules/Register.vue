
<template>

<div>

  <b-form-group horizontal :label-cols="3" description="URL to your terms of service document" label="Terms of Service" label-for="module.config.terms_of_service">
    <b-form-input type="url" id="module.config.terms_of_service" v-model="module.config.terms_of_service" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="URL to your privacy policy" label="Privacy Policy" label-for="module.config.privacy_policy">
    <b-form-input type="url" id="module.config.privacy_policy" v-model="module.config.privacy_policy" />
  </b-form-group>

  <b-form-group horizontal :label-cols="3" description="Ask the user to agree with the Terms of Service and/or Privacy Policy."
    label="Ask approval?" label-for="module.config.approval">

    <b-form-checkbox id="module.config.approval" v-model="module.config.approval" :value="true" :unchecked-value="false">
      {{ module.config.approval ? 'Enabled' : 'Disabled' }}
    </b-form-checkbox>

  </b-form-group>

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

      isEnabled: true,

    }
  },

  mounted(){


    this.$http.get(this.$murl('api/settings?namespace=registration')).then(response => {

      this.isEnabled = response.data.allow;

      this.$emit('alert',{
        text: 'You must enable registration before you can use this module.',
        link: '/registration'
      })

    });


    
    
    

  }


  
}
</script>
