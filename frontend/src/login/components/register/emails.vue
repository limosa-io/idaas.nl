<template>
  <div>
    <div
      class="form-group"
      v-for="(email, index) in userValue[
        'urn:ietf:params:scim:schemas:core:2.0:User'
      ].emails"
      :key="index"
    >
      <label for="email"
        >E-mail
        <span class="text-danger">*</span>
      </label>

      <input
        :class="{
          'is-invalid':
            props.errors[
              'urn:ietf:params:scim:schemas:core:2.0:User:emails.' +
                index +
                '.value'
            ],
        }"
        v-model="email.value"
        required
        type="text"
        class="form-control"
        id="email"
        placeholder=""
      />

      <div v-if="!props.errors.type" class="invalid-feedback">
        This is a required field and must be a valid mail address.
      </div>
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

