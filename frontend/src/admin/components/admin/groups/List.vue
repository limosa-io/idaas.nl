
<template>
  <MainTemplate title="Groups">
    <template v-slot:header>
      <MenuButton to="/groups/add"> Add Group </MenuButton>
    </template>

    <template v-slot:body v-if="loaded">

      <div class="d-flex flex-row justify-content-between">
        <div>
          <form class="form-inline">
            <label class="" for="entries">Show</label>
            <select class="form-control form-control-sm ml-2 mr-2" id="entries" v-model="itemsPerPage">
              <option value="20">20</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            entries
          </form>
        </div>
        <form class="form-inline" v-on:submit.prevent="onSubmit">
          <label class="sr-only" for="search.email">Name</label>
          <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="search.email" v-model="search.email"
            placeholder="" />

          <button type="submit" class="btn btn-primary mb-2 btn-sm">
            Search
          </button>

          <button v-if="checkedUsers && checkedUsers.length > 0" @click="deleteSelected()" type="button"
            class="btn btn-danger ml-2 mb-2 btn-sm">
            Deleted selected
          </button>
        </form>
      </div>

      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th scope="col">Name</th>
            <th scope="col">Display Name</th>
            <th style="width: 30px; text-align: center">
              <input @click.stop="selectAll" class="" type="checkbox" />
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) of users" @click="edit(user)" :key="index">
            <td style="width: 30%">
              {{ user["urn:ietf:params:scim:schemas:core:2.0:Group"].name }}
            </td>
            <td>
              {{
                user["urn:ietf:params:scim:schemas:core:2.0:Group"].displayName
              }}
            </td>
            <td @click.stop class="text-center">
              <input :value="user.id" v-model="checkedUsers" @click.stop class="" type="checkbox" />
            </td>
          </tr>

          <tr v-if="!users || users.length == 0">
            <td class="p-4 pl-0" colspan="4">There are no groups</td>
          </tr>
        </tbody>
      </table>

      <div class="d-flex flex-row align-items-center justify-content-between">
        <div>
          Showing {{ startIndex }} to
          {{ startIndex + parseInt(itemsPerPage) - 1 }} of
          {{ totalResults }} entries
        </div>
        <b-pagination v-if="totalResults > itemsPerPage" @input="changePage" size="md" :total-rows="totalResults"
          v-model="currentPage" :per-page="itemsPerPage" class=""></b-pagination>
      </div>
    </template>
  </MainTemplate>
</template>


<script setup>
import { ref, onMounted , getCurrentInstance, watch} from 'vue'
import { useRouter } from 'vue-router4';
import { maxios } from '@/admin/helpers.js';

import { notify } from '../../../helpers';

const vue = getCurrentInstance();
const router = useRouter();
const loaded = ref(false);
const currentPage = ref(1);
const startIndex = ref(1);
const itemsPerPage = ref(20);
const users = ref([]);
const totalResults = ref(null);
const checkedUsers = ref([]);
const filter = ref(null);
const search = ref({
  email: null,
});

watch(itemsPerPage, function (val) {
  changePage(currentPage);
});

onMounted(() => {
  var currentPage = parseInt(vue.proxy.$route.params.page || 1);
  console.log('mounted!');
  maxios
    .get(
      "api/scim/v2/Groups?count=20&startIndex=" +
      (currentPage - 1) * 20 +
      ""

    )
    .then(
      (response) => {
        users.value = response.data.Resources;
        totalResults.value = response.data.totalResults;
        startIndex.value = parseInt(response.data.startIndex);

        loaded.value = true;
      },
    );
});

function onSubmit() {
  filter.value =
    'name co "' + (search.value.email ? search.value.email : "") + '"';
  changePage(currentPage);
}

function selectAll() {
  for (var user of users.value) {
    checkedUsers.value.push(user.id);

    checkedUsers.value = Array.from(new Set(checkedUsers.value));
  }
}

function deleteSelected() {
  var promises = [];

  for (var i = 0; i < checkedUsers.value.length; i++) {
    promises.push(
      maxios.delete(
        "api/scim/v2/Groups/" + checkedUsers.value[i]
      )
    );
  }

  Promise.all(promises).then((e) => {
    notify({
      text: "We have succesfully deleted the selected groups.",
    });
    checkedUsers.value = [];
    changePage(currentPage);
  });
}

function changePage(page) {
  router.push({
    name: "groups.list",
    params: {
      page: page,
    },
  });

  maxios
    .get(
      "api/scim/v2/Groups?count=20&startIndex=" +
      ((page || 1) - 1) * itemsPerPage.value +
      (filter.value ? "&filter=" + filter.value : "")

    )
    .then(
      (response) => {
        users.value = response.data.Resources;
        totalResults.value = response.data.totalResults;
        startIndex.value = parseInt(response.data.startIndex);
      }
    );
}

function edit(user) {
  router.push({
    name: "groups.edit",
    params: {
      group_id: user.id,
    },
  });
}
</script>

<style lang="scss">
.page-item.disabled .page-link {
  color: transparent;
}

.page-item .page-link,
.page-item.disabled .page-link {
  border-color: transparent;
}

body .pagination {
  margin-top: 0px;
}
</style>
