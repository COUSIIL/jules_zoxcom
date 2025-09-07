<template>

      

  <div class="fixed inset-0 flex items-center justify-center w-full h-full" v-if="!isMounted">

    <Loader class="w-20 h-20"/>

  </div>

  

  <div class="flex flex-col items-center max-w-full" v-else>
    <Confirm :isVisible="showConfirm"
    @confirm="confirmation(true)"
    @cancel="confirmation(false)"
  />

  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>

  <nav v-if="showDeliver" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <Deliver v-if="!isShipping" :isVisible="showDeliver"
    :_name="nameDeliver"
    :_phone1="phoneDeliver"
    :_total="totalDeliver"
    :_indexing="indexDeliver"
    @confirm="shipping"
    @cancel="cancelShipping"
  />

  <div v-else-if="isShipping">
    <Loader class="w-20 h-20"/>
  </div>
  

  </nav>

  
    <div class="flex items-center justify-center w-full mt-2.5">
      <Search v-model:searcher="searchValue" @search-submitted="search"></Search>
    </div>

    
    
    <div class="flex items-center justify-between w-full h-16 max-w-3xl my-5 min-w-52">
      <button class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly" type="button" @click="toFilters">
        {{ t('filter') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
          <path d="M3 7H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M3 17H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M18 17L21 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M15 7L21 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M6 7C6 6.06812 6 5.60218 6.15224 5.23463C6.35523 4.74458 6.74458 4.35523 7.23463 4.15224C7.60218 4 8.06812 4 9 4C9.93188 4 10.3978 4 10.7654 4.15224C11.2554 4.35523 11.6448 4.74458 11.8478 5.23463C12 5.60218 12 6.06812 12 7C12 7.93188 12 8.39782 11.8478 8.76537C11.6448 9.25542 11.2554 9.64477 10.7654 9.84776C10.3978 10 9.93188 10 9 10C8.06812 10 7.60218 10 7.23463 9.84776C6.74458 9.64477 6.35523 9.25542 6.15224 8.76537C6 8.39782 6 7.93188 6 7Z" stroke="currentColor" stroke-width="1.5" />
          <path d="M12 17C12 16.0681 12 15.6022 12.1522 15.2346C12.3552 14.7446 12.7446 14.3552 13.2346 14.1522C13.6022 14 14.0681 14 15 14C15.9319 14 16.3978 14 16.7654 14.1522C17.2554 14.3552 17.6448 14.7446 17.8478 15.2346C18 15.6022 18 16.0681 18 17C18 17.9319 18 18.3978 17.8478 18.7654C17.6448 19.2554 17.2554 19.6448 16.7654 19.8478C16.3978 20 15.9319 20 15 20C14.0681 20 13.6022 20 13.2346 19.8478C12.7446 19.6448 12.3552 19.2554 12.1522 18.7654C12 18.3978 12 17.9319 12 17Z" stroke="currentColor" stroke-width="1.5" />
      </svg>
      </button>



      <div class="flex items-center justify-between w-1/2 h-8 m-4 rounded-md shadow-md min-w-36 max-w-72 bg-whitly dark:bg-darkly">
        <div class="relative inline-block text-left">
          <!-- Bouton pour ouvrir le menu -->
          <button @click="toggleMultyDropdown" class="flex items-center justify-between h-8 p-2 m-2.5 cursor-pointer min-w-24 text-darkly dark:bg-darkly dark:text-whitly">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="min-w-[25px] text-darkly dark:text-whitly">
                <path d="M16.9767 19.5C19.4017 17.8876 21 15.1305 21 12C21 7.02944 16.9706 3 12 3C11.3126 3 10.6432 3.07706 10 3.22302M16.9767 19.5V16M16.9767 19.5H20.5M7 4.51555C4.58803 6.13007 3 8.87958 3 12C3 16.9706 7.02944 21 12 21C12.6874 21 13.3568 20.9229 14 20.777M7 4.51555V8M7 4.51555H3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            {{ t('actes') }}
            
          </button>

          <!-- Liste Dropdown -->
          <div 
            v-if="isOpen3" 
            class="absolute z-50 mt-2 border rounded-lg shadow-lg w-52 bg-whitly dark:bg-darkly"
            
            >
            <button
              v-for="(status, index2) in filteredStatus" 
              :key="status.name"
              @click="updateSelectedOrder('status', status.value)"
              class="flex items-center w-48 px-4 py-1 m-1.25 text-left hover:bg-gorry"
            >
              <span v-if="status.svg" v-html="status.svg" class="w-6 h-6 mr-2"></span>
              <span>{{ status.name }}</span>
            </button>
          </div>
        </div>
        

        <button class="flex flex-col items-center justify-center w-full h-12 p-0.5 m-1.25 cursor-pointer overflow-hidden text-ellipsis whitespace-nowrap min-w-6" @click="handleConfirm()">
          <svg class="text-rady" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
            <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
        </button>

          <div :class="selected ? 'flex items-center justify-center min-w-[12px] h-[12px] m-[11px] cursor-pointer bg-rady rounded-full' : 'flex items-center justify-center min-w-[14px] h-[14px] m-2.5 cursor-pointer border-2 border-darkly rounded-full dark:border-whitly'" @click="selectAll">
          
          </div>


      </div>
    </div>
    <div v-if="toFilter" class="flex flex-wrap items-center justify-between h-auto gap-1.25">
      <div class="inline-block text-left">
        <!-- Bouton pour ouvrir le menu -->
        <button @click="toggleDropdown2" class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly">
          {{ isOpen2Status }}
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
              <path d="M16.9767 19.5C19.4017 17.8876 21 15.1305 21 12C21 7.02944 16.9706 3 12 3C11.3126 3 10.6432 3.07706 10 3.22302M16.9767 19.5V16M16.9767 19.5H20.5M7 4.51555C4.58803 6.13007 3 8.87958 3 12C3 16.9706 7.02944 21 12 21C12.6874 21 13.3568 20.9229 14 20.777M7 4.51555V8M7 4.51555H3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          
          
        </button>

        <!-- Liste Dropdown -->
        <div 
          v-if="isOpen2" 
          class="absolute w-52 mt-2 bg-whitly dark:bg-darkly border rounded-lg shadow-lg"
          
          >
          <button
            v-for="(status, index2) in allStatus" 
            :key="status.name"
            @click="filterByStatus(status.value)"
            class="flex items-center w-48 px-4 py-1 m-1.25 text-left hover:bg-gorry"
          >
            <span v-if="status.svg" v-html="status.svg" class="w-6 h-6 mr-2"></span>
            <span>{{ status.value }}</span>
          </button>
        </div>
      </div>
      <button class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly" type="button" @click="reverseOrder()">
        {{ t('reverse') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#000000" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
          <path d="M11 6H15.5C17.9853 6 20 8.01472 20 10.5C20 12.9853 17.9853 15 15.5 15H4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M6.99998 12C6.99998 12 4.00001 14.2095 4 15C3.99999 15.7906 7 18 7 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
      <button class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly" type="button" @click="filter('1d')">
        {{ t('day') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
          <path d="M17 2V5M7 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M13 3.5H11C7.22876 3.5 5.34315 3.5 4.17157 4.67157C3 5.84315 3 7.72876 3 11.5V14C3 17.7712 3 19.6569 4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284C21 19.6569 21 17.7712 21 14V11.5C21 7.72876 21 5.84315 19.8284 4.67157C18.6569 3.5 16.7712 3.5 13 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M3.5 8.5H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M9 15.5C9 15.5 10.5 16 11 17.5C11 17.5 13.1765 13.5 16 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      </button>

      <button class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly" type="button" @click="filter('7d')">
        {{ t('week') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
          <path d="M17 2V5M7 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M13 3.5H11C7.22876 3.5 5.34315 3.5 4.17157 4.67157C3 5.84315 3 7.72876 3 11.5V14C3 17.7712 3 19.6569 4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284C21 19.6569 21 17.7712 21 14V11.5C21 7.72876 21 5.84315 19.8284 4.67157C18.6569 3.5 16.7712 3.5 13 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M3.5 8.5H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M9 15.5C9 15.5 10.5 16 11 17.5C11 17.5 13.1765 13.5 16 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      </button>

      <button class="flex items-center justify-between h-8 max-w-40 min-w-20 p-2 m-2.5 rounded-md shadow-md cursor-pointer bg-whitly text-darkly dark:bg-darkly dark:text-whitly" type="button" @click="filter('30d')">
        {{ t('month') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" class="mx-1.25 text-darkly dark:text-whitly">
          <path d="M17 2V5M7 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M13 3.5H11C7.22876 3.5 5.34315 3.5 4.17157 4.67157C3 5.84315 3 7.72876 3 11.5V14C3 17.7712 3 19.6569 4.17157 20.8284C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8284C21 19.6569 21 17.7712 21 14V11.5C21 7.72876 21 5.84315 19.8284 4.67157C18.6569 3.5 16.7712 3.5 13 3.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M3.5 8.5H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M9 15.5C9 15.5 10.5 16 11 17.5C11 17.5 13.1765 13.5 16 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      </button>
    </div>
    <div class="flex flex-col items-center justify-center w-full" v-if="isListed" v-for="(id, index) in orderID" :key="id">
      <div class="flex flex-col items-center justify-center w-full" v-if="isUpdating[index]">
        <Loader class="w-12 h-12"/>
      </div>
      <div class="flex flex-col items-center justify-center w-full" v-else>
        <div class="flex items-center justify-between w-full h-16 max-w-3xl p-1 mt-0 rounded-t-md shadow-md bg-whitly dark:bg-darkly" v-if="!droped[index]">
          <button :class="
          orderStatut[index] === 'waiting' ? 
            'numberBtn bg-rangy' : 
            orderStatut[index] === 'pending' ?
            'numberBtn bg-rangy' :
            orderStatut[index] === 'returned' ?
            'numberBtn bg-ioly' :
            orderStatut[index] === 'confirmed' ?
            'numberBtn bg-blue-500' :
            orderStatut[index] === 'completed' ?
            'numberBtn bg-green-500' :
            orderStatut[index] === 'shipping' ?
            'numberBtn bg-yelly' :
            orderStatut[index] === 'canceled' ?
            'numberBtn bg-rady' :
            orderStatut[index] === 'unreaching' ?
            
            'numberBtn bg-gorry' : 'numberBtn'" 
          
            @click="drop(index)">
            <h5>
              {{ index + 1 }}
            </h5>
            
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none">
              <path d="M18 9.00005C18 9.00005 13.5811 15 12 15C10.4188 15 6 9 6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
          </button>

          <div class="flex items-center justify-between w-[calc(100%-115px)]">
            <div class="flex flex-col items-start justify-center w-1/5 h-10 m-1.25 overflow-hidden min-w-20 text-ellipsis whitespace-nowrap" @click="drop(index)">
              <h4 class="text-sm font-bold text-darkly dark:text-whitly">
                order-{{ orderID[index] }}
              </h4>
              <h5 class="text-xs text-garry dark:text-gorry">
                {{ orderDay[index] }}
              </h5>
              
            </div>
  
            <div class="flex flex-col items-start justify-center w-1/5 h-10 m-1.25 overflow-hidden min-w-20 text-ellipsis whitespace-nowrap" @click="drop(index)">
              <h4 class="text-sm font-bold text-darkly dark:text-whitly">
                {{ orderPhone[index] }}
              </h4>
              <h5 class="text-xs text-garry dark:text-gorry">
                {{ orderName[index] }}
              </h5>
              
            </div>
  
            <div class="flex flex-col items-start justify-center w-1/5 h-10 m-1.25 overflow-hidden min-w-12 text-ellipsis whitespace-nowrap" @click="drop(index)">
              <h4 class="text-sm font-bold text-darkly dark:text-whitly">
                {{ orderWilaya[index] }}
              </h4>
              <h5 class="text-xs text-garry dark:text-gorry">
                {{ orderSZone[index] }}, {{ orderMZone[index] }}
              </h5>
              
            </div>
          </div>

          

  
          
          <a class="flex flex-col items-center justify-center h-12 m-2.5 cursor-pointer min-w-8 text-xs" :href="`tel: ${orderPhone[index]}`">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none" class="dark:text-whitly">
              <path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
          </svg>
          {{ orderStatut[index] }}
          </a>

          <div :class="select[index] ? 'selected' : 'notSelected'" @click="selecting(index)">
            
          </div>
        </div>
        <div class="order2 bg-whitly" v-else>
          <div class="order3">
            <button :class="
            orderStatut[index] === 'waiting' ? 
            'numberBtn bg-rangy' : 
            orderStatut[index] === 'pending' ?
            'numberBtn bg-rangy' :
            orderStatut[index] === 'returned' ?
            'numberBtn bg-ioly' :
            orderStatut[index] === 'confirmed' ?
            'numberBtn bg-blue-500' :
            orderStatut[index] === 'completed' ?
            'numberBtn bg-green-500' :
            orderStatut[index] === 'shipping' ?
            'numberBtn bg-yelly' :
            orderStatut[index] === 'canceled' ?
            'numberBtn bg-rady' :
            orderStatut[index] === 'unreaching' ?
            
            'numberBtn bg-gorry' : 'numberBtn'" 
             @click="drop(index)">
              <h5>
                {{ index + 1 }}
              </h5>
              
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none">
                <path d="M17.9998 15C17.9998 15 13.5809 9.00001 11.9998 9C10.4187 8.99999 5.99985 15 5.99985 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            </button>
            

            <div class="optionBar2">
              
              
              <div class="listedBtn2">

                <button v-if="!beta" class="iconBtn" @click="isEdit[index] = !isEdit[index]">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                    <path d="M6.53792 2.32172C6.69664 1.89276 7.30336 1.89276 7.46208 2.32172L8.1735 4.2443C8.27331 4.51403 8.48597 4.72669 8.7557 4.8265L10.6783 5.53792C11.1072 5.69664 11.1072 6.30336 10.6783 6.46208L8.7557 7.1735C8.48597 7.27331 8.27331 7.48597 8.1735 7.7557L7.46208 9.67828C7.30336 10.1072 6.69665 10.1072 6.53792 9.67828L5.8265 7.7557C5.72669 7.48597 5.51403 7.27331 5.2443 7.1735L3.32172 6.46208C2.89276 6.30336 2.89276 5.69665 3.32172 5.53792L5.2443 4.8265C5.51403 4.72669 5.72669 4.51403 5.8265 4.2443L6.53792 2.32172Z" stroke="currentColor" stroke-width="1.5" />
                    <path d="M14.4039 9.64136L15.8869 11.1244M6 22H7.49759C8.70997 22 9.31617 22 9.86124 21.7742C10.4063 21.5484 10.835 21.1198 11.6923 20.2625L19.8417 12.1131C20.3808 11.574 20.6503 11.3045 20.7944 11.0137C21.0685 10.4605 21.0685 9.81094 20.7944 9.25772C20.6503 8.96695 20.3808 8.69741 19.8417 8.15832C19.3026 7.61924 19.0331 7.3497 18.7423 7.20561C18.1891 6.93146 17.5395 6.93146 16.9863 7.20561C16.6955 7.3497 16.426 7.61924 15.8869 8.15832L7.73749 16.3077C6.8802 17.165 6.45156 17.5937 6.22578 18.1388C6 18.6838 6 19.29 6 20.5024V22Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <h5>
                    {{ t('edit') }}
                  </h5>
                  
                </button>
                
                
                <div class="inline-block text-left">
                  <!-- Bouton pour ouvrir le menu -->
                  <button @click="toggleDropdown(index)" :style="{display: 'flex', justifyContent: 'space-between',
                   alignItems: 'center', minWidth: '100px',
                    height: '40px', margin: '5px', cursor: 'pointer'}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                        <path d="M16.9767 19.5C19.4017 17.8876 21 15.1305 21 12C21 7.02944 16.9706 3 12 3C11.3126 3 10.6432 3.07706 10 3.22302M16.9767 19.5V16M16.9767 19.5H20.5M7 4.51555C4.58803 6.13007 3 8.87958 3 12C3 16.9706 7.02944 21 12 21C12.6874 21 13.3568 20.9229 14 20.777M7 4.51555V8M7 4.51555H3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ t('change status') }}
                    
                  </button>

                  <!-- Liste Dropdown -->
                  <div 
                    v-if="isOpen[index]" 
                    class="absolute mt-2 w-52 bg-whitly dark:bg-darkly border rounded-lg shadow-lg z-50"
                  >

                    <button
                      v-for="(status, index2) in filteredStatus" 
                      :key="index2"
                      @click="setDeliver(orderID[index], 'status', status.value, index)"
                      class="flex items-center w-48 px-4 py-1 text-left hover:bg-gorry"
                      :style="{margin: '5px'}"
                    >
                      <span v-if="status.svg" v-html="status.svg" class="w-6 h-6 mr-2"></span>
                      <span>{{ status.name }}</span>
                    </button>
                  </div>
                </div>

                <button class="iconBtnDel" @click="handleConfirm(orderID[index], index)">
                  <svg class="text-rady" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
                    <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <h5>
                  {{ t('delete') }}
                </h5>
                </button>
                <div :class="select[index] ? 'selected' : 'notSelected'" @click="selecting(index)">
            
                </div>
              </div>
              
            </div>
              

            
            
          </div>
          
          
        </div>
        <div v-if="note[index]" :style="{width: '100%', height: '30px', 
        maxWidth: '800px',
        borderRadius: '0px 0px 6px 6px'}" :class="
        orderStatut[index] === 'waiting' ? 
            'bg-rangy' : 
            orderStatut[index] === 'pending' ?
            'bg-rangy' :
            orderStatut[index] === 'returned' ?
            'bg-ioly' :
            orderStatut[index] === 'confirmed' ?
            'bg-blue-500' :
            orderStatut[index] === 'completed' ?
            'bg-green-500' :
            orderStatut[index] === 'shipping' ?
            'bg-yelly' :
            orderStatut[index] === 'canceled' ?
            'bg-rady' :
            orderStatut[index] === 'unreaching' ?
            
            'bg-gorry' : 'bg-gorry'" 
        >
        
          {{ note[index] }}
        
          
        </div>
        <div class="moreDirect" v-if="droped[index]">
          <div class="ip-container">
            <h5 class="ip-label">
              IP: <span class="ip-value">{{ orderIp[index] }}</span>
            </h5>
            <button class="copy-btn" @click="copyIp(orderIp[index])" :title="t('Copier')">
              <div v-html="icons['copy']"></div>
            </button>
          </div>
          <div class="moreOrder" :style="{width: '100%', display: 'flex'}">
            <div class="childElement2">
              
            <h4>
              order-{{ orderID[index] }}
            </h4>
            <h5>
              {{ orderDay[index] }}
            </h5>

            
          </div>
            
            
          </div>
          <div class="moreOrder" >
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
              <div v-if="!modItem[index].user" class="childElement2 wrraping">
                <button v-if="!beta" class="edit-icon" type="button" @click="modItem[index].user = true">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                    <path d="m15 5 3 3"/>
                  </svg>
                </button>
                <div class="alignStart">
                  <h4 style="display: flex; justify-content: space-between; align-items: center;">
                    <svg style="margin-right: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                      <path d="M16.3083 4.38394C15.7173 4.38394 15.4217 4.38394 15.1525 4.28405C15.1151 4.27017 15.0783 4.25491 15.042 4.23828C14.781 4.11855 14.5721 3.90959 14.1541 3.49167C13.1922 2.52977 12.7113 2.04882 12.1195 2.00447C12.04 1.99851 11.96 1.99851 11.8805 2.00447C11.2887 2.04882 10.8077 2.52977 9.84585 3.49166C9.42793 3.90959 9.21897 4.11855 8.95797 4.23828C8.92172 4.25491 8.88486 4.27017 8.84747 4.28405C8.57825 4.38394 8.28273 4.38394 7.69171 4.38394H7.58269C6.07478 4.38394 5.32083 4.38394 4.85239 4.85239C4.38394 5.32083 4.38394 6.07478 4.38394 7.58269V7.69171C4.38394 8.28273 4.38394 8.57825 4.28405 8.84747C4.27017 8.88486 4.25491 8.92172 4.23828 8.95797C4.11855 9.21897 3.90959 9.42793 3.49166 9.84585C2.52977 10.8077 2.04882 11.2887 2.00447 11.8805C1.99851 11.96 1.99851 12.04 2.00447 12.1195C2.04882 12.7113 2.52977 13.1922 3.49166 14.1541C3.90959 14.5721 4.11855 14.781 4.23828 15.042C4.25491 15.0783 4.27017 15.1151 4.28405 15.1525C4.38394 15.4217 4.38394 15.7173 4.38394 16.3083V16.4173C4.38394 17.9252 4.38394 18.6792 4.85239 19.1476C5.32083 19.6161 6.07478 19.6161 7.58269 19.6161H7.69171C8.28273 19.6161 8.57825 19.6161 8.84747 19.7159C8.88486 19.7298 8.92172 19.7451 8.95797 19.7617C9.21897 19.8815 9.42793 20.0904 9.84585 20.5083C10.8077 21.4702 11.2887 21.9512 11.8805 21.9955C11.96 22.0015 12.04 22.0015 12.1195 21.9955C12.7113 21.9512 13.1922 21.4702 14.1541 20.5083C14.5721 20.0904 14.781 19.8815 15.042 19.7617C15.0783 19.7451 15.1151 19.7298 15.1525 19.7159C15.4217 19.6161 15.7173 19.6161 16.3083 19.6161H16.4173C17.9252 19.6161 18.6792 19.6161 19.1476 19.1476C19.6161 18.6792 19.6161 17.9252 19.6161 16.4173V16.3083C19.6161 15.7173 19.6161 15.4217 19.7159 15.1525C19.7298 15.1151 19.7451 15.0783 19.7617 15.042C19.8815 14.781 20.0904 14.5721 20.5083 14.1541C21.4702 13.1922 21.9512 12.7113 21.9955 12.1195C22.0015 12.04 22.0015 11.96 21.9955 11.8805C21.9512 11.2887 21.4702 10.8077 20.5083 9.84585C20.0904 9.42793 19.8815 9.21897 19.7617 8.95797C19.7451 8.92172 19.7298 8.88486 19.7159 8.84747C19.6161 8.57825 19.6161 8.28273 19.6161 7.69171V7.58269C19.6161 6.07478 19.6161 5.32083 19.1476 4.85239C18.6792 4.38394 17.9252 4.38394 16.4173 4.38394H16.3083Z" stroke="currentColor" stroke-width="1.5" />
                      <path d="M8.5 16.5C9.19863 15.2923 10.5044 14.4797 12 14.4797C13.4956 14.4797 14.8014 15.2923 15.5 16.5M14 10C14 11.1046 13.1046 12 12 12C10.8955 12 10 11.1046 10 10C10 8.89544 10.8955 8.00001 12 8.00001C13.1046 8.00001 14 8.89544 14 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                  </svg>
                    {{ orderPhone[index] }}
                  </h4>
                  <h5>
                    {{ orderName[index] }}
                  </h5>
                </div>
                
                
              </div>
              <div v-else class="childElement2 wrraping">

                <button class="bg-rady" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: 25px; height: 25px; border-radius: 50%;"  @click="modItem[index].user = false">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
                    <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
                  <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; width: 100%;">
                    <div class="note">
                      <input v-model="orderPhone[index]" type="text" class="noteBar2"></input>
                    </div>
                    <div class="note">
                      <input v-model="orderName[index]" type="text" class="noteBar2"></input>
                    </div>
                    
                  
                  </div>
                  <div style="width: 100%; display: flex; justify-content: center; align-items: center;">
                    <button class="bg-rangy mr-2" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: calc(100% - 10px); height: 40px; border-radius: 6px; margin: 5px;" @click="modItem[index].user = false">
                      
                      <svg class="mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M16.9459 3.17305C17.5332 2.58578 17.8268 2.29215 18.1521 2.15173C18.6208 1.94942 19.1521 1.94942 19.6208 2.15173C19.946 2.29215 20.2397 2.58578 20.8269 3.17305C21.4142 3.76032 21.7079 4.05395 21.8483 4.37925C22.0506 4.8479 22.0506 5.37924 21.8483 5.84789C21.7079 6.17319 21.4142 6.46682 20.8269 7.05409L15.8054 12.0757C14.5682 13.3129 13.9496 13.9315 13.1748 14.298C12.4 14.6645 11.5294 14.7504 9.78823 14.9222L9 15L9.07778 14.2118C9.24958 12.4706 9.33549 11.6 9.70201 10.8252C10.0685 10.0504 10.6871 9.43183 11.9243 8.19464L16.9459 3.17305Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        <path d="M6 15H3.75C2.7835 15 2 15.7835 2 16.75C2 17.7165 2.7835 18.5 3.75 18.5H13.25C14.2165 18.5 15 19.2835 15 20.25C15 21.2165 14.2165 22 13.25 22H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <h3>
                        {{ t('update') }}
                      </h3>
                      </button>
                  </div>
                  
              </div>

              <div v-if="!modItem[index].delivery" class="childElement2 wrraping">
                <div class="alignStart">
                  <button v-if="!beta" class="edit-icon" type="button" @click="getDelivery(index, orderMethod[index], orderWilaya[index]); modItem[index].delivery = true;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                      <path d="m15 5 3 3"/>
                    </svg>
                  </button>
                  
    
                  <h4 style="display: flex; justify-content: space-between; align-items: center;">
                    <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                      <path d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z" stroke="currentColor" stroke-width="1.5" />
                      <path d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z" stroke="currentColor" stroke-width="1.5" />
                  </svg>
                    {{ orderWilaya[index] }}
                  </h4>
                  <h5>
                    {{ orderSZone[index] }}
                  </h5>
                  <h5>
                    {{ orderMZone[index] }}
                  </h5>
                </div>
                
                
              </div>
              <div v-else class="childElement2 wrraping">
                <button class="bg-rady" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: 25px; height: 25px; border-radius: 50%;"  @click="modItem[index].delivery = false">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
                    <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>

                <select class="btn2" style="width: calc(100% - 20px); max-width: 450px;" v-model="orderMethod[index]" required>
                  <option v-for="(optionDel, index) in deleveryMethod" :key="optionDel.id">
                      {{optionDel.name}}
                  </option>
              </select>

                <h3 style="width: calc(100% - 10px); max-width: 450px; display: flex; justify-content: space-between; align-items: center; margin-inline: 5px;">
                    الولاية
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M14 8H10C7.518 8 7 8.518 7 11V22H17V11C17 8.518 16.482 8 14 8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        <path d="M11 12L13 12M11 15H13M11 18H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M21 22V8.18564C21 6.95735 21 6.3432 20.7013 5.84966C20.4026 5.35612 19.8647 5.08147 18.7889 4.53216L14.4472 2.31536C13.2868 1.72284 13 1.93166 13 3.22873V7.7035" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M3 22V13C3 12.1727 3.17267 12 4 12H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22 22H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                </h3>
                
                <select class="btn2" style="width: calc(100% - 20px); max-width: 450px;" v-model="orderWilaya[index]" required>
                    <option v-for="(option, index) in deliveryList[index].price" :key="option.id">
                        {{option.name}}
                    </option>
                </select>
                
                
            
    
            <div v-if="orderWilaya[index]" style="width: 100%; max-width: 450px; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                <h3 style="width: 100%; max-width: 450px;">
                    {{ t('delivery zone') }}
                </h3>
                <div style="width: 100%; max-width: 450px; display: flex; justify-content: center; align-items: center;">
                    <button :class="orderType[index] === false ? 'btn3' : 'btn2'" type="button" style="width: 50%; height: 100px; max-width: 450px; display: flex; justify-content: center; align-items: center; flex-direction: column;" :checked="orderType[index]" @click="orderType[index] = false">
                        <h3>
                            {{ t('home') }}
                        </h3>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                            <path d="M3.16405 11.3497L4 11.5587L4.45686 16.1005C4.715 18.6668 4.84407 19.9499 5.701 20.7249C6.55793 21.5 7.84753 21.5 10.4267 21.5H13.5733C16.1525 21.5 17.4421 21.5 18.299 20.7249C19.1559 19.9499 19.285 18.6668 19.5431 16.1005L20 11.5587L20.836 11.3497C21.5201 11.1787 22 10.564 22 9.85882C22 9.35735 21.7553 8.88742 21.3445 8.59985L13.1469 2.86154C12.4583 2.37949 11.5417 2.37949 10.8531 2.86154L2.65549 8.59985C2.24467 8.88742 2 9.35735 2 9.85882C2 10.564 2.47993 11.1787 3.16405 11.3497Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="14.5" r="2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <button :class="orderType[index] === true ? 'btn3' : 'btn2'" type="button" style="width: 50%; height: 100px; max-width: 450px; display: flex; justify-content: center; align-items: center; flex-direction: column;" :checked="!orderType[index]" @click="orderType[index] = true">
                        <h3>
                            {{ t('stop-desk') }}
                        </h3>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                            <path d="M22 12H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M17 12V8C17 7.17267 17.1727 7 18 7H19C19.8273 7 20 7.17267 20 8V12" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            <path d="M20 17H16C14.1144 17 13.1716 17 12.5858 16.4142C12 15.8284 12 14.8856 12 13V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M4 12V22M20 12V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3 6V5C3 3.58579 3 2.87868 3.43934 2.43934C3.87868 2 4.58579 2 6 2H10C11.4142 2 12.1213 2 12.5607 2.43934C13 2.87868 13 3.58579 13 5V6C13 7.41421 13 8.12132 12.5607 8.56066C12.1213 9 11.4142 9 10 9H6C4.58579 9 3.87868 9 3.43934 8.56066C3 8.12132 3 7.41421 3 6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9.5 9L10 12M6.5 9L6 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                            
            </div>

            <div style="width: 100%; display: flex; justify-content: center; align-items: center;" >
              <div v-if="orderType[index] === true">
                <h3>
                  {{ orderDelPrice[index].home }}
              </h3>
              </div>
              <div v-else>
                <h3>
                  {{ orderDelPrice[index].desk }}
              </h3>
              </div>
            </div>

            
          
    

                <h3 style="width: calc(100% - 10px); max-width: 450px; display: flex; justify-content: space-between; align-items: center; margin-inline: 5px;">
                    البلدية
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M13 16.7033C13 15.7854 13 15.3265 13.2034 14.9292C13.4067 14.5319 13.7859 14.2501 14.5442 13.6866L15.0442 13.315C16.2239 12.4383 16.8138 12 17.5 12C18.1862 12 18.7761 12.4383 19.9558 13.315L20.4558 13.6866C21.2141 14.2501 21.5933 14.5319 21.7966 14.9292C22 15.3265 22 15.7854 22 16.7033V18.1782C22 19.9798 22 20.8806 21.4142 21.4403C20.8284 22 19.8856 22 18 22H13V16.7033Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        <path d="M18 12.0002V5C18 2.518 17.482 2 15 2H11C8.518 2 8 2.518 8 5V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <ellipse cx="3.5" cy="14" rx="1.5" ry="2" stroke="currentColor" stroke-width="1.5" />
                        <path d="M3.5 16V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M2 22H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M12 6H14M12 9H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M17.5 22L17.5 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                </h3>
                <select v-if="upsWork && orderMethod[index] === 'ups'" class="btn2" style="width: calc(100% - 20px); max-width: 450px;" v-model="orderSZone[index]" id="commune" required>
                  <option v-for="(option, index) in deliveryList[orderMInit[index]].price" :key="option.id">
                      {{option.name}}
                  </option>
                </select>
                <input v-else class="input" type="text" v-model="orderSZone[index]" required>




                <h3 v-if="orderMZone[index]" style="width: calc(100% - 10px); max-width: 450px; display: flex; justify-content: space-between; align-items: center; margin-inline: 5px;">
                    {{ t('adresse') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                    </svg>
                    
                </h3>
                <h3 v-else style="width: calc(100% - 10px); max-width: 450px; display: flex; justify-content: space-between; align-items: center; margin-inline: 5px;">
                    {{ t('adresse') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z" stroke="currentColor" stroke-width="1.5" />
                        <path d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    
                </h3>
                
                <input class="input" style="width: calc(100% - 10px);" type="text" v-model="orderMZone[index]">

                <div style="width: 100%; display: flex; justify-content: center; align-items: center;">
                  <button class="bg-rangy mr-2" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: calc(100% - 10px); height: 40px; border-radius: 6px; margin: 5px;" @click="modItem[index].dilivery = false">
                    
                    <svg class="mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                      <path d="M16.9459 3.17305C17.5332 2.58578 17.8268 2.29215 18.1521 2.15173C18.6208 1.94942 19.1521 1.94942 19.6208 2.15173C19.946 2.29215 20.2397 2.58578 20.8269 3.17305C21.4142 3.76032 21.7079 4.05395 21.8483 4.37925C22.0506 4.8479 22.0506 5.37924 21.8483 5.84789C21.7079 6.17319 21.4142 6.46682 20.8269 7.05409L15.8054 12.0757C14.5682 13.3129 13.9496 13.9315 13.1748 14.298C12.4 14.6645 11.5294 14.7504 9.78823 14.9222L9 15L9.07778 14.2118C9.24958 12.4706 9.33549 11.6 9.70201 10.8252C10.0685 10.0504 10.6871 9.43183 11.9243 8.19464L16.9459 3.17305Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                      <path d="M6 15H3.75C2.7835 15 2 15.7835 2 16.75C2 17.7165 2.7835 18.5 3.75 18.5H13.25C14.2165 18.5 15 19.2835 15 20.25C15 21.2165 14.2165 22 13.25 22H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <h3>
                      {{ t('update') }}
                    </h3>
                    </button>
                </div>

            </div>

              <div v-if="!isNoting[index]" class="childElement2 wrraping">
                <div class="alignStart">
                  <button class="edit-icon" type="button" @click="handleNote(index)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/>
                      <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                      <path d="m15 5 3 3"/>
                    </svg>
                  </button>
                  
    
                  <h4 style="display: flex; justify-content: space-between; align-items: center;">
                    <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                      <path d="M10.6119 5.00008L10.0851 7M12.2988 2.76313C12.713 3.49288 12.4672 4.42601 11.7499 4.84733C11.0326 5.26865 10.1153 5.01862 9.70118 4.28887C9.28703 3.55912 9.53281 2.62599 10.2501 2.20467C10.9674 1.78334 11.8847 2.03337 12.2988 2.76313Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                      <path d="M13 21.998C12.031 20.8176 10.5 18 8.5 18C7.20975 18.1059 6.53573 19.3611 5.84827 20.3287M5.84827 20.3287C5.45174 19.961 5.30251 19.4126 5.00406 18.3158L3.26022 11.9074C2.5584 9.32827 2.20749 8.0387 2.80316 7.02278C3.39882 6.00686 4.70843 5.66132 7.32766 4.97025L9.5 4.39708M5.84827 20.3287C6.2448 20.6965 6.80966 20.8103 7.9394 21.0379L12.0813 21.8725C12.9642 22.0504 12.9721 22.0502 13.8426 21.8205L16.6723 21.0739C19.2916 20.3828 20.6012 20.0373 21.1968 19.0214C21.7925 18.0055 21.4416 16.7159 20.7398 14.1368L19.0029 7.75375C18.301 5.17462 17.9501 3.88506 16.9184 3.29851C16.0196 2.78752 14.9098 2.98396 12.907 3.5" stroke="currentColor" stroke-width="1.5" />
                  </svg>
                    {{ t('note') }}
                  </h4>
                  <h5>
                    {{ note[index] }}
                  </h5>
                </div>
               
                
              </div>
              <div v-else class="childElement2 wrraping">

                <button class="bg-rady" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: 25px; height: 25px; border-radius: 50%;"  @click="clearNote(index)">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
                    <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </button>
                <div class="note">
                  <textarea v-model="note[index]" type="text" class="noteBar"></textarea>
                </div>
                
                <button class="bg-rangy mr-2" style="cursor: pointer; display: flex; justify-content: center; align-items: center; width: calc(100% - 10px); height: 40px; border-radius: 6px; margin: 5px;" @click="saveNote(index)">
                  <svg class="mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                    <path d="M16.9459 3.17305C17.5332 2.58578 17.8268 2.29215 18.1521 2.15173C18.6208 1.94942 19.1521 1.94942 19.6208 2.15173C19.946 2.29215 20.2397 2.58578 20.8269 3.17305C21.4142 3.76032 21.7079 4.05395 21.8483 4.37925C22.0506 4.8479 22.0506 5.37924 21.8483 5.84789C21.7079 6.17319 21.4142 6.46682 20.8269 7.05409L15.8054 12.0757C14.5682 13.3129 13.9496 13.9315 13.1748 14.298C12.4 14.6645 11.5294 14.7504 9.78823 14.9222L9 15L9.07778 14.2118C9.24958 12.4706 9.33549 11.6 9.70201 10.8252C10.0685 10.0504 10.6871 9.43183 11.9243 8.19464L16.9459 3.17305Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                    <path d="M6 15H3.75C2.7835 15 2 15.7835 2 16.75C2 17.7165 2.7835 18.5 3.75 18.5H13.25C14.2165 18.5 15 19.2835 15 20.25C15 21.2165 14.2165 22 13.25 22H11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ t('update') }}
              </button>
              
              </div>
            </div>
            

            <div class="childElement2">
              <!--svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                <path d="m15 5 3 3"/>
              </svg-->
              <h4 style="display: flex; justify-content: space-between; align-items: center;">
                <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                  <path d="M12 22C11.1818 22 10.4002 21.6698 8.83693 21.0095C4.94564 19.3657 3 18.5438 3 17.1613C3 16.7742 3 10.0645 3 7M12 22C12.8182 22 13.5998 21.6698 15.1631 21.0095C19.0544 19.3657 21 18.5438 21 17.1613V7M12 22L12 11.3548" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8.32592 9.69138L5.40472 8.27785C3.80157 7.5021 3 7.11423 3 6.5C3 5.88577 3.80157 5.4979 5.40472 4.72215L8.32592 3.30862C10.1288 2.43621 11.0303 2 12 2C12.9697 2 13.8712 2.4362 15.6741 3.30862L18.5953 4.72215C20.1984 5.4979 21 5.88577 21 6.5C21 7.11423 20.1984 7.5021 18.5953 8.27785L15.6741 9.69138C13.8712 10.5638 12.9697 11 12 11C11.0303 11 10.1288 10.5638 8.32592 9.69138Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M6 12L8 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M17 4L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
                {{ t('products') }}
              </h4>

              <h5 v-for="(id, i) in orderProduct[index]" :key="i">
                <div v-if="id.promo && id.promo > 0">
                  {{ id.qty }}x {{ id.name }} {{ id.promo }}
                </div>
                <div v-else>
                  {{ id.qty }}x {{ id.name }} {{ id.price }}
                </div>
                <div style="display: flex; justify-content: center; align-items: center;" v-if="id.items[i]">
                  <div class="boxItems">
                    <div style="margin-inline: 5px; width: 20px; height: 20px;" :style="{background: id.items[i].color}">

                    </div>

                    <div style="margin-inline: 5px;">
                      {{id.items[i].color_name}}
                    </div>
                    
                  </div>
                  <div class="boxItems">
                    {{id.items[i].size}}
                  </div>
                  
                </div>
                
              </h5>

              
              
            </div>

            <div class="childElement2">
              <!--svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                <path d="m15 5 3 3"/>
              </svg-->
              <h4 style="display: flex; justify-content: space-between; align-items: center;">
                <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                  <path d="M19.5 17.5C19.5 18.8807 18.3807 20 17 20C15.6193 20 14.5 18.8807 14.5 17.5C14.5 16.1193 15.6193 15 17 15C18.3807 15 19.5 16.1193 19.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                  <path d="M9.5 17.5C9.5 18.8807 8.38071 20 7 20C5.61929 20 4.5 18.8807 4.5 17.5C4.5 16.1193 5.61929 15 7 15C8.38071 15 9.5 16.1193 9.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                  <path d="M14.5 17.5H9.5M19.5 17.5H20.2632C20.4831 17.5 20.5931 17.5 20.6855 17.4885C21.3669 17.4036 21.9036 16.8669 21.9885 16.1855C22 16.0931 22 15.9831 22 15.7632V13C22 9.41015 19.0899 6.5 15.5 6.5M2 4H12C13.4142 4 14.1213 4 14.5607 4.43934C15 4.87868 15 5.58579 15 7V15.5M2 12.75V15C2 15.9346 2 16.4019 2.20096 16.75C2.33261 16.978 2.52197 17.1674 2.75 17.299C3.09808 17.5 3.56538 17.5 4.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M2 7H8M2 10H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
                {{ t('delivery') }}
              </h4>
              <h5>
                {{ orderMethod[index] }}
                {{ orderType[index] }}
                {{ orderDeliveryValue[index] }}
              </h5>
              
            </div>

            <div v-if="dis[index]" class="childElement2">
              <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                <path d="m15 5 3 3"/>
              </svg>

              <h4 style="display: flex; justify-content: space-between; align-items: center;">
                <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                  <path d="M10.9961 10H11.0111M10.9998 16H11.0148" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M7 13H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <circle cx="1.5" cy="1.5" r="1.5" transform="matrix(1 0 0 -1 16 8)" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M2.77423 11.1439C1.77108 12.2643 1.7495 13.9546 2.67016 15.1437C4.49711 17.5033 6.49674 19.5029 8.85633 21.3298C10.0454 22.2505 11.7357 22.2289 12.8561 21.2258C15.8979 18.5022 18.6835 15.6559 21.3719 12.5279C21.6377 12.2187 21.8039 11.8397 21.8412 11.4336C22.0062 9.63798 22.3452 4.46467 20.9403 3.05974C19.5353 1.65481 14.362 1.99377 12.5664 2.15876C12.1603 2.19608 11.7813 2.36233 11.472 2.62811C8.34412 5.31646 5.49781 8.10211 2.77423 11.1439Z" stroke="currentColor" stroke-width="1.5" />
              </svg>
                {{ t('discount') }}
              </h4>
              <h5>
                {{ dis[index] }}
              </h5>

              
              
            </div>

            <div class="childElement2">
              <h4 style="display: flex; justify-content: space-between; align-items: center;">
                <svg style="margin-right: 5px;"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"  fill="none">
                  <path d="M4 18.6458V8.05426C4 5.20025 4 3.77325 4.87868 2.88663C5.75736 2 7.17157 2 10 2H14C16.8284 2 18.2426 2 19.1213 2.88663C20 3.77325 20 5.20025 20 8.05426V18.6458C20 20.1575 20 20.9133 19.538 21.2108C18.7831 21.6971 17.6161 20.6774 17.0291 20.3073C16.5441 20.0014 16.3017 19.8485 16.0325 19.8397C15.7417 19.8301 15.4949 19.9768 14.9709 20.3073L13.06 21.5124C12.5445 21.8374 12.2868 22 12 22C11.7132 22 11.4555 21.8374 10.94 21.5124L9.02913 20.3073C8.54415 20.0014 8.30166 19.8485 8.03253 19.8397C7.74172 19.8301 7.49493 19.9768 6.97087 20.3073C6.38395 20.6774 5.21687 21.6971 4.46195 21.2108C4 20.9133 4 20.1575 4 18.6458Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M11 11H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M14 7L8 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
                {{ t('total') }}
              </h4>

              <h2>
                {{ total[index] }} DA
              </h2>
              <h5>
                {{ orderStatut[index] }}
              </h5>

            </div>

            <a class="callBtn" :href="`tel: ${orderPhone[index]}`">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"  fill="none">
                <path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
            </svg>
            </a>

          </div>
          
        
          
        </div>
        
      </div>
      
      
      
      
      
  

    </div>
    
  </div>
  <p class="h-20">
    
  </p>
  



            
</template>

<script>

import Loader from '../components/loader.vue';
import Search from '../components/search.vue';
import Confirm from '../components/confirm.vue';
import Deliver from '../components/deliver.vue';

import Message from '../components/elements/bloc/message.vue';

import icons from '~/public/icons.json'

export default {
  components: { Loader, Search, Confirm, Deliver, Message },
data() {
return {
  icons: [],
  orders: [], // Tableau pour stocker les commandes
  orderDay: [],
  orderIp: [],
  orderID: [],
  orderName: [],
  remarque: [],
  orderProduct: [],
  orderWilaya: [],
  orderPhone: [],
  orderPhone2: [],
  orderStatut: [],
  orderSZone: [],
  orderMZone: [],
  orderType: [],
  orderMethod: [],
  orderMInit: [],
  orderDelPrice: [],
  total: [],
  dis: [],
  note: [],
  droped: [],
  select: [],
  selectOption: [],
  resulted: [],
  isMessage: false,
  message: '',
  selected: false,
  log: 'initialising...',
  isListed: false,
  isMounted: false,
  loading: false,
  error: null,
  data: null,
  isVisible: false,
  searchValue: "",
  toFilter: false,
  orLog: '',
  deLog: '',
  isUpdating: [],
  idList: [],
  indexList: [],
  status: '',
  isOpen: [],
  isOpen2: false,
  isOpen3: false,
  isOpen2Status: 'Status',
  showConfirm: false,
  showDeliver: false,
  isShipping: false,
  isNoting: [],
  currentNote: [],
  nameDeliver: [],
  phoneDeliver: [],
  totalDeliver: [],
  indexDeliver: [],
  modItem: [],
  isEdit: [],
  wichDel: {},
  deliveryList: [],
  deleveryMethod : [],
  upsDel: [],
  upsWork: false,
  yalWork: false,
  gpxWork: false,
  beta: true,
  allStatus: [
    {name: 'All', value: 'all', 
    svg: ``},
    {name: 'wait', value: 'pending', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M17.2014 2H6.79876C5.341 2 4.06202 2.9847 4.0036 4.40355C3.93009 6.18879 5.18564 7.37422 6.50435 8.4871C8.32861 10.0266 9.24075 10.7964 9.33642 11.7708C9.35139 11.9233 9.35139 12.0767 9.33642 12.2292C9.24075 13.2036 8.32862 13.9734 6.50435 15.5129C5.14932 16.6564 3.9263 17.7195 4.0036 19.5964C4.06202 21.0153 5.341 22 6.79876 22L17.2014 22C18.6591 22 19.9381 21.0153 19.9965 19.5964C20.043 18.4668 19.6244 17.342 18.7352 16.56C18.3298 16.2034 17.9089 15.8615 17.4958 15.5129C15.6715 13.9734 14.7594 13.2036 14.6637 12.2292C14.6487 12.0767 14.6487 11.9233 14.6637 11.7708C14.7594 10.7964 15.6715 10.0266 17.4958 8.4871C18.8366 7.35558 20.0729 6.25809 19.9965 4.40355C19.9381 2.9847 18.6591 2 17.2014 2Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M9 21.6381C9 21.1962 9 20.9752 9.0876 20.7821C9.10151 20.7514 9.11699 20.7214 9.13399 20.6923C9.24101 20.509 9.42211 20.3796 9.78432 20.1208C10.7905 19.4021 11.2935 19.0427 11.8652 19.0045C11.955 18.9985 12.045 18.9985 12.1348 19.0045C12.7065 19.0427 13.2095 19.4021 14.2157 20.1208C14.5779 20.3796 14.759 20.509 14.866 20.6923C14.883 20.7214 14.8985 20.7514 14.9124 20.7821C15 20.9752 15 21.1962 15 21.6381V22H9V21.6381Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>`},
    {name: 'confirm', value: 'confirmed', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M17 11.8045C17 11.4588 17 11.286 17.052 11.132C17.2032 10.6844 17.6018 10.5108 18.0011 10.3289C18.45 10.1244 18.6744 10.0222 18.8968 10.0042C19.1493 9.98378 19.4022 10.0382 19.618 10.1593C19.9041 10.3198 20.1036 10.6249 20.3079 10.873C21.2513 12.0188 21.7229 12.5918 21.8955 13.2236C22.0348 13.7334 22.0348 14.2666 21.8955 14.7764C21.6438 15.6979 20.8485 16.4704 20.2598 17.1854C19.9587 17.5511 19.8081 17.734 19.618 17.8407C19.4022 17.9618 19.1493 18.0162 18.8968 17.9958C18.6744 17.9778 18.45 17.8756 18.0011 17.6711C17.6018 17.4892 17.2032 17.3156 17.052 16.868C17 16.714 17 16.5412 17 16.1955V11.8045Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M7 11.8046C7 11.3694 6.98778 10.9782 6.63591 10.6722C6.50793 10.5609 6.33825 10.4836 5.99891 10.329C5.55001 10.1246 5.32556 10.0224 5.10316 10.0044C4.43591 9.9504 4.07692 10.4058 3.69213 10.8732C2.74875 12.019 2.27706 12.5919 2.10446 13.2237C1.96518 13.7336 1.96518 14.2668 2.10446 14.7766C2.3562 15.6981 3.15152 16.4705 3.74021 17.1856C4.11129 17.6363 4.46577 18.0475 5.10316 17.996C5.32556 17.978 5.55001 17.8757 5.99891 17.6713C6.33825 17.5167 6.50793 17.4394 6.63591 17.3281C6.98778 17.0221 7 16.631 7 16.1957V11.8046Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M20 10.5V9C20 5.13401 16.4183 2 12 2C7.58172 2 4 5.13401 4 9V10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20 17.5C20 22 16 22 12 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
    {name: 'unreachable', value: 'unreaching', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
              <path d="M22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12Z" stroke="currentColor" stroke-width="1.5" />
              <path d="M12.2422 17V12C12.2422 11.5286 12.2422 11.2929 12.0957 11.1464C11.9493 11 11.7136 11 11.2422 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M11.992 8H12.001" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
    {name: 'deliver', value: 'shipping', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M19.5 17.5C19.5 18.8807 18.3807 20 17 20C15.6193 20 14.5 18.8807 14.5 17.5C14.5 16.1193 15.6193 15 17 15C18.3807 15 19.5 16.1193 19.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M9.5 17.5C9.5 18.8807 8.38071 20 7 20C5.61929 20 4.5 18.8807 4.5 17.5C4.5 16.1193 5.61929 15 7 15C8.38071 15 9.5 16.1193 9.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
            <path d="M14.5 17.5H9.5M19.5 17.5H20.2632C20.4831 17.5 20.5931 17.5 20.6855 17.4885C21.3669 17.4036 21.9036 16.8669 21.9885 16.1855C22 16.0931 22 15.9831 22 15.7632V13C22 9.41015 19.0899 6.5 15.5 6.5M15 15.5V7C15 5.58579 15 4.87868 14.5607 4.43934C14.1213 4 13.4142 4 12 4H5C3.58579 4 2.87868 4 2.43934 4.43934C2 4.87868 2 5.58579 2 7V15C2 15.9346 2 16.4019 2.20096 16.75C2.33261 16.978 2.52197 17.1674 2.75 17.299C3.09808 17.5 3.56538 17.5 4.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
    {name: 'return', value: 'returned', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
        <path d="M17 20C18.1046 20 19 19.1046 19 18C19 16.8954 18.1046 16 17 16C15.8954 16 15 16.8954 15 18C15 19.1046 15.8954 20 17 20Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M7 20C8.10457 20 9 19.1046 9 18C9 16.8954 8.10457 16 7 16C5.89543 16 5 16.8954 5 18C5 19.1046 5.89543 20 7 20Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M19 11H22V13C22 15.357 22 16.5355 21.2678 17.2678C20.7809 17.7546 20.0967 17.9178 19 17.9724M5 17.9724C3.90328 17.9178 3.2191 17.7546 2.73223 17.2678C2 16.5355 2 15.357 2 13V9C2 6.64298 2 5.46447 2.73223 4.73223C3.46447 4 4.64298 4 7 4H10.3C11.4168 4 11.9752 4 12.4271 4.14683C13.3404 4.44358 14.0564 5.15964 14.3532 6.07295C14.5 6.52485 14.5 7.08323 14.5 8.2C14.5 9.42079 14.5 10.0312 14.1657 10.444C14.0998 10.5254 14.0254 10.5998 13.944 10.6657C13.5312 11 12.9208 11 11.7 11H8M15 18H9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M14.5 6H16.3212C17.7766 6 18.5042 6 19.0964 6.35371C19.6886 6.70742 20.0336 7.34811 20.7236 8.6295L22 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M10 13C10 13 9.3279 12.4436 8.73729 11.9161C8.31975 11.5803 8 11.2926 8 11.0048C8 10.7498 8.24949 10.5128 8.6558 10.1415C9.23188 9.66187 10 9 10 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
    {name: 'cancel', value: 'canceled', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M15 9L12 12M12 12L9 15M12 12L15 15M12 12L9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47714 17.5228 1.99998 12 1.99998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M2.5 8.49998C2.86239 7.67055 3.3189 6.89164 3.85601 6.17676M6.17681 3.85597C6.89168 3.31886 7.67058 2.86237 8.5 2.49998" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
    {name: 'accomplish', value: 'completed', 
    svg: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
            <path d="M17 3.33782C15.5291 2.48697 13.8214 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 11.3151 21.9311 10.6462 21.8 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            <path d="M8 12.5C8 12.5 9.5 12.5 11.5 16C11.5 16 17.0588 6.83333 22 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          </svg>`},
  ],
};
},
async mounted() {
//76
// Récupérer les données au montage du composant
  this.fetchOrders();
  //this.getUps();
  //this.getYal();
  //this.getGpx();

  const res = await fetch('/icons.json')
  this.icons = await res.json()


},
computed: {
filteredStatus() {
  return this.allStatus.filter(status => status.name !== 'All');
}
},
methods: {

async copyIp (ip) {
  try {
    await navigator.clipboard.writeText(ip)
    this.isMessage = true
    this.message = "IP copied : " + ip
  } catch (err) {
    this.isMessage = true
    this.message = "error on trying to copy"
  }
},

formattedDate(day) {
  const numericYear = parseInt(new Date(day).toLocaleDateString('fr-FR', { year: 'numeric' }), 10);
  const numericMonth = parseInt(new Date(day).toLocaleDateString('fr-FR', { month: '2-digit' }), 10);
  const numericDay = parseInt(new Date(day).toLocaleDateString('fr-FR', { day: '2-digit' }), 10);
  const numericHour = parseInt(new Date(day).toLocaleTimeString('fr-FR', { hour: '2-digit', hour12: false }), 10);
  const numericMinute = parseInt(new Date(day).toLocaleTimeString('fr-FR', { minute: '2-digit' }), 10);

  // Multiplier toutes les valeurs
  const numericTime = numericYear * numericMonth * numericDay * numericHour * numericMinute;
  const Time = `${numericDay}-${numericMonth}-${numericYear} ${numericHour}:${numericMinute}`;

  return {numericTime, Time};
},


levenshteinDistance(a, b) {
    const matrix = Array.from({ length: a.length + 1 }, (_, i) =>
        Array.from({ length: b.length + 1 }, (_, j) => (i === 0 ? j : j === 0 ? i : 0))
    );

    for (let i = 1; i <= a.length; i++) {
        for (let j = 1; j <= b.length; j++) {
            matrix[i][j] = a[i - 1] === b[j - 1]
                ? matrix[i - 1][j - 1]
                : Math.min(matrix[i - 1][j] + 1, matrix[i][j - 1] + 1, matrix[i - 1][j - 1] + 1);
        }
    }

    return matrix[a.length][b.length];
},



async deliverOrder(index) {
  var data;

  var type;
    if(this.orderType[index] == 'Stop Desk') {
      type = true;
    } else {
      type = false;
    }
    console.log('this.orderType : ', type );
  if(this.orderMethod[index] === 'ups') {
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsWilaya', {
        method: 'GET',
      });

      data = await response.json();
    } catch (error) {
        console.log('responce: ', error);
    }

    

    try {

      var wilayaId = 0;
      const tolerance = 2; // Ajuste selon le niveau de tolérance souhaité

      for (let i = 0; i < data.length; i++) {
          const distance = this.levenshteinDistance(data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());

          if (distance <= tolerance) {
              wilayaId = data[i].wilaya_id;
              break; 
          }
      }

      if(wilayaId != 0) {
        const setToUps = {
          reference: `DNZ-${this.orderID[index]}`,
          nom_client: this.orderName[index],
          telephone: this.orderPhone[index],
          telephone_2: '',
          adresse: this.orderMZone[index],
          code_postal: '',
          commune: this.orderSZone[index],
          code_wilaya: wilayaId.toString(),  // ✅ Correction ici
          montant: this.total[index].toString(),  // ✅ Forcer string si nécessaire
          remarque: this.remarque[index],
          produit: this.orderProduct[index][0].name,  // ✅ Correction ici
          stock: '',
          quantite: '',
          produit_a_recupere: '',
          boutique: '',
          type: 1,
          stop_desk: '',
          weight: '',
        };

        

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addUpsOrder', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json', // ✅ Important !
              },
              body: JSON.stringify(setToUps), // ✅ Convertir en JSON ici
        });

        const data2 = await response2.json();
        if (data2.success) {
            console.log('wilaya: ', data2);
        } else {
              console.error(`Error: ${data2.message}`);
        }
      } else {
        console.error('no wilaya found');
      }

    } catch (error) {
        console.log('responce: ', error);
    }
    
    
    //const type = orderType.value === 0 ? "Livraison" : "Stop Desk";
  } else if(this.orderMethod[index] === 'anderson') {
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonWilaya', {
        method: 'GET',
      });

      data = await response.json();
    } catch (error) {
        console.log('responce: ', error);
    }

    

    try {

      var wilayaId = 0;
      const tolerance = 2; // Ajuste selon le niveau de tolérance souhaité

      for (let i = 0; i < data.length; i++) {
          const distance = this.levenshteinDistance(data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());

          if (distance <= tolerance) {
              wilayaId = data[i].wilaya_id;
              console.log('✅ Correspondance trouvée avec tolérance:', data[i].wilaya_name, '->', this.orderWilaya[index]);
              break; 
          }
      }

      if(wilayaId != 0) {
        const setToUps = {
          reference: `DNZ-${this.orderID[index]}`,
          nom_client: this.orderName[index],
          telephone: this.orderPhone[index],
          telephone_2: '',
          adresse: this.orderMZone[index],
          code_postal: '',
          commune: this.orderSZone[index],
          code_wilaya: wilayaId.toString(),  // ✅ Correction ici
          montant: this.total[index].toString(),  // ✅ Forcer string si nécessaire
          remarque: this.remarque[index],
          produit: this.orderProduct[index][0].name,  // ✅ Correction ici
          stock: '',
          quantite: '',
          produit_a_recupere: '',
          boutique: '',
          type: 1,
          stop_desk: '',
          weight: '',
        };

        

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addAndersonOrder', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json', // ✅ Important !
              },
              body: JSON.stringify(setToUps), // ✅ Convertir en JSON ici
        });

        const data2 = await response2.json();
        if (data2.success) {
            console.log('wilaya: ', data2);
        } else {
              console.error(`Error: ${data2.message}`);
        }
      } else {
        console.error('no wilaya found');
      }

    } catch (error) {
        console.log('responce: ', error);
    }
    
    
    //const type = orderType.value === 0 ? "Livraison" : "Stop Desk";
  } else if (this.orderMethod[index] === 'yalidine') {
    var center = 0;
    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getYalidineCenter', {
      method: 'GET',
    });
    console.log('response2: ', response2);
    const data2 = await response2.json();
    if (data2.success) {
      for (let i = 0; i < data2.data.data.length; i++) {
          if (this.orderWilaya[index] == data2.data.data[i].wilaya_name) {
              wilayaId = data2.data.data[i].wilaya_id;
              center = data2.data.data[i].center_id;
              break; 
          }
      }

      if(center != 0) {
        const { firstname, familyname } = this.splitName(this.orderName[index])

        const parcels = [{
          order_id: `CofP-${this.orderID[index]}`,
          from_wilaya_name: "Tipaza",
          firstname: firstname,
          familyname: familyname,
          contact_phone: this.orderPhone[index],
          address: this.orderMZone[index],
          to_commune_name: this.orderSZone[index],
          to_wilaya_name: this.orderWilaya[index],
          product_list: product_name,
          price: this.total[index],
          do_insurance: false,
          declared_value: this.total[index],
          height: 10,
          width: 10,
          length: 10,
          weight: 1,
          freeshipping: false,
          is_stopdesk: type,
          stopdesk_id: center,
          has_exchange: false,
          product_to_collect: null
        }];
        
        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addYalidineOrder', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ parcels }), // ✅ ici on met le tableau dans un objet
        });
        const data2 = await response2.json();
        if (data2.success) {
          console.log('wilaya: ', data2);
        } else {
          console.error(`Error: ${data2.message}`);
        }
      } else {
        console.error("Error: No center found", wilayaId, " ",  this.orderWilaya[index]);
      }

      
    } else {
      console.error(`Error: ${data2.message}`);
    }


  } else if(this.orderMethod[index] === 'guepex') {
    var center = 0;
    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getGuepexCenter', {
      method: 'GET',
    });
    const tolerance = 2;
    const data1 = await response2.json();
    if (data1.success) {
      for (let i = 0; i < data1.data.data.length; i++) {
        const distance = this.levenshteinDistance(data1.data.data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());
        
        const distance2 = this.levenshteinDistance(data1.data.data[i].commune_name.toLowerCase(), this.orderSZone[index].toLowerCase());

          if (distance <= tolerance && distance2 <= tolerance) {
              wilayaId = data1.data.data[i].wilaya_id;
              center = data1.data.data[i].center_id;
              break; 
          }
        
      }
      if(center == 0) {
        
      } else {
        center = null
        console.error("Error: No center found", wilayaId, " ",  this.orderWilaya[index]);
      }
      const { firstname, familyname } = this.splitName(this.orderName[index])

        const parcels = [{
          order_id: `CofP-${this.orderID[index]}`,
          from_wilaya_name: "Tipaza",
          firstname: firstname,
          familyname: familyname,
          contact_phone: this.orderPhone[index],
          address: this.orderMZone[index],
          to_commune_name: this.orderSZone[index],
          to_wilaya_name: this.orderWilaya[index],
          product_list: product_name,
          price: this.total[index],
          do_insurance: false,
          declared_value: this.total[index],
          height: 10,
          width: 10,
          length: 10,
          weight: 1,
          freeshipping: false,
          is_stopdesk: type,
          stopdesk_id: center,
          has_exchange: false,
          product_to_collect: null
        }];
        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addGuepexOrder', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ parcels }), // ✅ ici on met le tableau dans un objet
        });
        const data2 = await response2.json();
        if (data2.success) {
          console.log('wilaya: ', data2);
        } else {
          console.error(`Error: ${data2.message}`);
        }

      
    } else {
      console.error(`Error: ${data2.message}`);
    }


  }

  

},

