# Fleet API

API Symfony de gestion de flottes de vaisseaux.

## Stack

| Service         | Image                       | Accès                                   |
|-----------------|-----------------------------|-----------------------------------------|
| `php`           | PHP 8.4 FPM (Symfony 7.4)   | —                                       |
| `nginx`         | nginx 1.27                  | http://localhost:8080                   |
| `frontend`      | React 19 + Vite (Node 24)   | http://localhost:5173                   |
| `worker`        | consumer Messenger `async`  | —                                       |
| `database`      | PostgreSQL 17               | `localhost:5432` (app / app, fleet_api) |
| `elasticsearch` | Elasticsearch 8.18          | http://localhost:9200                   |
| `elasticvue`    | Elasticvue (UI Elasticsearch) | http://localhost:8081                 |
| `rabbitmq`      | RabbitMQ 4 + management     | `localhost:5672`, UI http://localhost:15672 (guest / guest) |

## Démarrage

```bash
make up          # ou : UID=$(id -u) GID=$(id -g) docker compose up -d --build
make logs s=php  # suivre la création du projet
make health      # {"status":"ok", ...}
```

Au **premier lancement**, le container `php` détecte l'absence de `composer.json` et :

1. crée le squelette Symfony (`symfony/skeleton`, version `SYMFONY_VERSION`, 7.4 LTS par défaut) ;
2. installe Doctrine ORM + migrations, Messenger + transport AMQP, Serializer, Validator,
   le client officiel Elasticsearch et MakerBundle (dev).

Les lancements suivants font simplement `composer install` si `vendor/` manque, puis
exécutent les migrations en attente.

Le container `frontend` fait de même : si `frontend/package.json` est absent, il génère une
application React + TypeScript avec Vite (`npm create vite`, template `react-ts`), installe les
dépendances puis lance le serveur de dev (hot reload). L'URL de l'API est exposée au code via
`import.meta.env.VITE_API_URL`.

## Configuration déjà en place

- `config/packages/messenger.yaml` : transport `async` sur RabbitMQ, échecs stockés en base (`failed`).
- `config/packages/elasticsearch.yaml` : service `Elastic\Elasticsearch\Client` injectable par autowiring.
- `src/Controller/HealthController.php` : `GET /health` teste les trois services.

Les ports, identifiants et la version de Symfony se surchargent via variables d'environnement
(`HTTP_PORT`, `POSTGRES_PORT`, `POSTGRES_PASSWORD`, `SYMFONY_VERSION`…).

## Commandes utiles

```bash
make console c="make:entity Ship"
make migration && make migrate
make composer c="require api"   # API Platform, si besoin
make sh
make npm c="install axios"      # npm dans le container frontend
```

## Comptes de test

Comptes présents dans la base PostgreSQL locale (dev uniquement), tous avec le mot de passe
`password123` :

| Email             | Rôle       |
|-------------------|------------|
| admin@fleet.test  | ROLE_ADMIN |
| boss@fleet.test   | ROLE_ADMIN |
| inter@fleet.test  | ROLE_ADMIN |
| pilote@fleet.test | ROLE_USER  |
| jwt@fleet.test    | ROLE_USER  |

Obtenir un token JWT puis appeler l'API :

```bash
TOKEN=$(curl -s -X POST http://localhost:8080/api/auth \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@fleet.test","password":"password123"}' | jq -r .token)

curl -H "Authorization: Bearer $TOKEN" http://localhost:8080/api
```

Les mots de passe sont hachés en base : un mot de passe oublié se réinitialise. Pour remettre
`password123` sur tous les comptes `@fleet.test` :

```bash
HASH=$(docker compose exec -T php php bin/console security:hash-password password123 \
  'App\User\Entity\User' -n | grep -oE '\$2y\$[^ ]+')

docker compose exec -T database psql -U app -d fleet_api \
  -c "update users set password_hash='$HASH' where email like '%@fleet.test';"
```

Créer un nouvel admin (8 caractères minimum) :

```bash
make console c="app:user:create-admin nouveau@fleet.test --password=password123"
```
