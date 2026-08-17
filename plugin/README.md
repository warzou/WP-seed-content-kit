# WP Seed Content Kit

Statut du package : 0.8.0-rc.7, hotfix de stabilité des alternances Native Loop dans le Visual Builder Divi, non stable.

WP Seed Content Kit est un plugin WordPress de contenus éditoriaux structurés et de présentations réutilisables.

Il fonctionne sans thème imposé, sans ACF obligatoire et sans dépendance à un constructeur de page particulier.

## Administration et Utilisation

Le menu administrateur regroupe Configuration, Témoignages, Citations, Annuaire et Utilisation. La page Utilisation explique le circuit Contenus, Collections, Templates et Intégrations, puis documente Shortcodes, Gutenberg, Spectra et Divi selon leur état réel.

Collections reste une API de sélection et un ensemble de paramètres. Aucun contenu Collection, CPT ou écran de sauvegarde n’est créé. CK-A2 accorde par défaut la gestion des contenus à Administrator et Editor, avec une attribution Editor configurable par module. Configuration, Utilisation, Templates et les outils avancés restent réservés à Administrator.
## Fonctionnalités actuelles

### Témoignages

- contenus structurés ;
- nom ou initiales ;
- texte ;
- photo ;
- date du témoignage ;
- Information complémentaire ;
- mise en avant ;
- ordre manuel ;
- templates réutilisables.

Le stockage builder-compatible utilise `post_title`, `post_excerpt`, l’image mise en avant, `seed_testimonial_text`, `seed_testimonial_name` et `seed_testimonial_context`. La date métier est optionnelle. Les anciennes métas `_seed_testimonial_*` restent uniquement des fallbacks de compatibilité.

### Citations

- citation ;
- auteur facultatif ;
- époque ou date affichée facultative ;
- source ou contexte facultatif ;
- mise en avant ;
- ordre manuel ;
- templates réutilisables.

### Annuaire - sortie publique 0.6.0

Le module natif Annuaire fournit son CPT administratif privé, vingt-deux métas validées, autorisation explicite et garde de publication. L4 ajoute une Data API publique fermee, des Collections par IDs, [seed_directory], deux groupes automatiques, une carte native responsive et des Templates Content Kit.

Seuls les contacts valides et explicitement visibles sont publics. Il n'existe aucune page individuelle, archive, recherche, AJAX public, import métier automatique ou adaptateur inter-plugin. Le CPT privé dispose d'une route REST authentifiée et de métas publiques builder-compatible pour Gutenberg et les constructeurs. Les mises à niveau runtime restent additives : RC.1 copie l'ancien statut de recherche vers le booléen dédié sans attribuer de type ; RC.2 initialise uniquement la visibilité publique des fiches déjà publiées et non protégées sans valeur explicite.

CK-A3 fournit à Editor et Administrator une fiche organisée en cinq panneaux avec nom affiché, profil multi-usages, statut historique, localisation, présentation/photo, coordonnées et autorisation. Les coordonnées sont des lignes répétables, ordonnées et privées par défaut. Le registry administratif propose Téléphone et E-mail sur une installation neuve, puis accepte des types supplémentaires à slug stable et comportement de lien contrôlé. Les builders consomment séparément les valeurs, liens et conditions de présence registry-driven afin de conserver la maîtrise du design. Une coordonnée peut rester privée en brouillon ; si elle est rendue publique, une valeur vide ou invalide bloque la publication avec un message lié au champ.

### Cards

Cards affiche les articles WordPress natifs sous forme de cartes. Il ne crée pas de type de contenu supplémentaire.

### Templates WP Seed

Les templates permettent de mettre en forme les Témoignages, les Citations et les fiches Annuaire avec :

- le contenu du template dans l'éditeur WordPress ;
- Gutenberg ou Spectra ;
- un layout Divi Library sélectionné comme source du rendu.

Sous Divi 5, le module natif « WP Seed — Témoignages » sélectionne et rend directement une Collection sans shortcode saisi. Les Layouts Divi Library restent disponibles comme source d’un Template WP Seed facultatif.

