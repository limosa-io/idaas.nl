<template>
  <button
    class="btn btn-secondary btn-lg btn-block mt-1"
    type="button"
    @click.prevent="init"
    style="background-color: #0073b0; border-color: rgba(0, 0, 0, 0.2)"
  >
    <i class="fab fa-linkedin"></i> <span>{{ $t("login.linkedin") }}</span>
  </button>
</template>

<script setup>
import { defineProps } from "vue";
import { baseProps } from "./composable";

const props = defineProps(baseProps);

function init() {
  if (props.authRequest.info.display == "popup") {
    let w = window.open(
      `/authchain/v2/p/redirect/${props.module.id}/${props.authRequest.stateId}`,
      "linkedin",
      "height=450, width=550"
    );

    window.addEventListener("message", function _linkedinListener() {
      if (event.data && event.data.type == "refresh_state") {
        w.close();
        window.removeEventListener("message", _linkedinListener, true);
      }
    });
  } else {
    //Linkedin login screens cannot be served from within an iframe. Therefore, window.top
    window.top.location = `/authchain/v2/p/redirect/${props.module.id}/${props.authRequest.stateId}`;
  }
}
</script>

