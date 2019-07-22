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
    <p>
      If you are a developer interested in contributing to the project, please
      visit <a href="https://github.com/deMenschRutger/SmashArchive">our GitHub repository</a>.
    </p>
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
