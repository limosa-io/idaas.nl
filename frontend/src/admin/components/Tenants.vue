
<template>
<div>

  <b-modal v-model="showModal" id="newTenant" title="New tenant" @ok="onSubmit">
    <form @submit.prevent="onSubmit">
      <div class="form-group">
        <label for="subdomain">Create your tenant on your subdomain</label>
        <input :class="{'is-invalid': errors['subdomain']}" v-model="subdomain" type="text" class="form-control" id="subdomain"
          aria-describedby="subdomainHelp" placeholder="subdomain">
        <small id="subdomainHelp" class="form-text text-muted">Choose a simple name.</small>

        <div v-for="(e,index) in errors.subdomain" class="invalid-feedback" :key="index">
          {{ e }}
        </div>

      </div>
    </form>
  </b-modal>

  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col">Domain</th>
        <th scope="col">Created</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="(tenant,index) of tenants" :key="index">
        <td>{{ tenant.subdomain }}<span class="text-muted">.ice.test</span></td>
        <td>{{ tenant.created_at }}</td>
      </tr>
      <button class="btn btn-primary mt-2" @click="showModal = true">New Tenant</button>
    </tbody>
  </table>

</div>

</template>

<script>
import Vue from 'vue';
import VueResource from 'vue-resource';
Vue.use(VueResource);

export default {

  data() {
    return {
      tenants: null,

      subdomain: null,

      showModal: false,
      errors: {}

    }
  },
  mounted() {

    this.$http.get('/api/tenants', {
      headers: {
        'Authorization': 'Bearer ' + this.getAccessToken()
      }
    }).then(response => {
      this.tenants = response.data;
    });

  },

  methods: {

    getAccessToken() {
      return window.sessionStorage.getItem('access_token');
    },

    onSubmit(event) {
      this.$http.post('/api/tenants', {
        subdomain: this.subdomain
      }, {
        headers: {
          'Authorization': 'Bearer ' + this.getAccessToken()
        }
      }).then(response => {
        
      }, response => {
        this.errors = response.data.errors;
        event.preventDefault();
      })


    }

  }

}
</script>

