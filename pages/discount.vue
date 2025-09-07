<template>
  <div class="flex flex-col items-center justify-around w-full">
    <div class="flex items-center justify-between w-full max-w-3xl p-2.5 mt-2.5 rounded-t-md shadow-md bg-whitly dark:bg-darkly">
      <p class="m-2.5">{{ t('discounts') }}</p>
      <Linker :link="'/addDiscount'" :text="t('add discount')" :svg="icons['promotion']" />
    </div>
  
    <div class="flex flex-col items-center justify-center w-full max-w-3xl p-1.25 transition-all duration-300 ease-in-out rounded-md"
      v-for="(name, index) in discountName" 
      :key="index"
    >
      <div v-if="!isUpdating[index]" class="w-full">
        <div class="flex items-center justify-between w-full h-12 p-1 mt-0 rounded-t-md shadow-md bg-whitly dark:bg-darkly">
          <div @click=" name.code[0].isMore = !name.code[0].isMore" class="flex items-center justify-between cursor-pointer">
            <div class="flex flex-col items-center justify-center min-w-[30px] max-w-[50px]">
              <label>{{ index + 1 }}</label>
              <svg v-if="!name.code[0].isMore" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M18 9.00005C18 9.00005 13.5811 15 12 15C10.4188 15 6 9 6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M17.9998 15C17.9998 15 13.5809 9.00001 11.9998 9C10.4187 8.99999 5.99985 15 5.99985 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="flex flex-col items-center justify-center min-w-[80px] max-w-[100px]">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M10.9961 10H11.0111M10.9998 16H11.0148" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M7 13H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="1.5" cy="1.5" r="1.5" transform="matrix(1 0 0 -1 16 8)" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M2.77423 11.1439C1.77108 12.2643 1.7495 13.9546 2.67016 15.1437C4.49711 17.5033 6.49674 19.5029 8.85633 21.3298C10.0454 22.2505 11.7357 22.2289 12.8561 21.2258C15.8979 18.5022 18.6835 15.6559 21.3719 12.5279C21.6377 12.2187 21.8039 11.8397 21.8412 11.4336C22.0062 9.63798 22.3452 4.46467 20.9403 3.05974C19.5353 1.65481 14.362 1.99377 12.5664 2.15876C12.1603 2.19608 11.7813 2.36233 11.472 2.62811C8.34412 5.31646 5.49781 8.10211 2.77423 11.1439Z" stroke="currentColor" stroke-width="1.5" />
              </svg>
              <label class="mx-1.25 text-lg">{{ name.code[0].name }}</label>
            </div>
            <div class="flex flex-col items-center justify-center min-w-[30px] max-w-[50px]">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M8.64298 3.14559L6.93816 3.93362C4.31272 5.14719 3 5.75397 3 6.75C3 7.74603 4.31272 8.35281 6.93817 9.56638L8.64298 10.3544C10.2952 11.1181 11.1214 11.5 12 11.5C12.8786 11.5 13.7048 11.1181 15.357 10.3544L17.0618 9.56638C19.6873 8.35281 21 7.74603 21 6.75C21 5.75397 19.6873 5.14719 17.0618 3.93362L15.357 3.14559C13.7048 2.38186 12.8786 2 12 2C11.1214 2 10.2952 2.38186 8.64298 3.14559Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20.788 11.0972C20.9293 11.2959 21 11.5031 21 11.7309C21 12.7127 19.6873 13.3109 17.0618 14.5072L15.357 15.284C13.7048 16.0368 12.8786 16.4133 12 16.4133C11.1214 16.4133 10.2952 16.0368 8.64298 15.284L6.93817 14.5072C4.31272 13.3109 3 12.7127 3 11.7309C3 11.5031 3.07067 11.2959 3.212 11.0972" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20.3767 16.2661C20.7922 16.5971 21 16.927 21 17.3176C21 18.2995 19.6873 18.8976 17.0618 20.0939L15.357 20.8707C13.7048 21.6236 12.8786 22 12 22C11.1214 22 10.2952 21.6236 8.64298 20.8707L6.93817 20.0939C4.31272 18.8976 3 18.2995 3 17.3176C3 16.927 3.20778 16.5971 3.62334 16.2661" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <label class="mx-1.25 text-lg">{{ name.code[0].code.length }}</label>
            </div>
      
            <div class="flex items-center justify-center">
              <div class="flex flex-col items-center justify-center min-w-[80px] max-w-[100px]">
                  <span v-html="name.code[0].type.svgUser" class="flex items-center justify-center"></span>
                <label class="mx-1.25 text-lg">{{ name.code[0].type.usage }}</label>
              </div>
      
              <div class="flex flex-col items-center justify-center text-center min-w-[80px] max-w-[100px]">
                <div class="flex items-center justify-center min-w-[80px] max-w-[100px]">
                  <span v-html="name.code[0].type.svgType" class="flex items-center justify-center"></span>
                  <h3 v-if="name.code[0].type.more" class="text-lg">{{ name.code[0].type.more }}</h3>
                </div>
                <label class="mx-1.25 text-lg">{{ name.code[0].type.limit }}</label>
              </div>
            </div>
          </div>
          
          <button @click="dellDiscount(index, 0)" class="z-50 flex flex-col items-center justify-center min-w-[30px] max-w-[50px] cursor-pointer">
            <svg class="text-rady" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
              <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <label class="mx-1.25 text-lg">{{ t('delete') }}</label>
          </button>
        </div>
        <div v-if="name.code[0].isMore">
          <button class="flex flex-col items-center justify-center w-full h-16 max-w-3xl p-2.5 mt-1.25 rounded-t-md shadow-md cursor-pointer bg-whitly dark:bg-darkly" @click="showAll(index)">
            <svg v-if="show[index]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
              <path d="M21.544 11.045C21.848 11.4713 22 11.6845 22 12C22 12.3155 21.848 12.5287 21.544 12.955C20.1779 14.8706 16.6892 19 12 19C7.31078 19 3.8221 14.8706 2.45604 12.955C2.15201 12.5287 2 12.3155 2 12C2 11.6845 2.15201 11.4713 2.45604 11.045C3.8221 9.12944 7.31078 5 12 5C16.6892 5 20.1779 9.12944 21.544 11.045Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15C13.6569 15 15 13.6569 15 12Z" stroke="currentColor" stroke-width="1.5" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
              <path d="M22 8C22 8 18 14 12 14C6 14 2 8 2 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              <path d="M15 13.5L16.5 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M20 11L22 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M2 13L4 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M9 13.5L7.5 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex items-center justify-center min-w-[80px] max-w-[100px]">
              <label v-if="show[index]">{{ t('hide all') }}</label>
              <label v-else>{{ t('show all') }}</label>
            </div>
          </button>
          <div class="flex flex-col items-center justify-center w-full max-w-3xl p-1.25 transition-all duration-300 ease-in-out rounded-md"
              v-for="(name, i) in name.code[0].code" 
              :key="i"
          > 
          <div class="relative flex items-center justify-center w-11/12 h-10 max-w-3xl p-10 mt-2 text-base font-bold uppercase border-2 border-dashed rounded-lg shadow-md bg-whitly dark:bg-darkly border-darkly dark:border-whitly">
            <button @click="name.show = !name.show" class="z-50 flex flex-col items-center justify-center min-w-[30px] max-w-[50px] cursor-pointer">
              <svg v-if="name.show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                  <path d="M21.544 11.045C21.848 11.4713 22 11.6845 22 12C22 12.3155 21.848 12.5287 21.544 12.955C20.1779 14.8706 16.6892 19 12 19C7.31078 19 3.8221 14.8706 2.45604 12.955C2.15201 12.5287 2 12.3155 2 12C2 11.6845 2.15201 11.4713 2.45604 11.045C3.8221 9.12944 7.31078 5 12 5C16.6892 5 20.1779 9.12944 21.544 11.045Z" stroke="currentColor" stroke-width="1.5" />
                  <path d="M15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15C13.6569 15 15 13.6569 15 12Z" stroke="currentColor" stroke-width="1.5" />
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M22 8C22 8 18 14 12 14C6 14 2 8 2 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M15 13.5L16.5 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20 11L22 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M2 13L4 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M9 13.5L7.5 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            </button>
            <div class="flex items-center justify-center min-w-[80px] max-w-[100px]">
              <label v-if="name.show">{{ name.code }}</label>
              <label v-else>******</label>
            </div>
    
            <div v-if="name.work === '1'">
              <svg class="text-rangy fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
                <path d="M18.8906 12.846C18.5371 14.189 16.8667 15.138 13.5257 17.0361C10.296 18.8709 8.6812 19.7884 7.37983 19.4196C6.8418 19.2671 6.35159 18.9776 5.95624 18.5787C5 17.6139 5 15.7426 5 12C5 8.2574 5 6.3861 5.95624 5.42132C6.35159 5.02245 6.8418 4.73288 7.37983 4.58042C8.6812 4.21165 10.296 5.12907 13.5257 6.96393C16.8667 8.86197 18.5371 9.811 18.8906 11.154C19.0365 11.7084 19.0365 12.2916 18.8906 12.846Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
              </svg>
            </div>
            <div v-else>
              <svg class="text-garry fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18">
                <path d="M4 12C4 8.72077 4 7.08116 4.81382 5.91891C5.1149 5.48891 5.48891 5.1149 5.91891 4.81382C7.08116 4 8.72077 4 12 4C15.2792 4 16.9188 4 18.0811 4.81382C18.5111 5.1149 18.8851 5.48891 19.1862 5.91891C20 7.08116 20 8.72077 20 12C20 15.2792 20 16.9188 19.1862 18.0811C18.8851 18.5111 18.5111 18.8851 18.0811 19.1862C16.9188 20 15.2792 20 12 20C8.72077 20 7.08116 20 5.91891 19.1862C5.48891 18.8851 5.1149 18.5111 4.81382 18.0811C4 16.9188 4 15.2792 4 12Z" stroke="currentColor" stroke-width="1.5" />
              </svg>
            </div>
    
            <div class="flex items-center justify-center min-w-[80px] max-w-[100px]">
              <label>{{ name.value }}</label>
              <label v-if="name.type === '1'">DA</label>
              <label v-else>%</label>
            </div>
    
            <button @click="dellDiscount(index, name.id)" class="z-50 flex flex-col items-center justify-center min-w-[50px] max-w-[50px] cursor-pointer">
              <svg class="text-rady" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              </svg>
            </button>
          </div>
          </div>
        </div>
      </div>
      <div v-else class="flex items-center justify-center w-full h-12 max-w-3xl p-1 mt-0 rounded-t-md shadow-md bg-whitly dark:bg-darkly">
        <Loader class="w-12 h-12"/>
      </div>
  </div>
  </div>
