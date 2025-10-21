<template>
    <div v-if="isMounted" style="width: 100%">
      <Explorer :show="showUploader" @confirm="addImage" @cancel="showUploader = false" style="all: none;"/>
      <div class="toolbar">
        <button type="button" @click="editor.chain().focus().toggleBold().run()" :class="{ active: editor.isActive('bold') }">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5 6C5 4.58579 5 3.87868 5.43934 3.43934C5.87868 3 6.58579 3 8 3H12.5789C15.0206 3 17 5.01472 17 7.5C17 9.98528 15.0206 12 12.5789 12H5V6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M12.4286 12H13.6667C16.0599 12 18 14.0147 18 16.5C18 18.9853 16.0599 21 13.6667 21H8C6.58579 21 5.87868 21 5.43934 20.5607C5 20.1213 5 19.4142 5 18V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleItalic().run()" :class="{ active: editor.isActive('italic') }">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 4H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M8 20L16 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 20H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleUnderline().run()" :class="{ active: editor.isActive('underline') }">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M5.5 3V11.5C5.5 15.0899 8.41015 18 12 18C15.5899 18 18.5 15.0899 18.5 11.5V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 21H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleStrike().run()" :class="{ active: editor.isActive('strike') }">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M4 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M17.5 7.66667C17.5 5.08934 15.0376 3 12 3C8.96243 3 6.5 5.08934 6.5 7.66667C6.5 8.15279 6.55336 8.59783 6.6668 9M6 16.3333C6 18.9107 8.68629 21 12 21C15.3137 21 18 19.6667 18 16.3333C18 13.9404 16.9693 12.5782 14.9079 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
  
        <!--button type="button" @click="editor.chain().focus().setHeading({ level: 1 }).run()">H1</button>
        <button type="button" @click="editor.chain().focus().setHeading({ level: 2 }).run()">H2</button>
        <button type="button" @click="editor.chain().focus().setHeading({ level: 3 }).run()">H3</button-->
  
        <!--button type="button" @click="editor.chain().focus().toggleBulletList().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M8 5L20 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M4 5H4.00898" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M4 12H4.00898" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M4 19H4.00898" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 12L20 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M8 19L20 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleOrderedList().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M11 6L21 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M11 12L21 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M11 18L21 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M3 15H4.5C4.77879 15 4.91819 15 5.03411 15.0231C5.51014 15.1177 5.88225 15.4899 5.97694 15.9659C6 16.0818 6 16.2212 6 16.5C6 16.7788 6 16.9182 5.97694 17.0341C5.88225 17.5101 5.51014 17.8823 5.03411 17.9769C4.91819 18 4.77879 18 4.5 18C4.22121 18 4.08181 18 3.96589 18.0231C3.48986 18.1177 3.11775 18.4899 3.02306 18.9659C3 19.0818 3 19.2212 3 19.5V20.4C3 20.6828 3 20.8243 3.08787 20.9121C3.17574 21 3.31716 21 3.6 21H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 3H4.2C4.36569 3 4.5 3.13431 4.5 3.3V9M4.5 9H3M4.5 9H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button-->
  
        <button type="button" @click="editor.chain().focus().setTextAlign('left').run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M9 6L17 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9 12L19 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9 18L17 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 3L5 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().setTextAlign('center').run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M3 3H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 9H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 15H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 21H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().setTextAlign('right').run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M7 6L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 12L15 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M7 18L15 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M19 3L19 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
  
        <button type="button" @click="showIt(true)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M11.5085 2.9903C7.02567 2.9903 4.78428 2.9903 3.39164 4.38238C1.99902 5.77447 1.99902 8.015 1.99902 12.4961C1.99902 16.9771 1.99902 19.2176 3.39164 20.6098C4.78428 22.0018 7.02567 22.0018 11.5085 22.0018C15.9912 22.0018 18.2326 22.0018 19.6253 20.6098C21.0179 19.2176 21.0179 16.9771 21.0179 12.4961V11.9958" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M4.99902 20.9898C9.209 16.2385 13.9402 9.93727 20.999 14.6632" stroke="currentColor" stroke-width="1.5" />
                <path d="M17.9958 1.99829V10.0064M22.0014 5.97728L13.9902 5.99217" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
  
        <!--button type="button" @click="editor.chain().focus().toggleLink({ href: prompt('Lien URL:') }).run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M10 13.229C10.1416 13.4609 10.3097 13.6804 10.5042 13.8828C11.7117 15.1395 13.5522 15.336 14.9576 14.4722C15.218 14.3121 15.4634 14.1157 15.6872 13.8828L18.9266 10.5114C20.3578 9.02184 20.3578 6.60676 18.9266 5.11718C17.4953 3.6276 15.1748 3.62761 13.7435 5.11718L13.03 5.85978" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M10.9703 18.14L10.2565 18.8828C8.82526 20.3724 6.50471 20.3724 5.07345 18.8828C3.64218 17.3932 3.64218 14.9782 5.07345 13.4886L8.31287 10.1172C9.74413 8.62761 12.0647 8.6276 13.4959 10.1172C13.6904 10.3195 13.8584 10.539 14 10.7708" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
  
        <button type="button" @click="editor.chain().focus().toggleBlockquote().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M10 8C10 9.88562 10 10.8284 9.41421 11.4142C8.82843 12 7.88562 12 6 12C4.11438 12 3.17157 12 2.58579 11.4142C2 10.8284 2 9.88562 2 8C2 6.11438 2 5.17157 2.58579 4.58579C3.17157 4 4.11438 4 6 4C7.88562 4 8.82843 4 9.41421 4.58579C10 5.17157 10 6.11438 10 8Z" stroke="currentColor" stroke-width="1.5" />
                <path d="M10 7L10 11.4821C10 15.4547 7.48429 18.8237 4 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M22 8C22 9.88562 22 10.8284 21.4142 11.4142C20.8284 12 19.8856 12 18 12C16.1144 12 15.1716 12 14.5858 11.4142C14 10.8284 14 9.88562 14 8C14 6.11438 14 5.17157 14.5858 4.58579C15.1716 4 16.1144 4 18 4C19.8856 4 20.8284 4 21.4142 4.58579C22 5.17157 22 6.11438 22 8Z" stroke="currentColor" stroke-width="1.5" />
                <path d="M22 7L22 11.4821C22 15.4547 19.4843 18.8237 16 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleCodeBlock().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M17 8L18.8398 9.85008C19.6133 10.6279 20 11.0168 20 11.5C20 11.9832 19.6133 12.3721 18.8398 13.1499L17 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M7 8L5.16019 9.85008C4.38673 10.6279 4 11.0168 4 11.5C4 11.9832 4.38673 12.3721 5.16019 13.1499L7 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M14.5 4L9.5 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button-->
  
        <button type="button" @click="editor.chain().focus().undo().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C8.66873 3 5.76018 4.80989 4.20404 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 3V4.27816C3 6.47004 3 7.56599 3.70725 8.16512C4.4145 8.76425 5.49553 8.58408 7.6576 8.22373L9 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().redo().run()">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C15.3313 3 18.2398 4.80989 19.796 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20.9991 3V4.27816C20.9991 6.47004 20.9991 7.56599 20.2918 8.16512C19.5846 8.76425 18.5036 8.58408 16.3415 8.22373L14.9991 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <button type="button" @click="insertRawHtml()">Insert HTML</button>
      </div>
  
      <editor-content :editor="editor" class="editor" />
    </div>
  </template>
  
