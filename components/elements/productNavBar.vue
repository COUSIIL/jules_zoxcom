<template>
    <div style="margin-inline: 20px;">
        <div class="navBarBox">
            <ProductNavBarBtn :svg="'package'" @click="doClicker(1)" :clicker="clickingList[0].click"/>
            <ProductNavBarBtn :svg="'tag'" @click="doClicker(2)" :clicker="clickingList[1].click"/>
            <ProductNavBarBtn :svg="'stack'" @click="doClicker(3)" :clicker="clickingList[2].click"/>
            <ProductNavBarBtn :svg="'adjust'" @click="doClicker(4)" :clicker="clickingList[3].click"/>
            <ProductNavBarBtn :svg="'function'" @click="doClicker(5)" :clicker="clickingList[4].click"/>
            <ProductNavBarBtn :svg="'screen'" @click="doClicker(6)" :clicker="clickingList[5].click"/>
            <ProductNavBarBtn :svg="'order'" @click="doClicker(7)" :clicker="clickingList[6].click"/>
        </div>
    </div>
    
</template>

<script setup>

import ProductNavBarBtn from './newBloc/productNavBarBtn.vue';

const clickingList = ref([{click: true}, {click: false}, {click: false}, {click: false}, {click: false}, {click: false}, {click: false}])

const emit = defineEmits(['getClick'])

const doClicker = (val) => {
    
    

    for(let i in clickingList.value) {

        if(val === (parseInt(i)+1)) {
            clickingList.value[i].click = true
            emit('getClick', val)
        } else {
            clickingList.value[i].click = false
        }
        
    }


}



</script>

<style scoped>

    .navBarBox{
        position:fixed;
        top: 10px;
        right: -10px;
        display: flex;
        justify-content: left;
        align-items: center;
        width: 100%;
        height: 100px;
        gap: 10px;
        z-index: 2000;
    }

    .navBarBox > * {
        pointer-events: auto;
    }

</style>