
// --- Le Composable ---

import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import QRCode from "qrcode";

import logoZox from "../public/zoxcom.svg";


import arabicFont from '../public/fonts/IBM_Plex_Sans_Arabic/IBMPlexSansArabic-Regular-base64.js'


export const useExporter = () => {

    const exportToCSV = (order) => {
        const date = new Date()
        var csv = []
        var str = ``
        if(order.length > 0) {
            for(let i in order) {
                
                if(i == 0) {
                    csv.push(orderToCSV(order[i]))
                } else {
                    csv.push(orderToCSV(order[i], true))
                }
            }
            str = `${order.length}-commandes ${date.getDate()}-${date.getMonth() + 1}-${date.getFullYear()}.csv`
        } else {
            csv.push(orderToCSV(order))
            str = `commande ${order.id}.csv`
        }

    const blob = new Blob(csv, { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    
    
    link.href = URL.createObjectURL(blob)
    link.setAttribute('download', str)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    }


    function orderToCSV(order, isMore = false) {

    let headers = [
        "id","create","name","phone","country","ip","status","tracking","type","method",
        "delegated","activity",
        "reminder_id","profile_image","deliveryZone","sZone","mZone","deliveryValue",
        "discount","total","totalQty","wilaya"
    ];

    // ---- Dynamically add product columns ----
    order.items.forEach((item, i) => {
        const n = i + 1;
        headers.push(
        `item_${n}_id`,
        `item_${n}_name`,
        `item_${n}_price`,
        `item_${n}_qty`,
        `item_${n}_image`
        );
    });

    // ---- Dynamically add note columns ----
    order.note.forEach((note, i) => {
        const n = i + 1;
        headers.push(
        `note_${n}_text`,
        `note_${n}_color`,
        `note_${n}_isClientNote`,
        `note_${n}_user`
        );
    });

    // ---- Create HEADER line ----
    let csv;
    if(!isMore) {
        csv = headers.join(",") + "\n";
    }
    

    var type
    if(order.type == 1) {
        type = 'stop desk'
    } else {
        type = 'home'
    }

    let values = [
        parseInt(order.id),
        order.create,
        order.name,
        order.phone,
        order.country,
        order.ip,
        order.status,
        order.tracking,
        type,
        order.method,
        order.delegated,
        order.activity,
        order.reminder_id,
        order.profile_image,
        order.deliveryZone,
        order.sZone,
        order.mZone,
        order.deliveryValue,
        order.discount,
        order.total,
        order.totalQty,
        order.deliveryZone,                // wilaya?
    ];

    // ---- Add item values ----
    order.items.forEach((item) => {
        values.push(
        item.id,
        cleanCSV(item.productName),
        item.price,
        item.qty,
        item.image
        );
    });

    // ---- Add note values ----
    order.note.forEach((n) => {
        values.push(
        cleanCSV(n.text),
        n.color,
        n.isClientNote,
        n.user
        );
    });

    // ---- Create the SINGLE DATA LINE ----
    if(csv === undefined) {
        csv = values.join(",") + "\n";
    } else {
        csv += values.join(",") + "\n";
    }


    return csv;
    }

    // Nettoyage texte (éviter le cassage CSV)
    function cleanCSV(v) {
    if (!v) return "";
    return `"${String(v).replace(/"/g, '""')}"`
    }




    const svgToPng = (svgUrl, width = 200, height = 60) => {
    return new Promise((resolve) => {
        const img = new Image();
        img.onload = function () {
        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, width, height);

        resolve(canvas.toDataURL("image/png"));
        };
        img.src = svgUrl;
    });
    };




    const exportListToPDF = async (orders) => {
        const doc = new jsPDF({
            orientation: "l",
            unit: "mm",
            format: "a4"
        });

        // FONT
        doc.addFileToVFS("IBMPlexSansArabic-Regular.ttf", arabicFont);
        doc.addFont("IBMPlexSansArabic-Regular.ttf", "IBMPlex", "normal");
        doc.setFont("IBMPlex");

        const columns = [
             { header: 'ID', dataKey: 'id' },
             { header: 'Date', dataKey: 'create' },
             { header: 'Client', dataKey: 'name' },
             { header: 'Phone', dataKey: 'phone' },
             { header: 'Wilaya', dataKey: 'deliveryZone' },
             { header: 'Commune', dataKey: 'sZone' },
             { header: 'Status', dataKey: 'status' },
             { header: 'Total', dataKey: 'total' },
             { header: 'Produits', dataKey: 'products' },
        ];
        
        const data = orders.map(o => ({
            id: o.id,
            create: o.create,
            name: o.name,
            phone: o.phone,
            deliveryZone: o.deliveryZone,
            sZone: o.sZone,
            status: o.status,
            total: o.total + ' DA',
            products: o.items.map(i => `${i.productName} (x${i.qty})`).join(', ')
        }));

        autoTable(doc, {
            columns: columns,
            body: data,
            styles: { font: "IBMPlex", fontSize: 10 },
            headStyles: { fillColor: [40, 40, 40] }
        });

        doc.save(`orders_list_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    const exportToThermalPDF = async (input) => {
        //const JsBarcode = (await import("jsbarcode")).default;
        
        // Si input n'est pas un tableau, on le transforme en tableau pour unifier le traitement
        const orders = Array.isArray(input) ? input : [input];

        const doc = new jsPDF({
            orientation: "p",
            unit: "mm",
            format: "a6"
        });

        // FONT
        doc.addFileToVFS("IBMPlexSansArabic-Regular.ttf", arabicFont);
        doc.addFont("IBMPlexSansArabic-Regular.ttf", "IBMPlex", "normal");
        doc.setFont("IBMPlex");

        for (let i = 0; i < orders.length; i++) {
            const order = orders[i];
            
            if (i > 0) {
                doc.addPage("a6", "p");
            }

        // LOGO centré
        const scale = 4;
        const logoPng = await svgToPng(logoZox, 100 * scale, 30 * scale);
        doc.addImage(logoPng, "PNG", 27.5, 5, 50, 15);

        doc.setFontSize(10);
        doc.text("Bordereau de livraison", 52.5, 24, { align: "center" });

        let y = 30;

        // ========= LINE (Barcode + QR) ==========
        let idCanvas = null;
        let qrCanvas = null;

        if (order.id) {
            idCanvas = await QRCode.toDataURL(order.id.toString(), { margin: 0, width: 100 });
        }

        if (order.tracking) {
            qrCanvas = await QRCode.toDataURL(order.tracking.toString(), { margin: 0, width: 100 });
        }

        // ========= TITRES AU-DESSUS DES QR =========
        doc.setFontSize(5);
        doc.setTextColor(40);

        // ID LABEL
        if (order.id) {
            doc.text(`ID`, 30, y - 2, { align: "center" });
        }

        // TRACKING LABEL
        if (order.tracking) {
            doc.text(`Tracking`, 75, y - 2, { align: "center" });
        }

        // ========= QR CODES =========
        if (idCanvas) doc.addImage(idCanvas, "PNG", 23, y, 15, 15);
        if (qrCanvas) doc.addImage(qrCanvas, "PNG", 68, y, 15, 15);

        y += 20;

        // ID LABEL
        if (order.id) {
            doc.text(`${order.id}`, 30, y, { align: "center" });
        }

        // TRACKING LABEL
        if (order.tracking) {
            doc.text(`${order.tracking}`, 75, y, { align: "center" });
        }

        y += 5;

        // Ligne fine
        // ===== SEPARATOR LÉGER =====
        doc.setDrawColor(200);
        doc.line(10, y, 95, y);
        y += 2;

        // ===================================================
        // =============   CONTAINER : INFO CLIENT   ==========
        // ===================================================
        doc.setFillColor(245);               // léger gris
        doc.roundedRect(10, y, 85, 6, 1, 1, "F");
        doc.setFontSize(6);
        doc.text("Détails Commande", 52.5, y + 4, { align: "center" });

        y += 10;
        doc.setFontSize(5);
        var cY = y;

        // Organisation des infos par section
        const info = [
            ["ID :", order.id],
            ["Créé le :", order.create],
            ["Nom :", order.name],
            ["Téléphone :", order.phone],
            ["Pays :", order.country],
            ["IP :", order.ip],
        ];

        // Contenu
        info.forEach(([k, v]) => {
            doc.text(`${k}`, 12, y);
            doc.text(String(v ?? ""), 25, y);
            y += 2;
        });
        

        const type = order.type == 1 ? "stop desk" : "home";

        const delivery = [
            ["Wilaya :", order.deliveryZone],
            ["Commune :", order.sZone],
            ["Adresse :", order.mZone],
            ["Méthode :", order.method],
            ["Statut :", order.status],
            ["Type :", type],
            ["Frais livraison :", order.deliveryValue + " DA"],
        ];

        delivery.forEach(([k, v]) => {
            doc.text('', 50, cY);
            doc.text(`${k}`, 50, cY);
            doc.text(String(v ?? ""), 65, cY);
            cY += 2;
        });

        y += 1;


        // ===================================================
        // =============   CONTAINER : NOTES   =================
        // ===================================================
        if (order.note?.length > 0) {

            doc.setFillColor(245);

            doc.roundedRect(10, y, 85, 6, 1, 1, "F");
            doc.setFontSize(6);
            doc.text("Notes", 52.5, y + 4, { align: "center" });

            y += 10;

            order.note.forEach(n => {
                if(n.text) {
                    doc.setFontSize(5);
                    doc.setTextColor(80);

                    if (n.user) {
                        doc.text(`Client: ${n.isClientNote ? "Oui" : "Non"}`, 13, y); y += 4;
                        y += 2;
                        doc.text(`User: ${n.user}`, 13, y); 
                        y += 2;
                        doc.text(`User: ${n.text}`, 13, y); 
                    }

                    y += 1;
                }
                
            });

            doc.setTextColor(0);
            y += 2;
            doc.line(10, y, 95, y);
            y += 4;
        }

        // ========= TABLE PRODUITS =========
        autoTable(doc, {
            startY: y,
            margin: { left: 10, right: 10 },
            tableWidth: 85,
            head: [["Produit", "Qté", "Prix"]],
            styles: { font: "IBMPlex", fontSize: 5, cellPadding: 1 },
            headStyles: { fillColor: [0, 0, 0], textColor: 255 },
            body: order.items.map(it => [
                it.productName,
                it.qty.toString(),
                it.price + " DA"
            ])
        });

        y = doc.lastAutoTable.finalY + 2;

        // ========= TOTAL =========
        doc.setFontSize(7);
        doc.text(`TOTAL : ${order.total} DA`, 10, y);
        y += 5;

        // FOOTER
        doc.setFontSize(5);
        doc.text("Merci pour votre confiance.", 52.5, y, { align: "center" });
        y += 2;
        doc.text("Zoxcom", 52.5, y, { align: "center" });
        }

        const fileName = orders.length === 1 ? `receipt_${orders[0].id}.pdf` : `receipts_batch_${new Date().getTime()}.pdf`;
        doc.save(fileName);
    };


    return {
        exportToCSV,
        exportToThermalPDF,
        exportListToPDF
    }
}