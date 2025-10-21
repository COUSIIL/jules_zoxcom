<template>
    <div style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="boxContainer">
            <Ups/>
        </div>
        <div class="boxContainer">
            <Anderson/>
        </div>
        <div class="boxContainer">
            <Yalidine/>
        </div>

        <div class="boxContainer">
            <Guepex/>
        </div>
        <div class="boxContainer">
            <InstagramScraper/>
        </div>
    </div>
    

</template>

<script setup>

import Ups from '../components/modules/ups.vue';
import Anderson from '../components/modules/anderson.vue';
import Yalidine from '../components/modules/yalidine.vue';
import Guepex from '../components/modules/guepex.vue';
import InstagramScraper from '../components/modules/InstagramScraper.vue';




</script>

<style>
.boxContainer {
    width: 90%;
    margin: 10px;
    border-radius: 8px;
    padding-block: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: var(--color-whitly);
    box-shadow: 0 4px 8px #3b3b3b20;
    text-align: center;
  }
  .dark .boxContainer {
    background-color: var(--color-darkly);
  }
</style>