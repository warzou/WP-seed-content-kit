# Module Annuaire

Statut : Release Candidate 0.8.0-rc.2 du module Annuaire natif, en validation et non stable.

## Périmètre

Annuaire est le module natif directory, actif par défaut et désactivable. Son CPT seed_directory reste strictement administratif : aucune archive, page individuelle, recherche publique ou entrée de sitemap. Sa route REST authentifiée sert les éditeurs et les constructeurs sans rendre le CPT publiquement interrogeable.

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

Une nouvelle publication invalide est ramenée en brouillon. Pour une fiche déjà publiée, les erreurs qui existaient avant une modification sans rapport sont conservées temporairement sans changer silencieusement son `post_status` ; un avertissement administratif persistant demande leur correction. Une nouvelle erreur éditoriale corrigible conserve également la publication lors de sa première sauvegarde et enregistre un marqueur privé composé du code, du champ ou de la ligne et d’une empreinte de l’état invalide. Une seconde sauvegarde du même état invalide place la fiche en brouillon ; une correction ou une autre valeur invalide efface ou renouvelle ce cycle. Le retrait d’autorisation et les erreurs critiques restent immédiatement stricts. La défense combine comparaison avant/après, validation avant écriture, contrôle après écriture ou transition et prédicat final.

Le marqueur technique n’est ni une donnée métier ni une méta publique. Gutenberg reçoit uniquement une projection REST en contexte `edit`, sans valeur fautive ni empreinte, afin d’afficher l’erreur, le maintien temporaire en ligne et la conséquence d’une seconde sauvegarde. Pour une coordonnée répétable, le message reprend le type de la ligne lorsque celui-ci est connu. Comme Gutenberg enregistre l’entité REST avant les métaboxes classiques, l’intention de publication est capturée par `editor.preSavePost`, puis finalisée par le store Core Data depuis `editor.savePost`, après la sauvegarde des métaboxes, uniquement si la validation rafraîchie est devenue entièrement propre. Un simple enregistrement ne publie jamais automatiquement la fiche.

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

Le module Template directory expose exactement vingt-cinq placeholders directory.*, du nom a directory.featured. Leur contexte provient exclusivement de la Data API publique. Un Template publie personnalise une carte ; les classes opt-in `wp-seed-directory-template-card__*` permettent a une fixture ou a un integrateur d'adopter les espacements de la carte native sans imposer ce design aux autres Templates. Un Layout Divi reste responsable de ses propres espacements. Template absent, brouillon, mauvais module, resultat vide, recursion, erreur, contexte ou assets invalides declenchent un fallback natif pour cette fiche seulement.

Gutenberg utilise le bloc Shortcode. Divi accepte le shortcode dans Texte ou Code et peut rendre un Layout Divi Library via un Template Content Kit. Aucun bloc ou module Divi specifique est cree.

Module desactive : shortcode vide, Collection vide, aucun asset et aucune exposition ; les donnees sont conservees. La RC exclut recherche ou filtres visibles, fiche individuelle, archive, AJAX public, migration automatique et cache persistant. Le plugin Directory autonome reste une reference comparative externe, sans couplage ni migration automatique.

## Guidage Utilisation CK-A4

L’onglet Collections documente les types de profil, leur opérateur OR/AND, le statut temporaire, les sélections et exclusions, la pagination, le tri par défaut et l’état vide. Le générateur produit uniquement un shortcode [seed_directory] à copier et peut lui ajouter un slug de Template. Il ne sauvegarde ni Collection ni association Template/Collection.

L’onglet Templates décrit exactement les vingt-cinq placeholders directory.*. Ils proviennent tous de la projection publique fermée. Les cinq placeholders de contact sont disponibles uniquement lorsque la valeur est valide et explicitement rendue publique ; aucune donnée privée n’est proposée. Sans attribut template, ou lorsqu’un Template demandé est inutilisable, la carte native reste le fallback local.

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

Les Templates exposent `directory.summary`, `directory.bio`, `directory.presentation`, son alias historique `directory.full_presentation`, ainsi que `directory.presentation_intro`, `directory.presentation_more` et `directory.has_more`. Le Layout du site choisit d’afficher ou non la présentation complète. Aucun champ de visibilité n’est exposé comme donnée utile : toute fiche rendue publiquement est déjà explicitement listée.

### Résumé, présentation complète et « Lire la suite »

