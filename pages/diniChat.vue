<template>
  <div :class="['dini-chat-layout', { 'dark-mode': isDarkMode }]">
    <!-- Sidebar for conversations -->
    <div class="sidebar">
      <div class="sidebar-header">
        <button class="new-chat-btn" @click="startNewConversation">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
          {{ t('new_chat') }}
        </button>
      </div>
      <div class="conversation-history">
        <div
          v-for="convo in conversations"
          :key="convo.id"
          class="conversation-item"
          :class="{ 'active': convo.id === activeConversationId }"
          @click="selectConversation(convo.id)"
        >
          <span class="convo-title">{{ convo.title }}</span>
          <div class="convo-actions">
            <button @click.stop="renameConversation(convo.id)">✏️</button>
            <button @click.stop="deleteConversation(convo.id)">🗑️</button>
          </div>
        </div>
      </div>
      <div class="sidebar-footer">
        <button @click="toggleDarkMode" class="theme-toggle">
            {{ isDarkMode ? '☀️' : '🌙' }}
        </button>
      </div>
    </div>

    <!-- Main chat panel -->
    <div class="chat-panel">
      <div class="chat-messages" ref="chatMessagesContainer">
        <div v-for="(message, index) in messages" :key="index" :class="['message-bubble', message.role]">
          <img :src="message.role === 'user' ? (user?.profile_image || '/z.svg') : '/dini.svg'" class="avatar" />
          <div class="message-content">
            <p v-html="formatMessage(message.content)"></p>
            <button v-if="message.role === 'assistant'" @click="copyToClipboard(message.content)" class="copy-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
            </button>
          </div>
        </div>
        <div v-if="isReplying" class="message-bubble assistant loading">
            <img src="/dini.svg" class="avatar" />
            <div class="message-content">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
      </div>
      <div class="chat-input-area">
        <textarea
          ref="inputBox"
          v-model="userInput"
          :placeholder="t('write_a_message')"
          @keydown.enter.exact.prevent="sendMessage"
          @keydown.enter.shift.prevent="userInput += '\\n'"
          @input="adjustTextareaHeight"
        ></textarea>
        <button @click="sendMessage" :disabled="isReplying || !userInput.trim()">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { useAuth } from '~/composables/useAuth';
import { useLang } from '~/composables/useLang';
import { marked } from 'marked';

const { t } = useLang();
const { getUser } = useAuth();
const user = computed(() => getUser());

const conversations = ref([]);
const messages = ref([]);
const activeConversationId = ref(null);
const userInput = ref('');
const isReplying = ref(false);
const isDarkMode = ref(false);
const inputBox = ref(null);
const chatMessagesContainer = ref(null);

const API_URL = '/backend/api.php';
const STREAM_URL = '/backend/diniChat/chat_stream.php';

// --- Lifecycle ---
onMounted(() => {
  loadConversations();
  const savedTheme = localStorage.getItem('diniChat-theme');
  if (savedTheme === 'dark') {
    isDarkMode.value = true;
  }
});

// --- API Calls ---
async function fetchApi(action, options = {}) {
    const url = `${API_URL}?action=${action}`;
    const response = await fetch(url, options);
    if (!response.ok) {
        throw new Error(`API Error: ${response.statusText}`);
    }
    return response.json();
}

async function loadConversations() {
    if (!user.value) return;
    try {
        const data = await fetchApi(`diniChat_get_conversations&user_id=${user.value.id}`);
        if (data.success) {
            conversations.value = data.conversations;
            if (conversations.value.length > 0) {
                selectConversation(conversations.value[0].id);
            }
        }
    } catch (error) {
        console.error('Failed to load conversations:', error);
    }
}

async function selectConversation(id) {
    activeConversationId.value = id;
    messages.value = [];
    try {
        const data = await fetchApi(`diniChat_get_conversation_messages&conversation_id=${id}`);
        if (data.success) {
            messages.value = data.messages;
            scrollToBottom();
        }
    } catch (error) {
        console.error(`Failed to load messages for conversation ${id}:`, error);
    }
}

