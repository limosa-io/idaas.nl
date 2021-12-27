
<template>

<div class="bgc-white bd bdrs-3 p-3 mt-2">

  <b-modal @ok="onSubmitNewTemplate" ref="newModel" id="newModel" title="New E-mail Template">

    <form class="needs-validation" v-on:submit="onSubmitNewTemplate">

      <div class="form-row mb-3">

        <div class="col-md-3">
          <label for="emailTemplate.name">Name</label>
        </div>
        <div class="col">
            <input id="emailTemplate.name" type="text" class="form-control" v-model="emailTemplate.name" />
        </div> 
      </div>
            
      <template v-for="(e, index) in errors">
        <div class="alert alert-danger" role="alert" :key="index">
          {{ e[0] }}
        </div>
      </template>

    </form>

  </b-modal>
  
  <!-- <h4 class="c-grey-900 mt-2">E-mail templates</h4> -->

  <button @click="$refs.newModel.show();" type="button" class="btn btn-primary btn-sm float-right">Add E-mail Template</button>
  <p>Manage your e-mail templates.</p>

  <table class="table table-hover table-striped">

    <thead>
      <tr>
        <th scope="col">Name</th>
      </tr>
    </thead>

    <tbody>

      <tr :key="index" v-for="(emailTemplate,index) in objects" @click="edit(emailTemplate)" :class="{'table-active': emailTemplate.default}" v-b-tooltip.hover :title="emailTemplate.default ? 'This is the default email template and cannot be deleted.': null">
        <td>{{ emailTemplate.name }}</td>
      </tr>

    </tbody>
  </table>
</div>

</template>

<script>

export default {

  data(){
    return {
      objects: null,

      errors: [],

        emailTemplate: {
            name: null,
            body: 'This is for most clients',
            body_plain: 'Text only email is used as a fallback mechannism for old clients',
            subject: 'New Template'

        }

    };
  },

  methods: {
    edit: function(object){

      this.$router.push({ name: 'emails.edit', params: { object_id: object.id }});

    },

    onSubmitNewTemplate(event){
      
        this.$http.post(this.$murl('api/mail_template'), this.emailTemplate).then(response => {

            this.objects.push(response.data);

            this.$noty({text: 'Succesfully created a new emailtemplate'});

            this.$router.push({ name: 'emails.edit', params: { object_id: response.data.id }});

            this.$refs.newModel.hide();

        }, responseError => {

            this.errors = responseError.data.errors;

        });

        event.preventDefault();

    }

  },

  mounted(){
    
    this.$http.get(this.$murl('api/mail_template')).then(response => {
      
      this.objects = response.data;

    }, response => {
      // error callback
    });

  }
  
}

</script>
