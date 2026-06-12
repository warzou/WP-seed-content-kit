# PUC TECHNICAL STUDY V1.1 - WP-seed-content-kit

Date : 12 juin 2026
Statut : etude technique
Decision produit : V1.1 = mises a jour GitHub via admin WordPress

## Objectif

Determiner l'implementation la plus simple et la plus robuste pour permettre :

```text
installation initiale par ZIP
↓
mises a jour depuis l'admin WordPress
↓
source GitHub Releases
```

Aucun code n'est implemente par ce document.

## Sources consultees

- Plugin Update Checker, depot officiel : https://github.com/YahnisElsts/plugin-update-checker
- Plugin Update Checker, releases : https://github.com/YahnisElsts/plugin-update-checker/releases
- Plugin Update Checker, composer metadata : https://raw.githubusercontent.com/YahnisElsts/plugin-update-checker/master/composer.json

## 1. Plugin Update Checker

Plugin Update Checker est une librairie PHP pour plugins et themes WordPress qui permet d'ajouter :

- notifications de mise a jour ;
- details de version ;
- mise a jour depuis l'UI native WordPress ;
- sources hors WordPress.org.

Le projet indique explicitement viser les plugins commerciaux, themes prives et autres projets qui ne sont pas heberges sur WordPress.org mais doivent beneficier d'un comportement proche des mises a jour natives.

Pour WP-seed-content-kit, cela correspond exactement au besoin V1.1.

## 2. Points verifies

### Compatibilite GitHub Releases

Statut : compatible.

Plugin Update Checker supporte plusieurs modes GitHub :

- releases GitHub ;
- tags Git ;
- branche stable.

Pour WP-seed-content-kit, le mode recommande est :

```text
GitHub Releases + release asset ZIP
```

Raison :

- le ZIP release est controle ;
- le contenu installe correspond au packaging teste ;
- les prereleases GitHub peuvent etre ignorees ;
- les notes de release peuvent alimenter l'ecran de details WordPress.

Point important :

- utiliser les release assets, pas l'archive source GitHub brute ;
- filtrer l'asset sur `wp-seed-content-kit.zip` si possible.

### Compatibilite plugins publics/prives

Depot public :

- compatible sans secret ;
- choix recommande pour V1.1 ;
- les sites WordPress peuvent verifier les releases GitHub sans token.

Depot prive :

- techniquement possible avec authentification ;
- necessite un token d'acces ;
- non recommande en V1.1 car le projet interdit les secrets dans le plugin.

Decision V1.1 :

```text
Depot/release accessible sans token.
Pas de token GitHub dans le plugin.
Pas de depot prive obligatoire.
```

### Dependances embarquees

Plugin Update Checker est une librairie PHP.

Le projet fournit une integration par copie du dossier `plugin-update-checker` dans le plugin, puis chargement depuis le fichier principal.

Composer existe pour le developpement ou l'autoload, mais WP-seed-content-kit ne doit pas exiger Composer sur les sites.

Decision V1.1 :

```text
Librairie embarquee dans le ZIP final.
Composer non requis sur site.
npm non requis.
```

### Maintenance du projet

Signaux observes :

- depot public actif ;
- environ 2.5k stars ;
- plusieurs centaines de commits ;
- releases recentes ;
- version recente observee : 5.7, publiee le 26 mai ;
- corrections recentes liees a PHP 8.4 et securite/compatibilite WordPress.

Interpretation :

- projet mature ;
- maintenance encore active ;
- usage large dans l'ecosysteme WordPress hors WordPress.org.

Risque :

- dependance maintenue par un projet externe ;
- necessite une veille minimale a chaque update majeur de WordPress/PHP.

### Licence

Licence declaree : MIT.

Interpretation :

- compatible avec une distribution embarquee dans WP-seed-content-kit ;
- autorise l'utilisation, la copie, la modification et la redistribution sous conditions de conservation de la notice de licence.

Obligation V1.1 :

- conserver la licence Plugin Update Checker dans le ZIP ;
- documenter la dependance embarquee.

### Perennite

Points favorables :

- API ciblee WordPress ;
- integration historique ;
- support GitHub Releases ;
- integration avec l'UI native WordPress ;
- maintenance recente.

Points de vigilance :

- depend de comportements internes de l'update system WordPress ;
- depend de l'API GitHub et de ses limites ;
- risque de changement de namespace/API entre versions majeures de PUC ;
- besoin de tests de regression apres mise a jour de PUC.

Conclusion perennite :

```text
Perennite acceptable pour V1.1 si la version de PUC est vendoree et pinnee.
```

## 3. Architecture cible V1.1

### Depot GitHub

Depot cible :

```text
WP-seed-content-kit
```

Regles :

- depot public ou releases publiques ;
- pas de secret requis pour consulter les releases ;
- branche principale utilisee pour le developpement ;
- releases GitHub utilisees comme source de distribution.

### Tags

Convention :

```text
v0.1.1
v0.2.1
v0.3.0
v1.0.0
```

Regles :

- le tag correspond a la version du plugin ;
- le tag pointe vers le commit exact utilise pour generer le ZIP ;
- ne jamais reutiliser un tag publie.

### Releases

Chaque release GitHub doit contenir :

- titre : `vX.Y.Z` ;
- notes de version ;
- statut : test/candidate/stable si necessaire ;
- asset `wp-seed-content-kit.zip`.

V1.1 doit ignorer les prereleases pour les sites standards.

### ZIP

Asset cible :

```text
wp-seed-content-kit.zip
```

Structure interne obligatoire :