async function startNewConversation() {
    if (!user.value) return;
    try {
        const data = await fetchApi('diniChat_create_conversation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: user.value.id, title: t('new_chat') })
        });
        if (data.success) {
            await loadConversations();
            selectConversation(data.conversation_id);
        }
    } catch (error) {
        console.error('Failed to create new conversation:', error);
    }
}

async function deleteConversation(id) {
    if (!confirm(t('confirm_delete_conversation'))) return;
    try {
        const data = await fetchApi('diniChat_delete_conversation', {
            method: 'POST', // Even though it's a delete action, the router uses POST
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: id })
        });
        if (data.success) {
            loadConversations();
            if (activeConversationId.value === id) {
                messages.value = [];
                activeConversationId.value = null;
            }
        }
    } catch (error) {
        console.error('Failed to delete conversation:', error);
    }
}

async function renameConversation(id) {
    const newTitle = prompt(t('enter_new_title'));
    if (!newTitle || !newTitle.trim()) return;
    try {
        const data = await fetchApi('diniChat_rename_conversation', {
            method: 'POST', // Using POST as per router setup
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: id, title: newTitle.trim() })
        });
        if (data.success) {
            loadConversations();
        }
    } catch (error) {
        console.error('Failed to rename conversation:', error);
    }
}

async function sendMessage() {
    if (!userInput.value.trim() || isReplying.value) return;
    if (!activeConversationId.value) {
        alert(t('please_select_or_create_a_conversation'));
        return;
    }

    const messageContent = userInput.value;
    messages.value.push({ role: 'user', content: messageContent });
    userInput.value = '';
    adjustTextareaHeight();
    scrollToBottom();

    isReplying.value = true;
    const assistantMessage = { role: 'assistant', content: '' };
    messages.value.push(assistantMessage);

    try {
        const response = await fetch(STREAM_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: messageContent,
                conversation_id: activeConversationId.value,
                user_id: user.value.id
            })
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        while (true) {
            const { value, done } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            const eventLines = chunk.split('\n\n').filter(line => line.trim());

            for (const line of eventLines) {
                if (line.startsWith('event: message')) {
                    const dataLine = line.substring(line.indexOf('data: ') + 6);
                    const data = JSON.parse(dataLine);
                    assistantMessage.content += data.text;
                    scrollToBottom();
                } else if (line.startsWith('event: end')) {
                    isReplying.value = false;
                    return;
                } else if (line.startsWith('event: error')) {
                    const dataLine = line.substring(line.indexOf('data: ') + 6);
                    const data = JSON.parse(dataLine);
                    assistantMessage.content = `**Error:** ${data.error}`;
                    isReplying.value = false;
                    return;
                }
            }
        }
    } catch (error) {
        assistantMessage.content = `**Error:** ${error.message}`;
    } finally {
        isReplying.value = false;
    }
}

// --- UI & UX ---
function formatMessage(content) {
    return marked(content);
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert(t('copied_to_clipboard'));
    });
}

function adjustTextareaHeight() {
    nextTick(() => {
        const el = inputBox.value;
        el.style.height = 'auto';
        el.style.height = (el.scrollHeight) + 'px';
    });
}

