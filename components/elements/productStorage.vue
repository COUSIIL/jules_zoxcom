<template>

</template>

<script setup>
    /*
    dans cette page créer le systeme de gestion de stock pour chaque produits une fois sauvegarder utilise les composant et style du project :
        - pour chaque varient de model créer un code unique ex: nomDuModel-white-XL-1-variableA5Caractere, nomDuModel-white-XL-2-variableA5Caractere ...; et si aucun varient est créer génere un code unique pour chaque model ex: nomDuModel-1-variableA5Caractere;
        - affiche tous ces code avec un code QR dans une liste afin de pouvoir visualiser la qty et la déffirence, et ajoute une option imprimer pour toutes la liste des codes QR.
        - améliore le systeme de gestion du stock si l'utilisateur veut renfluer et ajouter ou retirer du stock pour update la qty du produit model et varient correctement
        - une fois sauvegarder la base de donnée du stockage doit contenire id, product_id, isModal: 1 ou 0 si isVarient, unique_code;
        - donc une fois la commande est confirmer ou retourner modifier la commande pour attribuer ou retirer les unique_codes de chaque produits a fin de pouvoir suivre correctement le model ou le varient. et n'oublie pas de update le stock "qty" correctement.
    */
</script>

<style scoped>

</style>