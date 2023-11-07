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
          :style="{ backgroundColor: customerstyle['button_backgroundColor'] }"
          class="btn btn-primary btn-block"
          type="submit"
        >
          <span>{{ $t("login.activationButton") }}</span>
        </button>
      </template>
      <div v-else class="alert alert-info mt-2" role="alert">
        {{ $t("login.activationMailSend") }}
      </div>
    </form>

    <a
      v-else
      class="nav-link text-center"
      href="#"
      @click.prevent="activate(props.module)"
      active-class="active"
      >{{ $t("login.activationLink") }}</a
    >
  </div>
</template>

<script setup>
import { onMounted, ref, defineProps } from "vue";

import {activate, baseProps} from './composable'
import {useStateStore} from "../store";
import {request} from "./composable";

const state = useStateStore();
const props = defineProps(baseProps);

const username = ref(null);
const done = ref(false);
const error = ref(null);

onMounted(() => {

  if (props.authRequest.info.hint) {
    username.value = props.authRequest.info.hint;
  }

  if (
    props.lonely &&
    (props.authRequest.info.subject != null ||
      props.authRequest.info.hint != null)
  ) {
    // seems like 'watch' is always triggered
    autoSubmit();
  }
});

function autoSubmit() {
  request({}).then(
    (response) => {
      done.value = true;
    },
    (error) => {
      state.error(error.data.error);
      done.value = false;
    }
  );
}

function onSubmit() {
  if (username.value == null || username.value == "") {
    return;
  }

  request({
    username: username.value,
  }).then(
    (response) => {
      done.value = true;
    },
    (error) => {
      state.error(error.data.error);
      done.value = false;
    }
  );
}
</script>
