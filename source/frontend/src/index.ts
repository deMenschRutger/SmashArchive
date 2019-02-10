import Vue from 'vue';
import VueRouter from 'vue-router';
import * as components from './components';
import { UserStore } from './store';

Vue.use(VueRouter);

const routes = [
  { path: '/', component: components.home },
  { path: '/players', component: components.players },
  { path: '/tournaments', component: components.tournaments },
];

const router = new VueRouter({
  routes,
  mode: 'history',
});

new Vue({
  router,
  el: '#app',
});

const config: any = (window as any).config;

// (window as any).fbAsyncInit = async function(): Promise<void> {
//   FB.init({
//     appId: config.facebook.appId,
//     xfbml: false,
//     version: 'v3.0',
//   });
//
//   await UserStore.init();
// };
