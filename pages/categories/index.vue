<template>
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>

    <div class="flex flex-col items-center max-w-full">
        <div class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
            <div class="flex items-center justify-center mx-2.5 gap-1.25">
                <div v-html="resizeSvg(icons['category'], 20, 20)"></div>
                <h1>{{ newCategoryList.length }} {{t('categories')}}</h1>
            </div>
            <CallToAction :text="t('add category')" :svg="icons['add']" @clicked="activate(true)"/>
        </div>

        <EditCat v-if="addCat" @cancel="activate(false)" @saved="saved"/>

        <div v-for="(element, index) in newCategoryList" :key="index">
            <div class="flex flex-col items-end justify-between w-full max-w-3xl min-w-[300px] p-1.25 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md cursor-pointer bg-whitly dark:bg-darkly">
                <button type="button" class="flex items-center justify-start gap-2.5 bg-whizy dark:bg-darkow rounded-lg" @click="more(index)">
                    <img :src="element.image" :alt="element.name" class="max-w-[100px] min-w-[100px] max-h-[100px] min-h-[100px] rounded-full">
                    <div class="flex flex-col items-center justify-start w-full overflow-hidden text-xl font-bold text-ellipsis whitespace-nowrap">
                        {{ element.name }}
                        <div class="overflow-hidden text-xs font-bold text-gorry text-ellipsis whitespace-nowrap dark:text-garry">
                            {{ t('category') }}
                        </div>
                    </div>
                    <div class="flex items-center justify-center min-w-[25px] min-h-[25px] m-1.25 rounded-full bg-rady" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(element.value)"></div>
                </button>
                <div v-if="element.more">
                    <div v-for="(sub, index2) in element.subCategories" :key="index2">
                        <button type="button" class="flex items-center justify-start w-full gap-2.5 m-1.25 rounded-lg bg-whizy dark:bg-darkow" @click="moreSub(index, index2)">
                            <img :src="sub.image" :alt="sub.name" class="max-w-[80px] min-w-[80px] max-h-[80px] min-h-[80px] rounded-full">
                            <div class="flex flex-col items-center justify-start w-full overflow-hidden text-base font-bold text-ellipsis whitespace-nowrap">
                                {{ sub.name }}
                                <div class="overflow-hidden text-xs font-bold text-gorry text-ellipsis whitespace-nowrap dark:text-garry">
                                    {{ t('sub category') }}
                                </div>
                            </div>
                            <div class="flex items-center justify-center min-w-[25px] min-h-[25px] m-1.25 rounded-full bg-rady" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(sub.value)"></div>
                        </button>
                        <div v-if="sub.more">
                            <div v-for="(leaf, index3) in sub.subCategories" :key="index3">
                                <div class="flex items-center justify-start gap-2.5 mb-1.25 ml-12 rounded-lg bg-whizy dark:bg-darkow">
                                    <img :src="leaf.image" :alt="leaf.name" class="max-w-[50px] min-w-[50px] max-h-[50px] min-h-[50px] rounded-full">
                                    <div class="flex flex-col items-center justify-start w-full overflow-hidden text-sm font-bold text-ellipsis whitespace-nowrap">
                                        {{ leaf.name }}
                                        <div class="overflow-hidden text-xs font-bold text-gorry text-ellipsis whitespace-nowrap dark:text-garry">
                                            {{ t('sub category element') }}
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-center min-w-[25px] min-h-[25px] m-1.25 rounded-full bg-rady" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(leaf.value)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

    import EditCat from '../components/elements/editCategory.vue';
    import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
    import Message from '../components/elements/bloc/message.vue'

    import icons from '~/public/icons.json'

    const categoryList = ref([])
    const newCategoryList = ref([])

    const message = ref('')
    const isMessage = ref(false)

    var result = ref()

    onMounted(() => {
        getCategory()
    })


    var resizeSvg = (svg, width, height) => {
        return svg
        .replace(/width="[^"]+"/, `width="${width}"`)
        .replace(/height="[^"]+"/, `height="${height}"`)
    }

    const { t } = useLang()

    const addCat = ref(false)


    const activate = (val) => {
        addCat.value = val
    }

    const saved = () => {

    }

    function more(index) {
        newCategoryList.value[index].more = !newCategoryList.value[index].more
    }
    function moreSub(index, index2) {
        newCategoryList.value[index].subCategories[index2].more = !newCategoryList.value[index].subCategories[index2].more
    }

    function setNewList(list) {
        if (!list) return;
        // On vide la liste existante
        newCategoryList.value = [];

        // 1. Ajouter tous les items de niveau "meta"
        for (let item of list) {
            if (item.level === 'meta') {
            newCategoryList.value.push({
                name: item.label,
                image: item.image,
                value: item.value,
                more: false,
                subCategories: []
            });
            }
        }

        // 2. Ajouter les items de niveau "branch"
        for (let item of list) {
            if (item.level === 'branch') {
            const parentId = item.parent_id;
            if (!parentId) continue; // si parent_id est null/undefined/NaN on ignore
            // Chercher l'indice de la catégorie parent dans newCategoryList
            let parentIndex = newCategoryList.value.findIndex(cat => cat.value === parentId);
            if (parentIndex === -1) {
                // Le parent n'existe pas encore dans newCategoryList, on le crée
                const parentItem = list.find(c => c.value === parentId);
                if (parentItem) {
                newCategoryList.value.push({
                    name: parentItem.label,
                    image: parentItem.image,
                    value: parentItem.value,
                    more: false,
                    subCategories: []
                });
                parentIndex = newCategoryList.value.length - 1;
                }
            }
            // Maintenant on attache la branche au parent
            if (parentIndex !== -1) {
                newCategoryList.value[parentIndex].subCategories.push({
                name: item.label,
                image: item.image,
                value: item.value,
                more: false,
                subCategories: []
                });
            }
            }
        }

        // 3. Ajouter les items de niveau "leaf"
        for (let item of list) {
            if (item.level === 'leaf') {
            const parentId = item.parent_id;
            if (!parentId) continue;
            // Chercher l'objet branche correspondant dans newCategoryList
            let branchCat, metaIndex, branchIndex;
            for (let i = 0; i < newCategoryList.value.length; i++) {
                const subCats = newCategoryList.value[i].subCategories;
                branchIndex = subCats.findIndex(sub => sub.value === parentId);
                if (branchIndex !== -1) {
                branchCat = newCategoryList.value[i].subCategories[branchIndex];
                metaIndex = i;
                break;
                }
            }
            if (!branchCat) {
                // Si la branche n'existe pas encore, on la crée ainsi que son parent meta
                const branchItem = list.find(c => c.value === parentId);
                if (branchItem) {
                const metaId = branchItem.parent_id;
                // Chercher ou créer le meta parent
                metaIndex = newCategoryList.value.findIndex(cat => cat.value === metaId);
                if (metaIndex === -1) {
                    const metaItem = list.find(c => c.value === metaId);
                    if (metaItem) {
                    newCategoryList.value.push({
                        name: metaItem.label,
                        image: metaItem.image,
                        value: metaItem.value,
                        more: false,
                        subCategories: []
                    });
                    metaIndex = newCategoryList.value.length - 1;
                    }
                }
                // Créer la branche
                newCategoryList.value[metaIndex].subCategories.push({
                    name: branchItem.label,
                    image: branchItem.image,
                    value: branchItem.value,
                    more: false,
                    subCategories: []
                });
                branchIndex = newCategoryList.value[metaIndex].subCategories.length - 1;
                branchCat = newCategoryList.value[metaIndex].subCategories[branchIndex];
                }
            }
            // Enfin, ajouter la feuille au tableau subCategories de la branche
            if (branchCat) {
                branchCat.subCategories.push({
                name: item.label,
                image: item.image,
                value: item.value
                });
            }
            }
        }
    }

    async function getCategory() {
        categoryList.value = []
        newCategoryList.value = []
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=getCategory', {
        method: 'GET',
        });
        if (!response.ok) {
            isMessage.value = true
            message.value = t('error in getting data')
            return
        }
        result.value = await response.json();
        if (result.value.success) {
            for(var i =0; i < result.value.categories.length; i++) {
                categoryList.value.push({'label': result.value.categories[i].name, 'image': result.value.categories[i].image, 'value': parseInt(result.value.categories[i].id), 'level': result.value.categories[i].level, 'parent_id': parseInt(result.value.categories[i].parent_id)})
            }
        } else {
            isMessage.value = true
            message.value = result.message
        }

            

        setNewList(categoryList.value)


    }

async function deleteCategory(id) {
    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteCategory', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id })
        });

        if (!response.ok) {
            isMessage.value = true;
            message.value = t('server connection error');
            return;
        }

        const res = await response.json();

        isMessage.value = true;
        message.value = res.message || t('unknown error');

        getCategory()
        
    } catch (error) {
        isMessage.value = true;
        message.value = t('network or server error');
        console.error(error);
    }
}
</script>