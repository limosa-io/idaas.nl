<template>

<div>

<!-- TODO: likely not needed anymore. levels can be set via other settings. However, order must be set somehow ... -->
<b-modal @ok="onSubmitNewLevel" ref="newLevelModal" id="newLevelModal" title="New Scope">

  <form class="needs-validation" v-on:submit="onSubmitNewLevel">

    <div class="form-group">
      <label for="newLevelName">Name</label>
      <input type="name" class="form-control" id="newLevelName" aria-describedby="levelHelp" placeholder="For example urn:bronze" v-model="level.level">
      <small id="levelHelp" class="form-text text-muted">Name of the authentication level.</small>
    </div>

  </form>

</b-modal>


<button class="btn btn-sm btn-primary float-right" @click="add" type="button">
          Add Level
  </button>

<table class="table table-hover">

    <thead>
      <tr>
        <th scope="col">#</th>
        <th scope="col">Level</th>
        <th scope="col">Rank</th>
        <th class="col-md-1" scope="col" style="width: 40px"></th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(level, index) in levels" :key="index">
        <th scope="row">{{ index+1 }}</th>
        <td>
          <input :class="{'is-invalid': errors[level.id] && errors[level.id].errors.value }" @change="changedScope(level)" class="form-control" type="text" v-model="level.level" />

        <div v-for="(e, index) in (errors[level.id]  ? errors[level.id].errors.value || [] : [])" :key="index" class="invalid-feedback">
              {{ e }}
        </div>

        </td>


        <td>
          <input :class="{'is-invalid': errors[level.id] && errors[level.id].errors.ranking }" @change="changedScope(level)" class="form-control" type="text" v-model="level.ranking" />

        <div v-for="(e, index) in (errors[level.id]  ? errors[level.id].errors.ranking || [] : [])" :key="index" class="invalid-feedback">
              {{ e }}
        </div>

        </td>
       
        <td>

          <button type="button" class="btn btn-danger" @click="deleteObject(level)">Delete</button>
        </td>
      </tr>
    </tbody>

  </table>


</div>
</template>

<script>
export default {

  data(){
    return {
      
      errors: {},
      
      wasValidated: false,
      loading: false,

      level: {
        type: 'oidc',
        level: null
      },

      levels: null,

      changedScopes: new Set()

    }
  },

  mounted(){
    
    this.$http.get(this.$murl('authchain/v2/manage/authlevels')).then(response => {
      this.levels = response.data;
    }, response => {
      // error callback
    });

  },


  watch: {
    
  },

  methods: {

    add(){
      this.$refs.newLevelModal.show();
    },

    changedScope(scope){
      this.changedScopes.add(scope.id);
    },

    onSubmitNewLevel(event){

      this.$http.post(this.$murl('authchain/v2/manage/authlevels'),
        this.level
        ).then(response => {
            
            this.$refs.newLevelModal.hide();

            this.levels.push(response.data);
            this.level = {
              'type': 'oidc'
            };
            
            this.$noty({text: 'We have succesfully saved your new scope.'});
          // this.$router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({text: 'We could not save this.', type: 'error'});
        });


      event.preventDefault();

    },

    

    deleteObject(level){

      this.$http.delete(this.$murl('authchain/v2/manage/authlevel/' + level.id)).then(response => {

          this.levels.splice(this.levels.indexOf(level), 1);
          this.$noty({text: 'Deleted the level'});
            
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
          console.error(this.errors);

          this.$noty({text: 'We could not save scope&nbsp;<em>' + scope.name + '</em>.', type: 'error'});
        });



        }
      }

      event.preventDefault();

    }
  }


  
}
</script>