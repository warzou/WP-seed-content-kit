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

L4 controle la Data API publique, les Collections, les filtres et ordres, les deux shortcodes, les groupes, le HTML, les deux CSS, les vingt et un placeholders et tous les fallbacks. Il verifie aussi qu'une fiche sans photo ne rend aucun wrapper media ou placeholder et que la grille neutralise les marqueurs de liste herites du theme. Les sentinelles privees doivent etre absentes de la Data API, du contexte, des placeholders, du HTML natif ou template et des logs.

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

Exécuter `tests/divi-generic-loop-context-harness.php`, `tests/quote-builder-meta-contract-harness.php`, `tests/quote-builder-meta-migration-harness.php`, `tests/divi-quote-loop-collection-harness.php` et `tests/divi-per-item-context-harness.php` sous PHP 7.0.33 et PHP 8.4.x. Le harnais générique reproduit un binding historique sauvegardé, sa normalisation en mémoire sur `divi_visual_builder_settings_data_post_content`, l'appel sans contexte, la conservation du token différé, l'arrivée de `loop_id` ou `loop_object`, les clés QueryResults sans préfixe et la résolution distincte des clones Témoignages et Citations. Une source Annuaire synthétique vérifie le point d'extension sans annoncer de providers Annuaire existants.

Exécuter ensuite `tests/wordpress-divi-per-item-context-harness.php` avec `WP_SEED_WORDPRESS_LOAD` vers un WordPress isolé sous PHP 8.4 et Divi 5.9.0. Le harnais crée puis supprime trois Témoignages, un Layout et un Template fictifs. Il vérifie :

- cinq valeurs Dynamic Content distinctes par carte ;
- deux modules et deux Layouts sur une même page lors de la recette navigateur ;
- l'absence de variable brute et de répétition de la première carte ;
- le Layout enregistré inchangé ;
- quatre gardes : Layout statique, dynamique résolu, dynamique non résolu et réellement vide ;
- le fallback natif local et la restauration après erreur ;
- la parité frontend/Visual Builder, les viewports 1440, 820, 390 et 320 px et le zoom 200 %.

L'éditeur isolé du Layout peut rester sans contexte. Ne jamais enregistrer un ID fictif dans le Layout pour son aperçu. Supprimer le WordPress jetable, les médias, captures et copies privées de Divi après la recette.

La recette Native Loop prise en charge couvre Témoignages et Citations, en boucle simple et dans un Group Carousel. Elle doit vérifier les valeurs distinctes par clone, le frontend, le vrai Visual Builder et deux cycles Save/Close/Reopen. L'Annuaire reste hors périmètre tant que ses providers et ses règles de visibilité ne sont pas implémentés.

Note de supervision : Divi reconnaît les attributs de boucle préfixés par `loop_` via `getLoopedAttrs()` et retire ce préfixe pour lire la clé homologue dans QueryResults. Le contrat WPSCK s'appuie uniquement sur ce pipeline de données et sur les APIs REST/Provider existantes. Ne pas introduire de spike fondé sur les modules Webpack internes, l'arbre React, le DOM, `MutationObserver` ou un délai artificiel.

Confirmer la présence unique des treize options :

- Citations : Texte, Auteur, Époque, Source ;
- Témoignages : Visuel, Titre, Résumé, Témoignage complet, Nom, Contexte, Date, ID, Ancre.

Tester :

- un single `seed_quote` et un single `seed_testimonial` ;
- deux contextes serveur explicites contenant des éléments distincts ;
- une page ordinaire incompatible ;
- un `loop_id` non nul valide puis invalide ;
- un brouillon et un contenu privé ;
- la sélection, la sauvegarde et la réouverture d'un module ;
- la persistance brute unique de chaque identifiant ;
- la valeur ISO de Date du témoignage et sa valeur vide en contexte incompatible ;
- la résolution d'un contexte publié compatible lorsque le module Témoignages est désactivé.

Pour Photo, vérifier l'URL, l'ID média reconstruit, les dimensions, `srcset`, `sizes` et l'absence de chaîne `Array` ou de variable brute. Consigner séparément le texte alternatif, qui n'est pas garanti dans tous les modules.

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

## CK-A3 - Saisie Annuaire

Exécuter `tests/directory-l3-harness.php` sous PHP 7.0 et PHP 8.4, puis `tests/wordpress-directory-l3-harness.php` avec `WP_SEED_WORDPRESS_LOAD` vers un WordPress isolé.

