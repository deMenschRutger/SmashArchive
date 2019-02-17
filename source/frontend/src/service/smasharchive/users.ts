import { AxiosInstance } from 'axios';

export type AccessTokenResponse = {
  accessToken: string;
};

export type User = {
  id: number;
  username: string;
};

export default class Users {
  constructor(private agent: AxiosInstance) {}

  public async login(accessToken: string): Promise<AccessTokenResponse> {
    const response = await this.agent.post('/users/login/', {
      accessToken: accessToken,
    });

    return response.data.data;
  }

  public async me(accessToken: string): Promise<User> {
    const response = await this.agent.get('/users/me/', {
      headers: {
        Authorization: 'Bearer ' + accessToken,
      },
    });

    return response.data.data;
  }
}
