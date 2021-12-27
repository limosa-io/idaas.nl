<template>

<div>

  <h3 class="c-grey-900">Subjects</h3>

  <p>Subjects are linked to live sessions and issues access tokens.</p>

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

      <label class="sr-only" for="search.email">E-mail</label>
      <input type="search" class="form-control form-control-sm mb-2 mr-sm-2" id="search.email" v-model="search.email"
        placeholder="">

      <button type="submit" class="btn btn-primary mb-2 btn-sm">Search</button>

      <button v-if="checked && checked.length > 0" @click="deleteSelected()" type="button" class="btn btn-danger ml-2 mb-2 btn-sm">Deleted
        selected</button>
    </form>
  </div>

  <table class="table table-striped table-bordered">
    <thead>
      <tr>
        <th scope="col">Identifier</th>
        <th scope="col">User</th>
        <th scope="col">Created</th>
        <th scope="col">Details</th>
        <th style="width: 30px; text-align: center;">
          <input @click.stop="selectAll" class="" type="checkbox">
        </th>
      </tr>
    </thead>
    <tbody>

      <tr v-for="(subject,index) of resources" @click="edit(user)" :key="index">
        <td style="width: 20%">{{ subject['urn:ietf:params:scim:schemas:subjects'].identifier }}</td>
        <td class="">{{ subject['urn:ietf:params:scim:schemas:subjects'].user ? subject['urn:ietf:params:scim:schemas:subjects'].user.email : '' }}</td>
        <td>
          {{ subject.meta.created }}
        </td>
        <td class="details">
          <pre class="mb-0">{{ beautify(subject['urn:ietf:params:scim:schemas:subjects'].subject) }}</pre>
        </td>
        <td @click.stop class="text-center"><input :value="subject.id" v-model="checked" @click.stop class="" type="checkbox"></td>
      </tr>

      <tr v-if="!resources || resources.length == 0">
        <td class="p-4 pl-0" colspan="4">There are no subjects</td>
      </tr>
    </tbody>
  </table>

  <div class="d-flex flex-row align-items-center justify-content-between">

    <div>Showing {{ startIndex }} to {{ (startIndex+parseInt(itemsPerPage)-1) }} of {{ totalResults }} entries</div>

    <b-pagination v-if="totalResults > itemsPerPage" @input="changePage" size="md" :total-rows="totalResults" v-model="currentPage"
      :per-page="itemsPerPage" class=""></b-pagination>

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
      resources: [],
      totalResults: null,

      checked: [],

      search: {
        email: null,
        group: null
      },

      filter: null
    }
  },

  watch: {
    itemsPerPage: function (val) {
      this.changePage(this.currentPage);
    }
  },

  methods: {

    selectAll(){


    for (var resource of this.resources) {
        this.checked.push(resource.id);

        this.checked = Array.from(new Set(this.checked));
      }

    },

    onSubmit(){

      this.changePage(this.currentPage);

    },

    deleteSelected(){

      let promises = [];

      for(var c of this.checked){

        promises.push(
          this.$http.delete(this.$murl('api/scim/v2/Subjects/' + c))
        );

      }

      Promise.all(promises).then(e => {
        this.$noty({
          text: 'We have succesfully deleted the selected subject.'
        });
        this.checked = [];
        this.changePage(this.currentPage);
      });

    },

    beautify(subject){
      return JSON.stringify(subject, undefined, 3);
    },
    
    changePage(page) {
      

      this.$http
        .get(
          this.$murl(
            `api/scim/v2/Subjects?sortBy=meta.created&sortOrder=descending&count=${this.itemsPerPage}&startIndex=` +
            ((page || 1) - 1) * this.itemsPerPage +
            (this.filter ? "&filter=" + this.filter : "")
          )
        )
        .then(
          response => {
            this.resources = response.data.Resources;
            this.totalResults = response.data.totalResults;
            this.startIndex = parseInt(response.data.startIndex);
          },
          response => {
            // error callback
          }
        );
    },

  },


  mounted() {
    var currentPage = parseInt(this.$route.params.page || 1);

    this.$http
      .get(
        this.$murl(
          `api/scim/v2/Subjects?sortBy=meta.created&sortOrder=descending&count=${this.itemsPerPage}&startIndex=${(currentPage - 1) * 20}`
        )
      )
      .then(
        response => {
          this.resources = response.data.Resources;
          this.totalResults = response.data.totalResults;
          this.startIndex = parseInt(response.data.startIndex);

          this.loaded = true;




        },
        response => {}
      );

  },

}

</script>

<style lang="scss" scoped>
.details{
  max-width: 300px;
}
</style>