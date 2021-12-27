<template>

<div>

  <h3 class="c-grey-900">Access Tokens</h3>

  <p>Access tokens are used to protect APIs</p>

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

      <label class="sr-only" for="query">Keyword</label>
      <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="query" v-model="query"
        placeholder="">

      <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

      <button v-if="checked && checked.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted
        selected</button>

    </form>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th scope="col">Client</th>
        <th scope="col">User</th>
        <th scope="col">Subject</th>
        <th scope="col">Expiration</th>
        <th scope="col">Scopes</th>
        <th style="width: 30px; text-align: center;">
          <input @click.stop="selectAll" class="" type="checkbox">
        </th>
      </tr>
    </thead>
    <tbody>

      <tr v-for="token of tokens" :key="token.id">
        <td>{{ token.client.client_name }}</td>
        <td>{{ token.subject && token.subject.user ? token.subject.user.email : null }}</td>
        <td>{{ token.subject ? token.subject.identifier : null }}</td>
        <td>{{ token.expires_at }}</td>
        <td> <span v-for="(scope,index) of token.scopes" :key="index">{{ scope }} </span></td>
        <td @click.stop class="text-center"><input :value="token.id" v-model="checked" @click.stop class="" type="checkbox"></td>
      </tr>

    </tbody>
  </table>

  <div class="d-flex flex-row align-items-center justify-content-between">

    <div>Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries</div>
    <b-pagination v-if="pagination.total > pagination.per_page" @input="changePage" size="md" :total-rows="pagination.total"
      v-model="currentPage" :per-page="pagination.per_page" class=""></b-pagination>
  </div>

</div>

</template>

<script>

export default {

  data() {
    return {
      tokens: [],

      checked: [],

      itemsPerPage: 20,

      query: null,

      currentPage: 1,
      totalResults: null,
      startIndex: null,

      pagination: {

      }

    }
  },

  mounted() {
    this.changePage();
  },

  watch: {
    itemsPerPage: function(val){
      this.currentPage = 1;
      this.changePage();
    }
  },

  methods: {


    selectAll(){


    for (var token of this.tokens) {
        this.checked.push(token.id);

        this.checked = Array.from(new Set(this.checked));
      }

    },

    deleteSelected(){

      let promises = [];

      for(var c of this.checked){

        promises.push(

          this.$http.post(this.$murl('api/tokens/revoke'), {
            token: c
          })

        );
      }

      Promise.all(promises).then(e => {
        this.$noty({
          text: 'We have succesfully revoked the selected tokens.'
        });
        this.checked = [];
        this.changePage();
      });

    },

    onSubmit() {
      this.currentPage = 1;
      this.changePage();
    },

    changePage() {

      this.$http.get(this.$murl('api/tokens'), {
        params: {
          query: this.query,
          page: this.currentPage,
          size: this.itemsPerPage
        }
      }).then(response => {
        this.tokens = response.data.data
        this.pagination = response.data
      }, response => {
        // error callback

        this.totalResults = response.data.totalResults;
          this.startIndex = parseInt(response.data.startIndex);

      });

    }

  }

}
</script>

