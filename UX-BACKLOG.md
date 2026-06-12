# UX BACKLOG - WP-seed-content-kit

Date : 12 juin 2026
Statut : backlog UX issu des audits visuels reels

## Contexte

Ce document consolide les points UX observes lors des validations visuelles de V1 sur `avecguillaume.fr`.

Il ne change pas le perimetre fonctionnel du plugin.

Contraintes maintenues :

- pas de changement d'API shortcode ;
- pas de nouveau module ;
- pas de dependance externe ;
- CSS prefixe `seed-` ;
- compatibilite themes/builders conservee ;
- ACF non requis.

## V1.0.1 - Deja corrige

Objectif : corriger uniquement les frictions UX visibles sans modifier l'architecture ni les shortcodes.

### Traductions visibles

Statut : corrige.

Elements corriges :

- `Read` remplace par `Lire` ;
- `No testimonials to display yet.` remplace par `Aucun temoignage a afficher pour le moment.`

Justification :

- le site de validation est francophone ;
- les textes anglais etaient visibles en front ;
- la correction ne change pas l'API shortcode.

### Placeholder image leger

Statut : corrige.

Element corrige :

- remplacement du gris plat par un fond CSS plus doux sur `.seed-card__image-placeholder`.

Justification :

- les cartes sans image etaient techniquement correctes mais visuellement pauvres ;
- le ratio existant est conserve ;
- aucun HTML ni comportement n'a ete ajoute.

## V2 - Ameliorations candidates

Objectif : ameliorer la finition visuelle des cartes sans ajouter de module ni changer le role des shortcodes.

### Placeholders

Probleme observe :

- les contenus sans image restent moins qualitatifs que les contenus avec image ;
- le placeholder peut sembler vide sur des cartes editoriales.

Piste V2 :

- affiner le placeholder CSS existant ;
- conserver le meme element HTML ;
- garder un rendu neutre et reutilisable multi-sites.

Critere d'acceptation :

- meilleure presence visuelle ;
- aucun texte ou icone obligatoire ;
- aucun couplage au site `avecguillaume.fr`.

### Style CTA

Probleme observe :

- le lien `Lire` est fonctionnel mais tres discret ;
- selon le theme, il peut manquer de hierarchie visuelle.

Piste V2 :

- ameliorer le style du lien `.seed-card__button` ;
- rester compatible avec l'heritage typographique du theme ;
- eviter un bouton trop marque qui imposerait une direction graphique.

Critere d'acceptation :

- CTA plus identifiable ;
- contraste suffisant ;
- style toujours sobre et reutilisable.

### Harmonisation hauteurs

Probleme observe :

- les hauteurs de cartes varient selon la presence d'image, la taille des images sources et la longueur des titres ;
- sur mobile, certaines cartes deviennent nettement plus longues que les autres.

Piste V2 :

- stabiliser davantage la zone media ;
- verifier l'effet des images reelles et des placeholders ;
- conserver le responsive actuel.

Critere d'acceptation :

- perception plus reguliere des cartes ;
- pas d'overflow ;
- pas de changement d'API shortcode.

## V2 - Systeme de styles

Objectif : proposer des variantes d'affichage reutilisables, sans transformer le plugin en theme.

Ces variantes sont reportees en V2 car elles impliquent une decision produit plus large :

- nommage public ;
- compatibilite shortcode ;
- documentation ;
- tests multi-themes ;
- strategie de surcharge CSS.

### Style `minimal`

Intention :

- rendu tres discret ;
- peu de bordures ;
- integration forte dans le theme actif.

Usage cible :

- pages sobres ;
- listes editoriales ;
- sites avec charte graphique deja forte.

### Style `soft`

Intention :

- cartes legerement plus habillees ;
- fond, bordures et CTA plus presents ;
- rendu pret a l'emploi pour sites vitrines.

Usage cible :

- pages d'accueil ;
- sections de contenus selectionnes ;
- sites sans design system avance.

### Style `compact`

Intention :

- cartes plus denses ;
- moins d'espacement vertical ;
- priorite a la lecture rapide.

Usage cible :

- archives ;
- pages longues ;
- affichages avec beaucoup de contenus.

### Style `image-left`

Intention :

- media a gauche et contenu a droite sur desktop ;
- empilement naturel sur mobile ;
- lecture plus horizontale pour listes.

Usage cible :

- pages ressources ;
- listes d'articles ;
- contenus ou l'image est secondaire mais utile.

## Hors perimetre actuel

Les sujets suivants ne sont pas a traiter dans V1.0.1 :

- nouveau module ;
- nouveau CPT ;
- integration ACF ;
- reglages admin ;
- systeme complet de presets ;
- modification profonde du HTML ;
- changement de l'API shortcode.
