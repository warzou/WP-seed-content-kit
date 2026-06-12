# ATTRIBUTE SCHEMA MODEL - WP-seed-content-kit

Date : 12 juin 2026
Statut : decision d'architecture produit
Nature : documentation uniquement

## 1. Objectif

Ce document abstrait le modele `SHORTCODE-GENERATOR-MODEL.md`.

Il definit comment un module declare les attributs utilisables par son generateur de shortcode, sans imposer les memes champs metier a tous les modules.

Le modele cible reste :

```text
module -> attributes schema -> admin generator -> shortcode text -> frontend render
```

Le schema d'attributs sert a decrire :

- les champs affiches dans l'admin ;
- les valeurs acceptees ;
- les valeurs par defaut ;
- les regles de validation ;
- la conversion en attributs shortcode ;
- la documentation minimale des attributs publics.

Il ne sert pas a stocker une configuration globale d'affichage.

## 2. Principes

Un schema d'attributs doit rester :

- explicite ;
- limite au module concerne ;
- compatible avec les shortcodes ;
- non persistant par defaut ;
- sans effet sur les pages existantes tant que le shortcode colle dans la page ne change pas.

Le coeur mutualise la mecanique.

Chaque module garde sa responsabilite metier.

Exemple :

```text
Cards peut avoir category et show_button.
Testimonials peut avoir context et featured.
Audio peut avoir show_player et show_duration.
Directory peut avoir type et show_contact.
```

Ces champs ne doivent pas etre forces dans un modele commun artificiel.

## 3. Structure conceptuelle d'un champ

Chaque champ de schema peut etre decrit par les informations suivantes.

```text
key
label
group
type
default
required
allowed_values
min
max
shortcode_attribute
omit_when_default
sanitize
description
```

### Champs obligatoires

`key`

Identifiant interne stable du champ.

`label`

Libelle admin lisible.

`group`

Famille fonctionnelle du champ.

`type`

Type de champ attendu par le generateur.

`shortcode_attribute`

Nom de l'attribut dans le shortcode genere.

### Champs optionnels

`default`

Valeur utilisee si l'administrateur ne choisit rien.

`required`

Indique si le champ est obligatoire pour produire un shortcode valide.

`allowed_values`

Liste stricte des valeurs autorisees pour les champs a choix.

`min` / `max`

Bornes pour les nombres.

`omit_when_default`

Permet de garder le shortcode court en n'ecrivant pas les valeurs par defaut.

`sanitize`

Regle de nettoyage attendue.

`description`

Aide courte pour l'administrateur.

## 4. Field types

Les types suivants couvrent les besoins V1.3 et les modules futurs probables.

### `text`

Texte court libre.

Exemples :

```text
button_label
empty_message
```

Usage :

- libelles ;
- petits textes ;
- valeurs non structurees.

### `number`

Nombre entier borne.

Exemples :

```text
limit
columns
```

Usage :

- quantite ;
- nombre de colonnes ;
- bornes simples.

### `boolean`

Valeur oui/non convertie en attribut explicite.

Exemples :

```text
show_image
show_date
show_button
featured
```

Usage :

- visibilite ;
- filtres binaires ;
- options simples.

### `select`

Choix unique dans une liste stricte.

Exemples :

```text
orderby
order
style
layout
```

Usage :

- tri ;
- ordre ;
- variante limitee ;
- type de rendu si necessaire.

### `multi_select`

Choix multiple dans une liste stricte.

Usage a limiter en V1.x.

Exemples possibles :

```text
types
contexts
```

Ce type peut complexifier les shortcodes. Il doit etre ajoute seulement si un cas d'usage reel le justifie.

### `taxonomy`

Selection d'un terme WordPress.

Exemples :

```text
category
tag
quote_context
directory_type
```

Usage :

- categorie native ;
- etiquette native ;
- taxonomie de module si elle existe.

Le champ doit rester robuste si le terme est absent.

### `post_type`

Selection d'un type de contenu public supporte.

Usage possible pour des modules d'affichage transverses.

Exemple :

```text
post_type="post"
```

Ce type doit etre utilise prudemment pour ne pas transformer V1.x en moteur de requetes generique trop large.

