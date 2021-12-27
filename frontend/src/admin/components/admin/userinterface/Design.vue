<template>
  <div class="container-fluid design-editor">
    <b-modal hide-footer size="lg" ref="pickerModal" id="pickerModal" title="Select an image">
      <Picker v-on:picked="picked" :picker="true" />
    </b-modal>

    <div class="row">
      <div class="col-3">
        <div class="preview">
          <div class="form-group">
            <label for="userinterface.modules">Preview Module</label>

            <multiselect
              id="userinterface.modules"
              v-model="authRequest.next"
              :options="modules"
              label="name"
              track-by="id"
              :searchable="false"
              :close-on-select="true"
              :show-labels="false"
              :multiple="true"
              placeholder="Pick a value"
            ></multiselect>
          </div>

          <div class="form-group mb-0">
            <label for="userinterface.client">Preview Client</label>

            <b-form-select
              id="userinterface.client"
              v-model="clientSelected"
              :options="optionsClient"
            />
          </div>
        </div>

        <form
          class="needs-validation"
          novalidate
          :class="{'was-validated': false}"
          v-on:submit="onSubmit"
        >
          <template v-if="!advanced">
            <div class="row">
              <div class="col-6">
                <div class="form-group mt-3">
                  <label for="container.title">Page Title</label>
                  <input
                    class="form-control"
                    id="container.title"
                    v-model="style['title']"
                    type="text"
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="form-group mt-3">
                  <label for="label_display">Show label</label>
                  <b-form-select
                    v-model="style['label_display']"
                    :options="['show','hidden']"
                    class="mb-3"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.backgroundImage">Background Image</label>

                  <div class="input-group mb-3">
                    <input
                      type="text"
                      class="form-control"
                      aria-label="Small"
                      id="container.backgroundImage"
                      v-model="style['container_backgroundImage']"
                    />
                    <div class="input-group-append">
                      <button
                        class="btn btn-outline-secondary"
                        type="button"
                        @click="showPicker(m => style['container_backgroundImage']=m.url)"
                      >
                        <i class="ti-upload"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="style.logo">Logo</label>

                  <div class="input-group mb-3">
                    <input
                      type="text"
                      class="form-control"
                      aria-label="Small"
                      id="style.logo"
                      v-model="style['logo']"
                    />
                    <div class="input-group-append">
                      <button
                        class="btn btn-outline-secondary"
                        type="button"
                        @click="showPicker(m => style['logo']=m.url)"
                      >
                        <i class="ti-upload"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="navbar.show">Show top bar</label>
                  <b-form-select
                    v-model="style['navbar_show']"
                    :options="['show','hidden']"
                    class="mb-3"
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label for="button.navbar_backgroundColor">Color</label>

                  <input
                    class="form-control"
                    v-model="style['navbar_backgroundColor']"
                    :type="expert ? 'text' : 'color'"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.backgroundColor">Background</label>
                  <input
                    class="form-control"
                    id="container.backgroundColor"
                    v-model="style['container_backgroundColor']"
                    :type="expert ? 'text' : 'color'"
                  />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="button.backgroundColor">Button / text</label>

                  <div class="row ml-0 mr-0">
                    <input
                      class="form-control col"
                      v-model="style['button_backgroundColor']"
                      :type="expert ? 'text' : 'color'"
                    />

                    <!-- <input class="form-control col" v-model="style['button_fontColor']" :type="expert ? 'text' : 'color'"> -->
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="client_logo_show">Show client name</label>
                  <b-form-select
                    v-model="style['client_name_show']"
                    :options="['show','hidden']"
                    class="mb-3"
                  />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="client_logo_show">Show client logo</label>
                  <b-form-select
                    v-model="style['client_logo_show']"
                    :options="['show','hidden']"
                    class="mb-3"
                  />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.positionHorizonal">Horizonal position</label>
                  <b-form-select
                    v-model="style['container_positionHorizonal']"
                    :options="['left','center','right']"
                    class="mb-3"
                  />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="container.positionVertical">Vertical position</label>
                  <b-form-select
                    v-model="style['container_positionVertical']"
                    :options="['top','middle','bottom']"
                    class="mb-3"
                  />
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <codemirror
              class="mb-3 mt-3"
              id="codemirror"
              v-model="style['css']"
              :options="cmOptions"
            ></codemirror>
          </template>

          <button class="btn btn-primary btn-block">Save</button>
        </form>

        <button
          class="btn btn-link ml-0 pl-0"
          type="button"
          @click="advanced = !advanced"
        >{{ advanced ? 'Basic' : 'Advanced' }}</button>

        <router-link
          tag="button"
          class="btn btn-link ml-0 pl-0"
          :to="{name: 'userinterface.manager'}"
        >Media Manager</router-link>
      </div>

      <div class="col">
          <iframe @load="change" ref="iframes" :src="preview"></iframe>
      </div>
    </div>
  </div>
</template>


