
<template>
    

<div>

<ul class="nav nav-tabs">
  <li class="nav-item">
    <router-link :to="{name: 'saml.settings.general'}" class="nav-link" :exact="true" active-class="active">
      General
    </router-link>
  </li>

</ul>  

<div class="bgc-white bd bdrs-3 p-3">
  <router-view></router-view>
</div>

</div>


</template>


<script>
export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,

      serviceProvider: {
          entityId: null
      }

    }
  },

  methods: {
    
  }


  
}
</script>
