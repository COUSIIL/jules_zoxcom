#!/bin/bash

# ⚙️ Configuration
REMOTE_USER="mshurcnp"
REMOTE_HOST="57.128.97.32"
REMOTE_PORT="5804"
REMOTE_PATH="/home/mshurcnp/management.hoggari.com"
DIST_DIR=".output/public"
BACKEND_DIR="backend"
SSH_KEY="$HOME/.ssh/id_rsa"

# 🧩 Étape 1 : Build du projet Nuxt
echo "🔨 Installation des dépendances..."
pnpm install || { echo "❌ Erreur pnpm install"; exit 1; }

echo "🏗️ Génération du site statique..."
pnpm run generate || { echo "❌ Erreur pnpm run generate"; exit 1; }

# 🧩 Étape 2 : Installation des dépendances PHP localement
echo "📦 Installation des dépendances PHP..."
cd "$BACKEND_DIR" || exit 1
composer install --no-dev --optimize-autoloader || { echo "❌ Erreur composer"; exit 1; }
cd ..

# 🧩 Étape 3 : Déploiement via SFTP
echo "🚀 Déploiement via SFTP..."
sftp -i "$SSH_KEY" -P "$REMOTE_PORT" "$REMOTE_USER@$REMOTE_HOST" <<EOF
# 📁 Création des dossiers nécessaires
mkdir "$REMOTE_PATH"
mkdir "$REMOTE_PATH/backend"
mkdir "$REMOTE_PATH/backend/config"
mkdir "$REMOTE_PATH/backend/sql"
mkdir "$REMOTE_PATH/backend/sql/get"

# 🖥️ Upload du frontend Nuxt généré
cd "$REMOTE_PATH"
put -r "$DIST_DIR"/*

# 🗄️ Upload du backend (PHP + config)
cd "$REMOTE_PATH/backend"
put -r "$BACKEND_DIR"/*

# ⚙️ Upload du .env (si existant)
put "$BACKEND_DIR/.env" "$REMOTE_PATH/backend/.env"

# ⚙️ Upload du vendor (PHP)
put -r vendor "$REMOTE_PATH/backend/vendor"

bye
EOF

echo "✅ Déploiement terminé avec succès !"
