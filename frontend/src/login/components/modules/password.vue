<template>
  <form v-on:submit.prevent="onSubmit" class="mb-3">
    <div class="form-group">
      <label
        :style="{
          display: customerstyle.label_display != 'show' ? 'none' : 'block',
        }"
        for="usernameInput"
        >{{ $t("login.username") }}</label
      >
      <input
        type="text"
        class="form-control"
        id="usernameInput"
        :placeholder="$t('login.usernamePlaceholder')"
        v-model="username"
      />
    </div>

    <div class="form-group">
      <label
        :style="{
          display: customerstyle.label_display != 'show' ? 'none' : 'block',
        }"
        for="passwordInput"
        class="mt-2"
        >{{ $t("login.password") }}</label
      >
      <input
        type="password"
        class="form-control"
        id="passwordInput"
        :placeholder="$t('login.passwordPlaceholder')"
        v-model="password"
      />
    </div>

    <div class="form-check">
      <input
        v-model="remember"
        type="checkbox"
        class="form-check-input"
        id="remember"
      />
      <label class="form-check-label" for="remember">{{
        $t("login.remember")
      }}</label>
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      {{ error }}
    </div>

    <button
      :style="{
        backgroundColor: customerstyle['button_backgroundColor'] || '#FF0000',
      }"
      class="btn btn-primary btn-block"
      type="submit"
    >
      <span>{{ $t("login.loginButton") }}</span>
    </button>
  </form>
</template>

<script setup>
import { watch, onMounted, defineProps, ref } from "vue";
import { useStateStore } from "../store";
import { baseProps, request } from "./composable";

const state = useStateStore();
const username = ref(null);
const password = ref(null);
const remember = ref(true);
const error = ref(null);

const props = defineProps(baseProps);

onMounted(() => {
  if (props.authRequest.info.hint) {
    username.value = props.authRequest.info.hint;
  }
});

function onSubmit() {
  request(
  props.module,
  props.authRequest,  
  {
    username: username.value,
    password: password.value,
    remember: remember.value,
  }).then(
    (_response) => {
      // do nothing special
    }).catch((error) => {
      if (error.response.status == 422) {
        state.error(
          "The provided username or password is incorrect."
        )
      }
    }
  );
}
</script>
