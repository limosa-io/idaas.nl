
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
        doLogout();

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
