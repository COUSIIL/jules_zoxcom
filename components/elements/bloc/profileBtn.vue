<template>
  <div v-if="isLargeScreen"  style="height: 30px; display: flex; justify-content: space-between; align-items: center;">
    <NuxtLink class="title2" :to="targetLink">
      <div v-if="isAuth" class="circleProfile">
        <img :src="`https://management.hoggari.com/uploads/profile/${profileImage}`" :alt="user">
      </div>
      <div class="name">
        {{ user }}
      </div>
    
    </NuxtLink>
  </div>

  <div v-else style="height: 30px; display: flex; justify-content: space-between; align-items: center;">
    <NuxtLink class="title3" :to="targetLink">
      <div v-if="isAuth" class="circleProfile">
        <img :src="`https://management.hoggari.com/uploads/profile/${profileImage}`" :alt="user">
      </div>
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

  const profileImage = ref('')
  const targetLink = ref('/connexion')

  onMounted(() => {
    testLogin()
  })

  defineProps({
    user: String,
  })

  const testLogin = async () => {
    const stored = localStorage.getItem('auth')

    if (stored) {
      //const data = JSON.parse(stored) // maintenant c’est un objet JS
      const data2 = JSON.parse(stored) // maintenant c’est un objet JS
       // tu peux y accéder directement
      profileImage.value = data2.profile_image
      if (data2.username) {
        targetLink.value = `/team/${data2.username}`
      } else if (data2.user) {
        targetLink.value = `/team/${data2.user}`
      }
    }
  }
</script>

<style>

.circleProfile {
  width: 24px;
  height: 24px;
  min-width: 24px;
  min-height: 24px;
  background-color: white;
  border-radius: 50%;
  overflow: hidden; /* coupe ce qui dépasse pour rester rond */
  display: flex;
  align-items: center;
  justify-content: center;
}

.circleProfile img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* remplit le cercle en gardant le ratio */
  display: block;
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