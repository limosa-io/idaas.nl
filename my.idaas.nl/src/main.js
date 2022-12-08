import'./public-path';
import Vue from 'vue'
import App from './App.vue'
import VueRouter from 'vue-router'
import Vuex from 'vuex'
import CompleteLogin from './components/CompleteLogin.vue'

import CompleteLogout from './components/CompleteLogout.vue'
import InitLogin from './components/InitLogin.vue'
import InitLogout from './components/InitLogout.vue'

import ConfirmMail from './components/ConfirmMail.vue'
import AccessDenied from './components/AccessDenied.vue'

import Account from './components/Account.vue'
import storeData from './store'
import Tenants from './components/account/Tenants.vue'
import Profile from './components/account/Profile.vue'
import Password from './components/account/Password.vue'
import Security from './components/account/Security.vue'
import LinkAccount from './components/account/LinkAccount.vue'
import BootstrapVue from 'bootstrap-vue'
import VueResource from 'vue-resource';

import 'bootstrap-vue/dist/bootstrap-vue.css';


Vue.use(BootstrapVue);
Vue.use(Vuex)
Vue.use(VueResource);

Vue.directive('click-outside', {
    bind: function (el, binding, vnode) {
      el.clickOutsideEvent = function (event) {
        // here I check that click was outside the el and his childrens
        if (!(el == event.target || el.contains(event.target))) {
          // and if it did, call method provided in attribute value
          vnode.context[binding.expression](event);
        }
      };
      document.body.addEventListener('click', el.clickOutsideEvent)
    },
    unbind: function (el) {
      document.body.removeEventListener('click', el.clickOutsideEvent)
    },
  });

export const store = new Vuex.Store(storeData);

store.commit('accessToken', window.localStorage.getItem('access_token'));

Vue.use(VueRouter)
Vue.use(Vuex)

const routes = [

    {
        path: '/callback',
        component: CompleteLogin
    },

    {
        path: '/confirm',
        component: ConfirmMail,
        name: 'confirm_mail'
    },

    {
        path: '/post_logout',
        component: CompleteLogout
    },
    
    {
        path: '/login_needed',
        component: AccessDenied
    },

    {
        path: '/login',
        component: InitLogin
    },
    {
        path: '/logout',
        component: InitLogout
    },
    
    {
        path: '/',
        component: Account,

        children: [
        
            {
                path: '',
                component: Tenants
            },

            {
                path: 'profile',
                component: Profile
            },

            {
                path: 'password',
                component:  Password
            },

            {
                path: 'security',
                component:  Security
            },

            {
                path: 'linking',
                component: LinkAccount,
            },

        ]
        
    },    

]

const router = new VueRouter({
    routes, // short for `routes: routes`
    mode: 'history',
    base: '/'
});

Vue.config.productionTip = false

new Vue({
  render: h => h(App),
  router,
  store
}).$mount('#app')
