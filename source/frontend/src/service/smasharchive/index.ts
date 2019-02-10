import axios from 'axios';
import Players from './players';
import Tournaments from './tournaments';
import Users from './users';

class SmashArchive {
  public players: Players;
  public tournaments: Tournaments;
  public users: Users;

  constructor() {
    const agent = axios.create({
      baseURL: 'http://localhost:8000/api/',
    });

    this.players = new Players(agent);
    this.tournaments = new Tournaments(agent);
    this.users = new Users(agent);
  }
}

const smashArchive = new SmashArchive();

export default smashArchive;
