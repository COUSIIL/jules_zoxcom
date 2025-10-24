<template>
  <Loader v-if="loading" width="80px" />
  <Confirm :isVisible="showConfirm" @confirm="deleteOrder(idToEdit, indexToEdit), showConfirm = false" @cancel="cancelConfirmDelete"/>

  <Selector :options="statusOptions" :showIt="showStatus" :disabled="true" @close="showStatus = false" @update:modelValue="editStatus"/>
  <nav v-if="showDeliver" class="overlay">
    <Deliver v-if="!isShipping" :isVisible="showDeliver"
      :_name="nameDeliver"
      :_phone1="phoneDeliver"
      :_total="totalDeliver"
      :_indexing="indexDeliver"
      @confirm="shipping"
      @cancel="cancelShipping"
    />
    <div v-else-if="isShipping">
      <Loader :style="{width: '80px', height: '80px'}"/>
    </div>
  </nav>
  <div class="containerOrder">
    <Switcher />

    <div v-if="limitedDt && limitedDt.length" class="uler">
      <div
        v-for="(dts, index) in limitedDt"
        :key="index"
        class="ulerli"
      >
        <div class="floatingBtn">
          <RectBtn iconColor="#ff5555"  svg="trashX" @click:ok="showConfirmDelete(dts.id, index)"/>

          <RectBtn :text="dts.status" :iconColor="returnColor(dts.status)"  :svg="returnSVG(dts.status)" @click:ok="returnStatusList(dts.status, dts.id, index)"/>

          <button class="radioBtn" @click="dts.isSelected = !dts.isSelected">
            <Radio :selected="dts.isSelected"/>
          </button>
          
        </div>
        <div class="box1">
          <div class="order-item" role="listitem"
          aria-label="order"
          :class="[
              
              { active: dts.isMore }
            ]">
            <button
              type="button"
              :class="[
                'title1',
                `status-${dts.status.toLowerCase()}`,
                { active: dts.isMore }
              ]"
              @click="doMore(index)"
            >
    
              {{ index + 1 }}
              
            </button>
            <!-- HTML (version nettoyée) -->
          <div class="box2" @click="doMore(index)">
            <!-- groupe 1 -->
            <div class="boxGroup">
              <div class="boxItem">
                <div v-html="resizeSvg(iconsFilled['order'], 18, 18)"></div>
                <p class="text">order-{{ dts.id }}</p>
              </div>

              <div class="boxItem">
                <div v-html="resizeSvg(iconsFilled['phone'], 18, 18)"></div>
                <p class="text">{{ dts.phone }}</p>
              </div>
            </div>

            <!-- groupe 2 -->
            <div class="boxGroup">
              <div class="boxItem">
                <div v-html="resizeSvg(iconsFilled['alarm'], 18, 18)"></div>
                <p class="text">{{ dts.create }}</p>
              </div>

              <div class="boxItem">
                <div v-html="resizeSvg(iconsFilled['location'], 18, 18)"></div>
                <p class="text">{{ dts.name }} - {{ dts.deliveryZone }}</p>
              </div>
            </div>
          </div>


                
                
                
                
            
            <SquareBtn icon="phone" width="24" height="24" @click:ok="hrefLink(dts.phone)"/>

          </div>


          <div v-if="dts.isMore" class="order-details">

            <button class="copyCard" @click="copyIp(dts.ip, index)" title="Copy">
              <h4>
                <p>
                  <b>IP: </b> 
                  {{ dts.ip }}
                </p>

                <div v-if="dts.copiedId === false" v-html="resizeSvg(icons['copy'], 18, 18)"></div>
                <div v-else v-html="resizeSvg(iconsFilled['copyCheck'], 18, 18)"></div>

              </h4>
              
            </button>

            <!-- Livraison & Infos -->
            <div class="grid2">
              <div class="copyCard">
                <div class="rowFlex">
                  <h4>{{t('localisation')}}</h4>
                  <RectBtn svg="edit" @click:ok="toggleEdit(dts)" v-if="dts.status !== 'shipping'"/>
                </div>
                <div v-if="!dts.isEditing">
                  <p><b>Wilaya:</b> {{ dts.deliveryZone }}</p>
                  <p><b>Commune:</b> {{ dts.sZone }}</p>
                  <p><b>Adresse:</b> {{ dts.mZone }}</p>
                </div>
                <div v-else>
                  <input v-model="dts.deliveryZone" />
                  <input v-model="dts.sZone" />
                  <input v-model="dts.mZone" />
                </div>
              </div>

              <div class="copyCard infos">
                <div class="rowFlex">
                  <h4>{{t('customer information')}}</h4>
                  <RectBtn svg="edit" @click:ok="toggleEdit(dts)" v-if="dts.status !== 'shipping'"/>
                </div>
                <div v-if="!dts.isEditing">
                  <p><b>Nom:</b> {{ dts.name }}</p>
                  <p><b>Téléphone:</b> {{ dts.phone }}</p>
                  <p><b>Date:</b> {{ dts.create }}</p>
                </div>
                <div v-else>
                  <input v-model="dts.name" />
                  <input v-model="dts.phone" />
                </div>
              </div>

              <div class="copyCard infos">
                <div class="rowFlex">
                  <h4>{{t('delivery')}}</h4>
                  <RectBtn svg="edit" @click:ok="toggleEdit(dts)" v-if="dts.status !== 'shipping'"/>
                </div>
                <p><b>{{t('deliver name')}}:</b> {{ dts.method }}</p>
                <p v-if="dts.type == 0"><b>{{t('delivery type')}}:</b> {{t('home')}}</p>
                <p v-else><b>{{t('delivery type')}}:</b> {{t('stop desk')}}</p>
                <p><b>{{t('fees')}}:</b> {{ dts.deliveryValue }} DA</p>
                <p v-if="dts.trackingCode"><b>Tracking:</b> {{ dts.trackingCode }}</p>

              </div>
            </div>
            <!-- Produits -->
            <div class="products">
              <ul>
                <li v-for="(item, idx) in dts.items" :key="idx" class="product">
                  
                  <div class="product-info">
                    <div>
                      <div class="product-img-wrapper">
                        <img :src="item.image" alt="product" class="product-img" />
                      </div>
                      <p class="product-name">{{ item.productName }}</p>
                    </div>
                    
                    <div class="columnFlex">
                      <div v-for="(sub, i2) in item.items" :key="i2" class="sub-item">
                        <div class="tags">
                        
                          <span class="tag color">
                            <span class="color-dot" :style="{ background: sub.color }"></span>
                            {{ sub.color_name }}
                          </span>
                          <span class="tag size">Taille: {{ sub.size }}</span>
                          <span class="tag qty">x{{ sub.qty }}</span>
                        </div>

                        <div class="price">
                          <span v-if="sub.promo && sub.promo !== '0.00'" class="promo">
                            <span class="old">{{ sub.total }} DA</span>
                            <span class="new">{{ sub.promo }} DA</span>
                          </span>
                          <span v-else class="new">{{ sub.total }} DA</span>
                        </div>
      
                        
                      </div>
                    </div>
                    
                  </div>
                </li>
              </ul>
            </div>


            

            <!-- Note -->
            <div class="notes-section">
              <h4>Notes</h4>
              <ul>
                <li v-for="(note, noteIndex) in dts.notes" :key="noteIndex" class="note-item">
                  <span>{{ note.text }} - <i>{{ note.author }}</i></span>
                  <button @click="deleteNote(dts, noteIndex)">Delete</button>
                </li>
              </ul>
              <div class="add-note">
                <input type="text" v-model="dts.newNoteText" placeholder="Add a new note" />
                <button @click="addNote(dts)">Add Note</button>
              </div>
            </div>

            <!-- Total -->
            <div class="copyCard total">
              <h3>Total: {{ dts.total }} DA</h3>
            </div>
            <div v-if="dts.isEditing" class="edit-actions">
              <button @click="saveOrder(dts)">Save</button>
              <button @click="toggleEdit(dts)">Cancel</button>
            </div>
          </div>
        </div>
        
        
        



        
      </div>
    </div>
    <RectBtn text="more" @click="limitNewDt()" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useOrders } from '../../composables/getOrders';
