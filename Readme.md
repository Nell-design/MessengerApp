# MessengerApp

Ce projet est une application de messagerie temps réel construite avec Laravel (backend) et Vue 3 + Inertia.js (frontend).

## Prérequis

- **PHP >= 8.2**
- **Composer**
- **Node.js >= 18** (recommandé : Node 18 ou 20)
- **npm** (ou yarn)
- **Base de données** : SQLite (par défaut), MySQL, PostgreSQL ou autre supporté par Laravel

## Installation

1. **Cloner le dépôt**

```bash
git clone <url-du-repo>
cd MessengerApp
```

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Installer les dépendances Node.js**

```bash
npm install
```

4. **Configurer l'environnement**

Copiez le fichier `.env.example` en `.env` (si disponible) ou créez un fichier `.env` à la racine du projet. Adaptez les variables suivantes selon votre environnement :

```
APP_NAME=MessengerApp
APP_ENV=local
APP_KEY= # Généré à l'étape suivante
APP_URL=http://localhost

DB_CONNECTION=sqlite # ou mysql, pgsql, etc.
DB_DATABASE= # Chemin vers database.sqlite ou nom de la base
DB_USERNAME= # (si MySQL/PostgreSQL)
DB_PASSWORD= # (si MySQL/PostgreSQL)

BROADCAST_CONNECTION=reverb # ou pusher, etc.
REVERB_APP_KEY= # (si Reverb utilisé)
REVERB_APP_SECRET=
REVERB_APP_ID=
REVERB_HOST=
REVERB_PORT=443
REVERB_SCHEME=https

PUSHER_APP_KEY= # (si Pusher utilisé)
PUSHER_APP_SECRET=
PUSHER_APP_ID=
PUSHER_APP_CLUSTER=

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="MessengerApp"

# (Ajoutez d'autres variables selon vos besoins)
```

5. **Générer la clé d'application**

```bash
php artisan key:generate
```

6. **Créer la base de données (si SQLite)**

```bash
touch database/database.sqlite
```

7. **Lancer les migrations et seeders**

```bash
php artisan migrate --seed
```

## Lancement du projet

### En mode développement

Dans deux terminaux séparés :

**Terminal 1 : Serveur Laravel**
```bash
php artisan serve
```

**Terminal 2 : Vite (frontend)**
```bash
npm run dev
```

Ou, pour tout lancer en une seule commande (si `npx` est installé) :
```bash
composer run dev
```

### Accès à l’application

Ouvrez [http://localhost:8000](http://localhost:8000) dans votre navigateur.

## Commandes utiles

- `php artisan migrate:fresh --seed` : Réinitialiser la base et reseeder
- `npm run build` : Build des assets pour la production
- `composer run dev:ssr` : Lancer le SSR (Server Side Rendering)
- `composer test` : Lancer les tests backend

## Problèmes fréquents

- **Erreur de clé d’application** : relancer `php artisan key:generate`
- **Problème de base de données** : vérifier la config `.env` et que la base existe
- **Assets non générés** : relancer `npm install` puis `npm run dev`

---

Pour toute question, consultez la documentation Laravel ou ouvrez une issue sur le dépôt.
