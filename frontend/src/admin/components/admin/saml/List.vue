
<template>
  <Main title="SAML Service Providers">
    <template v-slot:header>
      <b-dropdown text="" right class="float-right">
        <a
          class="dropdown-item"
          :href="$oidcUrl('saml/v2/metadata.xml')"
          target="_blank"
          >SAML Metadata</a
        >
      </b-dropdown>

      <Button class="mr-2" :to="{ name: 'saml.serviceproviders.import' }">
        Import Service Provider
      </Button>

      <Button class="mr-2" :to="{ name: 'saml.serviceproviders.add' }">
        Add
      </Button>

      <Button
        class="btn-secondary float-right mr-2"
        :to="{ name: 'saml.settings.general' }"
      >
        Settings
      </Button>
    </template>

    <template v-slot:body>
      <p>Manage your SAML Service Providers.</p>

      <table class="table table-hover">
        <thead>
          <tr>
            <th scope="col" style="width: 20px">#</th>
            <th scope="col">Entity ID</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(serviceProvider, index) in serviceProviders"
            @click="edit(serviceProvider)"
            :key="index"
          >
            <th scope="row" class="pt-4">{{ index + 1 }}</th>
            <td class="pt-4">{{ serviceProvider.entityid }}</td>
          </tr>

          <tr v-if="!serviceProviders || serviceProviders.length == 0">
            <td colspan="2">There are no SAML applications configured</td>
          </tr>
        </tbody>
      </table>
    </template>
  </Main>
</template>


<script>
import Main from "@/admin/components/general/Main.vue";

export default {
  components: {
    Main
  },
  
  data() {
    return {
      serviceProviders: null,
    };
  },

  methods: {
    edit: function (serviceProvider) {
      this.$router.push({
        name: "saml.serviceproviders.edit",
        params: { id: serviceProvider.id },
      });
    },
  },

  mounted() {
    this.$http.get(this.$murl("api/saml/manage/serviceproviders")).then(
      (response) => {
        this.serviceProviders = response.data;
      },
      (response) => {
        // error callback
      }
    );
  },
};
</script>
