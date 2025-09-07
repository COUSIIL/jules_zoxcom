<template>
    <div class="flex flex-col h-full max-w-lg p-2.5 mx-auto mt-5 bg-gray-100 rounded-lg">
      <div class="flex flex-col flex-1 p-2.5 overflow-y-auto">
        <div v-for="(msg, index) in messages" :key="index"
             :class="[
                'max-w-[80%] p-2.5 my-1.25 rounded-lg leading-snug',
                msg.role === 'bot' ? 'self-start bg-gray-200' : 'self-end bg-blue-500 text-white'
             ]">
          {{ msg.content }}
        </div>
      </div>
  
      <div class="flex gap-1.25 p-2.5 bg-white border-t border-gray-300">
        <input v-model="userInput" @keyup.enter="sendMessage" :placeholder="t('write a message...')" class="flex-1 p-2.5 text-base border-none rounded-lg" />
        <button @click="sendMessage" class="px-4 py-2.5 text-base text-white bg-blue-500 border-none rounded-lg cursor-pointer">➤</button>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import { useLang } from '~/composables/useLang';
  
  export default {
    setup() {
      const { t } = useLang();
      const messages = ref([
        { role: 'bot', content: t('hello! how can i help you?') }
      ]);
      const userInput = ref('');
  
      const sendMessage = async () => {
        if (!userInput.value.trim()) return;

        messages.value.push({ role: 'user', content: userInput.value });

        try {
          const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatGemini', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userInput.value })
          });

          const data = await response.json();
          const reply = data.reply || t('response unavailable.');
          messages.value.push({ role: 'bot', content: reply });

        } catch (error) {
          messages.value.push({ role: 'bot', content: t('connection error with the ia.') });
        }

        userInput.value = '';
      };

  
      return { messages, userInput, sendMessage, t };
    }
  };
  </script>
  