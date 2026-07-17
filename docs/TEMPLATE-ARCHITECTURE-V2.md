# TEMPLATE ARCHITECTURE V2 - WP Seed Content Kit

Date : 12 juin 2026
Statut : architecture déployée ; adaptateurs Collections du lot D implémentés localement, non publiés

## 1) Vision

WP Seed Content Kit doit rester un framework éditorial :

- contenus structurés (CPT et champs métier)
- sélection de contenus (shortcodes)
- injection de mise en page via le constructeur du site

Le plugin ne doit pas devenir un builder.

Le builder (Gutenberg/Spectra/Divi) conserve la mise en page.
Le plugin conserve la requête de contenu et la sémantique métier.

## 2) Architecture recommandée V2

### 2.1 Base de données des templates : `seed_template`

Créer un CPT optionnel `seed_template` (sans surcharge du cœur de plugin).

- but : stocker des templates réutilisables par site
- portée : par module/usage (ex : `testimonials`)
- contenu : `post_content` comme zone de composition
- metadata : `module`, `slug`, `engine`, `status`

### 2.2 Placeholders typés

Le template ne contient pas de PHP. Il contient des placeholders typés qui sont remplacés côté plugin.

Exemple attendu :

- `{{photo}}`
- `{{name}}`
- `{{text}}`

Chaque placeholder est typé avant rendu.

- texte brut (`text`)
- html safe (`html_safe`)
- url (`url`)
- image (`image_html`, `image_url`)
- booléen (`boolean`)

### 2.3 Rendu “builder via post_content”

Le rendu ne crée pas de bloc builder propriétaire.

- le template est lu depuis `seed_template`
- les placeholders sont injectés par le plugin
- le résultat final sort comme HTML standard WordPress

### 2.4 Fallback PHP

Par défaut, si `template` est absent ou invalide, le rendu PHP natif existant doit rester actif.

- aucun changement de comportement visible si le shortcode ne demande pas explicitement de template
- les pages existantes restent stables

### 2.5 Shortcode `template="slug"`

Les shortcodes doivent accepter un attribut explicite :

- `template="accueil"`
- `template="accueil-accueil"`

L’attribut référence un item du CPT `seed_template`.

## 3) Item template vs Collection template

### Item template

Un template de fiche (ex. Témoignage).

- une entrée = un bloc de données
- appliqué une fois par élément
- idéal pour `seed_template`

### Collection template

Un wrapper autour d’un ensemble d’items.

- gère la structure de groupe (carrousel/liste/grid global)
- peut contenir un placeholder d’insertion `{{items}}`
- sera introduit après V2.0.

## 4) Compatibilité builders

- Gutenberg : shortcode inséré dans bloc Shortcode, rendu HTML du plugin.
- Spectra : pareil via bloc Shortcode/sections compatibles.
- Divi 4 : module Texte/Code + shortcode.
- Divi 5 : module Code/Texte + rendu stable.

Principe strict : les shortcodes restent la langue d’intégration ; le builder ne définit pas la logique métier.

## 5) Risques sécurité

- XSS via placeholders non échappés.
- injection HTML depuis metadata non filtrée.
- placeholders dynamiques sans type => rendus incohérents.

Prévention :

- placeholders typés
- sanitization à la source
- escaping selon le type
- fallback natif sûr si template invalide

## 6) Roadmap V2 Templates (pilotée)

- **v0.2.0** : CPT `seed_template` minimal + UI Templates (non intrusive)
- **v0.2.1** : moteur item-template pour le module Témoignages
- **v0.2.2** : générateur de shortcode avec `template="slug"`
- **v0.2.x** : wrappers collection et fonctionnalités avancées (étape 2)

## 7) Règles anti-dérive

- pas de mini-builder
- pas de 50 options de style
- constructeur natif = mise en page
- plugin = requête + sélection + injection de template
- shortcode = explicite (`[seed_testimonials ... template="accueil"]`)

## 8) Premier module pilote

Le premier pilote V2 est **Témoignages**, car :

- données métier existantes bien stabilisées
- déjà consommé en production de test
- photo, nom, texte, contexte prêts pour placeholders

## 9) Décision de frontière

Les templates servent le framework éditorial.

Ils ne remplacent pas la stratégie de build visuelle complète.
Ils ne créent pas de dépendance nouvelle au builder.

## 10) Collections V1 et Templates

Le lot D local conserve la frontière suivante :

1. `[seed_testimonials]` ou `[seed_quotes mode="daily"]` sélectionne des IDs ;
2. Content Data normalise chaque élément ;
3. le renderer natif ou le Template WP Seed produit le HTML ;
4. Gutenberg, Spectra ou Divi héberge le shortcode.

Exemples :

```text
[seed_testimonials ids="12,18,27" template="accueil"]
[seed_testimonials featured="only" limit="3" template="accueil"]
[seed_quotes mode="daily" template="citation-du-jour"]
```

Un Template continue de représenter un élément, pas la collection entière. Un Layout Divi Library peut rester sa source de rendu. Aucun bloc, module builder, placeholder `{{items}}` ou provider de collection n'est ajouté.
