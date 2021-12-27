<template>

<div>

  <div class="form-group" v-for="(email, index) in user['urn:ietf:params:scim:schemas:core:2.0:User'].emails" :key="index">
    <label for="email">E-mail
      <span class="text-danger">*</span>
    </label>

    <input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:emails.' + (index) + '.value'] }"
      v-model="email.value" required type="text" class="form-control" id="email" placeholder="">

    <div v-if="!errors.type" class="invalid-feedback">
      This is a required field and must be a valid mail address.
    </div>

  </div>
</div>

</template>

<script>

export default {
    

    props: ['user','errors'],


}

</script>

