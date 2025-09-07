<template>
    <div v-if="isMounted" class="w-full">
      <Explorer :show="showUploader" @confirm="addImage" @cancel="showUploader = false" class="all-none"/>
      <div class="flex flex-wrap gap-1.25 mb-1.25 mx-2.5">
        <button type="button" @click="editor.chain().focus().toggleBold().run()" :class="{ 'bg-hoggari': editor.isActive('bold') }" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5 6C5 4.58579 5 3.87868 5.43934 3.43934C5.87868 3 6.58579 3 8 3H12.5789C15.0206 3 17 5.01472 17 7.5C17 9.98528 15.0206 12 12.5789 12H5V6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M12.4286 12H13.6667C16.0599 12 18 14.0147 18 16.5C18 18.9853 16.0599 21 13.6667 21H8C6.58579 21 5.87868 21 5.43934 20.5607C5 20.1213 5 19.4142 5 18V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleItalic().run()" :class="{ 'bg-hoggari': editor.isActive('italic') }" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 4H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M8 20L16 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 20H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleUnderline().run()" :class="{ 'bg-hoggari': editor.isActive('underline') }" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M5.5 3V11.5C5.5 15.0899 8.41015 18 12 18C15.5899 18 18.5 15.0899 18.5 11.5V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 21H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().toggleStrike().run()" :class="{ 'bg-hoggari': editor.isActive('strike') }" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M4 12H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M17.5 7.66667C17.5 5.08934 15.0376 3 12 3C8.96243 3 6.5 5.08934 6.5 7.66667C6.5 8.15279 6.55336 8.59783 6.6668 9M6 16.3333C6 18.9107 8.68629 21 12 21C15.3137 21 18 19.6667 18 16.3333C18 13.9404 16.9693 12.5782 14.9079 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </button>
  
        <button type="button" @click="editor.chain().focus().setTextAlign('left').run()" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M9 6L17 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9 12L19 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9 18L17 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 3L5 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().setTextAlign('center').run()" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M3 3H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 9H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 15H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 21H16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().setTextAlign('right').run()" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M7 6L15 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M5 12L15 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M7 18L15 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M19 3L19 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
  
        <button type="button" @click="showIt(true)" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M11.5085 2.9903C7.02567 2.9903 4.78428 2.9903 3.39164 4.38238C1.99902 5.77447 1.99902 8.015 1.99902 12.4961C1.99902 16.9771 1.99902 19.2176 3.39164 20.6098C4.78428 22.0018 7.02567 22.0018 11.5085 22.0018C15.9912 22.0018 18.2326 22.0018 19.6253 20.6098C21.0179 19.2176 21.0179 16.9771 21.0179 12.4961V11.9958" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M4.99902 20.9898C9.209 16.2385 13.9402 9.93727 20.999 14.6632" stroke="currentColor" stroke-width="1.5" />
                <path d="M17.9958 1.99829V10.0064M22.0014 5.97728L13.9902 5.99217" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
  
        <button type="button" @click="editor.chain().focus().undo().run()" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C8.66873 3 5.76018 4.80989 4.20404 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3 3V4.27816C3 6.47004 3 7.56599 3.70725 8.16512C4.4145 8.76425 5.49553 8.58408 7.6576 8.22373L9 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <button type="button" @click="editor.chain().focus().redo().run()" class="p-2.5 bg-whity dark:bg-darky border-none rounded-md cursor-pointer transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C15.3313 3 18.2398 4.80989 19.796 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20.9991 3V4.27816C20.9991 6.47004 20.9991 7.56599 20.2918 8.16512C19.5846 8.76425 18.5036 8.58408 16.3415 8.22373L14.9991 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
      </div>
  
      <editor-content :editor="editor" class="p-4 m-2.5 min-h-[300px] border border-gray-300 rounded-md outline-none bg-gray-50 dark:bg-gray-800 dark:border-gray-700" />
    </div>
  </template>
  
  <script setup>
  import { ref, onBeforeUnmount, onMounted } from 'vue'
  import { Editor, EditorContent } from '@tiptap/vue-3'
  import StarterKit from '@tiptap/starter-kit'
  import Image from '@tiptap/extension-image'
  import TextAlign from '@tiptap/extension-text-align'
  import Link from '@tiptap/extension-link'
  import Underline from '@tiptap/extension-underline'
  import Strike from '@tiptap/extension-strike'
  import Blockquote from '@tiptap/extension-blockquote'
  import CodeBlock from '@tiptap/extension-code-block'
  import ImageResize from 'tiptap-extension-resize-image'

  import Explorer from '../components/elements/explorer.vue';
  const { t } = useLang();

  // 🔥 Props pour récupérer la valeur de description
  const props = defineProps({
    modelValue: {
      type: String,
      default: ''
    }
  })

  const imageUrls = ref([])

  const showUploader = ref(false);
  const emit = defineEmits(['update:modelValue']);
  const isMounted = ref(false);

  
  const editor = new Editor({
    extensions: [
      StarterKit,
      Image,
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Link.configure({ openOnClick: false }),
      Underline,
      Strike,
      Blockquote,
      CodeBlock,
      ImageResize.configure({
      allowBase64: true,
      sizes: ['small', 'medium', 'large'],
      resizable: true,
    }),
    ],
    content: props.modelValue ||  `<p>${t('Welcome to your enhanced Tiptap editor! 🚀')}</p>`,
    onBlur: () => {
    emit('update:modelValue', editor.getHTML()) // 🔥 Émet le contenu lors du blur
  }
  })
  
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

    console.log(editor['options']['content'])
    
    showUploader.value = false;
  }

  function showIt(value) {
    showUploader.value = value;
    console.log('show: ', showUploader.value);
  }

  onMounted(() => {
    isMounted.value = true;
  })

  
  onBeforeUnmount(() => {
    editor.destroy()
  })
  </script>