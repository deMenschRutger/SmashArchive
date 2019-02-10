<template>
  <div class="row">
    <div class="col-md-3 well">
      <h3>Tournaments</h3>
      <br />
      __filters__
    </div>
    <div class="col-md-9">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Location</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <tournaments-row
            v-for="tournament in tournaments"
            v-bind:key="tournament.id"
            v-bind:tournament="tournament"
          ></tournaments-row>
        </tbody>
      </table>
      <div class="text-center">
        __pagination__
        <br />
        <br />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { TournamentStore, UserStore } from '../store';

export default Vue.component('tournaments', {
  data: () => {
    return {
      tournaments: [] as any[],
    };
  },

  async created() {
    this.tournaments = await TournamentStore.getTournaments();
  },
});
</script>
