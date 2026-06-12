# CARDS GENERATOR V1.3 - WP-seed-content-kit

Date : 12 juin 2026
Statut : conception produit
Nature : documentation uniquement

## 1. Objectif

V1.3 Cards Configuration doit ajouter un generateur de shortcode pour le module `Cards`.

Le but est de permettre a un administrateur de construire facilement un shortcode `[seed_cards]` plus precis, sans connaitre tous les attributs disponibles.

Cette fonctionnalite reste volontairement au niveau 2 :

- shortcode puissant ;
- aide admin simple ;
- aucune integration native builder ;
- aucun layout editable dans Divi, Spectra, Gutenberg ou Astra ;
- aucune modification automatique des pages existantes.

V1.3 ne doit pas devenir un builder, un systeme de blocs, ni une page de reglages globaux.

## 2. UX detaillee de la page admin

La page existante `WP Seed Content Kit > Modules` reste l'unique page admin.

Elle contient :

1. une liste des modules ;
2. l'etat actif/inactif de chaque module ;
3. les shortcodes disponibles ;
4. une aide d'integration builder ;
5. une section de generation pour `Cards` quand le module est actif.

### Comportement attendu

Si `Cards` est actif :

- le module apparait comme actif ;
- le shortcode de base `[seed_cards]` reste affiche ;
- une section `Generer un shortcode Cards` apparait ;
- l'administrateur remplit les champs ;
- le shortcode genere est affiche dans un champ copiable ;
- aucun contenu WordPress n'est modifie ;
- aucune page n'est mise a jour automatiquement.

Si `Cards` est desactive :

- le module apparait comme inactif ;
- le shortcode `[seed_cards]` n'est plus considere disponible ;
- la section de generation Cards est masquee ou desactivee ;
- aucune suppression d'article, categorie ou tag n'a lieu ;
- les pages existantes ne sont pas modifiees.

### Aide d'utilisation

La page rappelle que le shortcode genere peut etre colle dans :

- Gutenberg : bloc Shortcode ;
- Spectra : bloc Shortcode ou Container ;
- Divi : module Code ou Texte ;
- Astra : page ou bloc classique ;
- editeur classique : contenu avec shortcodes.

Cette aide reste informative. Elle ne detecte pas le builder actif et ne cree aucune integration native.

## 3. Maquette textuelle

```text
WP Seed Content Kit - Modules

[message de confirmation si sauvegarde de modules]

Modules
-------------------------------------------------------------------------------
Module         Statut      Activable      Shortcode           Ou l'utiliser ?
-------------------------------------------------------------------------------
Cards          Actif       [x] Actif      [seed_cards]        Gutenberg...
Testimonials   Actif       [x] Actif      [seed_testimonials] Gutenberg...
Quotes         Prevu       Non            Non disponible      Gutenberg...
-------------------------------------------------------------------------------

[Enregistrer les modules]


Generer un shortcode Cards

Source
  Categorie                  [ Toutes les categories v]
  Tag                        [ Tous les tags v]

Affichage
  Nombre d'articles          [ 6 ]
  Colonnes                   [ 3 v]
  Ordre                      [ Date recente v]

Elements visibles
  [x] Image
  [x] Categorie
  [x] Date
  [x] Titre
  [x] Extrait
  [x] Bouton

Bouton
  Libelle                    [ Lire ]

Style
  Style                      [ Default v]

Shortcode genere
  [seed_cards category="inspirations" limit="6" columns="3" orderby="date" order="DESC" show_image="true" show_category="true" show_date="true" show_title="true" show_excerpt="true" show_button="true" button_label="Lire" style="default"]

  [Copier]
```

## 4. Champs exacts

### Module Cards

Champ :

```text
cards_enabled
```

Type :

```text
checkbox
```

Stockage :

```text
wp_seed_content_kit_modules['cards']
```

Valeur par defaut :

```text
true
```

Effet :

- `true` : le shortcode `[seed_cards]` est enregistre ;
- `false` : le shortcode `[seed_cards]` n'est pas enregistre.

### Categorie

Champ :

```text
category
```

Type admin :

```text
select
```

Source :

```text
categories WordPress natives
```

Valeur shortcode :

```text
category="slug-categorie"
```

Valeur vide :

```text
aucun attribut category
```

### Tag

Champ :

```text
tag
```

Type admin :

```text
select
```

Source :

```text
tags WordPress natifs
```

Valeur shortcode :

```text
tag="slug-tag"
```

Valeur vide :

```text
aucun attribut tag
```

Condition :

Le champ `tag` entre en V1.3 uniquement si l'implementation reste simple avec les taxonomies natives WordPress.

