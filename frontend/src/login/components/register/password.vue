<template>
  <div class="form-group">
    <label for="password"
      >Password
      <span class="text-danger">*</span>
    </label>

    <input
      required
      :class="{
        'is-invalid':
          props.errors['urn:ietf:params:scim:schemas:core:2.0:User:password'],
      }"
      v-model="
        userValue['urn:ietf:params:scim:schemas:core:2.0:User'].password
      "
      type="password"
      class="form-control"
      id="password"
      placeholder=""
    />

    <div v-if="!props.errors.type" class="invalid-feedback">
      This is a required field.
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
