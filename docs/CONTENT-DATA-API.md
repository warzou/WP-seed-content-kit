# Content Data API V1

Statut : candidat canonique validé avant implémentation

Ce document définit le contrat canonique de la Content Data API V1 de WP Seed Content Kit.

Il décrit les données que l'API devra fournir et les responsabilités qu'elle devra respecter. Il devient canonique après validation et commit dans le dépôt. Il ne définit ni fonctions PHP, ni signatures, ni organisation technique définitive.

La V1 normalise uniquement les modules Citation et Témoignage actuellement intégrés à WP Seed Content Kit. Elle ne décide pas de leur propriété métier à long terme, ne crée pas une API inter-plugin et ne transforme pas Content Kit en registre central de l'écosystème WP Seed. Elle ne crée aucune dépendance entre Content Kit et les autres plugins WP Seed.

## 1. Objectif

La Content Data API fournit une représentation métier stable des contenus structurés gérés par WP Seed Content Kit.

Elle sépare le stockage WordPress des consommateurs qui utilisent les données. Les consommateurs ne doivent pas avoir à connaître les clés de métadonnées, les détails des pièces jointes ou le champ WordPress utilisé pour l'ordre éditorial.

La chaîne conceptuelle est :

Stockage WordPress -> normalisation métier -> Content Data API -> consommateurs

La Content Data API décrit les contenus. Elle ne décide ni quels contenus sélectionner, ni comment les présenter.

## 2. Périmètre V1

La V1 couvre uniquement :

- Citation ;
- Témoignage ;
- objet média minimal utilisé par la photo d'un témoignage.

Sont exclus de la V1 :

- Cards ;
- Annuaire ;
- Créations sonores ;
- API générique de collections ;
- Dynamic Data ;
- Block Bindings ;
- providers pour builders ;
- intégrations propres à Gutenberg, Spectra, Divi ou Elementor ;
- HTML et logique de rendu.

Cards reste un rendu d'articles WordPress natifs. Il ne constitue pas un objet métier de la Content Data API.

## 3. Principes

### 3.1 Contrats métier distincts

Citation et Témoignage possèdent chacun leur propre contrat. La V1 ne crée pas un objet générique qui mélangerait leurs champs.

Les deux contrats suivent néanmoins les mêmes conventions de types, d'accès et d'états vides.

### 3.2 Données WordPress et données métier

Les données WordPress natives restent identifiées comme telles : identifiant, titre, identifiant d'URL, statut et permalien.

Les données métier décrivent le contenu éditorial propre à chaque module. Le titre WordPress ne remplace jamais la valeur métier principale.

### 3.3 Aucune présentation

L'API retourne des données. Elle ne retourne pas de balise HTML, n'ajoute pas de ponctuation et ne prépare pas une mise en page.

L'échappement final et la présentation appartiennent au consommateur qui produit le rendu.

### 3.4 Stockage masqué

Les noms métier du contrat ne révèlent pas les détails du stockage. En particulier, `display_order` représente l'ordre éditorial sans exposer le terme WordPress `menu_order`.

### 3.5 États vides prévisibles

Les consommateurs doivent pouvoir distinguer simplement une valeur absente d'un objet invalide. Les états vides sont définis par le présent contrat et ne dépendent pas de la manière dont WordPress stocke les données.

## 4. Contrat Citation V1

### 4.1 Données WordPress

| Champ | Type | État vide ou règle |
| --- | --- | --- |
| `id` | entier | Un objet sans identifiant valide n'est pas retourné comme Citation valide. |
| `title` | chaîne | Chaîne vide autorisée. Le titre reste une donnée WordPress. |
| `slug` | chaîne | Chaîne vide si aucun identifiant d'URL exploitable n'est disponible. |
| `status` | chaîne | Statut WordPress normalisé du contenu. |
| `permalink` | chaîne | Chaîne vide si aucun permalien n'est disponible. Conservé tant que le CPT reste public. |

### 4.2 Données métier

| Champ | Type | État vide ou règle |
| --- | --- | --- |
| `quote` | chaîne | Valeur métier principale. Chaîne vide si absente. |
| `author` | chaîne | Chaîne vide si absent. |
| `era` | chaîne | Époque ou date affichée libre. Chaîne vide si absente. |
| `source` | chaîne | Source ou contexte. Chaîne vide si absent. |
| `featured` | booléen | `false` si la citation n'est pas mise en avant. |
| `display_order` | entier | Ordre éditorial normalisé. |

### 4.3 Règles propres à Citation

- `quote` est la valeur métier principale ;
- `title` ne remplace jamais `quote` ;
- `author`, `era` et `source` sont facultatifs ;
- aucune ponctuation n'est ajoutée aux valeurs ;
- aucun HTML n'est généré ;
- aucune image ne fait partie du contrat Citation V1 ;
- `era` est un texte libre et ne constitue pas une valeur de tri chronologique.

