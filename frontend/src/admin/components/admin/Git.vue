<template>
  <MainTemplate title="Sync your configuration to a git repository">
    <template v-slot:body v-if="loaded">
      <Danger header="This is experimental functionality" body="Most importantly: always use a private repository as application secrets are exposed." />
      
      <div class="bgc-white bd bdrs-3 p-3 mt-2">
        <form
          class="needs-validation"
          novalidate
          :class="{ 'was-validated': wasValidated }"
          v-on:submit.prevent="onSubmit"
        >
          <div class="form-row">
            <label for="levels" class="col-md-3 col-form-label"
              >Git provider</label
            >
            <div class="col-md-9">
              <select v-model="git.type" class="form-control" id="group">
                <option :value="'github'">GitHub</option>
                <option :value="'none'">none</option>
              </select>
            </div>
          </div>

          <template v-if="git.type == 'github'">
            <div class="form-row form-group mt-3">
              <div class="col-md-3">
                <label for="token">Token</label>
              </div>
              <div class="col">
                <input
                  :class="{
                    'is-invalid': errors['settings.token'] ? true : false,
                  }"
                  v-model="git.settings.token"
                  required
                  type="text"
                  class="form-control"
                  id="token"
                  placeholder=""
                />

                <div v-if="errors['settings.token']" class="invalid-feedback">
                  This is a required field and must be minimal 3 characters
                  long.
                </div>
              </div>
            </div>

            <div class="form-row form-group mt-3">
              <div class="col-md-3">
                <label for="repository">Repository</label>
              </div>
              <div class="col">
                <input
                  :class="{
                    'is-invalid': errors['settings.repository'] ? true : false,
                  }"
                  v-model="git.settings.repository"
                  required
                  type="text"
                  class="form-control"
                  id="repository"
                  placeholder=""
                />

                <div
                  v-if="errors['settings.repository']"
                  class="invalid-feedback"
                >
                  This is a required field and must be minimal 3 characters
                  long.
                </div>
              </div>
            </div>

            <div class="form-row form-group mt-3">
              <div class="col-md-3">
                <label for="owner">Owner</label>
              </div>
              <div class="col">
                <input
                  :class="{
                    'is-invalid': errors['settings.owner'] ? true : false,
                  }"
                  v-model="git.settings.owner"
                  required
                  type="text"
                  class="form-control"
                  id="owner"
                  placeholder=""
                />

                <div v-if="errors['settings.owner']" class="invalid-feedback">
                  This is a required field and must be minimal 3 characters
                  long.
                </div>
              </div>
            </div>
          </template>

          <div class="form-row mt-3">
            <label for="levels" class="col-md-3 col-form-label"></label>
            <div class="col-md-9">
              <button type="submit" class="btn btn-primary">
                Save Changes
              </button>
            </div>
          </div>
        </form>
      </div>

      <div
        class="bgc-white bd bdrs-3 p-3 mt-2"
        v-if="git.settings.repository != null && git.settings.repository != ''"
      >
        <form
          class="needs-validation"
          novalidate
          :class="{ 'was-validated': wasValidated }"
          v-on:submit.prevent="onSubmit"
        >
          <div class="form-row">
            <label for="levels" class="col-md-3 col-form-label"
              >Pull changes</label
            >
            <div class="col-md-9">
              <button type="button" class="btn btn-secondary" @click="pull">
                Pull
              </button>
            </div>
          </div>

          <div class="form-row mt-3">
            <label for="levels" class="col-md-3 col-form-label"
              >Push changes</label
            >
            <div class="col-md-9">
              <button type="button" class="btn btn-secondary" @click="push">
                Push
              </button>
            </div>
          </div>
        </form>
      </div>
    </template>
  </MainTemplate>
</template>

<script setup>

import {ref, onMounted, getCurrentInstance} from 'vue';
import Danger from "@/admin/components/general/Danger.vue";
import { notify } from '../../helpers';
import { useRouter } from 'vue-router4';

const router = useRouter();
const loaded = ref(false);
const wasValidated = ref(false);
const errors = ref(null);
const git = ref({
  type: null, // disabled | github
  settings: {
    token: null,
    owner: null,
    repository: null,
  },
})

onMounted(() => {
  maxios.get("api/git").then((response) => {
    git.value = response.data;
    loaded.value = true;
  });
});

function pull() {
  maxios.get("api/git/pull");
}

function push() {
  maxios.put("api/git/push");
}

function onSubmit(event) {
  if (event.target.checkValidity()) {
    maxios.put("api/git", git.value).then(
      (response) => {
        notify({
          text: "We have succesfully saved your new Git settings.",
        });
        errors.value = null;
        router.replace({ name: 'oidc.client.edit', params: { client_id: response.data.client_id }});
      },
      (response) => {
        errors.value = response.data.errors;
        wasValidated.value = true;

        notify({
          text: "We could not save this.",
          type: "error",
        });
      }
    );

    //this.loading = true;
  } else {
    wasValidated.value = true;
    notify({
      text: "We could not save this.",
      type: "error",
    });
  }

  event.preventDefault();
}
</script>

<style>
</style>
