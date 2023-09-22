import "noty/src/noty.scss";
import "noty/src/themes/bootstrap-v4.scss";

export default {

  props: ['module', 'authRequest', 'customerstyle', 'lonely', 'active'],

  data() {
    return {
      urlActive: false,
      loading: false,
    }
  },

  watch: {
    $route(to, from) {

      if (to.params.module == 'register') {
        this.urlActive = true;
      }

    }
  },

  mounted(){
    if(this.active && this.authRequest.next.length == 1){
      // TODO: autoselect this module
    }
  },

  methods: {

    isActive() {
      return this.active || this.urlActive;
    },

    isIncomplete() {
      return this.authRequest.info.inc && this.authRequest.info.inc.module == this.module.id;
    },

    getIncompleteModuleState() {
      return (this.isIncomplete() ? this.authRequest.info.inc.moduleState : {}) || {}
    },

    activate() {
      this.$store.commit('activeModule', this.module.id);
    },

    deactivate() {
      this.$store.commit('activeModule', null);
    },

    overview() {
      this.$store.commit('activeModule', null);
    },

    request(data){
      this.loading = true;
      return this.$ice(this.module, this.authRequest, data).then(result => {
        this.loading = false;
        return result;
      });
    }

  }

}