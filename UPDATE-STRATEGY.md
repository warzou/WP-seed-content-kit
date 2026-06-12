# UPDATE STRATEGY - WP-seed-content-kit

Date : 12 juin 2026
Statut : document de strategie V1.1 prioritaire
Nature : maintenance multi-sites et mises a jour WordPress

## Objectif

WP-seed-content-kit doit rester facile a maintenir sur plusieurs sites WordPress.

L'objectif V1.1 est de livrer un canal de mise a jour fiable depuis le gestionnaire d'extensions WordPress, base sur GitHub Releases, sans compromettre les principes du projet :

- plugin autonome ;
- pas de secret embarque ;
- pas de dependance a un theme ;
- pas d'ACF obligatoire ;
- compatibilite sites prives ;
- rollback manuel possible ;
- tests obligatoires sur `avecguillaume.fr` avant tout site pilote.

Ordre produit retenu :

```text
V1.0 = socle actuel
V1.1 = mises a jour GitHub via admin WordPress
V1.2 = module Quotes/Citations
V2 = styles avances, modules configurables, ACF optionnel
```

V1.1 est une priorite produit, car l'objectif est de ne pas devoir desactiver, supprimer ou reinstaller manuellement le plugin a chaque version.

## Options comparees

### 1. ZIP manuel

Principe :

- generer `dist/wp-seed-content-kit.zip` ;
- installer ou remplacer le plugin manuellement via l'admin WordPress ou FTP ;
- conserver les notes de version dans la documentation projet.

Avantages :

- tres simple ;
- aucun appel externe depuis le plugin ;
- aucun code d'update a maintenir ;
- aucun secret ;
- parfait pour V1 et tests controles ;
- compatible avec tous les sites, publics ou prives.

Inconvenients :

- pas de notification de mise a jour dans WordPress ;
- risque d'ecart de version entre sites ;
- maintenance multi-sites manuelle ;
- plus fragile quand plusieurs sites pilotes existent.

Pertinence :

- choix correct pour V1.0 ;
- insuffisant des que plusieurs sites doivent suivre les releases regulierement.

### 2. GitHub Releases sans auto-update

Principe :

- creer un tag Git par version ;
- publier une GitHub Release ;
- attacher le ZIP installable `wp-seed-content-kit.zip` ;
- installer encore manuellement sur chaque site.

Avantages :

- historique clair des versions ;
- notes de release centralisees ;
- ZIP stable et telechargeable ;
- preparation naturelle vers update automatique ;
- aucun code supplementaire dans le plugin ;
- aucun secret dans WordPress.

Inconvenients :

- pas de notification native dans l'admin WordPress ;
- installation toujours manuelle ;
- demande une discipline de release stricte ;
- les sites ne savent pas automatiquement qu'une nouvelle version existe.

Pertinence :

- utile pour structurer les releases ;
- insuffisant pour V1.1, car il ne permet pas la mise a jour depuis l'admin WordPress.

### 3. GitHub Releases + Plugin Update Checker

Principe :

- embarquer la librairie Plugin Update Checker dans le plugin ;
- configurer le plugin pour lire les versions depuis GitHub Releases ;
- attacher le ZIP installable a chaque release ;
- WordPress detecte une nouvelle version et propose la mise a jour dans l'admin.

Avantages :

- experience proche des plugins officiels ;
- maintenance multi-sites beaucoup plus simple ;
- pas besoin de WordPress.org ;
- compatible avec un plugin prive si les releases sont publiques ou si un mecanisme d'acces est prevu plus tard ;
- GitHub Releases reste la source de verite ;
- pas de secret necessaire si le depot/releases sont publics.

Inconvenients :

- ajoute une dependance PHP embarquee ;
- ajoute des appels externes vers GitHub ;
- demande une revue securite ;
- demande des tests de mise a jour sur `avecguillaume.fr` ;
- impose une discipline stricte sur version PHP header, tag Git, release et ZIP ;
- les depots strictement prives compliquent l'absence de secret.

Pertinence :

- choix cible V1.1 ;
- doit etre implemente comme couche technique isolee, pas comme fonctionnalite editoriale.

### 4. WordPress.org

Principe :

