
// --- Le Composable ---

import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import QRCode from "qrcode";

import logoZox from "../public/zoxcom.svg";


import arabicFont from '../public/fonts/IBM_Plex_Sans_Arabic/IBMPlexSansArabic-Regular-base64.js'


export const useExporter = () => {

    const exportToCSV = (order) => {
    const csv = orderToCSV(order)

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.setAttribute('download', `commande_${order.id}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    }


    function orderToCSV(order) {

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
    let csv = headers.join(",") + "\n";

    var type
    if(order.type == 1) {
        type = 'stop desk'
    } else {
        type = 'home'
    }

    let values = [
        order.id,
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
    csv += values.join(",") + "\n";

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




    const exportToThermalPDF = async (order) => {
        const JsBarcode = (await import("jsbarcode")).default;

        const doc = new jsPDF({
            orientation: "p",
            unit: "mm",
            format: [80, 400]
        });

        // FONT
        doc.addFileToVFS("IBMPlexSansArabic-Regular.ttf", arabicFont);
        doc.addFont("IBMPlexSansArabic-Regular.ttf", "IBMPlex", "normal");
        doc.setFont("IBMPlex");

        // LOGO centré
        const scale = 3;
        const logoPng = await svgToPng(logoZox, 150 * scale, 40 * scale);
        doc.addImage(logoPng, "PNG", 15, 5, 50, 15);

        doc.setFontSize(14);
        doc.text("Bordereau de livraison", 40, 24, { align: "center" });

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
        doc.setFontSize(8);
        doc.setTextColor(40);

        // ID LABEL
        if (order.id) {
            doc.text(`ID`, 20, y - 2, { align: "center" });
        }

        // TRACKING LABEL
        if (order.tracking) {
            doc.text(`Tracking`, 60, y - 2, { align: "center" });
        }

        // ========= QR CODES =========
        if (idCanvas) doc.addImage(idCanvas, "PNG", 5, y, 30, 30);
        if (qrCanvas) doc.addImage(qrCanvas, "PNG", 45, y, 30, 30);

        y += 35;

        // ID LABEL
        if (order.id) {
            doc.text(`${order.id}`, 20, y, { align: "center" });
        }

        // TRACKING LABEL
        if (order.tracking) {
            doc.text(`${order.tracking}`, 60, y, { align: "center" });
        }

        y += 10;

        // Ligne fine
        // ===== SEPARATOR LÉGER =====
        doc.setDrawColor(200);
        doc.line(5, y, 75, y);
        y += 4;

        // ===================================================
        // =============   CONTAINER : INFO CLIENT   ==========
        // ===================================================
        doc.setFillColor(245);               // léger gris
        doc.roundedRect(5, y, 70, 6, 1, 1, "F");
        doc.setFontSize(11);
        doc.text("Détails Client", 40, y + 4, { align: "center" });

        y += 10;
        doc.setFontSize(10);

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
            doc.text(`${k}`, 6, y);
            doc.text(String(v ?? ""), 30, y);
            y += 5;
        });

        // Petit espace
        y += 2;


        // ===================================================
        // =============   CONTAINER : LIVRAISON   ============
        // ===================================================
        doc.setFillColor(245);
        doc.roundedRect(5, y, 70, 6, 1, 1, "F");
        doc.setFontSize(11);
        doc.text("Adresse & Livraison", 40, y + 4, { align: "center" });

        y += 10;
        doc.setFontSize(10);

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
            doc.text(`${k}`, 6, y);
            doc.text(String(v ?? ""), 30, y);
            y += 5;
        });

        y += 2;


        // ===================================================
        // =============   CONTAINER : STATUT + EXTRA   ========
        // ===================================================
        doc.setFillColor(245);
        doc.roundedRect(5, y, 70, 6, 1, 1, "F");
        doc.setFontSize(11);
        doc.text("Infos Supplémentaires", 40, y + 4, { align: "center" });

        y += 10;
        doc.setFontSize(10);

        const extra = [
            ["Délégué :", order.delegated == "1" ? "Oui" : "Non"],
            ["Activité :", order.activity || "Aucune"],
            ["ID Rappel :", order.reminder_id || "—"],
            ["Discount :", order.discount || "0"],
            ["Total :", order.total + " DA"],
            ["Quantité totale :", order.totalQty],
        ];

        extra.forEach(([k, v]) => {
            doc.text(`${k}`, 6, y);
            doc.text(String(v ?? ""), 40, y);
            y += 5;
        });

        y += 6;
        doc.setDrawColor(200);
        doc.line(5, y, 75, y);
        y += 8;


        // ===================================================
        // =============   CONTAINER : NOTES   =================
        // ===================================================
        if (order.note?.length > 0) {

            doc.setFillColor(245);
            doc.roundedRect(5, y, 70, 6, 1, 1, "F");
            doc.setFontSize(11);
            doc.text("Notes", 40, y + 4, { align: "center" });

            y += 10;

            order.note.forEach(n => {
                doc.setFontSize(10);
                doc.setTextColor(80);

                doc.text(`• ${n.text || "(vide)"}`, 8, y); y += 4;
                doc.text(`Couleur: ${n.color}`, 8, y); y += 4;
                doc.text(`Client: ${n.isClientNote ? "Oui" : "Non"}`, 8, y); y += 4;

                if (n.user) {
                    doc.text(`User: ${n.user}`, 8, y); 
                    y += 4;
                }

                y += 2;
            });

            doc.setTextColor(0);
            y += 4;
            doc.line(5, y, 75, y);
            y += 8;
        }

        // ========= TABLE PRODUITS =========
        autoTable(doc, {
            startY: y,
            margin: { left: 5, right: 5 },
            tableWidth: 70,
            head: [["Produit", "Qté", "Prix"]],
            styles: { font: "IBMPlex", fontSize: 10, cellPadding: 1 },
            headStyles: { fillColor: [0, 0, 0], textColor: 255 },
            body: order.items.map(it => [
                it.productName,
                it.qty.toString(),
                it.price + " DA"
            ])
        });

        y = doc.lastAutoTable.finalY + 8;

        // ========= TOTAL =========
        doc.setFontSize(13);
        doc.text(`TOTAL : ${order.total} DA`, 5, y);
        y += 10;

        // FOOTER
        doc.setFontSize(10);
        doc.text("Merci pour votre confiance.", 40, y, { align: "center" });
        y += 5;
        doc.text("Zoxcom", 40, y, { align: "center" });

        doc.save(`receipt_${order.id}.pdf`);
    };


    return {
        exportToCSV,
        exportToThermalPDF
    }
}