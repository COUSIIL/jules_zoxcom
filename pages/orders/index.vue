<template>
  <Loader v-if="loading" width="80px" />
  <Confirm :isVisible="showConfirm" @confirm="deleteOrder(idToEdit, indexToEdit), showConfirm = false" @cancel="cancelConfirmDelete"/>

  <Selector :options="statusOptions" :showIt="showStatus" :disabled="true" @close="showStatus = false" @update:modelValue="editStatus"/>

  <nav v-if="showDeliver" class="overlay">
    <Deliver
      v-if="!isShipping"
      :isVisible="showDeliver"
      :_name="nameDeliver"
      :_phone1="phoneDeliver"
      :_total="totalDeliver"
      :_indexing="indexDeliver"
      @confirm="shipping"
      @cancel="cancelShipping"
    />
    <div v-else-if="isShipping">
      <Loader width="80px"/>
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
                <p class="text">{{ dts.deliveryZone }} - {{ dts.sZone }}</p>
              </div>
            </div>
          </div>


                
                
                
                
            
            <SquareBtn icon="phone" width="24" height="24" @click:ok="hrefLink(dts.phone)"/>

          </div>

          <div
            v-if="Array.isArray(dts.note) && dts.note.length > 0 && dts.note.some(n => n?.text?.trim()) && !dts.isMore"
            :class="['noteBox', dts.status ? `status-${dts.status.toLowerCase()}` : '']"
          >



            <Note :speed="80" :gap="48">
              <div v-for="noti in dts.note" >
                <div v-if="noti?.text?.trim()" class="rowFlex2">
                  <img
                    class="circleImg"
                    :src="noti?.profile_image ? `https://management.hoggari.com/uploads/profile/${noti.profile_image}` : 'https://management.hoggari.com/uploads/profile/default.png'"
                    :alt="noti?.profile_image || t('user profile')"
                  />
                  <span v-if="noti.user" class="note-user"> {{ noti.user }} : </span>
                  <span class="note-text">{{ noti.text }}</span>
                  
                  
                </div>
                
              </div>
              
            </Note>
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
                  <RectBtn svg="edit" @click:ok=""/>
                </div>
                
                <p><b>Wilaya:</b> {{ dts.deliveryZone }}</p>
                <p><b>Commune:</b> {{ dts.sZone }}</p>
                <p><b>Adresse:</b> {{ dts.mZone }}</p>
                
              </div>

              <div class="copyCard infos">
                
                <div class="rowFlex">
                  <h4>{{t('customer information')}}</h4>
                  <RectBtn svg="edit" @click:ok=""/>
                </div>
                <p><b>Nom:</b> {{ dts.name }}</p>
                <p><b>Téléphone:</b> {{ dts.phone }}</p>
                <p><b>Date:</b> {{ dts.create }}</p>
              </div>

              <div class="copyCard infos">
                
                <div class="rowFlex">
                  <h4>{{t('delivery')}}</h4>
                  <RectBtn svg="edit" @click:ok=""/>
                </div>
                <p><b>{{t('deliver name')}}:</b> {{ dts.method }}</p>
                <p v-if="dts.type == 0"><b>{{t('delivery type')}}:</b> {{t('home')}}</p>
                <p v-else><b>{{t('delivery type')}}:</b> {{t('stop desk')}}</p>
                <p><b>{{t('fees')}}:</b> {{ dts.deliveryValue }} DA</p>
                <p><b>{{t('Tracking Code')}}:</b> {{dts.tracking}}</p>
                <p><b>{{t('activity')}}:</b> <div class="activityText" v-if="dts.activity">{{dts.activity}}</div></p>



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
            <div class="note-section">
              
              <div v-for="(note, noteIndex) in dts.note" :key="noteIndex" class="note-wrapper">
                <PostIt
                  v-if="note.text"
                  v-model="note.text"
                  :color="note.isClientNote ? '#d6e7ff' : note.color"
                  :size="300"
                  :rotate="noteIndex % 2 === 0 ? -2 : 2"
                  @update:modelValue="(value) => editNote(dts.id, dts.note)"
                />
                <div class="note-info" v-if="note.text">
                  <div class="rowFlex2">
                    <img
                      class="circleImg"
                      :src="note?.profile_image ? `https://management.hoggari.com/uploads/profile/${note.profile_image}` : 'https://management.hoggari.com/uploads/profile/default.png'"
                      :alt="note?.profile_image || t('user profile')"
                    />

                    <span class="note-user">{{ note.user }}</span>
                  </div>
                  
                  <RectBtn iconColor="#ff5555" svg="trashX" @click:ok="deleteNote(index, noteIndex)"/>
                </div>
              </div>
              <PostIt
                  :isBtn="true"
                  :size="300"
                  :rotate="noteIndex % 2 === 0 ? -2 : 2"
                  @update:modelValue="addNote(index)"
                />

              
            </div>

            <!-- Total -->
            <div class="copyCard total">
              <h3>Total: {{ dts.total }} DA</h3>
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


const { data, loading, getOrders, deleteOrder, updateOrderValue, deliverOrder, viewTracking } = useOrders();
const idToEdit = ref(0)
const statusID = ref(0)
const statusIndex = ref(0)
const indexToEdit = ref(0)
const showConfirm = ref(false)
const currentColor = ref('#ffef6c')

const showDeliver = ref(false);
const isShipping = ref(false);
const nameDeliver = ref([]);
const phoneDeliver = ref([]);
const totalDeliver = ref([]);
const indexDeliver = ref([]);

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
  if (vl === 'shipping') {
    const order = limitedDt.value[statusIndex.value];
    nameDeliver.value = [order.name];
    phoneDeliver.value = [order.phone];
    totalDeliver.value = [order.total];
    indexDeliver.value = [statusIndex.value];
    showDeliver.value = true;
  } else {
    await updateOrderValue(statusID.value, 'status', vl);
  }
};

