import Vue from 'vue';
import VueRouter from 'vue-router';
import * as components from './components';

Vue.use(VueRouter);

const routes = [
    { path: '/tournaments', component: components.tournaments }
];

const router = new VueRouter({
    routes,
});

new Vue({
    el: '#app',
    router,
});

(window as any).fbAsyncInit = function() {
    FB.init({
        appId: '1878227255734015',
        xfbml: false,
        version: 'v3.0'
    });
};
