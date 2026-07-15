# Recette de test - WP Seed Content Kit 0.3.0

Ce document définit la recette minimale à exécuter sur le ZIP exact avant tout tag ou toute release 0.3.0.

## Préconditions

- WordPress 6.5 ou version ultérieure ;
- PHP 7.0 ou version ultérieure ;
- sauvegarde du plugin installé et des données avant le test ;
- ZIP contenant une seule racine `wp-seed-content-kit/` ;
- contenus temporaires préfixés `SEED TEST -` ;
- accès aux logs PHP et à un rollback immédiat.

ACF, Composer, npm et les services externes ne sont pas requis.

## Installation et rollback

1. Relever la version installée et sauvegarder le dossier du plugin.
2. Installer le ZIP candidat exact depuis l'administration WordPress.
3. Activer le plugin et vérifier la version 0.3.0.
4. Vérifier l'administration et une page publique en HTTP 200.
5. Contrôler l'absence de fatal, warning ou sortie inattendue dans les logs.
6. Après la recette, réinstaller la sauvegarde et confirmer le retour à la version précédente.

## Non-régression

Tester au minimum :

```text
[seed_cards]
[seed_quotes]
[seed_quotes template="citations-accueil"]
[seed_testimonials]
[seed_testimonials template="test"]
```

Vérifier les filtres, limites, tris, contenus mis en avant, ordres manuels, placeholders et layouts Divi Library existants. Activer puis désactiver Citations et Témoignages ; confirmer que les CPT, menus et shortcodes suivent l'état du module sans casser Configuration générale.

## Content Data et Dynamic Data

- confirmer les contrats Citation, Témoignage et média ;
- confirmer les 12 champs du registre dans leur ordre documenté ;
- tester les IDs explicites et les contextes courants ;
- tester un ID invalide, un mauvais CPT et un contexte absent ;
- vérifier les valeurs vides typées : chaîne, booléen, entier ou `null` ;
- vérifier qu'un brouillon n'est pas exposé sans permission explicite ;
- vérifier qu'aucun resolver ne produit de HTML ou ne lit directement les métas.

## Gutenberg Block Bindings

- confirmer l'enregistrement unique de `wp-seed-content-kit/dynamic-data` ;
- tester `core/paragraph.content` et `core/heading.content` ;
- tester une Query Loop Citations avec plusieurs éléments ;
- tester une Query Loop Témoignages avec plusieurs éléments ;
- confirmer que chaque élément reçoit son propre contexte ;
- vérifier qu'un binding invalide retourne `null` et qu'une valeur métier vide retourne `''` ;
- confirmer qu'aucun sélecteur WP Seed natif n'est annoncé dans l'éditeur.

## Divi 5 Dynamic Content expérimental

Confirmer la présence unique des huit options :

- Citations : Texte, Auteur, Époque, Source ;
- Témoignages : Texte, Nom, Contexte, Photo.

Tester :

- un single `seed_quote` et un single `seed_testimonial` ;
- une boucle contenant au moins deux éléments distincts ;
- une page ordinaire incompatible ;
- un `loop_id` non nul valide puis invalide ;
- un brouillon et un contenu privé ;
- la sélection, la sauvegarde et la réouverture d'un module ;
- la persistance brute unique de chaque identifiant.

Pour Photo, vérifier l'URL, l'ID média reconstruit, les dimensions, `srcset`, `sizes` et l'absence de chaîne `Array` ou de variable brute. Consigner séparément l'aperçu du Visual Builder et le texte alternatif, qui ne sont pas garantis dans tous les modules ou contextes.

## Frontend et responsive

Vérifier mobile, tablette et bureau. Confirmer que les pages restent lisibles, que les grilles ne débordent pas, que les images conservent leurs proportions et que le CSS reste limité aux classes `seed-`.

## Nettoyage obligatoire

1. Lister puis supprimer tous les contenus `SEED TEST -` créés pour la recette.
2. Supprimer les scripts, harnais, sauvegardes temporaires et caches de test.
3. Restaurer le plugin sauvegardé si la release n'est pas encore publiée.
4. Vérifier qu'aucune fixture, page, métadonnée ou archive temporaire ne reste sur le site.

## Bloquants de release

Ne pas publier si le ZIP ne s'extrait pas correctement, si l'activation échoue, si une régression shortcode/template apparaît, si un brouillon est exposé, si un contexte incompatible utilise une valeur arbitraire, si un fatal ou warning WP Seed est présent, ou si le rollback n'est pas validé.
