import { AxiosInstance } from 'axios';
import { Pagination } from './types';

export type Tournament = {
  id: number;
  slug: string;
  source: string;
  name: string;
  country?: {
    id: number;
    code: string;
    name: string;
  };
  region?: string;
  city?: string;
  location?: string;
  dateStart?: string;
  dateEnd?: string;
  timeZone?: string;
  playerCount?: number;
  isComplete: boolean;
};

export type TournamentResponse = {
  data: Tournament[];
  pagination: Pagination;
};

export default class Tournaments {
  constructor(private agent: AxiosInstance) {}

  public async getAll(): Promise<TournamentResponse> {
    const response = await this.agent.get('/tournaments/', {
      params: {
        page: 3,
        limit: 5,
      },
    });

    return response.data;
  }
}
