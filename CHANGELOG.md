# Changelog

All notable changes to WP-seed-content-kit will be documented in this file.

## [Unreleased]

### Added

- Lot B - Modèle Témoignage : champs Date du témoignage et Information complémentaire, avec validation calendaire stricte et conservation des données historiques.
- Lot B - Alignement de Content Data, Dynamic Data, Gutenberg Block Bindings, Divi 5 Dynamic Content et des Templates : ajout de la date et maintien de l'Information complémentaire via `testimonial.context`, `{{date}}` et `{{context}}`.
- Lot C - API Collections V1 pour les Témoignages : sélection par IDs, filtres featured, quatre tris stables et limites normalisées.
- Lot C - Citation quotidienne déterministe, exclusion des contenus protégés par mot de passe et harnais Collections versionné.
- Lot D - Adaptateur CSV `ids`, tris Collections, `featured=all|only|exclude` et `limit="0"` pour `[seed_testimonials]`.
- Lot D - Mode déterministe `[seed_quotes mode="daily"]`, compatible avec les Templates WP Seed et les layouts Divi Library.
- Lot D - Harnais versionné des adaptateurs shortcodes, Templates et rendus builders indirects.

### Changed

- Le shortcode Témoignages sélectionne désormais ses contenus via Collections V1 tout en conservant ses valeurs par défaut historiques.
- Les Témoignages protégés par mot de passe sont exclus de Collections, y compris avec des IDs explicites ; les Citations protégées sont exclues du mode quotidien et du chemin aléatoire historique.
- Un Template Témoignages introuvable ou associé au mauvais module utilise explicitement le renderer natif, sans laisser de placeholder incompatible brut.

### Compatibility

- `[seed_testimonials]` reste limité à trois éléments et trié par `date DESC` par défaut.
- `[seed_quotes]` reste aléatoire par défaut ; `daily` est uniquement activé par un attribut explicite.
- Le markup des renderers reste identique pour une même liste d'IDs. La sélection diffère volontairement pour les contenus protégés, les anciennes valeurs `_seed_featured=0` et les nouveaux parcours `ids`, `daily` ou `limit="0"`.
- Aucun CPT ni numéro de version n'est modifié ; le lot B ajoute la Date du témoignage aux providers concernés et étend les Templates avec `{{date}}` et `{{context}}`.

## [0.3.0] - 2026-07-15

### Added

- Content Data API normalisée pour les Citations et les Témoignages, avec un objet média minimal.
- Registre Dynamic Data de 12 champs et résolveur strict avec contextes explicites ou courants, permissions et valeurs vides typées.
- Provider serveur Gutenberg Block Bindings pour `core/paragraph.content` et `core/heading.content`.
- Provider expérimental Divi 5 Dynamic Content avec quatre champs Citation : Texte, Auteur, Époque et Source.
- Provider expérimental Divi 5 Dynamic Content avec quatre champs Témoignage : Texte, Nom, Contexte et Photo.

### Changed

- Les rendus et placeholders Citation/Témoignage existants consomment désormais la Content Data API sans changer leur contrat public.
- La version minimale prise en charge est WordPress 6.5 avec PHP 7.0.

### Compatibility

- Les shortcodes, Templates WP Seed, placeholders et layouts Divi Library restent compatibles.
- Le provider Gutenberg est serveur uniquement ; l'interface éditeur native reste différée.
- Le provider Dynamic Content cible Divi 5 uniquement et a été validé au frontend sous Divi 5.9.0.
- Divi 4 n'est pas pris en charge par ce provider.
- L'aperçu Visual Builder reste incomplet pour certaines images et boucles ; le texte alternatif des images n'est pas garanti dans tous les modules.

## [0.1.0] - 2026-06-12

### Added

- Initial WordPress plugin skeleton.
- Testimonials module with CPT `seed_testimonial`.
- Native testimonial meta boxes.
- `[seed_testimonials]` shortcode.
- Cards module for native WordPress posts.
- `[seed_cards]` shortcode.
- Scoped CSS with `seed-` prefix.
- Minimal plugin documentation.
- Manual ZIP packaging workflow.

### Notes

- ACF is not required.
- Composer is not required.
- npm is not required.
- No GitHub update mechanism is included in this version.
- V1.1 will target GitHub Releases updates from WordPress admin.
