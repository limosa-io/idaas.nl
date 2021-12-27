
<template>
<div class="container-fluid">

  <b-dropdown text="" right class="float-right">
    <a class="dropdown-item" :href="$oidcUrl('saml/v2/metadata.xml')" target="_blank">SAML Metadata</a>
  </b-dropdown>

  <router-link tag="button" class="btn btn-md btn-primary float-right mr-2" :to="{name: 'saml.serviceproviders.import'}">
    Import Service Provider
  </router-link>

  <router-link tag="button" class="btn btn-md btn-secondary float-right mr-2" :to="{name: 'saml.serviceproviders.add'}">
    Add
  </router-link>

  <router-link tag="button" class="btn btn-md btn-secondary float-right mr-2" :to="{name: 'saml.settings.general'}">
    Settings
  </router-link>

  <h4 class="c-grey-900 mt-1 mb-3">SAML Service Providers</h4>


  <div class="row">
    <div class="col-md-12">

      <div class="bgc-white bd bdrs-3 p-3 mt-2">

        <p>Manage your SAML Service Providers.</p>

        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col" style="width: 20px">#</th>
              <th scope="col">Entity ID</th>
            </tr>
          </thead>

          <tbody>

            <tr v-for="(serviceProvider,index) in serviceProviders" @click="edit(serviceProvider)" :key="index">
              <th scope="row" class="pt-4">{{ index+1 }}</th>
              <td class="pt-4">{{ serviceProvider.entityid }}</td>
            </tr>

            <tr v-if="!serviceProviders || serviceProviders.length == 0">
              <td colspan="2">
                There are no SAML applications configured
              </td>
            </tr>

          </tbody>
        </table>

      </div>
    </div>
  </div>



</div>


</template>


<script>

export default {

  data(){
    return {
      serviceProviders: null
    };
  },

  methods: {
    edit: function(serviceProvider){
      this.$router.push({ name: 'saml.serviceproviders.edit', params: { id: serviceProvider.id }});
    }
  },

  mounted(){
    
    this.$http.get(this.$murl('api/saml/manage/serviceproviders')).then(response => {
      
      this.serviceProviders = response.data;

    }, response => {
      // error callback
    });

  }
  
}
</script>