Vérifier brouillon vide, publication valide, autorisation absente, photo sans alt, fiche sans photo et les cinq coordonnées en modes privé, public valide, public vide et public invalide. Un contact privé reste absent de la Data API, du contexte Template et du shortcode ; un contact public invalide rend la fiche inéligible et la publication revient en brouillon.

Avec Editor, vérifier création, publication valide, modification d’une fiche d’un autre éditeur, dépublication, corbeille et restauration. Confirmer l’absence de Configuration, Utilisation, Templates, Collections et outils techniques. Avec Administrator, confirmer la même fiche métier et les écrans avancés séparés. Tester les libellés, liens d’erreur, labels, fieldsets, clavier, focus, mobile, filtre de statut administratif, Quick Edit absent et publication en masse absente.

## CK-A4 - Templates, Collections et guidage

Exécuter tests/admin-usage-harness.php sous PHP 7.0 et PHP 8.4, puis les harnais historiques. Vérifier les quatre onglets, les quatre sous-onglets, les relations aria-selected/aria-controls, les flèches, Home/End, le focus visible, la copie des shortcodes et des 26 placeholders, ainsi que les tables mobiles.

Contrôler les trois exemples Fonctionnement et les catalogues Collections. Annuaire doit exposer exactement status, department, country, featured, ids, limit, orderby et order. Générer au moins un shortcode Annuaire, Témoignages et Citations, avec et sans Template, puis tester attribut invalide, résultat vide, module désactivé et alias Annuaire déprécié.

Dans WordPress Playground, vérifier Administrator avec accès complet et Editor sans Utilisation, Templates, Collections, générateurs ni placeholders. Tester sans Divi et sans Spectra, puis confirmer les libellés Fonctionnel, Indirect, Expérimental et Non disponible. Aucun test CK-A4 ne doit créer de CPT Collection, de sauvegarde, de REST/AJAX ou d’association persistante entre Collection et Template.

## Migration Annuaire CK-A6

Executer `tests/directory-migration-harness.php` sous les runtimes PHP compatibles, puis `tests/wordpress-directory-migration-harness.php` dans un WordPress isole sous PHP 8.4. Le second harnais exige le plugin actif et cree puis nettoie lui-meme ses contenus et son utilisateur Editor.

Verifier le manifeste ferme, les 16 fiches, les 13 medias, les refus globaux sans ecriture, l'import initial, le reimport `unchanged`, les mises a jour fiche/media sur ID conserve, `missing_from_source`, le registre non autoloaded, les permissions, la Data API, les Collections, les Templates, les shortcodes et les deux rollbacks. A la fin, aucune fiche, revision, piece jointe, option de lot, page, Template, Citation, Temoignage ou utilisateur de recette ne doit subsister.

## Regression globale CK-A7 et RC2

Executer les dix harnais autonomes et le lint complet sous PHP 7.0.33 puis PHP 8.4.23. Sous WordPress 7.0.2/PHP 8.4.23, executer les harnais Template Extension, Annuaire L2/L3/L4, roles et migration. Tester separement une installation neuve du ZIP, une mise a jour depuis 0.4.0 et un cycle desactivation, desinstallation puis reinstallation sur une copie dediee.

La matrice navigateur minimale couvre Citations, Temoignages, Annuaire, page mixte, administration Annuaire et Utilisation aux formats 1440 x 1000, 1280 x 900, 1024 x 900, 820 x 1180, 768 x 1024, 390 x 844, 375 x 812 et 320 x 700, puis a 200 % de zoom. Verifier HTTP 200, console, ressources, debordements, grille 3/2/1, images et alt, contacts, focus, onglets, formulaires et tables.

Le theme classique et Spectra sont testes dans le navigateur. Divi reste un produit tiers facultatif : en l'absence d'un package prive autorise dans l'environnement isole, ses contrats, son Layout Library, son fallback et son Dynamic Content experimental restent valides par les harnais dedies. Ne jamais ajouter Divi ou un autre builder au package Content Kit.

Construire deux fois le ZIP RC2 depuis les seuls fichiers distribues, comparer les SHA-256, extraire et relinter chaque archive. Le package doit avoir une racine unique wp-seed-content-kit/, des chemins Linux, aucun test, fixture, document historique, secret ou temporaire.

