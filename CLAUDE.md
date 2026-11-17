# fleetApi — conventions et organisation de l'équipe

Application de gestion de flottes de vaisseaux inspirée de X4: Foundations : une API
Symfony + API Platform et un front qui la consomme. Ce fichier est la référence commune
à tous les agents de `.claude/agents/`.

## Documents de référence

- `docs/SPEC.md` : spécification fonctionnelle (référentiel du jeu, décisions prises,
  questions ouvertes). Les stories en découlent.
- `docs/MODEL.md` : modèle de données et organisation du code.
- `docs/stories/` : user stories (écrites par `po`).
- `docs/qa/` : rapports de recette (écrits par `qa`).

## L'équipe

| Agent | Rôle | Écrit dans |
|---|---|---|
| `po` | Product Owner : transforme un besoin en user stories avec critères d'acceptation | `docs/stories/`, `docs/SPEC.md` |
| `dev-back` | Développeur back-end Symfony / API Platform / Doctrine / Messenger / Elasticsearch | `src/`, `config/`, `migrations/`, `tests/` |
| `dev-front` | Développeur front React + TypeScript | `frontend/` |
| `qa` | Recette : vérifie chaque critère d'acceptation, écrit les tests d'acceptation et de bout en bout | `tests/`, `frontend/e2e/`, `docs/qa/` |
| `reviewer` | Revue de code, lecture seule | aucun fichier (rapport) |

La session principale joue le rôle de **lead / scrum master** : elle reçoit la
demande, délègue aux agents dans l'ordre du workflow et fait la synthèse.

## Règles de travail avec l'utilisateur

- **Aucun commit ni push sans l'accord explicite de l'utilisateur.** On lui montre le
  diff (et une capture d'écran si c'est visuel), puis on attend son « ok ».
- On avance étape par étape : une ressource ou une fonctionnalité à la fois.
- Une branche par sujet (`feature/…`) et une pull request vers `main`.
- Les agents ne commitent jamais : c'est la session principale qui le fait, après accord.

## Workflow d'une fonctionnalité

```
besoin ─► po ─► dev-back ─┬─► qa ─► reviewer ─► terminé
                dev-front ┘    │        │
                   ▲           │        │
                   └───────────┴────────┘  (retours : bugs / remarques bloquantes)
```

1. **po** rédige la story `docs/stories/FLEET-XXX-slug.md` (statut `prête`). Il pose ses questions s'il manque une info métier.
2. **dev-back** implémente la partie API en premier : le contrat d'API (endpoints, payloads) est noté dans la story, section « Contrat d'API ».
3. **dev-front** implémente l'écran à partir de ce contrat. Back et front peuvent avancer en parallèle si le contrat est déjà figé dans la story.
4. **qa** vérifie chaque critère, écrit les tests manquants et produit `docs/qa/FLEET-XXX.md` (validé / rejeté avec bugs).
5. **reviewer** relit le diff complet. Une remarque « Bloquant » renvoie au dev concerné.
6. La story passe au statut `terminée` quand QA a validé et que le reviewer n'a plus de bloquant.

Statuts d'une story : `brouillon` → `prête` → `en cours` → `en recette` → `en revue` → `terminée`.
Chaque agent met à jour le statut quand il prend ou rend la main.

## Stack
- **Back** : PHP 8.4, Symfony 7.4, API Platform 5, PostgreSQL 17 (Doctrine), RabbitMQ (Messenger), Elasticsearch 8, authentification JWT (Lexik).
- **Front** : React 19 + TypeScript, Vite, dans `frontend/`. TanStack Query, React Router, React Hook Form + Zod. Lint avec oxlint.
- Tout tourne dans Docker : jamais de PHP/Composer sur l'hôte.

## Commandes
Services `php` et `frontend` dans `compose.yaml`. Raccourcis dans le `Makefile` (`make help`).

```bash
# Back
make console c="<commande>"            # docker compose exec php bin/console <commande>
make migration && make migrate
make qa                                # PHPStan (niveau max) + vérification du style
make cs-fix                            # corrige le style (PHP-CS-Fixer, règles @Symfony)

# Front
docker compose exec frontend npm run lint
docker compose exec frontend npm run build   # tsc -b (vérification des types) + build Vite
```

