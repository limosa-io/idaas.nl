<template>
    
    <div class="form-group">
      
          <label for="password">Password
            <span class="text-danger">*</span>
          </label>

          <input required :class="{'is-invalid': errors['urn:ietf:params:scim:schemas:core:2.0:User:password']}" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].password" type="password" class="form-control" id="password" placeholder="">

          <div v-if="!errors.type" class="invalid-feedback">
            This is a required field.
          </div>

        </div>

</template>


<script>

export default {
    

    props: ['user','errors'],


}

</script>
