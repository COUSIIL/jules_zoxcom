<template>
  <div v-if="isMounted">

    <WebStore/>

  </div>
</template>

<script setup>

import { ref, onMounted } from 'vue';
import WebStore from '../components/store/webStore.vue';
import Explorer from '../components/elements/explorer.vue';
import EmailEdit from '../components/elements/emailEdit.vue';
import EditCategory from '../components/elements/editCategory.vue';

const isMounted = ref(true);
var log = ref('');

onMounted(() => {
  
  //chatWithDeepSeek();
  //chatGpt();
  //chatMistral();
  //isMounted.value = true;
});


async function sendEmail() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=sendEmailOrder', {
      method: 'GET',
    });
    if (!response.ok) {
      log.value = 'error in getting response category'
    }
    const result = await response.json();
    if (result.success) {
      log.value = result.message
    } else {
      log.value = result.message
    }
}



async function chatWithDeepSeek() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatDeepSeek', {
      method: 'GET',
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
    console.log('result: ', result);
}

async function chatGpt() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatGPT', {
      method: 'GET',
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
    console.log('result: ', result);
}

async function chatMistral() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatMistral', {
      method: 'POST',
      body: JSON.stringify({ message: "vous êtes qui ?" })
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
}
</script>