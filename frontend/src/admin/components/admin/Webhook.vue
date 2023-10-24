<template>
  <MainTemplate title="Webhook" v-if="settings">
    <p>
      A webhook allows you to receive user events - creates, updates and
      deletions - in a near real-time manner. It is not only useful for
      logging purposes, but also to populate SIEM systems or for user data
      synchronization.
    </p>

    <form @submit.prevent="onSubmit">
      <div class="form-group">
        <label for="url">Webhook url</label>
        <input
          type="url"
          v-model.trim="settings.webhook_url"
          class="form-control"
          id="url"
          aria-describedby="urlHelp"
          placeholder="https://..."
        />
        <small id="urlHelp" class="form-text text-muted"
          >Use https to ensure the confidentially of the transmitted
          data.</small
        >
      </div>

      <button type="submit" class="btn btn-primary">Save</button>
    </form>
  </MainTemplate>
</template>

<script setup>

import {ref, onMounted, getCurrentInstance} from 'vue'
import {maxios, notify} from '@/admin/helpers.js'

const settings = ref(null);
const wasValidated = ref(false);

onMounted(async () => {
  const response = await maxios.get("api/settings?namespace=webhook");
  settings.value = response.data;
});

function onSubmit(){
  maxios.put("api/settings/bulk?namespace=webhook", settings.value).then(
    (response) => {
      notif({
        text: "We have succesfully saved your new OpenID Client settings.",
      });
      errors.value = null;
    },
    (e) => {
      errors.value = e.response.data.errors;
      wasValidated.value = true;

      notify({
        text: "We could not save this.",
        type: "error",
      });
    }
  );
}

</script>
