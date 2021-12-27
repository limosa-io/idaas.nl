
<template>
  <div class="container-fluid">
    <router-link tag="button" class="btn btn-md btn-primary float-right" to="/groups/add">
          Add Group
        </router-link>
    <h4 class="c-grey-900 mt-1 mb-3">Groups</h4>

<div class="row">
  <div class="col-md-12">
    <div class="bgc-white bd bdrs-3 pl-3 pr-3 pb-3 pt-2 mt-2" v-show="loaded">
      
      <!-- <h4 class="c-grey-900 mt-2">All groups</h4> -->

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
        <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="search.email" v-model="search.email" placeholder="">

        <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

        <button v-if="checkedUsers && checkedUsers.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted selected</button>
      </form>
      </div>
      
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Display Name</th>
              <th style="width: 30px; text-align: center;">
                <input @click.stop="selectAll" class="" type="checkbox">
              </th>
            </tr>
          </thead>
          <tbody>
            
            <tr v-for="(user,index) of users" @click="edit(user)" :key="index">
              <td style="width: 30%">{{ user['urn:ietf:params:scim:schemas:core:2.0:Group'].name }}</td>
              <td>{{ user['urn:ietf:params:scim:schemas:core:2.0:Group'].displayName }}</td>
              <td @click.stop class="text-center"><input :value="user.id" v-model="checkedUsers" @click.stop class="" type="checkbox"></td>
            </tr>

            <tr v-if="!users || users.length == 0">
              <td class="p-4 pl-0" colspan="4">There are no groups</td>
            </tr>
          </tbody>
        </table>

        <div class="d-flex flex-row align-items-center justify-content-between">

        <div>Showing {{ startIndex }} to {{ (startIndex+parseInt(itemsPerPage)-1) }} of {{ totalResults }} entries</div>
        <b-pagination v-if="totalResults > itemsPerPage" @input="changePage" size="md" :total-rows="totalResults" v-model="currentPage" :per-page="itemsPerPage" class=""></b-pagination>

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
      totalResults: null,

      checkedUsers: [],

      filter: null,

      search: {
        email: null
      }
    };
  },

  beforeRouteUpdate(to, from, next) {
    window.history.pushState({},
      document.title,
      "/groups/" + (to.params.page || 1)
    );
  },

  watch: {
    itemsPerPage: function (val) {
      this.changePage(this.currentPage);
    }
  },

  mounted() {
    var currentPage = parseInt(this.$route.params.page || 1);
    Vue.http
      .get(
        Vue.murl(
          "api/scim/v2/Groups?count=20&startIndex=" + (currentPage-1) * 20 + ""
        )
      )
      .then(
        response => {
          this.users = response.data.Resources;
          this.totalResults = response.data.totalResults;
          this.startIndex = parseInt(response.data.startIndex);

          this.loaded = true;
        },
        response => {}
      );
  },

  methods: {
    onSubmit() {
      this.filter = 'name co "' + (this.search.email ? this.search.email : '') + '"';
      this.changePage(this.currentPage);
    },

    selectAll(){
      for (var user of this.users) {
        this.checkedUsers.push(user.id);

        this.checkedUsers = Array.from(new Set(this.checkedUsers));
      }
    },

    deleteSelected() {

      var promises = [];

      for (var i = 0; i < this.checkedUsers.length; i++) {
        promises.push(this.$http.delete(this.$murl("api/scim/v2/Groups/" + this.checkedUsers[i])));
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
        name: "groups.list",
        params: {
          page: page
        }
      });

      this.$http
        .get(
          this.$murl(
            "api/scim/v2/Groups?count=20&startIndex=" +
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
        name: "groups.edit",
        params: {
          group_id: user.id
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
</style>
