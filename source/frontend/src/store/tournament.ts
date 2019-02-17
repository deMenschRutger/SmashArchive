import smashArchive from '../service/smasharchive';
import { Tournament } from '../service/smasharchive/tournaments';
import { Pagination } from '../service/smasharchive/types';
import { UserStore } from './';

export interface TournamentStore {
  state: {
    filters: {
      limit: number;
      page: number;
    };
    filtersUpdated: boolean;
    tournaments: Tournament[];
    pagination?: Pagination;
  };
  updateTournaments: () => Promise<void>;
  changePage: (pageNumber: number) => Promise<void>;
}

const store: TournamentStore = {
  state: {
    filters: {
      limit: 50,
      page: 1,
    },
    filtersUpdated: true,
    tournaments: [],
    pagination: undefined,
  },

  async updateTournaments() {
    if (!this.state.filtersUpdated) {
      return;
    }

    const response = await smashArchive.tournaments.getAll(
      this.state.filters.limit,
      this.state.filters.page,
    );

    this.state.tournaments = response.data;
    this.state.pagination = response.pagination;

    this.state.filtersUpdated = false;
  },

  async changePage(pageNumber) {
    this.state.filters.page = pageNumber;
    this.state.filtersUpdated = true;

    await this.updateTournaments();
  },
};

export default store;