### Content Data API

La Content Data API fournit une représentation normalisée des Citations, des Témoignages et de leur média. Elle centralise la lecture des données sans produire de HTML et sans dépendre d'un constructeur de page.

### Collections V1

Collections V1 sélectionne des Témoignages publics ordonnés et une Citation quotidienne déterministe. Le shortcode Témoignages utilise cette API tout en conservant ses valeurs par défaut historiques. Le shortcode Citations garde son hasard historique et propose explicitement `mode="daily"`.

### Dynamic Data

Dynamic Data expose 13 champs normalisés à des intégrations de présentation. Le résolveur utilise un contenu explicite ou le contexte WordPress courant, applique les permissions de lecture et retourne des valeurs vides typées lorsque le contexte n'est pas compatible.

### Gutenberg Block Bindings

Un provider serveur permet de lier huit champs texte WP Seed à l'attribut `content` des blocs Paragraphe et Titre Core. L'interface éditeur native WP Seed n'est pas finalisée : aucun sélecteur dédié n'est annoncé dans Gutenberg.

### Divi 5 Dynamic Content expérimental

Lorsqu'un Layout Divi Library sert de Template Témoignage, Content Kit injecte en mémoire l'ID du témoignage courant dans ses cinq variables Dynamic Content, y compris lorsqu'elles sont échappées dans un attribut de bloc sérialisé. L'injection précède le parsing frontend Divi ; chaque carte possède ainsi une identité de cache distincte. Une pile interne limitée à 16 niveaux est toujours restaurée et un signal attendu/résolu interdit les cartes dynamiques vides. Le contenu enregistré du Layout n'est jamais modifié et une erreur de contexte ou de rendu déclenche uniquement le fallback natif de la carte concernée.

L'édition isolée du Layout ne possède pas de témoignage courant et peut donc afficher ces champs vides. Aucun ID de démonstration n'est persisté pour fabriquer cet aperçu.

Sous Divi 5, le provider Dynamic Content enregistre quatre champs Citation (Texte, Auteur, Époque, Source) et cinq champs Témoignage (Texte, Nom, Information complémentaire, Date du témoignage, Photo). Leur sélection et leur persistance visuelles ont été validées sous Divi 5.9.0.

Ces sources dépendent du contenu courant ou du contexte explicite fourni par un Template Content Kit. Elles complètent les Templates WP Seed et les layouts Divi Library ; elles ne les remplacent pas.

Sous Divi 5.9.0, le groupe « WPSCK — Témoignages » regroupe Visuel, Titre, Résumé, Témoignage complet, Nom, Contexte, Date, ID et Ancre pour les boucles natives. Les valeurs sont résolues par item dans le frontend et le Visual Builder.

La Loop peut également porter le Group/slide d’un Group Carousel natif Divi. Les providers récupèrent alors le contexte imbriqué par `loop_id` ou `loop_object` ; aucun Carousel propriétaire n’est ajouté par WPSCK. Divi contrôle présentation et responsive, tandis que WPSCK contrôle données, requête, consentement et providers.

Le stockage reste builder-agnostic et compatible avec Gutenberg/custom-fields ainsi qu’avec de futurs adaptateurs Spectra/Astra. Annuaire réutilise les champs WordPress natifs et neuf métas publiques `seed_directory_*`, dont l'intitulé professionnel facultatif ; les métas privées historiques restent uniquement des fallbacks. Son groupe `WPSCK — Annuaire` expose les providers métier, dont les vues complète, introduction et suite dérivées du bloc More WordPress, dans les Native Loops et les Group Carousels natifs. L'intitulé professionnel reste une donnée éditoriale distincte des types de profil Praticien et Intervenant.

Faute de condition native Divi 5.9.0 sur la parité du clone Loop, seule l’alternance Detailed Témoignages dispose d’un CSS structurel opt-in, sans style éditorial.

## Template Extension API

