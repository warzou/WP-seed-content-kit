# Recette de test - WP Seed Content Kit

Ce document définit la recette minimale à exécuter sur le ZIP exact avant tout tag ou toute release.

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
3. Activer le plugin et vérifier que la version et le code correspondent au package temporaire testé.
4. Vérifier l'administration et une page publique en HTTP 200.
5. Contrôler l'absence de fatal, warning ou sortie inattendue dans les logs.
6. Après la recette, réinstaller la sauvegarde et confirmer le retour à la version précédente.

## Annuaire L2 a L4

Depuis la racine du depot, executer les harnais directory-l2, directory-l3, directory-l4, wordpress-directory-l2, wordpress-directory-l3 et wordpress-directory-l4.

L4 controle la Data API publique, les Collections, les filtres et ordres, les deux shortcodes, les groupes, le HTML, les deux CSS, les quinze placeholders et tous les fallbacks. Les sentinelles privees doivent etre absentes de la Data API, du contexte, des placeholders, du HTML natif ou template et des logs.

Le harnais WordPress L4 exige WP_SEED_WORDPRESS_LOAD vers un WordPress isole. Il charge les seize fiches fictives de tests/fixtures/directory-l4.json, teste 14 fiches eligibles, Gutenberg, Divi, theme classique, desactivation/reactivation et non-regression Citations/Temoignages. Il mesure Data API, Collection, shortcode natif, shortcode template, requetes et cache de resolution, puis supprime posts, templates, layouts et revisions.

## Non-regression

Tester au minimum :

```text
[seed_cards]
[seed_quotes]
[seed_quotes template="citations-accueil"]
[seed_testimonials]
[seed_testimonials template="test"]
```

Vérifier les filtres, limites, tris, contenus mis en avant, ordres manuels, placeholders et layouts Divi Library existants. Activer puis désactiver Citations et Témoignages ; confirmer que les CPT, menus et shortcodes suivent l'état du module sans casser Configuration générale.

## Modèle Témoignage

- vérifier la présence des champs Date du témoignage et Information complémentaire dans l'administration ;
- enregistrer `2026-02-28` et `2024-02-29`, puis refuser `2026-02-29`, `2026-02-31` et `26-02-2026` sans détruire une ancienne valeur valide ;
- vérifier qu'un champ absent de la requête conserve la méta existante ;
- vérifier qu'un champ présent et exactement vide supprime une ancienne date valide comme invalide ;
- vérifier qu'une valeur non vide invalide, notamment des espaces seuls, des espaces autour d'une date ou un retour ligne, conserve la méta existante sans normalisation silencieuse ;
- vérifier qu'une date valide au format exact `YYYY-MM-DD` remplace une ancienne valeur valide ou invalide ;
- confirmer que la date ISO reste inchangée dans Content Data et Dynamic Data, puis qu'elle est localisée uniquement dans le rendu et le placeholder `{{date}}` ;
- confirmer que `{{context}}` restitue Information complémentaire et qu'aucune clé `testimonial.information` n'est créée.

## Content Data et Dynamic Data

- confirmer les contrats Citation, Témoignage et média ;
- confirmer les 13 champs du registre dans leur ordre documenté, dont `testimonial.testimonial_date` au format ISO strict ;
- tester les IDs explicites et les contextes courants ;
- tester un ID invalide, un mauvais CPT, un contexte absent, une date impossible et une date bissextile valide ;
- vérifier les valeurs vides typées : chaîne, booléen, entier ou `null` ;
- vérifier qu'un brouillon n'est pas exposé sans permission explicite ;
- désactiver chaque module fonctionnel et confirmer qu'un contenu publié explicitement compatible reste résoluble par Content Data et Dynamic Data ;
- vérifier qu'aucun resolver ne produit de HTML ou ne lit directement les métas.

## Collections V1

Depuis la racine du dépôt source, exécuter le harnais direct :

```text
php tests/collections-harness.php
```

