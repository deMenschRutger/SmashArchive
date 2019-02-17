import * as _ from 'lodash';
import smashArchive from '../service/smasharchive';
import { Tournament } from '../service/smasharchive/tournaments';
import { Pagination } from '../service/smasharchive/types';
import { UserStore } from './';

export interface TournamentStore {
  state: {
    filters: {
      limit: number;
      page: number;
      name: string | undefined;
      location: string | undefined;
    };
    filtersUpdated: boolean;
    tournaments: Tournament[];
    pagination?: Pagination;
  };
  updateTournaments: () => Promise<void>;
  updateFilter: (values: {
    [key: string]: string | number | undefined;
  }) => Promise<void>;
}

const store: TournamentStore = {
  state: {
    filters: {
      limit: 50,
      page: 1,
      name: undefined,
      location: undefined,
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
      this.state.filters.name,
      this.state.filters.location,
    );

    this.state.tournaments = response.data;
    this.state.pagination = response.pagination;

    this.state.filtersUpdated = false;
  },

  async updateFilter(values) {
    _.each(values, (value, key) => {
      if (this.state.filters[key] === value) {
        return;
      }

      this.state.filters[key] = value;
      this.state.filtersUpdated = true;
    });

    await this.updateTournaments();
  },
};

export default store;