splitName(fullName) {
  if (!fullName || typeof fullName !== 'string') return { firstname: '', familyname: '' }

  // Supprimer les caractères spéciaux et doubles espaces
  fullName = fullName.trim().replace(/\s+/g, ' ').replace(/[^\w\s\u0600-\u06FF]/g, '')

  const parts = fullName.split(' ')

  if (parts.length === 1) {
    return { firstname: parts[0], familyname: '' }
  }

  // Heuristique simple : le prénom vient généralement en premier
  // Pour les noms arabes, on suppose aussi Prénom Nom (comme en français/anglais)
  const firstname = parts.slice(0, 1).join(' ')
  const familyname = parts.slice(1).join(' ')

  return { firstname, familyname }
},


drop(id) {
  this.droped[id] = !this.droped[id];
},

selecting(id) {
  this.select[id] = !this.select[id];
  const idIndex = this.idList.indexOf(this.orderID[id]);
  if (idIndex !== -1) {
    this.idList.splice(idIndex, 1); // Supprimer l'élément de idList
    this.indexList.splice(idIndex, 1);

    this.nameDeliver.splice(idIndex, 1);
    this.phoneDeliver.splice(idIndex, 1);
    this.totalDeliver.splice(idIndex, 1);
    this.indexDeliver.splice(idIndex, 1);
  } else {
    this.idList.push(this.orderID[id]);
    this.indexList.push(id);

    this.nameDeliver.push(this.orderName[id]);
    this.phoneDeliver.push(this.orderPhone[id]);
    this.totalDeliver.push(this.total[id] - parseFloat(this.deliveryValue[id]));
    this.indexDeliver.push(id);
  }
},

