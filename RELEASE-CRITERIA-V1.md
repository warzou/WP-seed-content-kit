# RELEASE CRITERIA V1 - WP-seed-content-kit

Date : 11 juin 2026
Statut : criteres de terminaison V1
Nature : documentation produit et validation release

## Definition

WP-seed-content-kit V1 est considere comme termine uniquement quand tous les criteres de ce document sont remplis.

V1 doit rester le socle minimal reutilisable :

- plugin WordPress autonome ;
- module `testimonials` ;
- module `cards` pour articles WordPress natifs ;
- shortcodes universels ;
- CSS prefixe `seed-` ;
- aucune dependance obligatoire ;
- aucun theme impose ;
- aucun ACF obligatoire ;
- aucun module hors V1.

Tout element absent de ce document est exclu de V1.

## 1. Fonctionnalites obligatoires

### Plugin

- Le plugin dispose d'un fichier principal `wp-seed-content-kit.php`.
- Le plugin peut etre active dans WordPress.
- Le plugin peut etre desactive dans WordPress.
- Le plugin ne modifie pas un theme.
- Le plugin ne requiert pas de theme enfant.
- Le plugin ne requiert pas Divi, Astra, Spectra ou Gutenberg comme dependance.
- Le plugin ne requiert pas ACF.
- Le plugin ne requiert pas Composer.
- Le plugin ne requiert pas npm.

### Module testimonials

- Un CPT `seed_testimonial` existe.
- Le CPT est gere par le plugin, pas par un theme.
- Les champs natifs suivants existent :
  - nom ou initiales ;
  - texte ;
  - contexte ;
  - date ;
  - consentement de publication ;
  - mise en avant.
- Les champs sont sauvegardes avec securite WordPress minimale.
- Les temoignages sans consentement ne sont jamais affiches par le shortcode.
- Le shortcode `[seed_testimonials]` existe.
- Le shortcode `[seed_testimonials]` accepte uniquement les attributs V1 documentes :
  - `limit` ;
  - `columns` ;
  - `featured` ;
  - `context`.

### Module cards

- Le module cards affiche des articles WordPress natifs.
- Le module cards ne cree pas de CPT supplementaire.
- Le module cards ne suppose aucune categorie metier.
- Le shortcode `[seed_cards]` existe.
- Le shortcode `[seed_cards]` accepte uniquement les attributs V1 documentes :
  - `limit` ;
  - `columns` ;
  - `category` ;
  - `show_image` ;
  - `show_excerpt` ;
  - `show_button` ;
  - `button_label`.

### Rendu et CSS

- Les shortcodes produisent un HTML stable.
- Les shortcodes gerent un etat vide.
- Les classes publiques sont prefixees `seed-`.
- Le CSS est confine aux composants du plugin.
- Le CSS ne contient pas de reset global.
- Le CSS ne cible pas `body`, `main`, `section`, `article`, `h1`, `h2`, `a`, `img` sans classe.
- Le rendu responsive fonctionne sans JavaScript.

### Explicitement hors V1

- Module Quotes.
- Module Stage.
- Module Actualite.
- Module Ressources.
- Module Equipe.
- Module Events.
- Integration ACF.
- Page de reglages admin.
- Activation/desactivation de modules via UI.
- Import Word/PDF.
- Bloc Gutenberg dedie.
- Module Divi dedie.
- Presets metiers.
- Migration automatique depuis prototype.

## 2. Tests obligatoires

### Tests statiques

- Tous les fichiers PHP passent une validation syntaxique.
- Aucune trace de secret n'est presente.
- Aucun appel HTTP externe n'est present.
- Aucune dependance Composer n'est requise.
- Aucune dependance npm n'est requise.
- Aucun nom Therapsy n'est present dans le code du plugin.
- Aucune categorie pilote n'est codee en dur.
- Les prefixes PHP, CSS, meta et shortcodes sont verifies.

### Tests securite WordPress

- Les meta boxes utilisent un nonce.
- Les sauvegardes verifient le nonce.
- Les sauvegardes verifient les capabilities.
- Les sauvegardes ignorent les autosaves.
- Les sauvegardes ignorent les revisions.
- Les donnees entrantes sont sanitisees.
- Les donnees sortantes sont echappees.
- Aucun message d'erreur PHP public n'est affiche.
- Aucune modification en masse de contenus n'est effectuee.

### Tests plugin

- Activation sans erreur fatale.
- Desactivation sans erreur fatale.
- Reactivation sans erreur fatale.
- Le CPT `seed_testimonial` apparait dans l'administration.
- Les permaliens restent fonctionnels apres activation.
- Les pages existantes restent accessibles.
- Les articles existants restent accessibles.

