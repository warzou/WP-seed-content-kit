# AI CONTEXT - WP-seed-content-kit

Ce document donne le contexte stable a reutiliser par une IA travaillant sur le projet.

## 1. Projet

Nom :

```text
WP-seed-content-kit
```

Nature :

```text
Framework editorial WordPress reutilisable.
```

## 2. Sites

Site laboratoire :

```text
https://avecguillaume.fr
```

Premier site pilote production :

```text
https://therapsycorporel.fr
```

Le site laboratoire doit servir avant toute intervention sur le site pilote production.

## 3. Coeur conceptuel

Le coeur du projet est :

1. contenus structures ;
2. affichages reutilisables.

Les CPT sont des modules.

Les affichages sont une couche independante.

Ne pas reduire le projet a un simple plugin de CPT.

Ne pas reduire le projet a une simple bibliotheque de shortcodes.

## 4. Contraintes non negociables

- ACF optionnel.
- Divi 4 compatible.
- Divi 5 compatible.
- Astra compatible.
- Spectra compatible.
- Gutenberg compatible.
- Shortcodes universels.
- CSS prefixe.
- Aucun theme impose.
- Plugin installable par ZIP.
- Pas de dependance externe obligatoire.

## 5. Strategie technique

### Plugin d'abord

Le projet doit etre porte par un plugin WordPress.

Ne pas mettre la logique metier dans :

- un theme ;
- un theme enfant ;
- Astra ;
- Divi ;
- Spectra.

Un theme enfant peut etre tolere pour des ajustements visuels projet, mais ne doit jamais etre obligatoire pour la V1.

### Modules

Les fonctionnalites doivent etre pensees en modules :

- modules de contenus ;
- modules d'affichage ;
- modules d'integration ;
- modules futurs d'import.

### CPT

Creer un CPT seulement si le besoin le justifie.

Un CPT est pertinent pour :

- temoignages ;
- ressources ;
- equipe ;
- evenements/stages si volumetrie ou logique evenementielle forte.

Un CPT est souvent inutile pour :

- actualites simples ;
- articles de journal ;
- annonces ponctuelles ;
- stages rares pouvant rester des articles natifs.

### ACF

ACF est optionnel.

V1 doit pouvoir fonctionner sans ACF.

V2+ peut ajouter :

- integration ACF si ACF est actif ;
- field groups optionnels ;
- compatibilite sans ACF maintenue.

### Shortcodes

Les shortcodes sont la couche universelle d'integration.

Ils doivent rester :

- lisibles ;
- documentes ;
- stables ;
- compatibles builders.

Attributs types :

```text
limit
columns
category
featured
context
type
```

## 10. Architecture V2 (templates)

Le V2 doit rester un pont d'injection, pas un builder.

Principes :

- Le plugin définit la **sélection** du contenu (CPT + requêtes + filtres).
- Les constructeurs restent propriétaires de la **mise en page**.
- Ajouter une mécanique de templates :
  - CPT dédié `seed_template` pour stocker le contenu de template ;
  - placeholders typés (texte/html/url/image/booléen) ;
  - rendu via `post_content` du template ;
  - fallback PHP existant quand aucun template n'est fourni.
- Shortcode étendu avec `template="slug"` (ex: `[seed_testimonials template="accueil"]`).
- Distinction :
  - template d’item (une entité métier) ;
  - wrapper de collection (global pour liste/grille, plus tard).

## 6. Compatibilite builders/themes

### Divi 4

- module Code ;
- module Texte ;
- shortcode standard ;
- ne pas dependre du DOM Divi.

### Divi 5

- compatibilite shortcode ;
- HTML stable ;
- CSS scoped.

### Astra

- aucun hook Astra obligatoire ;
- respect des styles globaux ;
- CSS uniquement sur composants du plugin.

### Spectra

- bloc Shortcode ;
- sections Spectra ;
- pas de modification des blocs Spectra.

### Gutenberg

- bloc Shortcode ;
- blocs dedies possibles plus tard.

## 7. CSS

Le CSS doit etre prefixe.

Prefixe framework recommande :

```text
seed-
```

Regles :

- pas de style global sur `body`, `h1`, `a`, `img`, etc. ;
- pas de reset CSS ;
- pas de dependance a Bootstrap/Tailwind ;
- responsive par CSS Grid/Flex ;
- composants surchargeables.

## 8. Regles pour agents IA

Avant de coder :

- lire `VISION.md` ;
- lire `PROJECT-SNAPSHOT.md` ;
- lire `ARCHITECTURE-GUARDRAILS.md` ;
- verifier si la demande est documentaire ou code ;
- ne jamais deployer sans validation.

Ne jamais :

- modifier un site WordPress sans validation explicite ;
- afficher des secrets ;
- imposer ACF ;
- ajouter une dependance externe sans justification ;
- creer un CPT par reflexe ;
- melanger logique de contenu et theme ;
- casser la compatibilite shortcode.

Toujours preferer :

- WordPress natif quand suffisant ;
- modules petits ;
- CSS scoped ;
- rendu surchargeable ;
- documentation courte mais claire ;
- tests avant deploiement.
