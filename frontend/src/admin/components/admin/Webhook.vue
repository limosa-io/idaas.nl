<template>

<div class="container-fluid">
  <h4 class="c-grey-900 mt-1 mb-3">Webhook</h4>

  <div class="row" v-if="settings">
    <div class="col-md-12">
      <div class="bgc-white bd bdrs-3 p-3 mt-2">

        <p>A webhook allows you to receive user events - creates, updates and deletions - in a near real-time manner.
          It is not only useful for logging purposes, but also to populate SIEM systems or for user data
          synchronization.</p>

        <form @submit.prevent="onSubmit">
          <div class="form-group">
            <label for="url">Webhook url</label>
            <input type="url" v-model.trim="settings.webhook_url" class="form-control" id="url" aria-describedby="urlHelp"
              placeholder="https://...">
            <small id="urlHelp" class="form-text text-muted">Use https to ensure the confidentially of the transmitted
              data.</small>
          </div>

          <button type="submit" class="btn btn-primary">Save</button>

        </form>

      </div>

    </div>
  </div>

</div>

</template>


<script>
export default {

  data() {
    return {
      settings: null,
      wasValidated: false

    }
  },

  mounted() {

    this.$http.get(this.$murl('api/settings?namespace=webhook')).then(response => {

      this.settings = Object.assign({
        rule_user_event: null,
        rule_jit: null,
        rule_attribute: null
      }, response.data);

      this.wasValidated = false;
      
    });

  },

  methods: {

    onSubmit(event) {

      if (event.target.checkValidity()) {

        this.$http.put(this.$murl('api/settings/bulk?namespace=webhook'),
          this.settings
        ).then(response => {

          this.$noty({
            text: 'We have succesfully saved your new OpenID Client settings.'
          });
          this.errors = null;

        }, response => {
          this.errors = response.data.errors;
          this.wasValidated = true;

          this.$noty({
            text: 'We could not save this.',
            type: 'error'
          });
        });

        //this.loading = true;
      } else {
        this.wasValidated = true;
        this.$noty({
          text: 'We could not save this.',
          type: 'error'
        });
      }

    }

  }
}
</script>
