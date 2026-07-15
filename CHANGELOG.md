# Changelog

All notable changes to WP-seed-content-kit will be documented in this file.

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
