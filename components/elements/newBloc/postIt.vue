<script setup>
import { ref, watch, onMounted } from 'vue';
import RectBtn from '../../elements/newBloc/rectBtn.vue';
import iconsFilled from '../../../public/iconsFilled.json';


const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // Tableau de notes
  color: { type: String, default: '#ffef6c' },
  size: { type: [Number, String], default: 200 },
  rotate: { type: Number, default: 0 },
  auth: {type: Object, default: {}}
});



const webLink = 'https://management.hoggari.com/uploads/profile/'

const emit = defineEmits(['update:modelValue']);
const newNoteRef = ref(null);

const resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`);
};

onMounted(() => {
  if (!newNoteRef.value) {
    console.warn('newNoteRef n’est pas encore défini');
  }
});

const noteListRef = ref(null);

// Scroll automatique vers le bas à chaque ajout
watch(() => props.modelValue.length, () => {
  if (noteListRef.value) {
    noteListRef.value.scrollTop = noteListRef.value.scrollHeight;
  }
});



const addNote = () => {
  if (!newNoteRef.value) {
    console.warn('newNoteRef non défini');
    return;
  }
  const text = newNoteRef.value.innerText.trim();

  if (!text) return;

  const newNotes = [
    ...props.modelValue,
    { text: text, user: props.auth.username, profile_image: props.auth.profile_image, color: props.color }
  ];

  emit('update:modelValue', newNotes);

  newNoteRef.value.innerText = '';
};



// Supprimer une note
const deleteNote = (index) => {
  const newNotes = [...props.modelValue];
  newNotes.splice(index, 1);
  emit('update:modelValue', newNotes);
};

// Modifier une note existante
const editNote = (index, text) => {
  const newNotes = [...props.modelValue];
  newNotes[index].text = text;
  emit('update:modelValue', newNotes);
};
</script>

<template>
  <div class="postitClick" :style="{ width: '100%', height: size + 'px', transform: `rotate(${rotate}deg)` }">
    <div class="postit-body" :style="{ backgroundColor: color }">
      <!-- Anciennes notes intégrées -->
      <div class="note-list">
        <div v-for="(note, index) in modelValue" :key="index" class="note-column">
          <div class="note-row">
            <div class="mini-row">
              <img class="circleImg"
               :src="note.profile_image ? `${webLink}${note.profile_image}` : `${webLink}default.png`"
               :alt="note.user || 'User'" />
              <span class="note-user">{{ note.user }}:</span>
            </div>
            
            <RectBtn iconColor="#ff5555" svg="trashX" @click:ok="deleteNote(index)" />
          </div>
          <div class="note-text" contenteditable="true"
               @blur="editNote(index, $event.target.innerText)">{{ note.text }}
          </div>
          
        </div>
      </div>
      <!-- Zone d'ajout d'une nouvelle note -->
      <div 
        ref="newNoteRef" 
        class="postit-content" 
        contenteditable="true"
        placeholder="Ajouter une note..."
        @keydown.enter.prevent="addNote"
      ></div>

      <!-- Bouton + pour ajouter la note -->
      <button class="plus-icon" @click="addNote">
        <span v-html="resizeSvg(iconsFilled['plus'], 30, 30)"></span>
      </button>


    </div>
  </div>
</template>


<style scoped>
.circleImg {
  width: 32px;           /* largeur de l'image */
  height: 32px;          /* hauteur égale pour un cercle parfait */
  border-radius: 50%;    /* rend l'image circulaire */
  object-fit: cover;     /* recadre l'image pour remplir le cercle sans déformation */
  border: 2px solid #fff; /* optionnel : bord blanc autour de l'image */
  box-shadow: 0 1px 3px rgba(0,0,0,0.3); /* léger ombrage pour le relief */
  margin-right: 6px;     /* espace entre l'image et le texte */
  flex-shrink: 0;        /* empêche l'image de rétrécir dans un flex container */
}


.note-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* PostIt ajout note */
.postitClick {
  display: inline-block;
  position: relative;
  cursor: pointer;
  transition: transform 0.12s ease;
}

.postit-body {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  box-shadow: 
      2px  2px 2px 0px #aca7afc2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  position: relative;
}
.dark .postit-body {
  box-shadow: 
        2px 2px 2px 0px rgba(0, 0, 0, 0.761);
}

.postit-content {
  width: 100%;
  min-height: 50px;
  outline: none;
  white-space: pre-wrap;
  overflow: auto;
  font-size: 14px;
  line-height: 1.3;
  color: #222;
  background: var(--color-yelly20);
  caret-color: #111;
  border-radius: 8px;
}

.plus-icon {
  position: absolute;
  bottom: 8px;
  right: 8px;
  color: var(--color-darkly);
}

.note-list {
  width: 100%;
  flex: 1; /* prend tout l'espace disponible */
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: var(--color-darkly);
  overflow-y: auto;   /* scroll vertical */
  padding-right: 4px; /* pour éviter que le scroll cache le contenu */
}

/* Optionnel : scroll fin et discret */
.note-list::-webkit-scrollbar {
  width: 6px;
}

.note-list::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.2);
  border-radius: 3px;
}


.note-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 8px;
  border-bottom: 1px solid var(--color-darkly);

}

.mini-row {
  display: flex;
  align-items: center;
  justify-content: center;

}

.note-column {
  display: flex;
  flex-direction: column;
  align-items: left;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 6px;
}

.note-user {
  font-weight: 600;
}

.note-text {
  flex: 1;
  min-width: 50px;
  outline: none;
  white-space: pre-wrap;
  
}
</style>
