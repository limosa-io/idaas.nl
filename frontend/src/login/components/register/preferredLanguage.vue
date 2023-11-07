<template>
  <div class="form-group">
    <label for="user.preferredLanguage"
      >Language
      <span class="text-danger">*</span>
    </label>

    <select
      class="form-control"
      v-model="
        userValue['urn:ietf:params:scim:schemas:core:2.0:User']
          .preferredLanguage
      "
    >
      <option disabled value="">Please select one</option>
      <option
        v-for="(l, index) in props.customerstyle.languages"
        :key="index"
        :value="l"
      >
        {{ $t("general.locale." + l) }}
      </option>
    </select>

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

