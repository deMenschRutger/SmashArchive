<template>
    <ul class="nav navbar-nav navbar-right" v-if="UserStore.hasSession()">
        <li>
            <a href="#">Welcome {{ UserStore.state.profile.username }}</a>
        </li>
        <li>
            <a href="#" @click.prevent="logout">Logout</a>
        </li>
    </ul>
    <ul class="nav navbar-nav navbar-right" v-else>
        <li>
            <a href="#" @click.prevent="login">Login</a>
        </li>
    </ul>
</template>

<script lang="ts">

import Vue from 'vue';
import { UserStore } from '../store';

export default Vue.component('authentication', {
    data: () => {
        return {
            UserStore,
        }
    },

    methods: {
        login: async function (): Promise<void> {
            if (!UserStore.state.authentication.initialized || UserStore.state.authentication.accessToken) {
                return;
            }

            // TODO: Handle a rejected login attempt.
            await UserStore.login();
        },

        logout: async function (): Promise<void> {
            if (!UserStore.state.authentication.initialized || !UserStore.state.authentication.accessToken) {
                return;
            }

            // TODO: Handle a rejected logout attempt.
            await UserStore.logout();
        }
    }
});

</script>
