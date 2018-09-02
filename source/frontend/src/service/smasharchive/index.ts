import axios, { AxiosInstance } from 'axios';
import Tournaments from './tournaments';
import Users from './users';

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashArchive {
    /**
     * @type {Object}
     */
    public tournaments: Tournaments;

    /**
     * @type {Object}
     */
    public users: Users;

    constructor () {
        const agent: AxiosInstance = axios.create({
            baseURL: 'http://localhost:8000/api/',
        });

        this.tournaments = new Tournaments(agent);
        this.users = new Users(agent)
    }
}

const smashArchive: SmashArchive = new SmashArchive();

export default smashArchive;
