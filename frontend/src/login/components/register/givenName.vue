<template>
  <div class="form-group">
    <label for="register.name.givenName"
      >First name
      <span class="text-danger">*</span>
    </label>
    <input
      :class="{
        'is-invalid':
          props.errors[
            'urn:ietf:params:scim:schemas:core:2.0:User:name.givenName'
          ],
      }"
      v-model="
        userValue['urn:ietf:params:scim:schemas:core:2.0:User'].name.givenName
      "
      required
      type="text"
      class="form-control"
      id="register.name.givenName"
      placeholder=""
    />

    <div v-if="!props.errors.type" class="invalid-feedback">
      This is a required field and must be minimal 3 characters long.
    </div>
  </div>
</template>

<script setup>
import { defineProps, computed } from "vue";

const props = defineProps(["modelValue", "errors"]);

const userValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

</script>

