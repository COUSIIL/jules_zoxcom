<template>
  <div class="image-to-html-container">
    <h1>Image to HTML Converter</h1>
    <p>Upload an image of a website mockup, and the AI will convert it into HTML and CSS code.</p>

    <div class="uploader-section">
      <div class="file-input-wrapper">
        <button class="btn-select-file" @click="triggerFileInput">{{ selectedFile ? selectedFile.name : 'Select an Image' }}</button>
        <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/png, image/jpeg, image/webp" style="display: none;" />
      </div>
      <button @click="uploadAndConvert" :disabled="!selectedFile || isLoading" class="btn-convert">
        <span v-if="isLoading">
          <div class="spinner-small"></div>
          Converting...
        </span>
        <span v-else>Convert to HTML</span>
      </button>
    </div>

    <div v-if="isLoading" class="loading-indicator">
      <p>The AI is generating the code. This may take a moment...</p>
      <div class="spinner"></div>
    </div>

    <div v-if="error" class="error-message">
      <p>An error occurred: {{ error }}</p>
    </div>

    <div v-if="generatedHtml" class="result-section">
      <div class="html-preview">
        <h2>Preview</h2>
        <iframe :srcdoc="generatedHtml" frameborder="0"></iframe>
      </div>
      <div class="code-view">
        <div class="code-header">
          <h2>Code</h2>
          <button @click="copyToClipboard">Copy Code</button>
        </div>
        <pre><code>{{ cleanHtml }}</code></pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const fileInput = ref(null);
const selectedFile = ref(null);
const isLoading = ref(false);
const generatedHtml = ref('');
const error = ref('');

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0];
  generatedHtml.value = '';
  error.value = '';
};

const cleanHtml = computed(() => {
    // Remove Markdown backticks and "html" language identifier
    return generatedHtml.value.replace(/```html|```/g, '').trim();
});


const uploadAndConvert = async () => {
  if (!selectedFile.value) {
    error.value = 'Please select a file first.';
    return;
  }

  isLoading.value = true;
  generatedHtml.value = '';
  error.value = '';

  const formData = new FormData();
  formData.append('image', selectedFile.value);

  try {
    // Step 1: Upload the file
    const uploadResponse = await fetch('/backend/imageToHtml/upload.php', {
      method: 'POST',
      body: formData,
    });

    if (!uploadResponse.ok) {
      const errorData = await uploadResponse.json();
      throw new Error(errorData.error || 'File upload failed');
    }

    const uploadResult = await uploadResponse.json();
    if (!uploadResult.success) {
      throw new Error(uploadResult.error || 'File upload failed');
    }

    // Step 2: Start streaming with the file ID
    const fileId = uploadResult.fileId;
    const eventSource = new EventSource(`/backend/imageToHtml/convert.php?fileId=${encodeURIComponent(fileId)}`);

    generatedHtml.value = ''; // Reset before streaming

    eventSource.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            if (data.candidates && data.candidates[0].content.parts[0].text) {
                generatedHtml.value += data.candidates[0].content.parts[0].text;
            }
        } catch (e) {
            // This can happen if the stream sends incomplete JSON.
            // For this use case, we can often ignore it and wait for the next message.
            console.warn("Could not parse stream data:", event.data);
        }
    };

    eventSource.addEventListener('error', (e) => {
      // The 'error' event from EventSource is generic.
      // We rely on our custom error event from the PHP script.
      if (e.data) {
          const data = JSON.parse(e.data);
          error.value = data.error || 'An unknown streaming error occurred.';
      } else {
          error.value = 'A connection error occurred with the streaming service.';
      }
      isLoading.value = false;
      eventSource.close();
    });

    eventSource.addEventListener('end', () => {
      isLoading.value = false;
      eventSource.close();
    });

  } catch (err) {
    error.value = err.message;
    isLoading.value = false;
  }
};

const copyToClipboard = () => {
  navigator.clipboard.writeText(cleanHtml.value).then(() => {
    alert('Code copied to clipboard!');
  });
};

</script>

<style scoped>
.image-to-html-container {
  padding: 2rem;
  max-width: 1200px;
  margin: auto;
}

.uploader-section {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 2rem;
  background-color: #f7f7f7;
  padding: 1.5rem;
  border-radius: 8px;
}

.file-input-wrapper .btn-select-file {
  background-color: #fff;
  border: 1px solid #ccc;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
}
.file-input-wrapper .btn-select-file:hover {
  background-color: #f0f0f0;
}

.btn-convert {
  background-color: #007aff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-convert:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}
.btn-convert:hover:not(:disabled) {
  background-color: #0056b3;
}

.loading-indicator {
  text-align: center;
  padding: 2rem;
}

.spinner, .spinner-small {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-left-color: #09f;
  animation: spin 1s ease infinite;
}
.spinner {
  width: 48px;
  height: 48px;
  margin: 1rem auto;
}
.spinner-small {
    width: 18px;
    height: 18px;
    border-width: 2px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-message {
  color: #d9534f;
  background-color: #f2dede;
  border: 1px solid #ebccd1;
  padding: 15px;
  border-radius: 4px;
  margin: 1rem 0;
}

.result-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-top: 2rem;
  height: 70vh;
}

.html-preview, .code-view {
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    border-radius: 8px;
    overflow: hidden;
}

.html-preview h2, .code-header h2 {
  margin: 0;
  font-size: 1rem;
}
.code-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background-color: #f7f7f7;
    border-bottom: 1px solid #ccc;
}
.code-header button {
    padding: 5px 10px;
    border: 1px solid #ccc;
    background: #fff;
    cursor: pointer;
    border-radius: 5px;
}


.html-preview iframe {
  width: 100%;
  flex-grow: 1;
  border: none;
}

.code-view pre {
  background-color: #2d2d2d;
  color: #f1f1f1;
  padding: 1rem;
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  flex-grow: 1;
  overflow-y: auto;
  font-family: 'Courier New', Courier, monospace;
}
</style>
