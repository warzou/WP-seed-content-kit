# SIMPLE CALENDAR - integration Divi

Statut : architecture finale SIMPLE CALENDAR, rollback fonctionnel et nettoyage
DEV valides le 2026-09-08. Voir [decision produit](SIMPLE-CALENDAR-DECISION.md) et
[rapport final](SIMPLE-CALENDAR-PROTOTYPES-DELETION-COMPLETE.md).

## Architecture finale

WPSCK gere les donnees, l'administration, le calendrier normal, les
programmations, leur multiplicite, les filtres et les providers individuels.
Structure, Grouped, Chronologique, Next Date et le shortcode normal sont conserves.
Divi gere la presentation, la mise en page et les sections annuelles manuelles.

Pour une nouvelle annee : dupliquer la section Divi de l'annee precedente,
puis adapter les dates et les filtres explicites Programme/Annee/Section.
Les dates canoniques des programmations restent administrees dans WPSCK.
Il n'y a ni creation automatique d'annee ni duplication automatique de donnees.

La multiplicite reste necessaire : plusieurs programmations d'un meme Theme
dans un meme Programme et une meme Annee sont autorisees. Seul le doublon exact
Programme + Annee + Theme + Section + debut + fin est refuse, avec exclusion
de l'ID courant et verrou concurrentiel lors de la sauvegarde.

## Fonctionnalites abandonnees

Full Dynamic Calendar, Program/Year Discovery (`PROGRAM_YEAR`,
`UPCOMING_PROGRAMS`), Child Modules Calendar/Schedule/Date/Title/People et
Annual Planning N vers N+1 ont ete abandonnes. Ils ne sont pas disponibles.
Le module Calendar dedie et son template unique multi-programmes/multi-annees
ne font plus partie du produit.

Les anciens rapports V3/V4/V5 sont des preuves historiques, pas des instructions
de deploiement ou de recreation des prototypes. Pour les experiences retirees :
DOCUMENT HISTORIQUE - fonctionnalite abandonnee.
Les filtres explicites et providers du calendrier normal decrits ci-dessous
restent conserves ; ils ne dependent pas de ces experiences.

## Boucle native conservee

Carte Section manuelle dans Divi ; seule la programmation est repetee. Aucun
contexte parent prive, aucun ID Programme code dans WPSCK, aucune carte HTML
generee. Le shortcode et la Structure pedagogique restent inchanges.

Collection PHP : `wp_seed_content_formation_get_schedules`. Filtres Programme,
annee, Section, Theme, statut, intervenant ; tri start_date/display_order ASC/DESC.
Le shortcode conserve cancelled exclu par defaut. L'adaptateur Divi inclut tous
les statuts sauf filtre explicite, afin que le designer puisse les afficher.
Les relations simples sont filtrees en SQL et prechargees en lot. Les autres
clauses natives sont conservees sans reinterpretation ; pas de cache persistant
susceptible de masquer une mise a jour.

## Configuration native explicite

Activer la Loop du conteneur d'une programmation, CPT Programmations annuelles
(`seed_schedule`). Configurer Meta Query native, AND, comparaison = :

| Filtre | Cle | Type |
| --- | --- | --- |
| Programme | `_seed_formation_program_id` | NUMERIC |
| Annee | `_seed_formation_calendar_year` | NUMERIC |
| Section | `_seed_formation_section_id` | NUMERIC |
| Theme facultatif | `_seed_formation_theme_id` | NUMERIC |
| Statut facultatif | `_seed_formation_status` | CHAR |

Statuts : planned/postponed/cancelled. Les filtres Meta Query natifs restent
disponibles. Le panneau optionnel Calendrier WPSCK, decrit plus bas, propose
les memes dimensions explicites avec des listes lisibles Programme/Section.
Tris : Date de programmation (`wpsck_schedule_date`) / Ordre de programmation
(`wpsck_schedule_order`) ; sens natif ASC/DESC. Limites et exclusions Divi preservees.

## Champs scalaires

Prefixe Divi : `loop_wpsck_formation_schedule_`.
Prefixe portable Content Data/Block Bindings : `formation.schedule.`.

| Suffixe | Valeur |
| --- | --- |
| id | ID Schedule |
| date | Plage de dates formatee, format commun au shortcode |
| start_date / end_date | Dates ISO |
| title | Intitule resolu : annuel, snapshot, Theme |
| annual_display_title | Surcharge annuelle brute, vide possible |
| theme | Titre canonique memorise, fallback Theme |
| section | Libelle Section memorise |
| program | Titre Programme public resolu |
| people | Noms visibles, separes par virgules |
| status | Statut traduit |
| year / order | Annee / ordre |
| url | Vide, aucune destination publique actuellement |

people/person_ids restent aussi dans la projection canonique. Aucune nouvelle
personne ou URL n'est inventee. Les balises et tout le style appartiennent a Divi.