import Switcher from '../../components/elements/newBloc/switcher.vue';
import Loader from '../../components/elements/animations/loaderBlack.vue';
import RectBtn from '../../components/elements/newBloc/rectBtn.vue';
import SquareBtn from '../../components/elements/newBloc/squareConBtn.vue';
import Note from '../../components/elements/newBloc/noteTicker.vue';
import PostIt from '../../components/elements/newBloc/postIt.vue';
import Selector from '../../components/elements/bloc/select.vue';
import Radio from '../../components/elements/bloc/radio.vue';
import Confirm from '../../components/elements/bloc/confirm.vue';
import Deliver from '../../components/deliver.vue';

import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'

const { t } = useLang()

const showDeliver = ref(false);
const isShipping = ref(false);
const nameDeliver = ref([]);
const phoneDeliver = ref([]);
const totalDeliver = ref([]);
const indexDeliver = ref([]);

const cancelShipping = () => {
  showDeliver.value = false;
};


const { data, loading, getOrders, deleteOrder, updateOrderValue } = useOrders();
const idToEdit = ref(0)
const statusID = ref(0)
const statusIndex = ref(0)
const indexToEdit = ref(0)
const showConfirm = ref(false)
const currentColor = ref('#ffef6c')
var showConfirmDelete = (id, index) => {
  idToEdit.value = id
  indexToEdit.value = index
  showConfirm.value = true
}

