import { AxiosInstance } from 'axios';

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

export default class Players {
  constructor(private agent: AxiosInstance) {}

  public async getAll(): Promise<Player[]> {
    const response = await this.agent.get('/profiles/');

    return response.data.data;
  }
}