### Tests testimonials

- Creer un temoignage brouillon.
- Renseigner tous les champs.
- Publier sans consentement.
- Verifier que le temoignage sans consentement ne s'affiche pas.
- Cocher le consentement.
- Verifier que le temoignage s'affiche.
- Cocher la mise en avant.
- Tester `featured="true"`.
- Tester `featured="false"`.
- Tester `featured="all"`.
- Tester `context`.
- Tester `limit`.
- Tester `columns`.
- Tester l'etat vide.

### Tests cards

- Tester `[seed_cards]` sans attribut.
- Tester `limit`.
- Tester `columns`.
- Tester `category`.
- Tester une categorie absente.
- Tester `show_image="false"`.
- Tester `show_excerpt="false"`.
- Tester `show_button="false"`.
- Tester `button_label`.
- Tester un article avec image mise en avant.
- Tester un article sans image mise en avant.
- Tester l'etat vide.

### Tests responsive

- Tester mobile environ 375 px.
- Tester tablette environ 768 px.
- Tester desktop environ 1280 px.
- Verifier que les cartes ne debordent pas.
- Verifier que les textes longs restent lisibles.
- Verifier que les images restent stables.
- Verifier que les grilles se replient correctement.

## 3. Validation sur avecguillaume.fr

`https://avecguillaume.fr` est le site laboratoire.

La validation sur ce site est obligatoire avant toute installation sur `therapsycorporel.fr`.

### Preconditions

- Validation explicite du responsable projet.
- Version locale testee.
- Sauvegarde minimale disponible.
- Plan de desactivation rapide connu.
- Page de test ou brouillon identifiee.
- Aucun test direct sur contenu critique.
- Aucun changement de theme.
- Aucun changement de builder.

### Validation admin

- Le plugin s'active.
- Le plugin se desactive.
- Le CPT `seed_testimonial` est visible.
- Les champs testimonials sont utilisables.
- Les champs testimonials persistent apres sauvegarde.
- L'administration WordPress reste stable.

### Validation front

- `[seed_testimonials]` fonctionne sur une page de test.
- `[seed_cards]` fonctionne sur une page de test.
- Les etats vides sont propres.
- Le rendu desktop est correct.
- Le rendu tablette est correct.
- Le rendu mobile est correct.
- Aucun style global du site n'est casse.

### Validation builders/themes

- Gutenberg accepte les shortcodes.
- Spectra accepte les shortcodes si disponible.
- Divi 4 accepte les shortcodes si disponible.
- Divi 5 accepte les shortcodes si disponible.
- Astra ne subit pas de degradation globale.
- Aucun shortcode ne depend du DOM d'un builder.

### Criteres de sortie laboratoire

La validation `avecguillaume.fr` est reussie uniquement si :

- aucun bug bloquant n'est observe ;
- aucun conflit theme ou builder n'est observe ;
- aucun contenu existant n'est degrade ;
- aucun message d'erreur public n'est visible ;
- les deux shortcodes V1 sont utilisables ;
- le plugin peut etre desactive sans degradation persistante.

## 4. Conditions avant installation sur therapsycorporel.fr

`https://therapsycorporel.fr` est le premier site pilote production.

Aucune installation sur ce site n'est autorisee tant que les conditions suivantes ne sont pas remplies.

### Conditions prealables obligatoires

- V1 est validee localement.
- V1 est validee sur `avecguillaume.fr`.
- Les risques residuels sont documentes.
- Une validation explicite separee est donnee pour le site pilote.
- Une sauvegarde base de donnees est disponible.
- Une sauvegarde fichiers est disponible.
- Une sauvegarde uploads est disponible.
- Une sauvegarde themes/plugins est disponible.
- La restaurabilite minimale est verifiee.
- Un plan de rollback est connu.
- Une fenetre d'intervention est definie.

### Conditions d'installation

- Installation via ZIP ou dossier plugin clairement identifie.
- Activation manuelle.
- Premier test sur page brouillon ou zone non publique.
- Aucun test direct sur page publique critique.
- Aucun changement de theme.
- Aucun changement de builder.
- Aucun contenu existant modifie automatiquement.

### Conditions de validation pilote

