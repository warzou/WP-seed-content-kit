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
- premier consommateur migré dans `includes/modules/quotes/template-data.php` via `wp_seed_content_get_quote_data()` ;
- aucun shortcode migré ;
- aucun renderer migré ;
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

Le premier consommateur migré est `includes/modules/quotes/template-data.php`. Les lectures directes de `_seed_quote_text`, `_seed_quote_author`, `_seed_quote_era` et `_seed_quote_source` y ont été remplacées par `wp_seed_content_get_quote_data()`.

Le contrat public reste inchangé : les placeholders `{{quote}}`, `{{author}}`, `{{era}}` et `{{source}}` sont conservés dans le même ordre. `quote` reste de type `textarea` ; `author`, `era` et `source` restent de type `text`. Si l'API ne retourne aucune donnée exploitable, les quatre placeholders restent présents avec des chaînes vides.

Les contenus publiés sont accessibles par défaut. Un contenu non publié exige `allow_unpublished=true`, un utilisateur authentifié et la capacité `edit_post` sur le contenu. Avec cette option explicite et cette capacité, un contenu en corbeille reste lisible dans ce premier lot ; ce comportement est connu et accepté.

Les shortcodes, renderers, templates et fallbacks restent inchangés. La migration du premier consommateur a été validée en runtime sur `emilieaucoeurdeletre.fr` : les rendus sans template, avec template natif et avec Layout Divi sont strictement identiques avant et après migration par SHA256.

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

## 12. Prochain chantier autorisé

Le prochain consommateur devra être migré dans un micro-lot séparé, sans modifier les contrats publics ni étendre le périmètre de l'API.
