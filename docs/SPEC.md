# Spécification fonctionnelle — Fleet API

> Document de travail : il décrit l'univers à représenter et sert de référence
> pour la modélisation. Les points encore ouverts sont listés en fin de document.

## 0. Principes

- **Univers : X4: Foundations.** Le référentiel colle le plus possible au jeu
  (races, factions, tailles, rôles, équipements). Les écarts volontaires sont
  signalés explicitement.
- **Périmètre actuel : le référentiel** (catalogue) — ce qui existe dans
  l'univers : modèles de vaisseaux, équipements, mods, logiciels, races,
  factions… Les vaisseaux configurés par les acheteurs viendront ensuite
  (voir section 2).
- **Jeu de base uniquement** : les extensions (Split Vendetta, Cradle of
  Humanity, Tides of Avarice, Kingdoms End, Timelines…) ne sont pas gérées pour
  l'instant.
- Les données du référentiel sont issues du jeu. Elles devront être vérifiées
  dans le jeu (ou ses fichiers) : la version initiale de ce document a été
  rédigée de mémoire.

## 1. Référentiel

### 1.1 Race

Peuple qui **conçoit** un modèle de vaisseau ou un équipement. Jeu de base :
Argon, Paranid, Teladi, Xenon, Kha'ak (Split, Terran et Boron viennent des
extensions).

### 1.2 Faction

Organisation politique qui **produit et vend** : Fédération Argon, République
d'Antigone, Compagnie Teladi, Ministère des Finances, Godrealm des Paranid,
Ordre Sacré, pirates…

- Une faction appartient à une race.
- Une race peut avoir plusieurs factions.

### 1.2 bis Disponibilité par faction

Comme dans le jeu, un modèle de vaisseau, un équipement, un mod ou un logiciel
n'est **vendu que par certaines factions**. Le référentiel indique, pour chaque
élément, les factions qui le vendent.

### 1.3 Taille

`XS`, `S`, `M`, `L`, `XL`.

La taille s'applique aux vaisseaux, aux emplacements et aux équipements : un
emplacement n'accepte qu'un équipement de sa taille.

### 1.4 Rôle (catégorie)

Fonction du vaisseau, distincte de sa taille. Exemples :

| Taille | Rôles                                                              |
|--------|--------------------------------------------------------------------|
| S      | chasseur léger, chasseur lourd, intercepteur, éclaireur, cargo, mineur |
| M      | corvette, canonnière, cargo, mineur                                |
| L      | frégate, destroyer, cargo, mineur, ravitailleur                     |
| XL     | porte-vaisseaux, cuirassé, constructeur, ravitailleur               |

(Liste à compléter et vérifier.)

### 1.5 Modèle de vaisseau

Fiche de catalogue, par exemple « Behemoth Vanguard » (destroyer argon).

| Caractéristique   | Description                                                    |
|-------------------|----------------------------------------------------------------|
| Nom               | Nom du modèle                                                  |
| Race              | Race conceptrice                                               |
| Taille            | S, M, L ou XL                                                  |
| Rôle              | Chasseur, destroyer, …                                          |
| Variante          | Vanguard, Sentinel, Raider…                                     |
| Version           | Mk1, Mk2…                                                       |
| Coque             | Points de coque                                                |
| Masse / inertie   | Comportement en vol                                            |
| Équipage max      | Nombre de membres d'équipage                                   |
| Soute             | Volume et type (conteneurs, solides, liquides)                  |
| Hangar / amarrage | Capacité en vaisseaux S et M, nombre de quais                  |
| Stockage          | Missiles, drones, déployables (satellites, mines…)             |
| Prix              | Voir section 1.11 (table de prix dédiée, historisée)            |
| Fabrication       | Recette : marchandises nécessaires à la construction           |
| Emplacements      | Liste des emplacements d'équipement (voir 1.6)                 |

### 1.6 Emplacement (slot)

Point de montage d'un équipement sur un modèle de vaisseau.

- **Type** : moteur, bouclier, propulseur, arme fixe, tourelle.
- **Taille** : un emplacement n'accepte qu'un équipement de sa taille.
- **Position** sur le vaisseau : **simple libellé** pour le moment (avant,
  arrière, dessus, dessous, bâbord, tribord…).
- **Groupe** (optionnel) : sur les vaisseaux L et XL, tourelles et boucliers
  sont rassemblés en groupes (par exemple « tourelles avant »).

Un emplacement reçoit **un seul** équipement.

### 1.7 Équipements

Catalogue commun : un même équipement peut être monté sur plusieurs modèles,
dès lors que le type et la taille de l'emplacement correspondent.

Caractéristiques communes : nom, type, taille, race, niveau (Mk1/2/3).
Le prix est géré à part (section 1.11).

