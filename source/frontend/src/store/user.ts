import * as jwt from 'jsonwebtoken';
import * as _ from 'lodash';
import * as Facebook from '../service/facebook';
import smashArchive from '../service/smasharchive';

export interface UserStore {
    state: {
        authentication: {
            accessToken: string | null;
            initialized: boolean;
        };
        profile: {
            id: number | null;
            username: string | null;
        }
    };
    init: () => Promise<void>;
    tokenIsValid: (accessToken: string) => boolean;
    reconnect: () => Promise<void>;
    hasSession: () => void;
    clearSession: () => void;
    login: () => Promise<void>;
    logout: () => Promise<void>;
    updateProfile: () => Promise<void>;
}

const store: UserStore = {
    state: {
        authentication: {
            accessToken: null,
            initialized: false,
        },
        profile: {
            id: null,
            username: null,
        },
    },

    /**
     * @return {Promise<void>}
     */
    async init(): Promise<void> {
        const accessToken: string | null = localStorage.getItem('app/accessToken');

        if (accessToken && this.tokenIsValid(accessToken)) {
            this.state.authentication.accessToken = accessToken;
        } else {
            await this.reconnect();
        }

        this.state.authentication.initialized = true;

        return this.updateProfile();
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
        this.clearSession();

        const loginStatus: facebook.AuthResponse = await Facebook.getLoginStatus();

        if (loginStatus.status !== 'connected') {
            return;
        }

        const response: any = await smashArchive.users.login(
            loginStatus.authResponse.accessToken,
        );

        if (response.accessToken) {
            localStorage.setItem('app/accessToken', response.accessToken);
            this.state.authentication.accessToken = response.accessToken;
        }
    },

    /**
     * @return {boolean}
     */
    hasSession(): boolean {
        return !!this.state.authentication.accessToken;
    },

    /**
     * @return {void}
     */
    clearSession(): void {
        localStorage.removeItem('app/accessToken');

        this.state.authentication.accessToken = null;

        this.state.profile = {
            id: null,
            username: null,
        };
    },

    /**
     * @return {Promise<void>}
     */
    async login(): Promise<void> {
        await Facebook.login();
        await this.reconnect();

        return this.updateProfile();
    },

    /**
     * @return {Promise<void>}
     */
    async logout(): Promise<void> {
        await Facebook.getLoginStatus();
        await Facebook.logout();

        this.clearSession();
    },

    /**
     * @return {Promise<void>}
     */
    async updateProfile(): Promise<void> {
        if (!this.hasSession()) {
            return;
        }

        const accessToken: string = <string>this.state.authentication.accessToken;

        this.state.profile = await smashArchive.users.me(accessToken);
    },
};

export default store;
