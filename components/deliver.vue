<template>
        
      <div v-if="isVisible && isMounted" class="modal" style="max-width: 300px">
        <div class="list2">
            <div v-for="(ref, index2) in _total" :key="index2" >
              <div style="max-width: 200px">
                <button style="width: 20px; cursor: pointer;" type="button" @click="changeOrder(index2)">
                  {{ index2 + 1}}
              </button>
              </div>
                
            </div>
        </div>
        
        <select class="btn2" style="width: 90%" name="method" id="method" v-model="method">
            <option>
                ups
            </option>
        </select>
        <div>
            Name
            <input class="input" type="text" v-model="name" @blur="_name[index] = name">
        </div>
        <div>
            Phone
            <input class="input" type="text" v-model="phone1" @blur="_phone1[index] = phone1">
        </div>
        <div>
            2nd phone
            <input class="input" type="text" v-model="phone2" @blur="_phone2[index] = phone2">
        </div>
        <div>
            Give note
            <input class="input" type="text" v-model="note" @blur="_note[index] = note">
        </div>
        <div>
            Total
            <input class="input" type="text" v-model="total" @blur="_total[index] = total">
        </div>
        <p>{{ message }}</p>
        <div class="buttons">
          <button class="confirm" @click="confirm()">Yes</button>
          <button class="cancel" @click="this.$emit('cancel')">No</button>
        </div>
      </div>

</template>
  
  <script>
  export default {
    name: 'Deliver',
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
  
  <style>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal {
    background: white;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    margin-top: 50px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    font-size: 2vh;
  }

  
  .buttons {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
  }
  
  .buttons button {
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
  }
  
  .confirm {
    background-color: #4caf50;
    color: white;
  }
  
  .cancel {
    background-color: #f44336;
    color: white;
  }
  </style>
  