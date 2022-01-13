<template>
  <div>
    <b-modal @hidden="url = null" :hide-footer="url == null" ok-only size ref="demoModal" id="demoModal" :title="title">
      <template v-if="url == null">
        <div class="list-group">
          <div
            @click="createApplication('vue')"
            class="list-group-item list-group-item-action d-flex align-items-center"
          >
            <div class="float-left">
              <img style="width: 80px;" alt="Vue.js" src="./vue.svg" />
            </div>
            <div class="m-2">
              <p class="card-text">Vue.js - The Progressive JavaScript Framework</p>
            </div>
          </div>
        </div>

        <div class="list-group">
          <div
            @click="createApplication('angular')"
            class="list-group-item list-group-item-action d-flex align-items-center"
          >
            <div class="float-left">
              <img style="width: 80px;" alt="Vue.js" src="./angular.svg" />
            </div>
            <div class="m-2">
              <p
                class="card-text"
              >Angular is a platform for building mobile and desktop web applications</p>
            </div>
          </div>
        </div>

        <div class="list-group">
          <div
            @click="createApplication('react')"
            class="list-group-item list-group-item-action d-flex align-items-center"
          >
            <div class="float-left">
              <img style="width: 80px;" alt="Vue.js" src="./react.svg" />
            </div>
            <div class="m-2">
              <p class="card-text">A JavaScript library for building user interfaces</p>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <p>Your test application is now ready! Well ... if you see a blank page, wait a few minutes and retry.</p>
        <a :href="url" target="_blank" class="btn btn-primary btn-lg btn-block mb-2">{{ url }}</a>
        <p>This test application is an OpenID Relying Party and added to the list of your applications with name 'Demo Application'.</p>
        <p>If you have some development experience, you may edit the application details on <a target="_blank" :href="url_edit">codesandbox.io</a>.</p>
      </template>
    </b-modal>
  </div>
</template>

<script>
export default {
  name: "popup-demo-application",

  data(){
    return {
      url: null,
      url_edit: null,
      title: 'Pick your framework',
      client: null

    }
  },

  methods: {
    show() {
      this.$refs.demoModal.show();
    },
    createApplication(type) {

      this.title = 'Pick your framework';

      let client = null;
      let date = new Date();

      this.$http
        .post(this.$oidcUrl("oauth/connect/register"), {
          client_name: `Test Application - ${date.getDate() + '/' + (date.getMonth()+1) + '/' + date.getFullYear() + ' ' + date.getHours() + ':' + date.getMinutes()}`,
          application_type: "web",
          public: "public",
          grant_types: ["authorization_code", "refresh_token"]
        })
        .then(response => {
          client = response.data;
          return this.$http.get(this.$murl("api/example_client"), {
            params: {
              client_id: response.data.client_id
            }
          });
        })
        .then(response => {
          return this.$http
            .post("https://codesandbox.io/api/v1/sandboxes/define?json=1", {
              files: response.body
            }, {
              credentials: false
            })
            .then(response => {
              // Here is your demo app => https://fvpgp.csb.app
              return response.body.sandbox_id;
            })
            .catch(response => {});
        })
        .then(response => {
          this.url = `https://${response}.csb.app`;
          this.url_edit = `https://codesandbox.io/s/${response}`;
          this.title = 'Your test application is ready';

          return this.$http.put(
            this.$oidcUrl("oauth/connect/register/" + client.client_id),
            {
              ...client,
              redirect_uris: [this.url + "/"],
              post_logout_redirect_uris: [this.url + "/"]
            }
          );
        }).then(response => {
          this.client = response.data;
        });
    }
  }
};
</script>

<style lang="scss">
.list-group {
  cursor: pointer;
}
</style>