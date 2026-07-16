# Project Snapshot - WP Seed Content Kit

Date : 15 juillet 2026
Statut : version 0.3.0 publiée ; lot B Modèle Témoignage implémenté localement et en attente de revue du diff
Version courante : 0.3.0
Version stable publiée : 0.3.0
Commit stable : 650d0ed4af8554f620d97d1a91e62d6848b418ef
Tag stable : v0.3.0
WordPress minimum : 6.5
PHP minimum : 7.0

Ce document est la mémoire de reprise du dépôt WP Seed Content Kit. Le dépôt Git et le code committé priment en cas de contradiction technique.

## 1. Mission actuelle

WP Seed Content Kit fournit des contenus éditoriaux structurés et des présentations réutilisables dans WordPress.

Il combine actuellement :

- des modules de contenus structurés ;
- des shortcodes universels ;
- des templates réutilisables ;
- un moteur de placeholders ;
- un rendu natif ;
- une source de rendu Divi Library ;
- une administration compatible avec les usages WordPress courants.

Le plugin ne doit pas devenir un builder, un thème ou le registre central de l'écosystème WP Seed.

## 2. Référence d'écosystème

La charte canonique commune se trouve dans le dépôt racine `wp-seed` :

- `ECOSYSTEM-ARCHITECTURE.md`

Principes applicables à Content Kit :

- chaque plugin métier reste autonome ;
- Content Kit ne devient pas une dépendance obligatoire ;
- Content Kit ne devient pas le propriétaire central des métiers WP Seed ;
- les intégrations entre plugins restent optionnelles et explicites ;
- la présentation reste séparée des données métier.

Le présent snapshot reste autosuffisant pour reprendre Content Kit. Il ne copie pas intégralement les chartes d'écosystème.

## 3. État fonctionnel stable

### Modules fonctionnels

Témoignages :

- CPT `seed_testimonial` ;
- module activable, actif par défaut ;
- nom ou initiales ;
- texte ;
- photo via l'image mise en avant WordPress ;
- mise en avant ;
- ordre manuel ;
- rendu fallback ;
- templates et placeholders.

Citations :

- CPT `seed_quote` ;
- module activable, actif par défaut ;
- citation ;
- auteur facultatif ;
- époque ou date affichée libre ;
- source ou contexte ;
- mise en avant ;
- ordre manuel ;
- rendu fallback ;
- templates et placeholders.

Cards :

- rendu d'articles WordPress natifs ;
- aucun CPT métier propre ;
- hors du périmètre Content Data API V1.

### Modules prévus

Les modules suivants restent annoncés mais non fonctionnels :

- Annuaire ;
- Créations sonores.

Ils ne possèdent pas encore de contrat métier stable.

## 4. Templates WP Seed

Le CPT `seed_template` est fonctionnel.

Il est :

- non public ;
- non consultable comme page publique ;
- disponible dans l'administration ;
- compatible avec l'éditeur WordPress grâce à `show_in_rest` ;
- identifié par son `post_name`, présenté à l'utilisateur comme Identifiant.

Un template choisit une source du rendu :

- Contenu de ce template ;
- Layout Divi Library.

Le rendu natif utilise le contenu du template.

Le rendu Divi Library :

- référence un contenu `et_pb_layout` publié ;
- remplace les placeholders WP Seed ;
- rend les blocs Divi ;
- conserve le contenu natif du template comme fallback.

L'édition directe de `seed_template` avec Divi a été supprimée. Le workflow Divi validé est :

Template WP Seed -> Layout Divi Library -> rendu par shortcode

L'UX Templates comprend notamment :

- réglages du module, de l'identifiant et de la source du rendu ;
- sélection et accès aux layouts Divi ;
- métabox basse « Comment utiliser ce template » ;
- shortcode copiable ;
- placeholders copiables ;
- exemple de template ;
- cockpit Templates avec contenus récents et aide constructeur.

## 5. Shortcodes publics

Les shortcodes publics actuels sont :

- `[seed_cards]` ;
- `[seed_testimonials]` ;
- `[seed_quotes]`.

Les attributs publics existants, notamment `template`, `featured`, `limit`, `orderby`, `order`, `columns` et `context` selon le module, doivent rester compatibles.

Les shortcodes restent responsables :

- des requêtes ;
- des filtres ;
- des limites ;
- des tris ;
- du choix des contenus ;
- du template demandé.

## 6. Placeholders actuels

Témoignages :

