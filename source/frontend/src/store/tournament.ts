import smashArchive from '../service/smasharchive';
import { UserStore } from './';

export interface TournamentStore {
  state: {
    tournaments: any[];
  };
  getTournaments: () => Promise<any[]>;
}

const store: TournamentStore = {
  state: {
    tournaments: [],
  },

  async getTournaments() {
    if (this.state.tournaments.length === 0) {
      const response = await smashArchive.tournaments.getAll();

      this.state.tournaments = response.data;
    }

    return this.state.tournaments;
  },
};

export default store;