- soumettre le plugin au repertoire officiel WordPress.org ;
- respecter les guidelines ;
- maintenir le plugin via SVN WordPress.org ;
- beneficier des mises a jour natives WordPress.

Avantages :

- canal officiel ;
- confiance utilisateur ;
- mises a jour natives ;
- distribution publique simple ;
- pas de librairie update checker a embarquer.

Inconvenients :

- revue WordPress.org ;
- workflow SVN supplementaire ;
- exposition publique du plugin ;
- charge de support public ;
- contraintes de readme, assets, stable tag, guidelines ;
- trop lourd tant que le plugin sert surtout des sites controles.

Pertinence :

- pas adapte a V1.1 ;
- a reevaluer en V3 ou plus tard si WP-seed-content-kit devient un produit public generaliste.

## Recommandation

Decision recommandee :

```text
OUI, Plugin Update Checker entre en priorite V1.1.
```

Condition :

Plugin Update Checker entre en V1.1 uniquement comme infrastructure de maintenance multi-sites, pas comme nouvelle fonctionnalite editoriale.

Justification :

- la priorite produit change vers la maintenance multi-sites ;
- le ZIP manuel est deja trop fragile a moyen terme ;
- WordPress.org est trop tot ;
- GitHub Releases seul ne resout pas la detection admin ;
- Plugin Update Checker repond directement au besoin : detecter et installer les updates depuis l'admin WordPress.

Cette decision doit etre encadree par des criteres stricts :

- aucune cle API dans le plugin ;
- aucun token GitHub dans le code ;
- depot ou release asset accessible sans authentification pour V1.1 ;
- librairie embarquee dans le ZIP final ;
- aucun Composer requis sur le site ;
- aucune configuration admin V1.1 obligatoire ;
- tests complets sur `avecguillaume.fr` avant tout autre site ;
- rollback manuel documente.

## Architecture V1.1 proposee

### 1. Versionner le plugin

Source de verite :

- header `Version` dans `wp-seed-content-kit.php` ;
- constante PHP interne, par exemple `WP_SEED_CONTENT_KIT_VERSION` ;
- tag Git correspondant, par exemple `v0.1.1`.

Regle :

- la version du header, la constante, le tag Git et la GitHub Release doivent correspondre ;
- aucune release ne doit etre publiee si ces valeurs divergent.

Convention recommandee :

```text
0.1.0 = V1 test initial
0.1.1 = V1.0.1 UX polish / packaging test
0.2.0 = V1.1 update infrastructure
```

### 2. Publier un ZIP release

Source :

```text
dist/wp-seed-content-kit.zip
```

Contraintes du ZIP :

- racine interne unique `wp-seed-content-kit/` ;
- fichier principal `wp-seed-content-kit/wp-seed-content-kit.php` ;
- chemins internes POSIX avec `/` ;
- pas d'entrees dossiers ;
- pas de `.git` ;
- pas de fichiers temporaires ;
- pas de secrets ;
- pas de docs racine projet inutiles ;
- librairie update checker incluse uniquement si V1.1 l'active.

Publication :

- creer un tag Git ;
- creer une GitHub Release ;
- attacher `wp-seed-content-kit.zip` ;
- ajouter notes de release courtes ;
- verifier le telechargement public du ZIP.

### 3. Detecter une mise a jour dans WordPress

Approche V1.1 :

- utiliser Plugin Update Checker ;
- pointer vers le depot GitHub ou la release GitHub ;
- preferer les GitHub Releases plutot qu'une branche de developpement ;
- utiliser les release assets pour que WordPress installe exactement le ZIP package.

Regle importante :

- WordPress ne doit jamais installer l'archive source GitHub brute si elle ne correspond pas au ZIP plugin attendu ;
- le package installe doit rester `wp-seed-content-kit/`.

### 4. Mettre a jour depuis l'admin

Experience cible :

- WordPress affiche une mise a jour disponible dans Extensions ;
- l'administrateur clique sur mettre a jour ;
- WordPress telecharge le ZIP release ;
- le plugin est remplace ;
- l'activation reste stable ;
- les contenus existants restent intacts.

Tests obligatoires V1.1 :

