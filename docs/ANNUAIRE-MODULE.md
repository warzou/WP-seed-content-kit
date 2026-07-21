# Module Annuaire

Statut : candidat de recette 0.6.0-rc.1 du module Annuaire natif.

## Périmètre

Annuaire est le module natif directory, actif par défaut et désactivable. Son CPT seed_directory reste strictement administratif : aucune archive, page individuelle, recherche publique, route REST ou entrée de sitemap.

L3 fournit les champs, administration et eligibilite. L4 ajoute la Data API publique, les Collections, les shortcodes, le rendu natif et les Templates Content Kit. Il ne cree ni recherche, single, archive, migration runtime ou adaptateur avec WP Seed Directory.

## Champs

Les champs WordPress natifs sont le titre (nom), l’image mise en avant (photo), son texte alternatif, le résumé (présentation) et menu_order.

Les dix-neuf métas privées sont :

- _seed_directory_status, _seed_directory_city, _seed_directory_postal_code, _seed_directory_department, _seed_directory_country, _seed_directory_featured ;
- _seed_directory_phone, _seed_directory_email, _seed_directory_website, _seed_directory_facebook, _seed_directory_instagram et leurs cinq indicateurs _visible ;
- _seed_directory_publication_authorized, _seed_directory_internal_note, _seed_directory_last_verified.

Les booléens vrais sont stockés sous la forme 1 ; une valeur fausse est supprimée. Le pays absent vaut FR. Les statuts autorisés sont practicing et seeking_models. Codes postaux et départements restent des chaînes afin de préserver les zéros initiaux, 2A et 2B.

Les e-mails et dates sont validés strictement. Les URLs doivent être absolues en HTTP(S). Facebook et Instagram n’acceptent que leur domaine et ses sous-domaines. Le téléphone conserve une forme lisible, sans HTML.

## Administration

Quatre panneaux structurent la fiche :

1. Identité et présentation.
2. Situation.
3. Coordonnées et visibilité.
4. Publication et suivi.

Le titre WordPress porte le nom et le panneau Photo porte l’image. Si une photo existe, elle doit être une pièce jointe image avec URL HTTP(S) et texte alternatif non vide avant publication. Aucun alt ni média par défaut n’est généré.

La liste affiche uniquement photo, nom, statut, ville/département, autorisation, ordre et date. Aucun contact ni note interne n’y apparaît. Quick Edit et Bulk Edit restent neutralisés.

## Publication et confidentialité

wp_seed_content_directory_is_publicly_eligible( $post_id ) retourne vrai uniquement pour une fiche publiée, non protégée par mot de passe, autorisée, nommée, avec statut et pays valides et, le cas échéant, photo et alt valides.

Une publication invalide est ramenée en brouillon. La défense combine validation avant écriture, contrôle après écriture ou transition et prédicat final. Retirer l’autorisation via le formulaire admin rend immédiatement la fiche inéligible et la ramène en brouillon.

wp_seed_content_directory_get_public_contacts() ne retourne que les contacts valides, explicitement visibles et rattachés à une fiche éligible. Un contact masqué ou invalide est absent, sans rendre la fiche entière inéligible.

wp_seed_content_directory_get_admin_data() exige edit_seed_directory_entry pour la fiche et retourne alors les champs complets, y compris contacts privés, autorisation, note et date de vérification. Cette fonction n’est ni une API REST ni la Data API publique prévue pour L4.

## Capacités et cycle de vie

Les quatre capacités primitives restent :

- edit_seed_directory_entries ;
- publish_seed_directory_entries ;
- read_private_seed_directory_entries ;
- delete_seed_directory_entries.

Seul administrator les reçoit à l’activation. Désactiver le module retire le CPT et son menu, sans supprimer posts, médias, métas ou capacités. La réactivation retrouve les données.

Les révisions natives couvrent le titre et la présentation. Les métas métier restent attachées à la fiche courante ; elles ne sont pas dupliquées dans une sortie publique et toute restauration repasse par la garde d’éligibilité.

## Sortie publique L4

wp_seed_content_directory_get_public_data($post_id) retourne uniquement le schema ferme id, name, photo, bio, status, status_label, location, featured, display_order et contacts. Une fiche ineligible retourne false. Les contacts absents, invalides ou masques ne figurent pas dans le tableau.

wp_seed_content_directory_get_entries($args) retourne des IDs eligibles. Les filtres sont status, department, country, featured, limit, orderby, order et ids. Ordre display_order : ordre manuel, nom normalise sans distinction de casse ou d'accent, puis ID. Aucun ID explicite ne contourne eligibilite.

[seed_directory] accepte les memes attributs et template. [wp_seed_directory] est un alias temporaire deprecie, sans avertissement public. Les valeurs invalides retournent une chaine vide. Les groupes restent, dans cet ordre, En exercice puis En recherche de modeles ; un groupe vide est omis.

La carte native affiche uniquement photo ou emplacement neutre, nom, statut, localisation, presentation et contacts publics. La grille CSS est 3/2/1 colonnes. Le CSS structurel est charge seulement avec des fiches rendues et le CSS de carte seulement avec une carte native.

## Templates et confidentialite

Le module Template directory expose exactement quinze placeholders directory.*, du nom a directory.featured. Leur contexte provient exclusivement de la Data API publique. Un Template publie personnalise une carte ; template absent, brouillon, mauvais module, resultat vide, recursion, erreur, contexte ou assets invalides declenchent un fallback natif pour cette fiche seulement.

Gutenberg utilise le bloc Shortcode. Divi accepte le shortcode dans Texte ou Code et peut rendre un Layout Divi Library via un Template Content Kit. Aucun bloc ou module Divi specifique est cree.

Module desactive : shortcode vide, Collection vide, aucun asset et aucune exposition ; les donnees sont conservees. La RC exclut recherche ou filtres visibles, fiche individuelle, archive, REST/AJAX, migration et cache persistant. Le plugin Directory autonome reste une reference comparative externe, sans couplage ni migration automatique.