Le contrat public 1.0 permet à un plugin tiers d'enregistrer un module de Template et des placeholders typés, puis de rendre un `seed_template` publié par slug. Le contexte transmis est fermé, les erreurs sont typées et le fallback reste sous la responsabilité du plugin appelant.

Détection minimale :

```php
wp_seed_content_kit_supports('template_extension', '1.0');
```

Le contrat fonctionne sans Divi, ne crée aucun endpoint et ne lit aucune donnée métier tierce implicitement. Voir `docs/TEMPLATE-EXTENSION-API.md`.

## Shortcodes publics

```text
[seed_cards]
[seed_testimonials]
[seed_quotes]
[seed_directory]
```

Exemples :

```text
[seed_testimonials limit="0" orderby="display_order" order="asc"]
[seed_testimonials featured="only" limit="3" template="accueil"]
[seed_testimonials ids="12,18,27" template="accueil"]
[seed_quotes template="citations-accueil"]
[seed_quotes mode="daily" template="citation-du-jour"]
[seed_directory status="practicing" featured="all" template="annuaire-carte"]
```

Le détail des attributs et placeholders se trouve dans `docs/USAGE.md`.

## Compatibilité

WP Seed Content Kit est conçu pour fonctionner avec :

- WordPress 6.5 ou version ultérieure ;
- PHP 7.0 ou version ultérieure ;
- les thèmes classiques ;
- Gutenberg ;
- Spectra ;
- Astra ;
- Divi Library ;
- les zones acceptant les shortcodes WordPress.

Le plugin reste fonctionnel sans Divi. Le provider Dynamic Content nécessite Divi 5 ; il n'est pas chargé sous Divi 4.

ACF, Composer, npm et les services externes ne sont pas requis pour utiliser le plugin.

## Installation ZIP

Le ZIP doit contenir un seul dossier racine :

```text
wp-seed-content-kit/
```

Structure minimale attendue :

```text
wp-seed-content-kit/
- wp-seed-content-kit.php
- includes/
- assets/
- README.md
- docs/
```

Installation :

1. ouvrir Extensions > Ajouter une extension dans WordPress ;
2. téléverser le ZIP ;
3. activer WP Seed Content Kit ;
4. vérifier les modules actifs ;
5. tester les shortcodes sur une page de brouillon.

## Mises à jour

Les versions stables sont distribuées par GitHub Releases et détectées dans l'administration WordPress par le mécanisme de mise à jour embarqué.

L'asset de release attendu est `wp-seed-content-kit.zip`.

## Limites actuelles

Le plugin ne fournit pas :

- de module Divi 4 ou de module Divi personnalisé pour Citations et Annuaire ;
- de widget Elementor ;
- de bloc Gutenberg personnalisé ;
- de sélecteur WP Seed finalisé dans l'éditeur Gutenberg ;
- d'intégration ACF obligatoire ;
- d'import ou de migration automatique ;
- de recherche publique ni de fiche individuelle pour le module Annuaire ;
- de desinstallation destructive automatique des donnees ;
- de module fonctionnel Créations sonores.

Le provider Divi 5 Dynamic Content reste expérimental. Il est pris en charge sur un contenu courant individuel, dans un Template Content Kit avec contexte par carte et dans les Native Loops Témoignages et Citations, y compris dans un Group Carousel natif. L’alternance gauche/droite des sous-colonnes exige le CSS structurel opt-in fourni par Content Kit, car Divi 5.9.0 ne peut pas conditionner leur ordre selon la parité du clone Loop parent.

## Documentation

- `docs/USAGE.md`
- `docs/TESTING.md`
- `docs/UPDATES.md`

## Parcours Utilisation CK-A4

La page Utilisation explique le parcours Contenus → Collections → Templates → Intégrations. Les Collections sont des paramètres non persistants de sélection. Les Templates sont facultatifs et indépendants de la sélection. Les générateurs Témoignages, Citations et Annuaire produisent des shortcodes copiables sans enregistrer de réglage.

