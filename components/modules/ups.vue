<template>
  <div style="width: 90%; display: flex; justify-content: space-between; align-items: center;">
    <li>
      <div style="width: 30px; height: 30px; border-radius: 8px; display: flex; justify-content: center; align-items: center;">
        <img src="../../public/ups.svg" alt="Ups logo" />
      </div>
      

      <h3 class="title">UPS API KEY</h3>
    </li>
    
    <Toggle v-if="!loading" :toggle="work" @toggle="activator('ups_module')"/>

</div>

<div style="width: 300px; min-width: 5px; display: flex; justify-content: center; align-items: center;">
  <h3>
      Key :
  </h3>
  
</div>
    

    <input class="input" v-model="upsKey" type="text">

    <button v-if="!saving" class="btn2" style="width: 50%;" @click="applyUps" type="button">
      Save
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
        <path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M6 13.5L7.5 9L9.375 13.5M6 13.5L5.5 15M6 13.5H9.375M9.375 13.5L10 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M12.5 12V9.7C12.5 9.51387 12.5 9.42081 12.5245 9.34549C12.5739 9.19327 12.6933 9.07393 12.8455 9.02447C12.9208 9 13.0139 9 13.2 9H14.5C15.3284 9 16 9.67157 16 10.5C16 11.3284 15.3284 12 14.5 12H12.5ZM12.5 12V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M18.5 9V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    </button>
    <div v-else>
      <Loader :style="{width: '80px', height: '80px'}"/>
    </div>
    <h3>
      {{ disLog }}
    </h3>
  </template>
  
  <script>

import Loader from '../../components/loader.vue';
import Toggle from '../components/toggle.vue';
  
  
  export default {
  name: 'Modules',
  data() {
    return {
      isMounted: false,
      disLog: '',
      upsKey: '',
      name: 'ups',
      work: false,
      saving: false,
      loading: true,

    };
  },
  
  props: {

  },
  
  mounted() {
    this.isMounted = true;
    this.getUps();
    
  },
  methods: {
    async getUps () {
      this.loading = true;
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=testUps', {
          method: 'GET'
        });

        if (!response.ok) {
          console.log('error in response');
          this.loading = false;
          return;
        }

        const textResponse = await response.json();

        if (textResponse.success) {
          console.log('textResponse: ', textResponse.data.key);
          this.upsKey = textResponse.data.key;
          if (textResponse.data.work == 1) {
            this.work = true;
          } else {
            this.work = false;
          }

          this.loading = false;
          
        } else {
          console.log('textResponse: ', textResponse.message);
          this.loading = false;
        }
    },

    async activator (table) {
      this.work = !this.work
      const order = JSON.stringify({
        table: table,
        value: this.work, // Si vide, envoie null
      });

      const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateActivator', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: order,
        });

        if (!response.ok) {
          return;
        }

        const textResponse = await response.json();

        if (textResponse.success) {
          console.log('textResponse: ', textResponse.message);
        } else {
          console.error('textResponse: ', textResponse.message);
        }
    },

    async applyUps() {
      this.saving = true;
      let job = this.work ? 1 : 0;

      const upsModule = JSON.stringify({
        name: this.name,
        key: this.upsKey || null, // Si vide, envoie null
        work: job,
      });

      console.log('data: ', upsModule);

      this.disLog = "Waiting response...";

      try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=upsModule', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: upsModule,
        });

        if (!response.ok) {
          this.disLog = "Error in response";
          this.saving = false;
          return;
        }

        this.disLog = "Waiting data...";
        const textResponse = await response.json();

        if (textResponse.success) {
          this.disLog = textResponse.message;
        } else {
          this.disLog = textResponse.message + (textResponse.data ? ` : ${textResponse.data}` : '');
        }
      } catch (error) {
        this.disLog = `Request failed: ${error.message}`;
      } finally {
        this.saving = false;
      }
    }

  },
  };
  </script>

  <style>
  li {
    padding: 5px;
    margin: 2px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    gap: 10px;
  }
  </style>
  