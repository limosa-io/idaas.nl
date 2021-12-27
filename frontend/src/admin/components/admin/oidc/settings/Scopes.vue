<template>

<div>

<b-modal @ok="onSubmitNewScope" ref="newScopeModal" id="newScopeModal" title="New Scope">

  <form class="needs-validation" :class="{'was-validated': wasValidatedNewScope}" v-on:submit="onSubmitNewScope">

    <div class="form-group">
      <label for="newScopeName">Name</label>
      <input type="name" class="form-control" id="newScopeName" aria-describedby="emailHelp" placeholder="For example profile or email" v-model="scope.name">
      <small id="emailHelp" class="form-text text-muted">Name of the scope.</small>
    </div>
    <div class="form-group">
      <label for="newScopeDescription">Description</label>
      <textarea required class="form-control" id="newScopeDescription" rows="2" v-model="scope.description"></textarea>
    </div>

  </form>

</b-modal>


<b-modal id="scopeInformation" ref="scopeInformationModal" title="Claims for scope" ok-only>
  <p>
   This scope can be used to request the following claims.
  </p>
  <p>
    <ul class="list-group">
   <li class="list-group-item" v-for="(c,index) in claims" :key="index">
              {{ c }}
        </li>
        </ul>
   
  </p>
</b-modal>


<form v-if="scopes != null" class="needs-validation" novalidate :class="{'was-validated': wasValidated}" v-on:submit="onSubmit">

<button class="btn btn-sm btn-primary float-right" @click="addScope" type="button">
          Add Scope
  </button>

  <h3 class="c-grey-900">Scopes</h3>

  <p>You may define your own set of scopes. Two scopes are always there: <code class="highlighter-rouge">openid</code> and <code class="highlighter-rouge">online_access</code>. Scopes with <code class="highlighter-rouge">profile</code>, <code class="highlighter-rouge">email</code>, <code class="highlighter-rouge">address</code> and <code class="highlighter-rouge">phone</code> are by default linked to a set of attributes.   </p>

  <table class="table table-hover">

    <thead>
      <tr>
        <th scope="col" class="text-center" style="width: 20px;">#</th>
        <th class="col-md-" scope="col">Scope</th>
        <th scope="col">Description</th>
        <th class="col-md-1" scope="col" style="width: 50px;"></th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(scope, index) in scopes" :key="index">
        <th class="align-middle text-center" scope="row">
          <b-badge @click="showScopeInformation(scope.name)" v-b-tooltip.hover title="This scope is mapped to a set of claims. Click for more information." pill variant="primary" v-if="mapping != null && mapping[scope.name]">{{ index+1 }}</b-badge><span v-else>{{ index+1 }}</span>
        </th>
        <td>
          <input :readonly="scope.system" :class="{'is-invalid': errors[scope.id] && errors[scope.id].errors.name }" @change="changedScope(scope)" class="form-control" type="text" v-model="scope.name" />

        <div v-for="(e, index) in (errors[scope.id]  ? errors[scope.id].errors.name || [] : [])" class="invalid-feedback" :key="index">
              {{ e }}
        </div>

        </td>
        <td>
          <textarea :readonly="scope.system" :class="{'is-invalid': errors[scope.id] && errors[scope.id].errors.description }" @change="changedScope(scope)" class="form-control" id="exampleFormControlTextarea1" rows="1" v-model="scope.description"></textarea>

          <div v-for="(e, index) in (errors[scope.id]  ? errors[scope.id].errors.description || [] : [])" class="invalid-feedback" :key="index">
              {{ e }}
        </div>

        </td>
        <td>

          <button v-if="!scope.system" type="button" class="btn btn-danger" @click="deleteScope(scope)">Delete</button>
        </td>
      </tr>
    </tbody>

  </table>


  <button class="btn btn-md btn-primary" type="submit">
          Save
  </button>


</form>

</div>
</template>

<script>
export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      wasValidatedNewScope: false,
      loading: false,

      scope: {},

      mapping: null,

      //for selected scope
      claims: [],

      scopes: null,

      changedScopes: new Set()

    }
  },

  mounted(){

    this.$http.get(this.$murl('api/oAuthScope')).then(response => {
      
      this.scopes = response.data;
      
      // Load after loading the most important things
      this.$http.get(this.$murl('api/oAuthScope/mapping')).then(response => {
      
        this.mapping = response.data;
        
      }, response => {
        // error callback
      });

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

    showScopeInformation(scope){

      this.claims = this.mapping[scope];

      this.$refs.scopeInformationModal.show();

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