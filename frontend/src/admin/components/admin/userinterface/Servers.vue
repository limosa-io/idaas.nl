<template>
  <div class="container-fluid">
    <h4 class="c-grey-900 mt-1 mb-3">UI Servers</h4>

    <div class="bgc-white bd bdrs-3 p-3 mt-2">
      <b-modal @ok="onSubmit" ref="newModel" id="newModel" title="New UI">
        <form class="needs-validation" v-on:submit="onSubmit">
          <div class="form-row mb-3">
            <div class="col-md-3">
              <label for="ui.url">Url</label>
            </div>
            <div class="col">
              <input id="ui.url" type="url" class="form-control" v-model="ui.url" />
            </div>
          </div>

          <template v-for="(e, index) in errors">
            <div class="alert alert-danger" role="alert" :key="index">{{ e[0] }}</div>
          </template>
        </form>
      </b-modal>

      <button
        @click="$refs.newModel.show();"
        type="button"
        class="btn btn-primary btn-sm float-right">Add Server</button>
      <p>Manage your UIs. You can connect each application to its own UI. By default, applications will make use of the default login ui.</p>

      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th scope="col">Url</th>
            <th scope="col">Name</th>
            <th scope="col" style="width: 40px;"></th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>{{ $oidcUrl('') }}</td>
            <td>Built in UI</td>
            <td>
              <button
                class="btn btn-primary btn-block"
                @click="$router.push({name: 'userinterface.design'});"
              >Edit</button>
            </td>
          </tr>
          <tr v-for="(ui,index) in objects" :key="index">
            <td>{{ ui.url }}</td>
            <td>{{ ui.name }}</td>
            <td>
              <button class="btn btn-danger btn-block" @click="deleteUi(ui)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      objects: null,

      ui: {
        url: null
      }
    };
  },

  mounted() {
    this.load();
  },

  methods: {
    deleteUi(ui) {
      this.$http.delete(this.$murl("api/uiServers/" + ui.id)).then(
        response => {
          this.load();
        },
        response => {
          // error callback
        }
      );
    },
    load() {
      this.$http.get(this.$murl("api/uiServers")).then(
        response => {
          this.objects = response.data;
        },
        response => {
          // error callback
        }
      );
    },
    onSubmit(event) {
      this.$http.post(this.$murl("api/uiServers"), this.ui).then(
        response => {
          this.$refs.newModel.hide();
          this.load();
        },
        response => {
          // error callback
          this.errors = response.data.errors;
        }
      );

      event.preventDefault();
    }
  }
};
</script>

<style lang="scss" scoped>
table tr td {
  vertical-align: middle;
}
</style>
