# ROADMAP - WP-seed-content-kit

Date : 12 juin 2026
Statut : roadmap produit stricte
Nature : cadrage des versions V1.0, V1.1, V1.2, V2

## Principe directeur

WP-seed-content-kit est un framework editorial WordPress reutilisable.

Le coeur du projet reste double :

```text
contenus structures + affichages reutilisables
```

La roadmap doit proteger le projet contre la derive fonctionnelle.

Toute fonctionnalite non explicitement incluse dans une version est exclue de cette version.

## Regles permanentes

- Plugin WordPress d'abord.
- Aucun theme impose.
- Aucune dependance externe obligatoire.
- ACF optionnel, jamais obligatoire.
- Shortcodes universels.
- CSS prefixe `seed-`.
- Compatibilite Divi 4, Divi 5, Astra, Spectra et Gutenberg par shortcodes.
- Aucun deploiement sans validation explicite.
- Aucun site WordPress modifie sans validation explicite.
- Aucun secret.
- Aucun appel externe en V1.
- Aucun CPT cree sans justification produit.

## V1.0 - Socle minimal reutilisable

### Objectifs

Prouver que le projet peut exister comme plugin WordPress autonome, installable plus tard par ZIP, avec un contenu structure simple et un affichage reutilisable simple.

V1.0 doit fournir uniquement :

- squelette plugin WordPress ;
- architecture modulaire minimale ;
- module `testimonials` ;
- CPT `seed_testimonial` ;
- champs natifs pour temoignages ;
- shortcode `[seed_testimonials]` ;
- module `cards` pour articles WordPress natifs ;
- shortcode `[seed_cards]` ;
- CSS responsive prefixe `seed-` ;
- documentation minimale ;
- validation locale ;
- validation sur site laboratoire.

V1.0 ne doit pas chercher a couvrir tous les futurs cas metiers.

### Criteres de sortie

V1.0 peut etre consideree terminee quand :

- le plugin s'active localement sans erreur fatale ;
- le CPT `seed_testimonial` est disponible dans l'administration WordPress ;
- les champs temoignage natifs sont sauvegardes correctement ;
- les temoignages sans consentement ne sont pas affiches ;
- `[seed_testimonials]` affiche les temoignages publies et consentis ;
- `[seed_cards]` affiche des articles WordPress natifs ;
- les attributs V1 documentes fonctionnent ;
- le CSS reste confine aux classes `seed-` ;
- aucun style global agressif n'est present ;
- aucun code ne depend de Divi, Astra, Spectra ou Gutenberg ;
- aucun code ne depend d'ACF ;
- aucun appel externe n'est present ;
- la documentation minimale d'installation et d'usage existe ;
- le packaging ZIP probable est verifie.

### Criteres de validation

Validation technique minimale :

- syntaxe PHP validee ;
- activation/desactivation testee localement ;
- absence de warning PHP visible ;
- verification des nonces ;
- verification des capabilities ;
- verification de la sanitization ;
- verification de l'escaping ;
- verification de l'absence de secrets ;
- verification de l'absence d'appels HTTP externes ;
- verification de l'absence de dependances Composer, npm ou framework CSS/JS.

Validation WordPress :

- test shortcode dans Gutenberg ;
- test shortcode dans un contexte compatible Spectra ;
- test shortcode dans Divi 4 ;
- test shortcode dans Divi 5 si environnement disponible ;
- test avec theme Astra ;
- test responsive mobile, tablette, desktop ;
- test avec categorie absente pour `[seed_cards]` ;
- test avec aucun article disponible ;
- test avec aucun temoignage consenti.

Validation site :

- tout test sur site WordPress doit etre precede d'une validation explicite ;
- aucune activation publique sans sauvegarde et strategie de rollback.

### Risques

- ZIP incomplet si README ou docs plugin manquent.
- Syntaxe PHP non validee si PHP CLI indisponible.
- `flush_rewrite_rules()` modifie les regles de permaliens a l'activation/desactivation.
- Conflits CSS possibles avec builders si le rendu n'est pas teste.
- Confusion produit si des modules futurs sont ajoutes trop tot.
- Derive possible vers un plugin de CPT si la couche d'affichage n'est pas preservee.
- Derive possible vers une bibliotheque de shortcodes si les contenus structures sont negliges.

