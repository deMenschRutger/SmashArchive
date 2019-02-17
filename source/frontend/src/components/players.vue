<template>
  <div class="row">
    <div class="col-md-3 well">
      <h3>Players</h3>
      <br />
      __filters__
    </div>
    <div class="col-md-9">
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
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { PlayerStore } from '../store';

export default Vue.component('players', {
  data: () => {
    return {
      store: PlayerStore,
    };
  },

  created() {
    return PlayerStore.updatePlayers();
  },
});
</script>
