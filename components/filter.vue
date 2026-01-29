<template>

  <div class="filter-wrapper" @click.self="emit('close')">
    <div class="filterBox">

      <div class="rawBox">
        <h1>{{ t('Filter Orders') }}</h1>
        <Btn svg="x" iconColor="#ff5555" @click:ok="emit('close')" :isSimple="true" />
      </div>

      <div class="scroll-container">

        <!-- ✅ GRID des statuts -->
        <label class="section-label">{{ t('Status') }}</label>
        <div class="status-grid">
            <div
            v-for="st in statusMap"
            :key="st.value"
            class="boxColumn"
            @click="selectStatus(st.value)"
            >
                <div class="status-item" :class="['status-' + st.value, { active: filters.status === st.value }]">
                    <div v-if="st.svg" v-html="resizeSvg(st.svg, 24, 24)"></div>
                </div>
                <div style="font-size: 11px; margin-top: 5px; text-align:center;">
                    {{ t(st.label) }}
                </div>

            </div>
        </div>

        <div class="divider"></div>

        <!-- Inputs -->
        <div class="boxColumn">

            <div class="input-group full-width">
                <label>{{ t('Date Period') }}</label>
                <VueDatePicker
                    v-model="dateRange"
                    range
                    :enable-time-picker="false"
                    :dark="isDark"
                    format="yyyy-MM-dd"
                    placeholder="Select Date Range"
                />
            </div>

            <div class="input-group full-width">
                 <Selector
                    v-model="filters.product_id"
                    :options="productOptions"
                    :placeHolder="t('All Products')"
                    :img="icons['package']"
                    class="full-width-selector"
                 />
            </div>

            <div class="inputs-grid">
                <div class="input-group">
                    <Selector
                        v-model="filters.wilaya"
                        :options="wilayaOptions"
                        :placeHolder="t('All Wilayas')"
                        :img="icons['location']"
                    />
                </div>
                <div class="input-group">
                    <Inputer
                        v-model="filters.commune"
                        :placeHolder="t('Commune')"
                        :img="icons['location']"
                    />
                </div>
            </div>

            <div class="input-group full-width">
                <Selector
                    v-model="filters.method"
                    :options="deliveryOptions"
                    :placeHolder="t('Delivery Company')"
                    :img="icons['truck']"
                    class="full-width-selector"
                 />
            </div>

            <div class="input-group full-width">
                <label>{{ t('Price Range') }}</label>
                <div class="price-range">
                    <Inputer type="number" v-model="filters.min_price" :placeHolder="t('Min')" :img="icons['money']" />
                    <span>-</span>
                    <Inputer type="number" v-model="filters.max_price" :placeHolder="t('Max')" :img="icons['money']" />
                </div>
            </div>

        </div>

        <div class="actions">
          <Btn :text="t('apply filters')" svg="check" color="var(--color-greeny)" @click:ok="applyFilters" style="width: 100%;" />
          <Btn :text="t('clear filters')" svg="clear" icon-color="var(--color-rady)" @click:ok="resetFilters" style="width: 100%;" />
        </div>

      </div>

      

    </div>
  </div>

</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Btn from './elements/newBloc/rectBtn.vue'
import Selector from './elements/bloc/select.vue'
import Inputer from './elements/bloc/input.vue'
import icons from '~/public/icons.json'
import iconsFilled from '~/public/iconsFilled.json'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import { useLang } from '~/composables/useLang'

const { t } = useLang()

const emit = defineEmits(['close', 'selected'])
const props = defineProps({
    wilayas: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] }
})

// Detect Dark Mode (assuming class 'dark' on html or body)
const isDark = ref(false)
if (process.client) {
    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDark.value = document.documentElement.classList.contains('dark');
}

const filters = ref({
    status: 'all',
    start_date: '',
    end_date: '',
    wilaya: '',
    commune: '',
    method: '',
    min_price: '',
    max_price: '',
    product_id: ''
})

const dateRange = ref(null)

// Watch dateRange to update start/end
watch(dateRange, (newVal) => {
    if (newVal && Array.isArray(newVal) && newVal.length === 2 && newVal[0] && newVal[1]) {
        filters.value.start_date = newVal[0].toISOString().split('T')[0]
        filters.value.end_date = newVal[1].toISOString().split('T')[0]
    } else {
        filters.value.start_date = ''
        filters.value.end_date = ''
    }
})

const resizeSvg = (svg, width, height) => {
    if(!svg) return ''
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}

const statusMap = ref([
  { label: 'All', value: 'all', svg: icons['list'] },
  { label: 'Confirmed', value: 'confirmed', svg: iconsFilled['thumb-up'] },
  { label: 'Canceled', value: 'canceled', svg: iconsFilled['x'] },
  { label: 'Pending', value: 'waiting', svg: iconsFilled['alarm'] },
  { label: 'Completed', value: 'completed', svg: iconsFilled['check'] },
  { label: 'Unreaching', value: 'unreaching', svg: iconsFilled['phone'] },
  { label: 'Returned', value: 'returned', svg: iconsFilled['back'] },
  { label: 'Shipping', value: 'shipping', svg: iconsFilled['truck'] },
])

