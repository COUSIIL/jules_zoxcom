<template>
    <div class="chat-container">
      <div class="chat-box">
        <div v-for="(msg, index) in messages" :key="index" :class="['message', msg.role]">
          {{ msg.content }}
        </div>
      </div>
  
      <div class="input-box">
        <input v-model="userInput" @keyup.enter="sendMessage" :placeholder="t('write a message...')" />
        <button @click="sendMessage">➤</button>
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
  
  <style>
  /* Style minimaliste du chat */
  .chat-container {
    max-width: 600px;
    margin: auto;
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #f8f8f8;
    border-radius: 10px;
    padding: 10px;
  }
  
  .chat-box {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    display: flex;
    flex-direction: column;
  }
  
  .message {
    max-width: 80%;
    padding: 10px;
    margin: 5px 0;
    border-radius: 8px;
    line-height: 1.4;
  }
  
  .bot {
    align-self: flex-start;
    background: #e0e0e0;
  }
  
  .user {
    align-self: flex-end;
    background: #007aff;
    color: white;
  }
  
  .input-box {
    display: flex;
    gap: 5px;
    padding: 10px;
    background: white;
    border-top: 1px solid #ddd;
  }

  .input-box button {
    background: #007aff;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
  }
  
  input {
    flex: 1;
    padding: 10px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
  }
  
  </style>
  