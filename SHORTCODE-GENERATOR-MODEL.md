# SHORTCODE GENERATOR MODEL - WP-seed-content-kit

Date : 12 juin 2026
Statut : decision d'architecture produit
Nature : documentation uniquement

## 1. Principe generique

Le generateur de shortcode devient le modele standard des futurs modules de WP-seed-content-kit.

Le modele standardise la mecanique suivante :

```text
module -> attributes schema -> admin generator -> shortcode text -> frontend render
```

Le generateur n'est pas un builder.

Il ne modifie pas les pages existantes.

Il ne stocke pas de reglage global d'affichage par defaut.

Il aide l'administrateur a produire un shortcode explicite, copiable, puis colle manuellement dans une page, un bloc Shortcode, un module Code ou un module Texte.

## 2. Objectif du modele

Le modele doit permettre :

- d'aider les administrateurs non techniques ;
- de rendre les shortcodes plus faciles a composer ;
- de garder les pages explicites ;
- de conserver la compatibilite Divi, Spectra, Astra, Gutenberg et editeur classique ;
- d'eviter les integrations natives prematurees ;
- d'eviter les reglages globaux qui changent des pages en silence ;
- de reutiliser une UX commune entre modules.

## 3. Ce qui est mutualisable

Peut etre mutualise :

- declaration du module ;
- activation/desactivation du module ;
- affichage du statut module ;
- affichage du shortcode de base ;
- aide `Ou l'utiliser ?` ;
- rendu d'un formulaire de generation ;
- rendu d'un champ shortcode copiable ;
- bouton copier si ajoute ;
- validation de champs generiques ;
- sanitization d'attributs ;
- escaping admin ;
- whitelists de valeurs ;
- bornage des nombres ;
- conversion de booleens en `true` / `false` ;
- construction d'une chaine shortcode ;
- tests de non-persistence ;
- documentation des attributs publics.

Attributs souvent mutualisables :

```text
limit
columns
orderby
order
featured
context
style
```

Champs de visibilite souvent mutualisables :

```text
show_image
show_title
show_excerpt
show_button
button_label
```

Ces champs restent optionnels selon le module.

## 4. Ce qui reste specifique par module

Ne doit pas etre standardise globalement :

- les champs metier ;
- les requetes ;
- les CPT ;
- les meta fields ;
- les conditions de visibilite metier ;
- les textes d'etat vide ;
- la logique de consentement ;
- la notion d'auteur/source ;
- la logique de categorie ou tag ;
- les regles de confidentialite ;
- les templates HTML propres au module.

Chaque module doit definir son propre schema d'attributs.

Exemple :

```text
Cards != Testimonials != Quotes
```

Le modele impose une structure de generation, pas une structure editoriale unique.

## 5. Difference avec un systeme de reglages

### Generateur de shortcode

Un generateur de shortcode :

- ne stocke pas de configuration d'affichage globale ;
- produit un shortcode texte ;
- rend chaque usage visible dans la page ;
- ne change rien tant que le shortcode n'est pas colle ;
- ne modifie pas les pages existantes ;
- limite les regressions multi-sites ;
- reste compatible avec tous les builders acceptant les shortcodes.

Exemple :

```text
[seed_cards category="inspirations" limit="3" columns="3"]
```

### Systeme de reglages

Un systeme de reglages :

- stocke une configuration globale ;
- peut changer le rendu de plusieurs pages en une seule modification admin ;
- introduit une logique de priorite entre reglages et attributs shortcode ;
- demande une documentation plus lourde ;
- augmente le risque de regression silencieuse.

Exemple a eviter en V1.x :

```text
Tous les shortcodes [seed_cards] utilisent automatiquement category="inspirations".
```

Decision :

V1.x privilegie les generateurs non persistants.

Les presets enregistrables et reglages globaux eventuels sont reportes a V2.

## 6. Impact pour Cards

Cards est le premier module a appliquer le modele.

V1.3 Cards Generator doit fournir :

