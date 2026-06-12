# GITHUB RELEASE PLAN - WP-seed-content-kit

Date : 12 juin 2026
Statut : plan preliminaire V1.1
Nature : documentation uniquement

## Objectif

Preparer la strategie GitHub avant toute integration Plugin Update Checker.

Ce plan ne contient :

- aucun code ;
- aucune dependance ;
- aucune integration PUC ;
- aucune publication GitHub ;
- aucun tag cree.

## 1. Etat Git local

Etat observe :

```text
branche: main
commits: aucun commit
remote: aucun remote configure
working tree: fichiers non suivis
```

Interpretation :

- le projet est initialise localement ;
- rien n'est encore versionne dans Git ;
- aucun depot GitHub n'est relie au workspace local ;
- il n'est pas encore possible de creer un tag fiable tant qu'un premier commit propre n'existe pas.

Action necessaire avant release :

- definir le depot GitHub cible ;
- ajouter le remote `origin` ;
- faire un premier commit de reference ;
- seulement ensuite creer `v0.1.0`.

## 2. Existence du depot GitHub

Verification actuelle :

- aucun remote Git n'est configure localement ;
- aucune URL GitHub n'est connue dans le depot local ;
- aucune existence de depot GitHub n'est confirmee depuis l'etat Git local ;
- une recherche publique rapide ne confirme pas de depot public evident nomme `WP-seed-content-kit`.

Conclusion :

```text
Depot GitHub non confirme.
```

Decision a prendre :

- creer ou identifier le depot GitHub officiel ;
- choisir sa visibilite :
  - public recommande pour V1.1 sans token ;
  - prive non recommande si cela impose un secret dans le plugin.

Nom recommande :

```text
WP-seed-content-kit
```

URL cible a confirmer :

```text
https://github.com/<owner>/WP-seed-content-kit
```

## 3. Convention de version initiale

### `0.1.0`

Role :

- premiere version taggable ;
- socle V1.0 actuel ;
- plugin installable par ZIP ;
- modules `testimonials` et `cards` ;
- shortcodes `[seed_testimonials]` et `[seed_cards]` ;
- CSS prefixe ;
- documentation minimale ;
- packaging ZIP verifie.

Statut :

- version de base pour initialiser GitHub Releases ;
- pas encore infrastructure update admin.

### `0.1.1`

Role :

- patch compatible ;
- corrections UX/polish ;
- corrections packaging ;
- aucun changement API shortcode.

### `0.2.0`

Role :

- V1.1 ;
- integration future GitHub Releases + Plugin Update Checker ;
- detection et mise a jour depuis admin WordPress.

Non inclus dans ce plan :

- aucune implementation PUC.

## 4. Premier tag Git

Tag cible :

```text
v0.1.0
```

Preconditions :

- tous les fichiers retenus pour V1.0 sont ajoutes a Git ;
- le premier commit est cree ;
- le ZIP `dist/wp-seed-content-kit.zip` est verifie ;
- la version du fichier principal correspond a `0.1.0` ;
- le changelog `v0.1.0` est pret ;
- le depot GitHub cible est configure.

Regle :

- ne pas creer `v0.1.0` tant que le premier commit propre n'existe pas ;
- ne pas retagger silencieusement `v0.1.0` apres publication ;
- en cas d'erreur, publier `v0.1.1`.

## 5. Premier changelog

Changelog propose pour `v0.1.0` :

```text
## v0.1.0 - Initial test release

### Added
- Initial WordPress plugin skeleton.
- Testimonials module with CPT `seed_testimonial`.
- Native testimonial meta boxes.
- `[seed_testimonials]` shortcode.
- Cards module for native WordPress posts.
- `[seed_cards]` shortcode.
- Scoped CSS with `seed-` prefix.
- Minimal plugin documentation.
- Manual ZIP packaging workflow.

### Changed
- Not applicable.

### Fixed
- Not applicable.

### Notes
- ACF is not required.
- Composer is not required.
- npm is not required.
- No GitHub update mechanism is included in this version.
- V1.1 will target GitHub Releases updates from WordPress admin.
```

## 6. Premiere release GitHub

Release cible :

```text
v0.1.0
```

Titre recommande :

```text
v0.1.0 - Initial test release
```

Type :

```text
pre-release ou test release
```

Recommandation :

- marquer `v0.1.0` comme prerelease si l'objectif est de ne pas la proposer plus tard aux sites via PUC ;
- publier comme release normale uniquement si elle doit devenir la base publique stable detectee par les outils de release.

Comme V1.1 utilisera probablement GitHub Releases pour les updates, il faudra definir une regle avant PUC :

- prereleases ignorees pour les sites standards ;
- releases normales reservees aux versions installables.

## 7. Premier asset ZIP

Asset attendu :

```text
wp-seed-content-kit.zip
```

Source locale :

```text
dist/wp-seed-content-kit.zip
```

Structure obligatoire :

```text
wp-seed-content-kit/
  wp-seed-content-kit.php
  includes/
  assets/
  README.md
  docs/
```

Verifications obligatoires avant upload :

```text
has_backslash=false
has_directory_entries=false
main_file_present=true
unique_root=wp-seed-content-kit
no_secrets=true
no_git=true
no_temp_files=true
```

Regle V1.1 future :

- Plugin Update Checker devra telecharger cet asset ZIP ;
- ne pas utiliser l'archive source automatique GitHub comme package WordPress.

## 8. Workflow GitHub preliminaire

### Etape 1 - Stabiliser le contenu V1.0

- valider le perimetre ;
- exclure les travaux V1.1/V1.2 du tag `v0.1.0` si non implementes ;
- verifier que la documentation correspond a l'etat reel.

### Etape 2 - Creer ou connecter le depot GitHub

- creer le depot GitHub officiel si absent ;
- ajouter le remote `origin` ;
- verifier l'URL ;
- choisir public si V1.1 doit eviter les tokens.

### Etape 3 - Premier commit

- ajouter les fichiers projet retenus ;
- exclure secrets, temporaires, captures locales inutiles ;
- committer sous un message clair, par exemple :

```text
Initial V1.0 plugin skeleton and release docs
```

### Etape 4 - Tag `v0.1.0`

- tagger le commit de release ;
- pousser le tag.

### Etape 5 - Creer GitHub Release `v0.1.0`

- utiliser le changelog propose ;
- attacher `wp-seed-content-kit.zip` ;
- verifier le telechargement de l'asset.

### Etape 6 - Verification post-release

- telecharger le ZIP depuis GitHub ;
- verifier la structure interne ;
- verifier que l'asset porte bien le nom attendu ;
- documenter le resultat.

## 9. Points ouverts

- Owner GitHub du depot.
- Visibilite public/prive.
- Choix prerelease ou release normale pour `v0.1.0`.
- Inclusion ou non de `dist/wp-seed-content-kit.zip` dans Git.
- Creation d'un `CHANGELOG.md` des V1.1 ou maintien du changelog dans les releases GitHub.
- Politique de branches :
  - `main` seulement pour le debut ;
  - branche `release/*` plus tard si necessaire.

## 10. Decision recommandee

Recommandation preliminaire :

```text
Creer un depot GitHub public.
Faire un premier commit propre.
Tagger v0.1.0.
Publier une GitHub Release v0.1.0 avec asset wp-seed-content-kit.zip.
Garder Plugin Update Checker hors de v0.1.0.
```

Raison :

- V1.1 a besoin d'une base GitHub propre avant toute integration update ;
- un depot public evite les secrets ;
- un asset ZIP stable prepare le futur flux PUC ;
- le premier tag sert de point de depart clair pour les mises a jour futures.
