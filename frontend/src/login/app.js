/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import { createApp, configureCompat } from 'vue'
import { createRouter, createWebHistory } from 'vue-router4'

import App from './App.vue'
import Login from './components/Login.vue'
import Selection from './components/Selection.vue'
import Logout from './components/Logout.vue'
import Error from './components/Error.vue'
import { i18n } from './i18n'

import { createPinia } from 'pinia'

configureCompat({
 // MODE: 3,
//  GLOBAL_MOUNT: false
 GLOBAL_MOUNT: 'suppress-warning'
})

const pinia = createPinia()

const routes = [

  {
    path: '/oauth/logout',
    name: 'logout',
    component: Logout
  },
  {
    path: '/oauth/authorize',
    name: 'error',
    component: Error
  },
  {
    path: '/saml/v2/login',
    name: 'error.saml',
    component: Error
  },
  {
    path: '/error',
    name: 'error.default',
    component: Error
  },

  {
    path: '/',
    component: Login,

    children: [

      {
        path: ':hash?/register/:module?',
        name: 'login.register',
        component: () =>
          import('./components/Register.vue')
      },

      {
        path: '/:hash?/:module?',
        name: 'login',
        component: Selection
      }
    ]

  }];

const router = createRouter({
  routes: routes,
  history: createWebHistory()
});

const app = createApp(App);

app.use(pinia);
app.use(router);
app.use(i18n);
app.mount('#app');
