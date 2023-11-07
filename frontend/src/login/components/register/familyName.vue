<template>
  <div class="form-group">
    <label for="register.name.familyName"
      >Family name
      <span class="text-danger">*</span>
    </label>
    <input
      :class="{
        'is-invalid':
          props.errors[
            'urn:ietf:params:scim:schemas:core:2.0:User:name.familyName'
          ],
      }"
      v-model="
        userValue['urn:ietf:params:scim:schemas:core:2.0:User'].name.familyName
      "
      required
      type="text"
      class="form-control"
      id="register.name.familyName"
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