### `url`

Adresse web.

Exemples :

```text
source_url
audio_url
```

Usage :

- lien source ;
- ressource audio ;
- lien externe.

Ce type ne doit pas introduire d'appel externe automatique en V1.x.

### `slug`

Identifiant textuel court et stable.

Exemples :

```text
context
type
source
```

Usage :

- contexte ;
- type ;
- valeur editorialement lisible.

### `hidden`

Valeur technique non affichee dans l'interface.

Usage exceptionnel.

Ce type ne doit pas cacher une configuration qui modifierait silencieusement les pages.

## 5. Field groups

Les groupes permettent de structurer les generateurs sans imposer les memes champs a tous les modules.

### `source`

Definit d'ou vient le contenu.

Exemples :

```text
post_type
source
playlist
```

### `filters`

Restreint les contenus affiches.

Exemples :

```text
category
tag
featured
context
type
```

### `sorting`

Definit l'ordre des contenus.

Exemples :

```text
orderby
order
```

### `quantity`

Definit combien d'elements afficher.

Exemples :

```text
limit
offset
```

`offset` reste hors V1.3 sauf besoin explicite.

### `layout`

Definit la forme generale du rendu.

Exemples :

```text
columns
layout
```

Ce groupe est optionnel. Un module Audio ou Directory peut ne pas utiliser `columns`.

### `visibility`

Definit les informations visibles.

Exemples :

```text
show_image
show_title
show_excerpt
show_date
show_button
show_author
show_source
show_duration
```

### `labels`

Definit les textes courts affiches dans le rendu.

Exemples :

```text
button_label
empty_message
```

### `behavior`

Definit un comportement simple du rendu.

Exemples futurs possibles :

```text
open_in_new_tab
show_player
```

Ce groupe doit rester tres limite en V1.x pour eviter de creer un builder.

## 6. Attributs communs

Les attributs communs sont des candidats reutilisables, pas une obligation.

Un module les adopte uniquement s'ils ont du sens pour son rendu.

### `limit`

Nombre d'elements a afficher.

Type recommande :

```text
number
```

Groupe :

```text
quantity
```

### `orderby`

Champ de tri autorise.

Type recommande :

```text
select
```

Groupe :

```text
sorting
```

### `order`

Sens du tri.

Type recommande :

```text
select
```

Valeurs communes :

```text
asc
desc
```

### `columns`

Nombre de colonnes.

Type recommande :

```text
number
```

Groupe :

```text
layout
```

Important :

`columns` ne doit etre propose que pour les modules a rendu grille.

### `featured`

Filtre de mise en avant.

Type recommande :

```text
boolean
```

Groupe :

```text
filters
```

Important :

`featured` n'est commun que si le module possede une vraie notion de mise en avant.

### `context`

Filtre editorial simple.

Type recommande :

```text
slug
```

Groupe :

```text
filters
```

Important :

`context` ne doit pas devenir une taxonomie cachee globale. Chaque module doit definir son sens.

## 7. Attributs optionnels par famille

### Affichage de carte ou grille

Attributs possibles :

```text
show_image
show_category
show_date
show_title
show_excerpt
show_button
button_label
```

Ces attributs conviennent surtout aux modules proches de Cards.

### Affichage de temoignage

Attributs possibles :

```text
show_name
show_context
show_date
featured
context
```

La logique de consentement reste hors du generateur.

### Affichage de citation

Attributs possibles :

```text
show_author
show_source
show_context
show_source_link
featured
context
```

La distinction avec Testimonials reste obligatoire.

### Affichage d'annuaire

Attributs possibles :

```text
type
category
region
show_name
show_role
show_location
show_contact
show_excerpt
```

Le module Directory ne doit pas etre suppose comme une grille de cartes uniquement.

### Affichage audio

Attributs possibles :

```text
source
playlist
limit
show_player
show_title
show_duration
show_transcript_link
show_description
```

Le module Audio ne doit pas imposer une logique de colonnes si le rendu principal est un lecteur ou une liste d'episodes.

## 8. Exemple Cards

Shortcode cible :

