<template>

    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>

    <div :style="{maxWidth: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}">
        <div class="boxContainer1">
    
        <div style="display: flex; justify-content: center; align-items: center; margin-inline: 10px; gap: 5px;">
            <div v-html="resizeSvg(icons['category'], 20, 20)">

            </div>
            <h1>
            {{ newCategoryList.length }} {{t('categories')}}
            </h1>
            
        </div>

        <CallToAction :text="t('add category')" :svg="icons['add']" @clicked="activate(true)"/>

        </div>

        <EditCat v-if="addCat" @cancel="activate(false)" @saved="saved"/>

        <div v-for="(element, index) in newCategoryList" :key="index">
            <div class="boxContainer2">
                <button type="button" class="titleTeam" @click="more(index)">
                    <img :src="element.image" :alt="element.name">
                    <div class="textDiv">
                        {{ element.name }}
                        <div class="miniText">
                            {{ t('category') }}
                        </div>
                    </div>
                    <div style="min-width: 25px; min-height: 25px; margin: 5px; border-radius: 50%; display: flex; justify-content: center; align-items: center; background-color: var(--color-rady);" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(element.value)">

                    </div>
                            
                </button>
                <div v-if="element.more">
                    <div v-for="(sub, index2) in element.subCategories" :key="index2">
                        <button type="button" class="miniTitle" @click="moreSub(index, index2)">
                            <img :src="sub.image" :alt="sub.name">                            
                            <div class="textDiv">
                                {{ sub.name }}
                                <div class="miniText">
                                    {{ t('sub category') }}
                                </div>
                            </div>
                            <div style="min-width: 25px; min-height: 25px; margin: 5px; border-radius: 50%; display: flex; justify-content: center; align-items: center; background-color: var(--color-rady);" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(sub.value)">

                            </div>
                            
                        </button>
                        <div v-if="sub.more">
                            <div v-for="(leaf, index3) in sub.subCategories" :key="index3">
                                <div class="shortTitle">
                                    <img :src="leaf.image" :alt="leaf.name">
                                    <div class="textDiv">
                                        {{ leaf.name }}
                                        <div class="miniText">
                                            {{ t('sub category element') }}
                                        </div>
                                    </div>
                                    <div style="min-width: 25px; min-height: 25px; margin: 5px; border-radius: 50%; display: flex; justify-content: center; align-items: center; background-color: var(--color-rady);" v-html="resizeSvg(icons['deleteImg'], 20, 20)" @click="deleteCategory(leaf.value)">

                                    </div>
                                    
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
            message.value = 'error in getting data'
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
            message.value = 'Erreur de connexion au serveur';
            return;
        }

        const res = await response.json();

        isMessage.value = true;
        message.value = res.message || 'Erreur inconnue';

        getCategory()
        
    } catch (error) {
        isMessage.value = true;
        message.value = 'Erreur réseau ou serveur';
        console.error(error);
    }
}
</script>


<style>

  .boxContainer1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
  }
  .dark .boxContainer1{
    background-color: var(--color-darkly);
  }

  .boxContainer2 {
    display: flex;
    align-items: right;
    justify-content: space-between;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    padding: 5px;
    cursor: pointer;
  }
  .dark .boxContainer2{
    background-color: var(--color-darkly);
  }
  

  .no_image {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    border-radius: 50px;
    background-color: var(--color-whizy);
  }
  .dark .no_image {
    background-color: var(--color-darkow);
  }

  .center_column {
    width: 90%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .center_flex {
    width: 100%;
    display: flex;
    min-width: 250px;
    max-width: 200px;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-inline: 5px;
  }

  .insider {
    width: 50%;
    display: flex;
    min-width: 150px;
    max-width: 300px;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }
  .titleTeam {
    display: flex;
    justify-content: left;
    align-items: center;
    gap: 10px;
    background-color: var(--color-whizy);
    border-radius: 8px;
  }
  .dark .titleTeam{
    background-color: var(--color-darkow);
  }
  .titleTeam img {
    max-width: 100px;
    min-width: 100px;
    max-height: 100px;
    min-height: 100px;
    border-radius: 50%;
  }
  .titleTeam .textDiv {
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: left;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 20px;
    font-weight: bold;
  }
  .titleTeam .textDiv .miniText {
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 12px;
    font-weight: bold;
    color: var(--color-gorry);
  }
  .dark .titleTeam .textDiv .miniText {
    color: var(--color-garry);
  }
  .miniTitle {
    width: calc(100% - 20px);
    margin: 5px 5px 5px 20px;
    display: flex;
    justify-content: left;
    align-items: center;
    gap: 10px;
    background-color: var(--color-whizy);
    border-radius: 8px;
  }
  .dark .miniTitle{
    background-color: var(--color-darkow);
  }

  .miniTitle img {
    max-width: 80px;
    min-width: 80px;
    max-height: 80px;
    min-height: 80px;
    border-radius: 50%;
  }
  .miniTitle .textDiv {
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: left;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 16px;
    font-weight: bold;
  }
  .miniTitle .textDiv .miniText {
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 12px;
    font-weight: bold;
    color: var(--color-gorry);
  }
  .dark .miniTitle .textDiv .miniText {
    color: var(--color-garry);
  }

  .shortTitle {
    margin-left: 50px;
    margin-bottom: 5px;
    display: flex;
    justify-content: left;
    align-items: center;
    gap: 10px;
    background-color: var(--color-whizy);
    border-radius: 8px;
  }
  .dark .shortTitle{
    background-color: var(--color-darkow);
  }

  .shortTitle img {
    max-width: 50px;
    min-width: 50px;
    max-height: 50px;
    min-height: 50px;
    border-radius: 50%;
  }
  .shortTitle .textDiv {
    width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: left;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 14px;
    font-weight: bold;
  }
  .shortTitle .textDiv .miniText {
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 12px;
    font-weight: bold;
    color: var(--color-gorry);
  }
  .dark .shortTitle .textDiv .miniText {
    color: var(--color-garry);
  }

  .center_flex img {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    object-fit: cover; /* Remplit la zone en gardant le ratio */
    display: flex;
    border-radius: 50%; /* Cercle parfait */
    

  }



</style>