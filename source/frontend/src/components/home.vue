<template>
  <div>
    <p>
      Hello smasher!
    </p>
    <p>
      If you're reading this it means you have found an early version of the
      SmashArchive project. The purpose of this early version is to confirm that
      the data from the previous SmashRanking project has been imported
      correctly and that new data can be imported from smash.gg and Challonge.
      Obviously the project is far from done, but a large part of the back-end
      is already in place, and some data can already be accessed through an API.
    </p>
    <br />
    <h4>To-do</h4>
    <ul>
      <li>
        Add additional API endpoints to retrieve information about tournaments
        and players.
      </li>
      <li>
        Improve the way tournaments and players are shown in the front-end.
      </li>
      <li>
        Add API endpoints to add and modify tournaments and players.
      </li>
      <li>
        Create an admin panel for admins to manage the data in the database.
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.component('home', {});
</script>

<style>
body {
  padding-top: 80px;
}
</style>
