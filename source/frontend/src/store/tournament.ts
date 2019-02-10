import smashArchive from '../service/smasharchive';
import { Tournament } from '../service/smasharchive/tournaments';
import { UserStore } from './';

export interface TournamentStore {
  state: {
    tournaments: Tournament[];
  };
  getTournaments: () => Promise<Tournament[]>;
}

const store: TournamentStore = {
  state: {
    tournaments: [],
  },

  async getTournaments() {
    if (this.state.tournaments.length === 0) {
      this.state.tournaments = await smashArchive.tournaments.getAll();
    }

    return this.state.tournaments;
  },
};

export default store;
