<template>
  <div class="container">
    <h1>{{ isEdit ? 'Modifier' : 'Créer' }} une méthode de transaction</h1>

    <form @submit.prevent="onSubmit" class="form">
      <label>
        Nom
        <input v-model="form.name" required />
      </label>

      <label>
        Type
        <input v-model="form.type" placeholder="ex: payout, deposit" />
      </label>

      <section class="list-block">
        <h2>Gains (banque_id_win)</h2>
        <div v-for="(item, idx) in form.banque_id_win" :key="idx" class="list-item">
          <select v-model.number="item.banque_id">
            <option :value="null">-- choisir banque --</option>
            <option v-for="b in banks" :key="b.id" :value="b.id">{{ b.name }} ({{ b.currency }})</option>
          </select>

          <input type="number" step="0.01" v-model.number="item.value" placeholder="montant ou pourcentage" />

          <select v-model="item.type">
            <option value="fix">fix</option>
            <option value="%">%</option>
          </select>

          <button type="button" @click="removeWin(idx)">Supprimer</button>
        </div>

        <button type="button" @click="addWin">+ Ajouter un gain</button>
      </section>

      <section class="list-block">
        <h2>Pertes (banque_id_lose)</h2>
        <div v-for="(item, idx) in form.banque_id_lose" :key="idx" class="list-item">
          <select v-model.number="item.banque_id">
            <option :value="null">-- choisir banque --</option>
            <option v-for="b in banks" :key="b.id" :value="b.id">{{ b.name }} ({{ b.currency }})</option>
          </select>

          <input type="number" step="0.01" v-model.number="item.value" placeholder="montant ou pourcentage" />

          <select v-model="item.type">
            <option value="fix">fix</option>
            <option value="%">%</option>
          </select>

          <button type="button" @click="removeLose(idx)">Supprimer</button>
        </div>

        <button type="button" @click="addLose">+ Ajouter une perte</button>
      </section>

      <div class="actions">
        <button type="submit">{{ isEdit ? 'Mettre à jour' : 'Créer' }}</button>
        <button type="button" @click="resetForm">Annuler</button>
      </div>

      <div v-if="message" class="message">{{ message }}</div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const banks = ref([]);
const message = ref('');

const form = ref({
  id: null,
  name: '',
  type: '',
  // arrays of objects: { banque_id, value, type }
  banque_id_win: [],
  banque_id_lose: []
});

const isEdit = computed(() => !!form.value.id);

function emptyEntry() {
  return { banque_id: null, value: null, type: 'fix' };
}

function addWin() { form.value.banque_id_win.push(emptyEntry()); }
function addLose() { form.value.banque_id_lose.push(emptyEntry()); }
function removeWin(i) { form.value.banque_id_win.splice(i, 1); }
function removeLose(i) { form.value.banque_id_lose.splice(i, 1); }

async function loadBanks() {
  try {
    // appelle l'API finance.php?action=listbanks (ajuste l'URL si besoin)
    const res = await fetch('/backend/finance.php?action=listbanks');
    const json = await res.json();
    if (json && json.success && Array.isArray(json.data)) {
      banks.value = json.data;
    } else {
      // si la structure est différente, adapte
      banks.value = json.data || [];
    }
  } catch (err) {
    console.error('Erreur loadBanks', err);
  }
}

async function loadModel(id) {
  try {
    // Charge le model existant via endpoint PHP (GET by id)
    const res = await fetch(`/backend/transaction_model.php?id=${id}`);
    const json = await res.json();
    if (json && json.success && json.data) {
      const d = json.data;
      form.value.id = d.id;
      form.value.name = d.name;
      form.value.type = d.type;
      // Les colonnes banque_id_win/lose sont stockées en JSON string dans la DB
      try {
        form.value.banque_id_win = JSON.parse(d.banque_id_win || '[]');
      } catch (e) {
        form.value.banque_id_win = [];
      }
      try {
        form.value.banque_id_lose = JSON.parse(d.banque_id_loos || '[]');
      } catch (e) {
        form.value.banque_id_lose = [];
      }
    }
  } catch (err) {
    console.error('Erreur loadModel', err);
  }
}

async function onSubmit() {
  message.value = '';

  // Validation basique
  if (!form.value.name.trim()) {
    message.value = 'Le nom est requis.';
    return;
  }

  // Préparer payload (sérialiser les listes en JSON)
  const payload = {
    id: form.value.id || undefined,
    name: form.value.name,
    type: form.value.type,
    banque_id_loos: JSON.stringify(form.value.banque_id_lose),
    banque_id_win: JSON.stringify(form.value.banque_id_win)
  };

  try {
    const res = await fetch('https://management.hoggari.com/backend/finance.php?action=addTransactionModel', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if (json && json.success) {
      message.value = json.message || 'Enregistré.';
      // si création -> redirect / reload list
      setTimeout(() => {
        router.push({ name: 'transaction-models-list' }).catch(()=>{});
      }, 600);
    } else {
      message.value = json.message || 'Erreur serveur.';
    }
  } catch (err) {
    console.error('Erreur submit', err);
    message.value = 'Erreur de communication.';
  }
}

function resetForm() {
  form.value = { id: null, name: '', type: '', banque_id_win: [], banque_id_lose: [] };
}

onMounted(async () => {
  await loadBanks();

  const id = route.query.id || null;
  if (id) {
    form.value.id = Number(id);
    await loadModel(form.value.id);
  } else {
    // avoir au moins une ligne par défaut
    addWin(); addLose();
  }
});
</script>

<style scoped>
.container { max-width:900px; margin:24px auto; padding:16px; border:1px solid #eee; border-radius:8px; }
.form label { display:block; margin-bottom:12px; }
.form input, .form select { width:100%; padding:8px; margin-top:6px; box-sizing:border-box; }
.list-block { margin-top:18px; padding:12px; background:#fafafa; border-radius:6px; }
.list-item { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
.list-item select, .list-item input { flex:1; }
.actions { margin-top:16px; display:flex; gap:8px; }
.message { margin-top:12px; color:green }
</style>

