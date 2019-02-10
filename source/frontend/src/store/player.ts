import smashArchive from '../service/smasharchive';
import { UserStore } from './';

export interface PlayerStore {
  state: {
    players: any[];
  };
  getPlayers: () => Promise<any[]>;
}

const store: PlayerStore = {
  state: {
    players: [],
  },

  async getPlayers() {
    if (this.state.players.length === 0) {
      const response = await smashArchive.players.getAll(UserStore.getAccessToken());

      this.state.players = response.data;
    }

    return this.state.players;
  },
};

export default store;
