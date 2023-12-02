<template>
  <button
    class="btn btn-secondary btn-lg btn-block mt-1"
    :style="{
      'background-color':
        (props.module.config ? props.module.config.button_color : null) || '#e8711c',
    }"
    :class="{ 'btn-loading': loading }"
    @click="init"
    style="border-color: rgba(0, 0, 0, 0.2)"
  >
    <span>{{
      $t("login.oidcButton")
    }}</span>
  </button>
</template>

<script setup>
import { onMounted, watch, ref } from "vue";

import { defineProps } from "vue";
import { baseProps, isIncomplete } from "./composable";
import { useStateStore } from "../store";
const props = defineProps(baseProps);

const lonely = ref(props.lonely)
const state = useStateStore();

const loading = ref(false);
var lastCalled = 0;

onMounted(() => {
  loading.value = false;

  if (props.lonely && !isIncomplete(props.authRequest, props.module)) {
    init();
  } else if (isIncomplete(props.authRequest, props.module)) {
    state.error(
      "Could not authenticate using the chosen method. Please choose a different method.",
    );

    deactivate();
  }

});

watch(lonely, (val) => {
  loading.value = false;

  if (val && !isIncomplete()) {
    init();
  }
});

function init() {
  if (new Date() - lastCalled < 500) {
    return;
  }

  lastCalled = new Date();
  loading.value = true;

  request(props.module,props.authRequest, {
    init: true,
  }).then(
    (response) => {
      window.top.location = response.data.url;
    },
    (response) => {}
  );
}
</script>
