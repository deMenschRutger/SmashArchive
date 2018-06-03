<template>
    <li>
        <a href="#" @click.prevent="login">Login</a>
    </li>
</template>

<script lang="ts">

import Vue from 'vue';
import * as Facebook from '../service/facebook';
import { UserStore } from '../store';

export default Vue.component('authentication', {
    methods: {
        login: async function (): Promise<void> {
            if (!UserStore.state.authentication.initialized || UserStore.state.authentication.authenticated) {
                return;
            }

            try {
                const loginResult: facebook.AuthResponse = await Facebook.login();

                console.log(loginResult);

            } catch (error) {
                // TODO: Handle the rejected login attempt
                console.log('TODO: Handle the rejected login attempt.');
            }
        }
    }
});

</script>
