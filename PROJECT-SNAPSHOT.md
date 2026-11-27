# Project Snapshot - WP Seed Content Kit

## Canonical development and release source

The official repository is https://github.com/warzou/WP-seed-content-kit and
the cumulative reference is its `main` branch. The stable 0.9.0 release
reference is the commit resolved by `git rev-parse 'v0.9.0^{commit}'`. Start
new work from a freshly fetched `origin/main`. An approved change is not
finished while it exists only in a local worktree: record it in Git and
integrate it into the official branch. Do not reset other worktrees to do so.
See `docs/CONSOLIDATED-RELEASE-GIT.md` for tests, build, distribution and
release safety. Keep site data, credentials and private validation artifacts
outside Git.

## Cumulative release review (2026-11-27)

The isolated `codex/consolidated-release` checkout starts from official
`v0.8.1` (`889afc0`) and consolidates the validated Directory, Formation
Simple Calendar, and module discovery changes from the Directory worktree.
The admin thumbnail fix is integrated as targeted hunks. Candidate version:
`0.9.0-rc.2`, not published. See `docs/CONSOLIDATED-RELEASE.md` for scope,
MariaDB validation and Human Pass, and `docs/CONSOLIDATED-RELEASE-GIT.md`
for the exact state and publication plan. The sections below describe historical
checkpoints, not the current DEV manifest.

## Directory 3201 removed without dependency (2026-10-06)

New explicit authorization supersedes previous preservation/browser-stop decisions.
Normal wp_delete_post(3201,true) removed the draft, 49 revisions and 73 metadata
rows. Verified temporary private database/resource backup preceded deletion and
was removed with the helper after final technical validation. Native hooks stayed
enabled; no SQL deletion, global restoration or manual cache purge.
Fresh SQL comparison: no other post/postmeta or business/taxonomy/preset change.
One Divi Global Feature cache option and four transient rows individually qualified;
six native derived files added for diagnostic HTTP/404 requests. No UNKNOWN delta.
1896/1897 content unchanged; fresh HTTP200, three Directory loops and twelve cards.
Before/fresh loop DOM hashes and all six checked CSS resources exactly match.
3201 HTTP404, no remaining revisions or orphan metadata. No product fix required.
PHP7/8: NativeLoop282, More20, Bindings91, L3/providers209, L4/Data68 PASS each;
151 PHP files lint PASS each, three JS assets syntax PASS, index empty.
Technical PASS; HUMAN VISUAL CHECK PENDING (Computer Use unavailable, no screenshot
or viewport/overflow PASS claimed). Pixel/6354, Events, Formation untouched.
Evidence: ../intervenants-display-audit-20261005/REMOVE3201-FINAL-REPORT.md.
No commit/push/tag/release. Earlier sections below describe their historical state.

## Directory administrative follow-up (2026-10-06)

Approved scoped follow-up implemented and deployed: new intervenant menu_order
is max(existing intervenants)+10 (empty population: 10), respecting explicit
manual positions and existing records; country optional; historical summary
DEPRECATED in admin/write paths, existing post_excerpt and read contracts preserved.
Directory now owns initial allocation; shared manual-order automatic allocation
and repair defer only for Directory. Manual moves and other CPTs are unchanged.
An initial transactional WordPress test exposed the shared allocator conflict;
the transaction rolled back exactly, the conflict was corrected and retested.
Final real WordPress fixture harness: 13 assertions PASS, transaction rolled back.
Post-shutdown raw SQL check: posts/postmeta/presets exactly match the fresh baseline.
DEV runtime: 251 files exact; HTTP frontend/editor asset: 200/200.
PHP 7.0/8.4 targeted tests pass; no full-suite claim for this lot.
Option 2/1897, 1896/3201, real people, Formation, Events, Pixel and 6354 unchanged.
3201 remains a draft with 49 revisions. 2249 is a historical recipe consumer.
No fixture, page or preset persists from this lot. No commit/push/tag/release.
Closure resumed after PC interruption: fresh HTTP 200 / 12 cards, raw SQL and
presets exactly unchanged; runtime/source 251 files exact; Pixel/Events manifest
unchanged. Targeted local tests and lint rerun PASS. No redeployment or fixture
recreation. Scoped helpers removed, index empty, prior worktree changes preserved.

## Directory Option 2 final closure (2026-10-06)

Guillaume HUMAN PASS: PASS. Page 1897 is final; no further Directory behavior,
preset, real-person or page change belongs to this closure. Native Directory
Loop data/providers/More remain unchanged. Shared definitive native Divi presets
own common card, portrait and separator decoration; structural WPSCK classes
retain desktop alternation and responsive stacking without per-clone CSS.
11/12/13/20 cards, 11->12->13->12->13 transitions, authenticated/anonymous HTTP,
native editing, Save/Close/Reopen and six responsive widths passed. No manual
purge, clone-specific invalidation or WP-Super-Cache fix was added.
Final read-only SQL qualification: all post fingerprints unchanged since the
technical acceptance; only qualified native Divi cache metadata deltas. Presets,
WPSCK/Events/Pixel runtime, real business metadata, 1896/3201 and 6354 unchanged.
Directory TEMP pages/fixtures/revisions/presets and remote helpers: zero.
The external historical TEMP Formation page 4462 is outside this Directory lot
and remains untouched, as required by its prior preservation decision.
New closure execution helpers removed; useful historical reports and source
evidence retained outside the delivered plugin. Following explicit scoped approval,
48 historical local execution scripts were inventoried, checked for external
execution references, hash-verified and removed. The inventory and historical
evidence remain in the external audit folder. Product code unchanged.
See docs/ANNUAIRE-MODULE.md, section "Option 2". No commit/push/tag/release.
Functional chantier closed with HUMAN PASS; scoped historical-script cleanup completed.

## Current product decision: simple calendar (2026-09-08)

Full Dynamic Calendar, Program/Year discovery, experimental Child Modules and
Annual Planning are abandoned and removed locally. WPSCK owns data and the
normal administrative calendar; Divi owns manually duplicated annual sections.
Schedule multiplicity, explicit filters and individual providers are retained.
See docs/SIMPLE-CALENDAR-DECISION.md. Earlier V4/V5 entries below are historical,
not currently available features. DEV rollback now deployed and verified: 250
files exact, normal Formation transactional tests pass. Following separate
authorization accepting normal Divi invalidation, TEMP drafts 4989/5007/5154,
their 19 revisions and 76 metadata rows were permanently removed. Published
TEMP 4462 is preserved. Full database and wp-content backups were verified.
Final comparison: no other post changes; 16 derived Divi cache rows generated
by frontend checks, explicitly journaled. Historical baselines remain intact.
See docs/SIMPLE-CALENDAR-PROTOTYPES-DELETION-COMPLETE.md. The preceding rollback
in SIMPLE-CALENDAR-FINAL-CLEANUP-REPORT.md remains historical evidence.

Date : 18 août 2026
Statut : 0.8.1 stable ; contrats Testimonials, Annuaire portable et Native Divi Loop validés
Version courante du code : 0.8.1
Version stable publiee de reference : 0.8.1
Commit de base de preparation stable : commit portant le tag v0.8.1
Tag stable publie de reference : v0.8.1
WordPress minimum : 6.5
PHP minimum : 7.0

Ce document est la mémoire de reprise du dépôt WP Seed Content Kit. Le dépôt Git et le code committé priment en cas de contradiction technique.

## 1. Mission actuelle

WP Seed Content Kit fournit des contenus éditoriaux structurés et des présentations réutilisables dans WordPress.
### Annuaire L4

Le module directory comprend désormais :

- CPT privé seed_directory, hors REST, recherche, archive, single et sitemap ;
- vingt-deux métas métier privées avec sanitation centralisée et pays par défaut FR ;
- cinq panneaux admin, colonnes sans données privées et capacités de contenu accordées par défaut à administrator et editor ;
- autorisation explicite, validation photo/alt et garde de publication avant/après écriture ;
- prédicat canonique d’éligibilité et séparation stricte des contacts visibles ;
- révisions natives et cycle de désactivation/réactivation non destructif.

