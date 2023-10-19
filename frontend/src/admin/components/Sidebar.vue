<template>

<div class="sidebar">
  <div class="sidebar-inner">
    <!-- ### $Sidebar Header ### -->
    <div class="sidebar-logo">
      <div class="peers ai-c fxw-nw">
        <div class="peer peer-greed">
          <router-link class="sidebar-link td-n" to="/">
            <div class="peers ai-c fxw-nw">
              <div class="peer">
                <div class="logo p-1">
                  <img src="../assets/static/images/bulb.svg" width="50" style="margin-left: 6px; margin-top: 3px; padding: 12px;"  />
                </div>
              </div>
              <div class="peer peer-greed">
                <h5 class="lh-1 mb-0 logo-text ml-1">idaas.nl</h5>
              </div>
            </div>
          </router-link>
        </div>
        <div class="peer">
          <div class="mobile-toggle sidebar-toggle">
            <a @click="$emit('toggle-sidebar')" class="td-n">
              <i class="ti-arrow-circle-left"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ### $Sidebar Menu ### -->
    <ul class="sidebar-menu scrollable pos-r">

      <!-- add class "dropdown open" if has children -->
      <template v-for="(r, index) in router.options.routes[0].children">
        <li class="nav-item" :key="index" :class="{'mt-3': index == 0, 'dropdown': (r.children != null && !r.hideChildren), 'open': (r.children != null )  }" v-if="!r.hide" >
          
          <!-- class="sidebar-link" -->
          <router-link :to="r.path" class="sidebar-link" :exact="true" active-class="active"> 

            <span class="icon-holder">
              <i v-if="r.style" :class="[ r.style.icon ]"></i>
            </span>
            <span class="title">{{ (r.meta ? r.meta.label : null) || r.name }}</span>

            <span v-if="r.children && (!r.meta || !r.meta.hideChildren)" class="arrow"><i class="ti-angle-right"></i></span>

          </router-link>

          <ul v-if="r.children && (!r.meta || !r.meta.hideChildren)" class="dropdown-menu">
            <template v-for="(child,index) in r.children">
              <li :key="index" v-if="!child.hide" class="nav-item dropdown">
                <router-link :to="child.path">
                  <span>{{  (child.meta ? child.meta.label : null) || child.name }}</span>
                </router-link>
              </li>
            </template>
          </ul>
          
        </li>
      </template>

    </ul>
  </div>
</div>

</template>

<script setup>

import { useRouter } from 'vue-router4';

const router = useRouter();

</script>
