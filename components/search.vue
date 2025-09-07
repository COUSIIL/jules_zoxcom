<template>
    <div class="flex items-center w-full max-w-md p-1.25 m-1.25 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 rounded-full border-gorry dark:bg-darkly dark:border-garry">
        <textarea 
          class="flex-1 h-10 px-4 py-2.5 text-base bg-transparent border-none outline-none resize-none overflow-hidden text-darkly dark:text-whitly focus:ring-2 focus:ring-blue-500 dark:focus:ring-cyan-500"
          :value="searcher" 
          @input="$emit('update:searcher', $event.target.value)" 
          :placeholder="t('Search by | order-id | phone | name')">
        </textarea>
    
        <button @click="sendSearch" class="flex items-center justify-center p-2.5 rounded-full cursor-pointer bg-rangy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="text-whity">
                <path d="M17.5 17.5L22 22" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20 11C20 6.02944 15.9706 2 11 2C6.02944 2 2 6.02944 2 11C2 15.9706 6.02944 20 11 20C15.9706 20 20 15.9706 20 11Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
</template>


<script>
import { useLang } from '~/composables/useLang';

export default {
  name: "Search",
  setup() {
    const { t } = useLang();
    return { t };
  },
  props: {
    searcher: {
      type: String,
      default: "",
    },
  },
  methods: {
    sendSearch() {
      this.$emit("search-submitted", this.searcher); // Émet un événement au parent
    },
  },
};
</script>