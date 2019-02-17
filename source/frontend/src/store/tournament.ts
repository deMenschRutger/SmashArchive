import smashArchive from '../service/smasharchive';
import { Tournament } from '../service/smasharchive/tournaments';
import { Pagination } from '../service/smasharchive/types';
import { UserStore } from './';

export interface TournamentStore {
  state: {
    tournaments: Tournament[];
    pagination?: Pagination;
  };
  getTournaments: () => Promise<Tournament[]>;
}

const store: TournamentStore = {
  state: {
    tournaments: [],
    pagination: undefined,
  },

  async getTournaments() {
    if (this.state.tournaments.length === 0) {
      const response = await smashArchive.tournaments.getAll();

      this.state.tournaments = response.data;
      this.state.pagination = response.pagination;
    }

    return this.state.tournaments;
  },
};

export default store;
