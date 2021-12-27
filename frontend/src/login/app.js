/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './public-path';
import Vue from 'vue'
import VueRouter from 'vue-router'

import App from './App.vue'
import Login from './components/Login'
import Selection from './components/Selection'
import Logout from './components/Logout'
import Error from './components/Error'
import store from './components/store';
import {i18n} from './i18n'

import Session from './components/Session'

Vue.use(VueRouter)

const routes = [
  
  {
    path: '/oauth/logout',
    name: 'logout',
    component: Logout
  },
  {
    path: '/oauth/authorize',
    name: 'error',
    component: Error
  },
  {
    path: '/saml/v2/login',
    name: 'error.saml',
    component: Error
  }, 
  {
    path: '/error',
    name: 'error.default',
    component: Error
  }, 
  {
    path: '/session',
    name: 'session',
    component: Session
  }, 
  {
  path: '/',
  component: Login,

  children: [

    {
      path: ':hash?/register/:module?',
      name: 'login.register',
      component: () =>
        import ('./components/Register')
    },

    {
      path: '/:hash?/:module?',
      name: 'login',
      component: Selection
    }
  ]

}];

const router = new VueRouter({
  routes: routes,
  mode: 'history'
});

//TODO: Ensure you get the url of the log in server!
const baseURL = location.protocol + '//' + window.location.hostname;

Vue.mixin({
  methods: {

    $noty: function (properties) {

      properties.timeout = 5000;
      properties.animation = {};
      properties.layout = 'topCenter';
      properties.type = 'error';
      properties.theme = 'bootstrap-v4';
      properties.progressBar = true;

      properties.closeWith = ['click', 'button'];


      import ( /* webpackChunkName: "noty" */ 'noty').then(module => {
        new module.default(properties).show();
      });

    },

    b64DecodeUnicode: function (str) {
      return decodeURIComponent(
        Array.prototype.map
        .call(atob(str), function (c) {
          return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
        })
        .join("")
      );
    },
    
    $updateAuthRequest(authRequestRaw){

      var authRequest =  JSON.parse(this.b64DecodeUnicode(authRequestRaw));

      if (authRequest && authRequest.info.don) {

        this.$post(authRequest.info.fin, {
          'authRequest': authRequest.stateId
        });

      } else if (authRequest.next == null) {
        store.commit('authrequest', authRequest);
      } else {
        store.commit('authrequest', authRequest);
      }

    },

    $get(path) {
      return this.$http.get(baseURL + path);
    },

    $post(path, params, method) {

      var method = method || "post"; 

      var form = document.createElement("form");
      form.setAttribute("method", method);
      form.setAttribute("action", path);

      for (var key in params) {
        if (params.hasOwnProperty(key)) {
          var hiddenField = document.createElement("input");
          hiddenField.setAttribute("type", "hidden");
          hiddenField.setAttribute("name", key);
          hiddenField.setAttribute("value", params[key]);

          form.appendChild(hiddenField);
        }
      }

      document.body.appendChild(form);
      form.submit();

    },

    $ice: function (module, authRequest, data) {

      return new Promise((resolve, reject) => {
        
        if (!authRequest || !authRequest.stateId) {
          reject('invalid state');
        }

        this.$http.post(authRequest.info.api.replace('NAME_OF_THE_MODULE', typeof module === 'object' ? module.id : module), data, {
          headers: {
            "X-StateId": authRequest.stateId
          }
        }).then(
          response => {
            
            this.$updateAuthRequest(response.headers.get("x-authrequest"));

            resolve(response);

          }
          
        ).catch(response => {
                    
            // error callback
            if(response.status == 403){
              this.$noty({
                text: "You don't have permissions to access this app."
              });
            }else if(response.status == 404){
              document.location = this.authRequest.info.nok;
            }

            this.$updateAuthRequest(response.headers.get("x-authrequest"));
            reject(response);
          
        });
      });
    }

  }

});

new Vue({
  router,
  store,
  i18n,
  render: createEle => createEle(App)
}).$mount('#app');

export default App;