Le résumé et la présentation complète sont deux données portables indépendantes. `post_excerpt` est l’unique source du résumé ; `post_content` est l’unique source de la présentation complète. Un bloc More WordPress facultatif dans `post_content` définit une frontière portable : `presentation_intro` contient la partie avant le premier marqueur, `presentation_more` la partie après, et `has_more` indique sa présence. `presentation` et `full_presentation` conservent toujours l’intégralité du contenu public, sans commentaires techniques More ou `noteaser`. Sans marqueur, l’introduction est identique à la présentation complète, la suite est vide et `has_more` vaut faux.

Le marqueur natif peut être inséré avec le bloc More Gutenberg ou avec `<!--more-->` dans l’éditeur de code. Son éventuel libellé personnalisé ne devient pas une donnée métier. La coupure doit être placée entre deux blocs ou paragraphes : le splitter reste déterministe et ne tente pas de réparer une balise HTML coupée. WPSCK ne stocke ni copie du découpage, ni état ouvert/fermé, ni réglage d’accordéon, ni comportement propre à un builder.

Un rendu peut afficher le résumé puis proposer un contrôle « Lire la suite » vers la présentation complète lorsque les deux valeurs existent et diffèrent. Si le résumé est vide, le builder peut afficher directement la présentation complète. Si les deux valeurs sont identiques, elles ne doivent pas être rendues simultanément. Cette décision appartient au Template ou au builder et ne modifie jamais les données.

Dans Divi, un Toggle ou un Accordion natif peut consommer `WPSCK — Annuaire — Introduction` et `WPSCK — Annuaire — Suite de présentation`; le provider complet `Présentation` reste disponible. Dans Gutenberg, les mêmes champs texte sont disponibles via la source Block Bindings WPSCK sur les blocs Core compatibles ; `directory.has_more` reste accessible par la Content Data API pour une composition conditionnelle. Spectra/Astra peuvent reproduire le même pattern avec leurs composants, un Template ou des blocs Core, sans dépendance ajoutée à WPSCK.

Le composant repliable reste accessible au clavier. Il utilise un vrai bouton avec un état `aria-expanded` correctement maintenu, ou la sémantique native `<details>/<summary>`. WPSCK n’ajoute aucun JavaScript propriétaire lorsque le builder fournit déjà cette interaction.

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

Le module de Collection reste indépendant du Loop Builder. En complément, le contrat Native Loop expose les données Annuaire dans les modules Divi natifs, sans shortcode dans le canevas, observation du DOM ou module Carousel propriétaire.

## Ordre des filtres Collections en RC.3

`ids` et `exclude_ids` sont normalisés et dédupliqués séparément. Quand les deux sont présents, la Collection calcule la différence avant toute pagination. Elle applique ensuite les règles publiques, les types et statuts, les autres filtres, le tri, l'offset et la limite. Une différence vide reste vide. Cette correction ne modifie aucune donnée et ne nécessite aucune migration depuis RC.2.

## Stockage portable et Native Loop

Le stockage builder-compatible réutilise `post_title` pour le nom, `post_excerpt` pour la présentation courte, `post_content` pour la présentation complète, l'image mise en avant pour le visuel et `menu_order` pour l'ordre manuel. `seed_directory_professional_label` contient l'intitulé professionnel facultatif affichable sous le nom ; il reste distinct des classifications `seed_directory_status` et `seed_directory_profile_types`. Les autres métas publiques portables restent `seed_directory_city`, `seed_directory_postal_code`, `seed_directory_department`, `seed_directory_country` et `seed_directory_featured`. L'ancienne méta `_seed_directory_profession` est un fallback de lecture et une source de migration non destructive ; la valeur publique canonique gagne toujours. `seed_directory_seeking_models` n'est plus une donnée métier écrite : ses variantes publique et privée restent uniquement des fallbacks legacy de lecture, de migration et de rollback.

Les anciennes métas privées correspondantes restent des fallbacks de lecture. Une valeur publique existante, y compris vide ou fausse, gagne toujours. La migration privée vers publique est explicite, additive, non destructive et réversible ; elle ne s'exécute jamais à l'activation. Les contacts bruts, leurs choix de visibilité, l'autorisation de publication, la visibilité dans les annuaires, la note interne et la date de vérification restent privés. Seule la projection des contacts valides et explicitement publics atteint les providers.