- `{{photo}}` ;
- `{{photo_url}}` ;
- `{{photo_alt}}` ;
- `{{name}}` ;
- `{{text}}` ;
- `{{context}}` ;
- `{{date}}`.

Citations :

- `{{quote}}` ;
- `{{author}}` ;
- `{{era}}` ;
- `{{source}}`.

Les placeholders restent une couche de présentation. Ils ne constituent pas le stockage métier.

## 7. Builders

Compatibilités actuelles :

- Gutenberg : édition native des templates ;
- Spectra : compatibilité via Gutenberg ;
- Divi : layouts Divi Library comme source de rendu ;
- Elementor : aide de compatibilité dans l'administration, sans intégration de rendu dédiée.

Content Kit ne fournit ni module Divi personnalisé, ni widget Elementor, ni bloc Gutenberg propriétaire.

## 8. Administration

L'administration comprend :

- activation des modules fonctionnels ;
- visibilité optionnelle des modules dans le menu WordPress ;
- rôles autorisés ;
- générateurs de shortcodes ;
- gestion des templates ;
- ordre manuel basé sur le champ WordPress natif `menu_order` ;
- opt-out de l'interface Yoast pour les contenus structurés concernés.

La configuration générale reste réservée aux administrateurs.

## 9. Distribution et mises à jour

Le canal stable repose sur :

- tags Git `vMAJOR.MINOR.PATCH` ;
- GitHub Releases publiques ;
- asset canonique `wp-seed-content-kit.zip` ;
- racine ZIP `wp-seed-content-kit/` ;
- chemins ZIP POSIX ;
- Plugin Update Checker 5.7 embarqué ;
- sélection explicite de l'asset `wp-seed-content-kit.zip`.

Les contrôles de release obligatoires incluent :

- absence de chemins ZIP avec antislash ;
- présence du fichier principal ;
- extraction réelle ;
- vérification du SHA256 local et public.

Ne jamais modifier Plugin Update Checker, le nom de l'asset ou le workflow de release dans un lot sans rapport.

La release 0.3.0 a été publiée depuis le commit candidat validé. Les prochaines releases doivent conserver la même règle : recette du ZIP construit depuis le commit exact avant tout tag ou publication.

## 10. Content Data API V1

La conception métier est documentée dans :

- `docs/CONTENT-DATA-API.md`

Statut actuel :

- contrat documentaire validé ;
- premier socle PHP implémenté dans `includes/core/content-data.php` ;
- chargement global immédiatement après `core/helpers.php`, indépendamment de l'activation des modules ;
- deux consommateurs Citations migrés via `wp_seed_content_get_quote_data()` :
  - `includes/modules/quotes/template-data.php` ;
  - `includes/modules/quotes/render.php` ;
- deux consommateurs Témoignages migrés via `wp_seed_content_get_testimonial_data()` :
  - `includes/modules/testimonials/template-data.php` ;
  - `includes/modules/testimonials/render.php` ;
- aucun shortcode migré ;
- aucune donnée migrée.

Le périmètre V1 couvre uniquement :

- Citation ;
- Témoignage ;
- objet média minimal.

Fonctions disponibles :

- `wp_seed_content_get_quote_data()` ;
- `wp_seed_content_get_testimonial_data()` ;
- `wp_seed_content_get_media_data()`.

L'API normalise les données métier sans produire de HTML, connaître les templates ou dépendre d'un builder.

Les consommateurs migrés sont `includes/modules/quotes/template-data.php` et `includes/modules/quotes/render.php`. Les lectures directes de `_seed_quote_text`, `_seed_quote_author`, `_seed_quote_era` et `_seed_quote_source` y ont été remplacées par `wp_seed_content_get_quote_data()`.

Le contrat public reste inchangé : les placeholders `{{quote}}`, `{{author}}`, `{{era}}` et `{{source}}` sont conservés dans le même ordre. `quote` reste de type `textarea` ; `author`, `era` et `source` restent de type `text`. Si l'API ne retourne aucune donnée exploitable, les quatre placeholders restent présents avec des chaînes vides.

Les contenus publiés sont accessibles par défaut. Un contenu non publié exige `allow_unpublished=true`, un utilisateur authentifié et la capacité `edit_post` sur le contenu. Avec cette option explicite et cette capacité, un contenu en corbeille reste lisible dans ce premier lot ; ce comportement est connu et accepté.

Le rendu natif Citations conserve dans `render.php` toute la logique de présentation : HTML, classes CSS, échappement, `nl2br()`, conditions d'affichage et message « Citation vide. ». Les identifiants invalides, brouillons et mauvais CPT produisent désormais cette carte vide, conformément au contrat supporté.

