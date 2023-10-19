<template>
  <div>
    <h1>
      {{ $t("login.consentText", { application: props.authRequest.info.nam }) }}
    </h1>

    <div>
      <div class="list-group mb-2">
        <ul class="list-group">
          <li
            class="list-group-item"
            v-for="s of props.authRequest.info.sco"
            :key="s"
          >
            {{ $t(scopeDescription[s] ? scopeDescription[s] : s) }}
          </li>
        </ul>
      </div>

      <button
        :style="{ backgroundColor: customerstyle['button_backgroundColor'] }"
        class="btn btn-primary btn-block"
        @click="init"
      >
        <span>{{ $t("login.consentAllow") }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { request, baseProps } from "./composable";

const props = defineProps(baseProps);

const scopeDescription = {};
const scopeIcons = {
  openid: "shoe-prints",
  email: "at",
};

onMounted(() => {
  request({
    init: true,
  }).then((response) => {
    scopeDescription.value = response.data;
  });
});

function init() {
  request(props.module, props.authRequest, {});
}
</script>

<style lang="scss" scoped>
.list-group-item {
  height: 62px;
  padding-top: 20px;
  border-left-style: none;
  border-right-style: none;
  border-radius: 0px;

  svg {
    margin-right: 10px;
  }
}
</style>
