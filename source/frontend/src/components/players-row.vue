<template>
    <tr>
        <td>
            <a href="#">{{ player.gamerTag }}</a>
        </td>
        <td>{{ player.location }}</td>
    </tr>
</template>

<script lang="ts">

import Vue from 'vue';

export default Vue.component('players-row', {
    props: ['player'],
});

</script>
