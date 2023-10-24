<template>
  <div class="large-form-items">
    <form v-if="props.lonely" v-on:submit.prevent="onSubmit">
      <template v-if="!done">
        <div class="form-group">
          <label
            for="username"
            :style="{
              display: props.customerstyle.label_display != 'show' ? 'none' : 'block',
            }"
            >{{ $t("login.username") }}</label
          >
          <input
            type="text"
            class="form-control"
            id="username"
            :placeholder="$t('login.usernamePlaceholder')"
            v-model="username"
          />
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>

        <button
          :style="{ backgroundColor: props.customerstyle['button_backgroundColor'] }"
          class="btn btn-primary btn-block"
          :class="{ 'btn-loading': isLoading }"
          type="submit"
        >
          <span>{{ $t("login.passwordlessButton") }}</span>
        </button>
      </template>

      <div v-else class="alert mt-2" role="alert">
        {{ $t("login.passwordlessSuccess") }}
      </div>
    </form>

    <a
      v-else
      class="nav-link text-center"
      href="#"
      @click.prevent="activate(props.module)"
      active-class="active"
    >
      {{ $t("login.passwordlessLink") }}
    </a>
  </div>
</template>

<script setup>
import { watch, defineProps, ref } from "vue";
import { useStateStore } from "@/login/components/store.js";

import { activate, baseProps, request } from "./composable";

const username = ref(null);
const remember = ref(false);
const done = ref(false);
const error = ref(null);
const isLoading = ref(false);
const state = useStateStore();

watch(done, (val) => {
  state.alert = val ? "info" : "none";
});

const props = defineProps(baseProps);

function onSubmit() {
  if (isLoading.value || username.value == null || username.value == "") {
    return;
  }

  isLoading.value = true;

  request(props.module, props.authRequest, {
    username: username.value,
    remember: remember.value,
  }).then(
    (response) => {
      done.value = true;
      isLoading.value = false;
    },
    (e) => {
      isLoading.value = false;
      state.error(e.response.data.error);
    }
  );
}
</script>
