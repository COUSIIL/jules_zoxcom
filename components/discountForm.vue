<template>
  <form v-if="isMounted" @submit.prevent="saveDiscount" class="w-1/2">
    <div class="flex flex-col items-center justify-center">
      <h2>{{ t('discount name') }}</h2>
      <input class="input" v-model="name" type="text">
      
    </div>

    <div class="flex items-center justify-center">
      <button type="button" :class="percentage === true ? 'btn1' : 'btn2'" @click="percentage = true; fixedValue = false">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
          <path d="M8 16L16 8M10 9C10 9.55228 9.55228 10 9 10C8.44772 10 8 9.55228 8 9C8 8.44772 8.44772 8 9 8C9.55228 8 10 8.44772 10 9ZM16 14.8284C16 15.3807 15.5523 15.8284 15 15.8284C14.4477 15.8284 14 15.3807 14 14.8284C14 14.2761 14.4477 13.8284 15 13.8284C15.5523 13.8284 16 14.2761 16 14.8284Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" />
      </svg>
      {{ t('percentage') }}
      </button>
      <button type="button" :class="fixedValue === true ? 'btn1' : 'btn2'" @click="percentage = false; fixedValue = true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="25" height="25" fill="none">
          <path d="M5.82338 12L4.27922 10.4558C2.57359 8.75022 2.57359 5.98485 4.27922 4.27922C5.98485 2.57359 8.75022 2.57359 10.4558 4.27922L19.7208 13.5442C21.4264 15.2498 21.4264 18.0152 19.7208 19.7208C18.0152 21.4264 15.2498 21.4264 13.5442 19.7208L10.0698 16.2464C9.00379 15.1804 9.00379 13.4521 10.0698 12.386C11.1358 11.32 12.8642 11.32 13.9302 12.386L15.8604 14.3162" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      {{ t('fixed value') }}
      </button>
    </div>
    
    <div class="flex flex-col items-center justify-center">
      <input v-if="percentage === true" class="input" v-model="percentValue" type="number" :disabled="!percentage">

      <input v-else class="input" v-model="countValue" type="number" :disabled="!fixedValue">
    </div>


    <div v-if="usage" class="flex flex-col items-center justify-center">
      <h3>{{ t('quantity') }}</h3>
      <input class="input" v-model="qty" type="number">
    </div>

    <div v-if="!usage && !limitation" class="flex flex-col items-center justify-center">
      <h3>{{ t('usage') }}</h3>
      <input class="input" v-model="usages" type="number">
    </div>

    <div v-if="limitation" class="flex flex-col items-center justify-center">
      <h3>{{ t('valid until') }}</h3>
      <input class="input" v-model="validUntil" type="date">
    </div>

    <div class="flex flex-col items-center justify-center">
      <button class="btn3" style="width: 100%;" @click.prevent="generateCode">
        {{ t('generate') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
          <path d="M10 6C6.22876 6 4.34315 6 3.17157 7.17157C2 8.34315 2 10.2288 2 14C2 17.7712 2 19.6569 3.17157 20.8284C4.34315 22 6.22876 22 10 22H14C17.7712 22 19.6569 22 20.8284 20.8284C22 19.6569 22 17.7712 22 14C22 12.8302 22 11.8419 21.965 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M18 2L18.2948 2.7966C18.6813 3.84117 18.8746 4.36345 19.2556 4.74445C19.6366 5.12545 20.1588 5.31871 21.2034 5.70523L22 6L21.2034 6.29477C20.1588 6.68129 19.6366 6.87455 19.2556 7.25555C18.8746 7.63655 18.6813 8.15883 18.2948 9.2034L18 10L17.7052 9.2034C17.3187 8.15883 17.1254 7.63655 16.7444 7.25555C16.3634 6.87455 15.8412 6.68129 14.7966 6.29477L14 6L14.7966 5.70523C15.8412 5.31871 16.3634 5.12545 16.7444 4.74445C17.1254 4.36345 17.3187 3.84117 17.7052 2.7966L18 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
      </svg>
      </button>
    
      <div v-for="(code, index) in codes" :key="index" class="flex flex-col items-center justify-center">
        <input 
          class="discountInput" 
          :value="code"
          @blur="updateCode($event.target.value, index)"
          type="text"
        >
      </div>
    
      <button class="btn1" style="width: 100%;" v-if="!saving" type="submit">
        {{ t('save discount') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
          <path d="M12 22.0002C7.75736 22.0002 5.63604 22.0002 4.31802 20.5358C3 19.0713 3 16.7143 3 12.0002C3 7.28617 3 4.92915 4.31802 3.46468C5.63604 2.00022 7.75736 2.00022 12 2.00022C16.2426 2.00022 18.364 2.00022 19.682 3.46468C21 4.92915 21 7.28617 21 12.0002C21 16.7143 21 19.0713 19.682 20.5358C18.364 22.0002 16.2426 22.0002 12 22.0002Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M8 2.5V9.82621C8 11.0733 8 11.6969 8.38642 11.9201C9.13473 12.3523 10.5384 10.9103 11.205 10.4761C11.5916 10.2243 11.7849 10.0984 12 10.0984C12.2151 10.0984 12.4084 10.2243 12.795 10.4761C13.4616 10.9103 14.8653 12.3523 15.6136 11.9201C16 11.6969 16 11.0733 16 9.82621V2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      </button>
      <div v-if="disLog" class="flex flex-col items-center justify-center w-full">
        {{ disLog }}
      </div>
    </div>
  </form>
</template>

<script>
import { useLang } from '~/composables/useLang';

export default {
name: 'DiscountForm',
setup() {
    const { t } = useLang();
    return { t };
},
data() {
  return {
    isMounted: false,
    fixedValue: false,
    percentage: false,
    qty: 1,
    disLog: '',
    validUntil: '',
    usages: 1,
    name: '',
    countValue: 0,
    percentValue: 0,
    saving: false,
    codeGenerated: false,
    codes: [],
  };
},

props: {
    usage: {
        type: Boolean,
        default: false,
    },
    limitation: {
        type: Boolean,
        default: false,
    },
},

mounted() {
  this.isMounted = true;
  this.disLog = '';
},
methods: {
  handlePercentageChange() {
    if (this.percentage) {
      this.fixedValue = false;
    }
  },
  handleFixedValueChange() {
    if (this.fixedValue) {
      this.percentage = false;
    }
  },

  generateCode() {
    this.codes = []; // Réinitialise la liste des codes

    for (let i = 0; i < this.qty; i++) {
        let generatedCode = Math.random().toString(36).substr(2, 6).toUpperCase();
        this.codes.push(generatedCode);
    }

    if ((this.usage === false && this.limitation === false) || (this.usage === false && this.limitation === true)) {
        this.codes = [this.codes[0]]; // Un seul code promo
    }

    console.log('codes: ', this.codes);

    this.codeGenerated = true;
  },

  updateCode(code, index) {
    this.codes[index] = code;
  },


  async saveDiscount() {
    this.saving = true;
    this.disLog = this.t("saving discount...");
    if((this.fixedValue || this.percentage) && this.codes) {
        var type = false;
        var value = 0;
        if (this.fixedValue && !this.percentage) {
            type = true;
            value = this.countValue;
        } else if (!this.fixedValue && this.percentage) {
            type = false;
            value = this.percentValue;
        }
        const postDiscount = JSON.stringify({
            name: this.name,
            usage: this.usage,  // Doit être un nombre (0 ou 1)
            limitation: this.limitation,  // Doit être un nombre (0 ou 1)
            type: type,  // Assurez-vous qu'il est bien un entier
            value: value,  // S'assurer que c'est un nombre décimal
            qty: parseInt(this.qty),  // Nombre d’unités
            valid_until: this.validUntil ? new Date(this.validUntil).toISOString() : null,  // Format propre
            codes: this.codes  // Doit être un tableau non vide
        });

        console.log('data: ', postDiscount);

        this.disLog = this.t("waiting response...");
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=postDiscount', {
        method: 'POST',
        body: postDiscount,
        });
        if(!response2.ok){
            this.disLog = this.t("error in response");
            this.saving = false;
            return;
        }
        this.disLog = this.t("waiting data...");
        const textResponse = await response2.json();
        if (textResponse.success) {
            this.disLog = textResponse.message;
            this.saving = false;
        } else {
            this.disLog = textResponse.message + ' : ' + textResponse.data;
            this.saving = false;
        }
    } else {
      this.saving = false;
    }
  },
},
};
</script>