toFilters() {
  this.toFilter = !this.toFilter;
},


toggleDropdown(index) {
  this.isOpen[index] = !this.isOpen[index];
},
toggleDropdown2() {
  this.isOpen2 = !this.isOpen2;
},
toggleMultyDropdown() {
  this.isOpen3 = !this.isOpen3;
},


selectAll() {
  if (this.selected === false) {
    this.selected = true;
    for (let i = 0; i < this.select.length; i++) {
      this.idList.push(this.orderID[i]);
      this.indexList.push(i);

      this.nameDeliver.push(this.orderName[i]);
      this.phoneDeliver.push(this.orderPhone[i]);
      this.totalDeliver.push(this.total[i] - parseFloat(this.deliveryValue[i]));
      this.indexDeliver.push(i);

      setTimeout(() => {
        
        this.select[i] = true;
      }, Math.random() * 200); // Délai aléatoire entre 0 et 200ms
    }
  } else {
    this.selected = false;
    for (let i = 0; i < this.select.length; i++) {
      const idIndex = this.idList.indexOf(this.orderID[i]); // Trouver l'index dans idList
        if (idIndex !== -1) {
          this.idList.splice(idIndex, 1); // Supprimer l'élément de idList
          this.indexList.splice(idIndex, 1);
          this.nameDeliver.splice(idIndex, 1);
          this.phoneDeliver.splice(idIndex, 1);
          this.totalDeliver.splice(idIndex, 1);
          this.indexDeliver.splice(idIndex, 1);
        }
      setTimeout(() => {
        
        this.select[i] = false;
      }, Math.random() * 200); // Délai aléatoire entre 0 et 200ms
    }
  }
},

