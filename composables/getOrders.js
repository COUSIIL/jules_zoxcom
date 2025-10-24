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
                console.log('data: ', data.value) 
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
        console.log('data2: ', result) 
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

    const deliverOrder = async (order, product, deliveryType, method, total, delivery) => {

        var data;

        var type;
        var product_name = '';

        const items = product.items ?? [];

        for (let pro of items) {

            if (product_name === '') {
                product_name = `${product.name}-${pro.color_name}-${pro.size}`;
            } else {
                product_name = `${product_name}, ${product.name}-${pro.color_name}-${pro.size}`;
            }
        }
        

        if(deliveryType == 'Stop Desk') {
            type = true;
        } else {
            type = false;
        }

        const info = await getDelivryM()
        var ttc
        var dropAreaID
        var dropAreaName
        for(let M of info.data) {
            if(M.delivery_name === method) {
                dropAreaID = M.drop_area_id
                dropAreaName = M.drop_area_id
                if(M.include_fees == "1") {
                    ttc = total + delivery
                } else {
                    ttc = total
                }
            }
        }

        if(method === 'ups') {
            try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUpsWilaya', {
                method: 'GET',
            });

            data = await response.json();
            } catch (error) {
                console.log('responce: ', error);
                return
            }

            

            

            try {

            var wilayaId = 0;
            const tolerance = 2; // Ajuste selon le niveau de tolérance souhaité

            for (let i = 0; i < data.length; i++) {
                const distance = levenshteinDistance(data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());

                if (distance <= tolerance) {
                    wilayaId = data[i].wilaya_id;
                    break; 
                }
            }

            if(wilayaId != 0) {
                const setToUps = {
                reference: `DNZ-${order.id}`,
                nom_client: order.name,
                telephone: order.phone,
                telephone_2: '',
                adresse: order.mZone,
                code_postal: '',
                commune: order.sZone,
                code_wilaya: wilayaId.toString(),  // ✅ Correction ici
                montant: ttc,  // ✅ Forcer string si nécessaire
                remarque: order.note,
                produit: product_name,  // ✅ Correction ici
                stock: '',
                quantite: '',
                produit_a_recupere: '',
                boutique: '',
                type: 'Livraison',
                stop_desk: type,
                weight: '',
                };

                

                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addUpsOrder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // ✅ Important !
                    },
                    body: JSON.stringify(setToUps), // ✅ Convertir en JSON ici
                });

                const data2 = await response2.json();
                if (!data2.success) {
                    console.error(`Error: ${data2.message}`);
                }
            } else {
                console.error('no wilaya found');
            }

            } catch (error) {
                console.log('responce: ', error);
            }
            
            
            
        } else if(method === 'anderson') {
            //const type = deliveryType.value === 0 ? "Livraison" : "Stop Desk";
            try {
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getAndersonWilaya', {
                method: 'GET',
            });

            data = await response.json();
            } catch (error) {
                console.error('responce: ', error);
                return
            }


            try {

            var wilayaId = 0;
            const tolerance = 2; // Ajuste selon le niveau de tolérance souhaité

            for (let i = 0; i < data.length; i++) {
                const distance = this.levenshteinDistance(data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());

                if (distance <= tolerance) {
                    wilayaId = data[i].wilaya_id;
                    break; 
                }
            }

            if(wilayaId != 0) {
                const setToUps = {
                reference: `DNZ-${order.id}`,
                nom_client: order.name,
                telephone: order.phone,
                telephone_2: '',
                adresse: order.mZone,
                code_postal: '',
                commune: order.sZone,
                code_wilaya: wilayaId.toString(),  // ✅ Correction ici
                montant: ttc,  // ✅ Forcer string si nécessaire
                remarque: order.note,
                produit: product_name,  // ✅ Correction ici
                stock: '',
                quantite: '',
                produit_a_recupere: '',
                boutique: '',
                type: 'Livraison',
                stop_desk: type,
                weight: '',
                };

                

                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=addAndersonOrder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // ✅ Important !
                    },
                    body: JSON.stringify(setToUps), // ✅ Convertir en JSON ici
                });

                const data2 = await response2.json();
                if (!data2.success) {
                    console.error(`Error: ${data2.message}`);
                }
            } else {
                console.error('no wilaya found');
            }

            } catch (error) {
                console.log('responce: ', error);
            }
            
            
            //const type = orderType.value === 0 ? "Livraison" : "Stop Desk";
        } else if (method === 'yalidine') {
            
            var center = 0;
            const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getYalidineCenter', {
            method: 'GET',
            });
            const data2 = await response2.json();
            const tolerance = 2;
            
            
            if (data2.success) {
            for (let i = 0; i < data2.data.data.length; i++) {
                const distance = this.levenshteinDistance(data2.data.data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());
                
                const distance2 = this.levenshteinDistance(data2.data.data[i].commune_name.toLowerCase(), order.sZone.toLowerCase());

                if (distance <= tolerance && distance2 <= tolerance) {
                    wilayaId = data2.data.data[i].wilaya_id;
                    center = data2.data.data[i].center_id;
                    break; 
                }
            }

            if(!wilayaId) {
                const url = data2.data.links.next

                while (!wilayaId) {

                const bodyUrl = JSON.stringify({
                    url: url,
                });
                
                const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=yalidineCenterNext', {
                    method: 'POST',
                    body: bodyUrl
                });
                    const next = await response2.json();
                    for (let i = 0; i < next.data.data.length; i++) {
                    const distance = this.levenshteinDistance(next.data.data[i].wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());
                
                    const distance2 = this.levenshteinDistance(next.data.data[i].commune_name.toLowerCase(), order.sZone.toLowerCase());

                    if (distance <= tolerance && distance2 <= tolerance) {
                        wilayaId = next.data.data[i].wilaya_id;
                        center = next.data.data[i].center_id;
                        break; 
                    }
                    }
                }

                
            }
            if(!center) {
                center = null
                console.error("Error: No center found", wilayaId, " ",  this.orderWilaya[index]);
            }


            if(center != -1) {
                const { firstname, familyname } = this.splitName(order.name)

                const parcels = [{
                order_id: `CofP-${order.id}`,
                from_wilaya_name: "Tipaza",
                firstname: firstname,
                familyname: familyname,
                contact_phone: order.phone,
                address: order.mZone,
                to_commune_name: order.sZone,
                to_wilaya_name: this.orderWilaya[index],
                product_list: product_name,
                price: this.total[index],
                do_insurance: false,
                declared_value: this.total[index],
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
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ parcels }), // ✅ ici on met le tableau dans un objet
                });
                const data2 = await response2.json();
                if (data2.success && data2.data.length > 0) {
                  const trackingCode = data2.data[0].tracking;
                  await updateOrderValue(order.id, 'tracking_code', trackingCode);
                } else {
                  console.error(`Error: ${data2.message}`);
                }
            } else {
                console.error("Error: No center found", wilayaId, " ",  this.orderWilaya[index]);
            }

            
            } else {
            console.error(`Error: ${data2.message}`);
            }


        } else if (method === 'guepex') {
            let center = null;
            let wilayaId = null;
            const tolerance = 2;


            try {
            // Première requête
            const response = await fetch('https://management.hoggari.com/backend/api.php?action=getGuepexCenter');
            const data1 = await response.json();


            if (!data1.success) {
                console.error(`Error: ${data1.message}`);
                return;
            }

            // Fonction utilitaire pour chercher dans une liste
            const searchCenters = (list) => {
                for (let item of list) {
                const distance = this.levenshteinDistance(item.wilaya_name.toLowerCase(), this.orderWilaya[index].toLowerCase());
                const distance2 = this.levenshteinDistance(item.commune_name.toLowerCase(), order.sZone.toLowerCase());

                if (distance <= tolerance && distance2 <= tolerance) {
                    wilayaId = item.wilaya_id;
                    center = item.center_id;
                    return true;
                }
                }
                return false;
            };

            // 🔎 1. On cherche dans la première page
            if (!searchCenters(data1.data.data)) {
                let url = data1.data.links?.next || null;

                let pageCount = 0;
                const MAX_PAGES = 20;

                while (url && !wilayaId && pageCount < MAX_PAGES) {
                pageCount++;
                const responseNext = await fetch(
                    'https://management.hoggari.com/backend/api.php?action=guepexCenterNext',
                    {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ url })
                    }
                );

                const next = await responseNext.json();

                if (!next?.data?.data) break;

                // Chercher dans la page
                if (searchCenters(next.data.data)) break;

                // Vérifie s'il reste d'autres pages
                if (next.data.has_more && next.data.links?.next) {
                    url = next.data.links.next;
                } else {
                    // plus de pages disponibles
                    url = null;
                }
                }
            }
            const { firstname, familyname } = this.splitName(order.name)
                const parcels = [{
                order_id: `CofP-${order.id}`,
                from_wilaya_name: "Tipaza",
                firstname: firstname,
                familyname: familyname,
                contact_phone: order.phone,
                address: order.mZone,
                to_commune_name: order.sZone,
                to_wilaya_name: this.orderWilaya[index],
                product_list: product_name,
                price: this.total[index],
                do_insurance: false,
                declared_value: this.total[index],
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
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ parcels }), // ✅ ici on met le tableau dans un objet
                });
                const data2 = await response2.json();
                if (!data2.success) {
                console.error(`Error: ${data2.message}`);
                }

            
            } catch (err) {
            console.error("Request error:", err);
            }


        }

        

    }

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


    

    return {
        data,
        log,
        existe,
        loading,
        getOrders,
        deleteOrder,
        updateOrderValue,
        deliverOrder,
    };
};