</template>

<script setup>

import { ref, onMounted } from 'vue';

import Loader from '../components/loader.vue';
import Confirm from '../components/elements/bloc/confirm.vue';
import Linker from '../components/elements/bloc/classBtn.vue';
import icons from '~/public/icons.json'
  


const discountList = ref([]);
var discountName = ref([]);
const isMounted = ref(false);
const test = ref('');
const disLog = ref('');
var show = ref([]);
var deLog = ref('');
var isUpdating = ref([]);

onMounted(() => {
    getDiscount()
});


function showAll(index) {
  var isShow = false;
  show.value[index] = !show.value[index];
  isShow = show.value[index];
  for(var i = 0; i < discountName.value[index].code[0].code.length; i++){
    discountName.value[index].code[0].code[i].show = isShow;
  }
};

async function dellDiscount(index, ids) {

  var id = [];
  if (ids === 0) {
    for(var i = 0; i < discountName.value[index].code[0].code.length; i++){
      id.push(discountName.value[index].code[0].code[i].id);
    }
  } else {
    id.push(ids);
  }
  
  
  isUpdating.value[index] = true;
  const deleteDiscount = JSON.stringify({
        id: id,
        });

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=deleteDiscount', {
        method: 'POST',
        body: deleteDiscount,
        });
        if(!response2.ok){
            deLog.value = t("error in response");
            console.error('this.deLog: ', this.deLog);
            isUpdating.value[index] = false;
            return;
        }
        const textResponse = await response2.json();  // Récupérer la réponse en texte
        if (textResponse.success) {
            deLog.value = textResponse.message;
            discountList.value = [];
            discountName.value = [];
            show.value = [];
            await getDiscount();
            isUpdating.value[index] = false;
        } else {
            deLog.value = textResponse.message;
            console.error('this.deLog: ', this.deLog);
            isUpdating.value[index] = false;
        }
        isUpdating.value[index] = false;
        
};


