<template>
  <div class="row">
    <div class="col-md-3 well">
      <h3>Players</h3>
      <br />
      __filters__
    </div>
    <div class="col-md-9">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Location</th>
          </tr>
        </thead>
        <tbody>
          <players-row
            v-for="player in players"
            v-bind:key="player.id"
            v-bind:player="player"
          ></players-row>
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
import { PlayerStore } from '../store';

export default Vue.component('players', {
  data: () => {
    return {
      players: [],
    };
  },

  async created() {
    await PlayerStore.getPlayers();
  },
});
</script>
