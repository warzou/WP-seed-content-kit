# ADMIN UX MODULES / GENERATEURS V1.4.1 - WP-seed-content-kit

Date : 12 juin 2026
Statut : conception UX produit
Nature : documentation uniquement

## 1. Contexte

Le test reel de V1.4 montre que la page `Modules` devient confuse quand elle porte a la fois :

- l'activation des modules ;
- l'information d'integration ;
- le generateur de shortcode Cards.

La decision produit V1.4.1 est donc de separer clairement :

- la gestion des modules ;
- la generation de shortcodes.

Cette separation ne change pas le principe d'architecture :

```text
module -> attributes schema -> admin generator -> shortcode text -> frontend render
```

Elle change seulement l'organisation de l'administration WordPress.

## 2. Arborescence admin cible

Menu cible :

```text
WP Seed Content Kit
├── Modules
└── Generateurs
```

### Modules

Page compacte dediee a l'etat des modules.

Objectif :

- comprendre ce qui est actif ;
- activer ou desactiver les modules activables ;
- voir les shortcodes de base ;
- voir rapidement ou utiliser les shortcodes.

### Generateurs

Page dediee aux generateurs de shortcodes.

Objectif :

- composer un shortcode explicite ;
- copier ce shortcode ;
- le coller manuellement dans une page, un bloc Shortcode, un module Code ou un module Texte.

Cette page ne doit pas devenir :

- un builder ;
- un systeme de reglages globaux ;
- un editeur de layout ;
- un systeme de presets enregistres.

## 3. UX Modules compacte

La page `Modules` doit redevenir courte et lisible.

Contenu recommande :

```text
WP Seed Content Kit - Modules

Module          Statut      Activable      Shortcode de base      Ou l'utiliser ?
Cards           Actif       Non/Oui        [seed_cards]           Gutenberg, Spectra, Divi, Astra
Testimonials    Actif       Oui            [seed_testimonials]    Gutenberg, Spectra, Divi, Astra
Quotes          Prevu       Non            Non disponible         Gutenberg, Spectra, Divi, Astra

[Enregistrer les modules]
```

### Regles UX

- aucune grande section de generation sur cette page ;
- aucune longue liste de champs ;
- aucun formulaire Cards detaille ;
- aucun bouton copier avance sauf le shortcode de base si deja present ;
- le focus reste sur l'etat des modules.

### Role de la page

La page repond a une seule question :

```text
Quels modules existent et lesquels sont actifs ?
```

Elle ne doit pas repondre a :

```text
Comment composer un shortcode detaille ?
```

Cette seconde question appartient a `Generateurs`.

## 4. UX Generateurs dediee

La page `Generateurs` doit regrouper les generateurs disponibles et prevus.

Contenu recommande V1.4.1 :

```text
WP Seed Content Kit - Generateurs

Generateur Cards
  Generateur disponible
  Formulaire non persistant
  Shortcode genere readonly
  Bouton Copier

Generateur Temoignages
  Prevu
  Shortcode de base : [seed_testimonials]

Generateur Citations
  Prevu
  Non disponible
```

### Regles UX

- les generateurs actifs apparaissent en premier ;
- les modules prevus peuvent etre visibles, mais sans formulaire actif ;
- chaque generateur doit rappeler que le shortcode doit etre copie dans une page ;
- aucun champ ne doit enregistrer une configuration globale ;
- aucune modification admin ne doit changer une page existante.

### Role de la page

La page repond a une seule question :

```text
Quel shortcode dois-je copier pour obtenir l'affichage voulu ?
```

Elle ne doit pas repondre a :

```text
Quels modules sont actifs ?
```

Cette question appartient a `Modules`.

## 5. Impact Cards

### Etat V1.4

Generateur Cards est actuellement affiche dans la page `Modules`.

Probleme UX :

- la page devient longue ;
- l'information de module et la configuration de shortcode se melangent ;
- l'utilisateur peut croire que le formulaire Cards enregistre un reglage global ;
- le bouton `Enregistrer les modules` peut etre percu comme lie au generateur.

### Cible V1.4.1

Cards doit rester :

- actif selon la logique module existante ;
- disponible via `[seed_cards]` ;
- configurable uniquement par attributs shortcode ;
- genere depuis `Generateurs`, pas depuis `Modules`.

Le generateur Cards doit etre deplace vers `Generateurs`.

Le comportement shortcode ne change pas.

Les attributs deja ajoutes restent :

```text
category
tag
limit
columns
orderby
order
show_image
show_category
show_date
show_title
show_excerpt
show_button
button_label
```

## 6. Impact Testimonials

### Etat actuel

