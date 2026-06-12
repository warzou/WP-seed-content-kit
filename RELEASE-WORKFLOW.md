# RELEASE WORKFLOW - WP-seed-content-kit

Date : 12 juin 2026
Statut : convention de release future et base V1.1
Nature : documentation uniquement

## Objectif

Ce document prepare la strategie GitHub Releases de WP-seed-content-kit.

Il ne decrit aucune implementation de code, mais il fixe les conventions necessaires a V1.1, dont l'objectif est la mise a jour depuis l'admin WordPress via GitHub Releases.

Objectifs :

- versionner clairement le plugin ;
- produire des ZIP installables et reproductibles ;
- publier des releases lisibles ;
- faciliter la distribution multi-sites ;
- garder V1 simple et maintenable.

Ordre produit retenu :

```text
V1.0 = socle actuel
V1.1 = mises a jour GitHub via admin WordPress
V1.2 = module Quotes/Citations
V2 = styles avances, modules configurables, ACF optionnel
```

## 1. Convention de version

WP-seed-content-kit utilise une convention proche de SemVer :

```text
MAJOR.MINOR.PATCH
```

### `0.1.0`

Signification :

- premiere version V1 testable ;
- squelette plugin ;
- module testimonials ;
- module cards ;
- shortcodes V1 ;
- CSS prefixe ;
- ZIP installable.

Usage :

- tests initiaux ;
- validation du socle ;
- pas encore une release produit publique stable.

### `0.1.1`

Signification :

- correctif ou polish compatible V1 ;
- aucun changement d'API shortcode ;
- aucun nouveau module ;
- aucune architecture nouvelle.

Exemples acceptes :

- traduction de libelles visibles ;
- correction CSS mineure ;
- correction packaging ;
- correction bug sans changement fonctionnel.

### `0.2.0`

Signification :

- premiere V1.1 ;
- infrastructure de mise a jour GitHub via admin WordPress ;
- peut introduire Plugin Update Checker comme dependance embarquee ;
- ne doit pas casser les shortcodes V1.

Exemples attendus :

- detection d'une nouvelle version depuis WordPress ;
- telechargement du ZIP GitHub Release ;
- mise a jour depuis l'ecran Extensions ;
- rollback manuel documente.

### `0.3.0`

Signification :

- premiere V1.2 ;
- module Quotes/Citations si revalide ;
- nouveau module editorial separe ;
- ne doit pas casser l'infrastructure update V1.1 ;
- ne doit pas casser les shortcodes V1.0.

### `1.0.0`

Signification :

- premiere version produit stable ;
- API shortcode consideree stable ;
- installation ZIP validee ;
- documentation minimale complete ;
- processus de release reproductible ;
- strategie de mise a jour admin stabilisee ;
- tests V1 obligatoires passes.

Implication :

- apres `1.0.0`, tout changement cassant doit etre evite ou reporte a une version majeure future.

## 2. Convention de tags Git

Les tags Git suivent le format :

```text
vMAJOR.MINOR.PATCH
```

Exemples :

```text
v0.1.0
v0.1.1
v0.2.0
v1.0.0
```

Regles :

- le tag doit pointer vers le commit exact utilise pour generer le ZIP ;
- le tag doit correspondre a la version du fichier principal du plugin ;
- le tag doit correspondre au titre de la GitHub Release ;
- ne pas reutiliser un tag publie ;
- en cas d'erreur de release, publier une nouvelle version patch.

Interdits :

- tags flottants comme `latest` ;
- tags sans prefixe version claire ;
- tags reutilises pour remplacer silencieusement une release.

## 3. Convention de changelog

Chaque release doit contenir un changelog court et lisible.

Format recommande :

```text
## v0.1.1 - 2026-06-12

### Added
- ...

### Changed
- ...

### Fixed
- ...

### Removed
- ...

### Notes
- ...
```

Sections :

- `Added` : nouvelle capacite ;
- `Changed` : changement visible ou comportement ajuste ;
- `Fixed` : correction de bug ;
- `Removed` : retrait explicite ;
- `Notes` : limites, precautions, rollback, compatibilite.

Regles :

- ne pas documenter du bruit interne ;
- signaler tout changement d'API shortcode ;
- signaler tout changement de schema de donnees ;
- signaler toute dependance ajoutee ;
- mentionner si le ZIP est une version de test ou une release stable.

Pour V1, le changelog peut rester dans la description GitHub Release.

