<template>
  <tr>
    <td>
      <a href="#">{{ tournament.name }}</a>
    </td>
    <td>{{ tournament.location }}</td>
    <td>{{ tournament.dateStart }}</td>
  </tr>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.component('tournaments-row', {
  props: ['tournament'],
});
</script>
