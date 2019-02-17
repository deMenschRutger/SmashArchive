<template>
  <div class="row">
    <div class="col-md-3 well">
      <h3>Tournaments</h3>
      <br />
      <form v-on:submit.prevent="updateFilters">
        <div class="form-group">
          <label for="filterName">Name</label>
          <input type="text" class="form-control" id="filterName" v-model="name">
        </div>
        <div class="form-group">
          <label for="filterLocation">Location</label>
          <input type="text" class="form-control" id="filterLocation" v-model="location">
        </div>
        <button type="submit" class="btn btn-default">Filter</button>
      </form>
    </div>
    <div class="col-md-9" v-if="store.state.tournaments.length > 0">
      <pagination
        v-bind:pagination="store.state.pagination"
        v-bind:store="store"
      ></pagination>
      <br />
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
            v-for="tournament in store.state.tournaments"
            v-bind:key="tournament.id"
            v-bind:tournament="tournament"
          ></tournaments-row>
        </tbody>
      </table>
      <pagination
        v-bind:pagination="store.state.pagination"
        v-bind:store="store"
      ></pagination>
      <br />
      <br />
    </div>
    <div class="col-md-9" v-else>
      Could not find any results for your query.
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { TournamentStore, UserStore } from '../store';

export default Vue.component('tournaments', {
  data: () => {
    return {
      store: TournamentStore,
      name: undefined,
      location: undefined,
    };
  },

  created() {
    return TournamentStore.updateTournaments();
  },

  methods: {
    updateFilters () {
      return this.store.updateFilter({
        page: 1,
        name: this.name,
        location: this.location,
      });
    }
  },
});
</script>
