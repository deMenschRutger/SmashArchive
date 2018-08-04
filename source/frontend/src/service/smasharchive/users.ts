import { AxiosInstance, AxiosResponse } from 'axios';

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
export default class Users {
    /**
     * @param {Object} agent
     */
    constructor (private agent: AxiosInstance) {}

    /**
     * @param {string} accessToken
     *
     * @return {Promise<*>}
     */
    public async login(accessToken: string): Promise<any> {
        const response: AxiosResponse = await this.agent.post('/users/login/', {
            accessToken: accessToken,
        });

        return response.data.data;
    }

    /**
     * @param {string} accessToken
     *
     * @return {Promise<*>}
     */
    public async me(accessToken: string): Promise<any> {
        const response: AxiosResponse = await this.agent.get('/users/me/', {
            headers: {
                Authorization: 'Bearer ' + accessToken,
            }
        });

        return response.data.data;
    }
}
