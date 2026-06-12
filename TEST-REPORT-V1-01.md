# TEST REPORT V1-01 - WP-seed-content-kit

Date : 12 juin 2026
Site : https://avecguillaume.fr
Mode : validation fonctionnelle initiale sur brouillons
Statut : brouillons uniquement, rien publie publiquement

## Contexte

Le plugin WP Seed Content Kit est installe et active sur le site laboratoire.

Version detectee via REST :

```text
WP Seed Content Kit 0.1.0
```

Plugin REST slug :

```text
wp-seed-content-kit/wp-seed-content-kit
```

## Actions effectuees

### 1. Verification CPT

CPT verifie via REST :

```text
seed_testimonial
```

Resultat :

```text
OK
```

Details :

```text
slug: seed_testimonial
rest_base: seed_testimonial
name: Testimonials
```

### 2. Creation temoignage brouillon

Un temoignage de test a ete cree en brouillon.

```text
ID: 2009
Status: draft
Title: Seed Testimonial Test Draft 20260612-075506
Link: https://www.avecguillaume.fr/?post_type=seed_testimonial&p=2009
```

Note : les champs meta du module testimonials ne sont pas exposes via REST dans V1. Le brouillon valide donc l'existence du CPT et la creation d'un contenu `seed_testimonial`, mais pas la saisie des meta boxes admin.

### 3. Creation page brouillon `[seed_testimonials]`

Une page brouillon a ete creee.

```text
ID: 2010
Status: draft
Title: Seed Test Testimonials Draft 20260612-075506
Content: [seed_testimonials]
Link: https://www.avecguillaume.fr/?page_id=2010
```

### 4. Creation page brouillon `[seed_cards]`

Une page brouillon a ete creee.

```text
ID: 2011
Status: draft
Title: Seed Test Cards Draft 20260612-075506
Content: [seed_cards]
Link: https://www.avecguillaume.fr/?page_id=2011
```

## Verification rendu HTML

### `[seed_testimonials]`

Rendu REST :

```html
<p class="seed-testimonials__empty">No testimonials to display yet.</p>
```

Resultat :

```text
OK
```

Interpretation :

- Le shortcode est execute.
- L'etat vide est rendu.
- Aucun temoignage brouillon n'est affiche.
- Aucun temoignage sans consentement n'est affiche.
- Aucun message d'erreur PHP n'est visible dans le rendu.

### `[seed_cards]`

Rendu REST :

```text
OK
```

Signaux detectes :

```text
seed-cards: present
seed-card: present
seed-cards__grid: present
seed-card--post: present
```

Extrait du rendu :

```html
<section class="seed-cards" data-columns="3">
  <div class="seed-cards__grid seed-cards__grid--cols-3">
    <article class="seed-card seed-card--post seed-card--category-mes-inspirations">
```

Resultat :

```text
OK
```

Interpretation :

- Le shortcode est execute.
- Des articles natifs sont recuperes.
- Les cartes sont rendues.
- Les classes CSS publiques sont prefixees `seed-`.
- Aucun message d'erreur PHP n'est visible dans le rendu.

## Verification CSS

Fichier CSS teste :

```text
https://www.avecguillaume.fr/wp-content/plugins/wp-seed-content-kit/assets/css/seed-content-kit.css
```

Resultat HTTP :

```text
200 OK
Content-Type: text/css
Content-Length: 3025
```

Resultat :

```text
OK - fichier CSS accessible
```

Limite :

Le chargement effectif du CSS dans une page brouillon complete n'a pas ete verifie par navigateur authentifie. La verification REST confirme le rendu shortcode ; la verification HTTP confirme que l'asset CSS installe est accessible.

## Verification responsive

Verification statique :

- Le CSS contient des grilles responsive.
- Le CSS contient des media queries pour tablettes et mobiles.
- Le rendu utilise les classes `seed-cards__grid--cols-*` et `seed-testimonials__grid--cols-*`.

Resultat :

```text
PARTIEL
```

Limite :

La verification visuelle mobile/tablette/desktop sur page brouillon necessite une preview navigateur authentifiee. Elle n'a pas ete executee dans ce passage REST.

## Absence d'erreur PHP visible

Les rendus REST des deux pages brouillon ont ete scannes pour :

```text
Fatal error
Parse error
Warning:
Notice:
Deprecated:
```

Resultat :

```text
OK - aucune erreur PHP visible dans le rendu REST
```

## Elements non publies

Tous les contenus crees sont en brouillon :

```text
seed_testimonial 2009: draft
page 2010: draft
page 2011: draft
```

Aucune publication publique effectuee.

## Resultats synthetiques

| Verification | Resultat |
|---|---|
| Plugin actif | OK |
| CPT `seed_testimonial` | OK |
| Temoignage brouillon cree | OK |
| Page brouillon `[seed_testimonials]` creee | OK |
| Page brouillon `[seed_cards]` creee | OK |
| Rendu HTML `[seed_testimonials]` | OK |
| Rendu HTML `[seed_cards]` | OK |
| CSS accessible | OK |
| Responsive | PARTIEL |
| Absence erreur PHP visible | OK |
| Publication publique | Aucune |

## Points a verifier ensuite

- Ouvrir les brouillons en preview navigateur authentifiee.
- Confirmer que le CSS est bien charge dans le HTML complet de la page.
- Tester les breakpoints mobile, tablette et desktop visuellement.
- Tester la saisie des meta boxes testimonials dans l'admin.
- Tester un temoignage publie avec consentement uniquement apres validation explicite.

## Verdict

Validation fonctionnelle initiale REST :

```text
PASS WITH VISUAL CHECKS PENDING
```

Le plugin repond correctement aux premiers tests REST en brouillon. Les controles visuels responsive et chargement CSS dans une preview authentifiee restent a faire avant validation complete V1 sur `avecguillaume.fr`.