```text
wp-seed-content-kit/
  wp-seed-content-kit.php
  includes/
  assets/
  README.md
  docs/
  vendor-or-lib/plugin-update-checker/
```

Le nom exact du dossier librairie sera decide a l'implementation, mais il doit rester clair et exclure tout outil de developpement inutile.

Contraintes :

- chemins POSIX ;
- racine unique ;
- pas de `.git` ;
- pas de secrets ;
- pas de fichiers temporaires ;
- Composer non requis ;
- npm non requis.

### Detection de mise a jour

Principe cible :

- WP-seed-content-kit charge Plugin Update Checker ;
- PUC interroge GitHub Releases periodiquement ;
- PUC compare la version distante avec le header `Version` du plugin installe ;
- si version distante superieure, WordPress affiche une mise a jour disponible.

Source de verite locale :

- header `Version` du fichier principal ;
- constante plugin equivalente si ajoutee plus tard.

Source de verite distante :

- GitHub Release/tag ;
- header `Version` dans le ZIP release ;
- asset ZIP attache a la release.

### Ecran WordPress

Experience administrateur cible :

- extension installee une premiere fois par ZIP ;
- une nouvelle version est publiee sur GitHub ;
- WordPress affiche une notification dans Extensions ;
- l'administrateur clique sur mettre a jour ;
- WordPress telecharge le ZIP release ;
- le plugin est remplace ;
- les contenus et options restent en place ;
- le plugin reste activable/desactivable normalement.

## 4. Workflow complet

```text
Codex
↓
modification code/documentation
↓
tests locaux
↓
generation ZIP POSIX
↓
verification ZIP
↓
commit
↓
tag vX.Y.Z
↓
GitHub Release
↓
asset wp-seed-content-kit.zip
↓
Plugin Update Checker detecte la version
↓
notification WordPress
↓
administrateur clique "mettre a jour"
↓
WordPress installe le ZIP
↓
verification post-update
```

### Role de Codex

Codex prepare :

- changements strictement scopes ;
- version coherente ;
- ZIP verifie ;
- changelog ;
- note de release ;
- checklist de validation.

Codex ne doit pas :

- publier sans validation explicite ;
- integrer de secret ;
- changer le canal de release sans decision ;
- modifier les sites WordPress sans validation.

### Role du commit

Le commit doit representer l'etat exact de la release.

Avant tag :

- version alignee ;
- tests passes ;
- ZIP reproductible ;
- changelog pret.

### Role du tag

Le tag identifie la version source.

Format :

```text
v0.1.1
```

Le tag ne doit pas etre modifie apres publication.

### Role de la release

La release fournit :

- notes de version ;
- asset ZIP ;
- version detectable ;
- source stable pour les sites.

### Role de WordPress

WordPress reste l'interface administrateur :

- detection ;
- notification ;
- details ;
- execution de la mise a jour.

## 5. Risques

### Mauvais ZIP installe

Risque :

- WordPress installe l'archive source GitHub au lieu du ZIP plugin.

Mitigation :

- utiliser les release assets ;
- filtrer explicitement `wp-seed-content-kit.zip` ;
- verifier la structure interne avant release.

### Secrets

Risque :

- besoin de token si depot prive.

Mitigation :

- V1.1 utilise des releases publiques ou accessibles sans token ;
- aucun token dans le plugin.

### Limites GitHub API

Risque :

- rate limit ou indisponibilite temporaire.

Mitigation :

- accepter une detection periodique ;
- ne pas rendre le plugin dependant de GitHub pour fonctionner au quotidien ;
- documenter que l'update peut etre retente plus tard.

### Version incoherente

Risque :

- header plugin, tag et release divergent.

Mitigation :

- checklist release obligatoire ;
- ne pas publier si les versions divergent.

### Dependence externe

Risque :

- PUC change d'API ou devient moins maintenu.

Mitigation :

- pinner une version PUC ;
- garder la librairie vendoree ;
- tester toute mise a jour PUC separement.

### Echec update WordPress

Risque :

- permissions fichiers, timeout, plugin partiellement remplace.

Mitigation :

- sauvegarde avant update ;
- rollback manuel ;
- conserver le ZIP precedent.

### Confusion V1.1 / V1.2

Risque :

- ajouter Quotes/Citations pendant l'infrastructure update.

Mitigation :

- V1.1 strictement reservee a la maintenance multi-sites ;
- Quotes/Citations reporte en V1.2.

## 6. Verdict

```text
RECOMMENDED
```

Justification :

- Plugin Update Checker repond directement au besoin V1.1 ;
- GitHub Releases sont supportees ;
- les release assets ZIP sont supportes ;
- l'UI WordPress native est conservee ;
- le projet est mature et encore maintenu ;
- la licence MIT est compatible avec l'embarquement ;
- l'approche reste simple si le depot/release asset est public et sans token.

Condition de recommandation :

```text
Utiliser GitHub Releases publiques + asset wp-seed-content-kit.zip + PUC vendore et pinne.
```

Non recommande si :

- le depot doit rester strictement prive sans serveur d'update dedie ;
- un token doit etre embarque dans le plugin ;
- le ZIP release ne peut pas etre garanti ;
- la validation update admin ne peut pas etre faite sur site de test.

## Decision technique proposee

Pour V1.1 :

- utiliser Plugin Update Checker ;
- utiliser GitHub Releases ;
- utiliser release asset `wp-seed-content-kit.zip` ;
- ne pas utiliser l'archive source GitHub ;
- ne pas utiliser de token ;
- ne pas introduire de page de reglages admin ;
- ne pas ajouter Quotes/Citations ;
- documenter rollback manuel.
