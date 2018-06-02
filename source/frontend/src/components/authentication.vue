<template>
    <li>
        <a href="#" @click.prevent="login">Login</a>
    </li>
</template>

<script lang="ts">

import Vue from 'vue';

export default Vue.extend({
    name: 'authentication',

    methods: {
        login: function () {
            console.log('login');

            FB.getLoginStatus(function (response: any) {
                console.log(response);
            });
        }
    }
});

</script>

<style scoped>

</style>