<template>
  <div class="large-form-items">
    <template v-if="lonely">
      <form v-if="!done" v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label
            for="username"
            :style="{
              display: customerstyle.label_display != 'show' ? 'none' : 'block',
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
          :style="{ backgroundColor: customerstyle['button_backgroundColor'] }"
          class="btn btn-primary btn-block"
          :class="{ 'btn-loading': isLoading }"
          type="submit"
        >
          <span>{{ $t("login.otpEmailButton") }}</span>
        </button>
      </form>

      <form v-else v-on:submit.prevent="onSubmitOtp">
        <p>{{ $t("login.otpEmailSuccess") }}</p>

        <div class="form-group">
          <label
            for="otp"
            :style="{
              display: customerstyle.label_display != 'show' ? 'none' : 'block',
            }"
            >{{ $t("login.otp") }}</label
          >
          <input type="text" class="form-control" id="otp" v-model="otp" />
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>

        <button
          :style="{ backgroundColor: customerstyle['button_backgroundColor'] }"
          class="btn btn-primary btn-block"
          :class="{ 'btn-loading': isLoading }"
          type="submit"
        >
          <span>{{ $t("login.otpEmailButtonFinish") }}</span>
        </button>
      </form>
    </template>

    <a
      v-else
      class="nav-link text-center"
      href="#"
      @click.prevent="activate(props.module)"
      active-class="active"
    >
      {{ $t("login.otpEmailLink") }}
    </a>
  </div>
</template>

<script setup>

import { useStateStore } from '../store';
import {defineProps} from 'vue';
import {request, baseProps} from './composable';
const state = useStateStore();
const username = ref(null);
const otp = ref(null);
const remember = ref(false);
const done = ref(false);
const error = ref(null);
const isLoading = ref(false);
const user_id = ref(null);
const props = defineProps(baseProps);

function onSubmitOtp() {
  request(
  props.module,props.authRequest,  
  {
    otp: otp,
    user_id_hashed: user_id,
  }).then(
    (_response) => {
      
    },
    (error) => {
      state.error(error.data.error);
    }
  );
}

function onSubmit() {
  if (isLoading.value || username.value == null || username.value == "") {
    return;
  }

  isLoading = true;

  request(
  props.module, props.authRequest,  
  {
    username: username.value,
    remember: remember.value,
  }).then(
    (response) => {
      done = true;
      user_id = response.data.user_id_hashed;
      isLoading.value = false;
    },
    (error) => {
      loading.value = false;
      state.error(error.data.error);
    }
  );
}
</script>