Aucun shortcode, requête, template ou CSS n'a été modifié. La validation runtime sur `emilieaucoeurdeletre.fr` confirme par SHA256 des rendus strictement identiques pour les shortcodes, collections, tris, filtres featured, templates natifs et Layouts Divi.

Les placeholders Témoignages utilisent désormais `wp_seed_content_get_testimonial_data()` dans `includes/modules/testimonials/template-data.php`. Les lectures directes de `_seed_testimonial_name`, `_seed_testimonial_text`, `_wp_attachment_image_alt` et `_thumbnail_id` ont disparu de ce consommateur.

Les cinq placeholders historiques restent dans le même ordre et avec les mêmes types : `{{photo}}`, `{{name}}`, `{{photo_url}}`, `{{text}}` et `{{photo_alt}}`. Le lot B ajoute ensuite `{{context}}` et `{{date}}`. `{{date}}` est une projection localisée de la date ISO ; `{{context}}` conserve l'identifiant historique. `{{photo}}` reste généré avec `get_the_post_thumbnail()` en taille `thumbnail`, avec la classe `seed-testimonials__photo-image`, le lazy loading et les attributs WordPress natifs.

Le rendu natif Témoignages utilise désormais `wp_seed_content_get_testimonial_data()` dans `includes/modules/testimonials/render.php`. Les lectures directes stables de `_seed_testimonial_name`, `_seed_testimonial_text` et `_seed_testimonial_context` ont disparu. La photo repose sur `photo.id`, tandis que son HTML reste généré par `get_the_post_thumbnail()`.

`_seed_testimonial_date` alimente désormais `testimonial_date` dans la Content Data API après validation calendaire stricte. Une valeur historique invalide reste intacte en base et devient `''` en lecture. À la sauvegarde, un champ absent préserve la méta, une chaîne exactement vide la supprime même si elle était invalide, une valeur non vide invalide préserve l'ancienne méta sans `trim()`, et une date valide au format exact `YYYY-MM-DD` la remplace. `wp_seed_content_format_date()` et toute la présentation restent hors de l'API : HTML, classes, photo, échappement, footer et ordre nom, contexte, date.

Aucun shortcode, requête, filtre, template ou CSS n'a été modifié. La validation runtime sur `emilieaucoeurdeletre.fr` confirme par SHA256 des collections, filtres, ordres manuels et templates natifs strictement identiques. Les quatre consommateurs unitaires principaux utilisent désormais la Content Data API : `quotes/template-data.php`, `quotes/render.php`, `testimonials/template-data.php` et `testimonials/render.php`. Aucun scénario runtime Layout Divi Témoignages dédié n'est encore disponible.

Elle ne créera ni API générique de collections, ni abstraction inter-plugin, ni registre central WP Seed.

Les modules Citation et Témoignage sont normalisés parce qu'ils sont actuellement intégrés à Content Kit. Cette décision ne fixe pas leur propriété métier à long terme.

## 11. Compatibilités à protéger

- shortcodes publics inchangés ;
- placeholders existants inchangés ;
- comportement fallback inchangé ;
- templates existants inchangés ;
- `context` de `[seed_testimonials]` conservé ;
- lecture historique de `_seed_testimonial_date` conservée sans migration ni réécriture automatique ;
- aucune suppression automatique d'ancienne méta ;
- aucune migration globale ;
- visibilité publique des CPT inchangée dans le chantier API ;
- `seed_template` reste non public ;
- Cards reste hors Content Data API V1 ;
- Plugin Update Checker et packaging inchangés.

## 12. Dynamic Data V1

Le contrat de conception est défini dans :

- `docs/DYNAMIC-DATA.md`.

Le socle PHP Dynamic Data V1 est implémenté dans :

- `plugin/includes/core/dynamic-data.php`.

Il est chargé globalement après `core/content-data.php` et avant `core/modules.php`, indépendamment des modules actifs et des builders.

