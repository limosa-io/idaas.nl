<template>
  <div style="height: 100vh" v-show="loaded">
    <div
      id="idaas-container"
      @click="closePopup"
      v-if="authRequest != null && customerstyle != null && authRequest.next"
      class="container"
      :style="{backgroundColor: customerstyle['container_backgroundColor'], 'background-image': `url(${customerstyle['container_backgroundImage']})`}"
      @
    >
      <nav
        class="navbar navbar-dark pt-1 pb-1 justify-content-between align-items-stretch"
        :style="{backgroundColor: customerstyle['navbar_backgroundColor']}"
        v-if="customerstyle && (customerstyle['navbar_show'] || 'show') == 'show'"
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
        >{{ customerstyle.title }}</a>
        <div class="d-flex align-items-center">
          <b-dropdown
            v-if="customerstyle.languages && customerstyle.languages.length > 0"
            right
            :text="$t('general.locale.' + $i18n.locale)"
            variant="primary"
            class="language-selector"
          >
            <b-dropdown-item
              href="#"
              @click.prevent="switchLanguage(l)"
              v-for="(l, index) in customerstyle.languages"
              :key="index"
            >
              {{
              $t('general.locale.' + l) }}
            </b-dropdown-item>
          </b-dropdown>
        </div>
      </nav>
      <nav v-else class="navbar justify-content-end align-items-center">
        <b-dropdown
          v-if="customerstyle.languages && customerstyle.languages.length > 0"
          right
          :text="$t('general.locale.' + $i18n.locale)"
          variant="primary"
          class="language-selector"
        >
          <b-dropdown-item
            href="#"
            @click.prevent="switchLanguage(l)"
            v-for="(l, index) in customerstyle.languages"
            :key="index"
          >
            {{
            $t('general.locale.' + l) }}
          </b-dropdown-item>
        </b-dropdown>
      </nav>

      <router-view :authRequest="authRequest" :customerstyle="customerstyle" :next="next"></router-view>
    </div>

    <div class="container" v-else-if="authRequest == null">
      <!-- || !authRequest.info.don -->

      <div class="justify-content-center align-items-center row login-row">
        <!-- You're on the login page, but we're not sure where you want to go to next ... -->
      </div>
    </div>

    <v-style v-if="customerstyle">{{ customerstyle['css'] }}</v-style>
  </div>
</template>

<script>
import { EventBus } from "./eventBus.js";
import Vue from "vue";
import store from "./store";

import {BDropdown, BDropdownItem} from "bootstrap-vue";

import { switchLanguage } from "@/login/i18n.js";

import queryString from 'query-string';

var groupBy = function(xs, key) {
  return xs.reduce(function(rv, x) {
    (rv[x[key] || "default"] = rv[x[key] || "default"] || []).push(x);
    return rv;
  }, {});
};