## 5. Contrat Témoignage V1

### 5.1 Données WordPress

| Champ | Type | État vide ou règle |
| --- | --- | --- |
| `id` | entier | Un objet sans identifiant valide n'est pas retourné comme Témoignage valide. |
| `title` | chaîne | Chaîne vide autorisée. Le titre reste une donnée WordPress. |
| `slug` | chaîne | Chaîne vide si aucun identifiant d'URL exploitable n'est disponible. |
| `status` | chaîne | Statut WordPress normalisé du contenu. |
| `permalink` | chaîne | Chaîne vide si aucun permalien n'est disponible. Conservé tant que le CPT reste public. |

### 5.2 Données métier

| Champ | Type | État vide ou règle |
| --- | --- | --- |
| `text` | chaîne | Valeur métier principale. Chaîne vide si absente. |
| `name` | chaîne | Nom ou initiales. Chaîne vide si absent. |
| `context` | chaîne | Contexte éditorial. Chaîne vide si absent. |
| `photo` | objet média ou `null` | `null` si aucune photo n'est associée. |
| `featured` | booléen | `false` si le témoignage n'est pas mis en avant. |
| `display_order` | entier | Ordre éditorial normalisé. |

### 5.3 Règles propres à Témoignage

- `text` est la valeur métier principale ;
- `title` ne remplace jamais `text` ;
- `name` et `context` peuvent être vides ;
- `context` reste dans le contrat stable car il est encore public, documenté et utilisé par `[seed_testimonials]`, même s'il n'est plus exposé dans l'éditeur actuel ;
- `photo` représente la photo métier alimentée par l'image mise en avant WordPress ;
- aucune balise image n'est produite par l'API ;
- `{{photo}}`, `{{photo_url}}` et `{{photo_alt}}` sont des projections de présentation construites en aval à partir de `photo` ;
- aucun HTML n'est généré.

## 6. Contrat média V1

L'objet média V1 est volontairement minimal.

| Champ | Type | État vide ou règle |
| --- | --- | --- |
| `id` | entier | Identifiant WordPress de la pièce jointe. |
| `url` | chaîne | Chaîne vide si l'URL n'est pas disponible. |
| `alt` | chaîne | Chaîne vide si le texte alternatif est absent. |
| `width` | entier | `0` si la largeur n'est pas disponible. |
| `height` | entier | `0` si la hauteur n'est pas disponible. |
| `mime_type` | chaîne | Chaîne vide si le type MIME n'est pas disponible. |

Un média absent est représenté par `null`, et non par un objet média rempli de valeurs vides.

Sont exclus de l'objet média V1 :

- HTML ;
- `srcset` ;
- variantes de tailles ;
- légende ;
- description ;
- CSS ;
- attributs de chargement ;
- données propres à un builder.

## 7. Règles d'accès

### 7.1 Comportement par défaut

La Content Data API retourne par défaut uniquement les contenus publiés.

Cette règle correspond aux consommateurs publics actuels et évite qu'un brouillon soit exposé implicitement.

### 7.2 Contexte d'administration

Un contenu non publié peut être résolu seulement dans un contexte administratif explicitement demandé et autorisé. Ce contexte ne désigne pas nécessairement uniquement une page `wp-admin` : il décrit une intention d'accès non public soumise aux capacités WordPress.

L'accès doit alors respecter les capacités WordPress réelles de l'utilisateur pour le contenu concerné. La présence d'un identifiant valide ne suffit pas à autoriser la lecture d'un brouillon, d'un contenu privé ou d'un contenu en attente.

Le choix technique de l'interface d'autorisation n'est pas défini dans ce document.

### 7.3 Type attendu

La résolution doit vérifier que le contenu appartient bien au type métier demandé. Une Citation ne peut pas être retournée comme Témoignage, et inversement.

## 8. Responsabilités de l'API

La Content Data API doit :

- résoudre un identifiant ;
- vérifier le type de contenu attendu ;
- appliquer les règles d'accès ;
- lire les données WordPress et métier ;
- masquer les clés et détails de stockage ;
- normaliser les types ;
- normaliser l'objet média minimal ;
- fournir des états vides cohérents ;
- préserver les compatibilités historiques explicitement retenues ;
- devenir la source commune des consommateurs de données Citation et Témoignage.

## 9. Hors périmètre de l'API

La Content Data API ne doit pas :

- générer du HTML ;
- ajouter de ponctuation ;
- faire une requête de collection ;
- appliquer des limites, filtres ou tris de collection ;
- choisir les contenus à afficher ;
- sélectionner un template ;
- remplacer des placeholders ;
- connaître un layout Divi ;
- connaître Gutenberg, Spectra, Divi ou Elementor ;
- appeler les mécanismes WordPress de rendu de contenu, de blocs ou de shortcodes ;
- charger du CSS ;
- produire une carte, une grille ou une collection ;
- devenir une API métier générique partagée entre plusieurs plugins.

