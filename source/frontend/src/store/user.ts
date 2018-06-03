import * as Facebook from '../service/facebook';

export interface UserStore {
    state: {
        authentication: {
            initialized: boolean;
            authenticated: boolean;
        };
    };
    init: () => Promise<void>;
}

const store: UserStore = {
    state: {
        authentication: {
            initialized: false,
            authenticated: false,
        },
    },

    /**
     * @return {Promise<void>}
     */
    async init (): Promise<void> {
        const loginStatus: facebook.AuthResponse = await Facebook.getLoginStatus();

        if (loginStatus.status === 'connected') {
            this.state.authentication.authenticated = true;
        }

        this.state.authentication.initialized = true;
    },
};

export default store;
