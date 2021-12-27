<template>
    <div class="form-group">
          <label for="register.name.familyName">Family name
            <span class="text-danger">*</span>
          </label>
          <input :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:name.familyName']}" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].name.familyName"
            required type="text" class="form-control" id="register.name.familyName" placeholder="">

          <div v-if="!errors.type" class="invalid-feedback">
            This is a required field and must be minimal 3 characters long.
          </div>

        </div>

</template>

<script>

export default {
    

    props: ['user','errors'],


}

</script>

