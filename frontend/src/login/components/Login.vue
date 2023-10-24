<template>
  <div style="height: 100vh" v-show="loaded">
    <div
      id="idaas-container"
      @click="closePopup"
      v-if="authRequest != null && customerstyle != null && authRequest.next"
      class="container"
      :style="{
        backgroundColor: customerstyle['container_backgroundColor'],
        'background-image': `url(${customerstyle['container_backgroundImage']})`,
      }"
    >
      <nav
        class="navbar navbar-dark pt-1 pb-1 justify-content-between align-items-stretch"
        :style="{ backgroundColor: customerstyle['navbar_backgroundColor'] }"
        v-if="
          customerstyle && (customerstyle['navbar_show'] || 'show') == 'show'
        "
      >
        <div>
          <img
            v-if="customerstyle.logo != null && customerstyle.logo != ''"
            :src="customerstyle.logo"
            alt="Company Logo"
          />
        </div>
        <a
          v-if="customerstyle && customerstyle.title"
          class="navbar-brand"
          href="#"
          >{{ customerstyle.title }}</a
        >
        <div class="d-flex align-items-center">
          <select
            v-if="customerstyle.languages && customerstyle.languages.length > 0"
            right
            :text="$t('general.locale.' + $i18n.locale)"
            variant="primary"
            class="language-selector"
          >
            <option
              href="#"
              @click.prevent="switchLanguage(l)"
              v-for="(l, index) in customerstyle.languages"
              :key="index"
              :value="index"
            >
              {{ $t("general.locale." + l) }}
            </option>
          </select>
        </div>
      </nav>
      <nav v-else class="navbar justify-content-end align-items-center">
        <select
          v-if="customerstyle.languages && customerstyle.languages.length > 0"
          right
          :text="$t('general.locale.' + $i18n.locale)"
          variant="primary"
          class="language-selector"
        >
          <option
            @click.prevent="switchLanguage(l)"
            v-for="(l, index) in customerstyle.languages"
            :key="index"
            :value="index"
          >
            {{ $t("general.locale." + l) }}
          </option>
        </select>
      </nav>

      <router-view
        :authRequest="authRequest"
        :customerstyle="customerstyle"
        :next="next"
      ></router-view>
    </div>

    <div class="container" v-else-if="authRequest == null">
      <!-- || !authRequest.info.don -->

      <div class="justify-content-center align-items-center row login-row">
        <!-- You're on the login page, but we're not sure where you want to go to next ... -->
      </div>
    </div>

    <VStyle v-if="customerstyle"></VStyle>
  </div>
</template>

<script setup>
import {
  onMounted,
  watch,
  computed,
  ref,
  defineProps,
  h,
} from "vue";
import { useStateStore } from "./store";
import { useRouter, useRoute } from "vue-router4";
import axios from "axios";

import { switchLanguage } from "@/login/i18n.js";

import queryString from "query-string";
import { storeToRefs } from "pinia";

import { post } from "./modules/composable";

const state = useStateStore();

const props = defineProps(["authRequest"]);

var groupBy = function (xs, key) {
  return xs.reduce(function (rv, x) {
    (rv[x[key] || "default"] = rv[x[key] || "default"] || []).push(x);
    return rv;
  }, {});
};

const stateId = ref(null);

const error = ref(null);
const customerstyle = ref(null);
const loaded = ref(false);

const currentLocale = ref("en-GB");

const languages = ref([]);

const in_designer = ref(false);

const css = ref(null); //'body .login-box .login-container{border-radius: 0px!important;}'

const baseURL = location.protocol + "//" + window.location.hostname;
const router = useRouter();
const route = useRoute();
const http = axios.create({
  baseURL: baseURL,
});

onMounted(() => {
  // load the state from the hash
  in_designer.value = queryString.parse(location.search).designer != null;

  if (!in_designer.value) {
    loadFromHash();
  }

  http
    .get(
      `/api/uiSettings?version=${encodeURIComponent(
        window.information.resources_version
      )}`
    )
    .then(
      (response) => {
        customerstyle.value = setDefaults(response.data);

        if (inIframe()) {
          customerstyle.value.container_backgroundColor = "rgba(0,0,0,0.5)";
          customerstyle.value.container_backgroundImage = null;
        }
      },
      (response) => {
        customerstyle.value = {};
      }
    );

  window.addEventListener("message", (event) => {
    if (event.data && event.data.type == "refresh_state") {
      loadStateId(stateId);
    }
  });
  
  window.addEventListener("message", (event) => {
    if (event.data && event.data.type == "set_style") {
      customerstyle.value = Object.assign(
        {},
        customerstyle.value,
        event.data.style
      );
    }
  });

  // Used for the user interface designer.
  if (inIframe()) {
    loaded.value = true;

    window.onhashchange = () => {
      loadFromHash();
    };

    window.addEventListener(
      "message",
      (event) => {
        const allowedOrigin = window.information.manage;

        if (!event.data || event.origin != allowedOrigin) {
          return;
        }

        if (event.data.authRequest) {
          authRequest.value = event.data.authRequest;
          activeModule.value = null;
        }
      },
      false
    );
  }
});

