<template>
    <div v-if="isMounted" class="flex items-center justify-center w-full">
        <form @submit.prevent="saveDelivery" class="flex flex-col items-center justify-center w-full" v-if="!isUpdating || !isGetting">
            <h2 class="w-full max-w-3xl h-16 flex justify-center items-center mt-1.5 rounded-t-lg px-2.5 bg-whitly dark:bg-darkly shadow-sm">{{ t('create new delivery country methode') }}</h2>
            <div class="p-4 space-y-4 border rounded-lg min-w-[200px]">
                <h3 class="flex items-center justify-between w-full max-w-md">
                    {{ t('country name') }}
                    <svg class="mx-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M4 7L4 21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.7576 3.90865C8.45236 2.22497 5.85125 3.21144 4.55426 4.2192C4.32048 4.40085 4.20358 4.49167 4.10179 4.69967C4 4.90767 4 5.10138 4 5.4888V14.7319C4.9697 13.6342 7.87879 11.9328 11.7576 13.9086C15.224 15.6744 18.1741 14.9424 19.5697 14.1795C19.7633 14.0737 19.8601 14.0207 19.9301 13.9028C20 13.7849 20 13.6569 20 13.4009V5.87389C20 5.04538 20 4.63113 19.8027 4.48106C19.6053 4.33099 19.1436 4.459 18.2202 4.71504C16.64 5.15319 14.3423 5.22532 11.7576 3.90865Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </h3>
                <input class="w-full p-2 border rounded" type="text" v-model="country" @blur="searchDelivery(country)" :disabled="true" required/>

                <h4 class="flex items-center justify-between w-full max-w-md">
                    {{ t('administrative division name') }}
                    <svg class="mx-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M14 8H10C7.518 8 7 8.518 7 11V22H17V11C17 8.518 16.482 8 14 8Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        <path d="M11 12L13 12M11 15H13M11 18H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M21 22V8.18564C21 6.95735 21 6.3432 20.7013 5.84966C20.4026 5.35612 19.8647 5.08147 18.7889 4.53216L14.4472 2.31536C13.2868 1.72284 13 1.93166 13 3.22873V7.7035" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M3 22V13C3 12.1727 3.17267 12 4 12H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22 22H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </h4>
                <input class="w-full p-2 border rounded" type="text" v-model="city" :disabled="true" required/>

                <h4 class="flex items-center justify-between w-full max-w-md">
                    {{ t('local administrative unit name') }}
                    <svg class="mx-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M13 16.7033C13 15.7854 13 15.3265 13.2034 14.9292C13.4067 14.5319 13.7859 14.2501 14.5442 13.6866L15.0442 13.315C16.2239 12.4383 16.8138 12 17.5 12C18.1862 12 18.7761 12.4383 19.9558 13.315L20.4558 13.6866C21.2141 14.2501 21.5933 14.5319 21.7966 14.9292C22 15.3265 22 15.7854 22 16.7033V18.1782C22 19.9798 22 20.8806 21.4142 21.4403C20.8284 22 19.8856 22 18 22H13V16.7033Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        <path d="M18 12.0002V5C18 2.518 17.482 2 15 2H11C8.518 2 8 2.518 8 5V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <ellipse cx="3.5" cy="14" rx="1.5" ry="2" stroke="currentColor" stroke-width="1.5" />
                        <path d="M3.5 16V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M2 22H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M12 6H14M12 9H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M17.5 22L17.5 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </h4>
                <input class="w-full p-2 border rounded" type="text" v-model="hall" :disabled="true" required/>

                <h4 class="flex items-center justify-between w-full max-w-md">
                    {{ t('adresse name') }}
                    <svg class="mx-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M13.6177 21.367C13.1841 21.773 12.6044 22 12.0011 22C11.3978 22 10.8182 21.773 10.3845 21.367C6.41302 17.626 1.09076 13.4469 3.68627 7.37966C5.08963 4.09916 8.45834 2 12.0011 2C15.5439 2 18.9126 4.09916 20.316 7.37966C22.9082 13.4393 17.599 17.6389 13.6177 21.367Z" stroke="currentColor" stroke-width="1.5" />
                        <path d="M15.5 11C15.5 12.933 13.933 14.5 12 14.5C10.067 14.5 8.5 12.933 8.5 11C8.5 9.067 10.067 7.5 12 7.5C13.933 7.5 15.5 9.067 15.5 11Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </h4>
                <input class="w-full p-2 border rounded" type="text" v-model="adresse" :disabled="true" required/>
            </div>
            
            <div class="p-4 space-y-4 border rounded-lg" v-if="methode > 0" v-for="index in modal.length" :key="index">
                <h3>{{ index }}</h3>
                <div class="flex p-2.5 overflow-x-auto space-x-2.5">
                    <button v-if="isUps" class="flex flex-col items-center justify-around w-full min-w-[150px] max-w-xs m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="modal[index - 1].name = 'ups'; setDelivery((index - 1), modal[index - 1].name)">
                        {{ t('ups method') }}
                        <svg fill="currentColor" width="50px" height="50px" viewBox="0 0 14 14" role="img" focusable="false" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <path d="m 6.96499,1.000079 c -0.61711,0 -1.2357,0.056 -1.9384,0.1613 -1.09141,0.1641 -2.19962,0.4989 -2.90561,0.8778 l -0.14528,0.078 0,3.2204 0,3.2204 0.0546,0.3003 c 0.0639,0.3515 0.20439,0.7882 0.34088,1.0596 0.23374,0.4649 0.64146,0.9585 1.03154,1.2488 0.48862,0.3637 1.68247,0.9909 3.17356,1.6674 0.19976,0.091 0.38847,0.1653 0.41939,0.1659 0.17404,0 2.53598,-1.1668 3.23429,-1.6024 0.95597,-0.5963 1.5379,-1.4557 1.74104,-2.5713 0.0532,-0.2919 0.0533,-0.3 0.0533,-3.5008 l 0,-3.2083 -0.14528,-0.078 c -0.72134,-0.3872 -1.8616,-0.7273 -2.98459,-0.8903 C 8.19773,1.047779 7.58212,0.99807901 6.965,1.000079 Z m 2.39855,1.0921 c 0.97168,0 2.18921,0.038 2.2824,0.096 0.0199,0.012 0.0272,0.842 0.0272,3.0893 0,2.5863 -0.006,3.1157 -0.0362,3.3461 -0.13596,1.0291 -0.625,1.833 -1.4449,2.375 -0.36413,0.2407 -0.7284,0.4418 -1.47296,0.8128 -0.52775,0.263 -1.68622,0.8033 -1.72024,0.8022 -0.006,-2e-4 -0.23439,-0.1005 -0.50739,-0.223 -0.59315,-0.2662 -1.82833,-0.8767 -2.16711,-1.0712 -0.63338,-0.3637 -1.00476,-0.6582 -1.27938,-1.0144 -0.3518,-0.4564 -0.52378,-0.8369 -0.65031,-1.439 -0.0535,-0.2548 -0.0546,-0.2942 -0.0624,-2.249 l -0.008,-1.9896 0.21305,-0.1866 c 0.51461,-0.4507 1.21239,-0.9224 1.82324,-1.2326 1.16584,-0.5919 2.37713,-0.9233 3.9589,-1.0831 0.21139,-0.021 0.60225,-0.031 1.04392,-0.033 z m 0.83589,2.7755 c -0.0988,0 -0.19422,0.012 -0.28017,0.032 -0.38197,0.09 -0.68503,0.3172 -0.83726,0.6277 -0.0674,0.1375 -0.083,0.2005 -0.0919,0.3697 -0.0309,0.5884 0.18206,0.9146 0.8759,1.342 0.42821,0.2638 0.55777,0.4272 0.55931,0.7052 7.8e-4,0.1411 -0.008,0.1743 -0.0717,0.2642 -0.0996,0.141 -0.22513,0.2012 -0.44298,0.2124 -0.21501,0.011 -0.43845,-0.053 -0.6706,-0.1934 -0.0852,-0.051 -0.16102,-0.093 -0.16841,-0.093 -0.007,0 -0.0134,0.1641 -0.0134,0.3648 l 0,0.3648 0.18075,0.083 c 0.35883,0.1658 0.75921,0.2182 1.1055,0.1448 0.43844,-0.093 0.7703,-0.4043 0.88005,-0.8256 0.0461,-0.1771 0.0479,-0.5619 0.003,-0.7292 -0.0978,-0.3675 -0.31537,-0.5935 -0.94683,-0.9834 -0.35764,-0.2208 -0.47216,-0.3633 -0.47216,-0.5873 0,-0.1269 0.0387,-0.2135 0.13705,-0.307 0.21548,-0.2046 0.64342,-0.1817 0.99554,0.053 0.0796,0.053 0.15149,0.097 0.1598,0.097 0.008,0 0.0151,-0.1524 0.0151,-0.3386 l 0,-0.3386 -0.0943,-0.062 c -0.19816,-0.1312 -0.52596,-0.2069 -0.8225,-0.2024 z m -3.09928,0.027 c -0.0327,6e-4 -0.0661,0 -0.10016,0 -0.15315,0.01 -0.34629,0.031 -0.42917,0.051 -0.21552,0.052 -0.48694,0.1614 -0.55431,0.2237 l -0.0577,0.053 0,2.8422 c 0,1.5632 0.008,2.8497 0.0167,2.8589 0.009,0.01 0.19715,0.014 0.41769,0.01 l 0.40099,-0.01 0.006,-0.9096 0.006,-0.9095 0.35686,0 c 0.31362,10e-4 0.37671,-0.01 0.52068,-0.055 0.57742,-0.1995 0.94875,-0.7645 1.06071,-1.6142 0.0356,-0.2702 0.0149,-0.9529 -0.0356,-1.1753 -0.0977,-0.4302 -0.2349,-0.7038 -0.47424,-0.9458 -0.30412,-0.3075 -0.64439,-0.4371 -1.13515,-0.4283 z m -1.67376,0.042 -0.41783,0.01 -0.41782,0.01 -0.0121,1.6681 -0.0121,1.6681 -0.0661,0.049 c -0.0506,0.038 -0.11296,0.052 -0.26635,0.059 -0.17344,0.01 -0.2149,0 -0.30924,-0.047 -0.0879,-0.045 -0.12298,-0.084 -0.1816,-0.2014 l -0.0726,-0.1453 -0.0121,-1.5255 -0.0121,-1.5254 -0.41163,0 -0.41163,0 0,1.5618 c 0,1.4267 0.004,1.5753 0.0434,1.7191 0.13305,0.4825 0.40827,0.7561 0.86464,0.8596 0.22768,0.052 0.76758,0.034 1.05347,-0.034 0.11998,-0.029 0.31066,-0.098 0.42373,-0.1543 l 0.20563,-0.1023 0.006,-1.9316 0.006,-1.9316 z m 1.75164,0.5857 c 0.24943,9e-4 0.43468,0.1327 0.54812,0.3904 0.0997,0.2266 0.13297,0.4851 0.13152,1.0226 -10e-4,0.5384 -0.0232,0.7011 -0.13298,0.9926 -0.0689,0.1829 -0.27643,0.3993 -0.42488,0.443 -0.11819,0.035 -0.35748,0.035 -0.45115,0 l -0.0666,-0.024 0,-1.3673 0,-1.3673 0.0908,-0.039 c 0.0499,-0.022 0.16444,-0.044 0.25443,-0.05 0.0172,-0.001 0.0341,0 0.0507,0 z"/>
                        </svg>
                    </button>
                    <button class="flex flex-col items-center justify-around w-full min-w-[150px] max-w-xs m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="modal[index - 1].name = 'default'; setDelivery((index - 1), modal[index - 1].name)">
                        {{ t('fast method') }}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50" fill="none">
                            <path d="M8.62814 12.6736H8.16918C6.68545 12.6736 5.94358 12.6736 5.62736 12.1844C5.31114 11.6953 5.61244 11.0138 6.21504 9.65083L8.02668 5.55323C8.57457 4.314 8.84852 3.69438 9.37997 3.34719C9.91142 3 10.5859 3 11.935 3H14.0244C15.6632 3 16.4826 3 16.7916 3.53535C17.1007 4.0707 16.6942 4.78588 15.8811 6.21623L14.8092 8.10188C14.405 8.81295 14.2029 9.16849 14.2057 9.45952C14.2094 9.83775 14.4105 10.1862 14.7354 10.377C14.9854 10.5239 15.3927 10.5239 16.2074 10.5239C17.2373 10.5239 17.7523 10.5239 18.0205 10.7022C18.3689 10.9338 18.5513 11.3482 18.4874 11.7632C18.4382 12.0826 18.0918 12.4656 17.399 13.2317L11.8639 19.3523C10.7767 20.5545 10.2331 21.1556 9.86807 20.9654C9.50303 20.7751 9.67833 19.9822 10.0289 18.3962L10.7157 15.2896C10.9826 14.082 11.1161 13.4782 10.7951 13.0759C10.4741 12.6736 9.85877 12.6736 8.62814 12.6736Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button v-if="isYalidine" class="flex flex-col items-center justify-around w-full min-w-[150px] max-w-xs m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="modal[index - 1].name = 'default'; setDelivery((index - 1), 'yalidine')">
                        {{ t('yalidine method') }}
                        <div class="flex items-center justify-center p-1.5 bg-red-500 rounded-lg">
                            <img class="h-10" src="https://management.hoggari.com/yalidine.png"  alt="">
                        </div>
                    </button>
                </div>

                <h4 class="flex items-center justify-between w-full max-w-md">
                    {{ t('delivery method name') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M19.5 17.5C19.5 18.8807 18.3807 20 17 20C15.6193 20 14.5 18.8807 14.5 17.5C14.5 16.1193 15.6193 15 17 15C18.3807 15 19.5 16.1193 19.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                        <path d="M9.5 17.5C9.5 18.8807 8.38071 20 7 20C5.61929 20 4.5 18.8807 4.5 17.5C4.5 16.1193 5.61929 15 7 15C8.38071 15 9.5 16.1193 9.5 17.5Z" stroke="currentColor" stroke-width="1.5" />
                        <path d="M14.5 17.5H9.5M2 4H12C13.4142 4 14.1213 4 14.5607 4.43934C15 4.87868 15 5.58579 15 7V15.5M15.5 6.5H17.3014C18.1311 6.5 18.5459 6.5 18.8898 6.6947C19.2336 6.8894 19.4471 7.2451 19.8739 7.95651L21.5725 10.7875C21.7849 11.1415 21.8911 11.3186 21.9456 11.5151C22 11.7116 22 11.918 22 12.331V15C22 15.9346 22 16.4019 21.799 16.75C21.6674 16.978 21.478 17.1674 21.25 17.299C20.9019 17.5 20.4346 17.5 19.5 17.5M2 13V15C2 15.9346 2 16.4019 2.20096 16.75C2.33261 16.978 2.52197 17.1674 2.75 17.299C3.09808 17.5 3.56538 17.5 4.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M2 7H8M2 10H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </h4>
                <input class="w-full p-2 border rounded" type="text" required v-model="modal[index - 1].name" @blur="setDelivery((index - 1), modal[index - 1].name)"/>

                <button v-if="modal[index - 1].more === false" class="flex flex-row items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="modal[index - 1].more = !modal[index - 1].more">
                    <h3>{{ t('edit - show more') }}</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M18 9.00005C18 9.00005 13.5811 15 12 15C10.4188 15 6 9 6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <button v-else class="flex flex-row items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="modal[index - 1].more = !modal[index - 1].more">
                    <h3>{{ t('close - show less') }}</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M17.9998 15C17.9998 15 13.5809 9.00001 11.9998 9C10.4187 8.99999 5.99985 15 5.99985 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div class="flex flex-col items-center justify-center w-full max-w-3xl p-2.5 my-1.5 overflow-hidden transition-all duration-300 ease-in-out rounded-md" v-if="modal[index - 1].more === true && modal[index - 1].options.length > 0" v-for="i in modal[index - 1].options.length" :key="index">
                    <div class="flex items-center justify-between w-11/12 h-16 bg-whity dark:bg-darky">
                        <div v-if="modal[index - 1].options[i - 1].more" class="min-w-[20px] cursor-pointer mx-5" @click="modal[index - 1].options[i - 1].more = !modal[index - 1].options[i - 1].more">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M17.9998 15C17.9998 15 13.5809 9.00001 11.9998 9C10.4187 8.99999 5.99985 15 5.99985 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div v-else class="min-w-[20px] cursor-pointer mx-5" @click="modal[index - 1].options[i - 1].more = !modal[index - 1].options[i - 1].more">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M18 9.00005C18 9.00005 13.5811 15 12 15C10.4188 15 6 9 6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <button type="button" class="w-full mx-5 text-lg font-bold cursor-pointer" @click="modal[index - 1].options[i - 1].more = !modal[index - 1].options[i - 1].more">
                            {{modal[index - 1].options[i - 1].cityName}} {{ i }}
                        </button>

                        <Toggle :toggle="modal[index - 1].options[i - 1].isActive" @toggle="modal[index - 1].options[i - 1].isActive = !modal[index - 1].options[i - 1].isActive"/>

                        <button class="min-w-[20px] cursor-pointer mx-5" @click="removeOptions(index, i )" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#ff5555" fill="none">
                                <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                    <div v-if="modal[index - 1].options[i - 1].more" class="flex flex-col items-center justify-center w-full max-w-3xl p-2.5 my-1.5 overflow-hidden transition-all duration-300 ease-in-out rounded-md">
                        <h3>{{ i }}</h3>
                        <h4 class="flex items-center justify-between w-full max-w-md">{{ city }}
                            <svg class="mx-1.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M13 16.7033C13 15.7854 13 15.3265 13.2034 14.9292C13.4067 14.5319 13.7859 14.2501 14.5442 13.6866L15.0442 13.315C16.2239 12.4383 16.8138 12 17.5 12C18.1862 12 18.7761 12.4383 19.9558 13.315L20.4558 13.6866C21.2141 14.2501 21.5933 14.5319 21.7966 14.9292C22 15.3265 22 15.7854 22 16.7033V18.1782C22 19.9798 22 20.8806 21.4142 21.4403C20.8284 22 19.8856 22 18 22H13V16.7033Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M18 12.0002V5C18 2.518 17.482 2 15 2H11C8.518 2 8 2.518 8 5V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <ellipse cx="3.5" cy="14" rx="1.5" ry="2" stroke="currentColor" stroke-width="1.5" />
                                <path d="M3.5 16V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M2 22H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M12 6H14M12 9H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M17.5 22L17.5 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </h4>
                        <input class="w-full p-2 border rounded" type="text" required v-model="modal[index - 1].options[i - 1].cityName"/>
                        <h4 class="flex items-center justify-between w-full max-w-md">{{ t('home delivery price') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M9 22L9.00192 17.9976C9.00236 17.067 9.00258 16.6017 9.15462 16.2347C9.35774 15.7443 9.74746 15.3547 10.2379 15.1519C10.6051 15 11.0704 15 12.001 15V15C12.9319 15 13.3974 15 13.7647 15.152C14.2553 15.355 14.645 15.7447 14.848 16.2353C15 16.6026 15 17.0681 15 17.999V22" stroke="currentColor" stroke-width="1.5" />
                                <path d="M7.08848 4.76243L6.08847 5.54298C4.57181 6.72681 3.81348 7.31873 3.40674 8.15333C3 8.98792 3 9.95205 3 11.8803V13.9715C3 17.7562 3 19.6485 4.17157 20.8243C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8243C21 19.6485 21 17.7562 21 13.9715V11.8803C21 9.95205 21 8.98792 20.5933 8.15333C20.1865 7.31873 19.4282 6.72681 17.9115 5.54298L16.9115 4.76243C14.5521 2.92081 13.3724 2 12 2C10.6276 2 9.44787 2.92081 7.08848 4.76243Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                            </svg>
                        </h4>
                        <input class="w-full p-2 border rounded" type="text" required v-model="modal[index - 1].options[i - 1].homePrice"/>
                        <h4 class="flex items-center justify-between w-full max-w-md">{{ t('stop-desk delivery price') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                                <path d="M22 12H2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M17 12V8C17 7.17267 17.1727 7 18 7H19C19.8273 7 20 7.17267 20 8V12" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M20 17H16C14.1144 17 13.1716 17 12.5858 16.4142C12 15.8284 12 14.8856 12 13V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M4 12V22M20 12V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M3 6V5C3 3.58579 3 2.87868 3.43934 2.43934C3.87868 2 4.58579 2 6 2H10C11.4142 2 12.1213 2 12.5607 2.43934C13 2.87868 13 3.58579 13 5V6C13 7.41421 13 8.12132 12.5607 8.56066C12.1213 9 11.4142 9 10 9H6C4.58579 9 3.87868 9 3.43934 8.56066C3 8.12132 3 7.41421 3 6Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9.5 9L10 12M6.5 9L6 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </h4>
                        <input class="w-full p-2 border rounded" type="text" required v-model="modal[index - 1].options[i - 1].deskPrice"/>
                    </div>
                </div>

                <button v-if="modal[index - 1].more" class="flex items-center justify-center w-11/12 h-16 cursor-pointer bg-whity dark:bg-darky" type="button" @click="addOptions(index)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M12 8V16M16 12L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </button>
                <button class="flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" @click="removeMethode(index)" type="button">
                    <h2>{{ t('remove methode') }} {{index}}</h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </button>
            </div>
            
            <div class="p-4 space-y-4 border rounded-lg">
                <button class="flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" type="button" @click="addMethode">
                    {{ t('add methode') }}
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M12 8V16M16 12L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12Z" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                </button>
                
                <button class="flex items-center justify-around w-full min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer border border-rangy bg-whity rounded-md shadow-lg text-darkly dark:bg-darky dark:text-whitly" type="submit">
                    {{ t('save') }}
                    <svg class="text-hoggari" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                        <path d="M2 12.5C2 11.3954 2.89543 10.5 4 10.5C5.65685 10.5 7 11.8431 7 13.5V17.5C7 19.1569 5.65685 20.5 4 20.5C2.89543 20.5 2 19.6046 2 18.5V12.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.4787 7.80626L15.2124 8.66634C14.9942 9.37111 14.8851 9.72349 14.969 10.0018C15.0369 10.2269 15.1859 10.421 15.389 10.5487C15.64 10.7065 16.0197 10.7065 16.7791 10.7065H17.1831C19.7532 10.7065 21.0382 10.7065 21.6452 11.4673C21.7145 11.5542 21.7762 11.6467 21.8296 11.7437C22.2965 12.5921 21.7657 13.7351 20.704 16.0211C19.7297 18.1189 19.2425 19.1678 18.338 19.7852C18.2505 19.8449 18.1605 19.9013 18.0683 19.9541C17.116 20.5 15.9362 20.5 13.5764 20.5H13.0646C10.2057 20.5 8.77628 20.5 7.88814 19.6395C7 18.7789 7 17.3939 7 14.6239V13.6503C7 12.1946 7 11.4668 7.25834 10.8006C7.51668 10.1344 8.01135 9.58664 9.00069 8.49112L13.0921 3.96056C13.1947 3.84694 13.246 3.79012 13.2913 3.75075C13.7135 3.38328 14.3652 3.42464 14.7344 3.84235C14.774 3.8871 14.8172 3.94991 14.9036 4.07554C15.0388 4.27205 15.1064 4.37031 15.1654 4.46765C15.6928 5.33913 15.8524 6.37436 15.6108 7.35715C15.5838 7.46692 15.5488 7.5801 15.4787 7.80626Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <h3>{{ deLog }}</h3>
            </div>
        </form>
        <div v-else-if="isUpdating || isGetting" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <Loader class="w-20 h-20"/>
        </div>
    </div>
</template>

<script>
import { useLang } from '~/composables/useLang';
import Loader from '../components/loader.vue';
import Toggle from '../components/toggle.vue';


export default {
    name: 'Products',
    setup() {
        const { t } = useLang();
        return { t };
    },
    data() {
    return {
        country: 'algeria',
        methode: 0,
        option: 0,
        isMounted: false,
        modal: [],
        city: 'الولاية',
        hall: 'البلدية',
        adresse: 'العنوان',
        deLog: '',
        isUpdating: false,
        isGetting: true,
        delivery: {},
        isUps: false,
        isYalidine: true,
        wilayas: [
            {wilaya_id: 1, wilaya_name: 'Adrar', homePrice: 1600, deskPrice: 800},
            {wilaya_id: 2, wilaya_name: 'Chlef', homePrice: 900, deskPrice: 450},
            {wilaya_id: 3, wilaya_name: 'Laghouat', homePrice: 1200, deskPrice: 600},
            {wilaya_id: 4, wilaya_name: 'Oum El Bouaghi', homePrice: 900, deskPrice: 350},
            {wilaya_id: 5, wilaya_name: 'Batna', homePrice: 850, deskPrice: 400},
            {wilaya_id: 6, wilaya_name: 'Béjaïa', homePrice: 850, deskPrice: 400},
            {wilaya_id: 7, wilaya_name: 'Biskra', homePrice: 850, deskPrice: 350},
            {wilaya_id: 8, wilaya_name: 'Béchar', homePrice: 1400, deskPrice: 800},
            {wilaya_id: 9, wilaya_name: 'Blida', homePrice: 800, deskPrice: 350},
            {wilaya_id: 10, wilaya_name: 'Bouira', homePrice: 850, deskPrice: 400},
            {wilaya_id: 11, wilaya_name: 'Tamanrasset', homePrice: 1600, deskPrice: 1000},
            {wilaya_id: 12, wilaya_name: 'Tébessa', homePrice: 800, deskPrice: 600},
            {wilaya_id: 13, wilaya_name: 'Tlemcen', homePrice: 900, deskPrice: 350},
            {wilaya_id: 14, wilaya_name: 'Tiaret', homePrice: 950, deskPrice: 400},
            {wilaya_id: 15, wilaya_name: 'Tizi Ouzou', homePrice: 850, deskPrice: 400},
            {wilaya_id: 16, wilaya_name: 'Alger', homePrice: 750, deskPrice: 350},
            {wilaya_id: 17, wilaya_name: 'Djelfa', homePrice: 1200, deskPrice: 600},
            {wilaya_id: 18, wilaya_name: 'Jijel', homePrice: 850, deskPrice: 400},
            {wilaya_id: 19, wilaya_name: 'Sétif', homePrice: 850, deskPrice: 350},
            {wilaya_id: 20, wilaya_name: 'Saïda', homePrice: 1000, deskPrice: 400},
            {wilaya_id: 21, wilaya_name: 'Skikda', homePrice: 850, deskPrice: 400},
            {wilaya_id: 22, wilaya_name: 'Sidi Bel Abbès', homePrice: 900, deskPrice: 400},
            {wilaya_id: 23, wilaya_name: 'Annaba', homePrice: 850, deskPrice: 350},
            {wilaya_id: 24, wilaya_name: 'Guelma', homePrice: 850, deskPrice: 400},
            {wilaya_id: 25, wilaya_name: 'Constantine', homePrice: 850, deskPrice: 400},
            {wilaya_id: 26, wilaya_name: 'Médéa', homePrice: 850, deskPrice: 400},
            {wilaya_id: 27, wilaya_name: 'Mostaganem', homePrice: 900, deskPrice: 400},
            {wilaya_id: 28, wilaya_name: "M'Sila", homePrice: 900, deskPrice: 350},
            {wilaya_id: 29, wilaya_name: 'Mascara', homePrice: 950, deskPrice: 400},
            {wilaya_id: 30, wilaya_name: 'Ouargla', homePrice: 1200, deskPrice: 600},
            {wilaya_id: 31, wilaya_name: 'Oran', homePrice: 900, deskPrice: 350},
            {wilaya_id: 32, wilaya_name: 'El Bayadh', homePrice: 1400, deskPrice: 600},
            {wilaya_id: 33, wilaya_name: 'Illizi', homePrice: 1400, deskPrice: 600},
            {wilaya_id: 34, wilaya_name: 'Bordj Bou Arreridj', homePrice: 850, deskPrice: 400},
            {wilaya_id: 35, wilaya_name: 'Boumerdès', homePrice: 650, deskPrice: 400},
            {wilaya_id: 36, wilaya_name: 'El Tarf', homePrice: 850, deskPrice: 400},
            {wilaya_id: 37, wilaya_name: 'Tindouf', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 38, wilaya_name: 'Tissemsilt', homePrice: 950, deskPrice: 400},
            {wilaya_id: 39, wilaya_name: 'El Oued', homePrice: 1000, deskPrice: 600},
            {wilaya_id: 40, wilaya_name: 'Khenchela', homePrice: 800, deskPrice: 350},
            {wilaya_id: 41, wilaya_name: 'Souk Ahras', homePrice: 850, deskPrice: 400},
            {wilaya_id: 42, wilaya_name: 'Tipaza', homePrice: 850, deskPrice: 350},
            {wilaya_id: 43, wilaya_name: 'Mila', homePrice: 800, deskPrice: 350},
            {wilaya_id: 44, wilaya_name: 'Aïn Defla', homePrice: 900, deskPrice: 400},
            {wilaya_id: 45, wilaya_name: 'Naâma', homePrice: 1400, deskPrice: 800},
            {wilaya_id: 46, wilaya_name: 'Aïn Témouchent', homePrice: 950, deskPrice: 400},
            {wilaya_id: 47, wilaya_name: 'Ghardaïa', homePrice: 1200, deskPrice: 600},
            {wilaya_id: 48, wilaya_name: 'Relizane', homePrice: 950, deskPrice: 400},
            {wilaya_id: 49, wilaya_name: 'Timimoun', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 50, wilaya_name: 'Bordj Badji Mokhtar', homePrice: null, deskPrice: null},
            {wilaya_id: 51, wilaya_name: 'Ouled Djellal', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 52, wilaya_name: 'Béni Abbès', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 53, wilaya_name: "In Salah", homePrice: 1200, deskPrice: 800},
            {wilaya_id: 54, wilaya_name: "In Guezzam", homePrice: null, deskPrice: null},
            {wilaya_id: 55, wilaya_name: 'Touggourt', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 56, wilaya_name: 'Djanet', homePrice: 1200, deskPrice: 800},
            {wilaya_id: 57, wilaya_name: "El M'Ghair", homePrice: 1200, deskPrice: 800},
            {wilaya_id: 58, wilaya_name: 'El Meniaa', homePrice: 1200, deskPrice: 800},
        ]

    };
  },

  async mounted() {
    
    this.searchDelivery(this.country);
    this.getUps();
    this.isMounted = true;
    
  },
        
  methods: {

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
          console.log('textResponse: ', textResponse.data.key);
          if (textResponse.data.work == 1) {
            this.isUps = true;
          } else {
            this.isUps = false;
          }
          
        } else {
          console.log('textResponse: ', textResponse.message);
        }
    },

    async setDelivery(index, name) {
        const found = this.modal.filter(item => item.name === name);
            if (name === 'ups') {
            const response1 = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsWilaya', {
            method: 'GET',
            });
            if(!response1.ok){
                return;
            }

            const textResponse = await response1.json(); // Récupérer la réponse en JSON
            console.log('textResponse ', textResponse);

            const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsFees', {
            method: 'GET',
            });
            if(!response2.ok){
                return;
            }

            const textResponse1 = await response2.json(); // Récupérer la réponse en JSON
            
            if (textResponse && textResponse1){
                this.modal[index].options = [];
                for (let i = 0; i < this.wilayas.length; i++) {
                    const expectedId = i + 1;

                    // Cherche dans textResponse une wilaya avec wilaya_id == expectedId
                    const found = textResponse.find(w => w.wilaya_id === expectedId);
                    
                    if (found && textResponse1.livraison[i]) {
                        
                        // Trouvé dans la réponse, on utilise les tarifs associés
                        this.modal[index].options.push({ 
                            cityName: found.wilaya_name,
                            homePrice: textResponse1.livraison[i].tarif,
                            deskPrice: textResponse1.livraison[i].tarif_stopdesk,
                            isActive: true,
                            more: false,
                        });
                    } else {
                        // Non trouvé, on crée une entrée inactive
                        this.modal[index].options.push({ 
                            cityName: this.wilayas[i].wilaya_name,
                            homePrice: 0,
                            deskPrice: 0,
                            isActive: false,
                            more: false,
                        });
                    }
                    
                }
                this.modal[index].more = true;
            }
            
        } else if (name === 'default') {
            this.modal[index].options = [];
                for (var i = 0; i < this.wilayas.length; i++) {
                    this.modal[index].options.push({ 
                        cityName: this.wilayas[i].wilaya_name, 
                        homePrice: 0,
                        deskPrice: 0,
                        isActive: true,
                        more: false,
                    });
                }
                this.modal[index].more = true;
        } else if (name === 'yalidine') {
            const response1 = await fetch('https://management.hoggari.com/backend/api.php?action=getYalidineWilaya', {
            method: 'GET',
            });
            if(!response1.ok){
                return;
            }

            const textResponse = await response1.json(); // Récupérer la réponse en JSON
            console.log('textResponse: ', textResponse);
            
            if (textResponse){
                this.modal[index].options = [];
                for (var i = 0; i < textResponse.data.data.length; i++) {
                    const wilaya = textResponse.data.data;
                    
                    if(wilaya[i].is_deliverable) {
                        this.modal[index].options.push({ 
                        cityName: wilaya[i].name, 
                        homePrice: this.wilayas[i].homePrice,
                        deskPrice: this.wilayas[i].deskPrice,
                        isActive: true,
                        more: false,
                    });
                    } else{
                        this.modal[index].options.push({ 
                        cityName: wilaya[i].name, 
                        homePrice: 0,
                        deskPrice: 0,
                        isActive: false,
                        more: false,
                    });
                    }

                }
                this.modal[index].more = true;
            }
        }

        if (found.length > 1) {
            this.modal[index].name = this.modal[index].name + (index + 1);
        }


    },

    addMethode() {
        this.modal.push({ 
            name: "", 
            options: [],
            more: false,
        });
        this.methode++;

        
    },

    addOptions(index) {
        this.modal[index - 1].options.push({ 
            cityName: "", 
            homePrice: 0,
            deskPrice: 0,
            isActive: true,
            more: false,
        });
        this.option++;

    },

    async removeMethode(index) {
        const rIndex = index - 1;
        if (rIndex >= 0 && rIndex < this.modal.length) {
            if(this.delivery[0].options[rIndex]) {
                const id = this.delivery[0].options[rIndex].id;
                console.log("id: ", id);
                const deleteOption = JSON.stringify({
                    id: [id],
                });

                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=deleteDeliveryOptions', {
                method: 'POST',
                body: deleteOption,
                });
                if(!response2.ok){
                    console.error("error in response");
                    return;
                }
                const textResponse = await response2.json();  // Récupérer la réponse en texte
                if (textResponse.success) {
                    console.log(textResponse.message);
                    this.modal.splice(rIndex, 1);
                    this.methode--;
                } else {
                    console.error(textResponse.message);
                }
            } else {
                this.modal.splice(rIndex, 1);
                this.methode--;
            }

            //isUpdating.value = false;


            
        } else {
            console.error('Invalid index:', rIndex);
        }
    },

    removeOptions(index, i) {
        const rIndex = index - 1;
        const rI = i - 1;

        if (rIndex >= 0 && rIndex < this.modal.length) {
            const options = this.modal[rIndex].options;

            if (rI >= 0 && rI < options.length) {
                options.splice(rI, 1); // Supprimer l'option à l'index donné
                this.option--;
            } else {
                console.error('Invalid option index:', rI);
            }
        } else {
            console.error('Invalid modal index:', rIndex);
        }
    },

    async getDelivery() {
        this.isGetting = true;

            const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getDelivery', {
            method: 'GET',
            });
            if(!response2.ok){
                this.isGetting = false;
                return;
            }
            const textResponse = await response2.json(); // Récupérer la réponse en JSON

            if (textResponse.success) {

                this.delivery = textResponse.data;

                this.isGetting = false;
            }else {
                console.log('data: ', textResponse.message);
                this.isGetting = false;
            }
    },

    async searchDelivery (name) {
        await this.getDelivery();
        
        if(name) {
                const found = this.delivery.find(item => item.name === name);
            if (found) {
                this.methode = 0;
                this.option = 0;
                this.country = name;
                this.city = found.city_name;
                this.hall = found.hall_name;
                this.adresse = found.adress_name;

                
                const options = found.options;
                for(var ii = 0; ii < options.length; ii++) {

                    this.modal.push({ 
                        name: options[ii].name, 
                        options: [],
                        more: false,
                    });

                    this.methode++;

                    for(var iii = 0; iii < options[ii].price.length; iii++) {

                        var isActive;
                        if(options[ii].price[iii].isActive == 1) {
                            isActive = true;
                        } else {
                            isActive = false;
                        }
                        this.modal[ii].options.push({ 
                            cityName: options[ii].price[iii].name, 
                            homePrice: options[ii].price[iii].desk_price, 
                            deskPrice: options[ii].price[iii].home_price,
                            isActive: isActive,
                            more: false,
                        });
                        this.option++;

                    }
                }
                
                //return;
            }
        
        } else {
            console.log('no name: ', name);
        }
        
        
        
    },

    async saveDelivery() {
        this.isUpdating = true;
        const postDiscount = JSON.stringify({
            country_name: this.country,
            administartive_city_name: this.city,
            administartive_hall_name: this.hall,
            administartive_adresse_name: this.adresse,
            methods: this.modal,
            });

            this.deLog = this.t("waiting response...");
            const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=postDelivery', {
            method: 'POST',
            body: postDiscount,
            });
            if(!response2.ok){
                this.deLog = this.t("error in response");
                this.isUpdating = false;
                return;
            }
            this.deLog = this.t("waiting data...");
            const textResponse = await response2.json();  // Récupérer la réponse en texte
            if (textResponse.success) {
                this.deLog = textResponse.message;
                this.methode = 0;
                this.option = 0;
                this.modal = [];
                this.delivery = {};
                this.searchDelivery(this.country);
            } else {
                this.deLog = textResponse.error;
                this.isUpdating = false;
            }
    }

  }


        

}


</script>