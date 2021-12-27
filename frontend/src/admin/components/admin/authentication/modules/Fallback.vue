
<template>
    
<form class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">

  <h3 class="c-grey-900">Fallback</h3>

  <div class="form-group">
    
    <p>test</p>
    
  </div>


</form>


</template>


<script>
export default {

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
