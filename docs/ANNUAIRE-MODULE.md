# Module Annuaire

Statut : Release Candidate 0.8.0-rc.2 du module Annuaire natif, en validation et non stable.

## Périmètre

Annuaire est le module natif directory, actif par défaut et désactivable. Son CPT seed_directory reste strictement administratif : aucune archive, page individuelle, recherche publique, route REST ou entrée de sitemap.

L3 fournit les champs, administration et eligibilite. L4 ajoute la Data API publique, les Collections, les shortcodes, le rendu natif et les Templates Content Kit. Il ne cree ni recherche, single, archive, import métier automatique ou adaptateur avec WP Seed Directory.

## Champs

Les champs WordPress natifs sont le titre (nom), l’image mise en avant (photo), son texte alternatif, le résumé (présentation) et menu_order.

Les vingt-deux métas privées sont :

- _seed_directory_status, _seed_directory_profile_types, _seed_directory_seeking_models, _seed_directory_publicly_listed, _seed_directory_city, _seed_directory_postal_code, _seed_directory_department, _seed_directory_country, _seed_directory_featured ;
- _seed_directory_phone, _seed_directory_email, _seed_directory_website, _seed_directory_facebook, _seed_directory_instagram et leurs cinq indicateurs _visible ;
- _seed_directory_publication_authorized, _seed_directory_internal_note, _seed_directory_last_verified.

Les booléens vrais sont stockés sous la forme 1 ; une valeur fausse est supprimée. Le pays absent vaut FR. Les statuts autorisés sont practicing et seeking_models. Codes postaux et départements restent des chaînes afin de préserver les zéros initiaux, 2A et 2B.

Les e-mails et dates sont validés strictement. Les URLs doivent être absolues en HTTP(S). Facebook et Instagram n’acceptent que leur domaine et ses sous-domaines. Le téléphone conserve une forme lisible, sans HTML.

## Administration

Quatre panneaux structurent la saisie sans exposer les métadonnées techniques :

1. Identité : nom affiché, statut et mise en avant.
2. Localisation, présentation et photo : ville, code postal, département, pays, présentation courte et alt.
3. Coordonnées : valeur et case d’affichage visuellement liées pour téléphone, e-mail, site, Facebook et Instagram.
4. Autorisation et suivi : autorisation explicite, note interne facultative et dernière vérification facultative.

Le panneau Photo natif porte l’image. La photo est facultative ; si elle existe, elle doit être une pièce jointe image avec URL HTTP(S) et texte alternatif non vide avant publication. Aucun alt ni média par défaut n’est généré. Le pays vaut FR par défaut ; statut, autorisation et cinq visibilités restent explicitement non sélectionnés à la création.

La liste affiche photo, nom, statut, ville, département, autorisation, types de coordonnées publiques, état WordPress et modification. Elle ne montre jamais la valeur d’une coordonnée, une note interne ou l’ordre technique. Le filtre de statut reste réservé à l’administration. Quick Edit et la publication en masse sont neutralisés ; corbeille et restauration restent disponibles.
## Publication et confidentialité

wp_seed_content_directory_is_publicly_eligible( $post_id ) retourne vrai uniquement pour une fiche publiée, non protégée par mot de passe, autorisée, nommée, avec statut et pays valides et, le cas échéant, photo et alt valides.

Une publication invalide est ramenée en brouillon. La défense combine validation avant écriture, contrôle après écriture ou transition et prédicat final. Retirer l’autorisation via le formulaire admin rend immédiatement la fiche inéligible et la ramène en brouillon.

wp_seed_content_directory_get_public_contacts() ne retourne que les contacts valides, explicitement visibles et rattachés à une fiche éligible. Une valeur peut rester enregistrée à titre privé dans un brouillon. Dès que sa visibilité est cochée, une valeur vide ou invalide bloque la publication et aucune coordonnée de la fiche inéligible n’est exposée.

wp_seed_content_directory_get_admin_data() exige edit_seed_directory_entry pour la fiche et retourne alors les champs complets, y compris contacts privés, autorisation, note et date de vérification. Cette fonction n’est ni une API REST ni la Data API publique prévue pour L4.

## Capacités et cycle de vie

Les quatre capacités primitives restent :

- edit_seed_directory_entries ;
- publish_seed_directory_entries ;
- read_private_seed_directory_entries ;
- delete_seed_directory_entries.

Administrator et Editor les reçoivent par défaut. Configuration peut retirer l’attribution Editor sans affecter Administrator. Désactiver le module retire le CPT et son menu, sans supprimer posts, médias, métas ou capacités. La réactivation retrouve les données et les attributions conservées.