- activation/desactivation du module `Cards` ;
- schema d'attributs `[seed_cards]` ;
- generateur admin non persistant ;
- shortcode genere et copiable ;
- filtres par categorie ;
- filtre par tag si simple ;
- limite d'articles ;
- colonnes ;
- ordre :
  - date desc ;
  - date asc ;
  - title asc ;
- elements visibles :
  - image ;
  - categorie ;
  - date ;
  - titre ;
  - extrait ;
  - bouton ;
- libelle du bouton ;
- style simple optionnel si le CSS reste minimal.

Cards ne doit pas ajouter :

- reglage global d'affichage ;
- preset enregistrable ;
- layout interne editable ;
- dependance builder ;
- modification automatique de pages existantes.

## 7. Impact pour Testimonials

Testimonials reutilise la mecanique du generateur, pas les champs Cards.

V1.4 Testimonials Generator peut fournir :

- schema d'attributs `[seed_testimonials]` ;
- generateur admin non persistant ;
- shortcode genere et copiable ;
- limite de temoignages ;
- colonnes ;
- filtre `featured` ;
- filtre `context` ;
- elements visibles pertinents :
  - nom ;
  - contexte ;
  - date.

Testimonials reste specifique sur :

- CPT `seed_testimonial` ;
- consentement de publication ;
- confidentialite ;
- champs de temoignage ;
- exclusion des temoignages non consentis.

Le generateur ne doit jamais affaiblir la logique de consentement.

## 8. Impact pour Quotes

Quotes reutilise le modele apres validation de Cards et Testimonials.

V1.5 Quotes/Citations peut fournir :

- module separe `quotes` ;
- CPT `seed_quote` ;
- shortcode `[seed_quotes]` ;
- schema d'attributs `[seed_quotes]` ;
- generateur admin non persistant ;
- shortcode genere et copiable ;
- limite ;
- colonnes ;
- filtre `featured` ;
- filtre `context` ;
- elements visibles pertinents :
  - auteur ;
  - source ;
  - contexte ;
  - lien source.

Quotes reste specifique sur :

- texte de citation ;
- auteur ;
- source ;
- attribution ;
- lien source optionnel ;
- distinction stricte avec Testimonials.

Quotes ne doit pas devenir une extension de Testimonials.

## 9. Architecture long terme

Structure conceptuelle cible :

```text
plugin/
├── includes/
│   ├── core/
│   │   ├── modules.php
│   │   ├── shortcode-generator.php
│   │   └── shortcode-attributes.php
│   ├── admin/
│   │   ├── modules-page.php
│   │   └── generator-renderer.php
│   └── modules/
│       ├── cards/
│       │   ├── module.php
│       │   ├── shortcode.php
│       │   ├── render.php
│       │   └── generator.php
│       ├── testimonials/
│       │   ├── module.php
│       │   ├── shortcode.php
│       │   ├── render.php
│       │   └── generator.php
│       └── quotes/
│           ├── module.php
│           ├── shortcode.php
│           ├── render.php
│           └── generator.php
```

Cette structure est une cible long terme, pas une obligation immediate.

V1.3 doit rester minimal et ne doit pas creer une abstraction lourde si le besoin reel ne l'exige pas encore.

## 10. Hors perimetre V1.x

Hors perimetre :

- builder interne ;
- blocs Gutenberg dedies ;
- module Divi dedie ;
- integration Spectra native ;
- integration Astra native ;
- detection builder ;
- layout editable ;
- presets enregistrables ;
- systeme de styles avance ;
- reglage global d'affichage ;
- logique d'heritage entre reglages globaux et attributs shortcode ;
- ACF obligatoire ;
- dependance externe.

Ces sujets sont au mieux V2 ou V3 selon leur nature.

## 11. Regle de decision

Avant d'ajouter un champ a un generateur, verifier :

- l'attribut existe-t-il ou est-il clairement necessaire ?
- la valeur peut-elle etre exprimee explicitement dans le shortcode ?
- la valeur est-elle sans effet sur les pages qui ne l'utilisent pas ?
- le champ est-il specifique au module ou vraiment mutualisable ?
- l'absence du champ casse-t-elle un cas d'usage reel ?

Si la reponse est incertaine, reporter.

Le modele doit rester un outil de clarte, pas une machine a options.
