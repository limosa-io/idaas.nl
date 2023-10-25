
<template>
  <MainTemplate title="SAML Service Providers">
    <template v-slot:header>
        <a
          class="btn btn-md btn-secondary float-right mr-2"
          :href="getOidcUrl('/saml/v2/metadata.xml')"
          target="_blank"
          >SAML Metadata</a
        >

      <MenuButton class="mr-2" :to="{ name: 'saml.serviceproviders.import' }">
        Import Service Provider
      </MenuButton>

      <MenuButton class="mr-2" :to="{ name: 'saml.serviceproviders.add' }">
        Add
      </MenuButton>

      <MenuButton
        class="btn-secondary float-right mr-2"
        :to="{ name: 'saml.settings.general' }"
      >
        Settings
      </MenuButton>
    </template>

    <template v-slot:body>
      <p>Manage your SAML Service Providers.</p>

      <table class="table table-hover" v-if="serviceProviders != null">
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
  </MainTemplate>
</template>


<script setup>
import MainTemplate from "@/admin/components/general/MainTemplate.vue";
import {ref, onMounted, getCurrentInstance} from 'vue'
import {maxios, getOidcUrl} from '@/admin/helpers.js'
import { useRouter } from "vue-router4";

const router = useRouter();
const vue = getCurrentInstance();
const serviceProviders = ref(null);

onMounted(async () => {
  const response = await maxios.get("api/saml/manage/serviceproviders");
  serviceProviders.value = response.data;
});

function edit(serviceProvider) {
  router.push({
    name: "saml.serviceproviders.edit",
    params: { id: serviceProvider.id },
  });
}

</script>