async function getDiscount() {
  const time = Math.floor(new Date().getTime() / 1000);
const response = await fetch('https://management.hoggari.com/backend/api.php?action=getDiscount', {
    method: 'GET',
    });
    if (!response.ok) {
    console.error('error in getting response category');
    }
    const result = await response.json();
    if (result.success) {
    console.log('result: ', result.data);
    for(var i =0; i < result.data.length; i++) {
      var type = {};
      var working = '';



          if(time > parseFloat(result.data[i].valid_until) || result.data[i].work === '0') {
            working = '0';
          } else {
            working = '1';
          }
          if(result.data[i].limitation === "1" && result.data[i].usage === "1"){
            const unixTimestamp = result.data[i].valid_until;
            const date = new Date(unixTimestamp * 1000); // Convertir en millisecondes
            const formattedDate = date.toLocaleDateString('fr-DZ', {
              year: '2-digit',
              month: '2-digit',
              day: '2-digit'
            });
            type = {usage: 'unique', limit: 'until', 
            svgUser: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" />
                <path d="M14 14H10C7.23858 14 5 16.2386 5 19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19C19 16.2386 16.7614 14 14 14Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
            </svg>`,
            svgType: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M19 2V5C19 8.86599 15.866 12 12 12M5 2V5C5 8.86599 8.13401 12 12 12M12 12C15.866 12 19 15.134 19 19V22M12 12C8.13401 12 5 15.134 5 19V22" stroke="currentColor" stroke-width="1.5" />
                <path d="M4 2H20M20 22H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>`,
            more: formattedDate}; // One usage code Create a time-limited code
          } else if (result.data[i].limitation === "1" && result.data[i].usage === "0"){
            type = {usage: 'unique', limit: 'untimed', 
            svgUser: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" />
                <path d="M14 14H10C7.23858 14 5 16.2386 5 19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19C19 16.2386 16.7614 14 14 14Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
            </svg>`,
            svgType: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M5 5.01959C5 8.88146 8.13401 12.0121 12 12.0121C8.13401 12.0121 5 15.1428 5 19.0047V22.0015M19 19.0047V22.0015" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M19 22.0013H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M2 2.02246L22 22.0011" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M6.5625 1.99805H20.0625M16.0405 10.9884C16.7103 10.4387 18.2094 9.32398 18.8236 6.79912C19.097 5.67517 19.027 3.59803 19.027 1.99805" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>`};  // One usage code Create a time-unlimited code
          } else if (result.data[i].limitation === "0" && result.data[i].usage === "1"){
            const unixTimestamp = result.data[i].valid_until;
            const date = new Date(unixTimestamp * 1000); // Convertir en millisecondes
            const formattedDate = date.toLocaleDateString('fr-DZ');
            type = {usage: 'multy', limit: 'until', 
            svgUser: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M15 8C15 9.65685 13.6569 11 12 11C10.3431 11 9 9.65685 9 8C9 6.34315 10.3431 5 12 5C13.6569 5 15 6.34315 15 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16 4C17.6568 4 19 5.34315 19 7C19 8.22309 18.268 9.27523 17.2183 9.7423" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M13.7143 14H10.2857C7.91876 14 5.99998 15.9188 5.99998 18.2857C5.99998 19.2325 6.76749 20 7.71426 20H16.2857C17.2325 20 18 19.2325 18 18.2857C18 15.9188 16.0812 14 13.7143 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M17.7143 13C20.0812 13 22 14.9188 22 17.2857C22 18.2325 21.2325 19 20.2857 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 4C6.34315 4 5 5.34315 5 7C5 8.22309 5.73193 9.27523 6.78168 9.7423" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3.71429 19C2.76751 19 2 18.2325 2 17.2857C2 14.9188 3.91878 13 6.28571 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>`,
            svgType: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M19 2V5C19 8.86599 15.866 12 12 12M5 2V5C5 8.86599 8.13401 12 12 12M12 12C15.866 12 19 15.134 19 19V22M12 12C8.13401 12 5 15.134 5 19V22" stroke="currentColor" stroke-width="1.5" />
                <path d="M4 2H20M20 22H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>`,
            more: formattedDate
          };  // Multy usage code Create a time-limited code
          } else if (result.data[i].limitation === "0" && result.data[i].usage === "0"){
            type = {usage: 'multy', limit: 'usage', 
            svgUser: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M15 8C15 9.65685 13.6569 11 12 11C10.3431 11 9 9.65685 9 8C9 6.34315 10.3431 5 12 5C13.6569 5 15 6.34315 15 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16 4C17.6568 4 19 5.34315 19 7C19 8.22309 18.268 9.27523 17.2183 9.7423" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M13.7143 14H10.2857C7.91876 14 5.99998 15.9188 5.99998 18.2857C5.99998 19.2325 6.76749 20 7.71426 20H16.2857C17.2325 20 18 19.2325 18 18.2857C18 15.9188 16.0812 14 13.7143 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M17.7143 13C20.0812 13 22 14.9188 22 17.2857C22 18.2325 21.2325 19 20.2857 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M8 4C6.34315 4 5 5.34315 5 7C5 8.22309 5.73193 9.27523 6.78168 9.7423" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M3.71429 19C2.76751 19 2 18.2325 2 17.2857C2 14.9188 3.91878 13 6.28571 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>`,
            svgType: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none">
                <path d="M2 20V7.02172C2 5.10465 2 4.00007 2.4389 4.00007C2.95995 4.00007 3.33531 4.66033 4.25033 6.27292L10.7497 17.7271C11.6647 19.3397 12.0233 20 12.5611 20C13 20 13 18.8954 13 16.9784V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16 13L22 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M16.5806 4.58081C17.3546 3.80672 20.6454 3.80672 21.4194 4.58081C22.1935 5.35489 22.1935 8.6456 21.4194 9.41968C20.6454 10.1938 17.3546 10.1938 16.5806 9.41968C15.8065 8.6456 15.8065 5.35489 16.5806 4.58081Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>`,
            more: `${result.data[i].qty - result.data[i].usages}`};  // Multy usage code Create a limited usage code
          }
        if (!discountName.value.some(item => item.code.some(subItem => subItem.name === result.data[i].name))) {

          discountName.value.push({
                code: [{
                    time: result.data[i].valid_until,
                    name: result.data[i].name,
                    type: type,
                    isMore: false,
                    code: [{
                        code: result.data[i].code,
                        id: result.data[i].id,
                        work: result.data[i].work,
                        show: false,
                        value: result.data[i].value,
                        type: result.data[i].type,
                    }]
                }]
            });
            isUpdating.value.push(false);
            show.value.push(false);
        } else {
            const index = discountName.value.findIndex(item => 
                item.code.some(subItem => subItem.name === result.data[i].name)
            );

            if (index !== -1) {
                const subIndex = discountName.value[index].code.findIndex(subItem => subItem.name === result.data[i].name);

                if (subIndex !== -1) {
                    discountName.value[index].code[subIndex].code.push({
                      code: result.data[i].code,
                      id: result.data[i].id,
                      work: result.data[i].work,
                      show: false,
                      value: result.data[i].value,
                      type: result.data[i].type,
                    });
                }
            }
        }
        
    }
    }
    isMounted.value = true;
}


  



</script>