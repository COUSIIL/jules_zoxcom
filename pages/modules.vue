<template>
    <div class="flex flex-col items-center justify-center w-full">
        <div class="w-11/12 my-2.5 rounded-lg p-2.5 flex justify-center items-center flex-col bg-whitly shadow-[0_4px_8px_rgba(59,59,59,0.125)] text-center dark:bg-darkly">
            <Ups/>
        </div>
        <div class="w-11/12 my-2.5 rounded-lg p-2.5 flex justify-center items-center flex-col bg-whitly shadow-[0_4px_8px_rgba(59,59,59,0.125)] text-center dark:bg-darkly">
            <Anderson/>
        </div>
        <div class="w-11/12 my-2.5 rounded-lg p-2.5 flex justify-center items-center flex-col bg-whitly shadow-[0_4px_8px_rgba(59,59,59,0.125)] text-center dark:bg-darkly">
            <Yalidine/>
        </div>

        <div class="w-11/12 my-2.5 rounded-lg p-2.5 flex justify-center items-center flex-col bg-whitly shadow-[0_4px_8px_rgba(59,59,59,0.125)] text-center dark:bg-darkly">
            <Guepex/>
        </div>
    </div>
    

</template>

<script setup>

import Ups from '../components/modules/ups.vue';
import Anderson from '../components/modules/anderson.vue';
import Yalidine from '../components/modules/yalidine.vue';
import Guepex from '../components/modules/guepex.vue';




</script>