import { ref } from 'vue';

// --- Le Composable ---

export const useOrders = () => {
    const data = ref()
    const log = ref('')
    const existe = ref(false)
    var loading = ref(true)
    

    const levenshteinDistance = (a, b) => {
        const matrix = Array.from({ length: a.length + 1 }, (_, i) =>
            Array.from({ length: b.length + 1 }, (_, j) => (i === 0 ? j : j === 0 ? i : 0))
        );

        for (let i = 1; i <= a.length; i++) {
            for (let j = 1; j <= b.length; j++) {
                matrix[i][j] = a[i - 1] === b[j - 1]
                    ? matrix[i - 1][j - 1]
                    : Math.min(matrix[i - 1][j] + 1, matrix[i][j - 1] + 1, matrix[i - 1][j - 1] + 1);
            }
        }

        return matrix[a.length][b.length];
    }

    var getDelivryM = async () => {
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getDeliveryMethod', {
            method: 'GET',
        });
        const data = await response2.json();
        return data

    }


    const getOrders = async () => {
        loading.value = true;
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=getOrders', {
            method: 'GET',
        });
        if (!response.ok) {
            log.value = 'error in getting response category';
            loading.value = false;
            return;
        }
        const result = await response.json();
        if (result.success) {
            if (!result.data) {
                log.value = 'No recent orders for now.';
                loading.value = false;
            } else {
                
                log.value = result.message;
                data.value = result.data; 
                await getProduct()
                 
 
            }
            existe.value = true;
            loading.value = false;
            
                
        } else {
            log.value = result.message;
            existe.value = false;
            loading.value = false;
        }

    }

    const getProduct = async () => {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=getProducts', {
            method: 'GET',
        });

        if (!response.ok) {
            isMessage.value = true
            message.value = t('error in request get products')
            return;
        }

        const result = await response.json();
        return result
    }

    const deleteOrder = async (id, index) => {

        const updateOrder = JSON.stringify({
            id: id,
        });

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=deleteOrder', {
            method: 'POST',
            body: updateOrder,
        });
        if(!response2.ok){
            log.value = `error in deleting order:  ${index}`;
            return;
        }
        const textResponse = await response2.json();  // Récupérer la réponse en texte
        if (textResponse.success) {

            await getOrders();
            /*//this.resetOrders();
            for (var i = 0; i < this.resulted.length; i++) {
            this.setOrders(i);
            
            }
            this.isUpdating[index] = false;*/
            log.value = textResponse.message;
        } else {
            log.value = textResponse.message;
        }

    }

    const deliverOrder = async (order, product, deliveryType, method, total, delivery, wilaya) => {
    let data, type;
    let product_name = '';
    // Construction de la liste de produits
    const items = product.items ?? [];
    for (let pro of items) {
        if (product_name === '') {
            product_name = `${product.name}-${pro.color_name}-${pro.size}`;
        } else {
            product_name = `${product_name}, ${product.name}-${pro.color_name}-${pro.size}`;
        }
    }

    // Détermination du type (livraison ou stop desk)
    type = (deliveryType === 'Stop Desk');
    var typeInt = 0

    if(deliveryType === 'Stop Desk') {
        typeInt = 0
    } else {
        typeInt = 1
    }

    // Récupération des infos de livraison
    const info = await getDelivryM();
    let ttc = parseFloat(total);
    let dropAreaID;
    for (let M of info.data) {
        if (M.delivery_name === method) {
            dropAreaID = M.drop_area_id;
            if (M.include_fees == "1") {
                ttc = +(parseInt(total) + parseInt(delivery || 0));
            }

        }
    }
    var note = ''
    if(order.note && order.note.length > 0) {
        note = order.note[order.note.length - 1].text
    } else {
        note = ''
    }

    // Pour chaque méthode de livraison, on envoie les données et on récupère la réponse
    if (method === 'ups') {
        try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsWilaya');
            const upsWilayas = await response.json();
            // Recherche de l'ID de wilaya avec distance de Levenshtein minimale
            let wilayaId = 0;
            const tolerance = 2;
            for (let i = 0; i < upsWilayas.length; i++) {
                const distance = levenshteinDistance(upsWilayas[i].wilaya_name.toLowerCase(), wilaya.toLowerCase());
                if (distance <= tolerance) {
                    wilayaId = upsWilayas[i].wilaya_id;
                    break;
                }
            }
            if (wilayaId !== 0) {
                const setToUps = {
                    reference: `DNZ-${(order.id)}`,
                    nom_client: order.name,
                    telephone: order.phone,
                    telephone_2: '',
                    adresse: order.mZone,
                    code_postal: '',
                    commune: order.sZone,
                    code_wilaya: wilayaId.toString(),
                    montant: ttc,
                    remarque: note,
                    produit: product_name,
                    stock: '',
                    quantite: '',
                    produit_a_recupere: '',
                    boutique: '',
                    type: 1,
                    stop_desk: typeInt,
                    weight: '',
                };
                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addUpsOrder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(setToUps),
                });
                const data2 = await response2.json();
                if (data2.success) {
                    if (data2 && data2.tracking) {
                        
                        await updateOrderValue((order.id), 'tracking_code', data2.tracking);
                    }
                    await updateOrderValue((order.id), 'status', 'shipping');
                } else {
                    console.error(`UPS error: ${data2.message}`);
                }
            } else {
                console.error('UPS: Aucune wilaya trouvée');
            }
        } catch (error) {
            console.error('UPS request error:', error);
        }
    } 
    else if (method === 'anderson') {
        try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonWilaya');
            const andersonWilayas = await response.json();
            let wilayaId = 0;
            const tolerance = 2;
            for (let i = 0; i < andersonWilayas.length; i++) {
                const distance = levenshteinDistance(andersonWilayas[i].wilaya_name.toLowerCase(), wilaya.toLowerCase());
                if (distance <= tolerance) {
                    wilayaId = andersonWilayas[i].wilaya_id;
                    break;
                }
            }

            if (wilayaId !== 0) {
                const setToAnderson = {
                    reference: `DNZ-${(order.id)}`,
                    nom_client: order.name,
                    telephone: order.phone,
                    telephone_2: '',
                    adresse: order.mZone,
                    code_postal: '',
                    commune: order.sZone,
                    code_wilaya: wilayaId.toString(),
                    montant: ttc,
                    remarque: note,
                    produit: product_name,
                    stock: '',
                    quantite: '',
                    produit_a_recupere: '',
                    boutique: '',
                    type: 1,
                    stop_desk: typeInt,
                    weight: '',
                };
                console.log('setToAnderson: ', setToAnderson)
                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addAndersonOrder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(setToAnderson),
                });
                const data2 = await response2.json();
                if (data2.success) {
                    if (data2 && data2.tracking) {
                        console.log('data2.tracking: ', data2.tracking)
                        await updateOrderValue((order.id), 'tracking_code', data2.tracking);
                    }
                    await updateOrderValue((order.id), 'status', 'shipping');
                } else {
                    console.error(`Anderson error: ${data2.message}`);
                }
            } else {
                console.error('Anderson: Aucune wilaya trouvée');
            }
        } catch (error) {
            console.error('Anderson request error:', error);
        }
    } 
    else if (method === 'yalidine') {
        try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getYalidineCenter');
            const dataYal = await response.json();
            let wilayaId = 0, center = null;
            const tolerance = 2;
            if (dataYal.success) {
                // Recherche dans la première page
                for (let i = 0; i < dataYal.data.data.length; i++) {
                    const distW = levenshteinDistance(dataYal.data.data[i].wilaya_name.toLowerCase(), wilaya.toLowerCase());
                    const distC = levenshteinDistance(dataYal.data.data[i].commune_name.toLowerCase(), order.sZone.toLowerCase());
                    if (distW <= tolerance && distC <= tolerance) {
                        wilayaId = dataYal.data.data[i].wilaya_id;
                        center = dataYal.data.data[i].center_id;
                        break;
                    }
                }
                // Si pas trouvé, parcourir les pages suivantes
                let urlNext = dataYal.data.links?.next;
                while (!wilayaId && urlNext) {
                    const responseNext = await fetch('https://management.hoggari.com/backend/api.php?action=yalidineCenterNext', {
                        method: 'POST',
                        body: JSON.stringify({ url: urlNext })
                    });
                    const next = await responseNext.json();
                    for (let i = 0; i < next.data.data.length; i++) {
                        const distW = levenshteinDistance(next.data.data[i].wilaya_name.toLowerCase(), wilaya.toLowerCase());
                        const distC = levenshteinDistance(next.data.data[i].commune_name.toLowerCase(), order.sZone.toLowerCase());
                        if (distW <= tolerance && distC <= tolerance) {
                            wilayaId = next.data.data[i].wilaya_id;
                            center = next.data.data[i].center_id;
                            break;
                        }
                    }
                    urlNext = next.data.links?.next;
                }
                if (!center) {
                    console.error('Yalidine: Aucun centre trouvé');
                }
                if (center != null) {
                    // Préparation des informations du colis
                    const splitName = (fullName) => {
                        const parts = fullName.split(' ');
                        return { firstname: parts.shift(), familyname: parts.join(' ') };
                    };
                    const { firstname, familyname } = splitName(order.name);
                    const parcels = [{
                        order_id: `CofP-${(order.id)}`,
                        from_wilaya_name: "Tipaza",
                        firstname,
                        familyname,
                        contact_phone: order.phone,
                        address: order.mZone,
                        to_commune_name: order.sZone,
                        to_wilaya_name: wilaya,
                        product_list: product_name,
                        price: total,
                        do_insurance: false,
                        declared_value: total,
                        height: 10,
                        width: 10,
                        length: 10,
                        weight: 1,
                        freeshipping: false,
                        is_stopdesk: type,
                        stopdesk_id: center,
                        has_exchange: false,
                        product_to_collect: null
                    }];
                    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addYalidineOrder', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ parcels }),
                    });
                    const data2 = await response2.json();
                    if (data2.success && data2.data.length > 0) {
                        const trackingCode = data2.data[0].tracking;
                        await updateOrderValue((order.id), 'tracking_code', trackingCode);
                        await updateOrderValue((order.id), 'status', 'shipping');
                    } else {
                        console.error(`Yalidine error: ${data2.message}`);
                    }
                }
            } else {
                console.error(`Yalidine fetch error: ${dataYal.message}`);
            }
        } catch (error) {
            console.error('Yalidine request error:', error);
        }
    } 
    else if (method === 'guepex') {
        try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getGuepexCenter');
            const data1 = await response.json();
            if (!data1.success) {
                console.error(`Guepex error: ${data1.message}`);
                return;
            }
            let center = null, wilayaId = null;
            const tolerance = 2;
            // Fonction de recherche de centre dans une liste
            const searchCenters = (list) => {
                for (let item of list) {
                    const distW = levenshteinDistance(item.wilaya_name.toLowerCase(), wilaya.toLowerCase());
                    const distC = levenshteinDistance(item.commune_name.toLowerCase(), order.sZone.toLowerCase());
                    if (distW <= tolerance && distC <= tolerance) {
                        wilayaId = item.wilaya_id;
                        center = item.center_id;
                        return true;
                    }
                }
                return false;
            };
            // Recherche dans la première page
            if (!searchCenters(data1.data.data)) {
                let urlNext = data1.data.links?.next || null;
                let pageCount = 0, MAX_PAGES = 20;
                while (urlNext && !wilayaId && pageCount < MAX_PAGES) {
                    pageCount++;
                    const responseNext = await fetch('https://management.hoggari.com/backend/api.php?action=guepexCenterNext', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ url: urlNext })
                    });
                    const next = await responseNext.json();
                    if (!next?.data?.data) break;
                    if (searchCenters(next.data.data)) break;
                    urlNext = next.data.has_more ? next.data.links.next : null;
                }
            }
            if (wilayaId == null) {
                console.error('Guepex: Aucune wilaya trouvée');
            } else {
                const splitName = (fullName) => {
                    const parts = fullName.split(' ');
                    return { firstname: parts.shift(), familyname: parts.join(' ') };
                };
                const { firstname, familyname } = splitName(order.name);
                const parcels = [{
                    order_id: `CofP-${(order.id)}`,
                    from_wilaya_name: "Tipaza",
                    firstname,
                    familyname,
                    contact_phone: order.phone,
                    address: order.mZone,
                    to_commune_name: order.sZone,
                    to_wilaya_name: wilaya,
                    product_list: product_name,
                    price: total,
                    do_insurance: false,
                    declared_value: total,
                    height: 5,
                    width: 5,
                    length: 5,
                    weight: 1,
                    freeshipping: false,
                    is_stopdesk: type,
                    stopdesk_id: center,
                    has_exchange: false,
                    product_to_collect: null
                }];
                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addGuepexOrder', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ parcels }),
                });
                const data2 = await response2.json();
                if (data2.success && data2.data.length > 0) {
                    const trackingCode = data2.data[0].tracking;
                    await updateOrderValue((order.id), 'tracking_code', trackingCode);
                    await updateOrderValue((order.id), 'status', 'shipping');
                } else {
                    console.error(`Guepex error: ${data2.message}`);
                }
            }
        } catch (err) {
            console.error("Guepex request error:", err);
        }
    }
};


    const updateOrderValue = async (id, status, value) => {

        loading.value = true;
        const updateOrder = JSON.stringify({
                id: id,
                status: status,
                value: value,
        });

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=updateOrderValue', {
        method: 'POST',
        body: updateOrder,
        });
        if(!response2.ok){
            //this.orLog = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            //this.orLog = textResponse.data;

            await getOrders();

            loading.value = false;
        } else {
            //this.orLog = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
        //this.isOpen[index] = false;
        //this.isEdit[index] = false;
        //this.isOpen2 = false;
    

    }


    var viewTracking = async (tracking) => {

        const trackRes = await fetch(`https://management.hoggari.com/backend/api.php?action=getTrackingOp&tracking=${tracking}`);
        if(!trackRes.ok){
            //this.orLog = "error in response";
            return;
        }

        const textResponse = await trackRes.json();  // Récupérer la réponse en texte
        if (textResponse) {
            //this.orLog = textResponse.data;
            const lastAct = textResponse.activity[textResponse.activity.length - 1].status
            return lastAct

        }
    }

    

    

    return {
        data,
        log,
        existe,
        loading,
        getOrders,
        deleteOrder,
        updateOrderValue,
        deliverOrder,
        viewTracking,
    };
};