- detection d'une version superieure ;
- affichage de la mise a jour dans l'admin ;
- mise a jour depuis `0.1.x` vers `0.2.0` sur `avecguillaume.fr` ;
- absence d'erreur fatale ;
- shortcodes toujours actifs ;
- CPT toujours present ;
- meta testimonials conservees ;
- desactivation possible apres update.

### 5. Eviter les secrets dans le plugin

Regle V1.1 :

```text
Aucun secret dans le plugin.
```

Implications :

- pas de token GitHub embarque ;
- pas d'application password ;
- pas de cle API ;
- pas d'endpoint prive necessitant authentification ;
- pas de fichier `.env` dans le ZIP.

Choix recommande :

- depot public ou release asset public pour V1.1 ;
- si le code doit rester prive, reporter l'auto-update ou prevoir plus tard un serveur d'update dedie avec configuration explicite.

### 6. Rester compatible sites prives

Cas vise :

- sites WordPress prives, non publics ou clients ;
- plugin installe sur plusieurs sites controles ;
- GitHub peut etre public pour le plugin, meme si les sites sont prives.

Compatible V1.1 si :

- les sites peuvent sortir vers GitHub en HTTPS ;
- aucun identifiant site n'est envoye ;
- aucune donnee metier n'est transmise ;
- la requete d'update se limite aux metadonnees de version et au telechargement du ZIP.

Non couvert en V1.1 :

- depot GitHub prive avec token ;
- licence commerciale ;
- canal d'update par client ;
- serveur d'update maison.
- module Quotes/Citations ;
- styles avances ;
- activation/desactivation de modules via UI ;
- integration ACF.

### 7. Prevoir rollback manuel

Rollback V1.1 attendu :

- conserver le ZIP de la version precedente ;
- documenter la procedure :
  - desactiver le plugin si necessaire ;
  - supprimer ou remplacer le dossier plugin ;
  - reinstaller le ZIP precedent ;
  - reactiver ;
  - verifier shortcodes et CPT.

Regle donnees :

- le rollback ne doit pas supprimer les contenus WordPress ;
- les testimonials restent des posts `seed_testimonial` ;
- aucune migration destructive ne doit etre introduite en V1.1.

## Critere d'entree V1.1

Plugin Update Checker peut etre implemente en V1.1 seulement si :

- V1.0 est stable sur `avecguillaume.fr` ;
- le ZIP V1.0 est installable ;
- le repository GitHub cible est defini ;
- la strategie public/prive est tranchee ;
- le format de release ZIP est stable ;
- le rollback manuel est documente ;
- le responsable projet valide l'ajout de cette dependance embarquee.

## Critere de sortie V1.1

V1.1 update infrastructure est validee si :

- une release GitHub publie un ZIP installable ;
- WordPress detecte la mise a jour ;
- WordPress installe la mise a jour depuis l'admin ;
- le plugin reste actif apres update ;
- aucun secret n'est present dans le ZIP ;
- aucun contenu existant n'est modifie automatiquement ;
- rollback manuel teste ou documente ;
- validation effectuee d'abord sur `avecguillaume.fr`.

## Decision

Decision produit :

```text
Faire Plugin Update Checker en V1.1 : OUI, prioritaire.
```

Raison :

La maintenance multi-sites est une priorite V1.1. Dans ce contexte, GitHub Releases + Plugin Update Checker est le meilleur compromis entre autonomie, simplicite d'administration WordPress et controle du canal de release.

Limite :

Cette decision ne doit pas ouvrir la porte a des reglages admin, licences, tokens, serveur d'update dedie, marketplace, module Quotes/Citations ou styles avances. Ces sujets sont hors V1.1.

## Ordre de roadmap associe

### V1.0

Socle actuel :

- plugin installable par ZIP ;
- testimonials ;
- cards ;
- shortcodes ;
- CSS prefixe ;
- documentation et validation.

### V1.1

Maintenance multi-sites :

- GitHub Releases ;
- ZIP release standardise ;
- Plugin Update Checker ;
- detection update dans WordPress ;
- mise a jour depuis l'admin ;
- rollback manuel.

### V1.2

Module Quotes/Citations :

- module editorial separe ;
- CPT et shortcode dedies si revalides ;
- aucun impact sur l'infrastructure update.

### V2

Evolution produit :

- styles avances ;
- modules configurables ;
- page de reglages ;
- ACF optionnel ;
- variantes et templates.
