import Vue from 'vue';

(window as any).fbAsyncInit = function() {
    FB.init({
        appId: '1878227255734015',
        xfbml: false,
        version: 'v3.0'
    });

    const template = `<li>
        <a href="#" @click.prevent="login">Login</a>
    </li>`;

    Vue.component('login', {
        template: template,

        methods: {
            login: function () {
                console.log('login');

                FB.getLoginStatus(function (response: any) {
                    console.log(response);
                });
            }
        }
    });

    new Vue({
        el: '#app',
    });
};