resetOrders() {
    this.orderDay = [];
    this.orderIp = [];
    this.orderID = [];  // Ajoute l'élément à orderID
    this.orderPhone = []; 
    this.orderPhone2 = []; 
    this.orderName = []; 
    this.remarque = [];
    this.orderProduct = []; 
    this.orderType = [];
    this.deleveryMethod = [];
    this.deliveryList = [];
    this.orderWilaya = []; 
    this.orderDeliveryValue = [];
    this.orderStatut = []; 
    this.orderSZone = []; 
    this.orderMZone = []; 
    this.orderMInit = [];
    this.orderDelPrice = [];
    this.note = []; 
    this.dis = []; 
    this.total = []; 
    this.droped = []; 
    this.select = []; 
    this.isOpen = [];
    this.isEdit = [];
    this.currentNote = [];
    this.isNoting = [];

},

filterByStatus(value) {
  this.status = value;
  this.resetOrders();
    for (var i = 0; i < this.resulted.length; i++) {
      if(value != 'all') {
        if(value === 'pending') {
          if(this.resulted[i].status === value || this.resulted[i].status === 'waiting'){
            this.setOrders(i);
          }
        } else {
          if(this.resulted[i].status === value){
            this.setOrders(i);
          }
        }
        
      } else {
        this.setOrders(i);
      }
       
      
              
    }

    this.idList = [];
  this.indexList = [];
  for(var ii = 0; ii < this.select.length; ii++) {
    this.select[ii] = false;
  }
  this.isOpen2Status = value;
  this.isOpen2 = false;
  this.selected = false;
},

