<template>
  <div>
    <div
      v-on:transitionend="height = '0%'"
      id="progressbar"
      ref="progressbar"
      :style="{ width: width, height: height }"
    ></div>
    <Transition>
      <div class="notification" :class="[notification_type]" v-if="show_notify">
        {{ notification }}
      </div>
    </Transition>
    <router-view></router-view>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";

import {useStateStore} from "./components/store";
import { storeToRefs } from 'pinia'

const width = ref("0%");
const height = ref("0%");
const showProgress = ref(false);



const state = useStateStore();
const { show_notify, notification, notification_type } = storeToRefs(state);


import { i18n } from "./i18n.js";

onMounted(() => {
  // Vue.http.interceptors.push((request, next) => {
  //   request.headers.set("Accept-Language", i18n.global.locale);
  //   this.width = "40%";
  //   this.height = "3px";
  //   showProgress.value = true;
  //   next((response) => {
  //     this.width = "100%";
  //   });
  // });
});
</script>

<style lang="scss">
@import "node_modules/bootstrap/scss/bootstrap";

.pre-wrap {
  white-space: pre-wrap;
  line-height: 2rem;
}

#progressbar {
  position: fixed;
  display: block;
  background-color: rgb(59, 206, 236);
  z-index: 2000;

  transition-timing-function: ease-in;
  transition: width 0.5s, height 0.5s;
}

body,
#app {
  width: 100vw;
  min-height: 100vh;
  margin: 0px;
  padding: 0px;
}

.btn-loading {
  span {
    color: transparent;
    float: left;
  }

  &:after {
    content: " ";
    display: block;
    width: 20px;
    position: relative;
    left: 50%;
    height: 20px;
    margin: 1px;
    margin-left: -10px;
    border-radius: 50%;
    border: 5px solid #fff;
    border-color: #fff transparent #fff transparent;
    animation: lds-dual-ring 0.6s linear infinite;
  }
}

@keyframes lds-dual-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.notification {
  position: fixed;
  top: 10px;
  right: 10px;
  width: min(450px, 100%);
  padding: 10px;
  background-color: #519beb;
  border-style: solid;
  border-width: 1px;
  border-color: #ccc;
  z-index: 99;
  color: white;
}

.notification.error{
  background-color: #eb5e51;
}

.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

</style>