var cancelConfirmDelete = () => {
  idToEdit.value = 0
  indexToEdit.value = 0
  showConfirm.value = false
}

var resizeSvg = (svg, width, height) => {
return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

const showStatus = ref(false)

const statusInfo = ref([
  {name: 'canceled', color: 'var(--color-rady)', svg: 'x'},
  {name: 'waiting', color: 'var(--color-rangy)', svg: 'alarm'},
  {name: 'pending', color: 'var(--color-rangy)', svg: 'alarm'},
  {name: 'confirmed', color: 'var(--color-blumy)', svg: 'thumb-up'},
  {name: 'completed', color: 'var(--color-greeny)', svg: 'check'},
  {name: 'shipping', color: 'var(--color-yelly)', svg: 'truck'},
  {name: 'unreaching', color: 'var(--color-gorry)', svg: 'phone'},
  {name: 'returned', color: 'var(--color-ioly)', svg: 'back'}
])

const statusOptions = ref([{value: 'test', label: 'image', img: ''}])

var returnStatusList = (val, id, index) => {
  statusID.value = id
  statusIndex.value = index
  if (val === 'shipping') {
    deliverOrder(limitedDt.value[index]);
    return;
  }
  if(val === 'canceled') {
    statusOptions.value = [
      {value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25)},
      {value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25)},
      {value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25)},
      {value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25)}
    ]
  } else if (val === 'waiting' || val === 'pending') {
    statusOptions.value = [
      {value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25)},
      {value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25)},
      {value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25)},
      {value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25)}
    ]
  } else if (val === 'confirmed') {
    statusOptions.value = [
      {value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25)},
      {value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25)},
      {value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25)},
      {value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25)}
    ]
  } else if (val === 'completed') {
    statusOptions.value = [
      {value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25)}
    ]
  } else if (val === 'shipping') {
    statusOptions.value = [
      {value: 'returned', label: 'Return', img: resizeSvg(iconsFilled['back'], 25, 25)},
      {value: 'completed', label: 'Completed', img: resizeSvg(iconsFilled['check'], 25, 25)}
    ]
  } else if (val === 'unreaching') {
    statusOptions.value = [
      {value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25)},
      {value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25)},
      {value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25)},
      {value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25)}
    ]
  } else if (val === 'returned') {
    statusOptions.value = [
      {value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25)},
      {value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25)},
      {value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25)},
      {value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25)}
    ]
  }

  showStatus.value = true
}

// Exemple d’appel à ton endpoint PHP depuis le composant ou un store
const getUserData = async () => {
  try {
    const res = await $fetch('https://management.hoggari.com/backend/metaApi.php?action=getInstagramHashtag')

  } catch (err) {
    console.error('Erreur:', err)
  }
}