| Type           | Caractéristiques spécifiques (à préciser)                      |
|----------------|----------------------------------------------------------------|
| Moteur         | Spécialité (combat, voyage, polyvalent), poussée, vitesse de voyage |
| Bouclier       | Capacité, vitesse de recharge, délai de recharge               |
| Propulseur     | Manœuvrabilité (vaisseaux S et M uniquement)                   |
| Arme fixe      | Tire dans l'axe du vaisseau ; dégâts, cadence, portée, chauffe |
| Tourelle       | Arme pivotante ; mêmes caractéristiques qu'une arme            |
| Lanceur de missiles | Existe en arme fixe et en tourelle                        |

Les armes et tourelles seront détaillées plus tard.

### 1.8 Modifications d'équipement (mods)

Améliorations appliquées à un équipement ou au vaisseau, comme dans X4.

| Cible            | Exemples d'effets (à vérifier)                               |
|------------------|--------------------------------------------------------------|
| Arme / tourelle  | Dégâts, cadence, portée, chauffe, vitesse des projectiles     |
| Moteur           | Poussée, vitesse de voyage, accélération                      |
| Bouclier         | Capacité, vitesse et délai de recharge                       |
| Vaisseau (coque) | Points de coque, masse, drag, capacité de soute…              |

Pour chaque mod :

- **Qualité** : basique, avancée, exceptionnelle.
- **Effet principal** avec une plage de valeurs (min / max), et éventuellement
  des **effets secondaires** (bonus ou malus).
