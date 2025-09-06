<template>

  <LoaderBlack v-if="isSaving" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div :style="{maxWidth: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}">
    <div v-if="!isActive" class="boxContainer1">
  
      <div style="display: flex; justify-content: center; align-items: center; margin-inline: 10px; gap: 5px;">
          <div v-html="resizeSvg(icons['bank'], 20, 20)">

          </div>
          <h1>
          {{ banksLength }} Banks
          </h1>
          
      </div>

      <CallToAction :text="t('add bank')" :svg="icons['add']" @clicked="activate"/>

    </div>

    <AddBank v-if="isActive" @saving="saving" @success="success" @cancel="cancel" @message="messager" @x="isActive = false"/>

    <div v-if="banks" v-for="(box, index) in banks" :key="index" class="center_column">

      <div class="boxContainer2">
        <div class="center_bank">
          <div v-html="resizeSvg(icons['bank'], 50, 50)">

          </div>
          <div class="insider2">
            
            <p class="titleBank">
              {{ box.name }}

              <!--Radio :selected="box.status" @changed="box.status = !box.status"/-->
            </p>
            <p class="minTitle4">
              {{ box.created_at }}
            </p>
          </div>
          
        </div>
        <div class="center_bank">
          <div v-html="resizeSvg(icons['piggyBank'], 20, 20)">

          </div>
          <p class="minTitle2">
            {{ t('current capital') }} : 
          </p>
          <p class="minTitle3">
            {{ box.current_balance }} {{ box.currency }}
          </p>
          <div 
            v-html="resizeSvg(icons[box.evaluation], 18, 18)" 
            :class="{
              'svg-up': box.evaluation === 'up',
              'svg-down': box.evaluation === 'down',
              'svg-stable': box.evaluation === 'stable'
            }">
          </div>

        </div>

        <div class="center_bank">
          <div v-html="resizeSvg(icons['fund'], 20, 20)">

          </div>
          <p class="minTitle4">
            {{ t('capital started with') }} {{ box.opening_balance }} {{ box.currency }}
          </p>
        </div>

      </div>
    </div>
    
  </div>

  <div style="height: 80px;">

  </div>

</template>

<script setup>

  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
  import AddBank from '../components/elements/addBanks.vue';
  import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
  import Message from '../components/elements/bloc/message.vue';
  //import Radio from '../components/elements/bloc/radio.vue';

  import icons from '~/public/icons.json'

  const router = useRouter();

  var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const { t } = useLang()

  const banksLength = ref(0)
  const banks = ref()
  const successData = ref()
  const isSaving = ref(false)
  const isMessage = ref(false)
  const message = ref('')
  const isActive = ref(false)

  onMounted(() => {
    getBanks()
  })

  const activate = () => {
    isActive.value = true
  }

  const success = (value) => {
    successData.value = value
    getBanks()
  }
  const cancel = () => {
    isActive.value = false
  }
  const saving = (value) => {
    isSaving.value = value
  }
  const messager = (value) => {
    isMessage.value = true
    message.value = value
  }

  const getBanks = async () => {
    try {
        isSaving.value = true
        const res = await fetch('https://management.hoggari.com/backend/api.php?action=getBanks', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
        })

        const data = await res.json()
        if (data.success) {
          banks.value = data.data.map(bank => {
            const value = bank.current_balance - bank.opening_balance
            let evaluation = 'stable'
            if (value < 0) evaluation = 'down'
            else if (value > 0) evaluation = 'up'
            return { ...bank, evaluation }
          })

          banksLength.value = banks.value.length
        } else {
        messager(data.message || 'Failed to load banks')
        }
    } catch (err) {
        console.error(err)
        messager('Error while fetching banks')
    } finally {
        isSaving.value = false
    }
  }

</script>

<style>

.boxContainer1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
  }
  .dark .boxContainer1{
    background-color: var(--color-darkly);
  }

  .boxContainer2 {
    display: flex;
    align-items: right;
    justify-content: space-between;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    padding: 5px;
    cursor: pointer;
  }
  .dark .boxContainer2{
    background-color: var(--color-darkly);
  }
  

  .no_image {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    border-radius: 50px;
    background-color: var(--color-whizy);
  }
  .dark .no_image {
    background-color: var(--color-darkow);
  }

  .center_column {
    width: 90%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .center_bank {
    width: 100%;
    display: flex;
    min-width: 250px;
    justify-content: left;
    align-items: center;
    gap: 10px;
    margin-inline: 5px;
  }

  .insider2 {
    display: flex;
    min-width: 150px;
    justify-content: left;
    align-items: center;
    flex-direction: column;
  }
  .titleBank {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 150px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 20px;
    font-weight: bold;
  }

  .minTitle {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 100px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 14px;
  }
  .minTitle2 {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 100px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 14px;
  }

  .minTitle4 {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 150px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 14px;
    color: var(--color-gorry);
  }
  .dark.minTitle4 {
    color: var(--color-garry);
  }

  .minTitle3 {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 120px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 16px;
    font-weight: bold;
    color: var(--color-greny);
  }

  .center_bank img {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    object-fit: cover; /* Remplit la zone en gardant le ratio */
    display: flex;
    border-radius: 50%; /* Cercle parfait */
    

  }

  .svg-up svg {
    color: var(--color-greny);
  }

  .svg-down svg {
    color: var(--color-rady);
  }

  .svg-stable svg {
    color: var(--color-garry);
  }


</style>