```text
[seed_cards]
```

Schema minimal possible :

```text
category
  group: filters
  type: taxonomy
  shortcode_attribute: category

tag
  group: filters
  type: taxonomy
  shortcode_attribute: tag

limit
  group: quantity
  type: number
  default: 6
  min: 1
  max: 12
  shortcode_attribute: limit

columns
  group: layout
  type: number
  default: 3
  min: 1
  max: 4
  shortcode_attribute: columns

orderby
  group: sorting
  type: select
  allowed_values: date, title
  default: date
  shortcode_attribute: orderby

order
  group: sorting
  type: select
  allowed_values: desc, asc
  default: desc
  shortcode_attribute: order

show_image
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_image

show_category
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_category

show_date
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_date

show_title
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_title

show_excerpt
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_excerpt

show_button
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_button

button_label
  group: labels
  type: text
  default: Lire
  shortcode_attribute: button_label
```

Exemples generes :

```text
[seed_cards]
[seed_cards category="inspirations" limit="3" columns="3"]
[seed_cards category="inspirations" limit="3" columns="3" show_date="false" button_label="Lire l'article"]
```

## 9. Exemple Testimonials

Shortcode cible :

```text
[seed_testimonials]
```

Schema minimal possible :

```text
limit
  group: quantity
  type: number
  default: 6
  min: 1
  max: 12
  shortcode_attribute: limit

columns
  group: layout
  type: number
  default: 2
  min: 1
  max: 3
  shortcode_attribute: columns

featured
  group: filters
  type: boolean
  default: false
  shortcode_attribute: featured

context
  group: filters
  type: slug
  shortcode_attribute: context

show_name
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_name

show_context
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_context

show_date
  group: visibility
  type: boolean
  default: false
  shortcode_attribute: show_date
```

Exemples generes :

```text
[seed_testimonials]
[seed_testimonials featured="true" limit="3"]
[seed_testimonials context="accompagnement" columns="1" show_date="true"]
```

Regle specifique :

Le generateur ne doit jamais permettre d'afficher des temoignages non consentis.

## 10. Exemple Quotes

Shortcode cible :

```text
[seed_quotes]
```

Schema minimal possible :

```text
limit
  group: quantity
  type: number
  default: 6
  min: 1
  max: 12
  shortcode_attribute: limit

columns
  group: layout
  type: number
  default: 3
  min: 1
  max: 4
  shortcode_attribute: columns

featured
  group: filters
  type: boolean
  default: false
  shortcode_attribute: featured

context
  group: filters
  type: slug
  shortcode_attribute: context

show_author
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_author

show_source
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_source

show_context
  group: visibility
  type: boolean
  default: false
  shortcode_attribute: show_context

show_source_link
  group: visibility
  type: boolean
  default: false
  shortcode_attribute: show_source_link
```

Exemples generes :

```text
[seed_quotes]
[seed_quotes featured="true" limit="1"]
[seed_quotes context="inspiration" columns="2" show_source="false"]
```

Regle specifique :

Quotes reste un module separe de Testimonials.

## 11. Exemple Directory

Directory est un exemple prospectif. Il ne fait pas partie de V1.3.

Shortcode possible :

```text
[seed_directory]
```

Schema minimal possible :

```text
type
  group: filters
  type: slug
  shortcode_attribute: type

category
  group: filters
  type: taxonomy
  shortcode_attribute: category

region
  group: filters
  type: slug
  shortcode_attribute: region

limit
  group: quantity
  type: number
  default: 20
  min: 1
  max: 100
  shortcode_attribute: limit

orderby
  group: sorting
  type: select
  allowed_values: title, date
  default: title
  shortcode_attribute: orderby

order
  group: sorting
  type: select
  allowed_values: asc, desc
  default: asc
  shortcode_attribute: order

layout
  group: layout
  type: select
  allowed_values: list, grid
  default: list
  shortcode_attribute: layout

columns
  group: layout
  type: number
  default: 3
  min: 1
  max: 4
  shortcode_attribute: columns

show_role
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_role

show_location
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_location

show_contact
  group: visibility
  type: boolean
  default: false
  shortcode_attribute: show_contact

show_excerpt
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_excerpt
```