- **Fabrication** : recette à partir de matériaux.
- Contraintes de compatibilité (type d'équipement, taille…) à préciser.

À noter : dans le jeu, la valeur d'un mod est tirée au hasard dans sa plage à
la fabrication. Le référentiel décrit les plages ; la valeur tirée concerne un
mod réellement fabriqué (voir section 2).

Les peintures (mods esthétiques) ne sont pas gérées pour l'instant.

### 1.9 Logiciels

Logiciels installables sur un vaisseau, avec un niveau (Mk1, Mk2…). Exemples
(à vérifier) : ordinateur d'amarrage, assistance de vol, scanner longue portée,
scanner d'objets, ordinateur de visée, ordinateur de commerce.

- Un modèle de vaisseau indique quels logiciels il peut recevoir.
- Un seul niveau par logiciel installé (Mk1 **ou** Mk2).

### 1.10 Consommables

Missiles, drones, satellites, mines. À détailler plus tard.

### 1.11 Prix

Les prix évoluent : ils sont stockés dans une **table dédiée**, comme sur un
site e-commerce, et non comme une colonne de l'élément vendu.

- Un prix concerne un élément du référentiel : modèle de vaisseau, équipement,
  mod ou logiciel.
- Montant en **crédits** : prix minimum, moyen et maximum du jeu.
- **Période de validité** (début, fin éventuelle) : un nouveau prix ferme le
  précédent, ce qui conserve l'historique.
- Le **prix courant** est celui dont la période couvre la date du jour.

## 2. Plus tard : configuration par l'acheteur

Hors périmètre pour l'instant. À terme, sur le principe d'un site e-commerce,
un **acheteur construira son vaisseau** à partir du référentiel :

- il choisit un **modèle** ;
- il choisit l'**équipement** de chaque emplacement, compatible en type et en
  taille ;
- il ajoute **logiciels** et **mods** ;
- **prix** = prix de la coque + équipements + logiciels + mods, en crédits ;
- il n'a accès qu'aux éléments vendus par la faction auprès de laquelle il
  achète (à préciser).

Le référentiel doit donc être conçu dès maintenant pour permettre ce calcul et
ces contrôles de compatibilité.

## 3. Fidélité à X4

Le référentiel suit le jeu, **sans écart volontaire**. En particulier, pas de
générateur d'énergie : il n'existe pas dans X4 (il existait dans X3).

## 4. Droits d'accès : permissions

L'accès au référentiel est contrôlé par des **permissions** : chacune autorise
une action précise sur une ressource, par exemple « lire les modèles de
vaisseaux » ou « modifier les équipements ».

- **La lecture du référentiel est réservée aux utilisateurs connectés.**
  Aucun accès anonyme.
- **Rôles = regroupements de permissions**, définis dans la hiérarchie des rôles
  de `config/packages/security.yaml` (`role_hierarchy`). Un utilisateur reçoit
  des rôles ; chaque rôle donne accès à ses permissions spécifiques. Exemple :

  ```yaml
  role_hierarchy:
      ROLE_USER:
          - ROLE_PERMISSION_SHIP_MODEL_READ
          - ROLE_PERMISSION_EQUIPMENT_READ
      ROLE_CATALOG_MANAGER:
          - ROLE_USER
          - ROLE_PERMISSION_SHIP_MODEL_WRITE
          - ROLE_PERMISSION_EQUIPMENT_WRITE
          - ROLE_PERMISSION_PRICE_WRITE
      ROLE_ADMIN:
          - ROLE_CATALOG_MANAGER
  ```

- Le code ne vérifie jamais un rôle, **uniquement des permissions**
  (`is_granted('ROLE_PERMISSION_SHIP_MODEL_WRITE')`). Les rôles ne servent qu'à les
  regrouper.
- **Nommage : `ROLE_PERMISSION_<RESSOURCE>_<ACTION>`.** Le préfixe `ROLE_` est
  imposé par le `RoleVoter` de Symfony, qui ne traite que les attributs
  commençant par `ROLE_` : aucun voter spécifique n'est nécessaire. Le segment
  `PERMISSION_` distingue les permissions des vrais rôles.
- Seuls les vrais rôles (énumération `Role`) peuvent être attribués à un
  utilisateur ; une permission ne s'obtient qu'à travers un rôle.
- Conséquence : la liste des rôles et de leurs permissions est fixée dans la
  configuration ; la modifier demande un déploiement.

Existant : rôles `ROLE_USER`, `ROLE_PILOT`, `ROLE_FLEET_COMMANDER`, `ROLE_ADMIN`
stockés sur l'utilisateur. Ils sont conservés et reçoivent leurs permissions
dans la hiérarchie ; la liste des rôles sera revue.

## 5. Choix de modélisation : pas d'EAV

Le modèle EAV (Entité-Attribut-Valeur) a été envisagé à cause du nombre de
caractéristiques. **Il n'est pas retenu**, pour les raisons suivantes :

- **Les caractéristiques sont connues à l'avance.** Ce sont des concepts stables
  du domaine, pas des attributs ajoutés à la volée par les utilisateurs.
- **Types et contraintes perdus.** En EAV, toutes les valeurs sont stockées de la
  même façon : plus de typage, de `NOT NULL`, de clés étrangères ni de validation
  simple au niveau de la base.
- **Requêtes coûteuses.** Une question simple (« les destroyers argon coûtant
  moins de X ») devient une série de jointures, difficile à indexer et à filtrer.
- **Le domaine est fait de relations** (modèle → emplacements → équipements),
  que l'EAV gère mal.

**Approche retenue :**

1. **Modèle relationnel classique** : une entité par concept, avec des colonnes
   typées. Les caractéristiques se répartissent entre ces entités.
2. **Héritage pour les équipements** : un équipement commun et des types
   spécialisés (moteur, bouclier, arme…) portant leurs propres colonnes.
3. **Colonne JSON (`jsonb` PostgreSQL) en complément, si besoin**, pour des
   caractéristiques secondaires. Une caractéristique qui devient importante est
   promue en vraie colonne.
4. **Elasticsearch** pour la recherche et les filtres multicritères.

## 6. Source des données du référentiel

**Décision : pour le moment, le référentiel est alimenté uniquement via l'API.**
La création, la modification et la suppression des données du référentiel
passent par les endpoints de l'API.

Piste pour plus tard : un **import depuis les fichiers du jeu**. Une fois
extraites des archives `cat/dat`, les données sont en XML (`libraries/wares.xml`
pour les prix et recettes, fichiers *macro* pour les vaisseaux et équipements).

## 7. Décisions prises

| Sujet                    | Décision                                                    |
|--------------------------|-------------------------------------------------------------|
| Source du référentiel    | Saisie via l'API uniquement (import du jeu : plus tard)     |
| Extensions               | Jeu de base uniquement                                      |
| Position d'un emplacement | Simple libellé                                             |
| Périmètre                | Référentiel seul ; configuration par l'acheteur plus tard   |
| Fidélité à X4            | Totale, pas de générateur d'énergie                         |
| Mods et logiciels        | Inclus dès maintenant                                       |
| Droits d'accès           | Permissions ; rôles = regroupements via `role_hierarchy`    |
| Lecture du référentiel   | Réservée aux utilisateurs connectés                         |
| Prix                     | Table dédiée historisée, en crédits (min / moyen / max)     |
| Disponibilité            | Chaque élément n'est vendu que par certaines factions       |

## 8. Questions ouvertes

1. **Prix par faction** : un même élément peut-il avoir un prix différent selon
   la faction qui le vend ?
2. **Rôles** : quels rôles garder ou créer (`ROLE_PILOT` et
   `ROLE_FLEET_COMMANDER` ont-ils encore un sens) ?
3. **Prix d'un vaisseau configuré** (plus tard) : quel prix retenir parmi
   minimum / moyen / maximum ?
