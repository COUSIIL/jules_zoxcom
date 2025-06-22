#!/bin/bash

# Arrêter le script en cas d'erreur
set -e

# Configuration
REMOTE_USER="mshurcnp"
REMOTE_HOST="57.128.97.32"
REMOTE_PORT="5804"
REMOTE_PATH="/home/mshurcnp/management.hoggari.com"
SSH_KEY="~/.ssh/id_rsa"  # Remplace si nécessaire

# 🔥 Étape 1 : Installer les dépendances et générer le projet Nuxt
echo "Installing dependencies for Nuxt..."
pnpm install

echo "Generating Nuxt project..."
pnpm run generate

# 🔥 Étape 2 : Installer les dépendances PHP dans le backend
echo "Installing PHP dependencies..."
cd backend
composer install --no-dev --optimize-autoloader
cd ..

# 🔥 Étape 3 : Créer la structure côté serveur si elle n'existe pas
echo "Creating remote directory structure..."
ssh -i $SSH_KEY -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST <<EOF
    mkdir -p $REMOTE_PATH
    mkdir -p $REMOTE_PATH/backend/config
    mkdir -p $REMOTE_PATH/backend/sql/get
EOF

# 🔥 Étape 4 : Déployer le frontend
echo "Deploying frontend..."
scp -i $SSH_KEY -P $REMOTE_PORT -r ./dist/* $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH

# 🔥 Étape 5 : Déployer le backend (sauf les fichiers sensibles)
echo "Deploying backend..."
scp -i $SSH_KEY -P $REMOTE_PORT -r ./backend/* $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/backend

# 🔥 Étape 6 : Déployer le fichier .env (⚠️ Sécurisé)
echo "Deploying .env..."
scp -i $SSH_KEY -P $REMOTE_PORT ./backend/.env $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/backend

# 🔥 Étape 7 : Déployer le dossier vendor
echo "Deploying vendor directory..."
scp -i $SSH_KEY -P $REMOTE_PORT -r ./vendor $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH

# 🔥 Étape 8 : Configurer les permissions
echo "Setting permissions..."
ssh -i $SSH_KEY -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST <<EOF
    chmod 600 $REMOTE_PATH/backend/.env
    chmod 600 $REMOTE_PATH/backend/config/dbConfig.php
    chmod -R 755 $REMOTE_PATH/backend/vendor
    chown -R www-data:www-data $REMOTE_PATH
EOF

# 🔥 Étape 9 : Redémarrer le serveur (si nécessaire)
echo "Restarting server..."
ssh -i $SSH_KEY -p $REMOTE_PORT $REMOTE_USER@$REMOTE_HOST <<EOF
    sudo systemctl restart nginx
EOF

# ✅ ✅ ✅ FIN ✅ ✅ ✅
echo "🚀 Deployment complete!"
