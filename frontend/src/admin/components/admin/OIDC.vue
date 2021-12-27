
<template>
  <router-view></router-view>
</template>


<script>
export default {

  data(){
    return {
      clients: null
    };
  },

  mounted(){
    
    this.$http.get(this.$oidcUrl('oauth/connect/register')).then(response => {
      
      this.clients = response.data;

    }, response => {
      // error callback
    });

  }
  
}
</script>
