<template>

<div>


<p>Not implemented</p>


</div>
</template>

<script>
export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,

      scope: {},

      scopes: null,

      changedScopes: new Set()

    }
  },

  mounted(){

    this.$http.get(this.$murl('api/oAuthScope')).then(response => {
      this.scopes = response.data;
    }, response => {
      // error callback
    });

  },


  watch: {
    
  },

  methods: {

    addScope(){
      this.$refs.newScopeModal.show();
    },

    changedScope(scope){
      this.changedScopes.add(scope.id);
    },

    onSubmitNewScope(event){
      this.$http.post(this.$murl('api/oAuthScope'),
        this.scope
        ).then(response => {
            this.scopes.push(response.data);
            this.scope = {};

            this.$refs.newScopeModal.hide();

            this.$noty({text: 'We have succesfully saved your new scope.'});
          // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({text: 'We could not save this.', type: 'error'});
        });


      event.preventDefault();

    },

    deleteScope(scope){


      this.$http.delete(this.$murl('api/oAuthScope/' + scope.id)).then(response => {

          this.scopes.splice(this.scopes.indexOf(scope), 1);
          this.$noty({text: 'Deleted the scope'});
            
        }, response => {
          this.$noty({text: 'Could not delete this'});
        }
        );


    },

    onSubmit(event){

      // for (let item of mySet){

      // }

      for(let scope of this.scopes){
        if(this.changedScopes.has(scope.id)){

          this.$http.put(this.$murl('api/oAuthScope/' + scope.id),
        scope
        ).then(response => {

          this.$set(this.errors, scope.id, null);

          this.changedScopes.delete(scope.id);

          this.$noty({text: 'Saved scope&nbsp;<em>' + scope.name + '</em>'});
            
        }, response => {

          //this.errors[scope.id] = response.data;

          this.$set(this.errors, scope.id, response.data)

          this.$noty({text: 'We could not save scope&nbsp;<em>' + scope.name + '</em>.', type: 'error'});
        });



        }
      }

      event.preventDefault();

    }
  }


  
}
</script>