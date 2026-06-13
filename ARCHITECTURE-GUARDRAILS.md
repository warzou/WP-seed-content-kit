# ARCHITECTURE GUARDRAILS - WP-seed-content-kit

## 1. Intention

WP-seed-content-kit doit rester un framework editorial WordPress reutilisable, pas un theme, pas un builder, pas une collection de hacks projet.

## 2. Principes fondamentaux

### Le coeur est double

Le projet porte deux couches de valeur :

1. contenus structures ;
2. affichages reutilisables.

Ces couches doivent rester decouplees.

### Les CPT sont des modules

Un CPT n'est pas une obligation.

Un CPT est un module active quand le besoin metier le justifie.

### Les affichages sont independants

Les grilles, cartes et listes doivent pouvoir afficher :

- des articles natifs ;
- des CPT ;
- des contenus filtres ;
- des contenus futurs.

Ne pas coder les affichages comme s'ils etaient reserves a un seul CPT.

## 3. Frontieres a respecter

### Ne pas imposer de theme

Interdit :

- dependance obligatoire a Astra ;
- dependance obligatoire a Divi ;
- dependance obligatoire a Spectra ;
- logique metier dans `functions.php` ;
- obligation de theme enfant en V1.

Autorise :

- compatibilite avec ces themes/builders ;
- hooks optionnels ;
- CSS surchargeable ;
- documentation d'integration.

### Ne pas imposer ACF

ACF peut etre supporte, pas requis.

V1 doit fonctionner sans ACF.

Si ACF est ajoute :

- detection de presence ;
- integration optionnelle ;
- fallback natif ;
- aucune erreur si ACF est absent.

### Ne pas ajouter de dependances lourdes

Interdit en V1 :

- framework PHP externe obligatoire ;
- framework JS obligatoire ;
- CSS framework obligatoire ;
- build tool obligatoire pour utiliser le plugin.

Autorise plus tard :

- outils de build pour developpement, si le plugin ZIP reste autonome ;
- librairie optionnelle justifiee.

## 4. Regles CPT

Avant de creer un CPT, repondre oui a plusieurs questions :

- le contenu a-t-il une structure stable ?
- doit-il etre gere hors du flux blog/journal ?
- doit-il etre reutilise sur plusieurs pages ?
- a-t-il besoin de champs propres ?
- a-t-il besoin d'une archive dediee ?
- le contenu serait-il confus comme simple article ?

Si la reponse est non, utiliser WordPress natif.

Exemples :

```text
Temoignages -> CPT probable
Articles de journal -> post natif
Actualites simples -> post natif
Stages rares -> post natif + metas
Stages catalogue -> CPT possible
Ressources structurees -> CPT possible
```

## 5. Regles d'affichage

Les affichages doivent etre :

- reutilisables ;
- accessibles par shortcode ;
- compatibles builders ;
- decouples des themes ;
- stylables sans modifier le plugin ;
- responsables de leur CSS uniquement.

Chaque affichage doit definir :

- requete ;
- rendu HTML ;
- classes CSS ;
- attributs shortcode ;
- etat vide ;
- comportement responsive.

## 6. Shortcodes

Les shortcodes sont obligatoires comme couche V1.

Ils doivent :

- fonctionner dans Divi 4 ;
- fonctionner dans Divi 5 ;
- fonctionner dans Gutenberg ;
- fonctionner dans Spectra ;
- fonctionner dans un widget ou editeur classique si possible.

Ils ne doivent pas :

- supposer un theme ;
- injecter du JS lourd ;
- casser si une categorie est absente ;
- afficher des erreurs PHP publiques.

### Generateurs de shortcodes

Les generateurs admin doivent rester une aide a la construction de shortcodes explicites.

Modele standard :

```text
module -> attributes schema -> admin generator -> shortcode text -> frontend render
```

Regles :

- preferer les shortcodes explicites generes puis copies dans les pages ;
- les generateurs sont non persistants par defaut ;
- aucun reglage global ne doit modifier silencieusement les pages existantes ;
- une modification admin ne doit pas changer le rendu d'une page si son shortcode n'a pas change ;
- ne pas standardiser les champs metier entre modules ;
- mutualiser la mecanique, pas les responsabilites editoriales ;
- documenter les attributs publics de chaque shortcode.

Interdit :

- builder interne ;
- layout editable dans l'admin ;
- presets enregistrables avant decision V2 ;
- reglage global d'affichage applique automatiquement aux shortcodes existants ;
- detection builder obligatoire ;
- integration native Divi, Spectra, Astra ou Gutenberg pour remplacer les shortcodes.

## 7. CSS

Regles :