function closePopup(event) {
  if (
    event.target.id == "idaas-container" ||
    event.target.className.includes("login-row")
  ) {
    window.parent.postMessage(
      {
        type: "close_popup",
      },
      authRequest.value.info.ser.redirectionUrls[0].match(/(.{8}.*?)(\/|$)/)[1]
    );
  }
}

function setDefaults(customerStyle) {
  return Object.assign(
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
      label_display: "hidden",
    },
    customerStyle
  );
}

function storeStateId(stateId) {
  var key = Math.random().toString(36).substring(3);
  sessionStorage.setItem(key, stateId);

  return key;
}

function inIframe() {
  try {
    return window.self !== window.top;
  } catch (e) {
    return true;
  }
}

function loadStateId(id) {
  stateId.value = id;

  http
    .get(
      "/api/authchain/v2/p/authresponse/" +
        stateId.value +
        "?t=" +
        new Date().getTime()
    )
    .then((response) => {
      console.log('load response dat2a...');

      state.authRequest = response.data;
      // state.authRequest = response.data;

      //TODO: preferably redirect the user immediatly, without entering the login ui
      if (state.authRequest.info.don) {
        post(state.authRequest.info.fin, {
          authRequest: state.authRequest.stateId,
        });
      }

      loaded.value = true;
    })
    .catch((response) => {
      router.replace({ name: "error" });
      loaded.value = true;
    });
}

function loadFromHash() {

  // https://login.notidaas.nl#state=9a70994f-edd7-4bcf-9779-89d004381c97

  const parsed = queryString.parse(location.hash);

  //ugly workaround to make it work in hash-mode
  if (parsed["/state"]) {
    parsed.state = parsed["/state"];
  }

  var state = null;

  if (parsed.state) {
    state = parsed.state;

    var key = storeStateId(parsed.state);

    console.log('replace with login route');
    router.replace({
      name: "login",
      params: {
        hash: key,
      },
    }); //module: 'passport'
  } else {
    state = sessionStorage.getItem(route.params.hash);
  }

  if (state) {
    loadStateId(state);
  } else {
    // TODO: do not init default when in UI dev mode
    document.location = "/init_default";
  }
}

const { authRequest, activeModule } = storeToRefs(state);

watch(authRequest, function (val) {
  console.log('authRequest updated ...');
  // TODO: if in iFrame, post to parent ... but to what URL? Or broadcast "get style" and only accept if state is correct?
  if (inIframe()) {
    // TODO: rename argument redirectionUrls to something like "uiUrls"
    // Pick the first one. Always. This is the 'primary ui'. The other one is the tenant's ui.
    window.parent.postMessage(
      {
        type: "get_style",
      },
      val.info.ser.redirectionUrls[0].match(/(.{8}.*?)(\/|$)/)[1]
    );
  }

  if (val.info.inc) {
    if (val.info.inc.messages) {
      for (let message of val.info.inc.messages) {
        state.info(message.message);
      }
    }
    console.log('update active module!');
    console.log('activeModule: ' + val.info.inc.module);
    activeModule.value = val.info.inc.module;
  } else if (val.next != null && val.next.length == 1) {
    activeModule.value = val.next[0].id;
  } else if (val.next != null) {
    var match = false;
    for (var n of val.next) {
      if (n.id == route.params.module) {
        match = true;
        break;
      }
    }

    if (!match) {
      console.log('activeModule to null');
      activeModule.value = null;
    }
  } else {
    console.log('activeModule to null. ...');
    activeModule.value = null;
  }
}, {deep: true});

// $route(to, from) {
//   state.alert = "none";
// }

const next = computed(function () {
  return authRequest.value && authRequest.value.next
    ? groupBy(
        authRequest.value.next
          ? authRequest.value.next.sort((a, b) => {
              // Ensure registration is presented last
              if (a.group == "register" && b.group != "register") {
                return 1;
              }

              if (a.group == null && b.group != null) {
                return -1;
              }

              //Ensure the password field is first
              if (a.type != "password" && b.type == "password") {
                return 1;
              }

              return -1;
            })
          : [],
        "group"
      )
    : [];
});

// export default {
//   components: {

//     "v-style": {
//       render: function(createElement) {
//         return createElement("style", this.$slots.default);
//       }
//     }
//   },

// };

