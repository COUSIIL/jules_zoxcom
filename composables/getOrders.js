import { ref } from 'vue';
import { useAuth } from './useAuth';

// --- Le Composable ---

export const useOrders = () => {
    const { auth } = useAuth();
    const data = ref()
    const log = ref('')
    const existe = ref(false)
    const resultProduct = ref()
    const updated = ref(0)
    const orLog = ref('')
    var loading = ref(true)
    

    var getDelivryM = async () => {
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getDeliveryMethod', {
            method: 'GET',
        });
        const data = await response2.json();
        return data

    }


    const getOrder = async (id) => {
        loading.value = true;
        const response = await fetch(`https://management.hoggari.com/backend/api.php?action=getOrder`, {
            method: 'POST',
            body: JSON.stringify({ order_id: id })
        });
        if (!response.ok) {
            log.value = 'error getting order';
            loading.value = false;
            return null;
        }
        const result = await response.json();
        loading.value = false;
        if (result.success && result.data && result.data.length > 0) {
            return result.data[0];
        } else {
            log.value = result.message;
            return null;
        }
    }

    const getOrders = async (queryParams = {}, silent = false) => {
        if (!silent) loading.value = true;

        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(queryParams)) {
            if (value !== null && value !== undefined && value !== '') {
                params.append(key, value);
            }
        }

        const queryString = params.toString();
        const url = `https://management.hoggari.com/backend/api.php?action=getOrders${queryString ? '&' + queryString : ''}`;

        const response = await fetch(url, {
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
                data.value = result.data.slice().reverse();
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
            // isMessage.value = true
            // message.value = t('error in request get products')
            return;
        }

        resultProduct.value = await response.json();
        
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
        const items = product ?? [];
        for (let pro of items) {

            for (let pro2 of pro.items) {
                if (product_name === '') {
                    product_name = `${pro.productName}-${pro2.color_name}-${pro2.size}`;
                } else {
                    product_name = `${product_name}, ${pro.productName}-${pro2.color_name}-${pro2.size}`;
                }
            }
            
        }

        // Détermination du type (livraison ou stop desk)
        type = (deliveryType === 'Stop Desk');
        var typeInt = 0

        //console.log('deliveryType: ', deliveryType)

        if(deliveryType === 'Stop Desk') {
            typeInt = 1
        } else {
            typeInt = 0
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
                //getAndersonCommune
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

                    /*const updateOrder = JSON.stringify({
                        id: wilayaId,
                    });

                    const resCommune = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonCommune', {
                        method: 'POST',
                        body: updateOrder,
                    });
                    if(!resCommune.ok){
                        return;
                    }
                    const textResponse = await resCommune.json();  // Récupérer la réponse en texte
                    if (textResponse.success) {
                        console.log('textResponse: ', textResponse.data)
                    }*/

                    const setToUps = {
                        reference: `${(order.id)}-${(order.owner)}`,
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
                            
                            await updateOrderValue((order.id), 'tracking_code', data2.tracking, auth.value?.username);
                        }
                        await updateOrderValue((order.id), 'status', 'shipping', auth.value?.username);
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

                // Simple search logic if Levenshtein is not available locally
                // Assuming wilaya match
                for (let i = 0; i < andersonWilayas.length; i++) {
                    if(andersonWilayas[i].wilaya_name.toLowerCase().includes(wilaya.toLowerCase()) || wilaya.toLowerCase().includes(andersonWilayas[i].wilaya_name.toLowerCase())) {
                        wilayaId = andersonWilayas[i].wilaya_id;
                        break;
                    }
                }


                if (wilayaId !== 0) {


                    const resCommune = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonCommune', {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ wilaya_id: wilayaId }) // ✅ Corrigé
                    });
                    if(!resCommune.ok){
                        return;
                    }
    
                    var textResponse = await resCommune.json();  // Récupérer la réponse en texte

                    // Match commune logic
                    for (let i = 0; i < textResponse.length; i++) {
                         if(textResponse[i].nom.toLowerCase().includes(order.sZone.toLowerCase())) {
                            order.sZone = textResponse[i].nom;
                            break;
                        }
                    }

                    const setToAnderson = {
                        reference: `${(order.id)}-${(order.owner)}`,
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
                    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addAndersonOrder', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(setToAnderson),
                    });
                    const data2 = await response2.json();
                    if (data2.success) {
                        if (data2 && data2.tracking) {
                            await updateOrderValue((order.id), 'tracking_code', data2.tracking, auth.value?.username);
                        }
                        await updateOrderValue((order.id), 'status', 'shipping', auth.value?.username);
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
                const response = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=getYalidineCenter');
                const dataYal = await response.json();

                let wilayaId = 0, center = null;

                let minDist1 = 0, minDist2 = 0;

                if (dataYal.success) {
                    // Recherche dans la première page
                    for (let i = 0; i < dataYal.data.data.length; i++) {
                        
                        //console.log('commune_name: ', dataYal.data.data[i].commune_name)
                        const dist1 = smartDistance(
                            dataYal.data.data[i].wilaya_name.toLowerCase(),
                            wilaya.toLowerCase()
                        );
                        const dist2 = smartDistance(
                            dataYal.data.data[i].commune_name.toLowerCase(),
                            order.sZone.toLowerCase()
                        );
                        if(minDist1 == 0 && minDist2 == 0) {
                            minDist1 = dist1;
                            minDist2 = dist2;
                            if(dist1 < 0.3) {
                                wilayaId = dataYal.data.data[i].wilaya_id
                            }
                            if(dist2 < 0.3) {
                                center = dataYal.data.data[i].center_id;
                                newOrderSzone.value = dataYal.data.data[i].commune_name
                                if(deliveryType === 'home') {
                                    center = 1;
                                    newOrderSzone.value = order.sZone
                                }
                            }
                            
                            

                        } else {
                            if(dist1 < minDist1 && dist1 < 0.3) {
                                minDist1 = dist1;
                                wilayaId = dataYal.data.data[i].wilaya_id
                            }
                            if(dist2 < minDist2  && dist2 < 0.3) {
                                minDist2 = dist2;
                                center = dataYal.data.data[i].center_id;
                                newOrderSzone.value = dataYal.data.data[i].commune_name
                                if(deliveryType === 'home') {
                                    center = 1;
                                    newOrderSzone.value = order.sZone
                                }
                            }
                        }
                        

                    }
                    // Si pas trouvé, parcourir les pages suivantes
                    let urlNext = dataYal.data.links?.next;
                    while (!wilayaId && urlNext) {
                        const responseNext = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=yalidineCenterNext', {
                            method: 'POST',
                            body: JSON.stringify({ url: urlNext })
                        });
                        const next = await responseNext.json();

                        for (let i = 0; i < next.data.data.length; i++) {
                            //console.log('commune_name: ', next.data.data[i].commune_name)
                            const dist1 = smartDistance(
                                next.data.data[i].wilaya_name.toLowerCase(),
                                wilaya.toLowerCase()
                            );
                            const dist2 = smartDistance(
                                next.data.data[i].commune_name.toLowerCase(),
                                order.sZone.toLowerCase()
                            );
                            if(minDist1 == 0 && minDist2 == 0 ) {
                                minDist1 = dist1;
                                minDist2 = dist2;
                                wilayaId = next.data.data[i].wilaya_id
                                center = next.data.data[i].center_id;
                                newOrderSzone.value = next.data.data[i].commune_name

                            } else {
                                if(dist1 < minDist1 && dist1 < 0.3) {
                                    minDist1 = dist1;
                                    wilayaId = next.data.data[i].wilaya_id
                                }
                                
                                if(dist2 < minDist2  && dist2 < 0.3) {
                                    minDist2 = dist2;
                                    center = next.data.data[i].center_id;
                                    newOrderSzone.value = next.data.data[i].commune_name
                                    if(deliveryType === 'home') {
                                        center = 1;
                                        newOrderSzone.value = order.sZone
                                    }
                                }
                            }
                                

                        }
                        urlNext = next.data.links?.next;
                    }
                    if (!center && deliveryType != 'home') {
                        console.error('Yalidine: Aucun centre trouvé');
                        return
                    }
                    if (center != null) {
                        // Préparation des informations du colis
                        const splitName = (fullName) => {
                            const parts = fullName.split(' ');
                            return { firstname: parts.shift(), familyname: parts.join(' ') };
                        };
                        //console.log('newOrderSzone.value: ', newOrderSzone.value)
                        const { firstname, familyname } = splitName(order.name);
                        const parcels = [{
                            order_id: `${(order.id)}-${(order.owner)}`,
                            from_wilaya_name: "Tipaza",
                            firstname,
                            familyname,
                            contact_phone: order.phone,
                            address: order.mZone,
                            to_commune_name: newOrderSzone.value,
                            to_wilaya_name: wilaya,
                            product_list: product_name,
                            price: parseInt(total),
                            do_insurance: false,
                            declared_value: parseInt(total),
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
                        const response2 = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=addYalidineOrder', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ parcels }),
                        });
                        const data2 = await response2.json();
                        console.log('yalidine: ', data2)

                        if (data2.success) {
                            
                            const trackingCode = data2.data[orderId].tracking;

                            if(trackingCode) {
                                await updateOrderValue((order.id), 'tracking_code', trackingCode);
                                await updateOrderValue((order.id), 'status', 'shipping');
                            }
                            
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
                
                
                const response = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=getGuepexCenter');
                const dataYal = await response.json();

                let wilayaId = 0, center = null;

                let minDist1 = 0, minDist2 = 0;

                if (dataYal.success) {
                    // Recherche dans la première page
                    for (let i = 0; i < dataYal.data.data.length; i++) {
                        
                        //console.log('commune_name: ', dataYal.data.data[i].commune_name)
                        const dist1 = smartDistance(
                            dataYal.data.data[i].wilaya_name.toLowerCase(),
                            wilaya.toLowerCase()
                        );
                        const dist2 = smartDistance(
                            dataYal.data.data[i].commune_name.toLowerCase(),
                            order.sZone.toLowerCase()
                        );
                        if(minDist1 == 0 && minDist2 == 0) {
                            minDist1 = dist1;
                            minDist2 = dist2;
                            if(dist1 < 0.3) {
                                wilayaId = dataYal.data.data[i].wilaya_id
                            }
                            if(dist2 < 0.3) {
                                center = dataYal.data.data[i].center_id;
                                newOrderSzone.value = dataYal.data.data[i].commune_name
                                if(deliveryType === 'home') {
                                    center = 1;
                                    newOrderSzone.value = order.sZone
                                }
                            }
                            
                            

                        } else {
                            if(dist1 < minDist1 && dist1 < 0.3) {
                                minDist1 = dist1;
                                wilayaId = dataYal.data.data[i].wilaya_id
                            }
                            if(dist2 < minDist2  && dist2 < 0.3) {
                                minDist2 = dist2;
                                center = dataYal.data.data[i].center_id;
                                newOrderSzone.value = dataYal.data.data[i].commune_name
                                if(deliveryType === 'home') {
                                    center = 1;
                                    newOrderSzone.value = order.sZone
                                }
                            }
                        }
                        

                    }
                    // Si pas trouvé, parcourir les pages suivantes
                    let urlNext = dataYal.data.links?.next;
                    while (!wilayaId && urlNext) {
                        const responseNext = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=guepexCenterNext', {
                            method: 'POST',
                            body: JSON.stringify({ url: urlNext })
                        });
                        const next = await responseNext.json();

                        for (let i = 0; i < next.data.data.length; i++) {
                            //console.log('commune_name: ', next.data.data[i].commune_name)
                            const dist1 = smartDistance(
                                next.data.data[i].wilaya_name.toLowerCase(),
                                wilaya.toLowerCase()
                            );
                            const dist2 = smartDistance(
                                next.data.data[i].commune_name.toLowerCase(),
                                order.sZone.toLowerCase()
                            );
                            if(minDist1 == 0 && minDist2 == 0 ) {
                                minDist1 = dist1;
                                minDist2 = dist2;
                                wilayaId = next.data.data[i].wilaya_id
                                center = next.data.data[i].center_id;
                                newOrderSzone.value = next.data.data[i].commune_name

                            } else {
                                if(dist1 < minDist1 && dist1 < 0.3) {
                                    minDist1 = dist1;
                                    wilayaId = next.data.data[i].wilaya_id
                                }
                                if(dist2 < minDist2  && dist2 < 0.3) {
                                    minDist2 = dist2;
                                    center = next.data.data[i].center_id;
                                    newOrderSzone.value = next.data.data[i].commune_name
                                    if(deliveryType === 'home') {
                                        center = 1;
                                        newOrderSzone.value = order.sZone
                                    }
                                }
                            }
                                

                        }
                        urlNext = next.data.links?.next;
                    }
                    
                    if (!center && deliveryType != 'home') {
                        console.error('Guepex: Aucun centre trouvé');
                        return
                    }
                    if (center != null) {
                        // Préparation des informations du colis
                        const splitName = (fullName) => {
                            const parts = fullName.split(' ');
                            return { firstname: parts.shift(), familyname: parts.join(' ') };
                        };
                        const { firstname, familyname } = splitName(order.name);
                        const parcels = [{
                            order_id: `${(order.id)}-${(order.owner)}`,
                            from_wilaya_name: "Tipaza",
                            firstname,
                            familyname,
                            contact_phone: order.phone,
                            address: order.mZone,
                            to_commune_name: newOrderSzone.value,
                            to_wilaya_name: wilaya,
                            product_list: product_name,
                            price: parseInt(total),
                            do_insurance: false,
                            declared_value: parseInt(total),
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
                    const response2 = await fetch('https://zoxcom.pietycloth.com/backend/api.php?action=addGuepexOrder', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ parcels }),
                    });
                    const data2 = await response2.json();

                    if (data2.success) {

                        const trackingCode = data2.data[orderId].tracking;

                        if(trackingCode) {
                            await updateOrderValue((order.id), 'tracking_code', trackingCode);
                            await updateOrderValue((order.id), 'status', 'shipping');
                        }
                    } else {
                        console.error(`Guepex error: ${data2.message}`);
                    }
                    }
                } else {
                    console.error(`Guepex fetch error: ${dataYal.message}`);
                }
            } catch (err) {
                console.error("Guepex request error:", err);
            }
        }
    };

    // Helper for delivery if needed (basic)
    const levenshteinDistance = (a, b) => {
        if(a.length == 0) return b.length;
        if(b.length == 0) return a.length;
        var matrix = [];
        var i;
        for(i = 0; i <= b.length; i++){ matrix[i] = [i]; }
        var j;
        for(j = 0; j <= a.length; j++){ matrix[0][j] = j; }
        for(i = 1; i <= b.length; i++){
            for(j = 1; j <= a.length; j++){
                if(b.charAt(i-1) == a.charAt(j-1)){
                    matrix[i][j] = matrix[i-1][j-1];
                } else {
                    matrix[i][j] = Math.min(matrix[i-1][j-1] + 1, Math.min(matrix[i][j-1] + 1, matrix[i-1][j] + 1));
                }
            }
        }
        return matrix[b.length][a.length];
    }

    const filterBy = async (by, value) => {
        if (by === 'status') {
             await getOrders({ status: value });
        } else if (by === 'all') {
             await getOrders();
        } else if (typeof by === 'object') {
             // Advanced filter object
             await getOrders(by);
        }
    }

    const search = async (value) => {
        // Use server-side search
        await getOrders({ search: value });
    }


    const updateOrderValue = async (id, status, value, owner) => {

        //loading.value = true;
        const updateOrder = JSON.stringify({
                id: id,
                status: status,
                value: value,
                owner: owner
        });

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=updateOrderValue', {
        method: 'POST',
        body: updateOrder,
        });
        if(!response2.ok){
            //this.orLog = "error in response";
            //loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            //this.orLog = textResponse.data;

            //await getOrders();
            updated.value = 1;
            //loading.value = false;
        } else {
            if(textResponse.message != 'Not enough stock available.') {
                updated.value = -1;
                orLog.value = textResponse.message;
            } else {
                updated.value = 1;
            }
            
            //loading.value = false;
        }
        //loading.value = false;
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
        search,
        filterBy,
        getProduct,
        resultProduct,
        updated,
        orLog,
        getOrder,
    };
};