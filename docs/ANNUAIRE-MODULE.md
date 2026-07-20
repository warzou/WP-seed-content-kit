# Module Annuaire

Statut : socle L2 en développement dans WP Seed Content Kit `0.6.0-dev`.

## Périmètre L2

Annuaire est un module natif de Content Kit :

- identifiant de module : `directory` ;
- libellé : Annuaire ;
- CPT : `seed_directory` ;
- menu : `Content Kit > Annuaire` ;
- actif par défaut et désactivable depuis la configuration des modules.

Le CPT est strictement administratif. Il n’est pas public, requêtable, indexé par la recherche ou les sitemaps, exposé dans REST, associé à une archive ou à une page individuelle.

## Capacités

WordPress conserve les trois métacapacités objet habituelles. Les opérations sont ramenées à quatre capacités primitives :

- `edit_seed_directory_entries` ;
- `publish_seed_directory_entries` ;
- `read_private_seed_directory_entries` ;
- `delete_seed_directory_entries`.

Seul le rôle `administrator` reçoit ces primitives à l’activation. Elles ne sont pas retirées lors d’une désactivation du module, conformément au cycle non destructif de Content Kit.

## Activation et désactivation

Quand Annuaire est actif, le CPT et le sous-menu sont enregistrés. Quand il est désactivé, ils disparaissent, mais aucun post, média ou droit n’est supprimé. La réactivation retrouve les données existantes.

Quick Edit et Bulk Edit sont retirés en L2. La corbeille et la suppression groupée restent soumises aux capacités.

## Hors périmètre

L2 ne fournit pas encore :

- les champs métier et panneaux Annuaire ;
- l’autorisation de publication ;
- la Data API et les Collections ;
- `[seed_directory]` ou `[wp_seed_directory]` ;
- le renderer, le CSS et les templates Annuaire ;
- une migration ou un adaptateur avec WP Seed Directory.

Ces éléments relèvent des lots L3 et L4. WP Seed Directory reste intact comme prototype de référence temporaire.
