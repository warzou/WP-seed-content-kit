# Rôles et capacités

## Principe

WP Seed Content Kit sépare les contenus éditoriaux de l’architecture du site. Administrator configure les modules, Templates, Collections, intégrations et droits. Editor alimente les contenus des modules qui lui sont attribués, sans accéder aux réglages avancés.

La visibilité d’un menu n’accorde jamais une autorisation. Chaque écran et chaque CPT vérifie une capacité WordPress réelle.

## Capacités de contenu

Chaque module natif utilise quatre capacités primitives. Elles couvrent aussi les contenus créés par d’autres utilisateurs grâce au mapping `edit_others_posts` et `delete_others_posts` du CPT.

| Module | Modifier/créer | Publier | Lire le privé | Supprimer |
| --- | --- | --- | --- | --- |
| Témoignages | `edit_seed_testimonials` | `publish_seed_testimonials` | `read_private_seed_testimonials` | `delete_seed_testimonials` |
| Citations | `edit_seed_quotes` | `publish_seed_quotes` | `read_private_seed_quotes` | `delete_seed_quotes` |
| Annuaire | `edit_seed_directory_entries` | `publish_seed_directory_entries` | `read_private_seed_directory_entries` | `delete_seed_directory_entries` |

Par défaut, Administrator et Editor reçoivent ces capacités. Configuration permet à Administrator de retirer ou rétablir l’attribution Editor pour chaque module. Administrator reste toujours autorisé.

## Capacités avancées

Les capacités suivantes sont réservées par défaut à Administrator :

- `manage_wp_seed_content_kit` : Configuration et activation des modules ;
- `manage_wp_seed_templates` : création et gestion des Templates ;
- `manage_wp_seed_collections` : architecture des Collections ;
- `manage_wp_seed_integrations` : page Utilisation et intégrations ;
- `manage_wp_seed_roles` : attribution des droits ;
- `manage_wp_seed_imports` : imports, migrations et maintenance associée.

Editor ne reçoit aucune de ces capacités. Le CPT `seed_template` utilise exclusivement `manage_wp_seed_templates` pour ses opérations.

## Menus et cycle de vie

Un module actif apparaît seulement si l’utilisateur possède sa capacité de modification. Editor voit les listes de contenus et les actions Ajouter des modules autorisés. Configuration, Utilisation et Templates restent absents de son interface.

Désactiver un module retire son CPT et ses menus pour la requête courante. Cette opération ne supprime ni contenu, ni méta, ni média, ni attribution de rôle. La réactivation restaure le CPT et applique la configuration de capacités conservée.

## WP Seed Events

WP Seed Events est un plugin autonome. Au commit audité `dfbb66dfa8745350dece988f23ab856fde14d4ed`, son CPT `wp_seed_event` utilise les capacités WordPress standard du type `post`; Editor peut donc gérer les événements selon les droits éditoriaux WordPress habituels, y compris les contenus d’autres auteurs. Ses pages Paramètres et Affichage utilisent `manage_options` et restent réservées à Administrator.

Les sous-menus métier actuels Types, Personnes et Lieux utilisent également `edit_posts` et sont donc visibles à Editor. La surface cible simplifiée « Tous les événements / Ajouter » exige un lot séparé dans WP Seed Events : le point d’application réel se trouve dans `wp_seed_events_register_event_post_type()` et `wp_seed_events_register_plugin_admin_menu()` de son fichier principal.

Content Kit ne charge, ne modifie et ne synchronise aucune capacité Events. Une harmonisation future doit être définie dans WP Seed Events ou par un contrat public commun explicitement approuvé. CK-A2 ne fusionne pas Events et n’ajoute aucune dépendance inter-plugin.

## Compatibilité

La synchronisation est effectuée à l’activation et une fois lors de la mise à niveau du schéma de capacités. Elle ne crée aucun contenu, endpoint REST ou action AJAX. Les options de modules et de position de menu existantes sont conservées.
## Parcours Annuaire CK-A3

Editor voit Annuaire, Toutes les personnes et Ajouter une personne lorsque le module lui est attribué. Il peut créer, enregistrer un brouillon incomplet, publier une personne valide, modifier les contenus d’autres éditeurs, dépublier, mettre à la corbeille et restaurer. Toute publication repasse par la validation canonique, y compris hors du formulaire principal.

Administrator utilise exactement la même fiche métier et conserve en plus les écrans avancés autorisés par les capacités `manage_wp_seed_*`. Ni la fiche ni la liste Annuaire n’exposent les noms de métadonnées, les indicateurs internes de visibilité, les imports, migrations ou capacités.

## Imports Annuaire CK-A6

`manage_wp_seed_imports` protege les appels internes d'import et de rollback en plus d'un contexte explicite. Administrator possede cette capacite avancee. Editor ne la recoit pas, ne voit aucun outil de migration et un appel direct est refuse avant toute ecriture. CK-A6 n'ajoute aucun ecran ou endpoint.