<script setup>
import { ref, onBeforeUnmount, onMounted } from 'vue'
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Image from '@tiptap/extension-image'
import TextAlign from '@tiptap/extension-text-align'
import Underline from '@tiptap/extension-underline'
import Strike from '@tiptap/extension-strike'
import Blockquote from '@tiptap/extension-blockquote'
import ImageResize from 'tiptap-extension-resize-image'
import { Node } from '@tiptap/core'

import Explorer from '../components/elements/explorer.vue';

// ✅ Extension custom pour HTML brut
const RawHTML = Node.create({
  name: 'rawHTML',
  group: 'block',
  atom: true,

  addAttributes() {
    return {
      html: {
        default: '',
      },
    }
  },

  parseHTML() {
    return [{ tag: 'raw-html' }]
  },

  // ✅ On insère directement le code HTML brut
  renderHTML({ HTMLAttributes }) {
    return ['raw-html', HTMLAttributes, 0]
  },

  addNodeView() {
    return ({ node }) => {
      const dom = document.createElement('div')
      dom.innerHTML = node.attrs.html || ''
      return { dom }
    }
  },
})

// ---- PROPS / EMIT ----
const props = defineProps({
  modelValue: { type: String, default: '' }
})
const emit = defineEmits(['update:modelValue'])

const isMounted = ref(false)
const showUploader = ref(false)

const imageUrls = ref([])

// ---- INITIAL CONTENT ----
let initialContent = `<p>Bienvenue 🚀</p>`
try {
  if (props.modelValue) {
    initialContent = JSON.parse(props.modelValue) // JSON valide
  }
} catch (e) {
  initialContent = props.modelValue // sinon HTML
}

// ---- EDITOR ----
const editor = new Editor({
  extensions: [
    StarterKit.configure({ codeBlock: false }),
    Image,
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
    Underline,
    Strike,
    Blockquote,
    ImageResize.configure({ allowBase64: true, resizable: true }),
    RawHTML,
  ],
  content: initialContent,
  onBlur: () => {
    // ✅ Sauvegarde toujours en JSON
    emit('update:modelValue', JSON.stringify(editor.getJSON()))
  }
})

// ---- FUNCTIONS ----
function insertRawHtml() {
  const html = prompt('Colle ton HTML/CSS/JS ici :', '<div class="box">Hello World</div>')
  if (html) {
    editor.chain().focus().insertContent({
      type: 'rawHTML',
      attrs: { html }
    }).run()
  }
}

function addImage(url) {


    
    
    if (url) {
      imageUrls.value.push(url);

      editor.chain().focus().insertContent([
        {
          type: 'image',
          attrs: { src: url }
        },
        {
          type: 'paragraph'
        }
      ]).run();
    }

    
    showUploader.value = false;
  }

function showIt(value) {
  showUploader.value = value;
}

onMounted(() => { isMounted.value = true })
onBeforeUnmount(() => editor.destroy())
</script>

  
  <style scoped>
  .toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 5px;
    margin-inline: 10px;
  }
  
  .toolbar button {
    padding: 5px 10px;
    background-color: var(--color-whity);
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.2s;
  }

  .dark .toolbar button {
    background-color: var(--color-darky);
  }
  
  .toolbar button.active {
    background-color: var(--color-hoggari);
  }
  
  .editor {
    all: none;
    border: 1px solid #ddd;
    padding: 15px;
    min-height: 300px;
    border-radius: 5px;
    background-color: #f9f9f9;
    outline: none;
    margin-inline: 10px;
  }
  .dark .editor {
    border: 1px solid #1b1b1b;
    background-color: #232323;
  }
  </style>
  