
<template>
<div class="container-fluid">
  <router-link tag="button" class="btn btn-md btn-primary float-right" to="/users/add">
    Add User
  </router-link>
  <h4 class="c-grey-900 mt-1 mb-3">User Administrator</h4>

  <div class="row">
    <div class="col-md-12">
      <div class="bgc-white bd bdrs-3 pl-3 pr-3 pb-3 pt-2 mt-2" v-show="loaded">

        <div class="d-flex flex-row justify-content-between">
          <div>
            <form class="form-inline">
              <label class="" for="entries">Show</label>
              <select class="form-control form-control-sm ml-2 mr-2" id="entries" v-model="itemsPerPage">
                <option :value="20">20</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              entries
            </form>

          </div>

          <form class="form-inline" v-on:submit.prevent="onSubmit">
            <select v-if="groups && groups.length > 0" v-model="search.group" class="form-control form-control-sm mb-2 mr-sm-2" id="group">
              <option :value="null">...</option>
              <option v-for="(group,index) in groups" :key="index" :value="group.id">{{
                group['urn:ietf:params:scim:schemas:core:2.0:Group'].name }}<template v-if="group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName">
                  ({{ group['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName }})</template></option>
            </select>
            <label class="sr-only" for="search.email">E-mail</label>
            <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="search.email" v-model="search.email"
              placeholder="">

            <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

            <button v-if="checkedUsers && checkedUsers.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted
              selected</button>
          </form>
        </div>

        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th scope="col">Email</th>
              <th scope="col">Username</th>
              <th scope="col">Name</th>
              <th scope="col">Created <span class="sort" @click="setSortBy('meta.created')">↓↑</span></th>
              <th style="width: 30px; text-align: center;">
                <input @click.stop="selectAll" class="" type="checkbox">
              </th>
            </tr>
          </thead>
          <tbody>

            <tr v-for="(user,index) of users" @click="edit(user)" :key="index">
              <td class="">{{ user['urn:ietf:params:scim:schemas:core:2.0:User'].emails[0].value }}</td>
              <td style="width: 30%">{{ user['urn:ietf:params:scim:schemas:core:2.0:User'].userName }}</td>
              <td style="width: 30%">{{ user['urn:ietf:params:scim:schemas:core:2.0:User'].displayName }}</td>
              <td class="">{{ formatDate(user.meta.created) }}</td>
              <td @click.stop class="text-center"><input :value="user.id" v-model="checkedUsers" @click.stop class=""
                  type="checkbox"></td>
                  
            </tr>

            <tr v-if="!users || users.length == 0">
              <td class="p-4 pl-0" colspan="4">There are no users. But what about the account you are now logged in with? It's a federated identity.</td>
            </tr>
          </tbody>
        </table>

        <div class="d-flex flex-row align-items-center justify-content-between">

          <div>Showing {{ startIndex }} to {{ (startIndex+parseInt(itemsPerPage)-1) }} of {{ totalResults }} entries</div>
          <b-pagination v-if="totalResults > itemsPerPage" @input="changePage" size="md" :total-rows="totalResults"
            v-model="currentPage" :per-page="itemsPerPage" class=""></b-pagination>

        </div>

      </div>
    </div>
  </div>
</div>

</template>

<script>
import Vue from "vue";

export default {

  data() {
    return {

      loaded: false,

      currentPage: 1,
      startIndex: 1,
      itemsPerPage: 20,
      users: [],
      groups: [],
      totalResults: null,

      checkedUsers: [],

      filter: null,

      sortOrder: 'descending',
      sortBy: 'id',

      search: {
        email: null,
        group: null
      }
    };
  },

  beforeRouteUpdate(to, from, next) {
    window.history.pushState({},
      document.title,
      "/users/" + (to.params.page || 1)
    );
  },

  watch: {
    itemsPerPage: function (val) {
      this.changePage(this.currentPage);
    }
  },

  mounted() {
    var currentPage = parseInt(this.$route.params.page || 1);

    this.$http
      .get(
        Vue.murl(
          `api/scim/v2/Users?count=${this.itemsPerPage}&startIndex=${(currentPage - 1) * 20}&sortBy=${this.sortBy}`
        )
      )
      .then(
        response => {
          this.users = response.data.Resources;
          this.totalResults = response.data.totalResults;
          this.startIndex = parseInt(response.data.startIndex);

          this.loaded = true;

          this.$http.get(this.$murl("api/scim/v2/Groups")).then(response => {
            this.groups = response.data.Resources;
          })


        },
        response => {}
      );

  },

  methods: {

    setSortBy(field){

      if(this.sortBy == field){
        this.sortOrder = this.sortOrder == 'ascending' ? 'descending' : 'ascending';
      }
      
      this.sortBy = field;
      this.changePage(this.currentPage);

    },

    formatDate(data){
      let date = new Date(Date.parse(data));
      return (date.getMonth()+1) + '/' + date.getDate() + '/' + date.getFullYear();
    },

    onSubmit() {

      var f = [];

      if (this.search.email) {
        f.push('emails.value co "' + (this.search.email ? this.search.email : '') + '"');
      }

      if (this.search.group) {
        f.push(`groups.value eq "${this.search.group}"`);

      }

      this.filter = f.join(' and ');

      this.currentPage = 1;

      this.changePage(this.currentPage);
    },

    selectAll() {
      for (var user of this.users) {
        this.checkedUsers.push(user.id);

        this.checkedUsers = Array.from(new Set(this.checkedUsers));
      }
    },

    deleteSelected() {

      var promises = [];

      for (var i = 0; i < this.checkedUsers.length; i++) {
        promises.push(this.$http.delete(this.$murl("api/scim/v2/Users/" + this.checkedUsers[i])));
      }

      Promise.all(promises).then(e => {
        this.$noty({
          text: 'We have succesfully deleted the selected user.'
        });
        this.checkedUsers = [];
        this.changePage(this.currentPage);
      });

    },

    changePage(page) {
      this.$router.push({
        name: "users.list",
        params: {
          page: page
        }
      });

      this.$http
        .get(
          this.$murl(
            `api/scim/v2/Users?sortBy=${this.sortBy}&sortOrder=${this.sortOrder}&count=${this.itemsPerPage}&startIndex=` +
            ((page || 1) - 1) * this.itemsPerPage +
            (this.filter ? "&filter=" + this.filter : "")
          )
        )
        .then(
          response => {
            this.users = response.data.Resources;
            this.totalResults = response.data.totalResults;
            this.startIndex = parseInt(response.data.startIndex);
          },
          response => {
            // error callback
          }
        );
    },

    edit(user) {
      this.$router.push({
        name: "users.edit",
        params: {
          user_id: user.id
        }
      });
    }
  }
};
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

.sort{
  float: right;
  cursor: pointer;

  .active {
    color: green;
  }
}
</style>
