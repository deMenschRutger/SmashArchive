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

  public async getAll(limit: number, page: number): Promise<PlayerResponse> {
    const params: { [key: string]: string | number } = { limit, page };

    const response = await this.agent.get('/profiles/', { params });

    return response.data;
  }
}
