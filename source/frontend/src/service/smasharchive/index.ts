import axios, { AxiosInstance } from 'axios';
import Users from './users';

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashArchive {
    /**
     * @type {Object}
     */
    public users: Users;

    constructor () {
        const agent: AxiosInstance = axios.create({
            baseURL: 'http://localhost:8000/api/v0.1/',
        });

        this.users = new Users(agent)
    }
}

const smashArchive: SmashArchive = new SmashArchive();

export default smashArchive;