### Nombre d'articles

Champ :

```text
limit
```

Type admin :

```text
number
```

Valeur par defaut :

```text
6
```

Bornes recommandees :

```text
1 a 24
```

Attribut shortcode :

```text
limit="6"
```

### Colonnes

Champ :

```text
columns
```

Type admin :

```text
select
```

Valeurs :

```text
1
2
3
4
```

Valeur par defaut :

```text
3
```

Attribut shortcode :

```text
columns="3"
```

### Ordre

Champ admin :

```text
order_preset
```

Type admin :

```text
select
```

Valeurs :

```text
date_desc
date_asc
title_asc
```

Traduction shortcode :

```text
date_desc  -> orderby="date" order="DESC"
date_asc   -> orderby="date" order="ASC"
title_asc  -> orderby="title" order="ASC"
```

Valeur par defaut :

```text
date_desc
```

### Elements visibles

Champs :

```text
show_image
show_category
show_date
show_title
show_excerpt
show_button
```

Type admin :

```text
checkbox
```

Valeur par defaut :

```text
true
```

Attributs shortcode :

```text
show_image="true"
show_category="true"
show_date="true"
show_title="true"
show_excerpt="true"
show_button="true"
```

Regle :

Si une option est decochee, le shortcode peut generer explicitement `false`.

Exemple :

```text
show_date="false"
```

### Libelle du bouton

Champ :

```text
button_label
```

Type admin :

```text
text
```

Valeur par defaut :

```text
Lire
```

Attribut shortcode :

```text
button_label="Lire"
```

### Style

Champ :

```text
style
```

Type admin :

```text
select
```

Valeurs possibles :

```text
default
compact
soft
```

Valeur par defaut :

```text
default
```

Attribut shortcode :

```text
style="default"
```

Decision V1.3 :

Le champ `style` est optionnel. Il ne doit entrer en V1.3 que si le CSS reste minimal et prefixe `seed-`.

Sinon, il doit etre reporte a V2.

## 5. Shortcodes generes

### Shortcode minimal

```text
[seed_cards]
```

Usage :

- comportement actuel ;
- aucun changement pour les pages existantes ;
- compatibilite V1 conservee.

### Shortcode categorie

```text
[seed_cards category="inspirations" limit="3" columns="3"]
```

Usage :

- afficher trois articles d'une categorie precise ;
- utile pour des sections editoriales courtes.

### Shortcode categorie + ordre

```text
[seed_cards category="inspirations" limit="6" columns="3" orderby="date" order="DESC"]
```

Usage :

- afficher les contenus les plus recents d'une categorie.

### Shortcode tag

```text
[seed_cards tag="lecture" limit="6" columns="3"]
```

Usage :

- afficher des articles associes a un tag transversal.

Condition :

Uniquement si le support des tags est retenu en V1.3.

### Shortcode compact sans image

```text
[seed_cards category="ressources" limit="6" columns="2" show_image="false" show_excerpt="true" show_button="true" button_label="Lire"]
```

Usage :

- liste de ressources ;
- affichage plus dense ;
- pas de dependance a la presence d'images.

### Shortcode avec elements masques

```text
[seed_cards category="inspirations" limit="4" columns="2" show_category="false" show_date="false" show_excerpt="false" show_button="true" button_label="Decouvrir"]
```

Usage :

- cartes plus simples ;
- mise en avant visuelle ou editoriale courte.

### Shortcode avec style simple

```text
[seed_cards category="articles" limit="6" columns="3" style="soft"]
```

Usage :

- variante visuelle legere ;
- seulement si `style` est valide pour V1.3.

## 6. Cas d'usage avecguillaume.fr

### Inspirations recentes

Besoin :

Afficher quelques contenus d'inspiration sur une page existante sans creer de bloc dedie.

Shortcode possible :

```text
[seed_cards category="inspirations" limit="3" columns="3" orderby="date" order="DESC" button_label="Lire"]
```

Interet :

- integration simple dans une page Gutenberg, Divi ou Spectra ;
- pas de modification automatique des pages ;
- le contenu reste gere comme article WordPress natif.

### Selection de lectures

Besoin :

Afficher des contenus relies a un theme transversal, si les tags sont propres et utiles.

Shortcode possible :

```text
[seed_cards tag="lecture" limit="6" columns="3" orderby="date" order="DESC" show_date="false" button_label="Explorer"]
```

Interet :

- le tag peut traverser plusieurs categories ;
- le shortcode reste explicite ;
- aucune taxonomie nouvelle n'est creee.

### Cartes sobres sans date

