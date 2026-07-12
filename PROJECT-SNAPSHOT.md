# Project Snapshot - WP Seed Content Kit

Date : 12 juillet 2026
Statut : version stable publiée, chantier documentaire Content Data API V1
Version stable : 0.2.33
Commit stable : c64e1f2
Tag stable : v0.2.33

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
- `{{text}}`.

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

Le contrat public reste strictement inchangé, dans le même ordre et avec les mêmes types : `{{photo}}`, `{{name}}`, `{{photo_url}}`, `{{text}}` et `{{photo_alt}}`. `{{photo}}` reste généré avec `get_the_post_thumbnail()` en taille `thumbnail`, avec la classe `seed-testimonials__photo-image`, le lazy loading et les attributs WordPress natifs. `{{photo_url}}` continue d'utiliser `wp_get_attachment_url()`. `{{photo_alt}}` utilise l'alt normalisé puis retombe sur `name` ; ce fallback reste dans la couche de présentation. Si l'API échoue, les cinq placeholders restent présents avec des chaînes vides.

Le rendu natif Témoignages utilise désormais `wp_seed_content_get_testimonial_data()` dans `includes/modules/testimonials/render.php`. Les lectures directes stables de `_seed_testimonial_name`, `_seed_testimonial_text` et `_seed_testimonial_context` ont disparu. La photo repose sur `photo.id`, tandis que son HTML reste généré par `get_the_post_thumbnail()`.

`_seed_testimonial_date` reste une compatibilité historique transitoire lue uniquement lorsque l'API a résolu un Témoignage valide. `wp_seed_content_format_date()` et toute la présentation restent dans `render.php` : HTML, classes, photo, échappement, footer et ordre nom, contexte, date.

Aucun shortcode, requête, filtre, template ou CSS n'a été modifié. La validation runtime sur `emilieaucoeurdeletre.fr` confirme par SHA256 des collections, filtres, ordres manuels et templates natifs strictement identiques. Les quatre consommateurs unitaires principaux utilisent désormais la Content Data API : `quotes/template-data.php`, `quotes/render.php`, `testimonials/template-data.php` et `testimonials/render.php`. Aucun scénario runtime Layout Divi Témoignages dédié n'est encore disponible.

Elle ne créera ni API générique de collections, ni abstraction inter-plugin, ni registre central WP Seed.

Les modules Citation et Témoignage sont normalisés parce qu'ils sont actuellement intégrés à Content Kit. Cette décision ne fixe pas leur propriété métier à long terme.

## 11. Compatibilités à protéger

- shortcodes publics inchangés ;
- placeholders existants inchangés ;
- comportement fallback inchangé ;
- templates existants inchangés ;
- `context` de `[seed_testimonials]` conservé ;
- lecture historique de `_seed_testimonial_date` conservée tant que les données existantes ne sont pas vérifiées ;
- aucune suppression automatique d'ancienne méta ;
- aucune migration globale ;
- visibilité publique des CPT inchangée dans le chantier API ;
- `seed_template` reste non public ;
- Cards reste hors Content Data API V1 ;
- Plugin Update Checker et packaging inchangés.

## 12. Dynamic Data V1

Le contrat de conception est défini dans :

- `docs/DYNAMIC-DATA.md`.

Aucune implémentation PHP Dynamic Data n'existe encore.

Le registre V1 comprendra exactement douze champs :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `quote.featured` ;
- `quote.display_order` ;
- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.photo` ;
- `testimonial.featured` ;
- `testimonial.display_order`.

Le registre sera descriptif et sans callback. Le résolveur consommera uniquement la Content Data API et ne produira aucun HTML. `testimonial.photo` restera un objet média structuré. Les données WordPress natives ne seront pas dupliquées.

Un ID explicite fourni sera prioritaire et autoritaire. S'il est invalide, inexistant, incompatible ou inaccessible, le résolveur retournera la valeur vide du champ sans fallback vers le contexte du provider ou le contenu WordPress courant.

Dynamic Data restera séparé des collections et des boucles. Les providers Gutenberg, Divi, Spectra, Elementor et autres builders resteront des lots futurs indépendants.

Le premier lot PHP sera limité à :

- registre descriptif ;
- résolveur commun ;
- chargement global après la Content Data API et avant tout provider ;
- tests directs du registre et du résolveur.

Aucun provider ne fera partie de ce premier lot.

## 13. Prochain chantier autorisé

Le prochain jalon envisagé est l'implémentation du registre et du résolveur Dynamic Data V1 dans le périmètre strict défini ci-dessus, sans modifier les contrats publics ni commencer un provider builder.
