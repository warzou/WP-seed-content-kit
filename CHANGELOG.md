# Changelog

All notable changes to WP-seed-content-kit will be documented in this file.

## [0.6.0-dev] - Unreleased

### Added

- Module Annuaire natif avec CPT privé seed_directory, sans page individuelle, archive, recherche publique, REST ou sitemap.
- Dix-neuf métas métier privées, sanitation centralisée et quatre panneaux administratifs au maximum.
- Autorisation explicite de publication, validation photo/alt et garde de publication avant et après écriture.
- Prédicat canonique wp_seed_content_directory_is_publicly_eligible() et extraction filtrée des seuls contacts valides et visibles.
- Vue interne protégée, colonnes administratives sans contact privé, notices consolidées et révisions natives des champs WordPress.
- Quatre capacités primitives Annuaire accordées uniquement au rôle administrator.

### Lifecycle

- La désactivation du module retire son CPT et son menu sans supprimer fiches, médias, métas ou capacités.
- La réactivation retrouve les données existantes.
- L4 fournit la Data API publique, les Collections, [seed_directory], son alias temporaire, la carte native responsive et les cartes par Template Content Kit.
- Le fallback est local a chaque fiche ; les contacts masques ou invalides ne quittent jamais la couche privee.
- Aucune recherche publique, page individuelle, archive, route REST/AJAX, migration runtime ou adaptateur inter-plugin.
### Compatibility

- Citations, Témoignages, Templates, Collections, Gutenberg, Divi et le Template Extension Contract 1.0 restent inchangés.
## [0.5.0-dev] - Unreleased

### Added

- Template Extension Contract 1.0 avec détection de capacités, registre public de modules et placeholders tiers et résultat de rendu typé.
- API publique `wp_seed_content_kit_render_template()` avec validation du slug, du module et du contexte.
- Types fermés, échappement contextuel, garde de récursion, assets par handles WordPress et cache de résolution limité à la requête.
- Harnais autonomes et WordPress pour un module tiers neutre, sans dépendance à un builder.

### Compatibility

- Les shortcodes, placeholders, renderers et fallbacks Témoignages/Citations conservent leurs parcours 0.4.0.
- Gutenberg utilise toujours le rendu serveur ; Divi conserve le workflow Layout Divi Library et reste facultatif.
- Aucun endpoint, CPT, stockage métier ou migration n'est ajouté.

## [0.4.0] - 2026-07-17

### Added

- Champs Date du témoignage et Information complémentaire dans le modèle Témoignage, avec validation calendaire stricte.
- Champ Dynamic Data `testimonial.testimonial_date`, disponible dans Gutenberg Block Bindings et Divi 5 Dynamic Content.
- Placeholders Témoignage `{{date}}` et `{{context}}` pour les Templates natifs et les layouts Divi Library.
- API Collections V1 avec `wp_seed_content_get_testimonials()` et `wp_seed_content_get_daily_quote()`.
- Sélection manuelle des Témoignages par IDs, filtres `featured=all|only|exclude`, quatre tris stables et limites normalisées.
- Tri `testimonial_date` fondé sur la date métier du Témoignage.
- Mode déterministe `[seed_quotes mode="daily"]`, stable pour la date et le fuseau WordPress du site.
- Nouveaux attributs Collections pour `[seed_testimonials]` : `ids`, `featured`, `orderby`, `order` et `limit="0"`.
- Harnais versionnés Collections et Adaptateurs couvrant les APIs, shortcodes, Templates et parcours builders indirects.

### Changed

- `[seed_testimonials]` sélectionne désormais ses contenus via Collections V1 tout en conservant ses valeurs par défaut historiques.
- Le libellé éditorial Contexte devient Information complémentaire sans changer l'identifiant métier `testimonial.context` ni la méta existante.
- Un Template introuvable ou associé au mauvais module revient explicitement au renderer natif.
- La documentation décrit les contrats Collections, les nouveaux attributs shortcode et les recettes Gutenberg, Spectra et Divi.

### Fixed

- Les dates historiques invalides restent intactes en stockage mais ne sont plus exposées comme des dates métier valides.
- `context="0"` conserve le comportement historique sans activer de filtre.
- Les tris utilisent un départage stable par ID et les fallbacks de Template ne laissent aucun placeholder incompatible brut.

### Security

- Les contenus protégés par mot de passe sont exclus des Collections, des sélections par IDs, de la Citation quotidienne et du chemin aléatoire historique.

### Compatibility

- `[seed_testimonials]` reste limité à trois éléments et trié par `date DESC` par défaut.
- `[seed_quotes]` reste aléatoire par défaut ; `daily` est uniquement activé par un attribut explicite.
- Le markup des renderers reste identique pour une même liste d'IDs. La sélection diffère volontairement pour les contenus protégés, les anciennes valeurs `_seed_featured=0` et les nouveaux parcours `ids`, `daily` ou `limit="0"`.
- Aucun CPT, shortcode public existant, provider de collection ou contrat de Template n'est supprimé.

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
