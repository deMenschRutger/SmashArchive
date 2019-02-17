<template>
  <div class="text-center">
    <nav v-if="pagination">
      <ul class="pagination">
        <li>
          <a
            href="#"
            v-on:click.prevent="changePageNumber(pagination.previous)"
          >
            <span>&laquo;</span>
          </a>
        </li>
        <li
          v-for="pageNumber in pagination.pagesInRange"
          v-bind:class="{ active: pageNumber === pagination.current }"
        >
          <a href="#" v-on:click.prevent="changePageNumber(pageNumber)">{{
            pageNumber
          }}</a>
        </li>
        <li>
          <a href="#" v-on:click.prevent="changePageNumber(pagination.next)">
            <span>&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.component('pagination', {
  props: ['pagination', 'store'],

  methods: {
    changePageNumber (pageNumber: number) {
      return this.store.updateFilter({
        page: pageNumber,
      });
    }
  }
});
</script>
