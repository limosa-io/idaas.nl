<template>
  <div>{{ is_logged_in ? 'true' : 'false' }}</div>
</template>

<script>
import settings from "./settings";

export default {

  data(){
    return {
      is_logged_in: false
    }
  },

  methods: {
    postUpdate(){
      this.is_logged_in = window.localStorage.getItem("access_token");
      
      parent.postMessage(
        {
          logged_in: window.localStorage.getItem("access_token") != null
        },
        settings.session_target_origin
      );
    }
  },

  mounted() {
    
    setInterval(() => {
      this.postUpdate();
    }, 10000);

    this.postUpdate();
  }
};
</script>

<style>
</style>