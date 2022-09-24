<template>
  <div id="app">
    <div v-on:transitionend="height = '0%'" id="progressbar" ref="progressbar" :style="{width: width, height: height}" ></div>
   <router-view></router-view>
  </div>
</template>

<script>
import Vue from 'vue'


import { i18n } from './i18n.js'

export default {

  data() {
    return {
      width: '0%',
      height: '0%',
      showProgress: false
    }
  },

  mounted() {

    Vue.http.interceptors.push((request, next) => {

      request.headers.set('Accept-Language', i18n.locale);

      this.width = '40%';
      this.height = '3px';
      this.showProgress = true;

      next((response) => {
        this.width = '100%';
      });
    });

  }
}

</script>

<style lang="scss">

@import "node_modules/bootstrap/scss/bootstrap";

.pre-wrap{
  white-space: pre-wrap;
  line-height: 2rem;
}


#progressbar{
  position: fixed;
  display: block;
  background-color: rgb(59, 206, 236);
  z-index: 2000;

  transition-timing-function: ease-in;
  transition: width 0.5s, height 0.5s;

  
}

body, #app{
    width: 100vw;
    min-height: 100vh;
    margin: 0px;
    padding: 0px;
}

.btn-loading{
  
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



</style>
