<template>
  <div class="row">
    <div class="col-md-3 well">
      <h3>Players</h3>
      <br />
      <form v-on:submit.prevent="updateFilters">
        <div class="form-group">
          <label for="filterTag">Tag</label>
          <input type="text" class="form-control" id="filterTag" v-model="tag">
        </div>
        <div class="form-group">
          <label for="filterLocation">Location</label>
          <input type="text" class="form-control" id="filterLocation" v-model="location">
        </div>
        <button type="submit" class="btn btn-default">Filter</button>
      </form>
    </div>
    <div class="col-md-9" v-if="store.state.players.length > 0">
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
          </tr>
        </thead>
        <tbody>
          <players-row
            v-for="player in store.state.players"
            v-bind:key="player.id"
            v-bind:player="player"
          ></players-row>
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
import { PlayerStore } from '../store';

export default Vue.component('players', {
  data: () => {
    return {
      store: PlayerStore,
      tag: undefined,
      location: undefined,
    };
  },

  created() {
    return PlayerStore.updatePlayers();
  },

  methods: {
    updateFilters () {
      return this.store.updateFilter({
        page: 1,
        tag: this.tag,
        location: this.location,
      });
    }
  },
});
</script>
