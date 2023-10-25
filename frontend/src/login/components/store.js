import { defineStore } from 'pinia'

export const useStateStore = defineStore('state', {
  state: () => (
    { 
      authRequest: null,
      activeModule: null,
      error: null,

      alert: 'none',

      show_notify: false,
      notification: '',
      notification_type: 'info'
    }
  ),

  actions: {
    error(message){
      return this.notify(message, 'error');
    },
    info(message){
      return this.notify(message, 'info');
    },
    notify(message, type) {
      this.notification = message;
      this.show_notify = true;
      this.notification_type = type;

      window.setTimeout(() => {
        this.show_notify = false;
      }, 5000);
    },
  },
})

// import Vuex from 'vuex';
// import Vue from 'vue';

// Vue.use(Vuex);

// export default new Vuex.Store({

//   state: {
//     authRequest: null,
//     activeModule: null,
//     error: null,

//     alert: 'none'
//   },

//   mutations: {

//     authRequest(state, authRequest) {
//         state.authRequest = authRequest;
//     },

//     activeModule(state, activeModule) {
//         state.activeModule = activeModule;
//     },

//     error(state, error) {
//         state.error = error;
//     },
    
//     alert(state, alert) {
//         state.alert = alert;
//     }
    
//   }

// });
