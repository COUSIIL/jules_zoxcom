import { ref } from 'vue';

export const useDelivery = () => {
    const isUpdatingWilaya = ref(false)
    const wilayas = ref([])
    const deliveryFees = ref([])
    const deliveryMethod = ref()
    const deliverySty = ref([])
    const municipalitys = ref([])
    const selectedWilaya = ref('')
    const selectedMunicipality = ref('')
    const isDesk = ref(false)
    const delIndex = ref(0)
    const wilIndex = ref(0)
    const deliveryIndex = ref(0)
    const selectedFees = ref([])


    async function getDelivery() {

        await getStoreDelivery()

        isUpdatingWilaya.value = true;

        wilayas.value = []

        try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getDeliveryMethod')

            if (!response.ok) {
                console.error('Erreur lors de la récupération des produits:', response.statusText)
                isUpdatingWilaya.value = false;
                return
            }

            const result = await response.json()

            for (var i of result.data) {
                try {
                    const contentObj = JSON.parse(i.delivery_content)
                    deliverySty.value.push(contentObj)
                } catch (e) {
                    console.error('error in parsing data')
                }

            }


            var myWilaya

            for (var d in result.data) {

                if (result.data[d].delivery_name === deliveryMethod.value) {

                    myWilaya = JSON.parse(result.data[d].delivery_info)


                    break
                }

            }

            for (var i = 0; i < myWilaya.length; i++) {
                if (myWilaya[i].wilaya_active) {
                    wilayas.value.push({ wilaya_id: myWilaya[i].wilaya_id, wilaya_name: myWilaya[i].wilaya_name, desk_method: myWilaya[i].delivery_desk, home_method: myWilaya[i].delivery_home })

                    deliveryFees.value.push({ wilaya_id: myWilaya[i].wilaya_id, tarif: myWilaya[i].home_price, tarif_stopdesk: myWilaya[i].desk_price, desk_active: myWilaya[i].desk_active, home_active: myWilaya[i].home_active })
                }

            }

            isUpdatingWilaya.value = false;


        } catch (error) {
            console.error('Une erreur est survenue:', error)
            isUpdatingWilaya.value = false;
        }
    }

    const getStoreDelivery = async () => {


        isUpdatingWilaya.value = true;

        const response = await fetch('https://management.hoggari.com/backend/api.php?action=getStoreDelivery', {
            method: 'GET',
        });

        if (!response.ok) {
            isUpdatingWilaya.value = false;
            return;
        }

        const textResponse = await response.json();

        if (textResponse.success) {

            for (var dev of textResponse.data) {
                if (dev.name === 'DinarZ') {
                    deliveryMethod.value = dev.method
                }

                break
            }

        }

        isUpdatingWilaya.value = false;

    }

    async function getCommune(wilaya) {
        isUpdatingWilaya.value = true;
        const id = {
            wilaya_id: wilaya.wilaya_id
        }
        if (wilaya.home_method != 'custom') {
            selectedWilaya.value = wilaya

        }
        var response
        var result
        if (wilaya.home_method === 'ups') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.home_method === 'anderson') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getAndersonCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.home_method === 'yalidine') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getYalidineCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.home_method === 'guepex') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getGuepexCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        }
        try {

            if (!response || !response.ok) {
                throw new Error(`HTTP error! ${response}`);
            }

            municipalitys.value = result

            //selectedFees.value = deliveryFees.value[wilaya.wilaya_id - 1]

            if (municipalitys.value.data) {
                setCommune(municipalitys.value.data.data[0])
                municipalitys.value = result.data.data
            } else {
                setCommune(municipalitys.value[0])
            }
            isUpdatingWilaya.value = false;
        } catch (error) {
            console.error('Une erreur est survenue:', error)
            isUpdatingWilaya.value = false;
        }

        isUpdatingWilaya.value = false;
    }

    async function setCommune(com) {

        if (com.nom) {
            selectedMunicipality.value = com.nom
        } else {
            selectedMunicipality.value = com.name
        }
        if (!com.has_stop_desk) {
            isDesk.value = false
        } else {
            isDesk.value = true
        }
        await getDeliveryFees(com.wilaya_id)



        for (var index in deliveryFees.value) {
            if (deliveryFees.value[index].wilaya_id === com.wilaya_id) {
                selectedFees.value = deliveryFees.value[index]
                break
            }

        }



    }

    async function getDeliveryFees(id) {

        var response
        var result
        //i hope i can touch you ther
        //the secret place i want to see, to touch
        //i can't believe my head my heart
        //all my sensation standing up
        //give me pleas this little thing
        //forgive me my room
        //i can't hear you any more, i can't hear you any moooooore, any more



        setUpMethod(id)

        isUpdatingWilaya.value = true;


        if (deliveryMethod.value === 'ups') {
            try {
                response = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsFees')
            } catch (e) {
                console.log(e)
                isUpdatingWilaya.value = false;
                return
            }
            if (!response.ok) {
                console.error(response.statusText)
                isUpdatingWilaya.value = false;
                return
            }
            result = await response.json()

            if (deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = res['tarif_stopdesk']
                            break
                        }
                    }
                }
            }
            if (deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif'] = res['tarif']
                            break
                        }
                    }
                }
            }

        } else if (deliveryMethod.value === 'anderson') {
            try {
                response = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonFees')
            } catch (e) {

                console.error(e)
                isUpdatingWilaya.value = false;
                return
            }
            if (!response.ok) {
                console.error(response.statusText)
                isUpdatingWilaya.value = false;
                return
            }
            result = await response.json()

            //console.log('result: ', result)

            if (deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = res['tarif_stopdesk']
                            break
                        }
                    }
                }
            }
            if (deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif'] = res['tarif']
                            break
                        }
                    }
                }
            }
        } else if (deliveryMethod.value === 'yalidine') {

            const response = await fetch(`https://management.hoggari.com/backend/api.php?action=getYalidineFees&wilaya_id=${selectedWilaya.value['wilaya_id']}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            result = await response.json()

            if (deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = result.data.per_commune[res]['express_desk']
                            break
                        }
                    }
                }
            }
            if (deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif'] = result.data.per_commune[res]['express_home']
                            break
                        }
                    }
                }
            }

        } else if (deliveryMethod.value === 'guepex') {
            try {
                response = await fetch(`https://management.hoggari.com/backend/api.php?action=getGuepexFees&wilaya_id=${selectedWilaya.value['wilaya_id']}`, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json"
                    }
                });
            } catch (e) {
                console.error(e)
                isUpdatingWilaya.value = false;
                return
            }

            result = await response.json()

            if (deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = result.data.per_commune[res]['express_desk']
                            break
                        }
                    }
                }
            }
            if (deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif'] = result.data.per_commune[res]['express_home']
                            break
                        }
                    }
                }
            }
        }


        //deliveryFees.value = result['livraison']
        isUpdatingWilaya.value = false;

    }

    function setUpMethod(id) {

        for (var index in deliveryFees.value) {
            if (deliveryFees.value[index].wilaya_id === id) {
                delIndex.value = index
                break
            }

        }
        for (var index2 in wilayas.value) {
            if (wilayas.value[index2].wilaya_id === id) {
                wilIndex.value = index2
                break
            }

        }

        if (wilayas.value[wilIndex.value].home_method && !deliveryIndex.value) {
            deliveryMethod.value = wilayas.value[wilIndex.value].home_method
        } else if (wilayas.value[wilIndex.value].desk_method && deliveryIndex.value) {
            deliveryMethod.value = wilayas.value[wilIndex.value].desk_method
        }

    }

    function setDelivery(i = 0) {
        deliveryIndex.value = i

        setUpMethod(selectedWilaya.value)

    }

    return {
        getDelivery,
        isUpdatingWilaya,
        wilayas,
        deliveryFees,
        getCommune,
        municipalitys,
        deliveryIndex,
        setDelivery,
        deliverySty,
        selectedFees,
        setCommune,
        isDesk


    }
}