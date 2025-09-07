<template>
    <div class="search">
        <textarea 
          class="searchBar" 
          :value="searcher" 
          @input="$emit('update:searcher', $event.target.value)" 
          :placeholder="t('Search by | order-id | phone | name')">
        </textarea>
    
        <button @click="sendSearch">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
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

<style>
.search{
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 450px;
    background-color: white;
    border: 2px solid var(--color-gorry);
    border-radius: 25px;
    padding: 5px;
    overflow: hidden;
    transition: all 0.3s ease;
    margin: 5px;
}
.dark .search{
    background-color: var(--color-darkly);
    border: 2px solid var(--color-garry);
}

.search button{
    background-color: var(--color-rangy);
    cursor: pointer;
    border: none;
    cursor: pointer;
    padding: 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.search button svg{
    color: var(--color-whity);
}

.searchBar {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 16px;
    padding: 10px 15px;
    resize: none;
    height: 40px;
    overflow: hidden;
    color: var(--color-darkly);
}

/* Effet focus */
.searchBar:focus {
    border-color: #007bff;
    box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
}

/* Mode sombre */
.dark .searchBar {
    background: transparent;
    color: var(--color-whitly);
    border-color: var(--color-darky);
}

.dark .searchBar:focus {
    border-color: #00bcd4;
    box-shadow: 0 0 8px rgba(0, 188, 212, 0.3);
}

/* Ajustement automatique de la hauteur */
.searchBar.auto-expand {
    min-height: 50px;
    height: auto;
    overflow-y: hidden;
}


</style>