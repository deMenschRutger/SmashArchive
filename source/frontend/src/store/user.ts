import * as jwt from 'jsonwebtoken';
import * as Facebook from '../service/facebook';
import smashArchive from '../service/smasharchive';

type DecodedJwt = {
  iat: number;
  exp: number;
  roles: string[],
  sub: number,
}

export interface UserStore {
  state: {
    authentication: {
      accessToken: string | null;
      initialized: boolean;
      roles: string[];
    };
    profile: {
      id: number | null;
      username: string | null;
    };
  };
  getAccessToken: () => string;
  init: () => Promise<void>;
  decodeToken: (accessToken: string) => DecodedJwt | null;
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
      roles: [],
    },
    profile: {
      id: null,
      username: null,
    },
  },

  getAccessToken() {
    if (!this.state.authentication.accessToken) {
      throw new Error('Could not retrieve a valid access token.');
    }

    return this.state.authentication.accessToken;
  },

  async init() {
    const accessToken = localStorage.getItem('app/accessToken');
    const decodedToken = this.decodeToken(accessToken || '');

    if (decodedToken) {
      this.state.authentication.accessToken = accessToken;
      this.state.authentication.roles = decodedToken.roles;
    } else {
      await this.reconnect();
    }

    this.state.authentication.initialized = true;

    return this.updateProfile();
  },

  decodeToken(accessToken: string) {
    const decodedToken = <DecodedJwt | null>jwt.decode(accessToken);

    if (!decodedToken || !decodedToken.exp) {
      return null;
    }

    const expiresAt = decodedToken.exp * 1000;
    const now = Date.now();

    if (expiresAt > now) {
      return decodedToken;
    }

    return null;
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

      const decodedToken = this.decodeToken(response.accessToken);

      if (decodedToken) {
        this.state.authentication.roles = decodedToken.roles;
      }
    }
  },

  hasSession() {
    return !!this.state.authentication.accessToken;
  },

  clearSession() {
    localStorage.removeItem('app/accessToken');

    this.state.authentication.accessToken = null;
    this.state.authentication.roles = [];

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
