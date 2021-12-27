import Vuex from 'vuex';
import Vue from 'vue';

Vue.use(Vuex);

export default new Vuex.Store({

  state: {
    authrequest: null,
    activeModule: null,
    error: null,

    alert: 'none'
  },

  mutations: {

    authrequest(state, authrequest) {
        state.authrequest = authrequest;
    },

    activeModule(state, activeModule) {
        state.activeModule = activeModule;
    },

    error(state, error) {
        state.error = error;
    },
    
    alert(state, alert) {
        state.alert = alert;
    }
    
  }

});
