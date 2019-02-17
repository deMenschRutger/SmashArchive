import * as _ from 'lodash';
import smashArchive from '../service/smasharchive';
import { Player } from '../service/smasharchive/players';
import { Pagination } from '../service/smasharchive/types';
import { UserStore } from './';

export interface PlayerStore {
  state: {
    filters: {
      limit: number;
      page: number;
    };
    filtersUpdated: boolean;
    players: Player[];
    pagination?: Pagination;
  };
  updatePlayers: () => Promise<void>;
  updateFilter: (values: {
    [key: string]: string | number | undefined;
  }) => Promise<void>;
}

const store: PlayerStore = {
  state: {
    filters: {
      limit: 50,
      page: 1,
    },
    filtersUpdated: true,
    players: [],
    pagination: undefined,
  },

  async updatePlayers() {
    if (!this.state.filtersUpdated) {
      return;
    }

    const response = await smashArchive.players.getAll(
      this.state.filters.limit,
      this.state.filters.page,
    );

    this.state.players = response.data;
    this.state.pagination = response.pagination;

    this.state.filtersUpdated = false;
  },

  async updateFilter(values) {
    _.each(values, (value, key) => {
      this.state.filters[key] = value;
    });

    this.state.filtersUpdated = true;

    await this.updatePlayers();
  },
};

export default store;
