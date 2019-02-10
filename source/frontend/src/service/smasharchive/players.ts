import { AxiosInstance } from 'axios';

export default class Players {
  constructor(private agent: AxiosInstance) {}

  public async getAll(accessToken: string) {
    const response = await this.agent.get('/profiles/', {
      headers: {
        Authorization: 'Bearer ' + accessToken,
      },
    });

    return response.data;
  }
}
