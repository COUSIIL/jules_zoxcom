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
                    if (typeof contentObj === 'object' && contentObj !== null) {
                        deliverySty.value.push(contentObj)
                    } else {
                        deliverySty.value.push(JSON.parse(contentObj))
                    }
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
                if (myWilaya[i].wilaya_active && myWilaya[i]?.wilaya_id) {
                    wilayas.value.push({ wilaya_id: myWilaya[i].wilaya_id, wilaya_name: myWilaya[i].wilaya_name, desk_method: myWilaya[i].delivery_desk, home_method: myWilaya[i].delivery_home })

                    deliveryFees.value.push({ wilaya_id: myWilaya[i].wilaya_id, tarif: myWilaya[i].home_price, tarif_stopdesk: myWilaya[i].desk_price, desk_active: myWilaya[i].desk_active, home_active: myWilaya[i].home_active })
                } else if(myWilaya[i].wilaya_active && myWilaya[i]?.id) {

                    wilayas.value.push({ wilaya_id: myWilaya[i].id, wilaya_name: myWilaya[i].name, desk_method: myWilaya[i].delivery_desk, home_method: myWilaya[i].delivery_home })

                    deliveryFees.value.push({ wilaya_id: myWilaya[i].id, tarif: myWilaya[i].home_price, tarif_stopdesk: myWilaya[i].desk_price, desk_active: myWilaya[i].desk_active, home_active: myWilaya[i].home_active })

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
                } else {
                    deliveryMethod.value = dev.method

                }

                break
            }

        }

        isUpdatingWilaya.value = false;

    }

    async function getCommune(wilaya, commu) {
        isUpdatingWilaya.value = true;
        municipalitys.value = []

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
            console.log('yalidine')
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
        if (wilaya.desk_method === 'ups') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.desk_method === 'anderson') {
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getAndersonCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.desk_method === 'yalidine') {
            console.log('yalidine')
            response = await fetch("https://management.hoggari.com/backend/api.php?action=getYalidineCommune", {
                method: "POST",
                body: JSON.stringify(id)
            });
            result = await response.json();
        } else if (wilaya.desk_method === 'guepex') {
            
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

            if(result?.data) {
                municipalitys.value = result.data
            } else {
                municipalitys.value = result
            }


            //selectedFees.value = deliveryFees.value[wilaya.wilaya_id - 1]
            //console.log('municipalitys.value: ', municipalitys.value)
            if(commu) {

                if (municipalitys.value?.length > 0) {
                    if(municipalitys.value[0].nom) {
                        for (const comu of municipalitys.value) {
                            if (comu.nom == commu) {
                                await setCommune(comu)
                                break
                                //municipalitys.value = result.data.data
                            }
                        }
                    } else {
                        for (const comu of municipalitys.value) {
                            if (comu.name == commu) {
                                await setCommune(comu)
                                break
                                //municipalitys.value = result.data.data
                            }
                        }
                    }
                    
                    
                } else if(municipalitys.value?.data.length > 0) {
                    municipalitys.value = municipalitys.value.data

                    if(municipalitys.value[0].nom) {
                        for (const comu of municipalitys.value) {
                            if (comu.nom == commu) {
                                await setCommune(comu)
                                break
                                //municipalitys.value = result.data.data
                            }
                        }
                    } else {
                        for (const comu of municipalitys.value) {
                            if (comu.name == commu) {
                                await setCommune(comu)
                                break
                                //municipalitys.value = result.data.data
                            }
                        }
                    }
                    
                    
                }
            } else {
                
                if(municipalitys.value?.data) {
                    await setCommune(municipalitys.value.data[0])
                } else {
                    await setCommune(municipalitys.value[0])
                }
                
            }
            
                
            
            isUpdatingWilaya.value = false;
        } catch (error) {
            console.error('Une erreur est survenue:', error)
            isUpdatingWilaya.value = false;
        }

        isUpdatingWilaya.value = false;
    }

    async function setCommune(com) {
        
        if (com?.nom) {
            selectedMunicipality.value = com.nom
        } else {
            selectedMunicipality.value = com.name
        }

        await getDeliveryFees(com)

        if (!com?.has_stop_desk || com.has_stop_desk === 0) {
            isDesk.value = false
            //console.log('false: ', isDesk.value)
        } else {
            isDesk.value = true
            //console.log('true: ', isDesk.value)
        }
        //console.log('selectedFees.value: ', selectedFees.value)



    }

    async function getDeliveryFees(com) {
        let id

        if(com?.wilaya_id) {
            id = com.wilaya_id
        } else {
            id = com
        }

        
        //i hope i can touch you ther,
        //the secret place i want to see,
        //i can't believe my head my heart,
        //all my sensation standing up,
        //give me pleas this little thing,
        //forgive me my room,
        //i can't hear you any more.



        setUpMethod(id)

        isUpdatingWilaya.value = true;

        var newWilayaList = {desk: [], home: []}

        for(let h of wilayas.value) {
            if (h?.desk_method) {
                if (newWilayaList.desk.includes(h.desk_method)) {
                    continue
                } else {
                    newWilayaList.desk.push(h.desk_method)
                }
            }
            if (h?.home_method) {
                if (newWilayaList.home.includes(h.home_method)) {
                    continue
                } else {
                    newWilayaList.home.push(h.home_method)
                }
            }
            
        }

        for(let l of newWilayaList.desk) {
            await getFees(l, 'desk')
                
        }
        for(let l of newWilayaList.home) {
            await getFees(l, 'home')
            
        }

        //console.log('deliveryFees.value[delIndex.value]: ', deliveryFees.value[delIndex.value])


        for (var index in deliveryFees.value) {
            if (deliveryFees.value[index].wilaya_id === id) {
                selectedFees.value = deliveryFees.value[index]
                break
            }

        }


        //deliveryFees.value = result['livraison']
        isUpdatingWilaya.value = false;



    }

    async function getFees (method, type) {

        var response
        var result

        if (method === 'ups') {
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

            if (type === 'desk' && deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = res['tarif_stopdesk']
                            break
                        }
                    }
                }
            } else if (type === 'home' && deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif'] = res['tarif']
                            break
                        }
                    }
                }
            }
            

        } else if (method === 'anderson') {
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

            if (type === 'desk' && deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = res['tarif_stopdesk']
                           break
                        }
                    }
                }
            } else if (type === 'home' && deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res of result['livraison']) {
                        if (res.wilaya_id === deliveryFees.value[delIndex.value]['wilaya_id']) {
                            deliveryFees.value[delIndex.value]['tarif'] = res['tarif']
                            break
                        }
                    }
                }
            }
            
        } else if (method === 'yalidine') {

            const response = await fetch(`https://management.hoggari.com/backend/api.php?action=getYalidineFees&wilaya_id=${selectedWilaya.value['wilaya_id']}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            result = await response.json()
            //console.log('yalidine: ', result)

            if (type === 'desk' && deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif_stopdesk'] = result.data.per_commune[res]['express_desk']
                                break
                        }
                    }
                }
            } else if (type === 'home' && deliveryFees.value[delIndex.value]['home_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                            deliveryFees.value[delIndex.value]['tarif'] = result.data.per_commune[res]['express_home']
                                break
                        }
                    }
                }
            }

            

        } else if (method === 'guepex') {
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
            //console.log('guepex: ', result)

            if (type === 'desk' && deliveryFees.value[delIndex.value]['desk_active']) {
                if (!deliveryFees.value[delIndex.value]['tarif_stopdesk']) {
                    for (let res in result.data.per_commune) {
                        if (result.data.per_commune[res].commune_name === selectedMunicipality.value) {
                        deliveryFees.value[delIndex.value]['tarif_stopdesk'] = result.data.per_commune[res]['express_desk']
                            break
                            
                        }
                    }
                }
            } else if (type === 'home' && deliveryFees.value[delIndex.value]['home_active']) {
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


        if (wilayas.value[wilIndex.value].desk_method && deliveryIndex.value === '1') {
            
            deliveryMethod.value = wilayas.value[wilIndex.value].desk_method
        } else if (wilayas.value[wilIndex.value].home_method) {
            deliveryMethod.value = wilayas.value[wilIndex.value].home_method
        }



    }

    function setDelivery(i) {

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