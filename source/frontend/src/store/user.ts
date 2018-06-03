import axios, { AxiosResponse } from 'axios';
import * as Facebook from '../service/facebook';

export interface UserStore {
    state: {
        authentication: {
            accessToken: string | null;
            initialized: boolean;
        };
    };
    init: () => Promise<void>;
    reconnect: () => Promise<void>;
    login: () => Promise<void>;
}

const store: UserStore = {
    state: {
        authentication: {
            accessToken: null,
            initialized: false,
        },
    },

    /**
     * @return {Promise<void>}
     */
    async init (): Promise<void> {
        if (this.state.authentication.accessToken) {
            this.state.authentication.initialized = true;

            return;
        }

        await this.reconnect();

        this.state.authentication.initialized = true;

        console.log(this.state.authentication);
    },

    /**
     * @return {Promise<void>}
     */
    async reconnect (): Promise<void> {
        const loginStatus: facebook.AuthResponse = await Facebook.getLoginStatus();

        if (loginStatus.status !== 'connected') {
            return;
        }

        const response: AxiosResponse = await axios.post('/api/v0.1/users/login/', {
            accessToken: loginStatus.authResponse.accessToken,
        });

        if (response.data.data.accessToken) {
            this.state.authentication.accessToken = response.data.data.accessToken;
        }
    },

    /**
     * @return {Promise<void>}
     */
    async login (): Promise<void> {
        await Facebook.login();

        return this.reconnect();
    },
};

export default store;
