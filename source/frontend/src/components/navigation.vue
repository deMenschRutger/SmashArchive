<template>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                        data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#">SmashArchive</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="#">Tournaments</a>
                    </li>
                    <li>
                        <a href="#">Players</a>
                    </li>
                    <authentication></authentication>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script lang="ts">

import Vue from 'vue';

export default Vue.component('navigation', {
});

</script>

<style scoped>

</style>