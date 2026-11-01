# Spécification fonctionnelle — Fleet API

> Document de travail : il décrit l'univers à représenter et sert de référence
> pour la modélisation. Les points encore ouverts sont listés en fin de document.

## 1. Vaisseaux

### 1.1 Vue d'ensemble

Un **vaisseau** est défini par :

| Élément        | Description                                                        | Cardinalité            |
|----------------|--------------------------------------------------------------------|------------------------|
| Catégorie      | Type de vaisseau : chasseur, destroyer, etc.                        | 1 seule par vaisseau   |
| Race           | Race qui construit le vaisseau                                      | 1 seule par vaisseau   |
| Coût           | Coût du vaisseau                                                    | 1 valeur               |
| Tourelles      | Emplacements d'armement positionnés sur le vaisseau                 | 0 à N                  |
| Moteurs        | Propulsion                                                          | à préciser             |
| Générateurs    | Production d'énergie                                                | à préciser             |
| Boucliers      | Protection                                                          | à préciser             |

### 1.2 Catégorie

Liste de référence des types de vaisseau (chasseur, destroyer, …).
Chaque vaisseau appartient à exactement une catégorie.

### 1.3 Race

Liste de référence des races de l'univers.
Chaque vaisseau est construit par exactement une race.

### 1.4 Tourelles

- Un vaisseau possède un nombre de tourelles (0 à N).
- Chaque tourelle a une **position** sur le vaisseau.
- Chaque tourelle porte **une ou plusieurs armes**.
- Les armes seront décrites plus tard (section à venir).

### 1.5 Moteurs, générateurs, boucliers

Équipements du vaisseau. Leurs caractéristiques restent à définir.

## 2. Choix de modélisation : pas d'EAV

Le modèle EAV (Entité-Attribut-Valeur) a été envisagé à cause du nombre de
caractéristiques. **Il n'est pas retenu**, pour les raisons suivantes :

- **Les caractéristiques sont connues à l'avance.** Coût, tourelles, moteurs…
  sont des concepts stables du domaine, pas des attributs ajoutés à la volée par
  les utilisateurs. C'est le cas où l'EAV n'apporte rien.
- **Types et contraintes perdus.** En EAV, toutes les valeurs sont stockées de la
  même façon : plus de typage, de `NOT NULL`, de clés étrangères ni de validation
  simple au niveau de la base.
- **Requêtes coûteuses.** Une question simple (« les destroyers de telle race
  coûtant moins de X ») devient une série de jointures, difficile à indexer et à
  filtrer, notamment dans API Platform.
- **Les relations ne rentrent pas dans l'EAV.** Tourelles → armes, vaisseau →
  moteurs sont des relations, pas des attributs scalaires.

**Approche retenue :**

1. **Modèle relationnel classique** : une entité par concept (vaisseau,
   catégorie, race, tourelle, arme, moteur, générateur, bouclier) avec des
   colonnes typées. Le nombre de paramètres se répartit naturellement entre ces
   entités au lieu de s'accumuler dans une seule.
2. **Colonne JSON (`jsonb` PostgreSQL) en complément, si besoin**, uniquement pour
   des caractéristiques secondaires ou expérimentales. Une caractéristique qui
   devient importante (filtrée, triée, calculée) est promue en vraie colonne.
3. **Elasticsearch** pour la recherche et les filtres multicritères, alimenté à
   partir du modèle relationnel.

## 3. Questions ouvertes

1. **Modèle ou exemplaire ?** Un « vaisseau » est-il un *modèle* (fiche du
   catalogue, ex. « Destroyer Argon Mk2 ») ou un *exemplaire* possédé dans une
   flotte ? Ou les deux (un exemplaire est construit à partir d'un modèle) ?
2. **Équipements interchangeables ?** Un moteur, un générateur ou un bouclier
   est-il propre à un vaisseau, ou choisi dans un catalogue d'équipements que
   plusieurs vaisseaux peuvent monter ?
3. **Combien d'équipements ?** Combien de moteurs, de générateurs et de
   boucliers un vaisseau peut-il avoir (1, N, un nombre fixé par le modèle) ?
4. **Position d'une tourelle** : simple libellé (avant, arrière, bâbord,
   tribord, dessus, dessous) ou coordonnées ?
5. **Coût** : une seule valeur (crédits), ou plusieurs ressources ?
6. **Catégorie et race** : listes fixes, ou gérables par un admin via l'API ?
7. **Armes** : une arme est-elle montée sur une seule tourelle, ou s'agit-il
   d'un type d'arme réutilisable sur plusieurs tourelles ?