Le groupe Divi `WPSCK — Annuaire` expose individuellement les champs métier stables. Une ligne de coordonnées contient un type, un libellé facultatif, un lien complet, sa visibilité publique et son ordre. Le libellé devient la valeur d'affichage lorsqu'il est renseigné ; sinon WPSCK dérive une représentation lisible du lien. Chaque type liant dispose d'un provider d'affichage stable et d'un provider `— Lien` qui retourne directement le `tel:`, `mailto:` ou l'URL HTTP(S) validée, sans doubler le schéma. L'Adresse reste display-only et conserve une valeur texte tant qu'aucun href cartographique canonique n'existe.

Les anciens téléphones et e-mails sans schéma restent lisibles pendant la transition. Un planificateur de migration read-only identifie les conversions déterministes vers `tel:` et `mailto:` sans exposer les valeurs ni écrire les métas. Toute ligne enregistrée par la nouvelle interface porte le contrat `full_link_v1` et doit respecter le schéma de son type. Une ligne privée, vide, invalide, inactive ou composée d'un libellé sans lien n'atteint ni la projection publique ni les conditions `renseigné`.

WPSCK ne stocke plus d'icône de présentation sur les lignes ni dans le registre des types. Les providers fournissent uniquement l'affichage, le href complet et la présence. Divi ou le builder consommateur reste exclusivement responsable de l'icône, de sa taille, de sa couleur, de sa position et de tous les espacements.

WPSCK ne fournit aucun provider ou renderer composite de coordonnées. Les conditions WPSCK `… renseigné` utilisent le provider individuel canonique `loop_*` et un évaluateur partagé : elles permettent de masquer un groupe Icône + Valeur lorsque la projection publique est vide. Divi reste seul responsable du choix des champs, de leur ordre, des icônes, du layout et du style.

Le registry stocke une liste ordonnée de types à slug immuable. Une installation neuve contient uniquement Téléphone et E-mail. Les comportements de valeur et de lien appartiennent à une liste sûre contrôlée par WPSCK ; l'administrateur configure le libellé, l'état, l'ordre et, pour un type personnalisé non ambigu, l'exposition comme provider individuel.

`Lien web` accepte toute URL HTTP(S) valide et rejette les schémas non sûrs. Les types Facebook et Instagram conservent leurs slugs, labels, providers et conditions propres, mais utilisent ce comportement générique : ils représentent un lien web que l'éditeur choisit d'associer au réseau concerné. Les anciens noms de comportement Facebook/Instagram sont normalisés en lecture vers `Lien web`, sans migration des lignes de coordonnées.

La protection administrative et le contrat public sont distincts. Site internet, Facebook et Instagram sont des types configurables normaux : ils sont désactivables et ne sont supprimables que lorsqu'aucune ligne canonique ne les utilise. Leurs IDs historiques de providers et conditions restent réservés dans un registre de contrats séparé et ne dépendent donc pas du badge `Système`. Téléphone et E-mail sont les seuls types protégés.

Un type système peut être désactivé mais jamais supprimé. Un type personnalisé peut être supprimé seulement si aucune ligne canonique, y compris privée ou invalide, ne l'utilise. Sinon l'interface indique le nombre de coordonnées concernées et le backend refuse la suppression ; la désactivation reste non destructive. Une ligne neuve non enregistrée peut être retirée immédiatement. Le slug reste l'identité des providers : recréer volontairement un slug supprimé recrée les mêmes IDs déterministes et peut donc réactiver d'anciens bindings builder.

Les providers individuels et leurs conditions de présence sont générés depuis ce registre. Lorsqu'un type compatible y est ajouté et activé pour l'exposition individuelle, son provider valeur, son provider lien éventuel et sa condition `<Type> renseigné` deviennent automatiquement disponibles dans Divi. Une seule déclaration centrale doit fournir le slug immuable, le libellé, l'état actif, la capacité `individual_provider` et un comportement sûr qui porte le normalizer ainsi que le link builder éventuel. Un registre de contrats distinct réserve les IDs publiés de phone, email, website, facebook, instagram, linkedin, whatsapp et address sans leur conférer automatiquement le statut administratif `Système`. `other` reste disponible dans les lignes répétables et la projection de données, mais ne dispose pas de provider individuel ni de rendu multi-lignes automatique ; un futur besoin doit passer par un contrat repeatable générique distinct.

Les coordonnées des fiches sont répétables et privées ligne par ligne. Désactiver un type ne supprime ni les lignes canoniques, ni les champs legacy, ni les bindings enregistrés. Une réactivation du même slug restaure ses providers déterministes sans modifier les fiches.

