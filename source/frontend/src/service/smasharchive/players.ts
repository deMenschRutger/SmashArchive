import { AxiosInstance } from 'axios';
import { Pagination } from './types';

export type Player = {
  id: number;
  slug: string;
  gamerTag: string;
  name?: string;
  nationality?: {
    id: number;
    code: string;
    name: string;
  };
  country?: {
    id: number;
    code: string;
    name: string;
  };
  region?: string;
  city?: string;
  location?: string;
  isCompeting: boolean;
  isActive: boolean;
};

export type PlayerResponse = {
  data: Player[];
  pagination: Pagination;
};

export default class Players {
  constructor(private agent: AxiosInstance) {}

  public async getAll(
    limit: number,
    page: number,
    tag: string | undefined,
    location: string | undefined,
  ): Promise<PlayerResponse> {
    let params: { [key: string]: string | number } = { limit, page };

    if (tag) {
      params.tag = tag;
    }

    if (location) {
      params.location = location;
    }

    const response = await this.agent.get('/profiles/', { params });

    return response.data;
  }
}