<script>
export default {
  components: {
    Picker: () => import("./Picker"),
    codemirror: resolve =>
      import(
        /* webpackChunkName: "vue-codemirror" */ "../../lib/codemirror.js"
      ).then(m => {
        resolve(m.default.codemirror);
      })
  },

  mounted() {
    this.previewSettings = JSON.parse(window.localStorage.getItem("preview"));

    this.firstLoad();

    document.onkeydown = e => {
      if (e.altKey) {
        this.expert = !this.expert;
      }
    };

    window.addEventListener("message", event => {
      if (!this.loaded) {
        this.loaded = true;
      }

      this.postAuthrequest();

    });
  },

  computed: {
    optionsClient: function() {
      return this.clients != null && this.clients.length > 0
        ? this.clients.map(v => {
            return {
              value: v,
              text: v.client_name
            };
          })
        : [];
    }
  },

  data() {
    return {
      advanced: false,

      cmOptions: {
        tabSize: 4,
        mode: "text/css",
        theme: "lucario",
        lineNumbers: true,
        line: true
      },

      expert: false,

      loaded: false,
      callback: null,

      backgroundUrl: null,

      clientSelected: null,

      clients: [],
      modules: [],

      authRequest: {
        stateId: "123",
        next: [],

        info: {
          lev: null,
          inc: null,
          oke: "https://www.google.com",
          nok: "https://www.google.com",
          ret: null,
          api: null,
          fin: null,
          don: null,
          sco: ["openid"],
          cla: null,
          def: [],
          app: "123",
          ser: {
            redirectionUrls: [
              this.$murl('/')
            ]
          },
          log: null,
          nam: "Test Application"
        }
      },

      style: {},

      showupload: false,
      cropped: null,

      preview: this.$oidcUrl("?designer=true")
    };
  },

  watch: {
    style: {
      handler(val) {
        this.change();
      },
      deep: true
    },

    clientSelected: function(client) {
      this.authRequest.info.nam = client.client_name;

      this.authRequest.info.log = client.logo_uri;
    },

    authRequest: {
      handler(val) {
        window.localStorage.setItem(
          "preview",
          JSON.stringify({
            items: val.next.map(o => o.id),
            client: this.clientSelected ? this.clientSelected.client_id : null
          })
        );

        this.postAuthrequest();
      },
      deep: true
    }
  },

  methods: {

    postAuthrequest(){
      this.$refs.iframes.contentWindow.postMessage(
        {
          authRequest: this.authRequest
        },
        this.$oidcUrl("")
      );
    },

    picked(m) {
      this.callback(m);
      this.$refs.pickerModal.hide();
    },

    showPicker(callback) {
      this.callback = callback;
      this.$refs.pickerModal.show();
    },

    firstLoad() {
      this.$http.get(this.$murl("api/settings?namespace=ui")).then(
        response => {
          this.style = response.data;

          this.style = Object.assign(
            {},
            {
              button_backgroundColor: "#2ad42b",
              logo: null,
              container_backgroundColor: "#20b2fa",
              container_backgroundImage: null,
              button_backgroundColor: "#2ad42b",
              container_positionVertical: "middle",
              container_positionHorizonal: "center",
              navbar_show: "hide",
              navbar_backgroundColor: "#343a40",
              client_logo_show: "null",
              client_name_show: "show",
              title: "",
              label_display: "hidden"
            },
            response.data
          );
        },
        response => {
          // error callback
        }
      );

      this.$http.get(this.$murl("authchain/v2/manage/modules")).then(
        response => {
          this.modules = response.data.filter(e => {
            return e.type != "consent" && e.type != "start";
          });

          if (this.previewSettings && this.previewSettings.items) {
            this.$set(
              this.authRequest,
              "next",
              this.modules.filter(e =>
                this.previewSettings.items.includes(e.id)
              )
            );
          } else {
            this.$set(this.authRequest, "next", [this.modules[0]]);
          }
        },
        response => {
          // error callback
        }
      );

      this.$http.get(this.$oidcUrl("oauth/connect/register")).then(
        response => {
          this.clients = response.data;

          if (this.previewSettings && this.previewSettings.client) {
            this.clientSelected = this.clients.find(
              e => e.client_id == this.previewSettings.client
            );
          }
        },
        response => {
          // error callback
        }
      );
    },

    crop() {
      let options = {
        type: "base64",
        format: "png",
        circle: false,
        size: {
          width: 255,
          height: 75
        }
      };

      this.$refs.imageupload.result(options, output => {
        this.style.logo = output;

        this.showupload = false;
      });
    },

    readFile: function(event) {
      var input = event.target;

      if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = event => {
          this.$refs.imageupload.bind({
            url: event.target.result
          });

          this.showupload = true;
        };

        reader.readAsDataURL(input.files[0]);
      } else {
        alert("Sorry - you're browser doesn't support the FileReader API");
      }
    },

    onSubmit(event) {
      this.$http
        .put(this.$murl("api/settings/bulk?namespace=ui"), this.style)
        .then(
          response => {
            this.$noty({
              text: "We have succesfully saved your provider settings."
            });
            // this.style = response.data;
          },
          response => {
            // error callback

            this.$noty({
              text: "We could not save this.",
              type: "error"
            });
          }
        );

      event.preventDefault();
    },

    change() {
      // TODO: update this to something like ..
      this.$refs.iframes.contentWindow.postMessage(
        {
          type: 'set_style',
          style: this.style
        },
        this.$oidcUrl("")
      );
    }
  }
};
</script>

<style lang="scss">
body .design-editor {
  .CodeMirror {
    min-height: 400px !important;
  }

  .modal-lg {
    min-width: 90%;
    width: 90vw;
    max-width: 90%;
  }

  .preview {
    h5 {
      font-size: 1rem;
    }

    border-bottom-style: dashed;
    border-bottom-color: rgb(186, 186, 186);
    border-bottom-width: 1px;
    margin-bottom: 5px;
    padding: 10px 10px 10px 10px;
    background-color: rgba(201, 201, 201, 0.5);
  }

  iframe {
    width: 100%;
    height: 100%;
    border-style: solid;
    border-width: 1px;
    border-color: #eeeeee;

    &.fullscreen {
      width: 100vw;
      height: 100vh;
      position: fixed;
      padding: 0px;
      margin: 0px;
      left: 0px;
      top: 0px;
      border-style: none;
      z-index: 9999;
      background-color: white;
    }
  }

  .input-group-prepend .btn,
  .input-group-append .btn {
    z-index: 0;
  }
}
</style>