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
    };
  };
  init: () => Promise<void>;
  tokenIsValid: (accessToken: string) => boolean;
  reconnect: () => Promise<void>;
  hasSession: () => boolean;
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

  async init() {
    const accessToken = localStorage.getItem('app/accessToken');

    if (accessToken && this.tokenIsValid(accessToken)) {
      this.state.authentication.accessToken = accessToken;
    } else {
      await this.reconnect();
    }

    this.state.authentication.initialized = true;

    return this.updateProfile();
  },

  tokenIsValid(accessToken: string) {
    const decodedToken: any | undefined = jwt.decode(accessToken);

    if (!decodedToken || !_.has(decodedToken, 'exp')) {
      return false;
    }

    const expiresAt = decodedToken.exp * 1000;
    const now = Date.now();

    return expiresAt > now;
  },

  async reconnect() {
    this.clearSession();

    const loginStatus = await Facebook.getLoginStatus();

    if (loginStatus.status !== 'connected') {
      return;
    }

    const response = await smashArchive.users.login(
      loginStatus.authResponse.accessToken,
    );

    if (response.accessToken) {
      localStorage.setItem('app/accessToken', response.accessToken);
      this.state.authentication.accessToken = response.accessToken;
    }
  },

  hasSession() {
    return !!this.state.authentication.accessToken;
  },

  clearSession() {
    localStorage.removeItem('app/accessToken');

    this.state.authentication.accessToken = null;

    this.state.profile = {
      id: null,
      username: null,
    };
  },

  async login() {
    await Facebook.login();
    await this.reconnect();

    return this.updateProfile();
  },

  async logout() {
    await Facebook.getLoginStatus();
    await Facebook.logout();

    this.clearSession();
  },

  async updateProfile() {
    if (!this.hasSession()) {
      return;
    }

    const accessToken = <string>this.state.authentication.accessToken;

    this.state.profile = await smashArchive.users.me(accessToken);
  },
};

export default store;
