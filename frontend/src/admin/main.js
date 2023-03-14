import Vue from 'vue'
import VueResource from 'vue-resource';
import App from './App.vue'
import router from './router'
import { BModal } from 'bootstrap-vue'
import { VBModal } from 'bootstrap-vue'
import {VBTooltip, BPagination} from 'bootstrap-vue';
import {BFormGroup} from './bootstrap-vue'
import state from './components/state.js'
import Button from "@/admin/components/general/Button.vue";
import Main from "@/admin/components/general/Main.vue";

Vue.use(VueResource);

Vue.component(
  'multiselect',
  () => import(/* webpackChunkName: "vue-multiselect" */ 'vue-multiselect')
);

Vue.component(
  'vue-croppie',
  () => import(/* webpackChunkName: "vue-croppie" */ './components/general/Croppie.vue')
);

Vue.component(
  'b-modal', BModal
);

Vue.component(
  'Button', Button
);

Vue.component(
  'Main', Main
);

Vue.directive('b-modal', VBModal);
Vue.component('b-form-group', BFormGroup); 
Vue.component('b-form-select', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormSelect); }));

Vue.component('b-form-input', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormInput); }));
Vue.component('b-form-textarea', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormTextarea); }));

Vue.component('b-form-radio-group', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormRadioGroup); }));
Vue.component('b-form-checkbox', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormCheckbox); }));
Vue.component('b-form-radio', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormRadio); }));
Vue.component('b-form-checkbox-group', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BFormCheckboxGroup); }));
Vue.component('b-dropdown', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BDropdown); }));
Vue.component('b-dropdown-item', (resolve) => import(/* webpackChunkName: "vue-bootstrap" */ './bootstrap-vue').then((m) => { resolve(m.BDropdownItem); }));
Vue.component('b-pagination', BPagination);


Vue.directive('b-tooltip', VBTooltip);

Vue.component('b-badge', (resolve) => import(/* webpackChunkName: "vue-bootstrap-toolip" */ 'bootstrap-vue/esm/components/badge/badge').then((m) => { resolve(m); }));

var access_token = null;

const resolvers = [];

Vue.http.options.credentials = true;

function refreshAndRetry(response, request){

  return new Promise(r => { 

    resolvers.push( (retry = true) => {
      
      // Check if retry is needed. Retry only once.
      if(retry){

        if(request.method.toLowerCase() == 'post'){
          r(Vue.http.post(request.url, request.body, {
            headers: request.headers,
            noRetry: true
          }));
        }else if(request.method.toLowerCase() == 'get'){
          r(Vue.http.get(request.url, {
            headers: request.headers,
            noRetry: true
          } ));
        }else if(request.method.toLowerCase() == 'delete'){
          r(Vue.http.delete(request.url, {
            headers: request.headers,
            noRetry: true
          }));
        }else if(request.method.toLowerCase() == 'patch'){
          r(Vue.http.patch(request.url, request.body, {
            headers: request.headers,
            noRetry: true
          }));
        }else if(request.method.toLowerCase() == 'put'){
          r(Vue.http.put(request.url, request.body, {
            headers: request.headers,
            noRetry: true
          }));
        }

      }else{
        r(response);
      }

    });

    // If the resolver length == 1, we have not requested refreshing the token
    if(resolvers.length == 1){

      let r = null;
      Vue.http.post(window.manageUrls.oidc + '/token', {
        grant_type: 'refresh_token',
        refresh_token: window.sessionStorage.getItem('refresh_token'),
        client_id: window.manageClient.clientId
      }, {
        public: true,
        noRetry: true
      }).then(response => {

        window.sessionStorage.setItem('access_token', response.body.access_token);
        window.sessionStorage.setItem('refresh_token', response.body.refresh_token)        
        access_token = response.body.access_token;

        while( (r = resolvers.pop()) !== undefined){
          r();
        }
    
      }).catch(response => {
        
        // We could not refresh the token, therefore, return the original response. That is, do not retry the request
        while( (r = resolvers.pop()) !== undefined){
          r(false);
        }

      });

    }
    
  });

}

async function refreshToken(response, request){

  let r = await refreshAndRetry(response, request);
  return r;

}

Vue.http.interceptors.push(request => {

  // Ensure the authorization header is only send to trusted resource servers        
  if (!request.public && (request.url.indexOf(window.manageUrls.oidc) == 0 || request.url.indexOf(window.manageUrls.manage) == 0)) {
    request.headers.set('Authorization', 'Bearer ' + (access_token || (access_token = window.sessionStorage.getItem('access_token'))));
  }
  return (response) => {
    
    // wait for refreshing to be done ...
    if (!request.noRetry && !request.public && response.status == 401) {
      return refreshToken(response, request);
    }

    if ((!response.ok && response.status == 0) || response.status == 401 || response.status == 403) {
      state.apiResponse = response;
    }
  };

});

// Set a proper title
router.beforeEach((to, from, next) => {
  document.title = 'idaas.nl - ' + ((to.meta ? to.meta.label : null) || to.name || 'home')
  next()
})

new Vue({
  router,
  render: createEle => createEle(App)
}).$mount('#app');

//export {router};
export default App;