L4 ajoute une Data API publique fermee, des Collections par IDs, [seed_directory], un alias temporaire, deux groupes fixes, une carte native responsive et des cartes personnalisees par Template Content Kit. La grille semantique neutralise les marqueurs de liste herites des themes et une fiche sans photo commence directement par son contenu, sans placeholder. Le fallback reste local a chaque fiche et ne peut exposer un contact masque. Il ne subsiste aucune recherche publique, single, archive, REST/AJAX, import métier automatique ou adaptateur inter-plugin. La migration de schéma 0.8.0-rc.1 est strictement additive et idempotente. Le depot WP Seed Directory reste strictement separe et inchange.
Il combine actuellement :

- des modules de contenus structurés ;
- des shortcodes universels ;
- des templates réutilisables ;
- un moteur de placeholders ;
- un rendu natif ;
- une source de rendu Divi Library ;
- une administration compatible avec les usages WordPress courants.

Pour les Templates Témoignages fondés sur un Layout Divi Library, le renderer transmet désormais explicitement l'ID de chaque témoignage. `value.post_id` est injecté dans la représentation JSON directe ou sérialisée des cinq variables Dynamic Content autorisées avant le parsing frontend, puis le Layout est parsé et resérialisé uniquement en mémoire. Une pile interne bornée, restaurée dans `finally`, isole les cartes. Un signal attendu/résolu refuse les cartes dynamiques non résolues et laisse le fallback natif prendre le relais sur la seule carte en erreur. Le Layout, ses révisions et ses métadonnées restent inchangés.

Sous Divi 5.9.0, le groupe Dynamic Content « WPSCK — Témoignages » expose douze champs compatibles avec une Native Loop et résolus par item dans le frontend et le Visual Builder. Le contrat inclut le résumé court, le texte complet et les projections WordPress More `intro`, `more` et `has_more`. La condition `WPSCK — Témoignages — Témoignage avec suite` utilise le provider canonique `loop_wpsck_testimonial_has_more`. Les Grid Offset Rules de Divi ciblent seulement les enfants directs du conteneur courant ; elles ne peuvent pas inverser les colonnes internes selon la parité du clone Loop parent. L’alternance utilise donc un CSS structurel opt-in, sans style éditorial. Son owner peut être une Section, une Row ou un Group. Le responsive historique reste média puis contenu ; les classes génériques `wpsck-loop--mobile-content-first` et `wpsck-loop--mobile-media-first` permettent un choix explicite par recette.

Le plugin ne doit pas devenir un builder, un thème ou le registre central de l'écosystème WP Seed.

### Travail local en cours — Parcours & calendrier

Le worktree contient un module optionnel formation, visible sous **Parcours & calendrier** et désactivé par défaut. Il ajoute les fondations Programme, Thème, Section et Programmation annuelle, leurs capacités, l'administration native, les snapshots historiques, les collections HTML-free, Dynamic Data, Block Bindings et un shortcode natif unique. Ce travail n'est ni committé ni publié; docs/PARCOURS-CALENDRIER.md constitue son contrat courant. WP Seed Events reste hors périmètre.

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
- nom ou initiales canonique dans `seed_testimonial_name` ;
- titre WordPress technique synchronisé depuis le nom ;
- résumé court canonique dans `post_excerpt` ;
- texte canonique dans `seed_testimonial_text` avec marqueur WordPress More facultatif ;
- projections texte complet, introduction, suite et `has_more` ;
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

### Module prévu

Créations sonores reste annoncé mais non fonctionnel et ne possède pas encore de contrat métier stable. Annuaire est désormais un module natif en cours d’implémentation par lots.

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

- de la normalisation de leurs attributs publics ;
- de l'appel à l'API Collections lorsqu'un parcours canonique existe ;
- de la conservation explicite des compatibilités historiques ;
- de la transmission des IDs au renderer natif ou au Template demandé ;
- du wrapper de collection et de l'état vide.

Le hasard historique du shortcode Citations reste un parcours distinct, explicitement conservé.

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

Content Kit fournit des modules Divi 5 natifs pour les Collections Témoignages et Annuaire. Il ne fournit ni module Divi 4, ni widget Elementor, ni bloc Gutenberg propriétaire.

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

La Content Data API ne sélectionne aucune collection et ne devient ni une abstraction inter-plugin, ni un registre central WP Seed. La sélection V1 appartient à la couche Collections séparée.

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

Le registre Dynamic Data conserve les champs Citations et Annuaire existants et expose le contrat Témoignages consolidé :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `quote.featured` ;
- `quote.display_order` ;
- `testimonial.title` ;
- `testimonial.summary` ;
- `testimonial.text` ;
- `testimonial.full_content` ;
- `testimonial.intro` ;
- `testimonial.more` ;
- `testimonial.has_more` ;
- `testimonial.id` ;
- `testimonial.anchor` ;
- `testimonial.anchor_url` ;
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

Dynamic Data reste séparé des collections et des boucles. Le provider serveur Gutenberg Block Bindings et le provider expérimental Divi 5 consomment le résolveur commun. L'état d'activation d'un module ne bloque pas les API unitaires lorsqu'un contenu publié compatible est fourni ; la précondition « module actif » reste limitée à la sélection par la couche Collections.

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

Le provider texte expose une allowlist explicite, annoncée comme `string` côté Gutenberg. Pour les Témoignages, elle contient :

