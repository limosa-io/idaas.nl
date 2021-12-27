
<template>
    
<div>
  <h3 class="c-grey-900">Add a new authentication module</h3>

  <div class="list-group">
    <a href="#" v-for="(value,t) in types" :key="t" @click.prevent="createModule(t)" class="list-group-item list-group-item-action">{{ value }}</a>
  </div>

</div>
</template>


<script>

export default {

  data() {
    return {

      errors: {},

      wasValidated: false,
      loading: false,

      module: {
        type: null,
        name: null,
        enabled: false,
        skippable: true
      },
      types: []

    }
  },

  mounted() {

    this.$http.get(this.$murl('/authchain/v2/manage/types')).then(response => {
      this.types = response.data;
    }, response => {
      // error callback
    });

  },

  methods: {

    createModule(t) {

      this.$http.post(this.$murl('/authchain/v2/manage/modules'),
          {
            type: t,
            enabled: false,
            skippable: true,
            hide_if_not_requested: false
          }
        ).then(response => {

          this.$noty({
            text: 'We have succesfully saved your new module.'
          });

          this.$router.replace({
            name: 'authentication.edit',
            params: {
              module_id: response.data.id
            }
          });

        }, response => {
          
          
        });


    }

  }

}

</script>