handleConfirm(orderID, index) {
  
  if (orderID !== undefined && orderID !== null && index !== undefined && index !== null) {
    this.wichDel = { order: orderID, index: index };
  } else {
    this.wichDel = {};
  }

  this.showConfirm = true;
  
},

saveNote(index) {
  this.updateOrderValue(this.orderID[index], 'note', this.note[index], index);
  this.currentNote[index] = this.note[index];
  this.isNoting[index] = false;
},

clearNote(index) {
  this.note[index] = this.currentNote[index];
  this.isNoting[index] = false;
},

handleNote(index) {
  this.isNoting[index] = !this.isNoting[index];
},

confirmation(value) {
  
  if(value === true) {
    if (this.wichDel && this.wichDel.order !== undefined && this.wichDel.index !== undefined) {
        this.deleteOrder(this.wichDel.order, this.wichDel.index);
    } else {
        this.deleteSelectedOrder();
    }
    this.showConfirm = false;
  }else {
    this.showConfirm = false;
  }

},

cancelShipping() {
  this.showDeliver = false;
  this.clearDeliverOrder();
},

async shipping({ name, phone1, phone2, note, total, indexing }) {
  this.isShipping = true;
    if (!indexing || indexing.length === 0) {
        this.showDeliver = false;
        this.isShipping = false;
        return;
    }
    
    for (let i = 0; i < indexing.length; i++) {
        this.orderName[indexing[i]] = name[i];
        this.orderPhone[indexing[i]] = phone1[i];
        this.orderPhone2[indexing[i]] = phone2[i];
        this.remarque[indexing[i]] = note[i];
        this.total[indexing[i]] = total[i];

        await this.deliverOrder(indexing[i]);
        await this.updateOrderValue(this.orderID[indexing[i]], 'status', 'shipping', indexing[i]);
    }

    this.showDeliver = false;
    this.clearDeliverOrder();
    this.isShipping = false;
},