### Tests : à mettre en place
PHPUnit (+ ApiTestCase, Foundry), Vitest + Testing Library et Playwright ne sont **pas
encore installés**. Tant qu'ils ne le sont pas, les agents vérifient le comportement en
appelant l'API (`curl` sur http://localhost:8080) et signalent les tests à écrire. Commandes
prévues une fois installés :

```bash
docker compose exec php bin/phpunit [chemin]
docker compose exec frontend npx vitest run
docker compose exec frontend npx playwright test
```

## Conventions back : DDD simplifié, un module par domaine

```
src/<Module>/
├── Entity/            # Entités Doctrine riches + #[ApiResource] + énumérations
├── Repository/        # save(), remove(), get() qui lève une exception métier
├── Service/           # Cas d'usage qui dépassent une entité (unicité, règles croisées)
├── Exception/         # Exceptions métier, traduites en codes HTTP (exception_to_status)
├── Api/
│   ├── Input/         # DTO d'entrée validés (contraintes Symfony)
│   └── State/         # Un processor par ressource (écriture)
└── Console/           # Commandes app:<module>:*
tests/<Module>/{Functional,Unit}/
```

Modules existants : `src/User/` (comptes, rôles) et `src/Catalog/` (référentiel ; `Race`
est le modèle de référence). Partagé : `src/Api/UriVariable.php`.

- `declare(strict_types=1);`, classes `final` (sauf entités Doctrine), typage strict, attributs PHP uniquement.
- **Entités riches** : propriétés privées, constructeur privé + constructeur nommé
  (`Race::create()`), méthodes métier (`rename()`) plutôt que des setters. Un `code`
  lisible et immuable (`CatalogCode`), un `name` modifiable, des identifiants UUID v7.
- **Lecture** : l'entité porte `#[ApiResource]` et est lue par le provider Doctrine
  d'API Platform (pagination, tri). Seuls les champs du groupe `<ressource>:read`
  (ex. `race:read`) sont sérialisés : jamais de donnée sensible.
- **Écriture** : DTO `Api/Input/` + **un processor par ressource** (ex. `RaceProcessor`)
  qui aiguille selon l'opération (`Post`, `Patch`, `Delete`) et appelle le service ou
  l'entité. Pas de processor générique. Variables d'URI lues avec `UriVariable::string()`
  (les identifiants arrivent en objets `Uuid`).
- **Sécurité** : une règle `security` sur chaque opération, avec des **permissions**
  `ROLE_PERMISSION_<RESSOURCE>_<ACTION>` (ex. `ROLE_PERMISSION_RACE_WRITE`) regroupées
  par rôle dans `role_hierarchy` (`config/packages/security.yaml`). Le code ne vérifie
  jamais un rôle, seulement des permissions. Un Voter seulement si la règle dépend de
  l'objet (ex. propriétaire d'une flotte).
- **Exceptions métier** déclarées dans `exception_to_status` (`config/packages/api_platform.yaml`).
- Aucun changement de schéma sans migration Doctrine, relue avant d'être appliquée.
- PHPStan niveau max et PHP-CS-Fixer doivent passer (`make qa`).

## Conventions front

```
frontend/src/
├── api/               # Client HTTP typé + types écrits à partir du contrat d'API
├── features/<module>/ # Composants, hooks et pages d'un domaine (catalog, fleet…)
├── components/        # Composants UI partagés
└── routes/
```

- TypeScript strict, pas de `any`.
- Les types de l'API reflètent exactement le contrat de la story.
- Chaque écran gère les états chargement / vide / erreur.
- Le front contient encore la page de démo Vite : à remplacer au premier écran réel.

## Définition de « terminé »
- Critères d'acceptation de la story tous validés par QA.
- `make qa` (PHPStan + CS Fixer), lint et build front au vert ; PHPUnit, Vitest et
  Playwright au vert dès qu'ils sont installés.
- Aucun « Bloquant » ouvert côté reviewer.
- Commit et push uniquement après l'accord de l'utilisateur.
