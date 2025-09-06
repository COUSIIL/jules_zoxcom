<template>
  <div class="storeMenu">
    <div v-if="loading">Loading...</div>

    <div v-if="!loading" class="spaceBox">
      <div class="alignBox">
        <img :src="faviconUrl" alt="Logo" class="logo" v-if="faviconUrl" />
        <div class="stackStart">
          <h4>{{ title }}</h4>
          <p>{{ webUrl }}</p>
        </div>
      </div>

      <Linker :link="`/store/${title}`" text="Manage" :svg="icons['settings']" />
        
    </div>

    <div v-if="!loading" class="seo-score-container">
      <h2>SEO Score</h2>
      <div class="progress-bar">
        <div class="progress" ref="progress">{{ progressText }}</div>
      </div>
    </div>

    <button class="alignDrop" @click="drop = !drop">
      <div v-if="drop">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#000000" fill="none">
          <path d="M17.9998 15C17.9998 15 13.5809 9.00001 11.9998 9C10.4187 8.99999 5.99985 15 5.99985 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        
      </div>

      <div v-else>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="#000000" fill="none">
          <path d="M18 9.00005C18 9.00005 13.5811 15 12 15C10.4188 15 6 9 6 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>


      
    </button>

    <div v-if="!loading && drop" class="audit-results">
      <div class="audit-card">
        <h3>SEO Ready</h3>
        <p :style="{ color: seoReady ? 'green' : 'red' }">
          {{ seoReady ? 'Yes, your website is SEO-ready.' : 'No, improvements are needed for SEO readiness.' }}
        </p>
      </div>
    
      <div class="audit-card">
        <h3>Facebook Pixel</h3>
        <p :style="{ color: facebookPixelInstalled ? 'green' : 'red' }">
          {{ facebookPixelInstalled ? 'Facebook Pixel is installed.' : 'Facebook Pixel is not detected.' }}
        </p>
      </div>
    
      <div class="audit-card">
        <h3>Google Analytics</h3>
        <p :style="{ color: googleAnalyticsInstalled ? 'green' : 'red' }">
          {{ googleAnalyticsInstalled ? 'Google Analytics is active.' : 'Google Analytics is missing.' }}
        </p>
      </div>
    
      <div class="audit-card">
        <h3>Google Ads</h3>
        <p :style="{ color: googleAdsInstalled ? 'green' : 'red' }">
          {{ googleAdsInstalled ? 'Google Ads tag is detected.' : 'Google Ads tag not found.' }}
        </p>
      </div>
    
      <div class="audit-card">
        <h3>Structured Data</h3>
        <p :style="{ color: structuredDataPresent ? 'green' : 'red' }">
          {{ structuredDataPresent ? 'Structured data (Schema.org) is present.' : 'No structured data detected.' }}
        </p>
      </div>
    
      <div class="audit-card">
        <h3>Speed Optimization</h3>
        <p :style="{ color: fastLoadingPractices ? 'green' : 'red' }">
          {{ fastLoadingPractices ? 'Async/defer practices are applied.' : 'No async/defer found, speed could improve.' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import icons from '~/public/icons.json'

import Linker from '../elements/bloc/classBtn.vue'

const {t} = useLang()



const title = ref('dinarz');
const description = ref('');
const faviconUrl = ref('');
const loading = ref(true);
const webUrl = ref("https://dinarz.hoggari.com");
const seoReady = ref(false);
const facebookPixelInstalled = ref(false);
const googleAnalyticsInstalled = ref(false);
const googleAdsInstalled = ref(false);
const structuredDataPresent = ref(false);
const fastLoadingPractices = ref(false);
const progress = ref(null); // ref du DOM
const progressText = ref('0%');
const drop = ref(false);

const manageSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
      <path d="M21.3175 7.14139L20.8239 6.28479C20.4506 5.63696 20.264 5.31305 19.9464 5.18388C19.6288 5.05472 19.2696 5.15664 18.5513 5.36048L17.3311 5.70418C16.8725 5.80994 16.3913 5.74994 15.9726 5.53479L15.6357 5.34042C15.2766 5.11043 15.0004 4.77133 14.8475 4.37274L14.5136 3.37536C14.294 2.71534 14.1842 2.38533 13.9228 2.19657C13.6615 2.00781 13.3143 2.00781 12.6199 2.00781H11.5051C10.8108 2.00781 10.4636 2.00781 10.2022 2.19657C9.94085 2.38533 9.83106 2.71534 9.61149 3.37536L9.27753 4.37274C9.12465 4.77133 8.84845 5.11043 8.48937 5.34042L8.15249 5.53479C7.73374 5.74994 7.25259 5.80994 6.79398 5.70418L5.57375 5.36048C4.85541 5.15664 4.49625 5.05472 4.17867 5.18388C3.86109 5.31305 3.67445 5.63696 3.30115 6.28479L2.80757 7.14139C2.45766 7.74864 2.2827 8.05227 2.31666 8.37549C2.35061 8.69871 2.58483 8.95918 3.05326 9.48012L4.0843 10.6328C4.3363 10.9518 4.51521 11.5078 4.51521 12.0077C4.51521 12.5078 4.33636 13.0636 4.08433 13.3827L3.05326 14.5354C2.58483 15.0564 2.35062 15.3168 2.31666 15.6401C2.2827 15.9633 2.45766 16.2669 2.80757 16.8741L3.30114 17.7307C3.67443 18.3785 3.86109 18.7025 4.17867 18.8316C4.49625 18.9608 4.85542 18.8589 5.57377 18.655L6.79394 18.3113C7.25263 18.2055 7.73387 18.2656 8.15267 18.4808L8.4895 18.6752C8.84851 18.9052 9.12464 19.2442 9.2775 19.6428L9.61149 20.6403C9.83106 21.3003 9.94085 21.6303 10.2022 21.8191C10.4636 22.0078 10.8108 22.0078 11.5051 22.0078H12.6199C13.3143 22.0078 13.6615 22.0078 13.9228 21.8191C14.1842 21.6303 14.294 21.3003 14.5136 20.6403L14.8476 19.6428C15.0004 19.2442 15.2765 18.9052 15.6356 18.6752L15.9724 18.4808C16.3912 18.2656 16.8724 18.2055 17.3311 18.3113L18.5513 18.655C19.2696 18.8589 19.6288 18.9608 19.9464 18.8316C20.264 18.7025 20.4506 18.3785 20.8239 17.7307L21.3175 16.8741C21.6674 16.2669 21.8423 15.9633 21.8084 15.6401C21.7744 15.3168 21.5402 15.0564 21.0718 14.5354L20.0407 13.3827C19.7887 13.0636 19.6098 12.5078 19.6098 12.0077C19.6098 11.5078 19.7888 10.9518 20.0407 10.6328L21.0718 9.48012C21.5402 8.95918 21.7744 8.69871 21.8084 8.37549C21.8423 8.05227 21.6674 7.74864 21.3175 7.14139Z" stroke="#000000" stroke-width="1.5" stroke-linecap="round"></path>
      <path d="M15.5195 12C15.5195 13.933 13.9525 15.5 12.0195 15.5C10.0865 15.5 8.51953 13.933 8.51953 12C8.51953 10.067 10.0865 8.5 12.0195 8.5C13.9525 8.5 15.5195 10.067 15.5195 12Z" stroke="#000000" stroke-width="1.5"></path>
  </svg>`)

const updateSeoScore = (score) => {
  if (progress.value) {
    progress.value.style.width = score + '%';
    progressText.value = score + '%';
  } else {
    console.error('Élément progress non trouvé');
  }
};

const fetchSiteInfo = async () => {
  try {
    const proxyUrl = "https://api.allorigins.win/get?url=" + encodeURIComponent(webUrl.value);
    const response = await fetch(proxyUrl);
    const data = await response.json();

    const parser = new DOMParser();
    const doc = parser.parseFromString(data.contents, "text/html");

    title.value = doc.querySelector('title')?.innerText || 'No title';
    description.value = doc.querySelector('meta[name="description"]')?.getAttribute('content') || 'No description';

    const favicon = doc.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
    if (favicon) {
      const href = favicon.getAttribute('href');
      if (href.startsWith('http')) {
        faviconUrl.value = href;
      } else {
        faviconUrl.value = webUrl.value + href;
      }
    }

    seoReady.value = !!(
      doc.querySelector('meta[name="description"]') &&
      doc.querySelector('link[rel="canonical"]')
    );

    const scripts = Array.from(doc.querySelectorAll('script'));

    facebookPixelInstalled.value = scripts.some(script => script.innerText.includes('fbq('));
    googleAnalyticsInstalled.value = scripts.some(script =>
      script.innerText.includes('gtag(') || script.innerText.includes('ga(')
    );
    googleAdsInstalled.value = scripts.some(script =>
      script.src.includes('adsbygoogle.js')
    );
    structuredDataPresent.value = doc.querySelector('script[type="application/ld+json"]') !== null;

    const asyncOrDeferScripts = scripts.filter(script => script.hasAttribute('async') || script.hasAttribute('defer'));
    fastLoadingPractices.value = asyncOrDeferScripts.length > scripts.length / 2;
    
  } catch (error) {
    console.error("Error fetching site info:", error);
  } finally {
    loading.value = false; // ===> Le DOM est maintenant prêt avec progress visible !
    nextTick(() => {
      const score = calculateSeoScore();
      updateSeoScore(score);
    });
  }
};

const calculateSeoScore = () => {
  let score = 0;
  if (seoReady.value) score += 20;
  if (facebookPixelInstalled.value) score += 15;
  if (googleAnalyticsInstalled.value) score += 15;
  if (googleAdsInstalled.value) score += 15;
  if (structuredDataPresent.value) score += 20;
  if (fastLoadingPractices.value) score += 15;

  return score; // sur 100
};



onMounted(() => {
  fetchSiteInfo();
});



</script>
  
  
  
<style scoped>
  .storeMenu {
    width: 90%;
    margin: 10px;
    border-radius: 8px;
    padding-block: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: var(--color-whitly);
    box-shadow: 0 4px 8px #3b3b3b20;
    text-align: center;
  }
  .dark .storeMenu {
    background-color: var(--color-darkly);
  }

  .spaceBox{
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .alignBox{
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .alignDrop{
    width: 100%;
    height: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
  }
  .stackStart{
    width: 150px; 
    max-width: 150px;
    display: flex; 
    justify-content: center; 
    align-items: start; 
    flex-direction: column;
  }

  .logo {
    width: 30px;
    height: 30px;
    object-fit: contain;
    border-radius: 50%;
    margin: 10px;
  }
  h2 {
    font-size: 1.5rem;
    margin: 0;
  }
  p {
    font-size: 0.7rem;
    color: gray;
  }

  .audit-results {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 3fr));
    gap: 20px;
    padding: 5px;
  }
  
  .audit-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.3s ease;
  }
  
  .audit-card:hover {
    transform: translateY(-4px);
  }
  
  .audit-card h3 {
    margin-bottom: 10px;
    font-size: 2.5vh;
    color: #222;
  }
  
  .audit-card p {
    font-size: 2vh;
    color: #444;
  }
  
  

  .seo-score-container {
    width: 100%;
    max-width: 400px;
    margin: 20px auto;
    text-align: center;
  }
  
  .progress-bar {
    width: 100%;
    height: 30px;
    background-color: #e0e0e0;
    border-radius: 15px;
    overflow: hidden;
    margin-top: 10px;
  }
  
  .progress {
    height: 100%;
    background: linear-gradient(90deg, #00c853, #b2ff59);
    color: #fff;
    text-align: center;
    line-height: 30px;
    font-weight: bold;
    transition: width 0.5s ease;
  }

  
  
  </style>
  