setDeliver(id, status, value, index) {
  console.log('value: ',value)
  if(value === 'shipping') {
    this.nameDeliver.push(this.orderName[index]);
    this.phoneDeliver.push(this.orderPhone[index]);
    if(this.orderMethod[index] === 'ups' || this.orderMethod[index] === 'anderson') {
      this.totalDeliver.push(this.total[index]);
    } else {
      this.totalDeliver.push((this.total[index] - parseFloat(this.orderDeliveryValue[index])));
    }
    
    this.indexDeliver.push(index);
    this.showDeliver = true;
  } else {
    this.updateOrderValue(id, status, value, index);
  }
},


setOrders(i) {
  const formattedDay = this.formattedDate(this.resulted[i].create);
  
  this.orderDay.push(formattedDay.Time);
  this.orderIp.push(this.resulted[i].ip);
  this.orderID.push(this.resulted[i].id); //Ajoute l'élément à orderID
  this.orderPhone.push(this.resulted[i].phone); 
  this.orderPhone2.push(''); 
  this.orderName.push(this.resulted[i].name); 
  this.remarque.push(''); 
  var products = [];
  for(var ii = 0; ii < this.resulted[i].items.length; ii++) {
    products.push({
      'name': this.resulted[i].items[ii].productName,
      'price': this.resulted[i].items[ii].price,
      'promo': this.resulted[i].items[ii].promo,
      'qty': this.resulted[i].items[ii].qty,
      'ref': this.resulted[i].items[ii].ref,
      'items': this.resulted[i].items[ii].items,
    });
    
  }


  if(this.resulted[i].type == 0) {
  this.orderType.push('Home');
  } else {
   this.orderType.push('Stop Desk');
  }
  //const type = orderType.value === 0 ? "Livraison" : "Stop Desk";
  //this.orderType.push(parseFloat(this.resulted[i].type));
  this.orderMethod.push(this.resulted[i].method); 
  this.deliveryList.push({});
  this.orderDelPrice.push({});
  this.orderMInit.push(-1); 
  this.orderProduct.push(products); 
  this.orderWilaya.push(this.resulted[i].deliveryZone); 
  this.orderDeliveryValue.push(this.resulted[i].deliveryValue); 
  this.orderStatut.push(this.resulted[i].status); 
  this.orderSZone.push(this.resulted[i].sZone); 
  this.orderMZone.push(this.resulted[i].mZone); 
  this.note.push(this.resulted[i].note); 
  this.currentNote.push(this.resulted[i].note);
  this.dis.push(this.resulted[i].discount); 
  this.total.push(this.resulted[i].total); 
  
  this.droped.push(false);
  this.select.push(false);
  this.isUpdating.push(false);
  this.isOpen.push(false);
  this.isEdit.push(false);
  this.isNoting.push(false);
  this.modItem.push({
    user: false,
    delivery: false,
    note: false,
    product: false,
  });
              
},
clearDeliverOrder() {
  this.nameDeliver = [];
  this.phoneDeliver = [];
  this.totalDeliver = [];
  this.indexDeliver = [];
  this.remarque = [];
  this.idList = [];
  this.indexList = [];
  this.isOpen3 = false;
  for(var i = 0; i < this.select.length; i++){
    this.select[i] = false;
  }
  this.selected = false;
},

