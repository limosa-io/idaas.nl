<template>
  <div class="large-form-items">
    <div v-if="props.lonely" class="panel panel-default">
      <div class="panel-heading">{{ $t("login.hotpTitle") }}</div>

      <div class="panel-body">
        <p>{{ $t("login.hotpDescription") }}</p>

        <form v-on:submit.prevent="onSubmit">
          <div class="form-group">
            <label
              :style="{
                display:
                  customerstyle.label_display != 'show' ? 'none' : 'block',
              }"
              for="otp"
              >{{ $t("login.hotpLabel") }}</label
            >
            <input
              type="text"
              class="form-control"
              id="otp"
              placeholder=""
              v-model="otp"
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
              $t("login.hotpTrustDevice")
            }}</label>
          </div>

          <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
          </div>

          <button
            :style="{
              backgroundColor: (customerstyle.button || {}).backgroundColor,
            }"
            class="btn btn-primary btn-block"
            type="submit"
          >
            <span>{{ $t("login.hotpButton") }}</span>
          </button>
        </form>
      </div>
    </div>

    <div v-else>
      <a
        class="nav-link text-center"
        href="#"
        @click.prevent="activate(props.module)"
        active-class="active"
        >{{ $("login.hotpLink") }}</a
      >
    </div>
  </div>
</template>

<script setup>
import {ref, getCurrentInstance} from "vue";
import { request, baseProps } from "./composable";
import { useStateStore} from "../store";
const state = useStateStore();
const otp = ref(null);
const remember = ref(null);
const error = ref(null);

const vue = getCurrentInstance();
const props = defineProps(baseProps);

function onSubmit(event) {
  request(
  props.module, props.authRequest,
  {
    otp: otp.value,
    remember: remember.value,
  }).then(
    (_result) => {},
    (error) => {
      state.error(error.data.error);
    }
  );

  event.preventDefault();
}
</script>