- Le plugin s'active sans erreur fatale.
- L'administration reste accessible.
- Les pages publiques existantes restent accessibles.
- Les articles existants gardent leurs URLs.
- Les permaliens restent fonctionnels.
- Un temoignage de test peut etre cree.
- Un temoignage sans consentement ne s'affiche pas.
- Un temoignage avec consentement s'affiche.
- `[seed_testimonials]` fonctionne sur page brouillon.
- `[seed_cards]` fonctionne sur page brouillon.
- Les shortcodes peuvent etre retires sans effet secondaire.
- Le plugin peut etre desactive sans perte de contenu natif.

## 5. Criteres de qualite du ZIP

La release ZIP V1 doit etre autonome et installable sans build.

### Structure attendue

Le ZIP doit contenir un dossier racine unique :

```text
wp-seed-content-kit/
```

Le dossier doit contenir au minimum :

```text
wp-seed-content-kit/
- wp-seed-content-kit.php
- includes/
- assets/
- README.md
- docs/
```

### Qualite technique

- Le ZIP ne contient pas `.git`.
- Le ZIP ne contient pas de fichiers temporaires.
- Le ZIP ne contient pas de secrets.
- Le ZIP ne contient pas de sauvegardes.
- Le ZIP ne contient pas de fichiers de developpement inutiles.
- Le ZIP ne requiert pas Composer.
- Le ZIP ne requiert pas npm.
- Le ZIP ne requiert pas compilation.
- Le ZIP ne requiert pas ACF.
- Le ZIP ne requiert pas de theme specifique.

### Verification installation

- Le ZIP s'installe via l'admin WordPress.
- Le plugin apparait dans la liste des extensions.
- Le plugin peut etre active.
- Le plugin peut etre desactive.
- Le plugin peut etre supprime sans supprimer les contenus WordPress natifs.

## 6. Criteres de non-regression

### WordPress natif

- Les pages existantes restent accessibles.
- Les articles existants restent accessibles.
- Les categories existantes restent accessibles.
- Les medias existants restent accessibles.
- Les permaliens existants restent fonctionnels.
- L'editeur Gutenberg reste utilisable.
- L'administration WordPress reste accessible.

### Themes et builders

- Aucun style global du theme n'est remplace.
- Aucun bloc Gutenberg existant n'est modifie.
- Aucun bloc Spectra existant n'est modifie.
- Aucun module Divi existant n'est modifie.
- Aucun hook Astra obligatoire n'est introduit.
- Aucun theme enfant n'est requis.

### Donnees

- Aucun article existant n'est modifie automatiquement.
- Aucune page existante n'est modifiee automatiquement.
- Aucune categorie existante n'est modifiee automatiquement.
- Aucun media existant n'est modifie automatiquement.
- Aucun contenu n'est supprime.
- Aucun import n'est execute.
- Aucun appel externe n'est execute.
- Aucun secret n'est affiche ou stocke.

### Compatibilite V1

- `[seed_testimonials]` reste stable.
- `[seed_cards]` reste stable.
- Les classes CSS publiques restent prefixees `seed-`.
- Les meta keys du module testimonials restent prefixees `_seed_`.
- Le plugin fonctionne sans ACF.
- Le plugin fonctionne sans Composer.
- Le plugin fonctionne sans npm.

## 7. Criteres de documentation

### Documentation projet obligatoire

- `VISION.md` existe.
- `PROJECT-SNAPSHOT.md` existe.
- `AI-CONTEXT.md` existe.
- `ARCHITECTURE-GUARDRAILS.md` existe.
- `ROADMAP.md` existe.
- `RELEASE-CRITERIA-V1.md` existe.

### Documentation plugin obligatoire

- Installation minimale documentee.
- Activation documentee.
- Desactivation documentee.
- Shortcode `[seed_testimonials]` documente.
- Shortcode `[seed_cards]` documente.
- Attributs des shortcodes documentes.
- Limites V1 documentees.
- Exclusions V1 documentees.
- Procedure de test documentee.
- Procedure de rollback documentee.

### Documentation de validation

- Resultats des tests locaux documentes.
- Resultats de validation `avecguillaume.fr` documentes.
- Risques residuels documentes.
- Decision de release documentee.
- Validation explicite du responsable projet documentee.

## Decision finale

V1 est terminee uniquement si :

- toutes les fonctionnalites obligatoires existent ;
- tous les tests obligatoires sont passes ;
- `avecguillaume.fr` est valide ;
- les conditions avant `therapsycorporel.fr` sont remplies ou explicitement marquees comme non encore executees ;
- le ZIP respecte les criteres de qualite ;
- les criteres de non-regression sont passes ;
- la documentation obligatoire est complete ;
- aucune derive V1.1, V2 ou V3 n'a ete introduite ;
- le responsable projet valide explicitement la release V1.
