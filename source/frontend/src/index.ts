import Vue from 'vue';
import * as components from './components';

(window as any).fbAsyncInit = function() {
    FB.init({
        appId: '1878227255734015',
        xfbml: false,
        version: 'v3.0'
    });

    new Vue({
        el: '#app',
        components: components,
    });
};
