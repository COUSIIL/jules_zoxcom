<template>

    <div class="bubble" :style="color">
        <div class="bubble-img-wrapper">
            <div v-if="props.img" v-html="resizeSvg(props.img, 20, 20)"></div>
            <img v-else src="public/z.svg" alt="z">
        </div>
        <h3 v-if="props.text">
            {{ props.text }}
        </h3>
        
    </div>


</template>

<script setup>

    import iconsFilled from '../../../public/iconsFilled.json'
    import icons from '../../../public/icons.json'

    const props = defineProps({
        text: {type: String, required: false},
        img: {type: String, required: false},
        color: {type: String, required: false}  
    })

    var resizeSvg = (svg, width, height) => {
        if(!width) {
            width = 20
        }
        if(!height) {
            height = 20
        
        }
        if(iconsFilled[svg]) {
            return iconsFilled[svg]
            .replace(/width="[^"]+"/, `width="${width}"`)
            .replace(/height="[^"]+"/, `height="${height}"`)
        } else {
            return icons[svg]
            .replace(/width="[^"]+"/, `width="${width}"`)
            .replace(/height="[^"]+"/, `height="${height}"`)
        }

    }


</script>

<style scoped>

    .bubble {
        display: flex;
        justify-content: left;
        align-items: center;
        gap: 5px;
        padding: 5px;
        height: 25px;
        min-height: 25px;
        min-width: 25px;
        background-color: var(--color-whitly);
        border-radius: 20px;
        box-shadow: 
        2px  2px 2px 0px #aca7afc2;
    }

    .dark .bubble {
        background: var(--color-darkly);
        box-shadow: 
        2px 2px 2px 0px rgba(0, 0, 0, 0.761);
    }

    .bubble h3 {
        max-width: 100px;
        min-width: 70px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 12px;
        font-weight: 500;
        padding-right: 10px;
    }

    .bubble-img-wrapper {
        width: 20px;
        min-width: 20px;
        height: 20px;
        min-height: 20px;
        border-radius: 50%;
        overflow: hidden;
    }



</style>