## 10. Consommateurs

Les consommateurs visés sont, dans l'ordre conceptuel :

1. les rendus fallback Citation et Témoignage ;
2. les fournisseurs de placeholders ;
3. les shortcodes, après leur sélection des identifiants ;
4. un éventuel registre Dynamic Data ;
5. d'éventuels Block Bindings ;
6. d'éventuels providers pour builders.

Les shortcodes restent responsables :

- des requêtes WordPress ;
- du choix des contenus ;
- des limites ;
- des filtres ;
- des tris ;
- du mode collection ;
- du choix du template demandé.

Le moteur de templates reçoit des valeurs préparées par ses fournisseurs de placeholders. Il n'a pas à connaître les clés de stockage métier.

## 11. Compatibilités historiques

### 11.1 Contexte du témoignage

Le contexte du témoignage fait partie du contrat stable V1.

Son stockage historique actuel est `_seed_testimonial_context`. Cette clé est un détail interne et ne doit pas être exposée aux consommateurs. Le contrat public utilise `context`.

### 11.2 Date historique du témoignage

La donnée stockée historiquement sous `_seed_testimonial_date` ne fait pas partie du contrat stable V1.

Elle reste une compatibilité transitoire :

- sa lecture historique ne doit pas être supprimée lors des premiers lots d'implémentation ;
- son éventuelle présence sur avecguillaume.fr doit être vérifiée avant toute suppression ;
- elle ne devient ni un nouveau champ stable, ni un placeholder V1 ;
- aucune migration ou suppression automatique n'est autorisée.

### 11.3 Consentement historique

La donnée historique `_seed_testimonial_consent` est écartée du contrat V1.

Elle n'est plus utilisée par le comportement actuel. Les éventuelles valeurs encore stockées ne doivent toutefois pas être supprimées automatiquement.

### 11.4 Principe de conservation

La normalisation en lecture ne constitue jamais une autorisation de réécrire, migrer ou supprimer les données existantes.

## 12. Stratégie de migration progressive

La migration vers la Content Data API doit être réalisée par petits lots, sans changer les contrats publics existants.

Ordre conceptuel :

1. introduire la normalisation en lecture seule ;
2. valider les contrats Citation, Témoignage et média sur des données réelles ;
3. faire consommer les données normalisées par les rendus fallback ;
4. faire consommer les mêmes données par les fournisseurs de placeholders ;
5. faire utiliser l'API par les shortcodes après leurs requêtes existantes ;
6. seulement ensuite, envisager Dynamic Data, Block Bindings ou providers builders dans des lots séparés.

Chaque étape doit conserver un comportement public identique avant de passer à la suivante.

Les métaboxes et handlers de sauvegarde restent propriétaires de l'édition et du stockage. La Content Data API V1 est une couche de lecture et de normalisation, pas une couche d'écriture.

## 13. Invariants de compatibilité

L'implémentation future doit garantir :

- aucun changement des shortcodes publics ;
- aucun changement des attributs de shortcodes ;
- aucun changement des placeholders existants ;
- maintien de `{{photo}}`, `{{photo_url}}` et `{{photo_alt}}` ;
- maintien du filtre `context` de `[seed_testimonials]` ;
- maintien de `orderby="menu_order"` au niveau du shortcode, même si le contrat métier expose `display_order` ;
- contenus non publiés exclus par défaut ;
- aucun HTML produit par l'API ;
- aucune dépendance à un template ou à un builder ;
- aucune suppression automatique d'ancienne méta ;
- aucune migration globale de données ;
- aucun changement de la visibilité publique actuelle des CPT dans le lot API ;
- le maintien de `permalink` tant que les CPT restent publics ne consacre pas leurs pages individuelles comme fonctionnalité produit ;
- la migration future du fallback Témoignage ne doit pas supprimer la lecture historique de `_seed_testimonial_date` avant vérification des données existantes, notamment sur avecguillaume.fr ;
- Cards inchangé et hors de la Content Data API V1.

## 14. Sujets reportés

Les sujets suivants sont explicitement reportés :

- API de collections ;
- contrat de données pour Cards ;
- Annuaire ;
- Créations sonores ;
- variantes et tailles d'images ;
- contrat stable pour une date de témoignage ;
- évolution de la visibilité publique des CPT ;
- usage produit des pages individuelles Citation et Témoignage ;
- Dynamic Data ;
- Block Bindings ;
- providers builders ;
- exposition inter-plugin ;
- signatures PHP et organisation technique de l'API.

## 15. Règle de lecture

Ce document fixe le contrat métier V1 avant son implémentation technique.

En cas de contradiction future entre une proposition d'implémentation et ce document, le contrat métier décrit ici doit être réexaminé explicitement avant toute modification. Une contrainte technique ne doit pas modifier silencieusement le sens des données.