## Integration et isolation

API DynamicContentOptionBase/Interface et DynamicContentElements ; providers
loop_* et augmentation QueryResults commune existante.
Hooks `divi_module_options_loop_post_type_results_query_args`,
`divi_loop_data_after_execution`, `et_builder_third_party_post_types`,
`et_builder_loop_order_by_options_seed_schedule`.

Le CPT ne devient pas une archive publique. L'ouverture/restauration temporaire
de publicly_queryable se limite a la requete REST Divi seed_schedule, comme dans
les adaptateurs existants. Aucun filtre n'est applique aux autres CPT.
Module desactive : discovery masquee, requete vide, binding connu vide ; aucune
suppression de donnees. Le registre portable reste stable.

Reference publique : https://dev.elegantthemes.com/docs/tutorials/module/advanced/extending-loop/

## Validation et historique

Les anciennes etapes de deploiement, de creation de page TEMP et de validation
du Full Dynamic ne sont plus a executer. Les prototypes 4989, 5007 et 5154 ont
ete supprimes ; 4462 reste explicitement conservee. Le rapport final reference
en tete documente le calendrier normal et les donnees reelles preservees :
71 programmations, 35 en 2026 et 36 en 2027, dont sept Supervisions par annee.
Aucune mise en page particuliere n'est imposee par ces donnees.

## Conjonction des filtres Meta conservee (origine V3.5)

The installed Divi LoopUtils::build_meta_query implementation converts flat
Meta rows to WP_Query clauses and assigns OR when there is more than one row.
Program + year + section therefore matched every schedule in the program.
The values were not missing: the boolean combination was wrong for the calendar
dimensions. WPSCK previously preserved OR and did not narrow the collection.

The shared Schedule query adapter now treats distinct scalar equality clauses
on program_id, calendar_year, section_id, theme_id and status as conjunctive
dimensions. This is scoped to a single seed_schedule post type. An existing
flat OR containing only these unique dimensions becomes AND before canonical
collection selection and before Divi executes WP_Query. Metadata values/types
are preserved; the existing collection contract performs value normalization.

No nested group, repeated dimension, foreign metadata key, array value or
non-equality operator is reinterpreted. Such complex OR expressions retain their
original semantics. No external CPT or Divi native provider is changed.
This intentionally narrow contract is not a general-purpose OR-to-AND rewrite.

Public hooks remain divi_module_options_loop_post_type_results_query_args and
divi_loop_data_after_execution. Despite the latter's name, Divi calls it before
execute_query in both normal and predictive paths. No private class invocation,
SQL replacement, frontend masking or page mutation is required.

Regression: formation-divi-calendar-harness.php reproduces native OR input,
individual dimensions, unsupported-query preservation and idempotence.
wordpress-formation-divi-calendar-harness.php verifies OR normalization, SQL
selection, a foreign-program empty result, date changes within the same card,
module discovery toggling and transactional fixture cleanup.

## Filtres natifs lisibles conserves (origine V3.7)

On the native Schedule Loop owner, open Content > Calendrier WPSCK and enable
Utiliser les filtres Calendrier. Select Programme, Annee, Section, Theme,
Statut and Tri by their labels. IDs remain internal. Sections are restricted to
the selected program; changing program clears the previous section selection.
This group is available on native section, row, column and group containers.
Its query behavior applies only to a single seed_schedule Loop.

Public extension points: divi.fieldLibrary.registerFieldComponent,
divi.moduleLibrary.moduleSettings.groups, divi.moduleLibrary.moduleAttributes,
divi.module.layout.childModule.loop.resultsQueryParams and the existing PHP
Loop query hooks. There is no DOM inference, private store mutation or external
provider filtering. Select fields use Divi's inputValue change payload.

Configuration is stored in the builder-owned attribute
wpsckCalendarFilters.innerContent.desktop.value. It is not Formation business
data. Program, year, section and theme remain canonical IDs/values; status and
sorting retain their canonical contracts. The server verifies referenced
objects and section/program ownership and fails closed on invalid input.

This is explicit opt-in: absent/disabled configuration preserves legacy Meta
filters. When enabled, readable dimensions replace only simple recognized
calendar equality clauses. Other/nested/complex clauses remain constraints.
Legacy page attributes and metadata keys are not deleted or migrated. To use
the old technical Meta interface again, disable the readable filter mode.

Both the builder request and frontend query use the same normalization and
canonical collection. The field script is builder-only and module-aware.
No frontend CSS or JavaScript is introduced by this integration.

Regression coverage: formation-divi-calendar-harness.php (129 assertions) and
formation-calendar-filters-harness.js (15 assertions). Real DEV persistence,
filter changes and responsive checks are recorded as historical evidence in
DYNAMIC-CALENDAR-DIVI-V3-7-REPORT.md. That report does not authorize recreating
the removed prototype or restoring Full Dynamic functionality.
