<template>
  <div class="form-group">
    <label for="register.userName"
      >Username
      <span class="text-danger">*</span>
    </label>
    <input
      :class="{
        'is-invalid':
          props.errors['urn:ietf:params:scim:schemas:core:2.0:User:userName'],
      }"
      v-model="
        userValue['urn:ietf:params:scim:schemas:core:2.0:User'].userName
      "
      required
      type="text"
      class="form-control"
      id="register.userName"
      placeholder=""
    />

    <div v-if="!props.errors.type" class="invalid-feedback">
      This is a required field and must be minimal 3 characters long.
    </div>
  </div>
</template>


<script setup>
import { defineProps, computed } from "vue";

const props = defineProps(["modelValue", "errors", 'customerstyle']);

const userValue = computed({
  get() {
    return props.modelValue
  },
  set(value) {
    emit('update:modelValue', value)
  }
})

</script>

