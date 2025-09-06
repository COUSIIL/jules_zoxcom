#!/bin/bash
# 🚀 Script d’installation pour Nuxt 3 dans Jules

# Activer corepack (permet d'utiliser pnpm/yarn facilement)
corepack enable

# Choisir pnpm (recommandé pour Nuxt 3, mais tu peux mettre npm ou yarn)
corepack prepare pnpm@latest --activate

# Installer les dépendances
pnpm install

# Compiler le projet en mode production
pnpm build

# (Optionnel) Lancer les tests si tu en as
# pnpm test
