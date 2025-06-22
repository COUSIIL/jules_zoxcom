#!/bin/bash

# Texte fixe à chercher
read -p "Entrez le texte a cherhcer : " SEARCH

# Demander à l'utilisateur le nouveau texte
read -p "Entrez le nouveau texte : " REPLACE

# Dossier où faire la recherche (par défaut le dossier actuel)
DIR=${1:-.}

# Parcourir tous les fichiers et remplacer
find "$DIR" -type f -exec sed -i "s|$SEARCH|$REPLACE|g" {} +

echo "Remplacement terminé de '$SEARCH' par '$REPLACE'."
