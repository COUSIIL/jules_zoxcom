<template>
    <div class="flex flex-col items-center justify-center w-full">
        <div class="flex flex-col items-center justify-center w-full max-w-3xl p-5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
            <h1>
                {{ t('add order') }}
            </h1>
        
            <h2 v-if="getting">
                {{ prodLog }}
            </h2>
        </div>
        
        <div class="flex flex-col items-center justify-center w-full max-w-3xl p-5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
            
            <label>{{ t('choose products') }}</label>
            <div class="flex p-2.5 overflow-x-auto space-x-2.5">
                <div v-for="(product, index) in productList" :key="product.id">  
                    <div class="flex-col max-w-xs min-w-[200px] rounded-md shadow-lg">
        
                            <span>{{ product.name }}</span>
                            <img v-if="product.image.startsWith('https')" 
                                 :src="product.image" 
                                 :alt="'product ' + index" 
                                 class="max-w-xs max-h-52 min-w-24 min-h-24"/>
                            <img v-else 
                                 :src="'https://management.dinarz.shop' + product.image" 
                                 :alt="'product ' + index" 
                                 class="max-w-xs max-h-52 min-w-24 min-h-24"/>
        
                        <!-- Modèles de produit -->
                        <div class="flex p-2.5 m-1.25 overflow-x-auto space-x-2.5">
                            <button class="flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" v-for="(model, i) in product.models"
                                    :key="model.id" 
                                    @click="setProducts(index, i)">   
                                <div class="flex flex-col items-center justify-center w-full max-w-3xl p-2.5 my-1.5 text-lg overflow-hidden transition-all duration-300 ease-in-out rounded-md">
                                    {{ model.modelName }}
                                    <img v-if="model.image.startsWith('https')" 
                                         :src="model.image" 
                                         :alt="'product ' + i" 
                                         class="max-w-20 max-h-20 min-w-20 min-h-20"/>
                                    <img v-else 
                                         :src="'https://management.dinarz.shop' + model.image" 
                                         :alt="'product ' + i" 
                                         class="max-w-20 max-h-20 min-w-20 min-h-20"/>
                                </div>
                            </button> 
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
        
        
        
        
        <div class="flex flex-col items-center justify-center w-full max-w-3xl p-5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
            <form v-if="isMounted" @submit.prevent="saveOrder" class="w-11/12">
                <div v-if="selectedProd.length > 0" v-for="(product, index) in selectedProd" :key="product.id">
                    <div class="m-2.5 min-w-[calc(100% - 20px)] border border-dotted rounded-md border-hoggari">
                        <h3>
                            {{product.name}}
                            
                        </h3>

                        <h3>
                            {{product.price}}
                            
                        </h3>
                        <div class="flex flex-col items-center justify-center w-full p-2.5">
                            <img class="w-full border-2 rounded-md" v-if="product.image.startsWith('https')"
                            :src="product.image" 
                            :alt="'product ' + i" 
                            />
                            <img v-else class="w-full border-2 rounded-md"
                            :src="'https://management.dinarz.shop' + product.image" 
                            :alt="'product ' + i" 
                            />
                            <div class="flex p-2.5 mt-2.5 overflow-x-auto max-h-28 space-x-2.5">
                                <div v-for="(detail, indexD) in product.details" :key="indexD">
                                    <button type="button" @click="addDetail(index, indexD)"
                                    :class="product.indexD != indexD ? 'bg-whity dark:bg-darky' : 'bg-whity dark:bg-darky border-2 border-rangy'" 
                                    class="flex flex-col items-center justify-center w-24 h-20 m-1.25 cursor-pointer rounded-md">
                                        <div :style="{width: '40px', 
                                        height: '40px',
                                        backgroundColor: detail.color,
                                        borderRadius: '50%',
                                        border: '2px solid var(--color-garry)'
                                    }">

                                        </div>
                                        <div class="w-24 overflow-hidden text-ellipsis whitespace-nowrap">
                                            {{detail.size}}
                                        </div>
                                    </button>
                                

                                
                            </div>
                            </div>

                        </div>

                        <div v-for="(select, indexS) in product.selected" :key="indexS" class="w-full">
                            <div class="flex items-center justify-center w-full">
                                <div type="button"
                                class="flex flex-col items-center justify-center w-24 h-20 m-2.5 rounded-md bg-whity dark:bg-darky">
                                    <div :style="{width: '40px', 
                                    height: '40px',
                                    backgroundColor: select.color,
                                    borderRadius: '50%',
                                    border: '2px solid var(--color-garry)'
                                    }">

                                    </div>
                                        <div class="w-24 overflow-hidden text-ellipsis whitespace-nowrap">
                                            {{select.size}}
                                        </div>
                                    </div>
                                    
                            <div class="w-full m-2.5">
                                <input class="flex items-center w-full min-w-full max-w-md p-1 mt-2.5 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="number" v-model="select.qty" min="1"  @change="calculateDtails(index)" required>
                                <div class="flex items-center justify-between w-full">
                                    <h3 class="flex items-center justify-center">
                                        <svg class="m-1.25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none">
                                            <path d="M14.5 12C14.5 13.3807 13.3807 14.5 12 14.5C10.6193 14.5 9.5 13.3807 9.5 12C9.5 10.6193 10.6193 9.5 12 9.5C13.3807 9.5 14.5 10.6193 14.5 12Z" stroke="currentColor" stroke-width="1.5" />
                                            <path d="M19 11.1415C18.6749 11.0944 18.341 11.0586 18 11.0347M6 12.9653C5.65897 12.9415 5.32511 12.9056 5 12.8585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M12 19.5C10.6675 20.1224 8.91707 20.5 7 20.5C5.93408 20.5 4.91969 20.3833 4 20.1726C2.49957 19.8289 2 18.9264 2 17.386V6.61397C2 5.62914 3.04003 4.95273 4 5.1726C4.91969 5.38325 5.93408 5.5 7 5.5C8.91707 5.5 10.6675 5.12236 12 4.5C13.3325 3.87764 15.0829 3.5 17 3.5C18.0659 3.5 19.0803 3.61675 20 3.8274C21.5817 4.18968 22 5.12036 22 6.61397V17.386C22 18.3709 20.96 19.0473 20 18.8274C19.0803 18.6167 18.0659 18.5 17 18.5C15.0829 18.5 13.3325 18.8776 12 19.5Z" stroke="currentColor" stroke-width="1.5" />
                                        </svg>
                                        {{product.price * select.qty}}
                                        
                                    </h3>
                                    <button type="button" @click="clearDetail(index, indexS)">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#ff5555" fill="none">
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
                        <h3 class="flex items-center justify-between">
                            
                            {{ t('total:') }}
                            <div class="flex items-center justify-between">
                                <svg class="m-1.25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none">
                                    <path d="M14.5 12C14.5 13.3807 13.3807 14.5 12 14.5C10.6193 14.5 9.5 13.3807 9.5 12C9.5 10.6193 10.6193 9.5 12 9.5C13.3807 9.5 14.5 10.6193 14.5 12Z" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M19 11.1415C18.6749 11.0944 18.341 11.0586 18 11.0347M6 12.9653C5.65897 12.9415 5.32511 12.9056 5 12.8585" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12 19.5C10.6675 20.1224 8.91707 20.5 7 20.5C5.93408 20.5 4.91969 20.3833 4 20.1726C2.49957 19.8289 2 18.9264 2 17.386V6.61397C2 5.62914 3.04003 4.95273 4 5.1726C4.91969 5.38325 5.93408 5.5 7 5.5C8.91707 5.5 10.6675 5.12236 12 4.5C13.3325 3.87764 15.0829 3.5 17 3.5C18.0659 3.5 19.0803 3.61675 20 3.8274C21.5817 4.18968 22 5.12036 22 6.61397V17.386C22 18.3709 20.96 19.0473 20 18.8274C19.0803 18.6167 18.0659 18.5 17 18.5C15.0829 18.5 13.3325 18.8776 12 19.5Z" stroke="currentColor" stroke-width="1.5" />
                                </svg>
                                {{product.total}}
                            </div>
                            
                            
                        </h3>

                        <button class="flex items-center justify-around w-4/5 max-w-md m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="removeProduct(index)">
                            {{ t('remove product') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M16 12L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12Z" stroke="currentColor" stroke-width="1.5" />
                            </svg>
                        </button>

                        
                    </div>
                    
                </div>
                <div class="flex items-center justify-center my-12">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
                        <path d="M12 7.5H13.5C14.3284 7.5 15 8.17157 15 9M12 7.5H10.5C9.67157 7.5 9 8.17157 9 9V9.5C9 10.3284 9.67157 11 10.5 11H13.5C14.3284 11 15 11.6716 15 12.5V13C15 13.8284 14.3284 14.5 13.5 14.5H12M12 7.5V6M12 14.5H10.5C9.67157 14.5 9 13.8284 9 13M12 14.5V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.94 21.5124L9.02913 20.3073C8.54415 20.0014 8.30166 19.8485 8.03253 19.8397C7.74172 19.8301 7.49493 19.9768 6.97087 20.3073C6.38395 20.6774 5.21687 21.6971 4.46195 21.2108C4 20.9133 4 20.1575 4 18.6458V8.00002C4 5.17158 4 3.75736 4.82699 2.87868C5.65399 2 6.98501 2 9.64706 2H14.3529C17.015 2 18.346 2 19.173 2.87868C20 3.75736 20 5.17158 20 8.00002V18.6458C20 20.1575 20 20.9133 19.538 21.2108C18.7831 21.6971 17.6161 20.6774 17.0291 20.3073C16.5441 20.0014 16.3017 19.8485 16.0325 19.8397C15.7417 19.8301 15.4949 19.9768 14.9709 20.3073L13.06 21.5124C12.5445 21.8374 12.2868 22 12 22C11.7132 22 11.4554 21.8374 10.94 21.5124Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="w-1/2 min-w-[100px] p-2.5 text-lg text-black rounded-lg bg-yelly">

                        <h3>
                            {{ qty }} {{ t('articles for') }} {{ totalProdPrice }}
                        </h3>
                        
                    </div>
                </div>

                <div class="m-2.5 min-w-[calc(100% - 20px)] border border-dotted rounded-md border-hoggari">
                    <h3 class="flex items-center justify-between w-11/12 max-w-md">
                        {{ t('phone') }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                            <path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        </svg>
                        
                    </h3>
                    
                    <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="phone" @blur="getCustomer(phone)" required>

                <h3 class="flex items-center justify-between w-11/12 max-w-md">
                    {{ t('name') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M13 21.9506C12.6711 21.9833 12.3375 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 12.3375 21.9833 12.6711 21.9506 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M7.5 17C8.90247 15.5311 11.0212 14.9041 13 15.1941M14.4951 9.5C14.4951 10.8807 13.3742 12 11.9915 12C10.6089 12 9.48797 10.8807 9.48797 9.5C9.48797 8.11929 10.6089 7 11.9915 7C13.3742 7 14.4951 8.11929 14.4951 9.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <circle cx="18.5" cy="18.5" r="3.5" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    
                </h3>
                    
                <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="name" required>
                
                
    
        
                <h3 class="flex items-center justify-between w-11/12 max-w-md">
                    {{ t('chose country') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M4 7L4 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.7576 3.90865C8.45236 2.22497 5.85125 3.21144 4.55426 4.2192C4.32048 4.40085 4.20358 4.49167 4.10179 4.69967C4 4.90767 4 5.10138 4 5.4888V14.7319C4.9697 13.6342 7.87879 11.9328 11.7576 13.9086C15.224 15.6744 18.1741 14.9424 19.5697 14.1795C19.7633 14.0737 19.8601 14.0207 19.9301 13.9028C20 13.7849 20 13.6569 20 13.4009V5.87389C20 5.04538 20 4.63113 19.8027 4.48106C19.6053 4.33099 19.1436 4.459 18.2202 4.71504C16.64 5.15319 14.3423 5.22532 11.7576 3.90865Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                </h3>
                    
                    <select class="flex items-center justify-around w-11/12 min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" v-model="country" required>
                        <option v-for="(country, index) in deliveryList" :key="country.id">
                         {{ country.name }}
                        </option>
                    </select>

                    <h3 v-if="country" class="flex items-center justify-between w-11/12 max-w-md">
                        {{ t('chose method') }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                            <path d="M19.5 17.5C19.5 18.8807 18.3807 20 17 20C15.6193 20 14.5 18.8807 14.5 17.5C14.5 16.1193 15.6193 15 17 15C18.3807 15 19.5 16.1193 19.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                            <path d="M9.5 17.5C9.5 18.8807 8.38071 20 7 20C5.61929 20 4.5 18.8807 4.5 17.5C4.5 16.1193 5.61929 15 7 15C8.38071 15 9.5 16.1193 9.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                            <path d="M14.5 17.5H9.5M19.5 17.5H20.2632C20.4831 17.5 20.5931 17.5 20.6855 17.4885C21.3669 17.4036 21.9036 16.8669 21.9885 16.1855C22 16.0931 22 15.9831 22 15.7632V13C22 9.41015 19.0899 6.5 15.5 6.5M2 4H12C13.4142 4 14.1213 4 14.5607 4.43934C15 4.87868 15 5.58579 15 7V15.5M2 12.75V15C2 15.9346 2 16.4019 2.20096 16.75C2.33261 16.978 2.52197 17.1674 2.75 17.299C3.09808 17.5 3.56538 17.5 4.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M2 7H8M2 10H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        
                    </h3>

                    
                    <select v-if="country" class="flex items-center justify-around w-11/12 min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" v-model="method" required>
                        <option v-for="(methodItem, i) in getMethods(country)" :key="i" :value="methodItem">
                            {{ methodItem.name }}
                        </option>
                    </select>

                    <div v-if="country && method && index1 != -1" class="flex flex-col items-center justify-between w-full max-w-md">
                        <h3 class="flex items-center justify-between w-11/12 max-w-md">
                            {{ deliveryList[index1].city_name }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M14 8H10C7.518 8 7 8.518 7 11V22H17V11C17 8.518 16.482 8 14 8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M11 12L13 12M11 15H13M11 18H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M21 22V8.18564C21 6.95735 21 6.3432 20.7013 5.84966C20.4026 5.35612 19.8647 5.08147 18.7889 4.53216L14.4472 2.31536C13.2868 1.72284 13 1.93166 13 3.22873V7.7035" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M3 22V13C3 12.1727 3.17267 12 4 12H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M22 22H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            
                        </h3>
                        
                        <select class="flex items-center justify-around w-11/12 min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" v-model="select" required>
                            <option v-for="(option, index) in method.price" :key="option.id">
                                {{option.name}}
                            </option>
                        </select>
                        
                        
                    
            
                    <div v-if="select" class="flex flex-col items-center justify-center w-11/12 max-w-md">
                        <h3 class="w-11/12 max-w-md">
                            {{ t('delivery zone') }}
                        </h3>
                        <div class="flex items-center justify-center w-11/12 max-w-md">
                            <button :class="zone1 === true ? 'flex items-center justify-around m-2.5 w-full min-w-[100px] max-w-[400px] p-1.5 cursor-pointer border border-rangy bg-whity rounded-md shadow-[0px_2px_5px_var(--color-hoggari)] text-darkly dark:bg-darky dark:text-whitly' : 'flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky'" type="button" class="flex flex-col items-center justify-center w-1/2 h-24 max-w-md" :checked="zone1" @click="calculateDeliveryPrice('home')">
                                <h3>
                                    {{ t('home') }}
                                </h3>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                    <path d="M3.16405 11.3497L4 11.5587L4.45686 16.1005C4.715 18.6668 4.84407 19.9499 5.701 20.7249C6.55793 21.5 7.84753 21.5 10.4267 21.5H13.5733C16.1525 21.5 17.4421 21.5 18.299 20.7249C19.1559 19.9499 19.285 18.6668 19.5431 16.1005L20 11.5587L20.836 11.3497C21.5201 11.1787 22 10.564 22 9.85882C22 9.35735 21.7553 8.88742 21.3445 8.59985L13.1469 2.86154C12.4583 2.37949 11.5417 2.37949 10.8531 2.86154L2.65549 8.59985C2.24467 8.88742 2 9.35735 2 9.85882C2 10.564 2.47993 11.1787 3.16405 11.3497Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="14.5" r="2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
    
                            <button :class="zone2 === true ? 'flex items-center justify-around m-2.5 w-full min-w-[100px] max-w-[400px] p-1.5 cursor-pointer border border-rangy bg-whity rounded-md shadow-[0px_2px_5px_var(--color-hoggari)] text-darkly dark:bg-darky dark:text-whitly' : 'flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky'" type="button" class="flex flex-col items-center justify-center w-1/2 h-24 max-w-md" :checked="zone2" @click="calculateDeliveryPrice('desk')">
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
        
                    <div v-if="deliveryPrice">
                        <h3>
                            {{ deliveryPrice }}
                        </h3>
                        
                    </div>
            

                        <h3 class="flex items-center justify-between w-11/12 max-w-md">
                            {{deliveryList[index1].hall_name}}
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
                        
                        <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="sZone" required>

        
        

                        <h3 v-if="deliveryList[index1].adress_name" class="flex items-center justify-between w-11/12 max-w-md">
                            {{deliveryList[index1].adress_name}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M3.77762 11.9424C2.8296 10.2893 2.37185 8.93948 2.09584 7.57121C1.68762 5.54758 2.62181 3.57081 4.16938 2.30947C4.82345 1.77638 5.57323 1.95852 5.96 2.6524L6.83318 4.21891C7.52529 5.46057 7.87134 6.08139 7.8027 6.73959C7.73407 7.39779 7.26737 7.93386 6.33397 9.00601L3.77762 11.9424ZM3.77762 11.9424C5.69651 15.2883 8.70784 18.3013 12.0576 20.2224M12.0576 20.2224C13.7107 21.1704 15.0605 21.6282 16.4288 21.9042C18.4524 22.3124 20.4292 21.3782 21.6905 19.8306C22.2236 19.1766 22.0415 18.4268 21.3476 18.04L19.7811 17.1668C18.5394 16.4747 17.9186 16.1287 17.2604 16.1973C16.6022 16.2659 16.0661 16.7326 14.994 17.666L12.0576 20.2224Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            </svg>
                            
                        </h3>
                        <h3 v-else class="flex items-center justify-between w-11/12 max-w-md">
                            {{ t('adresse') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z" stroke="currentColor" stroke-width="1.5" />
                                <path d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z" stroke="currentColor" stroke-width="1.5" />
                            </svg>
                            
                        </h3>
                        
                        
                        <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="mZone">

                    </div>
        
        
                


                <div class="flex flex-col items-center justify-between w-11/12 max-w-md">
                    <div class="flex items-center justify-between w-11/12">
                        {{ t('discount code') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M10.9961 10H11.0111M10.9998 16H11.0148" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M7 13H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="1.5" cy="1.5" r="1.5" transform="matrix(1 0 0 -1 16 8)" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2.77423 11.1439C1.77108 12.2643 1.7495 13.9546 2.67016 15.1437C4.49711 17.5033 6.49674 19.5029 8.85633 21.3298C10.0454 22.2505 11.7357 22.2289 12.8561 21.2258C15.8979 18.5022 18.6835 15.6559 21.3719 12.5279C21.6377 12.2187 21.8039 11.8397 21.8412 11.4336C22.0062 9.63798 22.3452 4.46467 20.9403 3.05974C19.5353 1.65481 14.362 1.99377 12.5664 2.15876C12.1603 2.19608 11.7813 2.36233 11.472 2.62811C8.34412 5.31646 5.49781 8.10211 2.77423 11.1439Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    </div>
                    
                    <input class="flex items-center w-11/12 max-w-md p-1 m-1 text-lg font-bold text-center text-blue-400 bg-white border-2 border-dashed rounded-md border-blue-400 dark:bg-darkly focus:border-blue-400" type="text" v-model="test" @change="testDiscount">
                </div>
                
                
                <div v-if="disLog">
                    {{ disLog }}
                </div>
            
        


                <h3 class="flex items-center justify-between w-11/12 max-w-md">
                    {{ t('take note') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M10.6119 5.00008L10.0851 7M12.2988 2.76313C12.713 3.49288 12.4672 4.42601 11.7499 4.84733C11.0326 5.26865 10.1153 5.01862 9.70118 4.28887C9.28703 3.55912 9.53281 2.62599 10.2501 2.20467C10.9674 1.78334 11.8847 2.03337 12.2988 2.76313Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M13 21.998C12.031 20.8176 10.5 18 8.5 18C7.20975 18.1059 6.53573 19.3611 5.84827 20.3287M5.84827 20.3287C5.45174 19.961 5.30251 19.4126 5.00406 18.3158L3.26022 11.9074C2.5584 9.32827 2.20749 8.0387 2.80316 7.02278C3.39882 6.00686 4.70843 5.66132 7.32766 4.97025L9.5 4.39708M5.84827 20.3287C6.2448 20.6965 6.80966 20.8103 7.9394 21.0379L12.0813 21.8725C12.9642 22.0504 12.9721 22.0502 13.8426 21.8205L16.6723 21.0739C19.2916 20.3828 20.6012 20.0373 21.1968 19.0214C21.7925 18.0055 21.4416 16.7159 20.7398 14.1368L19.0029 7.75375C18.301 5.17462 17.9501 3.88506 16.9184 3.29851C16.0196 2.78752 14.9098 2.98396 12.907 3.5" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    
                </h3>
                
                <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="note">

        
            <div v-if="total" class="flex flex-col items-center justify-center w-full">
                <div class="flex items-center justify-center w-full my-12">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
                        <path d="M12 7.5H13.5C14.3284 7.5 15 8.17157 15 9M12 7.5H10.5C9.67157 7.5 9 8.17157 9 9V9.5C9 10.3284 9.67157 11 10.5 11H13.5C14.3284 11 15 11.6716 15 12.5V13C15 13.8284 14.3284 14.5 13.5 14.5H12M12 7.5V6M12 14.5H10.5C9.67157 14.5 9 13.8284 9 13M12 14.5V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M10.94 21.5124L9.02913 20.3073C8.54415 20.0014 8.30166 19.8485 8.03253 19.8397C7.74172 19.8301 7.49493 19.9768 6.97087 20.3073C6.38395 20.6774 5.21687 21.6971 4.46195 21.2108C4 20.9133 4 20.1575 4 18.6458V8.00002C4 5.17158 4 3.75736 4.82699 2.87868C5.65399 2 6.98501 2 9.64706 2H14.3529C17.015 2 18.346 2 19.173 2.87868C20 3.75736 20 5.17158 20 8.00002V18.6458C20 20.1575 20 20.9133 19.538 21.2108C18.7831 21.6971 17.6161 20.6774 17.0291 20.3073C16.5441 20.0014 16.3017 19.8485 16.0325 19.8397C15.7417 19.8301 15.4949 19.9768 14.9709 20.3073L13.06 21.5124C12.5445 21.8374 12.2868 22 12 22C11.7132 22 11.4554 21.8374 10.94 21.5124Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="w-1/2 min-w-[100px] p-2.5 text-lg text-black rounded-lg bg-yelly">
                        <h3>
                            {{ t('total') }}
                        </h3>
                        <h3>
                            {{ total }}
                        </h3>
                        
                    </div>
                </div>
                <button class="flex items-center justify-around w-full h-12 max-w-md m-2.5 p-1.5 cursor-pointer rounded-md border border-rangy shadow-[0px_2px_5px_var(--color-rangy)] bg-whity dark:bg-darky" type="submit">
                    {{ t('order it now') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M14.9713 7.5C14.9713 7.5 15.4713 7.5 15.9713 8.5C15.9713 8.5 17.5595 6 18.9713 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.9954 15.042L19.0241 19.5927C18.9748 20.9362 17.8679 22 16.5192 22H5.39281C4.00847 22 2.88623 20.8814 2.88623 19.5014L2.9724 13.0355M8.98101 6.0129L5.1476 5.94884C4.25796 5.92732 3.46051 6.49283 3.18918 7.33765L2.09203 10.7538C1.96224 11.1579 1.95328 11.5994 2.16878 11.9654C2.95433 13.2993 5.06345 15.1192 8.41723 13.163M7.44587 11.3322C7.83597 12.6005 9.36528 14.8259 12.486 13.5372" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22.0003 7.01618C22.0003 9.78653 19.7585 12.0324 16.9932 12.0324C14.2278 12.0324 11.986 9.78653 11.986 7.01618C11.986 4.24582 14.2278 2 16.9932 2C19.7585 2 22.0003 4.24582 22.0003 7.01618Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            
    
        
            <h3 v-if="saveLog">
                {{ saveLog }}
            </h3>
            </div>
            
            
            </form>
        </div>
    </div>


    </template>
    
    <script>
    import { ref, onMounted } from 'vue';
    import { useLang } from '~/composables/useLang';
      
      export default {
        name: 'Add Orders',

        setup() {
    
        const { t } = useLang();
        const isMounted = ref(false);
        const test = ref('');
        const ordLog = ref('');
        const disLog = ref('');
        const prodLog = ref(t('waiting products'));
        const productList = ref([]);
        const selectedProd = ref([]);
        const name = ref('');
        const phone = ref('');
        const qty = ref(0);
        const select = ref('');
        const zone1 = ref(false);
        const zone2 = ref(false);
        const sZone = ref('');
        const mZone = ref('');
        const note = ref('');
        const country = ref('');
        const method = ref('');
        const deliveryList = ref([]);
        const delLog = ref('');
        const index1 = ref();
        const index2 = ref();
        const total = ref(0);
        const deliveryPrice = ref(0);
        const lastDeliveryPrice = ref(0);
        const totalProdPrice = ref(0);
        const discount = ref();
        const saveLog = ref('');
        const getting = ref(false);
        const discountV = ref('');
        const totalBefor = ref(0);

        async function saveOrder() {
            saveLog.value = t('saving order, please wait...');

            if (!name.value || !phone.value || !qty.value || !country.value || !method.value.name) {
                saveLog.value = t('please fill in all required fields.');
                return;
            }

            const orderData = {
                data_time: Math.floor(Date.now() / 1000),
                name: name.value,
                phone: phone.value,
                qty: qty.value,
                country: country.value,
                method: method.value.name,
                select: select.value,
                deliveryValue: `${deliveryPrice.value} DA`,
                type: zone1.value ? "0" : zone2.value ? "1" : "",
                zone1: zone1.value,
                zone2: zone2.value,
                sZone: sZone.value,
                mZone: mZone.value,
                discount: test.value,
                discountValue: discountV.value,
                note: note.value,
                total: total.value,
                selectedProd: selectedProd.value,
            };

            saveLog.value = t('saving order, please wait for response...');

            try {
                const response = await fetch("https://management.hoggari.com/backend/api.php?action=postOrder", {
                    method: "POST",
                    body: JSON.stringify(orderData)
                });
                console.log('response: ', response);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                
                const result = await response.json();
                

                if (result.success) {
                    saveLog.value = t('order saved successfully.');

                    // Réinitialiser toutes les valeurs en une seule étape
                    Object.assign({
                        name, phone, qty, country, method, select, sZone, mZone, note, test, discountV
                    }, {
                        value: '',
                    });

                    Object.assign({
                        zone1, zone2, selectedProd, deliveryList
                    }, {
                        value: [],
                    });

                    Object.assign({
                        total, deliveryPrice, lastDeliveryPrice, totalProdPrice, discount, index1, index2
                    }, {
                        value: 0,
                    });

                    delLog.value = '';
                } else {
                    saveLog.value = result.message;
                }
            } catch (error) {
                console.error('Error:', error);
                saveLog.value = `Error: ${error.message}`;
            }
        }



        function handleZone2Change() {
            zone1.value = true;
                if (zone1.value) {
                zone2.value = false; // Désactive fixedValue si percentage est activé
                }
        }
        function handleZone1Change() {
            zone2.value = true;
                if (zone2.value) {
                zone1.value = false; // Désactive percentage si fixedValue est activé
                }
        }

        function getMethods(selectedCountry) {
            const countryIndex = this.deliveryList.findIndex(item => item.name === selectedCountry);
            index1.value = countryIndex;
            
            return countryIndex !== -1 ? this.deliveryList[countryIndex].options : [];
        }

        function calculateDeliveryPrice(zone) {
            
            var delivery = 0;
            const priceIndex = this.method.price.findIndex(item => item.name === select.value);
            if(zone === 'home') {
                handleZone2Change();
                delivery = this.method.price[priceIndex].home_price;
            } else if (zone === 'desk') {
                handleZone1Change();
                delivery = this.method.price[priceIndex].desk_price;
            }

            deliveryPrice.value = parseFloat(delivery);
            
            updateTotal();
        }


        function updateTotal() {

            
            var dis = 0;
            if (discount.value) {
                if (discount.value.type === 0) {
                    dis = (totalProdPrice.value / 100) * discount.value.value;
                } else if (discount.value.type === 1) {
                   dis = discount.value.value;
                }
            }
            
            total.value = totalProdPrice.value + deliveryPrice.value - dis;
        }



        function removeProduct(index) {
            if (index >= 0 && index < this.selectedProd.length) {
                
                totalProdPrice.value -= this.selectedProd[index].price * this.selectedProd[index].qty;
                qty.value -= this.selectedProd[index].qty;
                this.selectedProd.splice(index, 1);
                updateTotal();
                
            } else {
                console.error('Invalid index:', index);
            }
        };

        function clearDetail(index, indexS) {
            if (index >= 0 && index < this.selectedProd.length) {
                this.selectedProd[index].selected[indexS].qty = 1;
                this.selectedProd[index].selected.splice(indexS, 1);
                this.calculateDtails(index);

                
            } else {
                console.error('Invalid index:', index);
            }
        };

        function calculateDtails(index) {
            
            const item = selectedProd.value[index].selected;
            let soloPrice = 0;

            for (let i = 0; i < item.length; i++) {
                soloPrice += item[i].qty * selectedProd.value[index].price;
            }

            //console.log('item: ', item);

            selectedProd.value[index].total = soloPrice;
            

            qty.value = 0;
            totalProdPrice.value = 0;
            
            for (let i = 0; i < selectedProd.value.length; i++) {
                for (let ii = 0; ii < selectedProd.value[i].selected.length; ii++) {
                    qty.value += selectedProd.value[i].selected[ii].qty;
                    totalProdPrice.value += selectedProd.value[i].selected[ii].qty * selectedProd.value[i].price;
                }
            }
            
        }


        function addDetail(index, indexD) {
            selectedProd.value[index].indexD = indexD;
            const item = selectedProd.value[index].selected;
            const item2 = selectedProd.value[index].details;

                const exists = item.findIndex(items => items.index === indexD);
                if (exists != -1) {
                    selectedProd.value[index].selected[exists].qty++;
                    
                } else {
                    selectedProd.value[index].selected.push({
                        color: item2[indexD].color, 
                        size: item2[indexD].size, 
                        index: indexD,
                        total: item2[indexD].price,
                        qty: 1
                    });
                }
            
            
            calculateDtails(index);
        }



        function setProducts(ID, id) {
            
            const product = productList.value[ID].models[id];


                const exists = selectedProd.value.findIndex(item => item.name === product.modelName);
                if (exists != -1) {
                    
                    totalProdPrice.value += selectedProd.value[exists].price;

                    addDetail(exists, 0);
                } else {
                    //console.log('test2: ', product);
                    selectedProd.value.push({
                        name: product.modelName, 
                        image: product.image, 
                        qty: 1,
                        price: parseFloat(product.sell),
                        ref: product.ref,
                        indexD: selectedProd.value.length,
                        details: product.details,
                        total: parseFloat(product.sell),
                        idP: ID,
                        idM: id,
                        selected: [{
                            color: product.details[0].color, 
                            size: product.details[0].size, 
                            index: 0,
                            total: product.sell,
                            qty: 1
                        }]
                    });

                    calculateDtails(selectedProd.value.length - 1);



                }
          

        }


        async function getProduct() {
            getting.value = true;
            try {
                const response = await fetch('https://management.hoggari.com/backend/api.php?action=getProducts', {
                    method: 'GET',
                });
        
                if (!response.ok) {
                    prodLog.value = t('error in response');
                    isMounted.value = false;
                    getting.value = false;
                    return;
                }
        
                const result = await response.json();
                productList.value = result.data;
                console.log('productList: ', productList.value);
                getting.value = false;
            } catch (error) {
                prodLog.value = t('error in fetching');
                getting.value = false;
            } finally {
                getting.value = false;
                isMounted.value = true;
            }
        }

        async function getDelivery() {
            try {
                const response = await fetch('https://management.hoggari.com/backend/api.php?action=getDelivery', {
                    method: 'GET',
                });
        
                if (!response.ok) {
                    delLog.value = t('error in response');
                    isMounted.value = false;
                    return;
                }
        
                const result = await response.json();
                deliveryList.value = result.data;
                console.log('deliveryList.value: ', deliveryList.value);
            } catch (error) {
                delLog.value = t('error in fetching');
            } finally {
                isMounted.value = true;
            }
        }

        async function getCustomer(phone) {
            const postBody = JSON.stringify({
                phone: phone
            });
    
    
    
            const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getCustomer', {
            method: 'POST',
            body: postBody,
            });
            if(!response2.ok){
                return;
            }
            const textResponse = await response2.json();  // Récupérer la réponse en texte
            if (textResponse.success) {
                    name.value = textResponse.data[0].name;
                    select.value = textResponse.data[0].items[0].delivery_zone;
                    zone1.value = false;
                    zone2.value = false;
                    sZone.value = textResponse.data[0].items[0].sZone;
                    mZone.value = textResponse.data[0].items[0].mZone;
                    country.value = textResponse.data[0].items[0].country;
                    method.value = textResponse.data[0].items[0].method;
                    index1.value = -1;
                    index2.value = 0;
                    deliveryPrice.value = 0;
                    lastDeliveryPrice.value = 0;
                    discount.value = 0;
                    discountV.value = '';
                    test.value = '';
            } else {
                console.log('textResponse message: ', textResponse.message);
            }
        }
    
    
        async function testDiscount() {
            if (test.value) {
                const postBody = JSON.stringify({
                    code: test.value,
                    time: Math.floor(Date.now() / 1000),
                    phone: phone.value
                });
                
                total.value = totalProdPrice.value + deliveryPrice.value;
        
                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=testDiscount', {
                method: 'POST',
                body: postBody,
                });
                if(!response2.ok){
                    disLog.value = t("error in response");
                    return;
                }
                const textResponse = await response2.json();  // Récupérer la réponse en texte
                if (textResponse.success) {
                    var type = '';
                    if(textResponse.message === '1' && totalProdPrice.value) {
                        if(textResponse.data.type === 0) {
                            type = '%';
                            discount.value = {value: parseFloat(textResponse.data.value), type: textResponse.data.type};
                            total.value -= (totalProdPrice.value / 100) * discount.value.value;
                        } else {
                            type = 'DA';
                            discount.value = {value: parseFloat(textResponse.data.value), type: textResponse.data.type};
                            total.value -= discount.value.value;
                        }
                        discountV.value = `${textResponse.data.value} ${type}`;
                        disLog.value = `CODE : ${textResponse.message} ${textResponse.data.value} ${type}`;
                    } else {
                        if (textResponse.message === '1') {
                                if(textResponse.data.type === 0) {
                                type = '%';
                                discount.value = {value: parseFloat(textResponse.data.value), type: textResponse.data.type};
                                total.value -= (totalProdPrice.value / 100) * discount.value.value;
                            } else {
                                type = 'DA';
                                discount.value = {value: parseFloat(textResponse.data.value), type: textResponse.data.type};
                                total.value -= discount.value.value;
                            }
                            discountV.value = `${textResponse.data.value} ${type}`;
                            disLog.value = `CODE : ${textResponse.message} ${textResponse.data.value} ${type}`;
                        } else if (textResponse.message === '2') {
                            disLog.value = t('the validity period has expired');
                        } else if (textResponse.message === '3') {
                            disLog.value = t('you have used the code with your phone');
                        } else if (textResponse.message === '4') {
                            disLog.value = t('the validity of the code has expired');
                        } else if (textResponse.message === '5') {
                            disLog.value = t('invalid code');
                        } else if (textResponse.message === '6') {
                            disLog.value = t('code not available');
                        } else if (textResponse.message === '7') {
                            disLog.value = t('phone number is important');
                        } else if (textResponse.message === '8') {
                            disLog.value = t('sorry there is no promotional code');
                        }
                    }


                    



                } else {
                    if (textResponse.message === '2') {
                            disLog.value = t('the validity period has expired');
                        } else if (textResponse.message === '3') {
                            disLog.value = t('you have used the code with your phone');
                        } else if (textResponse.message === '4') {
                            disLog.value = t('the validity of the code has expired');
                        } else if (textResponse.message === '5') {
                            disLog.value = t('invalid code');
                        } else if (textResponse.message === '6') {
                            disLog.value = t('code not available');
                        } else if (textResponse.message === '7') {
                            disLog.value = t('phone number is important');
                        } else if (textResponse.message === '8') {
                            disLog.value = t('sorry there is no promotional code');
                        }
                }
            } else {
                discount.value.value = 0;
                updateTotal();
                disLog.value = t('there is no code');
            }


        }
        onMounted(() => {
            getProduct();
            getDelivery();
          });
      
        return {
            isMounted,
            test,
            ordLog,
            disLog,
            prodLog,
            testDiscount,
            productList,
            getProduct,
            setProducts,
            selectedProd,
            name,
            phone,
            qty,
            select,
            sZone,
            note,
            removeProduct,
            country,
            method,
            deliveryList,
            delLog,
            getDelivery,
            getMethods,
            index1,
            index2,
            total,
            calculateDeliveryPrice,
            updateTotal,
            
            handleZone2Change,
            handleZone1Change,
            zone1,
            zone2,
            deliveryPrice,
            lastDeliveryPrice,
            totalProdPrice,
            saveOrder,
            mZone,
            discount,
            saveLog,
            getting,
            discountV,
            getCustomer,
            totalBefor,
            clearDetail,
            addDetail,
            calculateDtails,
        };
    
    }
    }
    
    </script>