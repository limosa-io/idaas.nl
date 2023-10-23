<template>
  <div class="container-fluid design-editor">
    <Modal ref="pickerModal" id="pickerModal" title="Select an image">
      <Picker v-on:picked="picked" :picker="true" />
    </Modal>

    <div class="row">
      <div class="col-3">
        <div class="preview">
          <div class="form-group">
            <label for="userinterface.modules">Preview Module</label>

            <multiselect id="userinterface.modules" v-model="authRequest.next" :options="modules" label="name"
              track-by="id" :searchable="false" :close-on-select="true" :show-labels="false" :multiple="true"
              placeholder="Pick a value"></multiselect>
          </div>

          <div class="form-group mb-0">
            <label for="userinterface.client">Preview Client</label>

            <FormSelect id="userinterface.client" v-model="clientSelected" :options="optionsClient" />
          </div>
        </div>

        <form class="needs-validation" novalidate :class="{ 'was-validated': false }" v-on:submit="onSubmit">
          <template v-if="!advanced">
            <div class="row">
              <div class="col-6">
                <div class="form-group mt-3">
                  <label for="container.title">Page Title</label>
                  <input class="form-control" id="container.title" v-model="style['title']" type="text" />
                </div>
              </div>
              <div class="col-6">
                <div class="form-group mt-3">
                  <label for="label_display">Show label</label>
                  <FormSelect v-model="style['label_display']" :options="['show', 'hidden']" class="mb-3" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.backgroundImage">Background Image</label>

                  <div class="input-group mb-3">
                    <input type="text" class="form-control" aria-label="Small" id="container.backgroundImage"
                      v-model="style['container_backgroundImage']" />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button"
                        @click="showPicker(m => style['container_backgroundImage'] = m.url)">
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
                    <input type="text" class="form-control" aria-label="Small" id="style.logo" v-model="style['logo']" />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button"
                        @click="showPicker(m => style['logo'] = m.url)">
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
                  <FormSelect v-model="style['navbar_show']" :options="['show', 'hidden']" class="mb-3" />
                </div>
              </div>
              <div class="col-6">
                <div class="form-group">
                  <label for="button.navbar_backgroundColor">Color</label>

                  <input class="form-control" v-model="style['navbar_backgroundColor']"
                    :type="expert ? 'text' : 'color'" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.backgroundColor">Background</label>
                  <input class="form-control" id="container.backgroundColor" v-model="style['container_backgroundColor']"
                    :type="expert ? 'text' : 'color'" />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="button.backgroundColor">Button / text</label>

                  <div class="row ml-0 mr-0">
                    <input class="form-control col" v-model="style['button_backgroundColor']"
                      :type="expert ? 'text' : 'color'" />

                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="client_logo_show">Show client name</label>
                  <FormSelect v-model="style['client_name_show']" :options="['show', 'hidden']" class="mb-3" />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="client_logo_show">Show client logo</label>
                  <FormSelect v-model="style['client_logo_show']" :options="['show', 'hidden']" class="mb-3" />
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <div class="form-group">
                  <label for="container.positionHorizonal">Horizonal position</label>
                  <FormSelect v-model="style['container_positionHorizonal']" :options="['left', 'center', 'right']"
                    class="mb-3" />
                </div>
              </div>

              <div class="col-6">
                <div class="form-group">
                  <label for="container.positionVertical">Vertical position</label>
                  <FormSelect v-model="style['container_positionVertical']" :options="['top', 'middle', 'bottom']"
                    class="mb-3" />
                </div>
              </div>
            </div>
          </template>

          <template v-else>

            <Codemirror class="mb-3 mt-3" id="codemirror" :options="cmOptions" v-model:value="style['css']">
            </Codemirror>

          </template>

          <button class="btn btn-primary btn-block">Save</button>
        </form>

        <button class="btn btn-link ml-0 pl-0" type="button" @click="advanced = !advanced">{{ advanced ? 'Basic' :
          'Advanced' }}</button>

        <!-- FIXME: errors-->
        <!-- <router-link
          class="btn btn-link ml-0 pl-0"
          :to="{name: 'userinterface.manager'}"
        >Media Manager</router-link> -->
      </div>

      <div class="col">
        <iframe @load="change" ref="iframes" :src="preview"></iframe>
      </div>
    </div>
  </div>