### Dependances

Dependances autorisees :

- WordPress natif ;
- PHP compatible WordPress ;
- CSS natif ;
- navigateur pour validation responsive.

Dependances interdites en V1.0 :

- ACF obligatoire ;
- Composer ;
- npm ;
- framework PHP externe ;
- framework JS ;
- framework CSS ;
- theme specifique ;
- builder specifique ;
- service externe.

## V1.1 - Mises a jour GitHub via admin WordPress

### Objectifs

Faciliter la maintenance multi-sites.

V1.1 doit permettre d'installer WP-seed-content-kit une premiere fois par ZIP, puis de recevoir les mises a jour depuis le gestionnaire d'extensions WordPress via GitHub Releases.

V1.1 devient prioritaire avant tout nouveau module editorial.

V1.1 peut ajouter :

- convention de version stricte ;
- publication GitHub Releases ;
- ZIP release attache a chaque release ;
- integration Plugin Update Checker ;
- detection des mises a jour dans l'admin WordPress ;
- mise a jour depuis l'ecran Extensions ;
- rollback manuel documente ;
- validation multi-sites controlee.

V1.1 ne doit pas ajouter :

- nouveau module editorial ;
- module Quotes/Citations ;
- page de reglages admin ;
- licences ;
- token ou secret embarque ;
- serveur d'update dedie ;
- dependance a un depot prive avec authentification obligatoire.

### Criteres de sortie

V1.1 peut etre consideree terminee quand :

- une release GitHub publie un ZIP installable ;
- le ZIP respecte la structure `wp-seed-content-kit/` ;
- WordPress detecte une version superieure ;
- WordPress propose la mise a jour dans l'admin ;
- la mise a jour s'execute depuis l'ecran Extensions ;
- le plugin reste actif apres mise a jour ;
- les shortcodes V1.0 restent stables ;
- le CPT `seed_testimonial` reste disponible ;
- aucun secret n'est present dans le plugin ;
- aucun token GitHub n'est requis en V1.1 ;
- le rollback manuel est documente.

### Criteres de validation

Validation technique :

- test de detection update depuis une version inferieure ;
- test de telechargement du ZIP release ;
- test de mise a jour admin ;
- test d'absence d'erreur fatale apres update ;
- test de maintien des contenus existants ;
- test de desactivation apres update ;
- verification que le ZIP n'inclut aucun secret ;
- verification que Composer n'est pas requis sur le site.

Validation produit :

- l'administrateur n'a pas besoin de supprimer/reinstaller le plugin a chaque version ;
- le canal GitHub Releases est comprehensible ;
- le rollback manuel reste possible ;
- la strategie reste compatible avec des sites prives.

### Risques

- Ajouter une dependance embarquee trop tot.
- Mal configurer le ZIP release et casser l'installation.
- Introduire un appel externe non documente.
- Rendre les sites dependants d'un depot prive inaccessible.
- Publier une release avec version, tag et ZIP incoherents.
- Confondre maintenance multi-sites et fonctionnalites editoriales.

### Dependances

Dependances autorisees :

- WordPress natif ;
- GitHub Releases comme source de distribution ;
- Plugin Update Checker embarque dans le ZIP ;
- ZIP release public ou accessible sans secret.

Dependances interdites en V1.1 :

- token GitHub embarque ;
- secret dans le plugin ;
- Composer requis sur site ;
- depot prive obligatoire ;
- serveur d'update maison ;
- service externe autre que GitHub Releases.

## V1.2 - Module Quotes/Citations

### Objectifs

Ajouter un module editorial separe pour citations, apres stabilisation du socle V1.0 et de la maintenance multi-sites V1.1.

Quotes/Citations peut etre pertinent si les citations doivent etre :