const shipping = async ({ name, phone1, phone2, note, total, indexing }) => {
  isShipping.value = true;
  for (let i = 0; i < indexing.length; i++) {
    const orderIndex = indexing[i];
    const order = limitedDt.value[orderIndex];
    order.name = name[i];
    order.phone = phone1[i];
    // order.phone2 = phone2[i]; // Add if you have a second phone field
    order.note.push({ text: `Note de livraison: ${note[i]}`, user: 'system', isClientNote: false, color: '#d6ffdf' });
    order.total = total[i];

    await deliverOrder(order, order.items, order.type === 1 ? 'Home' : 'Stop Desk', order.method, order.total, order.deliveryValue, order.deliveryZone);

  }
  showDeliver.value = false;
  isShipping.value = false;
};

const cancelShipping = () => {
  showDeliver.value = false;
};

const editNote = async (id, note) => {
  let noteJson;
  const authData = JSON.parse(localStorage.getItem('auth'));

  try {
    if (Array.isArray(note)) {
      // ✅ Si c’est un tableau de notes
      noteJson = JSON.stringify(
        note.map(n => ({
          text: n.text ?? '',
          user: n.user ?? null,
          // 🧠 Si user existe => image du user connecté, sinon vide
          profile_image: n.user ? authData?.profile_image || '' : '',
          isClientNote: !!n.isClientNote,
          color: n.color ?? '#fff'
        }))
      );
    } else {
      // ⚠ Note simple (client)
      noteJson = JSON.stringify([
        {
          text: typeof note === 'string' ? note : JSON.stringify(note),
          user: null,
          isClientNote: true,
          profile_image: '', // ❌ pas d’image pour le client
          color: '#fff'
        }
      ]);
    }

    await updateOrderValue(id, 'note', noteJson);
  } catch (error) {
    console.error('Erreur lors de la mise à jour des notes :', error);
  }
};



const addNote = (orderIndex) => {
  const authData = JSON.parse(localStorage.getItem('auth'));
  const currentUser = authData ? authData.username : 'unknown';

  const newNote = {
    text: 'New Note',
    user: currentUser,
    isClientNote: false,
    profile_image: authData.profile_image,
    color: '#ffef6c'
  };
  limitedDt.value[orderIndex].note.push(newNote);
};

const deleteNote = (orderIndex, noteIndex) => {
  limitedDt.value[orderIndex].note.splice(noteIndex, 1);
  editNote(limitedDt.value[orderIndex].id, limitedDt.value[orderIndex].note);
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
        let note = [];

        try {
          // ✅ Si item.note est une chaîne JSON valide
          if (typeof item.note === 'string') {
            const parsed = JSON.parse(item.note);

            if (Array.isArray(parsed)) {
              note = parsed.map(note => ({
                text: note.text ?? '',
                user: note.user ?? null,
                isClientNote: !!note.isClientNote,
                profile_image: note.profile_image,
                color: note.color ?? '#d6e7ff'
              }));
            } else if (parsed && typeof parsed === 'object') {
              // ✅ Si c’est un seul objet
              note = [{
                text: parsed.text ?? '',
                user: parsed.user ?? null,
                isClientNote: !!parsed.isClientNote,
                profile_image: parsed.profile_image,
                color: parsed.color ?? '#d6e7ff'
              }];
            } else if (parsed) {
              // ✅ Si c’est autre chose (string, nombre, etc.)
              note = [{
                text: String(parsed),
                user: null,
                isClientNote: true,
                profile_image: '',
                color: '#d6e7ff'
              }];
            }
          } else if (Array.isArray(item.note)) {
            // ✅ Déjà un tableau
            note = item.note.map(note => ({
              text: note.text ?? '',
              user: note.user ?? null,
              isClientNote: !!note.isClientNote,
              profile_image: note.profile_image,
              color: note.color ?? '#d6e7ff'
            }));
          } else if (item.note) {
            // ✅ Chaîne brute ou autre
            note = [{
              text: String(item.note),
              user: null,
              isClientNote: true,
              profile_image: '',
              color: '#d6e7ff'
            }];
          }
        } catch (e) {
          // ⚠ JSON invalide → note client simple
          note = [{
            text: String(item.note ?? ''),
            user: null,
            isClientNote: true,
            profile_image: '',
            color: '#d6e7ff'
          }];
        }

        return {
          ...item,
          isMore: false,
          isSelected: false,
          copiedId: false,
          activity: '',
          note
        };
      })
      .reverse();

    limitedDt.value = dt.value.slice(0, limit.value);
  }
};


const doMore = async (val) => {
  limitedDt.value[val].isMore = !limitedDt.value[val].isMore
  
  if(limitedDt.value[val].isMore === true && limitedDt.value[val].tracking) {
    limitedDt.value[val].activity = await viewTracking(limitedDt.value[val].tracking)
  }
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
}

/* Liste */
.uler {

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
  margin-bottom: 20px;

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
.rowFlex2 {
  width: 100%;
  display: flex;
  justify-content: left;
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

.note-section {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  margin-top: 16px;
}

.note-wrapper {
  position: relative;
}

.note-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
}
.note-text {
  font-size: 12px;
}
.note-user {
  font-size: 14px;
  font-weight: bold;
  margin-inline: 2px;
}
.activityText {
  font-size: 14px;
  color: var(--color-yelly);
  margin-inline: 2px;
}

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.circleImg {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  margin: 5px;
  object-fit: cover;        /* garde les proportions */
  object-position: center;  /* centre le contenu */
}
</style>
