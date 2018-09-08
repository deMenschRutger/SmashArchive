import { AxiosInstance, AxiosResponse } from 'axios';

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
export default class Players {
    /**
     * @param {Object} agent
     */
    constructor (private agent: AxiosInstance) {}

    /**
     * @return {Promise<*>}
     */
    public async getAll (): Promise<any> {
        const response: AxiosResponse = await this.agent.get('/players/');

        return response.data;
    }
}
