import { AxiosInstance } from 'axios';

export default class Tournaments {
  constructor(private agent: AxiosInstance) {}

  public async getAll(accessToken: string) {
    const response = await this.agent.get('/tournaments/', {
      headers: {
        Authorization: 'Bearer ' + accessToken,
      },
    });

    return response.data;
  }
}