Exemples possibles :

```text
[seed_directory]
[seed_directory type="praticien" region="bordeaux" layout="list"]
[seed_directory category="partenaires" layout="grid" columns="3" show_contact="true"]
```

Regle specifique :

Directory ne doit pas etre reduit a Cards. Il peut partager la mecanique du generateur sans partager le meme rendu.

## 12. Exemple Audio

Audio est un exemple prospectif. Il ne fait pas partie de V1.3.

Shortcode possible :

```text
[seed_audio]
```

Schema minimal possible :

```text
source
  group: source
  type: slug
  shortcode_attribute: source

playlist
  group: source
  type: slug
  shortcode_attribute: playlist

limit
  group: quantity
  type: number
  default: 5
  min: 1
  max: 20
  shortcode_attribute: limit

orderby
  group: sorting
  type: select
  allowed_values: date, title
  default: date
  shortcode_attribute: orderby

order
  group: sorting
  type: select
  allowed_values: desc, asc
  default: desc
  shortcode_attribute: order

show_player
  group: behavior
  type: boolean
  default: true
  shortcode_attribute: show_player

show_title
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_title

show_duration
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_duration

show_transcript_link
  group: visibility
  type: boolean
  default: false
  shortcode_attribute: show_transcript_link

show_description
  group: visibility
  type: boolean
  default: true
  shortcode_attribute: show_description
```

Exemples possibles :

```text
[seed_audio]
[seed_audio playlist="meditations" limit="3"]
[seed_audio source="podcast" show_transcript_link="true" show_description="false"]
```

Regle specifique :

Audio peut avoir un rendu principal en lecteur ou en liste d'episodes. Il ne doit pas heriter automatiquement de `columns`.

## 13. Regles de generation du shortcode

Le generateur doit :

- produire un shortcode texte lisible ;
- ecrire uniquement les attributs necessaires ;
- respecter les valeurs autorisees ;
- borner les nombres ;
- nettoyer les textes ;
- echapper l'affichage admin ;
- ne jamais modifier une page automatiquement ;
- ne jamais enregistrer un preset en V1.x ;
- ne jamais creer de dependance a un builder.

Recommandation :

Les valeurs par defaut peuvent etre omises du shortcode si cela ne change pas le rendu.

Exemple :

```text
[seed_cards]
```

peut rester preferable a :

```text
[seed_cards limit="6" columns="3" orderby="date" order="desc" show_image="true" show_title="true"]
```

## 14. Regles d'adoption par module

Avant d'ajouter un attribut a un schema, verifier :

- le shortcode sait-il deja exploiter cet attribut ou l'attribut est-il explicitement prevu ?
- l'attribut a-t-il un effet clair pour l'utilisateur ?
- la valeur peut-elle etre exprimee dans un shortcode lisible ?
- l'attribut ne modifie-t-il que le rendu ou la requete de ce shortcode ?
- l'attribut n'a-t-il aucun effet global silencieux ?
- le champ est-il vraiment necessaire au module concerne ?
- le champ est-il suffisamment stable pour etre documente publiquement ?

Si la reponse est incertaine, reporter.

## 15. Hors perimetre

Le schema d'attributs ne doit pas introduire :

- builder interne ;
- interface de layout ;
- presets enregistrables en V1.x ;
- reglage global d'affichage ;
- detection builder obligatoire ;
- integration native Divi ;
- integration native Spectra ;
- integration native Astra ;
- bloc Gutenberg dedie ;
- dependance ACF ;
- dependance externe ;
- modification automatique de pages existantes.

## 16. Decision pour V1.3

V1.3 doit utiliser Cards comme premier cas d'application du modele, mais le code ne doit pas supposer que tous les modules auront :

- des cartes ;
- des colonnes ;
- des images ;
- des extraits ;
- des boutons.

Le generateur V1.3 doit donc etre pense comme un rendu de schema, meme si l'implementation reste minimale.

L'objectif V1.3 n'est pas de construire toute l'abstraction future.

L'objectif V1.3 est de ne pas fermer la porte aux modules futurs.
