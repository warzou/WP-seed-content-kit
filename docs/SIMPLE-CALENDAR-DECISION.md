# Full Dynamic Calendar abandonne

Decision produit du 2026-09-08.

Le module Divi Calendar dedie, la discovery Programme/Annee, les Child Modules
experimentaux et Annual Planning N vers N+1 sont abandonnes. Leur complexite
et leur cout sont disproportionnes; le rendu dedie limite la liberte Divi.

WPSCK conserve les donnees, l'administration et le calendrier normal.
Divi conserve la presentation. Pour une nouvelle annee, dupliquer manuellement
la section Divi precedente et adapter ses filtres explicites et ses dates.
Les dates canoniques des Programmations restent administrees dans WPSCK.

Conserver les Programmes, Sections, Themes, Schedules, projections Structure,
Grouped, Chronologique, Next Date et champs dynamiques individuels.
La multiplicite est metier: Programme + Annee + Theme + Section + debut + fin,
avec exclusion de l'ID courant et verrou concurrentiel. Elle reste obligatoire
pour les sept Supervisions de chacune des annees 2026 et 2027.

Les rapports V4/V5 anterieurs constituent uniquement des preuves historiques,
pas des fonctionnalites disponibles. Aucun baseline historique n'est remplace.
Le retrait local ne constitue pas une preuve de deploiement DEV.

## Initial execution status (historical)

The later deployment and remaining page-deletion approval block are recorded
in [the final rollback report](SIMPLE-CALENDAR-ROLLBACK-REPORT.md).

Local cleanup completed on 2026-09-08. Local backup outside the repository:
wpsck-pre-simple-calendar-cleanup-20260908.tar.gz, SHA256
f769a1155f69f5b96f5939eefc7f6795145494a6c398330b6a58d4429d08b9ad.

Retained Formation harnesses pass PHP 7.0 and 8.4: foundation 18, model 16,
backend 82, UX 28, Divi adapter 129 assertions; multiplicity harness passes.
Divi filter JS: 15 assertions pass. Plugin PHP lint: 150 files on both versions.

DEV authenticated static preflight: HTTP 200. Read-only SQL comparison with
the prior observation finds 4089 metadata rows versus 4096: IDs 49854-49860
remain absent (Divi caches on posts 5154, 1901, 2489, 4219). No other value
change detected by that comparison. Historical baseline remains untouched.

Remote phase stopped before backup, deployment, transactional fixtures or
page deletion, pending qualification of that existing cache divergence.
5007 and 5154 were not deleted; no new TEMP page was created.
No WordPress mutation, no remote helper upload and no release operation.
