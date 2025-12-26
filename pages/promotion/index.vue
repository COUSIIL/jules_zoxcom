<script setup>
import { onMounted } from 'vue'
import { useMeta } from '../../composables/metaManager';

const meta = useMeta()

onMounted(async () => {
  await meta.fetchPages()
  await meta.fetchAdAccounts()
})
</script>

<template>
  <div>
    <button @click="meta.login">Login Meta</button>

    <h3>Mes pages :</h3>
    <div v-for="p in meta.pages" :key="p.id">{{ p.name }}</div>

    <h3>Mes comptes publicitaires :</h3>
    <div v-for="a in meta.adAccounts" :key="a.id">{{ a.id }}</div>
  </div>
</template>