## Correction responsive Annuaire RC3

Executer tests/directory-l3-harness.php sous PHP 7.0.33 et PHP 8.4.23, puis tests/wordpress-directory-l3-harness.php dans WordPress. Le CSS correctif doit etre emis uniquement sur les ecrans Annuaire.

Verifier avec Administrator et Editor les formats 1440 x 1000, 820 x 1180, 390 x 844, 320 x 700 et le zoom 200 %. Le document ne doit jamais depasser le viewport ; les champs de localisation et de contact restent fluides, tandis que leurs largeurs maximales desktop sont conservees. Recontroler les aides, erreurs, cases de visibilite, focus, cibles tactiles et l'absence de regression sur Citations, Temoignages, Templates et Utilisation.

## Validation stable 0.6.0

La stable reprend strictement le perimetre fonctionnel de RC4. Reexecuter le lint complet et les dix harnais autonomes sous PHP 7.0.33 et PHP 8.4.23, puis les six harnais WordPress sous WordPress 7.0.2. Valider une installation neuve, une mise a jour depuis 0.4.0 et le cycle activation, desactivation, reactivation, desinstallation non destructive et reinstallation.

Sur DEV protege, limiter les contenus temporaires au prefixe SEED CONTENT KIT TEST - STABLE -. Verifier Administrator, Editor, Citations, Temoignages, Annuaire natif et Template, page mixte, etat vide, Divi, confidentialite, absence de Collection persistante et nettoyage complet. La matrice visuelle minimale reste 1440 x 1000, 820 x 1180, 390 x 844, 320 x 700 et zoom 200 %.

Construire le ZIP stable deux fois depuis les seuls fichiers distribues. Les deux archives doivent etre identiques octet pour octet, avoir une racine unique wp-seed-content-kit/, utiliser uniquement des / et exclure tests, fixtures, documents de depot, secrets et temporaires.

## Module Divi 5 Témoignages — 0.7.0-rc.1

Exécuter `tests/divi-testimonial-collection-harness.php` sous PHP 7.0.33 et PHP 8.4.x, puis les harnais historiques. Vérifier l’absence complète du module et de la route lorsque Divi 5 est absent, l’enregistrement via `ModuleRegistration`, les valeurs par défaut, `ids`, `featured`, `context`, `limit`, `orderby`, `order`, Template et colonnes, ainsi que l’équivalence avec `[seed_testimonials]`.

Dans un WordPress isolé avec Divi 5.9.x, créer des Témoignages fictifs, un Template natif et un Template utilisant un Layout Divi Library. Vérifier le Visual Builder sans chargement permanent, les changements de réglages sans rechargement, les états vide et fallback, les droits Administrator/Editor, l’absence de fuite privée et les viewports 1440, 820, 390 et 320 px avec zoom 200 %. Après recette, supprimer site, fixtures, captures brutes, runtime et copie privée de Divi.

## Annuaire 0.8.0-rc.1 — profils multi-usages

Harnais dédiés :

```text
php tests/directory-profile-types-harness.php
WP_SEED_WORDPRESS_LOAD=/chemin/wp-load.php php tests/wordpress-directory-profile-types-harness.php
```

La matrice WordPress jetable couvre Alice (praticienne), Bruno (intervenant), Céline (les deux), David (praticien avec recherche active), Emma (intervenante avec recherche active), une fiche historique sans nouvelle méta et un brouillon. Elle vérifie les Collections exactes, la confidentialité, les shortcodes, les Templates, la sauvegarde Editor, le refus d'un utilisateur non autorisé, le nonce, la sauvegarde partielle et la migration idempotente.

La recette Divi 5.9.0 doit utiliser un Layout de Template Annuaire, sans Loop Builder, puis vérifier frontend, Visual Builder, profil multi-type, valeur vide, fallback local et intégrité du Layout enregistré.


## Annuaire 0.8.0-rc.2 — matrice de validation

Les harnais couvrent la case listée/non listée, les sauvegardes partielles, les Collections et IDs explicites, les trois champs de présentation, le filtrage HTML, vingt profils fictifs aux types et statuts variés, ainsi que la migration par lots, sa reprise, ses erreurs, ses compteurs et sa seconde exécution sans effet. Les suites WordPress et Divi doivent confirmer la parité frontend/Visual Builder sans exposition privée.

## Module Divi 5 Annuaire — 0.8.0-rc.2

