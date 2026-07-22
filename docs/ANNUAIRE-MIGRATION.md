# Migration fictive de l'Annuaire

Statut : CK-A6, version 0.6.0-rc.2-dev.

## Perimetre

CK-A6 fournit une API PHP interne pour exercer le cycle de migration du module Annuaire natif. Elle accepte uniquement un manifeste ferme, valide integralement les donnees avant toute ecriture et exige un contexte interne explicite ainsi que la capacite `manage_wp_seed_imports`. Aucun ecran, hook d'activation, shortcode, endpoint REST, AJAX ou admin-post ne declenche l'import.

Le manifeste de reference `tests/fixtures/native-directory-demo-v1.json` est entierement fictif. Il contient 16 fiches prefixees `SEED CONTENT KIT TEST - ANNUAIRE -`, 10 statuts `practicing`, 6 `seeking_models`, 14 publications autorisees et 2 brouillons. Ses 13 images PNG generees localement alimentent 16 associations, dont trois reutilisations.

## Schema ferme

La racine autorise uniquement `schema_version`, `batch_id`, `source_system`, `source_snapshot`, `generated_at`, `media` et `entries`. Les objets media et fiche ont egalement une liste fermee de cles. Les types, identifiants, references, pays, statuts, contacts publics, autorisations, alts, dimensions, MIME, donnees binaires et hashes sont controles avant la premiere ecriture. Une erreur retourne un resultat type en echec et ne cree ni fiche, ni media, ni registre.

Chaque fiche utilise exclusivement le titre, l'extrait, l'image mise en avant, `menu_order` et les 19 metas natives documentees dans `ANNUAIRE-MODULE.md`. Aucun champ historique abandonne n'est importe.

## References et hashes

La reference privee stable est `native-directory-demo:<source_id>`. Elle et le hash source ne sont conserves que dans les metas techniques de migration. Ils ne font partie ni de la Data API, ni du contexte Template, ni des placeholders, ni du HTML.

Les hashes utilisent SHA-256 sur un JSON canonique UTF-8 : cles triees recursivement, types conserves, Unicode et slashs non echappes. Le hash d'une fiche exclut `source_hash`; celui d'un media exclut `source_hash`; le hash global exclut `manifest_hash`.

## Import et idempotence

`wp_seed_content_directory_import_manifest()` valide d'abord le manifeste complet. Le premier import cree les objets absents et conserve leurs IDs dans un registre prive. Un second import identique ne reecrit rien. Une source dont le hash change est mise a jour sur le meme ID. Un media change est remplace de facon deterministe sur le meme attachment, sans doublon. Une source absente du manifeste n'est jamais supprimee automatiquement et est signalee dans `missing_from_source`.

Le registre de lot est une option non autoloaded derivee du `batch_id`. Il contient statut, snapshot, hash global, IDs crees, objets mis a jour, sauvegardes anterieures, horodatages et dernier resultat. Il ne contient aucune sortie publique.

## Rollback

`wp_seed_content_directory_rollback_migration_batch()` supprime uniquement les fiches creees par le lot et leurs revisions, restaure les fiches et medias preexistants depuis leurs sauvegardes, puis retire les metas internes et le registre. Un media cree par le lot n'est supprime que s'il n'est pas reutilise hors lot; sinon il est preserve et detache du registre. Le second rollback retourne `unchanged` et ne touche aucun contenu supplementaire.

Pages, Templates, Citations, Temoignages, fiches preexistantes et medias externes restent hors lot. Seule une fiche preexistante volontairement mise a jour est restauree a son etat exact.

## Permissions et securite

L'import et le rollback exigent le contexte retourne par `wp_seed_content_directory_migration_context()` et `manage_wp_seed_imports`. Administrator est autorise; Editor est refuse et ne voit aucun outil. Sans interface web, aucun nonce n'est expose. Toute migration de donnees reelles reste interdite sans lot et autorisation distincts.

## Verification

Le harnais autonome valide le schema, les hashes, les erreurs globales, les permissions et l'absence de surfaces automatiques. Le harnais WordPress valide import, reimport, mises a jour ciblees, media, source absente, registre, rendu public, Collections, Templates, shortcode, confidentialite, rollback et preservation du contenu hors lot. Il supprime ses contenus, utilisateurs et registres temporaires apres execution.

WP Seed Directory peut servir de reference conceptuelle, mais CK-A6 ne charge, n'appelle et ne copie aucun de ses namespaces ou fichiers.
