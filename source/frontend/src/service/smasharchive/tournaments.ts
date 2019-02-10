import { AxiosInstance } from 'axios';

export default class Tournaments {
  constructor(private agent: AxiosInstance) {}

  public async getAll() {
    const response = await this.agent.get('/tournaments/');

    return response.data;
  }
}