const productOptions = computed(() => {
    const opts = [{ label: t('All Products'), value: '' }];
    if (props.products) {
        props.products.forEach(p => {
            // Check if image is URL or path
            let img = p.image;
            if (img && !img.startsWith('http') && !img.startsWith('<svg')) {
                 img = 'https://management.hoggari.com' + img;
            }
            // Actually Selector expects relative or full path, let's just pass what we have.
            // Note: Selector implementation uses `src="/${props.modelImage}"`. If we pass a full URL, `/https://...` breaks.
            // Wait, Selector implementation:
            // <img v-if="!selectedImage && props.modelImage" :src="`/${props.modelImage}`" ... />
            // <img v-else-if="selectedImage" :src="`/${selectedImage}`" ... />
            // It prepends `/`. This is problematic for full URLs.
            // But `Selector.vue` also checks `isSvgString`.
            // If `option.img` is passed, `selectedImage` uses it.
            // Code: `<img v-if="option.img && !isSvgString(option.img)" class="img-circle" :src="`/${option.img}`" alt="icon" />`
            // It forces `/`.
            // BUT, `pages/products/index.vue` displays images.
            // The `Selector` seems designed for internal icons in public/ dir.
            // If I want to use product images, I might need to hack the URL or accept that it might be broken if I can't modify Selector.
            // However, the `Selector` component is used for status selection which uses SVG strings (icons).
            // Let's look at `products/index.vue` again. Images are `https://management.hoggari.com...`.
            // If I pass `option.img`, `Selector` renders it with `<img :src="/${option.img}">`.
            // This effectively breaks external URLs.
            // I should stick to using a generic package icon for products if I can't fix Selector, OR I assume `Selector` handles it if I didn't read it carefully.
            // Re-reading Selector.vue:
            // `:src="\`/\${option.img}\`"`
            // Yes, it enforces root relative path.
            // I'll leave `img` undefined for products options to avoid broken images, or use a generic icon.
            // The user said "utiliser les composant du project".
            // I'll rely on the default behavior or maybe passing a generic icon is safer.
            // I'll pass no image for product items, just label.

            opts.push({
                label: p.name,
                value: p.id
                // img: p.image
            });
        });
    }
    return opts;
});

const wilayaOptions = computed(() => {
    const opts = [{ label: t('All Wilayas'), value: '' }];
    if (props.wilayas) {
        props.wilayas.forEach(w => {
            opts.push({
                label: `${w.wilaya_id} - ${w.wilaya_name}`,
                value: w.wilaya_name
            });
        });
    }
    return opts;
});

const deliveryOptions = computed(() => [
    { label: t('All'), value: '' },
    { label: 'Yalidine', value: 'yalidine' },
    { label: 'Guepex', value: 'guepex' },
    { label: 'Anderson', value: 'anderson' },
    { label: 'UPS', value: 'ups' },
    { label: 'ZR Express', value: 'zr' }
]);

const selectStatus = (val) => {
    filters.value.status = val
}

const applyFilters = () => {
    emit('selected', { ...filters.value })
    emit('close')
}

const resetFilters = () => {
    filters.value = {
        status: 'all',
        start_date: '',
        end_date: '',
        wilaya: '',
        commune: '',
        method: '',
        min_price: '',
        max_price: '',
        product_id: ''
    }
    dateRange.value = null
}

</script>

<style scoped>

/* ✅ Wrapper */
.filter-wrapper {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  display: flex; justify-content: center; align-items: center;
  background-color: rgba(20, 20, 20, 0.5);
  backdrop-filter: blur(4px);
  z-index: 3000;
  padding: 20px;
}

/* ✅ Box */
.filterBox {
  width: 100%;
  max-width: 600px;
  max-height: 80vh;
  background-color: var(--color-whitly);
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  display: flex; flex-direction: column; gap: 15px;
}

.dark .filterBox { background-color: var(--color-darkly); }

.scroll-container {
    overflow-y: auto;
    flex: 1;
    padding-right: 5px;
}

.rawBox {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.rawBox h1 { font-size: 1.5rem; font-weight: 700; }

.section-label {
    font-weight: 700; margin-bottom: 15px; display: block;
    color: var(--color-zioly4);
    font-size: 1.1rem;
}

/* ✅ Grille des cercles */
.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  gap: 15px;
  width: calc(100% - 30px);
}

/* ✅ Boutons circulaires */
.status-item {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  text-align: center;
  font-weight: 600;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;
  color: #fff;
}

.status-item:hover {
  transform: scale(1.1);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.status-item.active {
    border: 3px solid var(--color-darky);
    transform: scale(1.15);
}
.dark .status-item.active {
    border: 3px solid var(--color-whitly);
}

.boxColumn {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 10px;
}

.divider {
    height: 1px;
    background-color: #eee;
    margin-block: 25px;
}
.dark .divider {
    background-color: #333;
}

/* Inputs */
.inputs-grid {
    display: flex;
    gap: 20px;
    width: 100%;
}
.inputs-grid > .input-group {
    flex: 1;
}

.input-group {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-group label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
}
.dark .input-group label { color: #aaa; }


.price-range {
    display: flex;
    align-items: center;
    gap: 10px;
}
.price-range span { color: #888; }

.actions {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Overrides for Selectors to fill width */
:deep(.floating-input3), :deep(.floating-input2) {
    width: 100% !important;
    max-width: 100% !important;
    margin-inline: 0 !important;
}

/* ✅ Couleurs dynamiques */
.status-all   { background-color: var(--color-zioly4) }
.status-confirmed   { background-color: var(--color-blumy) }
.status-shipping    { background-color: var(--color-yelly) }
.status-waiting     { background-color: var(--color-rangy) }
.status-pending     { background-color: var(--color-rangy) }
.status-unreaching  { background-color: var(--color-gorry) }
.status-returned    { background-color: var(--color-ioly) }
.status-canceled    { background-color: var(--color-rady) }
.status-completed   { background-color: var(--color-greeny) }

@media (max-width: 600px) {
    .inputs-grid {
        flex-direction: column;
    }
}
</style>