- toutes les classes publiques doivent etre prefixees ;
- pas de selecteurs globaux ;
- pas de reset ;
- pas de styles sur `body`, `main`, `section`, `article`, `h1`, `h2`, `a`, `img` sans classe ;
- responsive sans JS ;
- pas d'utilisation obligatoire d'un framework CSS.

Prefixe framework recommande :

```text
seed-
```

## 8. Architecture modulaire cible

Structure conceptuelle :

```text
plugin/
├── core/
│   ├── assets
│   ├── helpers
│   ├── modules
│   └── settings
├── modules/
│   ├── content-module-a/
│   ├── display-module-a/
│   └── integration-module-a/
├── integrations/
│   ├── acf
│   ├── divi
│   ├── astra
│   ├── spectra
│   └── gutenberg
└── assets/
```

Un module peut etre :

- contenu structure ;
- affichage ;
- integration ;
- outil d'import ;
- aide admin.

## 9. Compatibilite

### Divi 4

Valider :

- shortcode dans module Code ;
- shortcode dans module Texte ;
- rendu responsive ;
- pas de conflit CSS.

### Divi 5

Valider :

- shortcode ;
- rendu stable ;
- CSS scoped ;
- absence de dependance a l'ancien DOM Divi.

### Astra

Valider :

- typographie heritee correctement ;
- aucune dependance Astra ;
- aucun besoin de theme enfant.

### Spectra

Valider :

- shortcode dans bloc Spectra/Gutenberg ;
- pas de conflit de grille ;
- pas de conflit de boutons.

### Gutenberg

Valider :

- shortcode ;
- rendu front ;
- absence d'erreur editeur.

## 10. Installation ZIP

Le plugin doit pouvoir etre livre sous forme :

```text
wp-seed-content-kit.zip
```

Le ZIP doit contenir :

```text
wp-seed-content-kit/
├── wp-seed-content-kit.php
├── includes/
├── assets/
├── README.md
└── docs/
```

Le plugin ne doit pas exiger :

- Composer ;
- npm ;
- compilation ;
- ACF ;
- theme specifique.

## 11. Securite et sauvegarde

Avant toute installation sur un site :

- sauvegarde base de donnees ;
- sauvegarde fichiers ;
- sauvegarde uploads ;
- sauvegarde plugins/themes ;
- verification restaurabilite minimale.

Le plugin ne doit jamais :

- afficher des secrets ;
- stocker des identifiants externes sans besoin ;
- envoyer des donnees a un service externe en V1 ;
- modifier en masse des contenus sans confirmation.

### Release Safety - WordPress Packaging Safety

Symptôme observé :

- après mise a jour, WordPress peut afficher : `Le fichier de l'extension n'existe pas.`

Cause racine :

- ZIP GitHub Release avec chemins internes en style Windows (`\`).
- exemple incorrect : `wp-seed-content-kit\wp-seed-content-kit.php`
- exemple correct : `wp-seed-content-kit/wp-seed-content-kit.php`

Conséquence :

- WordPress ne retrouve plus le fichier principal du plugin lors de l'activation.

Règle de garde :

Avant validation d'une release WordPress, vérifier :

1) le ZIP local contient le chemin exact `wp-seed-content-kit/wp-seed-content-kit.php` ;
2) le nombre d'entrées contenant `\` est nul ;
3) le ZIP public GitHub retéléchargé passe les mêmes vérifications ;
4) le SHA256 local/public est comparé.

## 12. Roadmap guardrails

### V1

Autorise :

- modules de contenus structures simples ;
- cartes pour articles natifs ;
- shortcodes ;
- CSS scoped ;
- meta boxes natives ;
- documentation.

Eviter :

- ACF obligatoire ;
- import Word/PDF ;
- CPT Stage/Actualite par defaut ;
- integration builder profonde.

### V2

Autorise :

- reglages admin ;
- modules activables ;
- ACF optionnel ;
- variantes de cartes ;
- meilleurs presets.

### V3

Autorise :

- import Word/PDF vers brouillon ;
- blocs Gutenberg ;
- module Divi dedie ;
- presets metiers ;
- migration depuis prototypes.

## 13. Definition d'une bonne contribution

Une contribution est acceptable si elle :

- respecte le decouplage contenu / affichage ;
- n'impose pas de theme ;
- n'impose pas ACF ;
- conserve les shortcodes ;
- garde le CSS prefixe ;
- ajoute ou met a jour les tests/documentations ;
- evite les abstractions inutiles ;
- reste compatible avec installation ZIP.

Une contribution doit etre refusee ou retravaillee si elle :

- met la logique dans un theme ;
- rend ACF obligatoire ;
- cree un CPT sans justification ;
- ajoute une dependance lourde ;
- cible un builder au detriment des autres ;
- ajoute du CSS global ;
- casse l'usage shortcode.
