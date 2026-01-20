<template>
    <div class="modules-page">
        <div class="grid-container">
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
                <OrderDz/>
            </div>
        </div>
    </div>
</template>

<script setup>
import Ups from '../components/modules/ups.vue';
import Anderson from '../components/modules/anderson.vue';
import OrderDz from '../components/modules/orderDz.vue';
import Yalidine from '../components/modules/yalidine.vue';
import Guepex from '../components/modules/guepex.vue';
</script>

<style scoped>
.modules-page {
    width: 100%;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    width: 100%;
    max-width: 1400px;
}

.boxContainer {
    border-radius: 12px;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: var(--color-whitly);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.boxContainer:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.dark .boxContainer {
    background-color: var(--color-darkly);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>
