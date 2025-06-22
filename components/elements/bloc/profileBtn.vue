<template>
  <div v-if="isLargeScreen"  style="height: 30px; display: flex; justify-content: space-between; align-items: center;">
    <NuxtLink class="title2" to="/connexion">
      <div v-if="isAuth" class="circleProfile"></div>
      <div class="name">
        {{ user }}
      </div>
    
    </NuxtLink>
  </div>

  <div v-else style="height: 30px; display: flex; justify-content: space-between; align-items: center;">
    <NuxtLink class="title3" to="/connexion">
      <div v-if="isAuth" class="circleProfile"></div>
      <div v-else>
        <div class="name">
          {{ user }}
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<script setup>
const isAuth = useState('isAuth')
const isLargeScreen = useState('isLargeScreen')

defineProps({
  user: String,
})
</script>

<style>

.circleProfile{
  width: 24px; 
  height: 24px; 
  min-width: 24px; 
  min-height: 24px; 
  background-color: white; 
  border-radius: 50%;
}

.name{
  width: 100px;
  max-width: 120px; 
  max-height: 28px; 
  min-width: 50px; 
  min-height: 28px; 
  display: flex;
  align-items: center;
  margin-inline: 2px;
}


.title2 {
width: 200px;
height: 28px;
max-width: 200px; 
max-height: 28px; 
min-width: 100px; 
min-height: 28px; 
display: flex;
justify-content: space-between;
align-items: center;
margin-inline: 5px;
padding: 2px;
border-radius: 60px;
background: var(--color-zioly2);
color: var(--color-whizy);
font-size: 1.8vh;
font-weight: bold;
}

.title3 {
height: 28px;
max-width: 150px; 
max-height: 28px; 
min-width: 28px; 
min-height: 28px; 
display: flex;
justify-content: center;
align-items: center;
margin-inline: 5px;
border-radius: 60px;
background: var(--color-zioly2);
color: var(--color-whizy);
box-shadow: 0px 0px 4px var(--color-zioly2);
font-size: 1.8vh;
font-weight: bold;
border: 1px solid var(--color-zioly1);
}
/*.title2:hover {
background: var(--color-rangy);
box-shadow: 0px 0px 6px var(--color-hoggari);
}*/

.dark .title2 {
  box-shadow: 0px 0px 4px var(--color-darky);
}

.dark .title3 {
  box-shadow: 0px 0px 4px var(--color-darky);
}
</style>