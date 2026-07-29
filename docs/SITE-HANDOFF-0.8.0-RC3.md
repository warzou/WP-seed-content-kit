# Handoff site — WP Seed Content Kit 0.8.0-rc.3

Statut : préparé, à ne transmettre qu’après publication officielle de la préversion.

## Prompt de reprise

Projet consommateur exclusif : dépôt `psychotherapiedeletre-site`.

Artefact requis : ZIP officiel WP Seed Content Kit 0.8.0-rc.3, accompagné de son tag, de son commit, de sa taille et de son SHA-256 publiés. Ne consommer aucun worktree ou build local.

Avant toute installation, vérifier l’état Git du site, l’artefact et son checksum, puis créer une sauvegarde autonome hors web. Installer atomiquement l’artefact officiel sans modifier ses fichiers.

Auditer ensuite le contrat Annuaire publié :

- `directory.summary` provient de `post_excerpt` ;
- `directory.bio` est son alias strict ;
- `directory.full_presentation` provient de `post_content` ;
- `_seed_directory_publicly_listed=1` est nécessaire à toute Collection publique ;
- aucune sélection par ID ne contourne cette exclusion ;
- le statut WordPress, les types, Recherche de modèles, le consentement et les coordonnées restent indépendants.

Contrôler la migration RC.2 avant/après : seules les fiches Annuaire déjà publiées, non protégées et sans valeur explicite peuvent recevoir la valeur `1`. Les brouillons, fiches privées/protégées et valeurs explicites doivent rester inchangés. Ne créer aucune méta locale ni filtre dans le thème.

Reprendre la recette candidate des pages Intervenants et Praticiens uniquement avec les Collections, Templates et providers officiels. Vérifier frontend, Visual Builder, responsive, zoom 200 %, clavier, confidentialité, vingt profils réels et rollback. Ne publier aucune page et ne supprimer aucun module historique avant validation humaine distincte.

Dans Divi 5, remplacer le shortcode de recette par le module officiel `WP Seed — Annuaire`. Configurer les mêmes filtres que la Collection validée et comparer les IDs, l’ordre, les cartes, `summary`, `bio`, `full_presentation`, les types, Recherche de modèles, photos et coordonnées autorisées entre frontend et Visual Builder.

Vérifier que le module se recharge immédiatement après un changement de filtre, limite, ordre ou Template, puis après sauvegarde et réouverture. La route privée `/wp-seed-content-kit/v1/divi/directory-preview` doit refuser toute session absente, nonce invalide ou utilisateur sans `edit_pages`.

Tester explicitement une fiche non listée et un brouillon demandés par ID : ils doivent rester absents. Vérifier aussi le fallback local d’une carte lorsque le Template est absent, vide ou en erreur.

Ne pas utiliser le Loop Builder natif Divi, ne pas ajouter de CSS ou JavaScript au site et ne pas copier de logique de sélection dans le thème. Conserver les anciennes structures masquées jusqu’à validation humaine de la nouvelle Collection.

## Contrôle spécifique RC.3

Comparer une Collection utilisant simultanément `ids` et `exclude_ids` sur shortcode, module Divi, frontend et Visual Builder. Toute fiche présente dans les deux listes doit être absente. Vérifier une intersection partielle, une intersection totale, l'offset et la limite après exclusion. La migration publique introduite en RC.2 reste inchangée; le passage RC.2 vers RC.3 est un remplacement de code sans transformation métier ni migration de données.