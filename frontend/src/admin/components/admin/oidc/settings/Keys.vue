<template>

<div>
  <b-modal @ok="onSubmitImportX509" ref="importX509" id="importX509" title="Import X509" v-if="importx509">

    <form novalidate :class="{'was-validated': wasValidated}" class="needs-validation" v-on:submit="onSubmitImportX509">

      <div class="form-group row">
        <label for="x509" class="col-sm-2 col-form-label">Certificate</label>
        <div class="col-sm-10">
          <textarea :class="{'is-invalid': errors['x509']}" required class="form-control" rows="4" id="x509" v-model="importx509.x509"></textarea>

          <div v-for="(e ,index) in errors['x509']" class="invalid-feedback" :key="index">
            {{ e }}
          </div>

        </div>
      </div>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-2 col-form-label">Private Key</label>
        <div class="col-sm-10">
          <textarea :class="{'is-invalid': errors['private_key']}" class="form-control" rows="4" v-model="importx509.private_key"></textarea>
          <div v-for="(e, index) in errors['private_key']" class="invalid-feedback" :key="index">
            {{ e }}
          </div>
        </div>
      </div>

    </form>

  </b-modal>

  <button @click="createGenerated" class="btn btn-md btn-primary mb-3 float-right" type="button">
    Generate new keypair
  </button>

  <button @click="$refs.importX509.show();" class="btn btn-md btn-primary mb-3 mr-2 float-right" type="button">
    Import X509
  </button>

  <p>Configure the keypair used for signing access tokens and id tokens. The public key is published on <a :href="$oidcUrl('.well-known/jwks.json')">.well-known/jwks.json</a>.</p>

  <table class="table table-hover table-striped">

    <thead>
      <tr>
        <th  style="width: 400px;">kid</th>
        <th>Created</th>
        <th style="width: 40px;">Active</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="(record, index) in records" :key="index">
        <th scope="row">{{ record.id }}</th>
        <td>{{ record.created_at }}</td>
        <td>
          <button v-if="!record.active" class="btn btn-success btn-block" @click="activate(record)">Activate</button>
          <button v-else class="btn btn-danger btn-block" @click="deactivate(record)">Deactivate</button>
        </td>
      </tr>
    </tbody>
  </table>

</div>

</template>

<script>

export default {

  data() {
    return {

      errors: {},

      wasValidated: false,
      loading: false,

      records: null,

      importx509: null

    }
  },
  

  mounted() {

    this.reset();

    this.$http.get(this.$murl('api/openidKey')).then(response => {
        console.log('succes!');
      this.records = response.data;
      //console.log(JSON.stringify(this.scopes));

    }, response => {
      // error callback
      
    });

  },

  watch: {

  },

  methods: {

    reset(){
      this.importx509 = {
        x509: null, private_key: null
      };
    },

    activate(key) {

      key.active = true;

      this.$http.put(this.$murl('api/openidKey/' + key.id), key).then(response => {

        key = response;
      }, response => {
        this.$noty({text: response.data.error, type: 'error'});
        key.active = false;
      });


    },

    deactivate(key) {

      key.active = false;

      this.$http.put(this.$murl('api/openidKey/' + key.id), key).then(response => {

        key = response;
      }, response => {
        // error callback

      this.$noty({text: response.data.error, type: 'error'});
      });

    },

    createGenerated(){

        this.$http.post(this.$murl('api/openidKey/createGenerated'), {}).then(response => {

            this.records.push(response.data);
        
      }, response => {
        // error callback
      });

    },

    onSubmitImportX509(bvEvt){

      this.$http.post(this.$murl('api/openidKey'), this.importx509).then(response => {

            this.records.push(response.data);

            this.wasValidated = false;

            this.$refs.importX509.hide();
            this.reset();
        
      }, response => {
        this.errors = response.data.errors;
        this.wasValidated = true;
      });

      bvEvt.preventDefault();


    }

  }

}

</script>