Un fichier `CHANGELOG.md` pourra etre ajoute plus tard si le rythme de release augmente.

## 4. Convention de packaging ZIP

Nom du fichier release :

```text
wp-seed-content-kit.zip
```

Nom optionnel pour archivage local :

```text
wp-seed-content-kit-v0.1.1.zip
```

Regle GitHub Release :

- l'asset principal attache a la release doit s'appeler `wp-seed-content-kit.zip`.

Structure interne obligatoire :

```text
wp-seed-content-kit/
  wp-seed-content-kit.php
  includes/
  assets/
  README.md
  docs/
```

Contraintes techniques :

- racine interne unique `wp-seed-content-kit/` ;
- chemins internes au format POSIX avec `/` ;
- pas de chemins Windows avec `\` ;
- pas d'entrees dossiers si le packaging les evite ;
- fichier principal present ;
- pas de `.git` ;
- pas de fichiers temporaires ;
- pas de sauvegardes ;
- pas de secrets ;
- pas de docs racine projet inutiles ;
- pas de dependance de build requise apres installation.

Verification minimale avant publication :

```text
has_backslash=false
has_directory_entries=false
main_file_present=true
unique_root=wp-seed-content-kit
```

## 5. Workflow release

### Etape 1 - Stabiliser le contenu

- verifier que le perimetre de la version est clair ;
- refuser toute derive hors version ;
- verifier que les changements sont documentes ;
- verifier que les tests requis sont passes ou explicitement marques comme non applicables.

### Etape 2 - Aligner les versions

Verifier que la version est coherente dans :

- header `Version` du fichier principal ;
- documentation release ;
- nom du tag Git ;
- titre GitHub Release ;
- changelog.

Pour V1 sans automation, le header du plugin reste la source principale visible dans WordPress.

### Etape 3 - Generer le ZIP

- generer `dist/wp-seed-content-kit.zip` ;
- forcer les chemins internes POSIX ;
- inclure uniquement le contenu installable du plugin ;
- exclure racine projet, `.git`, fichiers temporaires, secrets et sauvegardes.

### Etape 4 - Verifier le ZIP

Verifier :

- structure interne ;
- fichier principal ;
- absence de backslashes ;
- absence de fichiers exclus ;
- taille raisonnable ;
- installation probable.

### Etape 5 - Creer le tag Git

Format :

```text
v0.1.1
```

Le tag doit etre cree uniquement apres validation du ZIP.

### Etape 6 - Creer la GitHub Release

Titre :

```text
v0.1.1
```

Contenu :

- resume de la version ;
- changelog ;
- notes de compatibilite ;
- statut : test, candidate ou stable ;
- instructions de rollback si necessaire.

Asset :

```text
wp-seed-content-kit.zip
```

### Etape 7 - Verification post-release

Verifier :

- le ZIP est telechargeable ;
- le ZIP telecharge est identique ou equivalent au ZIP local ;
- la structure interne reste correcte ;
- la release pointe vers le bon tag ;
- la documentation de validation est a jour.

### Etape 8 - Installation de validation

Pour V1.0 :

- installation manuelle du ZIP ;
- activation ;
- verification shortcodes ;
- verification CPT ;
- verification absence d'erreur ;
- rollback manuel si probleme.

Pour V1.1 :

- installation initiale par ZIP ;
- detection d'une version superieure depuis WordPress ;
- mise a jour depuis l'admin WordPress ;
- verification que le ZIP GitHub Release est bien utilise ;
- verification que le plugin reste actif ;
- rollback manuel si probleme.

## Regle V1.0

Pour V1.0, GitHub Releases prepare la distribution, mais ne remplace pas la validation manuelle.

```text
Pas d'auto-update V1.0.
Pas de Plugin Update Checker V1.0.
Pas de secret.
Pas de serveur d'update.
```

## Regle V1.1

V1.1 utilise ces conventions comme base pour la mise a jour depuis l'admin WordPress.

```text
Plugin Update Checker devient priorite V1.1.
GitHub Releases devient le canal de release.
Le ZIP release devient l'asset d'installation et de mise a jour.
```

Toute integration d'auto-update doit faire l'objet d'une revue de securite et d'une validation sur site de test.

## Regle V1.2

V1.2 est reservee au module Quotes/Citations si le besoin est revalide.

Quotes/Citations ne doit pas etre integre en V1.1 afin de ne pas retarder la maintenance multi-sites.

## Regle V2

V2 est reservee aux styles avances, modules configurables, reglages admin et ACF optionnel.