Les révisions natives couvrent le titre et la présentation. Les métas métier restent attachées à la fiche courante ; elles ne sont pas dupliquées dans une sortie publique et toute restauration repasse par la garde d’éligibilité.

## Sortie publique L4

wp_seed_content_directory_get_public_data($post_id) retourne uniquement le schéma fermé id, name, photo, bio, status, status_label, profile_types, profile_type_labels, profile_types_label, seeking_models, seeking_models_label, location, featured, display_order et contacts. Une fiche ineligible retourne false. Les contacts absents, invalides ou masques ne figurent pas dans le tableau.

wp_seed_content_directory_get_entries($args) retourne des IDs eligibles. Les filtres sont status (compatibilité), profile_type, profile_types, profile_type_operator, seeking_models, department, country, featured, ids, exclude_ids, offset, limit, orderby et order. Ordre display_order : ordre manuel, nom normalise sans distinction de casse ou d'accent, puis ID. Aucun ID explicite ne contourne eligibilite.

[seed_directory] accepte les memes attributs et template. [wp_seed_directory] est un alias temporaire deprecie, sans avertissement public. Les valeurs invalides retournent une chaine vide. Les groupes restent, dans cet ordre, En exercice puis En recherche de modeles ; un groupe vide est omis.

La carte native affiche uniquement la photo lorsqu'elle existe, puis le nom, le statut, la localisation, la presentation et les contacts publics. Sans photo, aucun emplacement, placeholder ou hauteur media n'est rendu : la carte commence directement par son contenu. La grille semantique `ul`/`li` reste en 3/2/1 colonnes et neutralise explicitement les marqueurs imposes par les themes. Le CSS structurel est charge seulement avec des fiches rendues et le CSS de carte seulement avec une carte native.

## Templates et confidentialite

Le module Template directory expose exactement vingt et un placeholders directory.*, du nom a directory.featured. Leur contexte provient exclusivement de la Data API publique. Un Template publie personnalise une carte ; les classes opt-in `wp-seed-directory-template-card__*` permettent a une fixture ou a un integrateur d'adopter les espacements de la carte native sans imposer ce design aux autres Templates. Un Layout Divi reste responsable de ses propres espacements. Template absent, brouillon, mauvais module, resultat vide, recursion, erreur, contexte ou assets invalides declenchent un fallback natif pour cette fiche seulement.

Gutenberg utilise le bloc Shortcode. Divi accepte le shortcode dans Texte ou Code et peut rendre un Layout Divi Library via un Template Content Kit. Aucun bloc ou module Divi specifique est cree.

Module desactive : shortcode vide, Collection vide, aucun asset et aucune exposition ; les donnees sont conservees. La RC exclut recherche ou filtres visibles, fiche individuelle, archive, REST/AJAX, migration et cache persistant. Le plugin Directory autonome reste une reference comparative externe, sans couplage ni migration automatique.

## Guidage Utilisation CK-A4

L’onglet Collections documente les types de profil, leur opérateur OR/AND, le statut temporaire, les sélections et exclusions, la pagination, le tri par défaut et l’état vide. Le générateur produit uniquement un shortcode [seed_directory] à copier et peut lui ajouter un slug de Template. Il ne sauvegarde ni Collection ni association Template/Collection.

L’onglet Templates décrit exactement les vingt et un placeholders directory.*. Ils proviennent tous de la projection publique fermée. Les cinq placeholders de contact sont disponibles uniquement lorsque la valeur est valide et explicitement rendue publique ; aucune donnée privée n’est proposée. Sans attribut template, ou lorsqu’un Template demandé est inutilisable, la carte native reste le fallback local.

Gutenberg utilise le bloc Shortcode Core. Spectra intègre indirectement ce bloc ou le contenu d’un Template. Divi utilise un module Texte ou Code et peut fournir un Layout Divi Library à un Template. Il n’existe ni provider Spectra natif, ni module Divi propriétaire, ni filtre public visible.

## Migration fictive CK-A6

Le module inclut une API interne de migration documentee dans `ANNUAIRE-MIGRATION.md`. Elle cible exclusivement le CPT et les 21 metas natives, ne charge aucun code de WP Seed Directory et ne déclenche jamais cet import fictif au runtime. La mise à niveau de schéma 0.8.0-rc.1 est séparée, additive et idempotente. Les references, hashes, notes et registres techniques restent prives et ne traversent aucune couche publique.

## Profils multi-usages — 0.8.0-rc.1

