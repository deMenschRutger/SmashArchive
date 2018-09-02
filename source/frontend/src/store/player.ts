import smashArchive from '../service/smasharchive';

export interface PlayerStore {
    state: {
        players: any[];
    };
    getPlayers: () => Promise<any[]>;
}

const store: PlayerStore = {
    state: {
        players: [],
    },

    /**
     * @return {Promise<*[]>}
     */
    async getPlayers(): Promise<any[]> {
        if (this.state.players.length === 0) {
            const response: any = await smashArchive.players.getAll();

            this.state.players = response.data;
        }

        return this.state.players;
    },
};

export default store;
