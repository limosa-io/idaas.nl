<template>

<div class="form-group">

  <label for="user.preferredLanguage">Language
    <span class="text-danger">*</span>
  </label>

  
  <select class="form-control" v-model="user['urn:ietf:params:scim:schemas:core:2.0:User'].preferredLanguage">
    <option disabled value="">Please select one</option>
    <option v-for="(l, index) in customerstyle.languages" :key="index" :value="l">{{ $t('general.locale.' + l) }}</option>
  </select>

  <div v-if="!errors.type" class="invalid-feedback">
    This is a required field and must be minimal 3 characters long.
  </div>

</div>

</template>

<script>
export default {
    
    props: ['user','errors', 'customerstyle'],

    data(){

        return {
            options: ['nl','de']
        }
    }
    
}
</script>

