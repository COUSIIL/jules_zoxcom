import { ref } from 'vue';

// --- Le Composable ---

export const useOrderDz = () => {

    const isWorkingDelegate = ref(false)
    const isDelegated = ref({id: '', value: 0})

    onMounted(() => {
        testOrderDz()
    })


    const fetcher = async (body) => {
        const response = await fetch(
            "https://management.hoggari.com/backend/api.php?action=postOrderDz",
            {
                method: "POST",
                //headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            }
        );
        if (!response.ok) {
            return;
        }
        var result = await response.json();
        //console.log('result: ', result)
        if(result.success) {

            isDelegated.value = {id: body.id, value: 1}
        } else {
            isDelegated.value = {id: body.id, value: 0}

        }
        

        
    }

    const testOrderDz = async () => {
        const response = await fetch(
            "https://management.hoggari.com/backend/api.php?action=testOrderDz",
            {
                method: "GET",
            }
        );
        if (!response.ok) {
            return;
        }
        var result = await response.json();
        if(result.success) {
            if(result.data.work == "1" && result.data.key) {
                isWorkingDelegate.value = true
            }

        }

        

        
    }
    const delegateOrder = async (order, product, wilaya, commune) => {

        

        let data = []

        for(let ordr of order) {

            const wilayaId = wilaya.wilaya_id
            let codePostal = ""
            for(let com of commune) {
                if(ordr.sZone == com.nom) {
                    codePostal = com.code_postal
                }
            }
            let items = []

            for (let item of ordr.items) {
                let models = []

                for (let model of item.items) {
                    let modelPrice = model.promo ? parseFloat(model.promo) : null

                    let des = ""
                    let catalog = []
                    for(let prod of product) {
                        if(model.id == prod.models.id) {
                            des = prod.description
                            catalog = prod.catalog
                            break
                        }
                    }
                    var mPrice = 0;
                    if(model?.promo && parseFloat(model.promo) > 0) {
                        mPrice = parseFloat(model.promo)
                    } else {
                        mPrice = parseFloat(model.total)
                    }

                    models.push({
                        id: parseFloat(model.id),
                        name: item.productName,
                        thumbnail: item.image,
                        description: des,
                        price: mPrice,
                        discount_price: 0,
                        quantity: parseInt(model.qty),
                        images: catalog
                    })

                    
                }
                var iPrice = 0;
                    if(item?.promo && parseFloat(item.promo) > 0) {
                        iPrice = parseFloat(item.promo)
                    } else {
                        iPrice = parseFloat(item.price)
                    }
                items.push({
                    price: iPrice,
                    quantity: parseInt(item.qty),
                    product: models[0]
                })
            }
            
            data.push({
                reference: ordr.id,
                nom_client: ordr.name,
                telephone: ordr.phone,
                telephone2: null,
                code_wilaya: wilayaId,
                wilaya: ordr.deliveryZone,
                commune: ordr.sZone,
                remarque: JSON.stringify(ordr.note),
                email: null,
                address: ordr.mZone,
                address2: null,
                code_postal: codePostal,
                ip_address: ordr.ip,
                user_agent: null,
                stop_desk: parseInt(ordr.type),
                shipping_price: parseFloat(ordr.deliveryValue),
                montant: parseFloat(ordr.total),
                items: items
            });
        }

        
        for (let command of data) {
            await fetcher(command);
        }
        

    }



    return {
        delegateOrder,
        isWorkingDelegate,
        isDelegated
    };
}