var returnColor = (vl) => {

  var myColor
  for(let color of statusInfo.value) {
    
    if(color.name === vl) {
      myColor = color.color
      break
    }
  }

  return myColor
  
};

var returnSVG = (vl) => {

  var mySvg
  for(let svg of statusInfo.value) {
    
    if(svg.name === vl) {
      mySvg = svg.svg
      break
    }
  }

  return mySvg
  
};


const dt = ref([]);
const limit = ref(20);
const limitedDt = ref([]);

watch(data, (newData) => {
  if (!newData || newData.length === 0) return;
  reverseOrders(newData);
});

onMounted(() => {
  getOrders();
  getUserData();
});

const editStatus = async (vl) => {

  await updateOrderValue(statusID.value, 'status', vl)
}

const updateNotes = async (order) => {
  await updateOrderValue(order.id, 'note', JSON.stringify(order.notes));
};

const addNote = (order) => {
  if (order.newNoteText.trim() !== '') {
    const author = order.isClientNote ? 'client' : 'user';
    order.notes.push({
      text: order.newNoteText,
      author: author,
      timestamp: new Date().toISOString()
    });
    order.newNoteText = '';
    updateNotes(order);
  }
};

const deleteNote = (order, noteIndex) => {
  order.notes.splice(noteIndex, 1);
  updateNotes(order);
};

const copyIp = async (ip, index) => {
  
  await navigator.clipboard.writeText(ip)
  limitedDt.value[index].copiedId = true
  setTimeout(() => {
    limitedDt.value[index].copiedId = false
  }, 1000) // 1000 ms = 1 seconde
  
}

const reverseOrders = (vl) => {
  if (Array.isArray(vl)) {
    dt.value = vl
      .map(item => {
        let notes = [];
        if (item.note) {
          try {
            // Attempt to parse the note field as JSON
            const parsedNotes = JSON.parse(item.note);
            if (Array.isArray(parsedNotes)) {
              notes = parsedNotes;
            } else {
              // If it's not an array, treat it as a single note object
              notes = [{ text: item.note, author: 'system', timestamp: new Date().toISOString() }];
            }
          } catch (e) {
            // If parsing fails, it's likely a plain string
            notes = [{ text: item.note, author: 'system', timestamp: new Date().toISOString() }];
          }
        }
        return {
          ...item,
          isMore: false,
          isSelected: false,
          copiedId: false,
          notes: notes, // Use the new 'notes' array
          newNoteText: '', // For the input field
          isEditing: false
        };
      })
      .reverse();

    limitedDt.value = dt.value.slice(0, limit.value);
  }
};

const doMore = (val) => {
  limitedDt.value[val].isMore = !limitedDt.value[val].isMore
}


const limitNewDt = () => {
  limit.value += 20;
  limitedDt.value = dt.value.slice(0, limit.value);
};

const hrefLink = (link) => {
  // Si c'est un numéro => tel:...
  if (/^\+?\d+$/.test(link)) {
    window.location.href = `tel:${link}`
  } else {
    // Sinon on considère que c'est un lien web
    window.open(link, "_blank")
  }
}

const getTrackingCode = async (deliveryMethod) => {
  // Simulate an API call to a delivery service
  // In a real application, this would be an actual API call
  // to UPS, FedEx, etc.
  console.log(`Getting tracking code for ${deliveryMethod}`);
  return `TRK${Math.random().toString(36).substr(2, 9)}`;
};

const deliverOrder = async (order) => {
  if (order.status === 'shipping') {
    console.log(`Order ${order.id} is already shipping.`);
    return;
  }
  nameDeliver.value.push(order.name);
  phoneDeliver.value.push(order.phone);
  totalDeliver.value.push(order.total);
  indexDeliver.value.push(order.id);
  showDeliver.value = true;
};