Le harnais doit valider les valeurs par défaut, les arguments mal formés, le mode `ids` autoritaire, les états publiés/brouillon/privé/protégé par mot de passe, les valeurs historiques de `_seed_featured`, les quatre tris dans les deux sens, les égalités par ID, les dates métier invalides en fin de liste, `limit` et les gardes de modules. Les modes normal et `ids` doivent exclure tout `post_password` non vide sans fallback ; `featured`, le tri et la limite ne doivent jamais le réintroduire. Content Data et Dynamic Data doivent rester résolubles indépendamment de ces gardes.

Pour la Citation quotidienne, vérifier l'exclusion des Citations protégées avant le tri et le calcul, le retour `0` si elles sont les seules candidates, la liste d'IDs publics triée, la graine `home_url('/')|YYYY-MM-DD`, les sept caractères SHA-256, le modulo, l'absence de mutation et les fuseaux `Europe/Paris`, `Pacific/Kiritimati` et `America/Adak`. Un test WordPress réel peut utiliser les contenus publiés existants ; aucune fixture publiée n'est nécessaire lorsque ces contenus suffisent.

Confirmer qu'un appel normal exécute une seule requête de posts, que le cache des métadonnées WordPress évite les N+1, et qu'un module désactivé retourne avant toute requête. Aucun transient, cache applicatif ou filtre public Collections ne doit être ajouté.

## Adaptateurs Collections

Exécuter le harnais des shortcodes, renderers et Templates :

```text
php tests/collections-adapters-harness.php
```

Le harnais réexécute d'abord les assertions Collections, puis vérifie :

- les valeurs historiques par défaut de `[seed_testimonials]` ;
- `limit="0"`, les valeurs vides, invalides ou négatives, le plafond positif de 24, les tris et l'alias `menu_order` ;
- `featured=only|exclude`, les alias `true|false` et le fallback d'une valeur invalide vers `all` ;
- les CSV d'IDs vides, invalides, mixtes, dupliqués, protégés, brouillons et d'un mauvais CPT ;
- le filtre historique `context`, notamment les valeurs vide et `"0"`, et l'autorité du mode `ids` ;
- le renderer natif, les placeholders `{{context}}` et `{{date}}`, l'échappement et le fallback d'un Template introuvable ou du mauvais module ;
- le hasard historique de `[seed_quotes]`, y compris pour une valeur `mode` inconnue ;
- `[seed_quotes mode="daily"]`, son absence de `RAND`, sa stabilité et ses états vides ;
- un Template natif Citation et un Layout Divi Library ;
- le rendu serveur d'un bloc Shortcode Gutenberg, le parcours shortcode compatible Spectra et plusieurs shortcodes sans état partagé.

En recette WordPress réelle, comparer avant/après le HTML de `[seed_testimonials]` et `[seed_quotes]` sans nouvel attribut. Tester ensuite Tous, featured, une sélection `ids`, la Citation quotidienne, un Template natif et un Layout Divi Library existant. Mesurer les requêtes, vérifier HTTP 200 et les logs, puis restaurer les fichiers exacts déployés temporairement. Aucune fixture publiée n'est nécessaire.

## Template Extension Contract 1.0

Exécuter :

```text
php tests/template-extension-harness.php
php tests/wordpress-template-extension-harness.php
```

Vérifier la version du contrat et toutes les capacités publiques, la fenêtre d'enregistrement, les doublons, les identifiants invalides, les types fermés, les contextes complets ou partiels, les clés inconnues, les valeurs obligatoires, les templates absents ou brouillons et les modules incompatibles.

Vérifier séparément l'échappement de chaque type, les erreurs provider, la récursion directe et indirecte, la restauration de pile, la validation atomique des assets et leur chargement unique après succès. Le harnais WordPress utilise un module tiers neutre et couvre le pipeline serveur Gutenberg ainsi que le fonctionnement sans classe Divi.

Relancer impérativement les harnais Collections et Adaptateurs afin de confirmer que Témoignages et Citations conservent leurs sorties historiques.

## Gutenberg Block Bindings

