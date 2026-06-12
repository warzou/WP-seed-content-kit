# WP-seed-content-kit - Vision

Date : 11 juin 2026
Statut : document de cadrage
Nature : framework editorial WordPress reutilisable

## 1. Objectif

WP-seed-content-kit est un framework editorial WordPress reutilisable pour creer :

1. des contenus structures ;
2. des affichages reutilisables.

Le projet doit rester un plugin WordPress installable par ZIP, sans theme impose et sans dependance externe obligatoire.

Il doit permettre de demarrer rapidement des sites editoriaux ou vitrines qui ont besoin de contenus mieux structures que les articles/pages natifs, tout en conservant WordPress natif lorsque c'est le bon outil.

## 2. Utilisateurs cibles

### Administrateurs de sites

- independants ;
- therapeutes ;
- coachs ;
- formateurs ;
- consultants ;
- associations ;
- petites structures locales.

Besoins :

- publier facilement ;
- eviter les structures complexes ;
- utiliser des shortcodes dans des pages existantes ;
- ne pas toucher au theme ;
- garder un controle editorial simple.

### Integrateurs WordPress

- freelances ;
- petites agences ;
- administrateurs techniques.

Besoins :

- installer un socle reutilisable ;
- activer seulement les modules utiles ;
- eviter les CPT disperses dans un theme ;
- maintenir une coherence entre plusieurs sites ;
- adapter les affichages sans reecrire toute la logique.

### Developpeurs

Besoins :

- architecture claire ;
- modules petits ;
- hooks et filtres ;
- rendu surchargeable ;
- conventions stables.

## 3. Sites pilotes

### Site laboratoire

```text
https://avecguillaume.fr
```

Role :

- developpement ;
- validation fonctionnelle ;
- tests de compatibilite ;
- tests Divi/Astra/Spectra/Gutenberg ;
- validation avant production.

### Premier site pilote production

```text
https://therapsycorporel.fr
```

Role :

- premier cas d'usage production ;
- validation d'un flux editorial reel ;
- usage de cartes de contenus ;
- validation d'un module de temoignages.

## 4. Coeur du projet

Le coeur du projet est double :

```text
contenus structures + affichages reutilisables
```

Ce n'est pas seulement un plugin de CPT.

Ce n'est pas seulement une bibliotheque de shortcodes.

La valeur vient de l'association des deux :

- des types de contenus bien choisis ;
- des champs propres ;
- des requetes fiables ;
- des cartes reutilisables ;
- une integration simple dans les pages existantes.

Principe directeur :

```text
structurer uniquement ce qui merite de l'etre, afficher simplement ce qui existe deja.
```

## 5. Architecture modulaire

Architecture cible :

```text
wp-seed-content-kit/
├── wp-seed-content-kit.php
├── includes/
│   ├── core/
│   ├── modules/
│   ├── integrations/
│   └── admin/
├── assets/
│   ├── css/
│   └── js/
└── docs/
```

Chaque module doit pouvoir etre :

- active ;
- desactive ;
- teste separement ;
- documente separement.

Modules initiaux possibles :

- Testimonials / Temoignages ;
- Journal Cards ;
- metadata legere pour articles natifs.

Modules futurs :

- ressources ;
- equipe ;
- events/stages si besoin catalogue ;
- import Word/PDF vers brouillon ;
- presets builders.

## 6. Role des CPT

Les CPT sont des modules, pas une obligation.

Un CPT est justifie quand le contenu :

- n'est pas un article editorial ;
- a une structure stable ;
- doit etre reutilise a plusieurs endroits ;
- doit avoir ses propres champs ;
- merite une archive ou une gestion admin separee.

Exemples pertinents :

- temoignages ;
- ressources ;
- membres d'equipe ;
- formations catalogue ;
- evenements si la logique devient vraiment evenementielle.

Exemples a eviter en CPT par defaut :

- actualites simples ;
- articles de blog ;
- annonces ponctuelles ;
- stages rares pouvant rester des articles natifs.

## 7. Role d'ACF

ACF est optionnel.

V1 doit fonctionner sans ACF, avec des champs natifs ou meta boxes simples.

Si ACF est present, une version future pourra :

- declarer des field groups localement ;
- lire les champs ACF ;
- beneficier de l'UX ACF ;
- conserver un fallback natif.

ACF doit rester une acceleration, pas une condition de fonctionnement.

## 8. Role des shortcodes

Les shortcodes sont la couche universelle d'integration.

Ils doivent permettre l'insertion dans :

- Divi 4 ;
- Divi 5 ;
- Gutenberg ;
- Spectra ;
- Astra ;
- editeur classique.

Attributs types :

```text
limit
columns
category
featured
context
type
show_image
show_excerpt
show_button
```

## 9. Compatibilite

Le plugin ne doit pas dependre d'un theme ou builder.

Regles :

- pas de modification de theme ;
- pas de surcharge obligatoire ;
- pas de CSS global agressif ;
- classes prefixees ;
- shortcodes compatibles ;
- rendu HTML propre et semantique.

### Divi 4 / Divi 5

- insertion via module Code ou Texte ;
- shortcode standard ;
- pas de dependance au DOM Divi ;
- module Divi dedie eventuel en V3 seulement.

### Astra

- aucun hook Astra obligatoire ;
- respect des styles globaux ;
- theme enfant non obligatoire.

### Spectra / Gutenberg

- insertion via bloc Shortcode ;
- pas de modification des blocs ;
- CSS non conflictuel.

## 10. Roadmap

### V1 - Socle minimal reutilisable

- plugin installable par ZIP ;
- architecture modulaire de base ;
- module Testimonials ;
- module Cards pour articles natifs ;
- shortcodes ;
- CSS cartes responsive ;
- documentation ;
- tests sur https://avecguillaume.fr ;
- preparation du premier pilote production.

Sans :

- ACF obligatoire ;
- import Word/PDF ;
- CPT Stage par defaut ;
- CPT Actualite par defaut ;
- blocs Gutenberg dedies ;
- dependance externe.

### V2 - UX admin et ACF optionnel

- page de reglages ;
- activation/desactivation des modules ;
- integration ACF optionnelle ;
- variations de cartes ;
- filtres supplementaires ;
- templates surchargeables ;
- documentation par cas d'usage.

### V3 - Import, builders et industrialisation

- workflow Word/PDF vers brouillon ;
- presets par metier ;
- blocs Gutenberg dedies ;
- module Divi eventuel ;
- export/import de configuration ;
- outils de migration depuis prototypes.

## 11. Definition de succes

V1 est reussie si :

- le plugin fonctionne sur https://avecguillaume.fr ;
- le meme socle peut etre deploye sur un premier site production ;
- les contenus restent simples a administrer ;
- les cartes peuvent etre inserees par shortcode ;
- le plugin ne casse aucun theme ;
- aucun CPT inutile n'est cree ;
- ACF reste optionnel.