Le registre V1 comprend exactement treize champs :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `quote.featured` ;
- `quote.display_order` ;
- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.testimonial_date` ;
- `testimonial.photo` ;
- `testimonial.featured` ;
- `testimonial.display_order`.

Le registre est descriptif et sans callback. Il expose les trois fonctions publiques suivantes :

- `wp_seed_content_get_dynamic_data_fields()` ;
- `wp_seed_content_get_dynamic_data_field()` ;
- `wp_seed_content_resolve_dynamic_data()`.

Le résolveur consomme uniquement la Content Data API : il ne lit aucune méta directement, ne produit aucun HTML et ne traite ni collection ni boucle. Un champ inconnu retourne une `WP_Error`. `testimonial.photo` retourne un objet média structuré ou `null`. Les données WordPress natives ne sont pas dupliquées.

Les IDs explicites et courants fournis dans le contexte sont prioritaires et autoritaires. S'ils sont invalides, inexistants, incompatibles ou inaccessibles, le résolveur retourne la valeur vide typée du champ sans fallback vers un autre contexte. `allow_unpublished` n'est accepté que pour la valeur booléenne stricte `true` ; la Content Data API reste propriétaire du contrôle d'accès.

Dynamic Data reste séparé des collections et des boucles. Le provider serveur Gutenberg Block Bindings et le provider expérimental Divi 5 consomment le résolveur commun. L'état d'activation d'un module ne bloque pas les API unitaires lorsqu'un contenu publié compatible est fourni ; la précondition « module actif » reste limitée à la future sélection de Collections.

La validation runtime du socle a été réalisée sur `emilieaucoeurdeletre.fr` :

- 83 tests sur 83 réussis ;
- résolution unitaire Content Data et Dynamic Data maintenue avec les modules désactivés ;
- aucune régression des shortcodes ni des templates existants.

## 13. Gutenberg Block Bindings V1

Le contrat de conception est défini dans :

- `docs/GUTENBERG-BLOCK-BINDINGS.md`.

Le provider serveur PHP V1 est implémenté dans :

- `plugin/includes/integrations/gutenberg/block-bindings.php`.

Il est chargé globalement après `core/dynamic-data.php` et avant `core/modules.php`, sur toutes les requêtes. Si `register_block_bindings_source()` est absente, l'enregistrement est silencieusement ignoré.

La source publique est `wp-seed-content-kit/dynamic-data`. Elle est enregistrée sur `init` à la priorité 10 par `wp_seed_content_register_gutenberg_block_bindings_source()` et résolue par `wp_seed_content_get_gutenberg_binding_value()`. Elle utilise les contextes `postId` et `postType`.

Le provider texte V1 expose uniquement huit champs, annoncés comme `string` côté Gutenberg :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.testimonial_date`.

Les cibles V1 sont `core/paragraph.content` et `core/heading.content`. Les arguments serveur sont `field_id`, obligatoire, et `post_id`, facultatif et autoritaire. Un `post_id` explicite ne retombe jamais sur le contexte courant après un échec.

Un binding mal formé, un bloc ou attribut interdit, un champ hors allowlist ou un `WP_Error` produit `null`. Une chaîne retournée normalement par le résolveur est conservée exactement, y compris `''`. Le provider ne produit aucun HTML, ne transforme pas le texte, ne lit aucune méta directement et n'active jamais `allow_unpublished`.

La validation statique et runtime sous WordPress 7.0.1 confirme :

- source enregistrée une seule fois ;
- Query Loops Citations et Témoignages réussies, avec contextes distincts par élément ;
- `null` conserve le contenu statique et `''` le remplace par une valeur vide ;
- textes multilignes, Unicode et HTML historique validés ;
- provider disponible et contenu publié compatible résoluble avec les modules désactivés ;
- brouillons non exposés ;
- aucune régression des shortcodes ni des templates ;
- aucun warning ou fatal WP Seed.

Le provider reste un socle interne sans interface éditeur terminée. WordPress 6.5 n'a pas été testé en runtime ; sa compatibilité repose sur l'API officielle et la revue statique. Aucun JavaScript éditeur n'est implémenté. `testimonial.photo`, les champs `featured` et `display_order`, Spectra, Divi et Elementor restent hors de ce provider. `testimonial.testimonial_date` retourne sa valeur ISO brute. Les Templates WP Seed et les Block Bindings restent deux workflows complémentaires.

L'audit de l'intégration éditeur a confirmé les capacités publiques de `registerBlockBindingsSource`, `getFieldsList` et `getValues` disponibles à partir de WordPress 6.9, ainsi que les contextes `postId` et `postType` d'une Query Loop. Il a aussi confirmé que le filtrage natif observé reste global par type d'attribut : aucune API publique identifiée ne permet de limiter précisément les champs WP Seed à la source, au bloc et à l'attribut autorisés.