Testimonials est un module actif par defaut et desactivable depuis `Modules`.

### Cible V1.4.1

Dans `Modules` :

- conserver l'activation/desactivation ;
- conserver le shortcode de base `[seed_testimonials]` ;
- conserver l'aide d'integration.

Dans `Generateurs` :

- afficher Testimonials comme generateur prevu ;
- ne pas ajouter de formulaire complet en V1.4.1 ;
- eventuellement afficher le shortcode de base comme rappel.

V1.4.1 ne doit pas :

- modifier le CPT `seed_testimonial` ;
- modifier la logique de consentement ;
- ajouter le generateur Testimonials complet ;
- changer l'API `[seed_testimonials]`.

## 7. Impact Quotes

### Etat actuel

Quotes est prevu, non activable, sans CPT actif et sans shortcode actif.

### Cible V1.4.1

Dans `Modules` :

- Quotes reste affiche comme `Prevu` ;
- Quotes reste non activable ;
- aucun CPT `seed_quote` n'est cree ;
- aucun shortcode `[seed_quotes]` actif n'est ajoute.

Dans `Generateurs` :

- afficher Quotes comme `Prevu` ;
- ne pas afficher de formulaire ;
- ne pas rendre `[seed_quotes]` copiable comme shortcode actif.

V1.4.1 ne doit pas avancer le module Quotes.

## 8. Migration depuis la page actuelle

### Ce qui change

Le bloc `Generateur Cards` quitte la page `Modules`.

Une nouvelle sous-page admin `Generateurs` apparait sous le menu `WP Seed Content Kit`.

### Ce qui ne change pas

- les modules existants ;
- les options modules existantes ;
- les shortcodes existants ;
- les attributs `[seed_cards]` ;
- le rendu front ;
- les contenus WordPress ;
- les contenus `seed_testimonial` ;
- Plugin Update Checker.

### Strategie recommandee

1. Conserver la page `Modules` comme page principale du menu.
2. Ajouter une sous-page `Generateurs`.
3. Deplacer le rendu du generateur Cards dans la sous-page `Generateurs`.
4. Garder les fonctions de generation non persistantes.
5. Ne pas migrer de donnees, car aucune configuration Cards n'est stockee.

### Compatibilite

La migration ne necessite aucune migration de base de donnees.

Elle ne modifie aucune page existante.

Elle ne doit pas invalider les shortcodes deja colles dans des pages.

## 9. Risques

### Risque : ajouter trop de navigation

Deux sous-pages restent acceptables.

Au-dela, le plugin pourrait paraitre plus complexe que son perimetre V1.x.

Mitigation :

- limiter le menu V1.4.1 a `Modules` et `Generateurs`.

### Risque : confusion entre generator et settings

L'utilisateur peut encore croire que le generateur enregistre quelque chose.

Mitigation :

- rappeler clairement sur `Generateurs` que le shortcode est a copier ;
- ne pas utiliser de bouton `Enregistrer` sur les generateurs ;
- garder le champ shortcode readonly visible.

### Risque : duplication des shortcodes de base

`Modules` et `Generateurs` peuvent tous deux afficher `[seed_cards]`.

Mitigation :

- `Modules` affiche le shortcode de base comme information ;
- `Generateurs` affiche un shortcode compose comme action.

### Risque : deplacer trop de logique en V1.4.1

La correction UX pourrait deriver vers une abstraction generique prematuree.

Mitigation :

- deplacer uniquement l'UI ;
- ne pas creer encore un framework admin complet ;
- ne pas implementer Generateur Temoignages ;
- ne pas implementer Quotes.

### Risque : rupture du lien action plugin

Le lien `Modules` dans la liste des extensions doit rester coherent.

Mitigation :

- conserver le lien `Modules` vers l'ecran compact ;
- ne pas remplacer ce lien par `Generateurs`.

## 10. Recommandation finale

Recommandation : faire V1.4.1.

La separation `Modules` / `Generateurs` est justifiee par le test reel.

Elle ameliore l'experience administrateur sans changer l'architecture, sans ajouter de fonctionnalite metier et sans casser les shortcodes.

Perimetre recommande V1.4.1 :

- ajouter la sous-page `WP Seed Content Kit > Generateurs` ;
- garder `WP Seed Content Kit > Modules` compacte ;
- deplacer Generateur Cards vers `Generateurs` ;
- afficher Testimonials et Quotes comme generateurs prevus, sans formulaire actif ;
- ne pas ajouter de persistance ;
- ne pas ajouter de presets ;
- ne pas ajouter de builder ;
- ne pas changer les APIs shortcodes existantes.

Verdict produit :

```text
RECOMMENDED
```