- confirmer l'enregistrement unique de `wp-seed-content-kit/dynamic-data` ;
- tester `core/paragraph.content` et `core/heading.content` ;
- tester une Query Loop Citations avec plusieurs éléments ;
- tester une Query Loop Témoignages avec plusieurs éléments ;
- confirmer que chaque élément reçoit son propre contexte ;
- vérifier qu'un binding invalide retourne `null` et qu'une valeur métier vide retourne `''` ;
- confirmer qu'aucun sélecteur WP Seed natif n'est annoncé dans l'éditeur ;
- tester `testimonial.testimonial_date` dans un contexte Témoignage, vide et incompatible ;
- confirmer qu'un contexte publié compatible reste résoluble lorsque le module Témoignages est désactivé.

## Divi 5 Dynamic Content expérimental

Confirmer la présence unique des neuf options :

- Citations : Texte, Auteur, Époque, Source ;
- Témoignages : Texte, Nom, Information complémentaire, Date du témoignage, Photo.

Tester :

- un single `seed_quote` et un single `seed_testimonial` ;
- une boucle contenant au moins deux éléments distincts ;
- une page ordinaire incompatible ;
- un `loop_id` non nul valide puis invalide ;
- un brouillon et un contenu privé ;
- la sélection, la sauvegarde et la réouverture d'un module ;
- la persistance brute unique de chaque identifiant ;
- la valeur ISO de Date du témoignage et sa valeur vide en contexte incompatible ;
- la résolution d'un contexte publié compatible lorsque le module Témoignages est désactivé.

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

## Recette 0.6.0-rc.1

La validation RC doit etre executee sous PHP 7.0.33 et PHP 8.4.23, puis depuis le ZIP installable exact sous WordPress. Elle couvre les modules historiques, le contrat Template Extension 1.0, l'Annuaire L2/L3/L4, l'activation, la desactivation, la reactivation et les profils Gutenberg, Divi et theme classique.

Le package RC est construit deux fois avec un ordre, des chemins et des metadonnees deterministes. Les deux archives doivent avoir le meme SHA-256. Le ZIP ne contient que le plugin installable sous une racine unique wp-seed-content-kit/ ; les tests, fixtures, documents de depot et artefacts de recette en sont exclus.

Pour l'Annuaire, verifier en plus que l'etat vide charge le CSS structurel sans CSS de carte, que les cartes gardent une hauteur naturelle, que la grille reste 3/2/1 et qu'aucune valeur privee ou invalide n'apparait dans le HTML.

## CK-A1 - Administration et Utilisation

Exécuter tests/admin-usage-harness.php sous PHP 7.0 et PHP 8.4. Le harnais vérifie les onglets, les liens Templates, les paramètres Collections, le générateur Annuaire non persistant et les statuts des intégrations.

Dans WordPress, vérifier avec administrator le menu Configuration, Témoignages, Citations, Annuaire et Utilisation. Désactiver chaque module et confirmer que son menu suit le réglage sans casser les autres pages.

Avec editor, confirmer que Utilisation reste inaccessible. Tester la navigation au clavier, le focus visible, les boutons Copier, aria-live et le repli mobile sous 782 pixels. Confirmer que CK-A1 ne crée ni REST, AJAX ou Collection persistante.

## CK-A2 - Rôles et capacités

Exécuter `tests/role-capabilities-harness.php` sous PHP 7.0 et PHP 8.4, puis `tests/wordpress-role-capabilities-harness.php` avec `WP_SEED_WORDPRESS_LOAD` vers un WordPress isolé.

Avec Administrator, vérifier Configuration, Utilisation, Templates et les trois modules actifs. Avec Editor, vérifier uniquement les listes et actions Ajouter des modules autorisés, ainsi que création, modification, publication, dépublication et suppression des contenus propres ou créés par d’autres éditeurs. Editor ne doit voir ni Configuration, Utilisation, Templates, Collections, imports ou maintenance.

Retirer puis restaurer l’attribution Editor module par module. Désactiver un module et confirmer menu/CPT absents, contenus et capacités conservés ; réactiver et confirmer leur retour. Vérifier les six capacités avancées Administrator, les quatre primitives de chaque module, les mappings CPT, l’absence de REST/AJAX et l’absence de dépendance à WP Seed Events ou WP Seed Directory.
