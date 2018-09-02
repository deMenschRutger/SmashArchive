import smashArchive from '../service/smasharchive';

export interface TournamentStore {
    state: {
        tournaments: any[];
    };
    getTournaments: () => Promise<any[]>;
}

const store: TournamentStore = {
    state: {
        tournaments: [],
    },

    /**
     * @return {Promise<*[]>}
     */
    async getTournaments(): Promise<any[]> {
        if (this.state.tournaments.length === 0) {
            const response: any = await smashArchive.tournaments.getAll();

            this.state.tournaments = response.data;
        }

        return this.state.tournaments;
    },
};

export default store;
