import { AxiosInstance } from 'axios';

export default class Users {
  constructor(private agent: AxiosInstance) {}

  public async login(accessToken: string) {
    const response = await this.agent.post('/users/login/', {
      accessToken: accessToken,
    });

    return response.data.data;
  }

  public async me(accessToken: string) {
    const response = await this.agent.get('/users/me/', {
      headers: {
        Authorization: 'Bearer ' + accessToken,
      },
    });

    return response.data.data;
  }
}
