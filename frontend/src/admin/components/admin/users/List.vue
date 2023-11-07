
<template>
  <MainTemplate title="Users">
    <template v-slot:header>
      <MenuButton to="/users/add"> Add User </MenuButton>
    </template>

    <template v-slot:body v-if="loaded">
      <div class="d-flex flex-row justify-content-between">
        <div>
          <form class="form-inline">
            <label class="" for="entries">Show</label>
            <select
              class="form-control form-control-sm ml-2 mr-2"
              id="entries"
              v-model="itemsPerPage"
            >
              <option :value="20">20</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
            entries
          </form>
        </div>

        <form class="form-inline" v-on:submit.prevent="onSubmit">
          <select
            v-if="groups && groups.length > 0"
            v-model="search.group"
            class="form-control form-control-sm mb-2 mr-sm-2"
            id="group"
          >
            <option :value="null">...</option>
            <option
              v-for="(group, index) in groups"
              :key="index"
              :value="group.id"
            >
              {{ group["urn:ietf:params:scim:schemas:core:2.0:Group"].name
              }}<template
                v-if="
                  group['urn:ietf:params:scim:schemas:core:2.0:Group']
                    .displayName
                "
              >
                ({{
                  group["urn:ietf:params:scim:schemas:core:2.0:Group"]
                    .displayName
                }})</template
              >
            </option>
          </select>
          <label class="sr-only" for="search.email">E-mail</label>
          <input
            type="search"
            class="form-control form-control-sm mb-2 mr-sm-2"
            id="search.email"
            v-model="search.email"
            placeholder=""
          />

          <button type="submit" class="btn btn-primary mb-2 btn-sm">
            Search
          </button>

          <button
            v-if="checkedUsers && checkedUsers.length > 0"
            @click="deleteSelected()"
            type="button"
            class="btn btn-danger ml-2 mb-2 btn-sm"
          >
            Deleted selected
          </button>
        </form>
      </div>

      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th scope="col">Email</th>
            <th scope="col">Username</th>
            <th scope="col">Name</th>
            <th scope="col">
              Created
              <span class="sort" @click="setSortBy('meta.created')">↓↑</span>
            </th>
            <th style="width: 30px; text-align: center">
              <input @click.stop="selectAll" class="" type="checkbox" />
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, index) of users" @click="edit(user)" :key="index">
            <td class="">
              {{
                user["urn:ietf:params:scim:schemas:core:2.0:User"].emails[0]
                  .value
              }}
            </td>
            <td style="width: 30%">
              {{ user["urn:ietf:params:scim:schemas:core:2.0:User"].userName }}
            </td>
            <td style="width: 30%">
              {{
                user["urn:ietf:params:scim:schemas:core:2.0:User"].displayName
              }}
            </td>
            <td class="">{{ formatDate(user.meta.created) }}</td>
            <td @click.stop class="text-center">
              <input
                :value="user.id"
                v-model="checkedUsers"
                @click.stop
                class=""
                type="checkbox"
              />
            </td>
          </tr>

          <tr v-if="!users || users.length == 0">
            <td class="p-4 pl-0" colspan="4">
              There are no users. But what about the account you are now logged
              in with? It's a federated identity.
            </td>
          </tr>
        </tbody>
      </table>

      <div class="d-flex flex-row align-items-center justify-content-between">
        <div>
          Showing {{ startIndex }} to
          {{ startIndex + parseInt(itemsPerPage) - 1 }} of
          {{ totalResults }} entries
        </div>
        <Pagination
          v-if="totalResults > itemsPerPage"
          @input="changePage"
          size="md"
          :total-rows="totalResults"
          v-model="currentPage"
          :per-page="itemsPerPage"
          class=""
        ></Pagination>
      </div>
    </template>
  </MainTemplate>
</template>

<script setup>
import {ref, getCurrentInstance, onMounted, watch} from "vue";
import {maxios, notify} from "@/admin/helpers.js";
import { useRoute, useRouter } from "vue-router4";

const router = useRouter();
const loaded = ref(false);
const currentPage = ref(1);
const startIndex = ref(1);
const itemsPerPage = ref(20);
const users = ref([]);
const totalResults = ref(null);
const checkedUsers = ref([]);
const groups = ref([]);
const search = ref({
  email: null,
  group: null,
});
const filter = ref(null);
const sortOrder = ref("descending");
const sortBy = ref("id");
const vue = getCurrentInstance();

watch(itemsPerPage, (val) => {
  changePage(currentPage.value);
});

watch(currentPage, val => {
  changePage(val);
});

function selectAll() {
  for (var user of users) {
    checkedUsers.value.push(user.id);

    checkedUsers.value = Array.from(new Set(checkedUsers.value));
  }
}

function onSubmit() {
  var f = [];

  if (search.value.email) {
    f.push(
      'emails.value co "' +
        (search.value.email ? search.value.email : "") +
        '"'
    );
  }

  if (search.value.group) {
    f.push(`groups.value eq "${search.value.group}"`);
  }

  filter.value = f.join(" and ");
  
  changePage(currentPage.value);
}

function formatDate(date) {
  return new Date(date).toLocaleString();
}

function setSortBy(s) {
  if (s == sortBy.value) {
    sortOrder.value = sortOrder.value == "descending" ? "ascending" : "descending";
  } else {
    sortBy.value = s;
    sortOrder.value = "descending";
  }

  changePage(currentPage.value);
}

function edit(user) {
  router.push("/users/edit/" + user.id);
}

function changePage(page) {
  maxios
    .get(
      `api/scim/v2/Users?sortBy=${sortBy.value}&sortOrder=${sortOrder.value}&count=${itemsPerPage.value}&startIndex=` +
        ((page || 1) - 1) * itemsPerPage +
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

function deleteSelected() {
  let promises = [];

  for (var c of checkedUsers.value) {
    promises.push(maxios.delete("api/scim/v2/Users/" + c));
  }

  Promise.all(promises).then(
    e => {
      notify({
        text: "We have succesfully deleted the selected user.",
      });
      checkedUsers.value = [];
      changePage(currentPage.value);
    },
    e => {
      notify({
        text: "We have succesfully deleted the selected user.",
      });
      checkedUsers.value = [];
      changePage(currentPage.value);
    }
  );
}

onMounted(() => {
  maxios
    .get(
      `api/scim/v2/Users?count=${itemsPerPage.value}&startIndex=${
        (currentPage.value - 1) * 20
      }&sortBy=${sortBy.value}`
    )
    .then(
      (response) => {
        users.value = response.data.Resources;
        totalResults.value = response.data.totalResults;
        startIndex.value = parseInt(response.data.startIndex);

        loaded.value = true;

        maxios.get("api/scim/v2/Groups").then((response) => {
          groups.value = response.data.Resources;
        });
      }
    );
});

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

.sort {
  float: right;
  cursor: pointer;

  .active {
    color: green;
  }
}
</style>
