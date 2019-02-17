import smashArchive from '../service/smasharchive';
import { Player } from '../service/smasharchive/players';
import { UserStore } from './';

export interface PlayerStore {
  state: {
    players: Player[];
  };
  getPlayers: () => Promise<Player[]>;
}

const store: PlayerStore = {
  state: {
    players: [],
  },

  async getPlayers() {
    if (this.state.players.length === 0) {
      this.state.players = await smashArchive.players.getAll();
    }

    return this.state.players;
  },
};

export default store;