Exécuter sous PHP 7.0.33 et PHP 8.4 :

```text
php tests/divi-directory-collection-harness.php
php tests/wordpress-divi-directory-collection-harness.php
```

Les harnais couvrent le chargement conditionnel Divi, les paramètres, la route GET, le nonce REST, `edit_pages`, les erreurs 400, les en-têtes sans cache, les Templates publiés et le renderer partagé. Rejouer ensuite tous les harnais historiques, CK-A6, les rôles, Template Extension, Témoignages et Citations.

Dans WordPress 7.0.2 avec Divi 5.9.0 exact, créer uniquement des fixtures jetables couvrant :

- Annuaire complet, Praticiens, Intervenants et Recherche de modèles ;
- types OR et AND, ID inclus et exclu, ordre, limite et offset ;
- fiche sans type, multi-type, non listée, brouillon, privée et protégée ;
- `summary`, `bio`, `full_presentation`, paragraphes, liste, lien et valeur vide ;
- Template natif, absent, brouillon, vide et erreur sur une carte intermédiaire.

Comparer les IDs, l’ordre et le HTML fonctionnel entre frontend et Visual Builder. Tester les changements de réglages, le rerender, la sauvegarde, la fermeture et la réouverture, sans chargement permanent ni erreur réseau ou console.

Vérifier 1440 × 1000, 820 × 1180, 390 × 844, 320 × 700, zoom 200 %, clavier, focus, mouvement réduit et frontend sans JavaScript. Supprimer ensuite WordPress, base, fixtures, captures brutes, profil navigateur et copie privée de Divi.

Sans Divi, confirmer l’absence du module et de la route, sans fatal ni régression des shortcodes. Ne jamais tester cette capacité avec le Loop Builder natif ou un contournement DOM.

## Régression RC.3 `ids` + `exclude_ids`

Valider `ids` seul, exclusions seules, intersection nulle, partielle et totale, doublons, IDs invalides ou inexistants, fiches non listées/brouillons/privées/protégées, types OR/AND, Recherche de modèles, tri, offset et limite. Rejouer les mêmes paramètres via l'API Collections, `[seed_directory]`, `[wp_seed_directory]`, le renderer partagé, le module Divi 5 et la route privée d'aperçu. Frontend et Visual Builder doivent retourner les mêmes IDs dans le même ordre. La mise à niveau RC.2 vers RC.3 ne déclenche aucune migration métier.

## Témoignages Native Divi Loop — 0.8.0-rc.4

Exécuter les harnais Collections, stockage portable, migration de consentement, adaptateur Loop et contexte Loop sous PHP 7.0 et PHP 8.4. Ils couvrent le fail-closed, featured sans consentement, les quatre modes, la limite, l’absence de doublons, le seed Builder, les alias par item et le rollback exact. Le filtre de contexte doit couvrir explicitement priorité de `seed_testimonial_context`, divergence avec `_seed_testimonial_context`, fallback legacy seul et valeurs identiques.

Dans WordPress avec Divi 5.9.0 exact, vérifier le groupe unique « WPSCK — Témoignages » et ses neuf champs dans l’ordre Visuel, Titre, Résumé, Témoignage complet, Nom, Contexte, Date, ID et Ancre. Contrôler leurs vraies valeurs sur les items 1, 2 et 3, sans valeur brute, puis effectuer deux cycles sauvegarde/fermeture/réouverture.

Vérifier ensuite une recette Detailed et un Group Carousel natif dont la Loop est portée par le Group/slide : frontend, Visual Builder, tailles 0/1/3/22/23 et contexte imbriqué distinct via `loop_id` puis `loop_object`. Les modules du Carousel et tout leur design restent natifs Divi ; WPSCK ne fournit aucun Carousel ni CSS de présentation Carousel.

Pour Detailed, vérifier alternance desktop via les classes opt-in, média en premier sur mobile et zoom 200 %. Les Grid Offset Rules natives ciblent les enfants directs du conteneur et ne remplacent pas la règle paire/impaire du clone Loop.

Confirmer enfin que `post_title`, `post_excerpt`, l’image mise en avant et les métas publiques `seed_testimonial_text`, `seed_testimonial_name`, `seed_testimonial_context` restent accessibles au contrat builder-agnostic. La date absente demeure vide. Aucun test permanent ne doit contenir d’ID ou d’URL de recette DEV.