</template>


<script setup>

import Picker from './Picker.vue'
import Modal from '@/admin/components/general/Modal.vue'

import { ref, onMounted, watch, computed } from 'vue';
import { maxios, laxios, manageUrl, oidcUrl, notify } from '@/admin/helpers.js';

const cmOptions = ref({
  tabSize: 4,
  lineNumbers: true,
  line: true,
});

onMounted(() => {
  previewSettings = JSON.parse(window.localStorage.getItem("preview"));
  firstLoad();

  document.onkeydown = e => {
    if (e.altKey) {
      expert.value = !expert.value;
    }
  };

  window.addEventListener("message", event => {
    if (!loaded.value) {
      loaded.value = true;
    }

    postAuthrequest();
  });
});


const optionsClient = computed(() => {
  return clients.value != null && clients.value.length > 0
    ? clients.value.map(v => {
      return {
        value: v,
        text: v.client_name
      };
    })
    : [];
});

const advanced = ref(false);
const expert = ref(false);
const loaded = ref(false);
var callback = null;
const backgroundUrl = ref(null);
const clientSelected = ref(null);
const clients = ref([]);
const modules = ref([]);
const authRequest = ref({
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
        manageUrl
      ]
    },
    log: null,
    nam: "Test Application"
  }
});

const style = ref({});
const showupload = ref(false);
const cropped = ref(null);
const preview = ref(oidcUrl + "?designer=true");
const iframes = ref(null);
var previewSettings = {};

watch(
  style,
  val => {
    if (loaded.value) {
      change();
    }
  },
  { deep: true }
);

watch(clientSelected, client => {
  authRequest.value.info.nam = client.client_name;
  authRequest.value.info.log = client.logo_uri;
});

watch(authRequest, val => {
  window.localStorage.setItem(
    "preview",
    JSON.stringify({
      items: val.next.map(o => o.id),
      client: clientSelected.value ? clientSelected.value.client_id : null
    })
  );

  postAuthrequest();
}, { deep: true });


function postAuthrequest() {
  iframes.value.contentWindow.postMessage(
    {
      authRequest: JSON.parse(JSON.stringify(this.authRequest))
    },
    oidcUrl
  );
}

function picked(m) {
  callback(m);
  pickerModal.hide();
}

function showPicker(c) {
  callback = c;
  pickerModal.show();
}

function firstLoad() {
  maxios.get('api/settings?namespace=ui').then(response => {
    style.value = response.data;

    style.value = Object.assign(
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
  });

  maxios.get('authchain/v2/manage/modules').then(response => {
    modules.value = response.data.filter(e => {
      return e.type != "consent" && e.type != "start";
    });

    if (previewSettings && previewSettings.items) {
      authRequest.value.next = modules.value.filter(e =>
        previewSettings.items.includes(e.id)
      );
    } else {
      authRequest.value.next = [modules.value[0]];
    }
  });

  laxios.get('oauth/connect/register').then(response => {
    clients.value = response.data;

    if (previewSettings && previewSettings.client) {
      clientSelected.value = clients.value.find(
        e => e.client_id == previewSettings.client
      );
    }
  });
}

function onSubmit(event) {
  maxios.put('api/settings/bulk?namespace=ui', style.value).then(response => {
    notify({
      text: 'We have succesfully saved your provider settings.'
    });
    // this.style = response.data;
  }, response => {
    // error callback

    notify({
      text: 'We could not save this.',
      type: 'error'
    });
  });

  event.preventDefault();
}

function change() {
  iframes.value.contentWindow.postMessage(
    {
      type: 'set_style',
      style: JSON.parse(JSON.stringify(style.value))
    },
    oidcUrl
  );
}

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