Le verdict initial de l'audit était `NEEDS HUMAN DECISION`. La décision humaine est de différer le JavaScript éditeur natif avec l'API publique actuelle. Aucune inscription éditeur par `getFieldsList` ou `getValues`, aucun endpoint REST et aucun JavaScript éditeur ne sont implémentés. Aucun contournement fondé sur une API interne et aucune modification des filtres globaux de WordPress ne sont retenus. Le provider serveur reste officiel, actif, validé sous WordPress 7.0.1, utilisable avec un markup contrôlé et au rendu frontend d'une Query Loop. Il constitue toujours la fondation publique du contrat Gutenberg Block Bindings.

Un futur réaudit éditeur ne sera engagé que si WordPress fournit un filtrage public suffisamment précis, une solution native officiellement supportée, ou si un besoin produit important justifie l'étude séparée d'une interface WP Seed dédiée.

## 14. Périmètre Divi 5 expérimental

Le provider expérimental Divi 5 Dynamic Content expose les quatre champs texte Citation, les quatre champs texte Témoignage et la photo du Témoignage avec l'architecture class-based fournie par Divi 5.

- sources Citation : `wp_seed_content_quote_quote`, `wp_seed_content_quote_author`, `wp_seed_content_quote_era` et `wp_seed_content_quote_source` ;
- base abstraite limitée aux Citations : `WP_Seed_Content_Divi_Dynamic_Content_Quote_Base` ;
- quatre classes concrètes distinctes pour Texte, Auteur, Époque et Source ;
- sources Témoignage : `wp_seed_content_testimonial_text`, `wp_seed_content_testimonial_name`, `wp_seed_content_testimonial_context`, `wp_seed_content_testimonial_date` et `wp_seed_content_testimonial_photo` ;
- base abstraite limitée aux Témoignages : `WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base` ;
- quatre classes texte concrètes pour Texte, Nom, Information complémentaire et Date du témoignage, plus une classe Photo indépendante projetant uniquement `testimonial.photo.url` ;
- bootstrap : `plugin/includes/integrations/divi/dynamic-content.php` ;
- chargeurs Citation et Témoignage indépendants, chacun fondé sur une liste fermée ;
- chargement sur `init`, priorité 10 ;
- appel unique à `load()` pour chaque source ;
- aucune inscription procédurale manuelle des filtres Divi ;
- architecture validée côté serveur et dans le Visual Builder sous Divi 5.9.0 ;
- single `seed_quote` et `seed_testimonial` fonctionnels ;
- contextes de boucle serveur fonctionnels avec des valeurs distinctes ;
- `loop_id => null` hors boucle corrigé par recours à `post_id` ;
- `loop_id` non nul autoritaire, sans fallback en cas d'invalidité ;
- neuf options WP Seed uniques côté serveur ;
- sélection, application, sauvegarde et réouverture visuelles validées pour les huit options antérieures ;
- frontend Theme Builder en contexte `seed_quote` validé pour les quatre valeurs ;
- frontend `seed_testimonial` validé pour Texte, Nom et Information complémentaire, y compris chaîne vide, multiligne, Unicode et HTML historique ;
- persistance brute unique et reconnaissance visuelle validées pour les quatre options Témoignage antérieures ; la Date du témoignage reste à valider visuellement ;
- source Photo de type `image`, sans source dérivée, tableau média ou HTML transmis à Divi ;
- reconstruction par Divi des IDs média, dimensions, `srcset` et `sizes` pour les pièces jointes locales testées ; alt retrouvé sur le single mais non garanti dans la boucle ;
- rendu frontend Photo validé en single et en boucle, sans variable brute ni chaîne `Array` ;
- aperçu Image du Visual Builder encore vide malgré la sélection et la persistance correctes de la source Photo ;
- provider Citation, Gutenberg, shortcodes, templates et placeholders non régressés ;
- statut expérimental maintenu ;
- prévisualisation directe d'un corps Theme Builder sans contexte métier et recette visuelle Loop Builder autonome reportées.

Le provider Gutenberg serveur est inclus depuis la version 0.3.0 ; son interface éditeur native reste différée. Le provider Divi 5 conserve un statut expérimental et les limites visuelles documentées ci-dessus. Les shortcodes publics et les layouts Divi Library restent inchangés ; le lot B étend uniquement les placeholders Témoignage.

Le lot B Modèle Témoignage réactive l'édition de la date civile et de l'Information complémentaire, normalise la date dans Content Data, l'expose dans Dynamic Data, Gutenberg et Divi, et ajoute les placeholders `{{context}}` et `{{date}}`. Il n'ajoute aucune API Collections, ne modifie aucun CPT et ne déclenche aucun changement de version. Tout jalon ultérieur reste soumis à une revue séparée.
