
<template>
  <div>
    <h1 v-if="state.activeModule == null && props.authRequest.next.lenght > 1">
      Sign in to {{ props.authRequest.info.nam }}
    </h1>

    <div class="panel panel-default large-form-items">
      <div class="panel-body">
        <slot></slot>
      </div>
    </div>
  </div>
</template>


<script setup>
import {defineProps} from 'vue'

import { useStateStore } from "./store";

const props = defineProps(["authRequest", "customerstyle"]);

const state = useStateStore();
</script>