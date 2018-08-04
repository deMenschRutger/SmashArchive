import axios, { AxiosResponse } from 'axios';
import * as jwt from 'jsonwebtoken';
import * as _ from 'lodash';
import * as Facebook from '../service/facebook';

export interface UserStore {
    state: {
        authentication: {
            accessToken: string | null;
            initialized: boolean;
        };
    };
    init: () => Promise<void>;
    tokenIsValid: (accessToken: string) => boolean;
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
    async init(): Promise<void> {
        const accessToken: string | null = localStorage.getItem('app/accessToken');

        if (accessToken && this.tokenIsValid(accessToken)) {
            this.state.authentication.accessToken = accessToken;
            this.state.authentication.initialized = true;

            return;
        }

        await this.reconnect();

        this.state.authentication.initialized = true;

        console.log(this.state.authentication);
    },

    /**
     * @param {string} accessToken
     *
     * @return {boolean}
     */
    tokenIsValid(accessToken: string): boolean {
        const decodedToken: any = jwt.decode(accessToken);

        if (!_.has(decodedToken, 'exp')) {
            return false;
        }

        const expiresAt: number = decodedToken.exp * 1000;
        const now: number = Date.now();

        return expiresAt > now;
    },

    /**
     * @return {Promise<void>}
     */
    async reconnect(): Promise<void> {
        const loginStatus: facebook.AuthResponse = await Facebook.getLoginStatus();

        if (loginStatus.status !== 'connected') {
            return;
        }

        const response: AxiosResponse = await axios.post('/api/v0.1/users/login/', {
            accessToken: loginStatus.authResponse.accessToken,
        });

        if (response.data.data.accessToken) {
            localStorage.setItem('app/accessToken', response.data.data.accessToken);
            this.state.authentication.accessToken = response.data.data.accessToken;
        }
    },

    /**
     * @return {Promise<void>}
     */
    async login(): Promise<void> {
        await Facebook.login();

        return this.reconnect();
    },
};

export default store;