const shipping = async ({ name, phone1, phone2, note, total, indexing }) => {
  isShipping.value = true;
  for (let i = 0; i < indexing.length; i++) {
    const orderIndex = limitedDt.value.findIndex(o => o.id === indexing[i]);
    if (orderIndex !== -1) {
      const order = limitedDt.value[orderIndex];
      order.name = name[i];
      order.phone = phone1[i];
      // Here you would call the actual delivery provider API
      console.log(`Shipping order ${order.id} for ${name[i]}`);
      order.trackingCode = await getTrackingCode(order.method);
      await updateOrderValue(order.id, 'status', 'shipping');
      await updateOrderValue(order.id, 'tracking_code', order.trackingCode);
    }
  }
  isShipping.value = false;
  showDeliver.value = false;
};

const splitName = (fullName) => {
  if (!fullName || typeof fullName !== 'string') return { firstname: '', familyname: '' };
  const parts = fullName.trim().split(' ');
  if (parts.length === 1) return { firstname: parts[0], familyname: '' };
  const firstname = parts.slice(0, 1).join(' ');
  const familyname = parts.slice(1).join(' ');
  return { firstname, familyname };
};

const toggleEdit = (order) => {
  order.isEditing = !order.isEditing;
};

const saveOrder = async (order) => {
  order.isEditing = false;
  // Recalculate total based on new data
  let newTotal = 0;
  order.items.forEach(item => {
    let price = (item.promo && item.promo !== '0.00') ? item.promo : item.total;
    newTotal += price * item.qty;
  });
  newTotal += parseFloat(order.deliveryValue);
  order.total = newTotal;

  await updateOrderValue(order.id, 'delivery_zone', order.deliveryZone);
  await updateOrderValue(order.id, 's_zone', order.sZone);
  await updateOrderValue(order.id, 'm_zone', order.mZone);
  await updateOrderValue(order.id, 'name', order.name);
  await updateOrderValue(order.id, 'phone', order.phone);
  await updateOrderValue(order.id, 'total', order.total);
};
</script>

<style scoped>
/* Variables locales pour personnalisation rapide */
.containerOrder {
  --copyCard-radius: 14px;
  --copyCard-padding: 12px;
  --copyCard-gap: 5px;
  --max-list-width: 980px;
  --accent: #26b426;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  gap: 14px;
  box-sizing: border-box;
  padding: 8px;
}

/* Liste */
.uler {
  margin: 10px 0;
  padding: 0;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: var(--copyCard-gap);
  list-style: none;
}

.ulerli {
  width: calc(100% - 10px);
  max-width: 800px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  gap: 5px;
  margin-bottom: 5px;

}


.containerOrder .order-item {
  width: 100%;
  min-height: 60px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-inline: 5px;
}


.box1 {
  width: 100%;
  min-width: 50px; /* IMPORTANT: permet au contenu de s'ellipsiser dans un grid/flex containerOrder */
  display: flex;
  align-items: center;
  justify-content: left;
  flex-direction: column;
  margin-inline: 5px;
  background: var(--color-whitly); /* fond clair au centre */
  padding: 2px;
  border-radius: 8px;
  box-shadow: 
      2px  2px 2px 1px #aca7afc2;
}
.dark .box1 {
  background: var(--color-darkly);
  box-shadow: 
        2px 2px 2px 1px rgba(0, 0, 0, 0.761)
}

.box1 p {
  width: 100%;
  min-width: 50px;
  display: flex;
  justify-content: left;
  align-items: center;

}

/* Container principal : ligne tant que possible, wrap si manque d'espace */
.box2 {
  display: flex;
  flex-direction: row; /* par défaut en ligne */
  align-items: center;
  justify-content: center;
  flex-wrap: wrap; /* permet un minimum de flexibilité */
  gap: 6px;

  width: 100%;
  min-width: 50px;
  font-size: clamp(12px, 1.6vw, 14px);
  font-weight: 500;
  margin-inline: 5px;
}




/* Chaque "groupe" contient plusieurs items (ex: order + phone).
   On laisse chaque groupe occuper au moins une "colonne" flexible. */
