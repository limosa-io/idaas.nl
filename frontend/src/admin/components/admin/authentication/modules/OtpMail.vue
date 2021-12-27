
<template>
  <div>
    <b-form-group
      horizontal
      :label-cols="3"
      description="Available template parameters are <code class=&quot;highlighter-rouge&quot;>otp</code>, <code class=&quot;highlighter-rouge&quot;>subject</code> and if present <code class=&quot;highlighter-rouge&quot;>user</code>."
      label="Email template"
      label-for="module.config.template_id"
    >
      <b-form-select
        id="module.config.template_id"
        aria-describedby="parentHelp"
        v-if="templates"
        value-field="id"
        text-field="name"
        v-model="module.config.template_id"
        :options="templates"
      />
    </b-form-group>
  </div>
</template>

<script>
export default {
  props: {
    module: null,
    info: null
  },

  data() {
    return {
      templates: {}
    };
  },

  mounted() {
    
    this.$http.get(this.$murl("api/mail_template")).then(
      response => {
        this.templates = response.data;
      },
      response => {
        // error callback
      }
    );
  },

  methods: {
    onSubmit(event) {
      event.preventDefault();
    }
  }
};
</script>