- gerees comme contenus reutilisables ;
- affichees a plusieurs endroits ;
- inserees par shortcode ;
- separees des articles natifs ;
- distinguees des temoignages ;
- affichees dans des cartes ou blocs courts ;
- filtrees par contexte ou mise en avant.

Quotes/Citations reste coherent avec le coeur du projet :

```text
contenus structures + affichages reutilisables
```

### Perimetre minimal propose

Si Quotes/Citations entre en V1.2, le perimetre minimal propose est :

- module separe `quotes` ;
- CPT `seed_quote` ;
- shortcode `[seed_quotes]` ;
- champs natifs simples :
  - texte ;
  - auteur ;
  - source ;
  - contexte ;
  - lien source optionnel ;
  - mise en avant ;
- rendu HTML simple ;
- reutilisation du CSS `seed-` existant quand possible ;
- attributs shortcode limites :
  - `limit` ;
  - `columns` ;
  - `featured` ;
  - `context` ;
- etat vide ;
- aucune dependance ACF ;
- aucun import automatique ;
- aucune taxonomie dediee en V1.2 ;
- aucune page de reglages admin ;
- aucune migration automatique depuis articles existants.

Quotes doit etre un module separe, pas une extension de `testimonials`.

### Risques

- Creer trop de CPT trop tot.
- Confondre citations et temoignages.
- Ajouter une logique bibliographique trop complexe.
- Introduire une gestion de sources trop lourde.
- Deriver vers un module d'import de contenus.
- Coder des besoins propres a un seul site.
- Retarder la stabilisation de la maintenance multi-sites.
- Multiplier les variantes de cartes avant V2.

### Criteres d'entree en V1.2

Quotes/Citations peut entrer en V1.2 uniquement si :

- V1.0 est validee ;
- V1.1 update infrastructure est validee ;
- les shortcodes V1.0 restent stables ;
- le CSS V1.0 reste stable ;
- le besoin Quotes/Citations est confirme par un usage reel ;
- les citations doivent etre reutilisees hors articles natifs ;
- le module peut rester separe de `testimonials` ;
- le module peut rester sans ACF ;
- le module peut rester sans import ;
- le module peut rester sans taxonomie dediee ;
- le module peut etre teste sans modifier un site de production ;
- la creation d'un CPT `seed_quote` est explicitement revalidee.

## V2 - Styles avances, modules configurables et ACF optionnel

### Objectifs

Ameliorer l'experience d'administration, les styles et la configurabilite du socle, sans casser la compatibilite V1.x et sans rendre ACF obligatoire.

V2 peut ajouter :

- page de reglages admin ;
- activation/desactivation de modules ;
- integration ACF optionnelle ;
- detection de presence ACF ;
- fallbacks natifs si ACF est absent ;
- field groups ACF optionnels ;
- variations de cartes ;
- systeme de styles :
  - `minimal` ;
  - `soft` ;
  - `compact` ;
  - `image-left` ;
- filtres supplementaires de shortcodes ;
- templates surchargeables simples ;
- documentation par cas d'usage ;
- meilleure UX admin pour les modules existants ;
- metadata legere pour articles natifs si besoin confirme.

### Criteres de sortie

V2 peut etre consideree terminee quand :

- les modules V1.x restent compatibles ;
- les shortcodes V1.x restent stables ;
- ACF peut ameliorer l'UX sans devenir obligatoire ;
- le plugin fonctionne avec ACF absent ;
- le plugin fonctionne avec ACF actif ;
- les modules peuvent etre actives/desactives sans casser les contenus existants ;
- les reglages admin sont simples et documentes ;
- les variantes de cartes restent CSS-scopees ;
- les templates surchargeables ne rendent pas un theme enfant obligatoire.

### Criteres de validation

Validation technique :

- tests de regression V1.x complets ;
- tests avec ACF absent ;
- tests avec ACF actif ;
- tests de sauvegarde des reglages admin ;
- verification des capabilities admin ;
- verification des nonces admin ;
- verification de l'absence d'erreur si un module est desactive ;
- verification du maintien des shortcodes V1.x.

Validation produit :

