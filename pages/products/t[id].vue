<template>

  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
  <Explorer v-if="isExplorer" :show="isExplorer" @confirm="getExplorerImg" @cancel="isExplorer = false" />
  <Explorer v-if="isExplorer2" :show="isExplorer2" @confirm="getExplorerCatalog" @cancel="isExplorer2 = false" />
  
  <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'left', alignItems: 'center'}" v-if="isMounted">
    
    <ProductNavBar @getClick="changePage"/>


    <!--
    -- finalise ProductPart inspire toi de la page products/[id].vue:
    -->
    <ProductPart v-if="currentPage === 1" @openExplorProdImg="getExplorerImg" @openExplorCataImg="getExplorerCatalog" :prodImage="img" @isMessage="openMessage" @message="ridMessage" :imageRef="imageRef" />

    <!--
    -- créer et finalise ProductModels inspire toi de la page products/[id].vue:
    -- ProductModels doit: 
    - récuperer et pouvoir modifier les model et items de chaque produit
    - avec une radio activator de is model active "minimum un model doit etre active"
    - avec des input: nom "important", image du model "important", brand, SKU et Référence
    - avec une radio activator de is variable price
    - if is variable varient = false :
      - affiche les input :
        - buying price
        - selling price "important"
        - promo price
        - qty "important" doit avoir une option infinie
        - les input: SKU et référence"créer des code unique pour chaque model"
    - avec une radio activator de is varient active "minimum un varient doit etre active"
    - avec un input : image du varient selectionable depuis les images du catalogue produit "important"
    - choix de couleur et taille + autre(button) "user doit pouvoir entrer un autre choix ex: label: puissance, valeur: String" avec chaque détail son activator
    - if is variable varient = true :
      - affiche les input :
        - buying price
        - selling price "important"
        - promo price
        - qty "important" doit avoir une option infinie
        - les input: SKU et référence"créer des code unique pour chaque varient"
    - button add varient
    - car avec une liste de tous les varient"option de supprimer les models"
    - button add model
    
    <ProductModels />

    -- créer et finalise ProductParameter:
    -- ProductParameter doit avoir:
    - radio activator de is rating and comment active
    - radio activator de is live chat active
    - radio activator de is cuntdown offer active
    - radio activator de is gambling game active
    - radio activator de is ai helper active
    - radio activator de is ASPA "automatique smart price adjust" active
    - radio activator de is selles canal active

    <ProductParameter />

    -- créer et finalise ProductTransaction:
    -- ProductTransaction doit avoir:
    - choix de transaction modal + création de transaction modal
    - ce composant doit décrire comment distribuer la marge du prix sur les banks
    - donc une fois la commande est accomplie distribue la marge selon la transaction modal

    <ProductTransaction />

    -- créer et finalise ProductViewer:
    -- ProductViewer doit avoir:
    - un viewer professionnelle propre et simple de la page produit
    - chaque élement doit etre controlable "monter et descendre chaque élement" a fin d'afficher la page produit comme s'houitter

    <ProductViewer />

    -- créer et finalise ProductBusiness:
    -- ProductBusiness doit avoir:
    - ce composant doit offrire la possibilité de rentrer toutes les information business du produit :
      - nom usine ou atelier ou fournisseur ou autre numero de telephone des responsable de création ou d'importation du produit temps de production ou d'importation, sible, genre ....
      - finalise ce composant avec toutes information pertinente de ce genre. ces information son uniquement dédier ou personnelle et non ou clients.

    <ProductBusiness />


    -- chaque composant doit avoir un button next ou back avec une barre de progression adapter pour chaque composant "si chaque composant a les valeur important remplie affiche la barre de progression avec la couleur blumy et si les valeurs important ne sont pas encore acomplie utilise la couleur rady et si tous est finie utilise la couleur greeny"
    -->





  </div>

  <LoaderBlack v-else width="100px" />
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Editor from '../../components/editor.vue';
import ProductNavBar from '../../components/elements/productNavBar.vue';
import ProductPart from '../../components/elements/productPart.vue';
import ProductModels from '../../components/elements/productModels.vue';
import ProductParameter from '../../components/elements/productParameter.vue';
import ProductTransaction from '../../components/elements/productTransaction.vue';
import ProductViewer from '../../components/elements/productViewer.vue';
import ProductBusiness from '../../components/elements/productBusiness.vue';
import Inputer from '../components/elements/bloc/input.vue';
import InputBtn from '../components/elements/bloc/inputBtn.vue';
import Lister from '../components/elements/bloc/list.vue';
import Selector from '../components/elements/bloc/select.vue';
import Gbtn from '../components/elements/bloc/gBtn.vue';
import Message from '../components/elements/bloc/message.vue';
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CancelBtn from '../components/elements/bloc/cancelBtn.vue';
import Explorer from '../../components/elements/explorer.vue';
import EditCat from '../components/elements/editCategory.vue';

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { getProduct, resultProduct } = useOrders()


const { t } = useLang()

const route = useRoute()

const productID = ref(-1)
const isMounted = ref(false)
const isMessage = ref(false)
const message = ref('')
const img = ref('')
const isExplorer = ref(false)
const isExplorer2 = ref(false)
const imageRef = ref('')
const currentPage = ref(1)



onMounted(async () => {
  if (!process.client) return;

  const storedId = route.params.id;
  productID.value = storedId.toString ? storedId : null;

  await getProduct()

  console.log('resultProduct: ', resultProduct.value)

  // on n'appelle PAS directement setProducts ici !

  isMounted.value = true
  
});


const getExplorerImg = (value) => {
  img.value = value
  isExplorer.value = !isExplorer.value
}

const getExplorerCatalog = (value) => {
  imageRef.value = value
  isExplorer2.value = !isExplorer2.value
}


const openMessage = (value) => {
  isMessage.value = value
}

const ridMessage = (value) => {
  message.value = value
}

const changePage = (value) => {
  currentPage.value = value
}




</script>

<style scoped>


</style>