Besoin :

Afficher des contenus intemporels sans insister sur la date de publication.

Shortcode possible :

```text
[seed_cards category="inspirations" limit="4" columns="2" show_date="false" show_category="false" button_label="Decouvrir"]
```

Interet :

- plus adapte aux contenus evergreen ;
- pas besoin de modifier le theme.

## 7. Cas d'usage therapsycorporel.fr

### Articles natifs par categorie

Besoin :

Afficher des articles WordPress natifs lies a une categorie editoriale existante.

Shortcode possible :

```text
[seed_cards category="articles" limit="6" columns="3" orderby="date" order="DESC" button_label="Lire"]
```

Interet :

- pas de CPT supplementaire ;
- pas de migration de contenu ;
- conservation du flux editorial WordPress natif.

### Ressources ou contenus d'accompagnement

Besoin :

Afficher une selection de contenus utiles sans imposer une mise en page complexe.

Shortcode possible :

```text
[seed_cards category="ressources" limit="4" columns="2" show_date="false" show_excerpt="true" button_label="Voir"]
```

Interet :

- affichage plus calme ;
- compatible avec les pages existantes ;
- ne depend pas du builder actif.

### Section courte sur page existante

Besoin :

Inserer quelques articles dans une page deja construite.

Shortcode possible :

```text
[seed_cards category="actualites" limit="3" columns="3" show_excerpt="false" button_label="Lire"]
```

Interet :

- utilisable dans un module Code ou Texte ;
- pas de layout interne editable ;
- pas d'impact sur les autres pages.

## 8. Generateur de shortcode vs vrai systeme de reglages

### Generateur de shortcode

Un generateur de shortcode :

- aide l'administrateur a construire une syntaxe ;
- produit un texte copiable ;
- ne change rien tant que le shortcode n'est pas colle dans une page ;
- ne modifie pas les pages existantes ;
- ne stocke pas de configuration d'affichage globale ;
- rend chaque usage explicite ;
- reste compatible avec tous les builders qui acceptent les shortcodes.

Exemple :

```text
[seed_cards category="inspirations" limit="3" columns="3"]
```

Avantage :

- simple ;
- previsible ;
- maintenable multi-sites ;
- faible risque de regression.

### Vrai systeme de reglages

Un vrai systeme de reglages :

- stocke des choix globaux en base ;
- peut modifier le rendu de toutes les pages utilisant `[seed_cards]` ;
- introduit une logique de priorite entre shortcode et reglages ;
- demande une documentation plus lourde ;
- augmente le risque de regression multi-sites ;
- rapproche le plugin d'un builder ou d'un systeme de templates.

Exemple a eviter en V1.3 :

```text
Reglage global : Cards utilise toujours category="inspirations" columns="3"
```

Risque :

Une modification admin pourrait changer des pages deja en ligne sans que le shortcode ait change.

### Decision V1.3

V1.3 doit livrer un generateur de shortcode.

V1.3 ne doit pas livrer un systeme de reglages globaux Cards.

La seule option persistante autorisee pour Cards est l'activation/desactivation du module.

## 9. Frontiere stricte V1.3

Autorise :

- activer/desactiver Cards ;
- generer un shortcode `[seed_cards]` ;
- copier le shortcode ;
- utiliser categories natives ;
- utiliser tags natifs si simple ;
- choisir limite, colonnes, ordre ;
- choisir les elements visibles ;
- choisir le libelle du bouton ;
- ajouter un style simple seulement si le CSS reste minimal.

Interdit :

- bloc Gutenberg ;
- module Divi ;
- integration Spectra native ;
- integration Astra native ;
- detection de builder ;
- layout interne editable ;
- presets enregistrables ;
- reglages globaux d'affichage Cards ;
- modification automatique de pages existantes ;
- creation de CPT ;
- dependance ACF ;
- dependance externe.

## 10. Tests de validation attendus

- `[seed_cards]` continue de fonctionner comme avant ;
- le module Cards est actif par defaut ;
- desactiver Cards retire le shortcode ;
- reactiver Cards restaure le shortcode ;
- le generateur produit un shortcode valide ;
- categorie vide = pas de filtre categorie ;
- categorie choisie = filtre categorie applique ;
- tag vide = pas de filtre tag ;
- tag choisi = filtre tag applique, si retenu ;
- ordre `date_desc`, `date_asc`, `title_asc` fonctionne ;
- elements visibles/masques fonctionnent ;
- le libelle du bouton est sanitize et affiche correctement ;
- aucun contenu existant n'est modifie ;
- aucune page existante n'est modifiee ;
- aucun builder n'est requis.