### Deux facettes indépendantes

`_seed_directory_profile_types` contient un tableau ordonné et normalisé de zéro, un ou deux slugs : `praticien`, `intervenant`. `_seed_directory_seeking_models` vaut `1` uniquement lorsque la recherche de modèles est active ; la valeur inactive est absente.

Le premier champ décrit un type durable. Le second décrit une situation temporaire. La V1 n'ajoute ni annonce, date d'expiration, discipline, quota, candidature ou coordonnées supplémentaires.

### Compatibilité

Une fiche sans type reste éligible dans l'Annuaire complet. Un filtre `profile_type` ou `profile_types` l'exclut. La migration additive copie uniquement l'ancien statut `seeking_models` vers le booléen et ne classe jamais automatiquement une ancienne fiche comme praticien.

### Administration et publication

Le panneau « Profil dans l'annuaire » accepte plusieurs types et aucun type obligatoire. Son marqueur de présence protège les deux nouvelles métadonnées lors d'une sauvegarde partielle. Nonce, capacité objet, autosave et révisions utilisent les gardes de l'éditeur Annuaire existant. Les règles d'autorisation de publication et de contacts publics ne changent pas.

### Contrat public

La Data API ajoute `profile_types`, `profile_type_labels`, `profile_types_label`, `seeking_models` et `seeking_models_label`, uniquement après le prédicat d'éligibilité. Les Templates ajoutent `directory.profile_types`, `directory.profile_type_slugs`, `directory.seeking_models` et `directory.seeking_models_active`. Les valeurs absentes sont des tableaux, booléens ou chaînes vides propres, jamais une sérialisation PHP.

## Présentation complète et visibilité publique — 0.8.0-rc.2

Le titre reste le nom. `post_excerpt` est la présentation courte et devient `summary`; `bio` est son alias strict. `post_content` est la présentation complète et devient `full_presentation` après rendu WordPress, suppression des shortcodes bruts non résolus et filtrage HTML public. L’absence de contenu produit une chaîne vide propre.

`_seed_directory_publicly_listed` est la vingt-deuxième méta métier privée. Seule la chaîne exacte `1` signifie vrai; faux est représenté par l’absence de méta. La case d’administration agit uniquement sur cette méta. Elle ne publie ni ne dépublie la fiche et ne modifie aucune autre donnée. Une sauvegarde partielle qui ne contient pas le panneau conserve la valeur existante.

L’éligibilité publique exige désormais simultanément le statut WordPress publié, l’absence de mot de passe, l’autorisation existante, la validité métier et la visibilité publique explicite. Collections, shortcode, cartes natives, Templates et contexte Divi utilisent tous ce même prédicat fermé; ni `ids` ni un autre filtre public ne le contourne.

Les Templates exposent `directory.summary`, `directory.bio` et `directory.full_presentation`. Le Layout du site choisit d’afficher ou non la présentation complète. Aucun champ de visibilité n’est exposé comme donnée utile : toute fiche rendue publiquement est déjà explicitement listée.

## Module Divi 5 officiel

Le module « WP Seed — Annuaire » est la surface Divi 5 officielle pour une Collection Annuaire. Il configure :

- statut public ;
- types Praticien et Intervenant, avec opérateur OR ou AND ;
- Recherche de modèles ;
- département, pays et mise en avant ;
- IDs inclus et exclus ;
- limite, offset, critère et direction de tri ;
- Template Content Kit facultatif.

Le shortcode historique et le module partagent `wp_seed_content_render_directory_collection()`. Le Visual Builder appelle le même renderer par une route serveur authentifiée. Les IDs, l’ordre, les trois présentations, les types, le statut, la photo et les coordonnées publiques autorisées doivent donc rester identiques au frontend.

La route est `GET /wp-seed-content-kit/v1/divi/directory-preview`. Elle exige le nonce WordPress REST, la capacité `edit_pages`, n’est enregistrée qu’avec Divi 5 et désactive tout cache public. Elle ne crée, ne modifie et ne publie aucune donnée.

Le contrôle `_seed_directory_publicly_listed=1` reste obligatoire avant toute sélection. Un ID explicite ne contourne ni ce contrôle, ni le statut publié, le mot de passe vide, l’autorisation ou la validation métier.

Le fallback d’un Template absent, brouillon, vide ou en erreur reste natif et limité à la carte concernée. Les autres cartes continuent leur rendu normal.

Le module n’est pas un adaptateur Loop Builder. Il n’utilise aucun shortcode dans le canevas, aucune observation du DOM et aucune API interne Divi non contractuelle.
