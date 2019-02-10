import { AxiosInstance } from 'axios';

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

export default class Tournaments {
  constructor(private agent: AxiosInstance) {}

  public async getAll(): Promise<Tournament[]> {
    const response = await this.agent.get('/tournaments/');

    return response.data.data;
  }
}