- chaque nouveau reglage doit resoudre un besoin confirme ;
- chaque variation de carte doit etre reutilisable ;
- aucun CPT ne doit etre ajoute sans justification ;
- aucune configuration ne doit imposer un site pilote.

### Risques

- Ajouter trop de reglages trop tot.
- Rendre l'administration plus complexe que le besoin reel.
- Rendre ACF implicitement necessaire.
- Introduire des comportements differents selon les themes.
- Multiplier les variantes visuelles sans gouvernance.
- Casser les shortcodes V1.x.

### Dependances

Dependances autorisees :

- WordPress natif ;
- ACF uniquement si present et detecte ;
- CSS natif ;
- hooks et filtres WordPress.

Dependances interdites en V2 :

- ACF obligatoire ;
- theme obligatoire ;
- builder obligatoire ;
- service externe obligatoire ;
- framework lourd obligatoire.

## V3 - Import, builders et industrialisation

### Objectifs

Industrialiser le framework apres validation des usages reels, avec des outils d'import, des integrations plus poussees et des presets reutilisables.

V3 peut ajouter :

- import Word vers brouillon ;
- import PDF vers brouillon ;
- workflow editorial d'import ;
- blocs Gutenberg dedies ;
- module Divi dedie eventuel ;
- presets metiers ;
- migration depuis prototypes ;
- export/import de configuration ;
- outillage multi-sites ;
- modules avances selon besoins confirmes :
  - ressources ;
  - equipe ;
  - evenements ;
  - formations catalogue.

### Criteres de sortie

V3 peut etre consideree terminee quand :

- les fonctionnalites V1 et V2 restent compatibles ;
- les imports creent des brouillons, pas des publications automatiques ;
- les imports ne modifient pas les contenus existants sans confirmation ;
- les blocs Gutenberg restent optionnels ;
- le module Divi reste optionnel ;
- les presets metiers peuvent etre actives sans coupler le coeur a un site ;
- les migrations depuis prototypes sont documentees et reversibles autant que possible ;
- l'export/import de configuration ne contient pas de secrets.

### Criteres de validation

Validation technique :

- tests de regression V1 ;
- tests de regression V2 ;
- tests d'import sur fichiers exemples ;
- tests de rollback ;
- tests de non-modification des contenus existants ;
- tests multi-sites ;
- tests Gutenberg ;
- tests Divi si module dedie ajoute ;
- verification de l'absence d'envoi de donnees a un service externe sans consentement explicite.

Validation produit :

- chaque module avance doit correspondre a un besoin recurrent ;
- chaque preset doit rester separable du coeur ;
- chaque migration doit avoir une strategie de sauvegarde ;
- aucun outil d'import ne doit publier automatiquement.

### Risques

- Transformer le plugin en builder.
- Transformer le plugin en theme de fait.
- Ajouter des imports trop dangereux.
- Coupler les presets a un site pilote.
- Multiplier les CPT sans besoin stable.
- Rendre le plugin difficile a maintenir.
- Introduire des dependances lourdes.

### Dependances

Dependances possibles mais a justifier :

- bibliotheques d'import optionnelles ;
- outils de build de developpement si le ZIP reste autonome ;
- APIs WordPress natives ;
- integrations Gutenberg ou Divi optionnelles.

Dependances interdites sans decision explicite :

- service externe obligatoire ;
- publication automatique sans validation ;
- theme obligatoire ;
- builder obligatoire ;
- dependance qui empeche l'installation ZIP autonome.

## Frontiere stricte

V1.0 livre le socle minimal.

V1.1 livre la maintenance multi-sites via mises a jour GitHub depuis l'admin WordPress.

V1.2 peut ajouter Quotes/Citations.

V2 ameliore les styles, les modules configurables et l'optionnalite ACF.

V3 industrialise les workflows avances.

Toute demande nouvelle doit etre classee avant implementation :

- V1 si elle est indispensable au MVP strict ;
- V2 si elle ameliore l'UX ou la configurabilite ;
- V3 si elle concerne import, builders dedies, presets ou migration ;
- hors roadmap si elle impose un theme, une dependance obligatoire ou un couplage site.
