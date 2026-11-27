# Parcours & calendrier

## Expérience d’administration

Le menu `Parcours & calendrier` regroupe `Programmes`, `Thèmes` et `Calendrier annuel`. L’écran Programme permet de nommer, monter, descendre, ajouter et supprimer les Sections. Une Section référencée par une programmation ne peut pas être supprimée. Le calendrier annuel regroupe les programmations par Section et affiche les intervenants, le statut ainsi que les dates originales d’un report.

Quand un seul Programme existe, le calendrier l’ouvre directement sur la première année disponible égale ou postérieure à l’année courante. À défaut, il utilise la dernière année disponible, puis l’année courante si aucune programmation n’existe. Le filtre reste libre : une autre année, y compris vide, peut toujours être saisie explicitement.

## Rendu natif

Le shortcode accepte `mode="structure"`, `mode="grouped"` ou `mode="calendar"`. Son attribut facultatif `heading_level` fixe le premier niveau de titre généré entre 2 et 6. Le fallback utilise `section`, `article`, `ol` et `ul`, avec une mise en page responsive structurelle sans palette éditoriale. Les builders peuvent ignorer ce rendu et consommer les mêmes Collections, Content Data et Block Bindings.

## Statut

Le module interne formation est désactivé par défaut. Son nom utilisateur est **Parcours & calendrier**. Le désactiver retire ses écrans et ses nouveaux champs des interfaces de découverte sans supprimer les contenus ni les identifiants de bindings persistés.

## Entités

- seed_program : titre, résumé, description et ordre.
- seed_theme : nom, résumé, description, image mise en avant et ordre.
- seed_section : entité technique rattachée à un Programme.
- seed_schedule : programmation annuelle technique, éditée depuis **Calendrier annuel**.

Les Sections et Programmations n'ont ni menu WordPress autonome, ni URL publique.

## Programmation annuelle

Les métas canoniques portent les relations Programme, Thème, Section, l'année civile, les dates, le statut, les dates originales, l'intitulé annuel, les personnes Directory et les snapshots historiques.

Les dates utilisent YYYY-MM-DD; l'année civile doit être celle de la date de début et la fin ne peut précéder le début. Les relations sont validées par CPT. Une Section doit appartenir au Programme choisi. Les personnes référencent uniquement seed_directory et conservent leur ordre.

L'unicité V1 porte sur (program_id, calendar_year, theme_id, section_id, start_date, end_date) parmi les programmations non placées à la corbeille. Un même Thème peut donc avoir plusieurs dates dans une même Section et année. Une modification exclut son propre ID. Le libellé annuel, les personnes et le statut ne rendent pas un doublon exact acceptable. Les statuts sont planned, postponed et cancelled. Une programmation annulée reste historisée, réserve son identité datée et est exclue par défaut des collections publiques.

La sauvegarde WPSCK sérialise la vérification et les écritures par un verrou MySQL nommé, propre à la base et au préfixe WordPress. Attente maximale : 5 secondes ; verrou indisponible : refus sans écriture. Le contrôle d'existence désactive le cache de requête. Le verrou est libéré dans finally. Ce n'est ni un index SQL unique ni une transaction de toutes les métas : les écritures SQL externes, restaurations externes et changements directs de métas ne sont pas couverts. Un import entourant les appels d'une transaction doit conserver le même verrou jusqu'au commit/rollback externe. Les coupures/reconnexions DB ne constituent pas une garantie transactionnelle. Aucun schéma ni migration de données ajouté.

Au premier passage en publication, le titre du Thème, le libellé de Section et son ordre sont figés. Au premier passage de planned vers postponed, les dates antérieures sont copiées une seule fois.

L'intitulé affiché suit : intitulé annuel, snapshot du Thème, titre canonique actuel.

## API, builders et rendu

Les projections HTML-free et collections couvrent Programme, Thème, Section, calendrier chronologique, calendrier groupé, structure sans dates, filtres et prochaine programmation par Section. Le tri chronologique est start_date, end_date, menu_order, ID. Le tri groupé utilise section_order_snapshot, puis menu_order, date et ID.

La Structure conserve un Thème par Programme/année/Section, avec le plus petit ordre puis ID comme représentant historique. Son champ id reste un ID de programmation ; theme_id reste distinct. Les calendriers ne dédupliquent jamais les programmations. La prochaine date utilise la fin : une séance encore en cours reste admissible ; les annulées sont exclues par défaut. Deux journées discontinues sont deux programmations d'une journée, pas une plage continue ni une nouvelle entité Occurrence.

Les champs formation.program.*, formation.theme.*, formation.section.* et formation.schedule.* utilisent le registre Dynamic Data partagé et Gutenberg Block Bindings. Spectra peut consommer les mêmes bindings; Astra reste propriétaire de la présentation.

Le shortcode unique [seed_program_schedule] accepte program, year et les modes structure, grouped et calendar. Son rendu est sémantique, monocolonne et sans design métier.

Une source Native Loop Divi dédiée n'est pas ajoutée tant qu'elle exigerait une API privée ou une heuristique de picker.

## Hors V1

- intégration ou agrégation WP Seed Events;
- table SQL et occurrences;
- rôles ou créneaux par intervenant;
- sous-interventions;
- pages publiques Programme/Thème;
- design métier ou données propres à Divi.

Un futur agrégateur peut normaliser start_date, end_date, display_title, status, source_type, source_id, url, category et section_label sans modifier ce stockage.