async updateOrderValue(id, status, value, index) {

    this.isUpdating[index] = true;
  const updateOrder = JSON.stringify({
        id: id,
        status: status,
        value: value,
        });
        console.log('updateOrder: ', updateOrder);
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=updateOrderValue', {
        method: 'POST',
        body: updateOrder,
        });
        if(!response2.ok){
            this.orLog = "error in response";
            this.isUpdating[index] = false;
            return;
        }
        const textResponse = await response2.json();  // Récupérer la réponse en texte
        if (textResponse.success) {
            this.orLog = textResponse.data;
            
            
            await this.getOrders();
            this.resetOrders();
            for (var i = 0; i < this.resulted.length; i++) {
              this.setOrders(i);
              
            }
            this.isUpdating[index] = false;
        } else {
            this.orLog = textResponse.message;
            this.isUpdating[index] = false;
        }
        this.isUpdating[index] = false;
        this.isOpen[index] = false;
        this.isEdit[index] = false;
        this.isOpen2 = false;
  

},

async deleteSelectedOrder() {
  
  for(var i = 0; i < this.idList.length; i++) {
    this.isUpdating[this.indexList[i]] = true;
    this.deleteOrder(this.idList[i], this.indexList[i]);
  }

  this.idList = [];
  this.indexList = [];
  for(var ii = 0; ii < this.select.length; ii++) {
    this.select[ii] = false;
  }
  
  this.selected = false;
  
        
},

async updateSelectedOrder(status, value) {
  console.log('test: ', value, ' ', status, ' ', this.nameDeliver[0]);
  if(value === "shipping" && this.nameDeliver[0]) {
    this.showDeliver = true;
    return;
  } else {
    for(var i = 0; i < this.idList.length; i++) {
    this.isUpdating[this.indexList[i]] = true;

    this.updateOrderValue(this.idList[i], status, value, this.indexList[i]);
    }

    this.idList = [];
    this.indexList = [];
    for(var ii = 0; ii < this.select.length; ii++) {
      this.select[ii] = false;
    }
    
    this.selected = false;
  }
  

        
},

async deleteOrder(id, index) {

    this.isUpdating[index] = true;
  const updateOrder = JSON.stringify({
        id: id,
        });

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=deleteOrder', {
        method: 'POST',
        body: updateOrder,
        });
        if(!response2.ok){
            this.deLog = "error in response";
            this.isUpdating[index] = false;
            return;
        }
        const textResponse = await response2.json();  // Récupérer la réponse en texte
        if (textResponse.success) {
            this.deLog = textResponse.message;
            await this.getOrders();
            this.resetOrders();
            for (var i = 0; i < this.resulted.length; i++) {
              this.setOrders(i);
              
            }
            this.isUpdating[index] = false;
        } else {
            this.deLog = textResponse.message;
            this.isUpdating[index] = false;
        }
  
  
        this.isUpdating[index] = false;
        this.isOpen[index] = false;
        this.isEdit[index] = false;
},

filterByDays(days) {
    this.resetOrders(); // Réinitialiser une seule fois avant la boucle
    this.isMounted = false;
    this.isListed = false;
    const now = new Date();
    
    // Convertir la date limite en numericTime
    const pastDate = new Date(now.getTime() - (days * 24 * 60 * 60 * 1000));
    const formattedPastDate = this.formattedDate(pastDate.toISOString());
    
    const timeLimit = formattedPastDate.numericTime;
    for (var i = 0; i < this.resulted.length; i++) {
        const formattedDay = this.formattedDate(this.resulted[i].create);
        
        if (formattedDay.numericTime >= timeLimit) {
            this.setOrders(i);
        }
    }
    
    
    this.isMounted = true;
    this.isListed = true;
},

filter(value) {
  
  if (value === '1d') {
    
    this.filterByDays(2);
  
  } else if(value === '7d') {
    
    this.filterByDays(7);

  } else if (value === '30d') {
    
    this.filterByDays(31);
  }

  

},

search(value) {
  let floated = value.match(/[\d.]+/g)?.join('') || ''; 
  //let floated = parseFloat(numbers);
  if (value.startsWith("order-")) {
    let number = parseFloat(value.replace("order-", ""));
    
    this.resetOrders();
    for (var i = 0; i < this.resulted.length; i++) {
          //const formattedDay = this.formattedDate(this.resulted[i].create);
          
            
      if(i === number) {
        this.setOrders(i);
      }
    }

  } else if (floated === '' && value != '') {
    this.resetOrders();
    for (var i = 0; i < this.resulted.length; i++) {
          //const formattedDay = this.formattedDate(this.resulted[i].create);
          
            
            if(this.resulted[i].name === value) {
              this.setOrders(i);
            }
    }
  } else if (floated != '' && value != '') {
    this.resetOrders();
    for (var i = 0; i < this.resulted.length; i++) {
          const formattedDay = this.formattedDate(this.resulted[i].create);
          
            
            if(this.resulted[i].phone === floated) {
              this.setOrders(i);
            }
    }
  } else {
    this.resetOrders();
    for (var i = 0; i < this.resulted.length; i++) {
      this.setOrders(i);
    }
  }
      
},

reverseOrder() {
  this.resetOrders();
  this.resulted.reverse();
  for (var i = 0; i < this.resulted.length; i++) {
          this.setOrders(i);

        
  }
},


async getOrders() {
const response = await fetch('https://management.hoggari.com/backend/api.php?action=getOrders', {
    method: 'GET',
    });
    if (!response.ok) {
      this.log = 'error in getting response category';
      return;
    }
    const result = await response.json();
    if (result.success) {
      if (!result.data) {
        this.log = 'No recent orders for now.';
      } else {
        
        this.log = result.message;
      
        this.resulted = result.data;
        this.reverseOrder();
        
        
      }
      this.isListed = true;
      
        
    } else {
      this.log = result.message;
      this.isListed = true;
    }

    
    
},

async fetchOrders() {
    this.log = 'request send, wiating responce...';
  try {

    await this.getOrders();
    this.status = 'all';
    this.isMounted = true;
    

    /*if(response.ok) {
        const data = await response.json(); // Convertir la réponse en JSON
        if(data.message) {
          this.log = data.message;
          this.orders = data; // Stocker les commandes dans le tableau `orders`
          // Assurez-vous que orders est un tableau valide
          console.log(this.orders);  // Accède à l'élément 0,1 du tableau

                  // Parcours des éléments à partir de l'indice 1
          for (var i = 1; i < this.orders.length; i++) {
              // Assurez-vous que this.orders[i] a un élément à l'indice 13
              this.orderDay.push(this.orders[i][0]);
              this.orderID.push(this.orders[i][13]);  // Ajoute l'élément à orderID
              this.orderPhone.push(this.orders[i][2]); 
              this.orderName.push(this.orders[i][1]); 
          }
          this.isListed = true;
          console.log(this.orderID);  // Affiche les IDs dans orderID
        } else {
          this.log = data.message;
        }

    } else {
      this.log = 'ERROR in response form';
    }*/


  } catch (error) {
    this.log = `error in getting request ${error}`;
    this.isMounted = true;
    this.isListed = false;
    //console.error("Erreur lors de la récupération des commandes:", error);
  }
},

async getDelivery(index, method, zone) {
  try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getDelivery', {
          method: 'GET',
      });

      if (!response.ok) {
          return;
      }

      this.deleveryMethod = await response.json();
      
      this.deleveryMethod = this.deleveryMethod.data[0].options;
      const exists = this.deleveryMethod.findIndex(item => item.name === method);
      if(exists != -1) {
        this.deliveryList[index] = this.deleveryMethod[exists];
        this.orderMInit[index] = exists;
        this.getDelPrice(index, zone);
        if(this.upsWork) {
          const init = this.deleveryMethod[0].price.findIndex(item => item.name === zone);
          this.getCommunes(index, init, zone);
        }
      }else {
        this.deliveryList[index] = this.deleveryMethod[0];
        this.orderMInit[index] = 0;
        this.orderMethod[index] = this.deleveryMethod[0].name;
        this.getDelPrice(index, zone);
        if(this.upsWork) {
          const init = this.deleveryMethod[0].price.findIndex(item => item.name === zone);
          this.getCommunes(index, init, zone);
        }
      }
      
  } catch (error) {
    console.log('Error in fetching: ', error);
  }
},

getDelPrice (index, name) {
  const exists = this.deliveryList[index].price.findIndex(item => item.name === name);
  if(exists != -1) {
        this.orderDelPrice[index] = {
          home: this.deliveryList[index].price[exists].home_price,
          desk: this.deliveryList[index].price[exists].desk_price,
        }

      }else {
        this.orderDelPrice[index] = {
          home: 0,
          desk: 0,
        }

      }
},

async getUps () {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=testUps', {
      method: 'GET'
    });

    if (!response.ok) {
      console.log('error in response');
      return;
    }

    const textResponse = await response.json();

    if (textResponse.success) {
      if (textResponse.data.work == 1) {
        this.upsWork = true;
      } else {
        this.upsWork = false;
      }

      
      
    } else {
      console.error('textResponse: ', textResponse.message);
    }
},

async getYal () {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=testYalidine', {
      method: 'GET'
    });

    if (!response.ok) {
      console.log('error in response');
      return;
    }

    const textResponse = await response.json();

    if (textResponse.success) {
      if (textResponse.data.work == 1) {
        this.yalWork = true;
      } else {
        this.yalWork = false;
      }
    } else {
      console.error('textResponse: ', textResponse.message);
    }
},

async getGpx () {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=testGuepex', {
      method: 'GET'
    });

    if (!response.ok) {
      console.log('error in response');
      return;
    }

    const textResponse = await response.json();

    if (textResponse.success) {
      if (textResponse.data.work == 1) {
        this.gpxWork = true;
      } else {
        this.gpxWork = false;
      }
    } else {
      console.error('textResponse: ', textResponse.message);
    }
},

/*async getYalCommunes() {
  const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getYalidineCommune', {
        method: 'GET',
    });

    const data = await response2.json();
},*/



async getCommunes(index, wilaya_id, wilaya) {
  const setToUps = {
      'wilaya_id': wilaya_id,
      'nom': wilaya,
  };

try {
    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getCommune', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(setToUps),
    });

    const data2 = await response2.json();
    
    
    // Vérifier si le <select> existe
    const selectCommune = document.getElementById("commune");
    if (!selectCommune) {
        console.error("Le champ <select> #commune n'existe pas !");
        return;
    }

    // Vider le <select> avant d'ajouter les nouvelles communes
    selectCommune.innerHTML = '';

    // Ajouter les nouvelles options
    data2.forEach(commune => {
        let option = document.createElement("option");
        option.value = commune.nom; // Utiliser le nom comme valeur
        option.textContent = commune.nom; // Nom et code postal
        
        selectCommune.appendChild(option);
        selectCommune.value = this.orderSZone[index];
    });

} catch (error) {
    console.error("Erreur lors de la récupération des communes:", error);
}
}
}
};
</script>

