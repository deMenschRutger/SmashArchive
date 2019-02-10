import { AxiosInstance } from 'axios';

export default class Players {
  constructor(private agent: AxiosInstance) {}

  public async getAll() {
    const response = await this.agent.get('/profiles/');

    return response.data;
  }
}