export default {
  components: {
    BDropdown,
    BDropdownItem,

    "v-style": {
      render: function(createElement) {
        return createElement("style", this.$slots.default);
      }
    }
  },

  data() {
    return {
      state: null,

      error: null,
      customerstyle: null,
      loaded: false,

      currentLocale: "en-GB",

      languages: [],

      in_designer: false,

      css: null //'body .login-box .login-container{border-radius: 0px!important;}'
    };
  },

  mounted() {
    // load the state from the hash
    this.in_designer = queryString.parse(location.search).designer != null;

    if (!this.in_designer) {
      this.loadFromHash();
    }

    this.$get(
      `/api/uiSettings?version=${encodeURIComponent(
        window.information.resources_version
      )}`
    ).then(
      response => {
        this.customerstyle = this.setDefaults(response.data);

        if(this.inIframe()){
          this.customerstyle.container_backgroundColor = "rgba(0,0,0,0.5)";
          this.customerstyle.container_backgroundImage = null;
        }

      },
      response => {
        this.customerstyle = {};
      }
    );

    window.addEventListener("message", () => {
        if(event.data && event.data.type == 'refresh_state'){
          this.loadStateId(this.state);
        }
    });

    window.addEventListener("message", () => {
        if(event.data && event.data.type == 'set_style'){
          this.customerstyle = Object.assign({}, this.customerstyle, event.data.style);
        }
    });

    // Used for the user interface designer.
    if (this.inIframe()) {
      this.loaded = true;

      window.onhashchange = () => {
        this.loadFromHash();
      };

      window.addEventListener(
        "message",
        event => {
          const allowedOrigin = window.information.manage;

          if (!event.data || event.origin != allowedOrigin) {
            return;
          }

          if (event.data.authRequest) {
            store.commit("authrequest", event.data.authRequest);
            store.commit("activeModule", null);
          }
        },
        false
      );
    }
  },

  watch: {

    activeModule: function(val) {
      this.$router.push({
        name: "login",
        params: {
          hash: this.$route.params.hash,
          module: val
        }
      });
    },

    authRequest: function(val) {

      // TODO: if in iFrame, post to parent ... but to what URL? Or broadcast "get style" and only accept if state is correct?
      if(this.inIframe()){
        // TODO: rename argument redirectionUrls to something like "uiUrls"
        // Pick the first one. Always. This is the 'primary ui'. The other one is the tenant's ui.
        window.parent.postMessage(
          {
            type: 'get_style'
          },
          val.info.ser.redirectionUrls[0].match(/(.{8}.*?)(\/|$)/)[1]
        );
      
      }

      if (val.info.inc) {
        if (val.info.inc.messages) {
          for (let message of val.info.inc.messages) {
            this.$noty({
              text: message.message
            });
          }
        }

        this.$store.commit("activeModule", val.info.inc.module);
      } else if (val.next != null && val.next.length == 1) {
        store.commit("activeModule", val.next[0].id);
      } else if (val.next != null) {
        var match = false;
        for (var n of val.next) {
          if (n.id == this.$route.params.module) {
            match = true;
            break;
          }
        }

        if (!match) {
          this.$store.commit("activeModule", null);
        }
      } else {
        this.$store.commit("activeModule", null);
      }
    },

    $route(to, from) {
      this.$store.commit("alert", "none");
    }
  },

  computed: {
    authRequest: function() {
      return store.state.authrequest;
    },

    activeModule: function() {
      return store.state.activeModule;
    },

    next: function() {
      return this.authRequest && this.authRequest.next
        ? groupBy(
            this.authRequest.next
              ? this.authRequest.next.sort((a, b) => {
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
    }
  },

  methods: {
    closePopup(event){
      if(event.target.id == 'idaas-container' || event.target.className.includes('login-row')){
        window.parent.postMessage(
          {
            type: 'close_popup'
          },
          this.authRequest.info.ser.redirectionUrls[0].match(/(.{8}.*?)(\/|$)/)[1]
        );

      }
    },

    setDefaults(customerStyle) {
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
          label_display: "hidden"
        },
        customerStyle
      );
    },

    switchLanguage(l) {
      switchLanguage(l);
    },

    storeStateId(stateId) {
      var key = Math.random()
        .toString(36)
        .substring(3);
      sessionStorage.setItem(key, stateId);

      return key;
    },

    inIframe() {
      try {
        return window.self !== window.top;
      } catch (e) {
        return true;
      }
    },

    loadStateId(state) {

      this.state = state;
      
      this.$get("/api/authchain/v2/p/authresponse/" + state + '?t=' + (new Date().getTime()))
        .then(response => {

          store.commit("authrequest", response.data);

          //TODO: preferably redirect the user immediatly, without entering the login ui
          if (this.authRequest.info.don) {
            this.$post(this.authRequest.info.fin, {
              authRequest: this.authRequest.stateId
            });
          }

          this.loaded = true;
        })
        .catch(response => {
          this.$router.replace({ name: "error" });
          this.loaded = true;
        });
    },

    loadFromHash() {
      const parsed = queryString.parse(location.hash);

      //ugly workaround to make it work in hash-mode
      if (parsed["/state"]) {
        parsed.state = parsed["/state"];
      }

      var state = null;

      if (parsed.state) {
        state = parsed.state;

        var key = this.storeStateId(parsed.state);

        this.$router.replace({
          name: "login",
          params: {
            hash: key
          }
        }); //module: 'passport'
      } else {
        state = sessionStorage.getItem(this.$route.params.hash);
      }

      if (state) {
        this.loadStateId(state);
      } else {
        // TODO: do not init default when in UI dev mode
        document.location = "/init_default";
      }
    }
  }
};
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