Le catalogue de Templates expose les placeholders publics réels, leur type et leur comportement vide. Le shortcode reste supporté partout ; Gutenberg utilise le bloc Shortcode Core et Spectra reste indirect. Divi 5 propose en plus le module natif « WP Seed — Témoignages », avec aperçu serveur, sélection de Collection et Template facultatif. Editor gère les contenus autorisés mais ne voit ni la gestion des Templates ni la configuration globale.

## Migration fictive CK-A6

Une API PHP interne permet de tester explicitement l'import et le rollback du manifeste fictif Annuaire. Elle valide le manifeste entier avant ecriture, conserve un registre prive non autoloaded et exige `manage_wp_seed_imports`. Elle ne s'execute jamais automatiquement et n'expose ni ecran, ni REST/AJAX, ni donnees de migration dans le rendu public. Voir `docs/ANNUAIRE-MIGRATION.md`.

## Module Divi 5 Témoignages — 0.7.0-rc.1

Le module `WP Seed — Témoignages` utilise directement la Collection canonique et le renderer partagé avec `[seed_testimonials]`. Il expose le titre facultatif, `featured`, `context`, `ids`, `limit`, `orderby`, `order`, le Template Content Kit facultatif et le nombre de colonnes. Le Visual Builder récupère un aperçu serveur authentifié ; le frontend ne dépend d’aucun shortcode généré.

Le module et sa route d’aperçu ne sont enregistrés que lorsque Divi 5 est actif. Sans Divi, le plugin, Gutenberg, les shortcodes, les Templates, Citations et Annuaire restent inchangés. Le choix des Templates est réservé aux utilisateurs disposant de `manage_wp_seed_templates`.

## Annuaire multi-usages — 0.8.0-rc.1

Une fiche Annuaire peut être praticien, intervenant, les deux ou ne pas encore être classée. La recherche actuelle de modèles est un statut temporaire indépendant.

```text
[seed_directory profile_type="praticien"]
[seed_directory profile_type="intervenant"]
[seed_directory seeking_models="1"]
[seed_directory profile_type="praticien" seeking_models="1"]
```

Sans ces attributs, `[seed_directory]` conserve son comportement historique et inclut les fiches existantes sans type. Les Collections restent non persistantes et aucun filtre n'est affiché aux visiteurs.


## Annuaire — visibilité publique et présentation complète — 0.8.0-rc.2

L’éditeur natif stocke la présentation complète dans `post_content`. La sortie publique sépare `summary`, `bio` (alias) et `full_presentation`. La case « Afficher cette personne dans les annuaires publics » est indépendante des autres statuts et ferme toutes les Collections lorsque sa méta exacte `1` est absente.

La migration RC.2 est additive, reprenable et idempotente. Elle liste uniquement les fiches existantes publiées, non protégées et sans valeur explicite, sans modifier leur contenu ou leurs autres métadonnées.

## Module Divi 5 Annuaire — 0.8.0-rc.2

Divi 5 propose le module `WP Seed — Annuaire`. Il configure les Collections complètes, Praticiens, Intervenants, Recherche de modèles, les combinaisons OR/AND, les IDs et exclusions, la limite, l’offset, le tri et un Template facultatif.

Le frontend et le Visual Builder utilisent le même renderer que `[seed_directory]`. L’aperçu est servi par une route REST privée qui exige un nonce valide et `edit_pages`, ne met pas sa réponse en cache public et ne permet aucun accès supplémentaire aux fiches.

Une fiche non listée, brouillon, privée, protégée ou non autorisée reste absente même si son ID est saisi dans le module. Le fallback Template reste local à chaque carte.

Le module est chargé uniquement lorsque Divi 5 est disponible. Sans Divi, le plugin et les shortcodes fonctionnent normalement. Le Loop Builder natif consomme la même Collection Annuaire via les providers `loop_wpsck_directory_*`, sans remplacer le module de Collection historique.

### Annuaire RC.3

La combinaison `ids` et `exclude_ids` est contractuelle : les exclusions sont soustraites de la sélection explicite avant éligibilité, filtres, tri, offset et limite. Une sélection entièrement exclue reste vide. Aucun changement de données ni migration n'est requis depuis 0.8.0-rc.2.
