<template>
  <LoaderBlack v-if="isSaving" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div class="flex flex-col items-center max-w-full">
    <div v-if="!isActive" class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
      <div class="flex items-center justify-center mx-2.5 gap-1.25">
          <div v-html="resizeSvg(icons['bank'], 20, 20)"></div>
          <h1>{{ banksLength }} {{ t('banks') }}</h1>
      </div>
      <CallToAction :text="t('add bank')" :svg="icons['add']" @clicked="activate"/>
    </div>

    <AddBank v-if="isActive" @saving="saving" @success="success" @cancel="cancel" @message="messager" @x="isActive = false"/>

    <div v-if="banks" v-for="(box, index) in banks" :key="index" class="flex flex-col items-center justify-center w-11/12">
      <div class="flex flex-col items-end justify-between w-full max-w-3xl min-w-[300px] p-1.25 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md cursor-pointer bg-whitly dark:bg-darkly">
        <div class="flex items-center justify-start w-full min-w-[250px] gap-2.5 mx-1.25">
          <div v-html="resizeSvg(icons['bank'], 50, 50)"></div>
          <div class="flex flex-col items-center justify-start min-w-[150px]">
            <p class="flex items-center justify-start min-w-[150px] overflow-hidden text-xl font-bold text-ellipsis whitespace-nowrap">
              {{ box.name }}
            </p>
            <p class="flex items-center justify-start min-w-[150px] overflow-hidden text-sm text-gorry text-ellipsis whitespace-nowrap dark:text-garry">
              {{ box.created_at }}
            </p>
          </div>
        </div>
        <div class="flex items-center justify-start w-full min-w-[250px] gap-2.5 mx-1.25">
          <div v-html="resizeSvg(icons['piggyBank'], 20, 20)"></div>
          <p class="flex items-center justify-start min-w-[100px] overflow-hidden text-sm text-ellipsis whitespace-nowrap">
            {{ t('current capital') }} : 
          </p>
          <p class="flex items-center justify-start min-w-[120px] overflow-hidden text-base font-bold text-greny text-ellipsis whitespace-nowrap">
            {{ box.current_balance }} {{ box.currency }}
          </p>
          <div 
            v-html="resizeSvg(icons[box.evaluation], 18, 18)" 
            :class="{
              'text-greny': box.evaluation === 'up',
              'text-rady': box.evaluation === 'down',
              'text-garry': box.evaluation === 'stable'
            }">
          </div>
        </div>

        <div class="flex items-center justify-start w-full min-w-[250px] gap-2.5 mx-1.25">
          <div v-html="resizeSvg(icons['fund'], 20, 20)"></div>
          <p class="flex items-center justify-start min-w-[150px] overflow-hidden text-sm text-gorry text-ellipsis whitespace-nowrap dark:text-garry">
            {{ t('capital started with') }} {{ box.opening_balance }} {{ box.currency }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="h-20"></div>
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
        messager(data.message || t('failed to load banks'))
        }
    } catch (err) {
        console.error(err)
        messager(t('error while fetching banks'))
    } finally {
        isSaving.value = false
    }
  }

</script>