function scrollToBottom() {
    nextTick(() => {
        const container = chatMessagesContainer.value;
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
}

function toggleDarkMode() {
    isDarkMode.value = !isDarkMode.value;
    localStorage.setItem('diniChat-theme', isDarkMode.value ? 'dark' : 'light');
}

</script>

<style scoped>
:root {
  --primary-bg: #ffffff;
  --secondary-bg: #f7f7f8;
  --sidebar-bg: #ececec;
  --text-primary: #000000;
  --text-secondary: #555555;
  --accent-color: #007aff;
  --accent-text: #ffffff;
  --border-color: #e0e0e0;
  --user-bubble-bg: #007aff;
  --assistant-bubble-bg: #e5e5ea;
}

.dark-mode {
  --primary-bg: #1a1a1a;
  --secondary-bg: #2a2a2a;
  --sidebar-bg: #1f1f1f;
  --text-primary: #ffffff;
  --text-secondary: #aaaaaa;
  --accent-color: #0a84ff;
  --accent-text: #ffffff;
  --border-color: #3a3a3c;
  --user-bubble-bg: #0a84ff;
  --assistant-bubble-bg: #2c2c2e;
}

.dini-chat-layout {
  display: flex;
  height: 100vh;
  background-color: var(--primary-bg);
  color: var(--text-primary);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* --- Sidebar --- */
.sidebar {
  width: 280px;
  background-color: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  padding: 12px;
  border-right: 1px solid var(--border-color);
}
.sidebar-header .new-chat-btn {
  width: 100%;
  padding: 12px;
  background-color: var(--accent-color);
  color: var(--accent-text);
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background-color 0.2s;
}
.new-chat-btn:hover {
  opacity: 0.9;
}
.conversation-history {
  flex-grow: 1;
  overflow-y: auto;
  margin-top: 16px;
}
.conversation-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s;
}
.conversation-item:hover {
  background-color: rgba(0,0,0,0.1);
}
.conversation-item.active {
  background-color: var(--accent-color);
  color: var(--accent-text);
}
.convo-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.convo-actions {
  display: flex;
  gap: 4px;
}
.convo-actions button {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  opacity: 0.6;
}
.convo-actions button:hover {
    opacity: 1;
}
.sidebar-footer {
  padding-top: 12px;
  border-top: 1px solid var(--border-color);
}
.theme-toggle {
    width: 100%;
    padding: 10px;
    background: var(--secondary-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
}

/* --- Chat Panel --- */
.chat-panel {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  height: 100vh; /* Ensure it takes full viewport height */
  max-height: 100vh;
  background-color: var(--primary-bg);
}

.chat-messages {
  flex-grow: 1;
  overflow-y: auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.message-bubble {
  display: flex;
  gap: 12px;
  max-width: 80%;
}
.message-bubble.user {
  align-self: flex-end;
  flex-direction: row-reverse;
}
.message-bubble.assistant {
  align-self: flex-start;
}
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: var(--border-color);
}
.message-content {
  padding: 12px 16px;
  border-radius: 18px;
  background-color: var(--assistant-bubble-bg);
  color: var(--text-primary);
  position: relative;
}
.message-bubble.user .message-content {
  background-color: var(--user-bubble-bg);
  color: var(--accent-text);
}
.message-content p {
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.copy-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,0.1);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    color: inherit;
    padding: 4px;
    display: none;
}
.message-content:hover .copy-btn {
    display: block;
}

/* --- Chat Input --- */
.chat-input-area {
  padding: 24px;
  border-top: 1px solid var(--border-color);
  background-color: var(--primary-bg);
  display: flex;
  align-items: flex-end;
  gap: 12px;
}
.chat-input-area textarea {
  flex-grow: 1;
  border: 1px solid var(--border-color);
  border-radius: 18px;
  padding: 12px 16px;
  font-size: 16px;
  resize: none;
  max-height: 200px;
  overflow-y: auto;
  background-color: var(--secondary-bg);
  color: var(--text-primary);
}
.chat-input-area button {
  height: 48px;
  width: 48px;
  border: none;
  border-radius: 50%;
  background-color: var(--accent-color);
  color: var(--accent-text);
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
}
.chat-input-area button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Loading animation */
.typing-indicator {
    display: flex;
    gap: 4px;
    padding: 12px 0;
}
.typing-indicator span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
    animation: bounce 1s infinite;
}
.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes bounce {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1.0); }
}
</style>
  