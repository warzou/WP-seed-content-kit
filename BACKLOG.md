# BACKLOG - WP-seed-content-kit

Date : 11 juin 2026
Statut : backlog produit
Nature : idees candidates hors V1 stricte

## Regle de backlog

Une entree de backlog n'est pas une autorisation d'implementation.

Toute entree doit etre revalidee avant developpement.

Le backlog ne doit pas affaiblir la frontiere V1.

## V1.3 Candidate - Quotes

### Decision

Quotes est valide comme candidat V1.3.

Quotes ne rentre pas en V1.

Quotes doit etre un module separe.

Quotes ne doit pas etre une extension du module `testimonials`.

Aucun code Quotes ne doit etre ajoute avant validation complete de V1.2 Modules.

### Pourquoi Quotes est pertinent

Le site laboratoire `https://avecguillaume.fr` contient un cas d'usage editorial reel autour des citations, inspirations, auteurs et references.

Un module Quotes peut apporter de la valeur si les citations doivent etre :

- gerees independamment des articles ;
- reutilisees sur plusieurs pages ;
- affichees par shortcode ;
- mises en avant ponctuellement ;
- associees a un auteur ou une source ;
- affichees dans un format court et stable.

Le cas Quotes reste aligné avec le coeur du projet :

```text
contenus structures + affichages reutilisables
```

### Pourquoi Quotes est exclu de V1

Quotes est exclu de V1 car V1 doit rester le socle minimal.

V1 doit valider uniquement :

- le plugin ;
- le module `testimonials` ;
- le module `cards` pour articles natifs ;
- les shortcodes `[seed_testimonials]` et `[seed_cards]` ;
- le CSS `seed-` ;
- la compatibilite builders par shortcode ;
- l'installation ZIP future.

Ajouter Quotes en V1 ajouterait un CPT supplementaire avant validation du socle.

Cela augmenterait les risques de :

- derive fonctionnelle ;
- confusion avec testimonials ;
- complexite admin prematuree ;
- retard sur la premiere release ZIP.

### Citation vs testimonial

Une citation est une reference editoriale attribuee a un auteur, une source ou une oeuvre.

Un testimonial est un retour d'experience d'une personne accompagnee.

Les deux peuvent partager un rendu en carte, mais ils ne partagent pas la meme responsabilite metier.

Differences importantes :

- testimonial : consentement obligatoire ;
- testimonial : confidentialite potentielle ;
- testimonial : preuve sociale ;
- quote : attribution et source ;
- quote : inspiration editoriale ;
- quote : pas de consentement client.

### Perimetre minimal propose

Le perimetre minimal V1.3 propose est :

- module `quotes` ;
- CPT `seed_quote` ;
- shortcode `[seed_quotes]` ;
- champs natifs :
  - texte ;
  - auteur ;
  - source ;
  - contexte ;
  - lien source optionnel ;
  - mise en avant ;
- rendu HTML simple ;
- CSS prefixe `seed-` ;
- etat vide ;
- attributs shortcode :
  - `limit` ;
  - `columns` ;
  - `featured` ;
  - `context`.

### Explicitement hors perimetre V1.3

- ACF obligatoire.
- Integration ACF.
- Import automatique depuis articles existants.
- Extraction automatique de citations.
- Gestion bibliographique avancee.
- Taxonomie dediee.
- Page de reglages admin.
- Presets visuels multiples.
- Migration automatique depuis `avecguillaume.fr`.
- Couplage avec une categorie du site laboratoire.
- Extension du module `testimonials`.

### Ce qui peut etre mutualise

- helpers de sanitization ;
- helpers d'attributs booleens ;
- gestion de colonnes ;
- rendu de grille ;
- CSS `seed-card` ;
- chargement conditionnel du CSS ;
- logique d'etat vide ;
- conventions de shortcodes ;
- tests responsive.

### Risques

- Ajouter trop de CPT trop tot.
- Confondre citations et temoignages.
- Melanger consentement client et attribution d'auteur.
- Transformer Quotes en gestion bibliographique.
- Ajouter une extraction automatique trop tot.
- Coder un besoin propre a `avecguillaume.fr`.
- Retarder la stabilisation V1.

### Criteres d'entree en V1.3

Quotes peut entrer en V1.3 uniquement si :

- V1 est terminee ;
- V1 est testee localement ;
- V1 est validee sur `avecguillaume.fr` ;
- V1.1 update infrastructure est validee ;
- V1.2 page Modules est validee ;
- les criteres de release V1 ne sont pas affaiblis ;
- le besoin de citations reutilisables est confirme ;
- les citations ne peuvent pas rester simplement dans des articles natifs ;
- le module peut rester minimal ;
- le module peut rester sans ACF ;
- le module peut rester sans import ;
- le module peut rester sans taxonomie dediee ;
- la creation du CPT `seed_quote` est revalidee explicitement.

### Recommandation actuelle

Conserver Quotes en backlog V1.3.

Ne pas modifier V1.

Ne pas coder maintenant.
