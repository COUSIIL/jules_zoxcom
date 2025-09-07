<template>
      <div v-if="isVisible && isMounted" class="p-2.5 mt-12 text-center text-lg bg-white rounded-lg shadow-lg max-w-[300px]">
        <div class="flex p-2.5 overflow-x-auto space-x-2.5">
            <div v-for="(ref, index2) in _total" :key="index2" >
              <div class="max-w-xs">
                <button class="w-5 cursor-pointer" type="button" @click="changeOrder(index2)">
                  {{ index2 + 1}}
              </button>
              </div>
            </div>
        </div>
        
        <select class="flex items-center justify-around w-11/12 min-w-[100px] max-w-[400px] m-2.5 p-1.5 cursor-pointer bg-whity rounded-md border border-rangy dark:bg-darky" name="method" id="method" v-model="method">
            <option>ups</option>
        </select>
        <div>
            {{ t('Name') }}
            <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="name" @blur="_name[index] = name">
        </div>
        <div>
            {{ t('Phone') }}
            <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="phone1" @blur="_phone1[index] = phone1">
        </div>
        <div>
            {{ t('2nd phone') }}
            <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="phone2" @blur="_phone2[index] = phone2">
        </div>
        <div>
            {{ t('Give note') }}
            <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="note" @blur="_note[index] = note">
        </div>
        <div>
            {{ t('Total') }}
            <input class="flex items-center w-11/12 max-w-md p-1 m-1 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 border-gorry rounded-md dark:bg-darkly dark:border-garry" type="text" v-model="total" @blur="_total[index] = total">
        </div>
        <p>{{ t(message) }}</p>
        <div class="flex justify-center mt-4 gap-2.5">
          <button class="px-4 py-2 text-white bg-green-500 border-none rounded-md cursor-pointer" @click="confirm()">{{ t('Yes') }}</button>
          <button class="px-4 py-2 text-white bg-red-500 border-none rounded-md cursor-pointer" @click="this.$emit('cancel')">{{ t('No') }}</button>
        </div>
      </div>
</template>
  
  <script>
  import { useLang } from '~/composables/useLang';

  export default {
    name: 'Deliver',
    setup() {
      const { t } = useLang();
      return { t };
    },
    data() {
      return {
        isMounted: false,
        methods: ['ups'],
        method: 'ups',
        name: '',
        phone1: '',
        phone2: '',
        note: '',
        total: '',
        index: 0,
      }
    },
    props: {
        isVisible: {
            type: Boolean,
            default: false, // Valeur booléenne par défaut
        },
        message: {
            type: String,
            default: "You confirm this action ?",
        },
        _name: {
            type: Array,
            default: [""],
        },
        _phone1: {
            type: Array,
            default: [""],
        },
        _phone2: {
            type: Array,
            default: [""],
        },
        _note: {
            type: Array,
            default: [""],
        },
        _total: {
            type: Array,
            default: [""],
        },
        _indexing: {
            type: Array,
        },
        _index: {
            type: Number,
            default: 0,
        },
    },
    mounted() {
      
      this.method = this.methods[0];
      this.index = this._index;
      this.name = this._name[0];
      this.phone1 = this._phone1[0];
      this.phone2 = this._phone2[0];
      this.note = this._note[0];
      this.total = this._total[0];
      for(var i = 0; i < this._total.length; i++) {
        this._indexing.push();
      }
      this.isMounted = true;
    },


    methods: {
        changeOrder(vIndex) {
            this.method = this.methods[0];
            this.index = vIndex;
            this.name = this._name[vIndex];
            this.phone1 = this._phone1[vIndex];
            this.phone2 = this._phone2[vIndex];
            this.note = this._note[vIndex];
            this.total = this._total[vIndex];
        },

        confirm() {
        this.$emit('confirm', {
            name: this._name,
            phone1: this._phone1,
            phone2: this._phone2,
            note: this._note,
            total: this._total,
            indexing: this._indexing,
        });
    }

    },
  };
  </script>