Les identifiants Divi enregistrés sont `loop_wpsck_directory_*`; les réponses QueryResults utilisent les alias sans préfixe `loop_`. Le contexte suit le contrat partagé : `loop_id`, puis `loop_object`, avec jeton différé en l'absence de clone résolu. L'ancre canonique vaut `annuaire-{ID}`.

Dans une Native Loop Divi 5.9, l'inspecteur d'un clone rendu peut ne pas réhydrater les valeurs responsive du module source. Ne pas enregistrer depuis ce clone lorsque les valeurs affichées contredisent le contenu canonique. Modifier le module source seulement lorsque son inspecteur restitue les valeurs attendues ; sinon, utiliser un preset natif Divi du groupe Dimensionnement, vérifié hors contexte Loop, comme unique source de présentation.

Le type `praticien` ou `intervenant` décrit une facette de profil, pas un métier. `directory.professional_label` expose l'intitulé éditorial facultatif et ne participe jamais au classement ou au filtrage. Aucun ancien `presentation_mode` n'existe dans la baseline : les rendus compact et détaillé sont deux designs Divi utilisant la même Collection et les mêmes données.

La requête Native Loop consomme exclusivement `wp_seed_content_directory_get_entries()`. Elle conserve les filtres statut, types OR/AND, Recherche de modèles, département, pays, mise en avant, IDs, exclusions, limite, offset et tri. Une fiche non listée, non autorisée, brouillon, privée, protégée ou invalide reste exclue, y compris lorsqu'un ID explicite est demandé. Gutenberg et de futurs adaptateurs Spectra/Astra lisent le même stockage public ; ils ne dépendent ni de Divi ni d'une sérialisation `$variable(...)`.

## Classifications canoniques et projections

Les registries WPSCK Statuts et Types de profil stockent un slug stable, un libellé, un état actif et un ordre. Les slugs système `en_exercice`, `recherche_modeles`, `praticien` et `intervenant` sont protégés. Une valeur personnalisée inutilisée peut être supprimée explicitement après confirmation ; toute fiche qui l'utilise bloque cette suppression côté serveur et l'interface en indique le nombre. Désactiver une valeur reste toujours l'option non destructive et la retire des nouveaux choix sans invalider les fiches qui la portent. Une ligne neuve vide peut être retirée immédiatement. Une fiche accepte un statut et plusieurs types.

Les coordonnées sont exposées uniquement par contrats individuels builder-agnostic. La Content Data API et les Block Bindings Gutenberg lisent les mêmes projections publiques que Divi, sans profil d'affichage, HTML agrégé ni état de présentation propre à un builder.

La résolution transitionnelle du statut suit cet ordre : `seed_directory_status` valide, ancienne méta de statut, indicateur legacy `seeking_models=true` vers `recherche_modeles`, puis `practicing` vers `en_exercice`. Pour les types, la méta publique valide gagne, puis la méta privée intervient en fallback. Aucune sauvegarde éditoriale normale ne réécrit les indicateurs `seeking_models` legacy.

`wp_seed_directory_status` et `wp_seed_directory_profile_type` sont des taxonomies de projection, jamais une seconde source métier. Elles sont sans métabox, non interrogeables publiquement et exposées en lecture seule dans REST. La synchronisation canonique vers termes est idempotente ; elle peut cibler une fiche, reconstruire toutes les projections ou auditer les écarts. Les filtres taxonomy des nouvelles Native Loops Divi sont retirés de la requête brute, interprétés, puis validés par la Collection canonique avant injection des IDs publics.

`orderby` accepte `menu_order` (alias historique `display_order`), `name`, `status`, `profile_type`, `date` et `id`, seuls ou sous forme de liste séparée par des virgules. L'ordre des registries classe les statuts et le premier type assigné selon cet ordre classe une fiche multi-profile. L'ID WordPress reste toujours le dernier départage déterministe.

Le moteur de migration n'est jamais automatique. Son dry-run produit pour chaque fiche l'ancien statut, l'indicateur legacy, les nouveaux statut/types, la projection attendue et les avertissements. `apply` écrit seulement les metas publiques canoniques et reconstruit les projections à partir du plan approuvé. Le snapshot conserve metas canoniques et legacy, termes et `menu_order`; `rollback` les restaure exactement.