.boxGroup {
  
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: flex-start;
  box-sizing: border-box;

  /* flexible : peut grandir et rétrécir.
     Le "basis" (ici 220px) est la taille préférée avant wrapping. */
  flex: 1 1 220px;
  min-width: 0; /* IMPORTANT pour autoriser le shrink du contenu */
  padding-inline: 6px;
}

/* Chaque élément intérieur (icone + texte) */
.boxItem {
  min-width: 150px;
  display: flex;
  align-items: center;
  gap: 6px;
  min-width: 0;      /* IMPORTANT : permet au <p> de s'ellipsiser/shrinker */
  flex: 0 1 auto;    /* ne force pas la ligne à prendre tout l'espace */
  padding: 4px 2px;
}

/* Icône : garder taille fixe */
.boxItem > div {
  width: 18px;
  height: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 18px;
}

/* Texte : gérer overflow et ellipsis quand l'espace manque */
.boxItem .text {
  margin: 0;
  padding: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  min-width: 0; /* encore important pour l'ellipsis dans flexbox */
  font-size: inherit;
}



/* --- responsive : sur conteneurs très étroits, empiler verticalement */
@media (max-width: 520px) {
  .box2 {
    flex-direction: column;
    align-items: flex-start; /* ou center selon ton besoin */
  }
  .boxGroup {
    width: 100%;
    flex: 1 1 100%;
  }
  /* si tu veux que le texte permette des sauts de ligne au lieu d'ellipsis : */
  .boxItem .text.wrap-when-small {
    white-space: normal;
    overflow: visible;
  }
}



.boxRow {
  min-width: 150px;
  display: flex;
  justify-content: left;
  align-items: center;
  padding-inline: 5px;
  gap: 5px;

}


/* Badge ID */
.containerOrder .order-item .title1 {
  height: 80%;
  min-height: 50px;
  width: 30px;
  min-width: 0; /* IMPORTANT: permet au contenu de s'ellipsiser dans un grid/flex containerOrder */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: clamp(12px, 1.6vw, 14px);
  display: flex;
  align-items: center;
  gap: 5px;
  justify-content: center;
  font-weight: 700;
  flex: 0 0 auto;
  margin-inline: 5px;
  border-radius: 4px 0 0 4px;
  cursor: pointer;
  transition:
    color 0.35s ease,
    background 0.35s ease,
    box-shadow 0.35s ease,
    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    

}

.title1:not(.active) {
  border-radius: 8px;
  box-shadow: 
     2px  2px 2px 1px #aca7afc2;
  transform: translateY(-2px); /* bouton ressorti */
}
.dark .title1:not(.active) {
  box-shadow: 
      2px 2px 2px 1px rgba(0, 0, 0, 0.761);
}

/* Actif (enfoncé comme un clic) */
.title1 .active {
  border-radius: 8px;
  box-shadow: none;
  transform: translateY(2px); /* bouton enfoncé */
}
.dark .title1 .active {
  box-shadow: none;
}



.status-confirmed-border {
  border: 1px solid color-mix(in srgb, var(--color-blumy) 50%, transparent);
}
.status-shipping-border {
  border: 1px solid color-mix(in srgb, var(--color-yelly) 50%, transparent);
}
.status-waiting-border {
  border: 1px solid color-mix(in srgb, var(--color-rangy) 50%, transparent);
}
.status-pending-border {
  border: 1px solid color-mix(in srgb, var(--color-rangy) 50%, transparent);
}
.status-unreaching-border {
  border: 1px solid color-mix(in srgb, var(--color-gorry) 50%, transparent);
}
.status-returned-border {
  border: 1px solid color-mix(in srgb, var(--color-ioly) 50%, transparent);
}
.status-canceled-border {
  border: 1px solid color-mix(in srgb, var(--color-rady) 50%, transparent);
}
.status-completed-border {
  border: 1px solid color-mix(in srgb, var(--color-greeny) 50%, transparent);
}

.status-confirmed {
    background-color: var(--color-blumy);
}
.status-shipping {
    background-color: var(--color-yelly);
}
.status-waiting {
    background-color: var(--color-rangy);
}
.status-pending {
    background-color: var(--color-rangy);
}
.status-unreaching {
    background-color: var(--color-gorry);
}
.status-returned {
    background-color: var(--color-ioly);
}
.status-canceled {
    background-color: var(--color-rady);
}
.status-completed {
    background-color: var(--color-greeny);
}



