
<template>
  <div :class="{ 'is-collapsed': isCollapsed }" @click="isCollapsed = false">
    <div v-on:transitionend="progressBarEnd" :class="{'with_animations': withAnimations}" id="progressbar" ref="progressbar" :style="{width: width, height: height}" ></div>

    <Modal :no-close-on-esc="true" :no-close-on-backdrop="true" :hide-header-close="true" @ok="doOAuth" :ok-only="true" header-bg-variant="danger" header-text-variant="light" ref="notAuthenticatedModal" id="modal1" title="Authentication required">
        <p class="my-4">You need to authenticate yourself!</p>
    </Modal>

    <Modal :no-close-on-esc="true" :no-close-on-backdrop="true" :hide-header-close="true" @ok="doLogout" :ok-only="true" header-bg-variant="danger" header-text-variant="light" ref="notPermissionsModal" id="modalPrivileges" title="Insufficient privileges">
        <p class="my-4">You do not have sufficient privileges! Contact the administrator to provide you with the required roles.</p>
    </Modal>

    <Modal @ok="doOAuth" :ok-only="true" header-bg-variant="danger" header-text-variant="light" ref="seriousProblems" id="modal1" title="Serious errors">
        <p class="my-4">CORS problems?</p>
    </Modal>
    
    <div  v-if="isLoaded">

      <sidebar @click.stop v-on:toggle-sidebar="isCollapsed = !isCollapsed"></sidebar>

      <div class="page-container">
        
        <TopBar v-if="userinfo" v-on:toggle-sidebar="isCollapsed = !isCollapsed" :userinfo="userinfo"></TopBar>

        <MainTemplate class='main-content bgc-grey-100'>
          
            <router-view></router-view>          
          
        </MainTemplate>

        <footer class="text-center p-3 c-grey-600">
          <span>Copyright © 2023 a11n. All rights reserved.</span>
        </footer>
      </div>
    </div>

  </div>
</template>


<script setup>
//FIXME: rewrite to Vue3
import '../assets/styles/index.scss';

import sidebar from './Sidebar.vue'
import TopBar from './TopBar.vue'
import state from './state.js'

import {laxios, maxios} from '@/admin/helpers.js'

import {ref, onMounted, onUnmounted, getCurrentInstance} from 'vue';
import Modal from '@/admin/components/general/Modal.vue'
import { useRouter, useRoute } from 'vue-router4'
import { getAccessToken } from '../helpers';

const router = useRouter()

const vue = getCurrentInstance();

const width = ref('0%');
const height = ref('0%');
const withAnimations = ref(true);
const isCollapsed = ref(false);
const showDismissibleAlert = ref(true);
const userinfo = ref(null);
const isLoaded = ref(false);
const oauth = ref({
  accessToken: null
});

onMounted(() => {
  console.log('mounted!!');
  if(getAccessToken() == null){
    doOAuth();
  }else{
    getUserInfo();
  }

  ping().then(response => {
    isLoaded.value = true;
      }, response => {
        // error callback
        isLoaded.value = false;
      });
});


function getUserInfo() {

      laxios.get('oauth/userinfo').then(response => {
        userinfo.value = response.data;
      }, response => {
        router.replace({name: 'error.default'});
      });

}

function doOAuth() {
  router.push({
    name: 'initlogin'
  });
}

function doLogout() {
  router.push({
    name: 'initlogout'
  });
}

function ping() {

  return maxios.get('api/ping');

}



// progressBarEnd(event) {

// if (event.propertyName == 'width' && this.width == '100%') {
//   this.height = '0px';
// }else if (event.propertyName == 'height' && this.height == '0px'){
//   this.withAnimations = false;        
//   this.width = '0%';
// }

// },

// interceptor(request, next) {

// // increase request

// clearTimeout(timeout);

// this.height = '3px';

// this.withAnimations = true;

// this.width = Math.max(40, (parseInt(this.width) * 1.1)) + '%';      

// return (response) => {
  
//   this.width = '100%';

//   //TODO: If 404, return to ...??
//   return !request.noRetry ? this.processAPIResponse(response, request) : response;

// };

// },



// processAPIResponse(response, request) {
// // return this.refreshAndRetry(response, request);

// if ((!response.ok && response.status == 0) || response.status == 401) {

//   // // IF has not tried to refresh already
//   // if(window.sessionStorage.getItem('refresh_token') != null && !request.noRetry){
    
//   //   // refresh the token and retry the original request
//   //   return this.refreshAndRetry(response, request);

    
//   // }else{
    
//   // }

//   this.startOAuth();

// } else if (response.status == 403) {
//   this.startMissingPermissions();
// }

// return response;

// },


//     Vue.http.interceptors.push(this.interceptor);

// var timeout = null;

// export default {

//   name: 'App',

//   created() {


//   },

//   mounted: function () {

//     /**
//      * workaround to deal with api responses returned before a component was mounted
//      */
//     if (state.apiResponse) {
//       this.processAPIResponse(state.apiResponse, null);
//     }

//     if (this.getAccessToken() == null) {
//       this.startOAuth();
//     } else {

//       this.getUserInfo();

//       this.ping().then(response => {
//         this.isLoaded = true;
//       }, response => {
//         // error callback
//         this.isLoaded = false;
//       });

//     }

//   },

//   methods: {

//     getAccessToken() {
//       return this.oauth.accessToken || (this.oauth.accessToken = window.sessionStorage.getItem('access_token'));
//     },

//     startMissingPermissions() {
//       this.$refs.notPermissionsModal.show();
//     },

//     startOAuth() {
//       this.doOAuth();
//     },

//     doOAuth() {
//       this.$router.push({
//         name: 'initlogin'
//       });
//     },

//     doLogout() {
//       this.$router.push({
//         name: 'initlogout'
//       });
//     },

//     getUserInfo() {

//       this.$http.get(this.$oidcUrl('oauth/userinfo')).then(response => {
//         this.userinfo = response.data;
//       }, response => {
//         this.$router.replace({name: 'error.default'});
//       });

//     },

//     ping() {

//       return this.$http.get(this.$murl('api/ping'));

//     },

//   },

//   components: {
//     'sidebar': sidebar,
//     'topbar': topBar
//   }

// }
</script>

<style lang="scss">

.noty_theme__mint.noty_type__success {
  background-color: #4285f4;
  border-bottom: 1px solid darken(#4285f4, 40%)
}
body #noty_layout__bottomCenter{
  width: 500px;
  max-width: 100%;
  text-align: center;
}

#progressbar{
  position: fixed;
  display: block;
  background-color: rgb(40, 210, 74);
  z-index: 2000;

  transition: none;  
  transition-timing-function: unset;

  &.with_animations{
    transition-timing-function: ease-in;
    transition: width 1.0s, height 0.2s;
  }

  
}

</style>
