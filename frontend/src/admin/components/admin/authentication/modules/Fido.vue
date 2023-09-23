
<template>
  <div>
    <p>This module currently has no settings.</p>
  </div>
</template>
  
  <script>
  export default {
    props: {
      module: null,
      info: null
    },
  
    data() {
      return {
        templates: {}
      };
    },
  
    mounted() {
      
     
    },
  
    methods: {
      mounted(){
        this.$http.get(this.$murl('api/mail_template')).then(response => {

          this.templates = response.data;

          }, response => {
          // error callback
        });
      },

      verify(){
        this.request({

        }).then(response => {

        });
      }

    }
  };
  </script>
  