.noteBox {
  width: 100%;
  min-height: 20px;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  border-end-end-radius: 4px;
  border-end-start-radius: 4px;
}

.order-details {
  width: 90%;
  margin-top: 14px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 10px;
}

/* --- Neumorphic copyCard --- */
.copyCard {
  width: 100%;
  padding: 10px;
  border-radius: 14px;
  background: var(--color-whity);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;
  text-align: left;
}

.dark .copyCard {
  background: var(--color-darky);
}

.copyCard h4 {
  width: 100%;
  height: 100%;
  font-size: 16px;
  font-weight: 700;
  color: var(--color-zioly1);
  display: flex;
  justify-content: center;
  align-items: center;
}

.copyCard p {
  margin: 2px 0;
  font-size: 14px;
  font-weight: 500;
}
.copyCard p b {
  margin-inline: 5px;
}

.rowFlex {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.columnFlex {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: left;
  flex-direction: column;
}

.ci {
  padding: 10px;
  border-radius: 50%;
  background-color: var(--color-darkow20);
  cursor: pointer;
}
.dark .ci {
  background-color: var(--color-whitly20);
}



/* --- Grid Layout --- */
.grid2 {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

@media (min-width: 768px) {
  .grid2 {
    grid-template-columns: 1fr 1fr;
  }
}

/* --- Produits --- */
.products ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* --- Carte Produit --- */
.product {
  width: 100%;
  display: flex;
  gap: 16px;
  padding: 16px;
  border-radius: 16px;
  background: var(--color-whity);
  transition: transform 0.2s ease, box-shadow 0.3s ease;
}


.dark .product {
  background: var(--color-darky);
}

.product-img-wrapper {
  flex-shrink: 0;
  width: 90px;
  height: 90px;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* --- Infos produit --- */
.product-info {
  flex: 1;
  display: flex;
  justify-content: left;
  gap: 8px;
}

.product-name {
  font-weight: 700;
  font-size: 16px;
  margin: 0;
}

.qty {
  font-size: 13px;
  color: var(--color-zioly2);
}

/* --- Détails (taille, couleur, qty) --- */
.sub-item {
  border-radius: 12px;
  padding: 8px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Tags */
.tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.tag {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 8px;
  font-weight: 600;
  background: #fff;
  box-shadow: -2px -2px 4px #ffffff,
              2px 2px 4px #c9c9c9;
}

.dark .tag {
  background: #2a2a2a;
  box-shadow: -2px -2px 4px rgba(255,255,255,0.05),
              2px 2px 4px rgba(0,0,0,0.7);
}

.color-dot {
  display: inline-block;
  width: 12px;
  height: 12px;
  border-radius: 4px;
  margin-right: 4px;
  border: 1px solid #ccc;
}

/* --- Prix --- */
.price {
  display: flex;
  align-items: baseline;
  gap: 8px;
  margin-top: 4px;
}

.price .old {
  font-size: 13px;
  color: #888;
  text-decoration: line-through;
}

.price .new {
  font-size: 15px;
  font-weight: 700;
  color: var(--color-greeny);
}

.price .promo .new {
  color: #e63946; /* Rouge promo */
}


/* --- Total --- */
.total {
  text-align: right;
}

.total h3 {
  font-size: 18px;
  font-weight: 800;
  color: var(--color-greeny);
}

.floatingBtn {
  width: 100%;
  display: flex;
  justify-content: right;
  align-items: center;
  gap: 5px;


}

.radioBtn {
  background-color: var(--color-whitly);
  backdrop-filter: 20px;
  box-shadow: 
     2px  2px 4px 1px #aca7afc2;
  transform: translateY(-2px); /* bouton ressorti */
  height: 25px;
  width: 25px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
}

.dark .radioBtn {
  background-color: var(--color-darkly);
  box-shadow: 
      2px 2px 4px 1px rgba(0, 0, 0, 0.761);
}



</style>