<style>

.ip-container {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid var(--color-whity);
  background-color: var(--color-whizy);
  font-size: 15px;
  margin: 8px 0;
}

.dark .ip-container {
  border: 1px solid var(--color-darkly);
  background-color: var(--color-darkow);
}

.ip-label {
  margin: 0;
  font-weight: 500;
}

.copy-btn {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
  transition: transform 0.2s;
}
.copy-btn:hover {
  transform: scale(1.2);
}

.boxItems{
display: flex; 
justify-content: center; 
align-items: center; 
padding: 5px; 
margin-inline: 5px; 
background: var(--color-whitly); 
border-radius: 8px;
}
.dark .boxItems {
background: black; 
}

.loading{
position:fixed;
height: 100%;
width: 100%;
display: flex;
justify-content: center;
align-items: center;
align-self: center;
}

.optionBar{
display: flex;
justify-content: space-between;
align-items: center;
width: 100%;
height: 50px;
margin-block: 20px;
min-width: 200px;
max-width: 800px;

}


.optionBar2{
display: flex;
justify-content: space-around;
align-items: center;
width: 100%;
min-width: 200px;
height: 50px;
}


.filterBtn{
display: flex;
justify-content: space-between;
align-items: center;
min-width: 80px;
max-width: 150px;
height: 30px;
margin: 10px;
padding: 8px;
cursor: pointer;
background-color: var(--color-whitly);
border-radius: 6px;
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
color: var(--color-darkly);
}
.dark .filterBtn{
background-color: var(--color-darkly);
color: var(--color-whitly);
}
.filterBtn svg{
color: var(--color-darkly);
margin-inline: 5px;
}
.dark .filterBtn svg{
color: var(--color-whitly);
}

.changeBtn{
display: flex;
justify-content: space-between;
align-items: center;
min-width: 100px;
height: 30px;
margin: 10px;
padding: 8px;
cursor: pointer;
color: var(--color-darkly);
}
.dark .changeBtn{
background-color: var(--color-darkly);
color: var(--color-whitly);
}
.changeBtn svg{
color: var(--color-darkly);
min-width: 25px;
}
.dark .changeBtn svg{
color: var(--color-whitly);
}

.listedBtn{
width: 50%;
height: 30px;
display: flex;
justify-content: space-between;
align-items: center;
min-width: 150px;
max-width: 280px;
background-color: var(--color-whitly);
border-radius: 6px;
margin: 15px;
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .listedBtn{
background-color: var(--color-darkly);
}

.filterList{
height: auto; /* Permet d'ajuster la hauteur selon le contenu */
display: flex;
flex-wrap: wrap; /* Permet le retour à la ligne si nécessaire */
justify-content: space-between;
align-items: center;
gap: 5px; /* Ajoute un espacement entre les éléments */

}


.listedBtn2{
display: flex;
justify-content: space-between;
align-items: center;
width: 100%;
font-size: 12px;
margin: 5px;
}

.listedBtn3 {
display: flex;
justify-content: space-between;
align-items: center;
width: 100%;

margin: 5px;
background-color: var(--color-whitly);
border-radius: 6px;
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);

overflow-x: auto; /* Permet le scroll horizontal si nécessaire */
white-space: nowrap; /* Empêche le retour à la ligne des éléments */
padding: 5px;
}

.dark .listedBtn3 {
background-color: var(--color-darkly);
}

.dark .listedBtn3 button {
color: var(--color-whitly);
}

.listedBtn3 button h4 {
font-size: 14px;
}

.listedBtn3 button h5 {
font-size: 10px;
}

/* Ajoute un style pour la barre de défilement si nécessaire */
.listedBtn3::-webkit-scrollbar {
height: 5px;
}

.listedBtn3::-webkit-scrollbar-thumb {
background: rgba(0, 0, 0, 0.2);
border-radius: 10px;
}

.iconBtn{
display: flex;
justify-content: center;
align-items: center;
flex-direction: column;
height: 50px;
width: 100%;
margin: 5px;
padding: 2px;
cursor: pointer;
overflow: hidden; /* Empêche le texte de déborder */
text-overflow: ellipsis; /* Ajoute des "..." si le texte est trop long */
white-space: nowrap; /* Empêche le texte de passer à la ligne */
}
.dark .iconBtn svg{
color: var(--color-whitly);
}

.iconBtnDel{
display: flex;
justify-content: center;
align-items: center;
flex-direction: column;
height: 50px;
width: 100%;
min-width: 24px;
margin: 5px;
padding: 2px;
cursor: pointer;
overflow: hidden; /* Empêche le texte de déborder */
text-overflow: ellipsis; /* Ajoute des "..." si le texte est trop long */
white-space: nowrap; /* Empêche le texte de passer à la ligne */
}
.dark .iconBtnDel svg{
color: var(--color-rady);
}

.numberBtn{
display: flex;
justify-content: center;
align-items: center;
flex-direction: column;
min-width: 30px;
max-width: 30px;
height: 50px;
padding: 5px;
cursor: pointer;
font-size: 10px;
color: var(--color-darkly);
}
.numberBtn svg {
color: var(--color-darkly);
}

.childElement {
width: 20%;
min-width: 85px;
height: 40px;
margin: 5px;
display: flex;
align-items: start;
justify-content: center;
flex-direction: column;
overflow: hidden; /* Empêche le texte de déborder */
text-overflow: ellipsis; /* Ajoute des "..." si le texte est trop long */
white-space: nowrap; /* Empêche le texte de passer à la ligne */
}

.childElement4 {
width: 20%;
min-width: 50px;
height: 40px;
margin: 5px;
display: flex;
align-items: start;
justify-content: center;
flex-direction: column;
overflow: hidden; /* Empêche le texte de déborder */
text-overflow: ellipsis; /* Ajoute des "..." si le texte est trop long */
white-space: nowrap; /* Empêche le texte de passer à la ligne */
}


.childElement2 {
position: relative;
flex: 1 1 auto; /* Permet de répartir l’espace */
min-width: 150px;
max-width: 100%; /* Empêche de dépasser */
margin: 5px;
display: flex;
align-items: start;
justify-content: center;
flex-direction: column;
overflow: hidden;
text-overflow: ellipsis;
white-space: nowrap;
padding: 5px;
background-color: var(--color-wagly);
border-radius: 8px;
height: auto;
}

.childElement3 {
min-width: 150px;
flex: 1 1 auto;
margin: 5px;
display: flex;
align-items: start;
justify-content: center;
flex-direction: column;
overflow: hidden;
text-overflow: ellipsis;
white-space: nowrap;
padding: 5px;
background-color: var(--color-wagly);
border-radius: 8px;
}

.wrraping {
display: flex;
align-items: flex-end;
justify-content: flex-start;
flex: 1 1 auto; /* Prend un espace flexible */
min-width: 150px;
}

.alignStart {
width: 100%;
display: flex;
align-items: flex-start;
justify-content: flex-start;
flex-direction: column;
}


.dark .childElement2 {
background-color: var(--color-darky);
}

.childElement2 .edit-icon {
position: absolute;
top: 5px;
right: 5px;
font-size: 14px;
color: #fff;
background: rgba(0, 0, 0, 0.2);
padding: 4px;
border-radius: 50%;
cursor: pointer;
transition: background 0.3s ease;
}
.dark .childElement2 .edit-icon {
background: rgb(47, 47, 47);
}

.childElement2 .edit-icon:hover {
background: rgba(0, 0, 0, 0.4);
}






.childElement h4{
font-size: 14px;
font-weight: bold;
color: var(--color-darkly);
}
.dark .childElement h4{
color: var(--color-whitly);
}

.childElement h5{
font-size: 12px;
color: var(--color-garry);
}
.dark .childElement h5{
color: var(--color-gorry);
}

.childElement2 h4{
font-size: 14px;
font-weight: bold;
color: var(--color-darkly);
}
.dark .childElement2 h4{
color: var(--color-whitly);
}

.childElement2 h5{
font-size: 12px;
color: var(--color-garry);
}
.dark .childElement2 h5{
color: var(--color-gorry);
}

.childElement4 h4{
font-size: 14px;
font-weight: bold;
color: var(--color-darkly);
}
.dark .childElement4 h4{
color: var(--color-whitly);
}

.childElement4 h5{
font-size: 12px;
color: var(--color-garry);
}
.dark .childElement4 h5{
color: var(--color-gorry);
}

.callBtn{
display: flex; /* Ajout pour centrer correctement */
align-items: center; /* Centrage vertical */
justify-content: center; /* Centrage horizontal */
flex-direction: column;
min-width: 30px;
height: 50px;
margin: 10px;
cursor: pointer;
font-size: 10px;
}
.dark .callBtn svg{
color: var(--color-whitly);
}

.notSelected {
display: flex; /* Ajout pour centrer correctement */
align-items: center; /* Centrage vertical */
justify-content: center; /* Centrage horizontal */
min-width: 14px;
height: 14px;
margin: 10px;
cursor: pointer;
border: 2px solid var(--color-darkly); /* Correction de la propriété border */
border-radius: 50%; /* Correction pour un cercle parfait */
}
.dark .notSelected{
border: 2px solid var(--color-whitly);
}

.selected {
display: flex; /* Ajout pour centrer correctement */
align-items: center; /* Centrage vertical */
justify-content: center; /* Centrage horizontal */
min-width: 12px;
height: 12px;
margin: 11px;
cursor: pointer;
background-color: var(--color-rady);
border-radius: 50%; /* Correction pour un cercle parfait */
}

.selected2 {
align-content: center; /* Centrage vertical */
min-width: 8px;
height: 8px;
cursor: pointer;
background-color: var(--color-green-500);
border-radius: 50%; /* Correction pour un cercle parfait */
margin: 5px;
}

/* Ajoute une transition fluide pour le dropdown */
.dropdown-enter-active, .dropdown-leave-active {
transition: opacity 0.2s ease-in-out;
}
.dropdown-enter, .dropdown-leave-to {
opacity: 0;
}

.note {
display: flex;
flex-wrap: wrap; /* Permet le passage à la ligne */
align-items: center;
width: calc(100% - 10px);
background-color: white;
border: 2px solid var(--color-gorry);
border-radius: 6px;
padding: 5px;
overflow: hidden;
transition: all 0.3s ease;
margin: 5px;
}

.dark .note{
background-color: var(--color-darkly);
border: 2px solid var(--color-garry);
}

.note button{
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

.note button svg{
color: var(--color-whity);
}

.noteBar {
flex: 1;
border: none;
outline: none;
background: transparent;
font-size: 16px;
padding: 10px 15px;
resize: none;
min-height: 40px;
height: auto;
overflow-y: auto;
color: var(--color-darkly);
word-break: break-word; /* Permet au texte de couper et passer à la ligne */
white-space: normal; /* Permet au texte de s'étendre verticalement */
}
/* Effet focus */
.noteBar:focus {
border-color: #007bff;
}
/* Mode sombre */
.dark .noteBar {
background: transparent;
color: var(--color-whitly);
border-color: var(--color-darky);
}
.dark .noteBar:focus {
border-color: #00bcd4;
}
/* Ajustement automatique de la hauteur */
.noteBar.auto-expand {
min-height: 50px;
height: auto;
overflow-y: hidden;
}

.noteBar2 {
border: none;
outline: none;
background: transparent;
font-size: 16px;
padding: 10px 15px;
height: 40px;
}

.moreOrder{
display: flex;
flex-direction: column;
margin-block: 5px;
}

.moreDirect{
width: 100%;
max-width: 800px;
align-items: center;
margin-block: 5px;
border-radius: 6px;
padding: 10px;
background-color: var(--color-whitly);
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .moreDirect{
background-color: var(--color-darkly);
}

</style>