const VStyle = h("style", {
  innerHTML: customerstyle.value != null ? customerstyle.value["css"] : null,
});
</script>

<style lang="scss">
@import "node_modules/bootstrap/scss/bootstrap";

body .language-selector.btn-group {
  .btn-primary:focus,
  .btn-primary.focus {
    box-shadow: none;
  }

  background-color: transparent;
  border-style: none;
  box-shadow: none;
  outline: none;

  .btn-primary:not(:disabled):not(.disabled):active,
  .btn-primary:not(:disabled):not(.disabled).active,
  .show > .btn-primary.dropdown-toggle {
    background-color: transparent;
    border-style: none;
    box-shadow: none;
    outline: none;
  }

  .btn,
  > .btn:hover,
  > .btn:focus,
  > .btn:active,
  > .btn.active {
    background-color: transparent;
    border-style: none !important;
  }
}

html {
  font-size: 100%;
}

@include media-breakpoint-up(sm) {
  html body .login-box .login-container {
    padding: 40px;
  }
}

body {
  background-color: transparent;
  font-family: "Helvetica Neue", Arial, sans-serif;

  // background-color: var(--main-bg-color);

  .btn-secondary {
    text-align: left;

    i,
    svg {
      margin-right: 10px;
    }
  }

  nav.navbar {
    padding-top: 0px;
    padding-bottom: 0px;

    img {
      max-height: 100%;
    }
  }

  .login-row {
    margin: 0px;
    flex: 1;
  }

  .container {
    width: 100vw;
    min-height: 100%;
    max-width: 100vw;
    padding: 0px;
    margin: 0px;
    display: flex;
    flex-direction: column;
    background-size: cover;

    nav {
      height: 50px;
      max-height: 50px;
      flex: 1;
    }
  }

  .login-box {
    max-width: 500px;

    .login-container {
      background-color: white;
      border-radius: 10px;
      box-shadow: rgba(0, 0, 0, 0.1) 0px 3px 20px 0px;

      @include media-breakpoint-up(md) {
        padding: 40px;
      }

      padding: 40px;
      padding-left: 10px;
      padding-right: 10px;

      &.alert-info {
        background-color: rgb(205, 231, 255);
      }

      h1 {
        font-family: OpenSans-Regular, "Lucida Sans", "Lucida Sans Regular",
          "Lucida Grande", "Lucida Sans Unicode", Geneva, Verdana, sans-serif;
        color: #212121;
        font-size: 1.5rem;
        text-align: center;
        margin-bottom: 30px;
      }

      .large-form-items {
        .panel-heading {
          display: none;
        }

        .form-group,
        input {
          margin: 0px;
          padding: 0px;
          border-radius: 0px;
          margin-bottom: -1px; // ensure overlapping with next, if labels are hidden
        }

        input {
          line-height: 1.2;
          height: 68px;
          padding: 0 25px 0 25px;
          font-size: 1rem;
          border: 1px solid #e6e6e6;
        }

        .btn-primary {
          border-radius: 0px;
          background-color: #4272d7;
          border-color: rgba(0, 0, 0, 0.2);
          text-transform: uppercase;
          margin-top: 20px;
          height: 68px;
          font-size: 1rem;
        }

        .form-check {
          padding-left: 0px;
          margin-top: 10px;
          display: flex;
          align-items: center;

          input {
            height: unset;
            margin-top: 0px;
            padding-top: 0px;
          }

          label {
            display: unset;
            font-size: 1rem;
            margin-left: 20px;
          }
        }
      }
    }
  }
}

body .noty_layout#noty_layout__topCenter {
  width: 600px;
  max-width: 100%;
}

.logo {
  width: 100px;
  height: 100px;
  background-size: contain;
  border-style: solid;
  border-color: #e6e6e6;
  border-width: 2px;
  background-repeat: no-repeat;
  background-position: center center;
  background-color: #ffffff;
  border-radius: 50%;
  margin-bottom: 20px;
}

.hr-text {
  line-height: 1em;
  position: relative;
  outline: 0;
  border: 0;
  color: black;
  text-align: center;
  height: 1.5em;
  opacity: 0.5;

  &:before {
    content: "";
    // use the linear-gradient for the fading effect
    // use a solid background color for a solid bar
    background: linear-gradient(to right, transparent, #818078, transparent);
    position: absolute;
    left: 0;
    top: 50%;
    width: 100%;
    height: 1px;
  }

  &:after {
    content: attr(data-content);
    position: relative;
    display: inline-block;
    color: black;

    padding: 0 0.5em;
    line-height: 1.5em;
    // this is really the only tricky part, you need to specify the background color of the container element...
    color: #818078;
    background-color: #fcfcfa;
  }
}
</style>
