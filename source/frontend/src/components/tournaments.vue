<template>
    <div class="row">
        <div class="col-md-3 well">
            <h3>Tournaments</h3>
            <br>
            Filters
        </div>
        <div class="col-md-9">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tournaments-row
                        v-for="tournament in tournaments"
                        v-bind:key="tournament.id"
                        v-bind:tournament="tournament"></tournaments-row>
                </tbody>
            </table>
            <div class="text-center">
                Pagination
            </div>
        </div>
    </div>
</template>

<script lang="ts">

import Vue from 'vue';
import { TournamentStore } from '../store';

export default Vue.component('tournaments', {
    data: () => {
        return {
            tournaments: [],
        }
    },

    created: async function (): Promise<void> {
        this.tournaments = await TournamentStore.getTournaments();
    }
});

</script>
