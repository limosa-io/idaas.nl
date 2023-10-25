<template>
  <div class="">
    <form v-if="props.lonely &&
      isIncomplete(props.authRequest, props.module) &&
      getIncompleteModuleState(props.authRequest, props.module).state == 'confirmed'
      " v-on:submit.prevent="onSubmitPassword">
      <h1 class="mt-3">{{ $t("login.resetPasswordChangeTitle") }}</h1>
      <p>{{ $t("login.resetPasswordChangeDescription") }}</p>
      <div class="form-group">
        <label for="password" :style="{
          display: customerstyle.label_display != 'show' ? 'none' : 'block',
        }">{{ $t("login.resetPasswordChangeFirst") }}</label>

        <div class="input-group mb-3">
          <input tabindex="1" autofocus :type="showFirst ? 'text' : 'password'" id="password" class="form-control"
            placeholder="" v-model="password" />
          <div class="input-group-append">
            <button tabindex="-1" class="btn btn-outline-secondary" type="button" @click="showFirst = !showFirst">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>
      </div>

      <div class="form-group">
        <label for="passwordRepeat">{{
          $t("login.resetPasswordChangeSecond")
        }}</label>

        <div class="input-group mb-3">
          <input tabindex="2" :type="showSecond ? 'text' : 'password'" id="passwordRepeat" class="form-control"
            placeholder="" v-model="passwordRepeat" />
          <div class="input-group-append">
            <button tabindex="-1" class="btn btn-outline-secondary" type="button" @click="showSecond = !showSecond">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div v-if="errorRepeat" class="alert alert-danger" role="alert">
          {{ errorRepeat }}
        </div>
      </div>

      <button :style="{ backgroundColor: customerstyle['button_backgroundColor'] }" class="btn btn-primary btn-block"
        type="submit">
        <span>{{ $t("login.resetPasswordChangeButton") }}</span>
      </button>
    </form>

    <form v-else-if="props.lonely" v-on:submit.prevent="onSubmit" class="large-form-items">
      <template v-if="!done">
        <div class="form-group">
          <label for="username" :style="{
            display: customerstyle.label_display != 'show' ? 'none' : 'block',
          }">Username</label>
          <input type="text" class="form-control" id="username" placeholder="Username or Email" v-model="username" />
        </div>

        <div v-if="error" class="alert alert-danger" role="alert">
          {{ error }}
        </div>

        <button :style="{ backgroundColor: customerstyle['button_backgroundColor'] }" class="btn btn-primary btn-block"
          :class="{ 'btn-loading': isLoading }" type="submit">
          <span>{{ $t("login.resetPasswordButton") }}</span>
        </button>
      </template>
      <div v-else class="mt-3 mb-3 text-center pre-wrap" role="alert">
        <p>{{ $t("login.resetPasswordChangeSuccess") }}</p>
      </div>
    </form>

    <a v-else class="nav-link text-center mt-0 mb-0" href="#" @click.prevent="activate(props.module)"
      active-class="active">{{ $t("login.passwordforgottenLink") }}</a>
  </div>
</template>

<script setup>
import { watch, getCurrentInstance, ref, defineProps } from "vue";
import { useStateStore } from "@/login/components/store.js";

import { activate, baseProps, isIncomplete, request, getIncompleteModuleState } from './composable'

const props = defineProps(baseProps);

const vue = getCurrentInstance();
const username = ref(null);
const password = ref(null);
const passwordRepeat = ref(null);
const showFirst = ref(false);
const showSecond = ref(false);
const remember = ref(false);
const done = ref(false);
const error = ref(null);
const errorRepeat = ref(null);
const isLoading = ref(false);
const state = useStateStore();

watch(done, function (val) {
  state.alert = val ? "info" : "none";
});

function sendlink() {
  activate(props.module);

  isLoading.value = true;

  request({}).then(
    (_response) => {
      isLoading = false;
      done = true;
    },
    (error) => {
      isLoading = false;
      vue.proxy.noty({ text: error.data });
    }
  );
}

function onSubmitPassword() {
  if (password.value != passwordRepeat.value) {
    errorRepeat.value = "The provided passwords are not equal";
    return;
  }

  request({
    password: password.value,
  }).then(
    (response) => {
      state.info("Your password has been updated");
    },
    (error) => {
      state.error(error.data.error);
    }
  );
}

function onSubmit() {
  if (isLoading.value) {
    return;
  }

  if (username.value == null || username.value == "") {
    return;
  }

  isLoading.value = true;

  request(
    props.module,
    props.authRequest,
    {
      username: username.value,
      remember: remember.value,
    }).then(
      (response) => {
        done.value = true;
        isLoading.value = false;
      },
      (e) => {
        console.error(e);
        isLoading.value = false;
        state.error(e.response.data.error);
      }
    );
}
</script>