<template>
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button
          type="button"
          class="navbar-toggle collapsed"
          data-toggle="collapse"
          data-target="#navbar"
          aria-expanded="false"
          aria-controls="navbar"
        >
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <router-link to="/" class="navbar-brand">SmashArchive</router-link>
      </div>
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
          <li>
            <router-link to="/tournaments">Tournaments</router-link>
          </li>
          <li>
            <router-link to="/players">Players</router-link>
          </li>
          <li v-if="isAdmin">
            <a>Admin</a>
          </li>
        </ul>
        <authentication></authentication>
      </div>
    </div>
  </nav>
</template>

<script lang="ts">
import Vue from 'vue';
import { UserStore } from '../store';

export default Vue.component('navigation', {
  data: () => {
    return {
      UserStore,
    };
  },

  computed: {
    isAdmin() {
      return UserStore.state.authentication.roles.indexOf('ROLE_ADMIN') >= 0;
    },
  },
});
</script>