- `testimonial.summary` ;
- `testimonial.text` ;
- `testimonial.intro` ;
- `testimonial.more` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.testimonial_date`.

Les quatre champs texte Citation et les champs Annuaire documentés restent disponibles. `testimonial.has_more` demeure un booléen métier hors des bindings texte Core ; Divi le transporte séparément en `1` ou chaîne vide pour sa condition clone-aware.

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
- persistance brute unique et reconnaissance visuelle validées pour les cinq options Témoignage ;
- source Photo de type `image`, sans source dérivée, tableau média ou HTML transmis à Divi ;
- reconstruction par Divi des IDs média, dimensions, `srcset` et `sizes` pour les pièces jointes locales testées ; alt retrouvé sur le single mais non garanti dans tous les modules ;
- rendu frontend Photo validé en single et dans le contexte contrôlé d'un Template avec Layout Divi, sans variable brute ni chaîne `Array` ;
- aperçu Image du Visual Builder encore vide malgré la sélection et la persistance correctes de la source Photo ;
- provider Citation, Gutenberg, shortcodes, templates et placeholders non régressés ;
- statut expérimental maintenu ;
- prévisualisation directe d'un corps Theme Builder sans contexte métier reportée ; Loop Builder natif non pris en charge sous Divi 5.9.0.

Le provider Gutenberg serveur est inclus depuis la version 0.3.0 ; son interface éditeur native reste différée. Le provider Divi 5 conserve un statut expérimental et les limites visuelles documentées ci-dessus. Les shortcodes publics et les layouts Divi Library restent inchangés ; le lot B étend uniquement les placeholders Témoignage.

Le lot B Modèle Témoignage est intégré sur `main`. Il réactive l'édition de la date civile et de l'Information complémentaire, normalise la date dans Content Data, l'expose dans Dynamic Data, Gutenberg et Divi, et ajoute les placeholders `{{context}}` et `{{date}}`. Il n'a ajouté aucune API Collections, modifié aucun CPT ni déclenché de changement de version.

## 15. Collections V1

Le contrat canonique est défini dans :

- `docs/COLLECTIONS.md`.

Le lot C est intégré sur `main` dans :

- `plugin/includes/core/collections.php` ;
- `tests/collections-harness.php`.

Il expose :

- `wp_seed_content_get_testimonials($args = array())`, qui retourne une liste ordonnée d'IDs de Témoignages publiés non protégés ;
- `wp_seed_content_get_daily_quote($args = array())`, qui retourne l'ID quotidien déterministe d'une Citation publiée non protégée ou `0`.

Le harnais Collections valide 95 assertions : arguments, IDs autoritaires, états protégés, featured, quatre tris, limites, modules, formule quotidienne et fuseaux.

Le lot D est intégré sur `main` par le commit `32dac5083491223800d7bf18dccb0a24f8eeaebb`. Il modifie uniquement les adaptateurs publics, les tests et la documentation :

- `[seed_testimonials]` sélectionne désormais ses IDs via Collections ;
- les valeurs historiques par défaut restent trois Témoignages, `date DESC` et un plafond positif de 24 ;
- `limit="0"` signifie explicitement tous les Témoignages éligibles ;
- `ids` accepte une liste CSV ordonnée, dédupliquée et sans fallback ;
- `featured=true|false` reste un alias de `only|exclude` ;
- `menu_order` reste un alias de `display_order` ;
- `context` reste un filtre de compatibilité via Content Data lorsqu'aucun mode `ids` n'est actif ; les valeurs absente, vide et `"0"` n'activent aucun filtre, conformément au comportement historique ;
- `[seed_quotes]` conserve son `ORDER BY RAND()` historique ;
- `[seed_quotes mode="daily"]` utilise `wp_seed_content_get_daily_quote()` sans hasard ;
- les Templates natifs et Layouts Divi Library restent responsables du HTML ;
- Gutenberg et Spectra utilisent le bloc Shortcode, sans nouveau bloc ni provider.

Le harnais `tests/collections-adapters-harness.php` réexécute les 95 assertions Collections puis valide 88 assertions d'adaptation : defaults, CSV, tris, featured, context, cas limites, états vides, sécurité, Templates, Divi Library et rendu serveur Gutenberg.

La recette runtime a été exécutée sur la base réelle de `emilieaucoeurdeletre.fr` avec WordPress 7.0.1 et PHP 8.4.21, dans un bootstrap isolé chargeant le plugin local sans remplacer la release installée. Pour les listes d'IDs éligibles comparées, le markup historique reste identique :

- `[seed_testimonials]` : SHA-256 `f00d833a752ae5023dc7643c732b20791b3693293f9243ea46ca2e7cf9fa5bd9` ;
- `[seed_quotes]` : SHA-256 `0eff183f54bdab0957512d55673cfa0e74a1c3beb610e1b2342537a67474aa58`.

La sélection diffère volontairement pour les contenus protégés par mot de passe, les anciennes valeurs non canoniques `_seed_featured=0` et les nouveaux parcours `ids`, `daily` ou `limit="0"`. Collections utilise aussi `suppress_filters=true` pour stabiliser la sélection ; les filtres de requête tiers appliqués à l'ancien `WP_Query` Témoignages ne font pas partie du contrat V1.

La sélection Tous, l'état featured vide sans fallback, les IDs, le Template Témoignage natif, la Citation quotidienne, le Layout Divi Library et le pipeline `the_content` d'un bloc Shortcode Gutenberg sont validés. Le mode quotidien n'exécute aucun `RAND` et ne laisse aucun placeholder brut. Le plugin public `0.3.0` a été restauré et n'a subi aucune modification durable.

Les lots B, C et D sont intégrés et constituent le périmètre fonctionnel du release candidate `0.4.0`. La release stable publique reste `0.3.0` et aucune release finale `0.4.0` n'est encore publiée. La préparation RC ne crée aucun cache applicatif, transient, provider ou CPT supplémentaire.

Le ZIP RC reproductible a été construit deux fois avec un SHA-256 identique, audité, extrait puis installé temporairement sur `emilieaucoeurdeletre.fr`. Les harnais Collections et Adaptateurs, les parcours administration, Content Data, Dynamic Data, Collections, shortcodes, Templates, Divi, Gutenberg, Spectra, frontend et Plugin Update Checker sont validés. Le site a ensuite été restauré exactement en `0.3.0`, sans fixture, transient simulé, sauvegarde ni fichier temporaire restant.

Prochaine étape : revue humaine du diff de préparation RC avant commit, tag et publication de la release `0.4.0`.

## 16. Template Extension Contract 1.0

Le lot 0.5.0-dev ajoute une API publique neutre permettant à un plugin tiers d'enregistrer un module de Template et des placeholders typés pendant l'action `wp_seed_content_kit_register_template_modules`.

Le contrat expose :

- `wp_seed_content_kit_get_contract_version()` ;
- `wp_seed_content_kit_supports()` ;
- `wp_seed_content_kit_register_template_module()` ;
- `wp_seed_content_kit_register_template_placeholders()` ;
- `wp_seed_content_kit_render_template()` ;
- `WP_Seed_Content_Kit_Render_Result`.

Le registre est ouvert une seule fois à `init` priorité 1. Les modules et placeholders tardifs, invalides ou dupliqués sont refusés. Le contexte est fermé, les clés inconnues sont supprimées et aucune méta métier tierce n'est lue implicitement.

Le rendu accepte uniquement un `seed_template` publié, un slug canonique et un module exact. Les erreurs retournent un HTML vide, aucun asset et un code public stable. Une pile par template/module protège les récursions directes et indirectes, avec une profondeur maximale de huit et une restauration garantie.

Les assets sont des handles WordPress préenregistrés, validés en totalité puis chargés uniquement après succès. Le cache de résolution est limité aux templates trouvés pendant la requête ; aucun cache persistant, option, transient, REST ou AJAX n'est ajouté.

Témoignages et Citations restent sur leurs parcours historiques 0.4.0 afin de garantir des sorties inchangées. Gutenberg conserve le pipeline serveur et Divi le workflow Layout Divi Library. Le contrat fonctionne sans Divi et ne contient aucun couplage métier particulier.

La spécification tierce canonique est `docs/TEMPLATE-EXTENSION-API.md`. Les harnais `tests/template-extension-harness.php` et `tests/wordpress-template-extension-harness.php` couvrent le registre, les types, l'échappement, les codes, les assets, la récursion et les builders indirects.

## 17. Preparation 0.6.0-rc.1

- Le module Annuaire natif couvre le cycle de vie prive, les 19 champs metier, l'eligibilite publique, les collections, la Data API, les shortcodes et les rendus natif, Content Kit et Divi.
- Le contrat Template Extension public reste en version 1.0 et ne produit aucun fallback metier pour les plugins tiers.
- L'etat vide charge uniquement le CSS structurel ; les cartes gardent une hauteur naturelle dans la grille responsive 3/2/1.
- Le plugin WP Seed Directory 0.1.0-rc.1 reste une reference comparative externe et n'est ni couple ni migre automatiquement.
- Limites RC : aucune recherche publique, aucune fiche individuelle, aucun REST/AJAX et aucune migration runtime.
- Le diff de preparation RC reste local, non stage et non committe jusqu'a approbation explicite.

## 18. CK-A1 - Administration et Utilisation

CK-A1 remplace la page Aide minimale par Utilisation tout en préservant les réglages et capacités existants. Le menu administrateur reste WP Seed Content Kit, Configuration, Témoignages, Citations, Annuaire et Utilisation.

Utilisation contient Fonctionnement, Templates, Collections et Intégrer dans une page. Les méthodes Shortcodes, Gutenberg, Spectra et Divi sont qualifiées comme fonctionnelles, indirectes, expérimentales ou indisponibles selon leur état réel. Collections reste une API de sélection sans CPT, sauvegarde ou écran de création.

CK-A2 complète cette architecture : editor reçoit par défaut les capacités de contenu de Témoignages, Citations et Annuaire, tandis que Configuration, Utilisation, Templates, Collections, rôles, intégrations et imports restent réservés à administrator.
## 19. CK-A2 - Rôles et capacités

Les CPT Témoignages, Citations et Annuaire utilisent des mappings de capacités propres. Administrator et Editor reçoivent par défaut les quatre primitives de chaque module : création/modification, publication, lecture privée et suppression, y compris pour les contenus d’autres éditeurs selon le mapping WordPress.

Configuration propose une matrice simple Administrator/Editor par module. Administrator est toujours autorisé. Les six capacités avancées `manage_wp_seed_*` séparent Configuration, Templates, Collections, intégrations, rôles et imports de la gestion éditoriale. Les menus utilisent `current_user_can()` sur ces capacités et non le nom du rôle.

La désactivation d’un module masque son CPT et ses menus sans retirer ses capacités ni supprimer ses données. WP Seed Events reste autonome au commit audité `dfbb66dfa8745350dece988f23ab856fde14d4ed` : son CPT utilise les capacités WordPress standard et ses réglages techniques restent sous `manage_options`. Aucun code Events ni dépendance inter-plugin n’est ajouté.

## 20. CK-A3 - Saisie Annuaire

L’écran Annuaire emploie quatre panneaux métier et le libellé « Nom affiché ». Editor et Administrator partagent la même saisie simple ; les écrans Configuration, Utilisation, Templates, Collections et outils avancés restent réservés par capacités.

Une fiche incomplète ou contenant une coordonnée privée invalide peut rester en brouillon. La publication exige un nom, un statut, un pays, l’autorisation explicite, un alt lorsque la photo existe et une valeur valide pour chaque coordonnée rendue publique. Aucune coordonnée n’est publique par défaut et aucune coordonnée publique n’est obligatoire.

La liste expose photo, nom, statut, ville, département, autorisation, types de coordonnées publiques, état WordPress et modification. Le filtre de statut est administratif uniquement. Quick Edit et la publication en masse restent neutralisés.

## 21. CK-A4 - Templates, Collections et intégrations

La page Utilisation présente le flux Contenus vers Collections, Templates et Intégrations avec des exemples Annuaire, Témoignages et Citations. Une Collection reste un ensemble de paramètres non persistant qui sélectionne les contenus ; un Template reste une présentation facultative, choisie au moment du shortcode et sans association enregistrée.

Les paramètres, valeurs autorisées, défauts et états vides sont documentés par module. Le générateur Annuaire couvre statut, département, pays, mise en avant, IDs, limite, tri, ordre et Template. Le catalogue expose uniquement les 26 placeholders publics réellement disponibles, dont exactement 15 directory.*, avec copie accessible et règles de visibilité.

Shortcodes est le parcours canonique. Gutenberg utilise le bloc Shortcode Core, Spectra reste indirect, Divi accepte Texte ou Code et les Layouts Divi Library. Les Block Bindings et Dynamic Content restent limités à leur périmètre réel. Utilisation et les réglages d’affichage demeurent hors du parcours Editor.

## CK-A6 - migration fictive native de l'Annuaire

CK-A6 ajoute un moteur interne explicite, sans execution automatique, pour valider et importer le manifeste fictif ferme `native-directory-demo-v1`. Le cycle couvre 16 fiches, 13 medias, idempotence, mises a jour ciblees, source absente, registre prive non autoloaded et rollback deterministe.

Administrator doit posseder `manage_wp_seed_imports` et fournir le contexte interne attendu. Editor est refuse. Aucune route REST/AJAX, interface admin ou dependance au depot WP Seed Directory n'est ajoutee. La version cible est 0.6.0-rc.2.

## 22. CK-A7 - regression globale et preparation RC2

La regression globale couvre les dix harnais autonomes sous PHP 7.0.33 et PHP 8.4.23, les six harnais WordPress sous WordPress 7.0.2/PHP 8.4.23, une installation neuve du ZIP, une mise a jour depuis 0.4.0 et le cycle desactivation, desinstallation puis reinstallation sur une copie dediee. Les contenus, IDs, statuts, metadonnees, reglages et capacites attendues sont preserves.

La recette navigateur utilise Microsoft Edge, le theme classique Twenty Twenty-One et Spectra. Quarante-deux captures couvrent Citations, Temoignages, Annuaire, page mixte, Template Annuaire, etat vide, administration Annuaire et Utilisation entre 1440 x 1000 et 320 x 700, plus une simulation correcte du zoom navigateur a 200 %. Aucun debordement, chevauchement, echec HTTP, erreur console ou fuite de donnee privee n'est releve. La grille Annuaire reste 3/2/1 et les quatorze fiches publiques restent reparties en deux groupes.

Divi reste facultatif et n'est pas distribue dans l'environnement isole. Son contrat, le rendu Layout Library, le fallback sans Divi et le Dynamic Content experimental sont couverts par les harnais autonomes et WordPress ; aucune nouvelle integration builder n'est ajoutee.

La RC2 conserve les Collections non persistantes, le Template Extension Contract 1.0, l'absence de REST/AJAX et l'absence de dependance au depot WP Seed Directory. Le ZIP officiel est construit hors depot de facon reproductible. Aucun staging, commit, push, tag, release ou site de production ne fait partie de CK-A7.

## 20. Module Divi 5 Témoignages — 0.7.0-rc.1

Le module `wp-seed-content-kit/testimonial-collection`, affiché comme « WP Seed — Témoignages », utilise l’API Divi 5 `ModuleRegistration`, un callback PHP et un composant Visual Builder dynamique. Il réutilise `wp_seed_content_get_testimonials()` et le renderer partagé avec le shortcode ; aucune requête métier n’est dupliquée et aucun shortcode n’est généré par l’interface.

L’aperçu passe par une route REST authentifiée réservée à `edit_pages`, uniquement enregistrée lorsque Divi 5 est disponible. Les Templates proposés sont publiés, associés à Témoignages et visibles seulement pour `manage_wp_seed_templates`. Le frontend conserve le fallback natif, les métadonnées privées ne sont jamais ajoutées au contexte, et le plugin reste activable sans Divi.

La RC1 conserve le shortcode historique et le Dynamic Content individuel. Les Templates Content Kit fondés sur un Layout Divi résolvent le frontend et le Visual Builder avec un contexte et une identité de cache distincts par témoignage ; une erreur intermédiaire déclenche uniquement le fallback natif de la carte concernée. L’utilisation directe des providers Content Kit dans le Loop Builder natif de Divi 5.9.0 reste explicitement non supportée.

## 22. Annuaire multi-usages — 0.8.0-rc.1

La Release Candidate 0.8.0-rc.1 ajoute deux facettes orthogonales au module Annuaire : une liste normalisée de types permanents (`praticien`, `intervenant`) et le booléen temporaire « Recherche de modèles ». Une fiche peut cumuler les deux types et le statut temporaire.

Les anciennes fiches sans type restent visibles dans la Collection complète. Elles ne sont jamais classées automatiquement et n'apparaissent dans une Collection typée qu'après une décision éditoriale. L'ancien statut `seeking_models` est copié de façon additive et idempotente vers le nouveau booléen, sans suppression ni réécriture du statut historique.

Les Collections restent non persistantes et constituent l'unique source de filtrage pour les shortcodes et intégrations. Le rendu natif historique reste inchangé ; les nouveaux libellés sont opt-in dans les Templates. Divi reste pris en charge via shortcode et Layout de Template, sans Loop Builder Annuaire.


## 23. Annuaire — visibilité publique et présentation complète — 0.8.0-rc.2

La RC.2 ajoute une présentation complète native dans `post_content` et sépare explicitement `summary`, `bio` (alias rétrocompatible strict) et `full_presentation`. La visibilité publique devient une décision indépendante stockée par `_seed_directory_publicly_listed=1` ; toute autre situation est exclue des Collections publiques.

Une migration additive et reprenable attribue cette valeur uniquement aux fiches existantes publiées, non protégées et sans valeur explicite. Elle ne classe aucun profil et ne modifie ni consentement, contacts, types, statut Recherche de modèles, médias, résumé ou présentation complète. Le Loop Builder natif Divi reste hors contrat ; les Templates Content Kit continuent d’alimenter les Layouts Divi avec un contexte public fermé.

## 24. Module Divi 5 Annuaire — 0.8.0-rc.2

Le module `wp-seed-content-kit/directory-collection`, affiché comme « WP Seed — Annuaire », utilise l’API publique Divi 5 `ModuleRegistration`. Il expose les filtres canoniques Annuaire, la sélection et l’exclusion par IDs, la limite, l’offset, le tri et un Template Content Kit publié.

Le shortcode `[seed_directory]`, le callback frontend du module et l’aperçu Visual Builder utilisent `wp_seed_content_render_directory_collection()`. Ce renderer appelle exclusivement `wp_seed_content_directory_get_entries()`, la Data API publique et le renderer de carte existant. Il n’existe aucune requête ou règle d’éligibilité propre au Builder.

L’aperçu est fourni par `GET /wp-seed-content-kit/v1/divi/directory-preview`. La route n’est enregistrée que lorsque Divi 5 est disponible, exige un nonce REST valide et `edit_pages`, est en lecture seule et répond sans cache public. Les Templates restent sélectionnables uniquement avec `manage_wp_seed_templates`.

Les Collections restent fermées : une fiche non listée, brouillon, privée, protégée ou non autorisée ne peut être rendue, même par ID explicite. `directory.summary`, `directory.bio` et `directory.full_presentation` suivent le même contexte et le fallback reste local à la carte défaillante.

Cette intégration ne prend pas en charge le Loop Builder natif Divi et n’utilise ni `MutationObserver`, ni remplacement DOM, ni API React/Webpack interne, ni délai arbitraire. Sans Divi, aucun module ni route n’est enregistré et les shortcodes historiques restent fonctionnels.

## 25. Annuaire - contrat `ids` + `exclude_ids` - 0.8.0-rc.3

La sélection publique normalise et déduplique séparément `ids` et `exclude_ids`. Si `ids` est fourni, la Collection calcule d'abord `ids - exclude_ids`; une intersection totale retourne immédiatement le résultat vide. Sans `ids`, `exclude_ids` filtre la population publique complète. Les règles d'éligibilité restent non contournables, puis viennent les filtres métier, le tri canonique, l'offset et la limite. L'ordre textuel de `ids` n'est pas un tri : `orderby` et `order` conservent le comportement historique. Aucun schéma, aucune méta et aucune donnée ne sont migrés entre RC.2 et RC.3.

## 26. Témoignages — Native Divi Loop — 0.8.0-rc.4

Le contrat public exige désormais la valeur exacte `_seed_testimonial_publication_consent=1`. La mise en avant indépendante utilise `_seed_testimonial_featured=1`. La Collection prend en charge `all`, `featured`, `random` et `featured_or_random`, tandis que le Visual Builder reçoit un ordre aléatoire déterministe.

Le stockage portable canonique utilise `post_title`, `post_excerpt`, l’image mise en avant, puis `seed_testimonial_text`, `seed_testimonial_name` et `seed_testimonial_context`. La date métier reste optionnelle. Les anciennes métas `_seed_testimonial_*` ne sont lues qu’en fallback de compatibilité lorsqu’aucune valeur canonique n’existe.

Divi 5 reste responsable de la présentation. Content Kit fournit les données, la requête publique, le consentement et les neuf providers « WPSCK — Témoignages ». Une Native Loop placée sur une Row ou sur le Group d’une slide de Group Carousel natif conserve son contexte par item : les providers utilisent `loop_id`, puis `loop_object`. Content Kit ne fournit aucun Carousel propriétaire.

Cette architecture ne stocke aucune donnée propre à Divi et reste exploitable par Gutenberg, les custom fields et de futurs adaptateurs Spectra/Astra. Divi 5.9.0 n’expose pas de condition impair/pair sur l’index du clone Loop ; l’alternance Detailed utilise donc uniquement le support CSS structurel opt-in WPSCK, sans style éditorial.

Le renderer et le module historiques restent disponibles. La migration des 22 témoignages historiques est un appel explicite ciblé et réversible, jamais une migration automatique.

## Parcours & calendrier V4.1 - 2026-09-07

Reprise du candidat V4 sans nouveau changement produit ni déploiement : manifeste
local/DEV `f88ce80514af8c0fbf2de85e15d659ad03dfee7144e4e2760186e5efae6c46d1`,
246 fichiers. Matrice et preuves détaillées :
[audit V4.1](docs/PARCOURS-CALENDRIER-V4-1-AUDIT.md).

47 harnais autonomes passent sous PHP 7.0 et 8.4 ; 61 assertions WordPress
transactionnelles passent, dont le handler admin des sections et les Block
Bindings réellement rendus. La programmation TEMP V4 4581 et sa révision 4582
ont été supprimées sous backup exact ; les 27 programmations représentatives
restent présentes. Les autres posts et métadonnées sont inchangés.

Revue visuelle V4.1 bloquée par la protection Computer Use : URL Chrome courante
non vérifiable avec assez de certitude. Aucun contournement. Ce n'est pas un
échec serveur : préflight authentifié HTTP 200. Mobile, workflow UI complet et
seconde passe visuelle restent non validés. Verdict SAFE_STOP, jamais HUMAN PASS
ni READY produit. Aucun commit/push/tag/release.

## Parcours & calendrier V4.2 - 2026-09-07

La validation visuelle est désormais possible avec Playwright/Chromium déjà
installés, authentification normale et aucune dépendance produit ajoutée.
Deux matrices de captures DEV couvrent les sept largeurs 1440 à 360px.
CRUD réel sur TEMP 4648 : création, modification, réouverture et suppression
validées ; 4648/4649 nettoyés sous backup, 27 références conservées.

Un défaut de largeur des cases à cocher mobiles est corrigé : suppression du
width:auto local pour conserver les dimensions WordPress natives 25x25px.
Runtime local/DEV exact, 246 fichiers, manifeste
`852d7025e3fd7e1ab3aadcee4d4461a45d6040481cb4d78ae593e1eb637ec5d1`.
Backend 61 assertions V4.1 conservé, 47 harnais autonomes PHP 7.0/8.4 PASS.
Deuxième passe mobile/visuelle achevée, aucune mutation de page/Events/Directory.
Voir [validation V4.2](docs/PARCOURS-CALENDRIER-V4-2-VALIDATION.md).
READY FOR GUILLAUME FINAL PRODUCT JUDGEMENT, pas HUMAN PASS.
Aucun commit/push/tag/release.

## Schedule Multiplicity V1 - 2026-09-07

Programmations multiples autorisées par identité exacte Programme/année/Thème/
Section/date début/date fin. Verrou applicatif MySQL par site ; Structure
dédupliquée, calendriers complets. Aucun import réel ni migration.

48/48 harnais autonomes sur PHP 7.0 et 8.4, 89 assertions WordPress DEV PASS.
Empreinte posts/postmeta identique après rollback des fixtures transactionnelles.
Runtime DEV déployé et vérifié, 246 fichiers :
`bb80d9bccc9e7a48983a22924877a7cbe47dbd160a831dcaf5e6835f16cafc8f`.

SAFE_STOP : préflight HTTP 200, puis contrôle HTTP final 403 après déploiement.
Aucun retry, aucune recette UI/mobile supplémentaire. Le candidat reste déployé
avec backup exact disponible. Pas de READY import ni de HUMAN PASS.
Voir [rapport V1](docs/PARCOURS-CALENDRIER-SCHEDULE-MULTIPLICITY-V1.md).

### Vérification post-déploiement V1.1

Diagnostic read-only : Accueil et page TEMP authentifiés HTTP 200 ; page TEMP
non authentifiée HTTP 401 attendu. Le 403 concerne le CSS Formation servi par
Apache. Cause identifiée : extraction sous umask 077 sans normalisation, runtime
en 700/600 contre 755/644 dans le backup. Les 246 fichiers correspondent toujours
exactement au candidat. Aucun changement de permissions effectué.

SAFE_STOP maintenu : réparation ciblée des permissions à autoriser séparément,
puis validation des assets et recette UI/mobile. Aucun import réel.
Voir [diagnostic V1.1](docs/PARCOURS-CALENDRIER-MULTIPLICITY-POST-DEPLOY-V1-1.md).

### Permissions post-Multiplicity corrigées

Réparation autorisée effectuée : 34 dossiers 755, 246 fichiers 644, zéro
exception, SHA produit inchangé. Accueil, page TEMP et les trois assets Formation
retournent HTTP 200 ; les corps CSS/JS correspondent aux hashes locaux.
Aucun changement de code, page ou donnée. Backup précédent conservé.

Le navigateur contrôlé est bloqué par ERR_INVALID_AUTH_CREDENTIALS ; recette
CRUD/frontends/mobile non validée, à effectuer en session authentifiée.
ASSETS RESTORED / READY FOR VISUAL REVIEW, pas HUMAN PASS ni READY import.
Garde-fou obligatoire au prochain deploy : normalisation 755/644 du candidat
après extraction, avant swap, et validation des permissions en plus du manifeste.
Voir [rapport de réparation](docs/PARCOURS-CALENDRIER-MULTIPLICITY-PERMISSIONS-FIX.md).

### Multiplicity V1.2 - validation navigateur finale

Playwright/Chromium indépendant authentifié fonctionne. Trois programmations
TEMP du même Thème ont été créées via l'UI, doublon exact refusé, date modifiée,
puis suppression UI et nettoyage privé des révisions. Les 27 références et
l'empreinte globale posts/postmeta sont strictement conservées.

25 captures : admin et Structure/Grouped/Chronologique à 1440/390/360, puis
seconde passe viewport mobile. 31 contrôles navigateur PASS, assets Chromium
200 avec hashes exacts, backend 82 assertions PHP 7.0/8.4 PASS. Aucun changement
produit ; manifeste DEV reste bb80d9bccc9e7a48983a22924877a7cbe47dbd160a831dcaf5e6835f16cafc8f.

SCHEDULE_MULTIPLICITY_V1_FINAL_VALIDATED, sans nouveau HUMAN PASS ni import réel.
Voir [validation V1.2](docs/PARCOURS-CALENDRIER-MULTIPLICITY-V1-2-VALIDATION.md).

### Import reel Parcours 2026/2027 - 2026-09-07

Import explicitement autorise termine : programme reel 4723, six sections,
32 themes (4 reutilises, 28 crees), 35 programmations 2026 et 36 en 2027.
Supervision : sept dates par an ; quatre stages residentiels au total.
Les 17 et 19 decembre 2027 sont deux journees distinctes, sans le 18.
Equivalences Odyssée/Ma légende et Santé Mentale/Psychopathologies non supposees.

Backup prive formation-real-2026-2027-20260907T062229Z ; import transactionnel
avec verrou et controle des anciennes lignes posts/postmeta. Pages, Directory,
Events, runtime et donnees preexistantes inchanges. Recettes conservees et listees,
dont la page 4462 et le programme 4446 ; ne pas supprimer les quatre themes partages.

Verification independante 82 controles PASS, WordPress transactionnel 89 PASS,
48/48 harnais sur PHP 7.0 et PHP 8.4. Revue Chromium des six rendus serveur
en preview locale, 18 captures a 1440/820/390, aucun overflow. Aucune integration
automatique dans une page existante. Warning tiers iFolders CLI documente.
Pas de commit/push/tag/release ; aucun changement produit.
Voir [rapport et matrice exhaustive](docs/PARCOURS-CALENDRIER-REAL-DATA-2026-2027-IMPORT.md).

## Calendrier Divi V3 - candidat local, 2026-09-07

Cartes Section manuelles, boucle Schedule unique avec filtres natifs Programme /
annee / Section / Theme / statut. Providers scalaires loop_* issus du registre
portable ; aucun contexte parent prive, aucune carte HTML imposee. Shortcode
preserve. Voir [configuration et limites](docs/FORMATION-DIVI-CALENDAR.md).

49 harnais autonomes PASS sous PHP 7.0 et 8.4 ; harnais Calendrier 87 assertions,
lint PHP PASS, syntaxe JS PASS, Gutenberg JS 14 PASS. Le harnais WordPress
transactionnel est ajoute mais NON EXECUTE. DEV NON DEPLOYE dans ce lot.
Prototype et responsive NON TESTES : ouverture du Visual Builder refusee avec
net::ERR_INVALID_AUTH_CREDENTIALS. Aucun contournement Basic Auth. Aucun changement
de page/donnee ; aucun commit/push/tag/release. Ne pas annoncer IMPLEMENTED en DEV.

## Calendrier Divi V3.1 - deploy et recette WordPress, 2026-09-07

Le statut local-only V3 ci-dessus est historique : candidat identique deploye
sur DEV, 248 fichiers, manifeste GNU-sort compatible
207f25b66575d80676aaa02cb238560cc5a2c7ceec2d2de263bc3476431880fd.
Backup prive wpsck-calendar-v31-20260907T094517Z ; permissions 755/644 verifiees
avant et apres swap. Preflight, accueil et trois assets Formation HTTP 200.

49 harnais PHP 7.0/8.4 PASS par version ; 85 controles WordPress read-only et
11 controles transactionnels PASS. Filtres/tris/providers, 35/36 programmations
et Supervision 7/7 verifies. Fixtures rollbackees ; empreintes exactes de tous
les posts, metas, relations et option modules conservees. Aucun code produit
modifie pendant V3.1, aucune page existante ni donnee reelle modifiee.

Recette UI toujours ouverte : navigateur integre ERR_INVALID_AUTH_CREDENTIALS ;
Chromium isole ERR_NETWORK_ACCESS_DENIED. Aucun contournement Basic Auth.
Nouvelle page TEMP native non creee, editabilite/activation UI/responsive et
mise a jour dynamique du rendu de page non testes. Tests serveur de desactivation
par filtre d'option en memoire seulement, pas preuve de discovery dans le builder.
SAFE_STOP sur ces validations precises, pas un echec du deploy ni un HUMAN PASS.
Rapport prive : WPSCK-DYNAMIC-CALENDAR-DIVI-V3-1-REPORT.md.
Temporaires nettoyes ; index vide ; aucun commit/push/tag/release.

## Calendrier Divi V3.2 - acces UI et arret LOW TRAFFIC, 2026-09-07

Chromium isole avec permission reseau explicite : Basic Auth puis login WordPress
Codex Superviseur fonctionnels. Nouvelle page TEMP 5007 en draft, titre
WPSCK TEMP — Calendrier dynamique Divi V3.2. Contenu Divi initial vide cree via UI.
Le flag natif _et_pb_use_builder restait vide apres conversion ; seule cette
nouvelle page a recu on via REST WordPress public avec garde ID/titre.
Le vrai Visual Builder s'ouvre maintenant. Aucune API privee Divi utilisee.

Arret avant construction : chargement automatique de
/wp-json/wp-seed-events/v1/person-types retourne HTTP 401 inattendu.
Politique LOW TRAFFIC appliquee, aucun retry ni changement Events/auth.
Cartes 2026/2027, edition dynamique et responsive NON VALIDES. Ne pas reporter
le blocage comme un nouvel echec Basic Auth : l'acces navigateur est prouve.
TEMP 5007 conservee en brouillon ; aucune autre page ni donnee reelle modifiee.
Aucun deploy/code produit/commit/push/tag/release ; helper et navigateur fermes.
Rapport prive : WPSCK-DYNAMIC-CALENDAR-DIVI-V3-2-REPORT.md.

## Calendrier Divi V3.3 - discovery corrigee, recette interrompue, 2026-09-07

Le 401 person-types provient du bundle event-people WP Seed Events au chargement
du Visual Builder. Aucun nonce REST observe sur cette requete. Probleme externe
NON BLOQUANT : navigation, selection, reglages Loop et picker fonctionnent.
Events et ses credentials/routes/donnees restent inchanges.

Bug WPSCK reel : Divi enumere les CPT show_ui avant son allowlist query-types.
Le CPT technique seed_schedule etait donc absent. La route publique query-types
ouvre temporairement show_ui puis restaure exactement le flag, y compris pour
les appels imbriques. Pas de modification persistante du CPT ni API privee.
Harnais calendrier 97 assertions sur PHP 7.0/8.4 ; 49 harnais standalone sur
chaque version ; lint PHP/JS PASS. Test WordPress transactionnel 11 controles
PASS, fixtures supprimees et empreintes donnees conservees.

Runtime deploye : 248 fichiers, manifeste
82bad60cd69e0ff33289ccf68d8035f88b30b1c4140d26313ada7c01784981ae.
Backup prive : .wpsck-rollbacks/wpsck-calendar-v33-20260907T102636Z.

5007 uniquement : seed natif configure et sauvegarde dans le vrai Divi avec
filtre programme 4723, annee 2026, section 4725, provider Date. Puis dix cartes
natives 2026/2027 soumises via REST WordPress public, sous garde du contenu
precedent et sauvegarde locale exacte du seed. Reponse HTTP 200, ID 5007 draft.
Deux relectures context=edit expirent ensuite a 30s, avec backoff : STOP reseau.
Le match=false du POST ne prouve pas un mismatch (content.raw peut etre absent
du contexte de reponse). Ne pas annoncer le prototype ou responsive PASS.
Relecture brute finale, clones frontend, editabilite complete et responsive
restent NON VALIDES. Les 4 stages residentiels sont exclus des cartes demandees
(population attendue 33/34, dont Supervision 7/7), sans supprimer leurs donnees.
71 programmations reelles non editees. Aucune autre page ni Events modifies.
Aucun commit/push/tag/release. Rapport prive V3.3 conserve hors depot.

## Calendrier Divi V3.4 - persistance prouvee, filtrage FAIL, 2026-09-07

Relecture REST limitee a content.raw HTTP 200 : 10 cartes conservees et attributs
natifs semantiquement identiques a la soumission V3.3 (echappement JSON seulement).
Frontend et Visual Builder HTTP 200. Chaque carte rend cependant 71 items des
deux annees : 710 au total, Supervision 71 au lieu de 7 pour chaque annee.
Les metaQuery programme/annee/section sont stockees mais inefficaces au rendu.
Cause exacte dans le pipeline a auditer, aucun correctif produit dans ce lot.

Test UI autorise sur premiere carte 5007 : Date 18px/interligne 1.5em/paragraphes
0.5em ; Intitule 20px/interligne 1.4em. Save Divi, relecture brute et reload
frontend confirment styles et bindings. Builder rouvert. Filtres inchanges.
SHA final 5007 : ac71ef611e5cd8437f485efd6dd4f6aa3bc449ed45b0d79c9264ba38e43c3076.
1440/1024 deux colonnes ; 820/768/390/360 une colonne. Overflow document 0 sauf
360 (30px, environnement admin a isoler). Pas de PASS responsive global.
Fixture dynamique, cycle module off/on et move/hide non executes apres echec
fonctionnel. Ni donnees reelles ni autres pages ni runtime modifies.
SAFE_STOP fonctionnel, pas Events/connectivite. Ne pas reconstruire 5007.
Rapport prive WPSCK-DYNAMIC-CALENDAR-DIVI-V3-4-REPORT.md et captures conserves.

## Calendrier Divi V3.5 - combinaison des filtres corrigee, 2026-09-07

Cause prouvee : Divi build_meta_query combine les lignes natives en OR. Le
programme suffit alors a inclure 71 fiches. Valeurs conservees, relation erronee
pour les dimensions calendrier. Adapter WPSCK commun REST/frontend : AND pour
egalites scalaires plates sur dimensions connues distinctes seulement. Autres
CPT/requetes complexes preserves. Aucun changement modele, donnees ou page.

Deploy atomique d'un fichier apres audit courant et backup exact :
.wpsck-rollbacks/wpsck-calendar-v35-20260907T105803Z.
248 fichiers, permissions 755/644, manifeste exact :
8f9f72f465b6170c204ccc524a7e6400d06d7f5ef56abd96a6716044796f3f25.
49 harnais PHP 7.0 et 8.4 chacun, calendrier 112 assertions, lint PHP/JS PASS.
Matrice WordPress de 20 requetes reelles et 16 controles transactionnels PASS.
Fixtures 5019/5020/5021/5022 rollbackees. Empreintes posts/metas/options conservees.

Frontend reel 5007 HTTP 200 : comptes 7/7/6/6/7 puis 7/7/6/7/7, annees isolees.
Supervision 7/7 ; plus aucune carte a 71. 67 rendus, les 4 stages residentiels
restent hors des cartes demandees. Raw tokens 0. Builder rouvert HTTP 200.
SHA page et styles V3.4 inchanges :
ac71ef611e5cd8437f485efd6dd4f6aa3bc449ed45b0d79c9264ba38e43c3076.
Mise a jour de fixture validee backend/provider + requete, pas par un cycle
navigateur de fixture. Module off/on teste transactionnellement, pas via UI.
Stacking responsive conserve. Overflow document connecte mesure 52px a 768 et
30px a 360 ; ne pas annoncer zero overflow global ni PASS visuel complet.
Aucun CSS/design modifie pour masquer ce constat hors correctif de filtres.
Rapport prive WPSCK-DYNAMIC-CALENDAR-FILTER-PIPELINE-V3-5-REPORT.md.
Aucun commit/push/tag/release.

## Calendrier Divi V3.6 - prototype fonctionnel valide, 2026-09-07

Vrai Visual Builder authentifie et frontend : 10 cartes, comptes 7/7/6/6/7 puis
7/7/6/7/7. Supervision 7/7 ; 17 et 19 decembre 2027 restent distincts. Date,
intitule, intervenants et statut prouves avec fixture temporaire 5025 creee via
admin, modifiee puis supprimee definitivement sous garde. Les 71 programmations
reelles, programme, sections, themes et Directory sont exactement preserves.

UI Divi : Date 19px, espacement paragraphes 0.6em, titre 21px et masquage Desktop
du Statut verifies apres Save/Close/Reopen. Retour ensuite aux styles 18px,
0.5em, 20px et statut visible. Seul delta semantique final : disabledOn desktop
explicitement false au lieu d'absent. Les 85 blocs, bindings et 10 filtres restent
identiques. SHA final 5007 draft :
39d53c6409832eee6d94187d8d68535aadc5d80bf8f4314fca96246369d2d792.

Module Formation off/on teste dans le vrai picker : 13 -> 0 -> 13 options,
IDs/labels restaures. Option modules restauree exactement. Aucune sauvegarde de
page pendant la desactivation. Donnees temporaires 0, helpers supprimes.

Overflow precedent localise : section/row du header Divi conservant brievement
l'ancienne largeur au resize (390 vers 360 = 30px, 820 vers 768 = 52px).
Apres stabilisation : zero aux six largeurs 1440/1024/820/768/390/360, sans CSS
ajoute ni overflow-x masque. Le transient au redimensionnement n'est pas corrige.

Reste presentation non bloquante : typographie disparate premiere carte/autres,
rythme vertical trop espace, repetition Prevu. Aucun redesign ni autre page,
Events, Directory ou runtime modifies. Pas de commit/push/tag/release.
Rapport : docs/DYNAMIC-CALENDAR-DIVI-V3-6-FINAL-PROTOTYPE.md.
READY_FOR_GUILLAUME_FINAL_REVIEW, pas une autorisation de publication.

## Dynamic Calendar V3.7 - readable filters (2026-09-07)

Public Divi field/settings/query hooks now expose Programme, Annee, Section,
Theme, Statut and Tri on native containers. Explicit opt-in, internal canonical
IDs preserved, sections restricted to program, same frontend/builder query path.
Real UI A/B/C, Supervision 7/7, save/reopen, module off/on and six responsive
widths validated. TEMP draft 5007 kept: 85 blocks, only ten new filter attributes.
No card/style/binding rewrite; all 71 schedules and other page contents unchanged.
Seven derived Divi cache metadata rows were invalidated, not business data.
DEV 250 files, manifest 8b44fa9160d6cfb311a0ad35ca45eb5666552bc9e90af2a384cd7977d6fa96a5.
49 PHP harnesses pass on 7.0/8.4; native filter JS harness 15 assertions.
No commit/push/tag/release. Report: docs/DYNAMIC-CALENDAR-DIVI-V3-7-REPORT.md.

## Program/year discovery architectural audit (2026-09-07)

No product/runtime/page change. Portable collection prototype kept under tests,
not plugin: PROGRAM_YEAR and UPCOMING_PROGRAMS reuse canonical effective dates.
Fresh read-only DEV: Divi 5.11.1, 35/36 schedules intact, site date 2026-09-07;
eligible counts 10/36, next starts 2026-09-25 / 2027-01-15. 2028 and expiration
proved synthetically. Parent clone context remains unproven for nested Schedule
queries in both builder and frontend. A flat group removes one nesting level,
not the context requirement. No fake CPT/year posts, private API or heuristic.
V3.7 page 5007 and its filters remain unchanged. No new Divi UI PASS claimed.
Report: docs/DYNAMIC-PROGRAM-YEAR-DISCOVERY-AUDIT.md.
WPSCK_DYNAMIC_PROGRAM_YEAR_DISCOVERY_LIMITATION.

## Program/year V4 core implementation (2026-09-07)

calendar-discovery.php now implements portable PROGRAM_YEAR, UPCOMING_PROGRAMS,
explicit context resolution and context-bound Schedule queries locally. Audit
wrappers delegate to production functions; no duplicated prototype algorithm.
WordPress fixture transaction: 22 checks PASS, eight fixture posts rolled back,
all wp_posts/postmeta rows restored. Active DEV runtime NOT deployed/changed.
No page edit, no false Divi automatic-year-zone PASS. V3.7 remains active.
Missing public parent-clone query context still limits full automatic Divi cards.
Report: docs/DYNAMIC-PROGRAM-YEAR-V4-IMPLEMENTATION.md.
No commit/push/tag/release. WPSCK_DYNAMIC_PROGRAM_YEAR_V4_LIMITATION.

## Program/year Divi V4.1 context feasibility (2026-09-07)

Audit only, no DEV access or product changes. Public Divi read-only parent/ancestor
selectors exist; saved parent attributes are not a proven current PROGRAM_YEAR
clone context. A-E reviewed beyond query hooks. Recommend E, a dedicated Calendar
consumer with separately styled elements, classified PARTIAL. Exact Divi 5.11.1
API availability, repeated editing and minimum column/responsive freedom still
need proof. No claim of native child-loop context injection or generic CURRENT
YEAR Heading token. Multi-program Section ownership and batched query requirements
documented. No prototype, deployment, page/data mutation or Git release operation.
Report: docs/DYNAMIC-PROGRAM-YEAR-DIVI-V4-1-CONTEXT-AUDIT.md.

## Dedicated Calendar V4.2 implementation (2026-09-07)

Local module wp-seed-content-kit/formation-calendar: structured data, independent
Divi elements/styles, automatic program/year groups. 53/53 PHP harnesses PASS on
7.0/8.4. Public module APIs verified on real Divi 5.11.1. Initial 255-file deploy
and WP transaction checks passed, but real prototype rendering was empty; both
TEMP page attempts rolled back. Local correction uses public skipAttrChildren /
skipChildren options. Corrected deploy stopped before replacement on global
postmeta fingerprint mismatch (page contents and wp_posts guards passed).
DEV then restored to exact original 250-file runtime under manifest guard.
No working V4.2 prototype retained; 5007 unchanged. No Visual Builder PASS:
controlled browser Basic Auth failed with ERR_INVALID_AUTH_CREDENTIALS.
No bypass, no commit/push/tag/release. Metadata drift attribution remains open;
do not silently relax guard or call V4.2 ready. Original runtime backup retained.
Report: docs/DYNAMIC-CALENDAR-DIVI-V4-2-REPORT.md.

### V4.2 qualified-baseline deployment (2026-09-07, subsequent authorized run)

The state above is historical. Metadata drift was attributed exactly to seven
derived Divi cache additions (49346-49352); no remaining historical row changed.
Separate detailed baseline qualified and preserved, old baseline unchanged.
Corrected V4.2 is now deployed: 255 files exact, permissions 755/644, HTTP and
new asset 200. Strict wp_posts/postmeta checks pass with zero row differences.
No guard exclusion. All 27 real WP transaction checks pass; fixtures rolled back.
53/53 standalone harnesses pass on both PHP versions. Public pages/5007 unchanged.
Real Divi visual validation still blocked by ERR_INVALID_AUTH_CREDENTIALS;
no editability, persistence, responsive or real visual 2028 PASS claimed.
No commit/push/tag/release. See docs/DYNAMIC-CALENDAR-DIVI-V4-2-QUALIFIED-DEPLOY.md.

### Annual planning V5 (local; deployment stopped before backup)

Preview/confirmation and transactional next-year draft creation implemented,
reusing Program/Section/Theme, without dates or historical status. Existing dated
save publishes completed drafts after capability and exact-duplicate validation.
54/54 standalone harnesses PASS on PHP 7.0/8.4; real WP V5 harness NOT RUN.
DEV remains exact V4.2 (255 files). One new Divi font cache on TEMP 5007,
meta_id 49388, stopped V5 before backup/replacement. No guard bypass or new baseline.
No real data/page/Directory/Events changes. No remote fixture retained.
Divi human validation and per-section layout freedom remain open.
See docs/ANNUAL-PLANNING-V5.md. No commit/push/tag/release.

### Annual planning V5: real DEV validation (2026-09-07)

Supersedes the preceding deployment STOP. Explicitly qualified cache 49388 was
matched exactly; all old detailed rows were retained in the guard. No baseline
replacement or global cache exclusion. Runtime manifest ./ prefix mismatch fixed
in the temporary comparator only, then all old 255 runtime files matched.
V5 now deployed atomically: 257 exact files, 755/644, site/Calendar asset HTTP 200.
Real WP fixture tests pass: 2027->2028 draft creation, inherited refs/people/order,
no dates, injected transaction rollback, source preservation, automatic year/title,
expiry, multi-program isolation and real Divi server rendering. Fixtures removed;
global posts and postmeta hashes unchanged after tests and final HTTP checks.
4723 remains 6 Sections / 32 used Themes / 71 Schedules (35/36). All pages unchanged.
Private rollback: .wpsck-rollbacks/annual-planning-v5-20260907T210016Z.
54/54 standalone tests pass on PHP 7.0/8.4. No product edits in this validation run.
Visual Builder still returns ERR_INVALID_AUTH_CREDENTIALS: visual editability,
responsive and real picker activation cycle NOT VERIFIED. No full visual PASS.
See docs/ANNUAL-PLANNING-V5-REAL-DEV-VALIDATION.md. No commit/push/tag/release.

### Full dynamic Calendar V5.1 (local candidate, visual authorization gate)

One generic module passes the exact local A/2026+A/2027+A/2028+B/2027 matrix,
without attr changes. Seven supervision occurrences/year preserved. Added opt-in
separate start/end/theme/annual-title/section/program native elements; defaults off.
No canonical data change or new private Divi API. 55 standalone harnesses PASS on
PHP 7.0/8.4; PHP/JS render contracts remain separate, not an HTML blob preview.
DEV remains V5/257 files: no V5.1 deployment or real fixture mutation this run.
Playwright/Chrome standard credential context hit ERR_NETWORK_ACCESS_DENIED;
elevated browser execution was denied by approval controls. No bypass attempted.
Divi editing, Save/Reopen, responsive and real activation proof remain pending.
No final product PASS. See docs/FULL-DYNAMIC-CALENDAR-V5-1.md. No Git release action.

### Full dynamic Calendar V5.2 (DEV deployed; visual gate remains)

Resumed V5.1 candidate: four Calendar runtime files changed, 257 files exact.
Candidate SHA 8a6fb8fad000d24813f6a0fdd04b130253c7138623cf7b4b6e3c89e2993d4a7d.
Backup .wpsck-rollbacks/calendar-v52-20260907T213343Z, atomic exchange, 755/644,
active plugin and authenticated site/Calendar asset 200. Detailed postmeta guard
matched every baseline row plus exact qualified cache 49388; unknown differences 0.
Both real WP harnesses pass: annual planning and exact single-template matrix
A/2026+A/2027+A/2028+B/2027, 2028 removal, expiry, empty/cancelled programs,
isolated fields and seven Supervision occurrences/year. Runtime activation cycle
and module option restoration pass; global posts/postmeta hashes unchanged.
All TEMP data rolled back; 4723/Directory/Events/pages/5007 unchanged.
One real browser attempt on 5007 failed ERR_INVALID_AUTH_CREDENTIALS before VB.
No bypass. GUI editability, Save/Reopen, responsive and picker cycle NOT VERIFIED.
55/55 local harnesses pass on PHP 7.0/8.4; no commit/push/tag/release.
See docs/FULL-DYNAMIC-CALENDAR-V5-2.md. Verdict SAFE_STOP for visual acceptance only.

### Calendar architecture gap: actual page vs deployed module (2026-09-08)

Fresh read of 5007: SHA f30d916cedf02f2d12f136e4cefba5247948aaad2dbdab2742a076b522c594cc,
draft, modified 2026-09-07 18:03:32, 35236 bytes. Zero dedicated Calendar instances.
Two literal year trees, ten seed_schedule native Group loops with static
4723/year/section filters. This is V3 construction, not a V5.2 single template.
V5.2 deployment preserved 5007; its real WP proof used a harness block string.
PROGRAM_YEAR is consumed inside the dedicated Calendar, not registered as a
general Native Loop source or inherited context for arbitrary child modules.
No page/data/product changes in diagnostic phase. New TEMP single-module visual
proof still required. See docs/DIVI-SINGLE-TEMPLATE-ARCHITECTURE-GAP.md.
Real Chrome follow-up confirms the dedicated Calendar in the native insert picker.
No insertion/save into5007. Separate new-page UI allocated incomplete TEMP5152;
title input failed UIA CacheRequest 0x80070057 and focus remained address bar.
No new Calendar instance or real four-pair visual proof completed. No fixture
Formation records created; TEMP5152 is retained explicitly, not claimed cleaned.
Local single-template harness243 cumulative assertions PASS on PHP7.0 and8.4.

### Directory 1896 role/order rendering (2026-10-13)

Latest scoped DEV correction: ordinal Divi selectors collided with changing
Directory clone indices. 1896 now uses 26 dedicated native presets and stable
card/portrait classes; native mask render flags remain on the template while
the drawing is shared by preset CSS. Conditions, bindings and animations are
preserved. No product code change or plugin deployment in this run.
Five final fixture permutation states PASS across all three loops. Fixtures and
remote helpers removed; real Directory/Pixel/6354/Events/Formation unchanged.
1897 remains byte-identical, 12 cards, portraits/separators/alternation intact.
Responsive checks PASS (390 bracketed at 389/391 because of existing Chrome zoom).
PHP 7.0/8.4 targeted tests/lint and JS syntax PASS. No Git release action.
Guillaume's HUMAN PASS is now VALIDATED: cream panel, wave, dark name, orange
icons and card consistency confirmed. Lot CLOSED; this final acceptance update
changes local documentation only. Private rollback and exact mutation evidence
remain archived outside the plugin. No new DEV/data/preset/product change or
Git release action. See docs/DIRECTORY-1896-ROLE-ORDER-FIX-REPORT.md.
