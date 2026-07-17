# Collections V1

Statut : contrat et API PHP V1 intégrés sur `main` ; adaptateurs shortcodes et Templates du lot D implémentés localement, non committés

Ce document définit le contrat canonique des Collections V1 de WP Seed Content Kit.

Il fixe le modèle métier enrichi du Témoignage, la sélection ordonnée des Témoignages, la sélection quotidienne d'une Citation et la frontière avec les adaptateurs de présentation. Le lot C fournit l'API PHP de sélection. Le lot D local relie cette API aux shortcodes et aux Templates sans modifier les providers ni les CPT.

En particulier :

- la Content Data API fournit `testimonial.testimonial_date`, indépendamment de l'API Collections ;
- le registre Dynamic Data comprend treize champs, dont la date ISO du témoignage ;
- `[seed_testimonials]` conserve ses valeurs historiques par défaut et traduit ses attributs vers Collections ;
- `[seed_quotes]` conserve son hasard historique et ajoute le mode explicite `mode="daily"` ;
- les deux fonctions PHP Collections sont chargées globalement depuis `includes/core/collections.php`, après l'état des modules ;
- la release publique reste `0.3.0` tant qu'aucune nouvelle release n'est publiée.

## 1. Objectif

Collections V1 sépare quatre responsabilités :

1. WordPress stocke les contenus et leurs métadonnées ;
2. la couche Collections choisit des contenus publiés et retourne leurs identifiants dans un ordre stable ;
3. la Content Data API normalise chaque contenu choisi ;
4. un shortcode, un Template WP Seed ou un constructeur produit la présentation.

La chaîne cible est :

Stockage WordPress -> Collections -> liste ordonnée d'IDs -> Content Data / Dynamic Data -> adaptateur -> rendu

La couche Collections répond uniquement à la question : « Quels contenus faut-il sélectionner et dans quel ordre ? »

Elle ne normalise pas les données métier et ne produit aucune présentation.

## 2. Périmètre V1

Collections V1 couvre :

- le modèle Témoignage enrichi cible ;
- une collection ordonnée de Témoignages ;
- une Citation quotidienne déterministe ;
- les frontières avec les shortcodes et Templates WP Seed ;
- les usages possibles avec Divi, Gutenberg et Spectra ;
- les règles de compatibilité avec les comportements existants.

Sont hors périmètre :

- la fermeture des singles ou archives publics des CPT ;
- une pagination propre à WP Seed ;
- AJAX ;
- un bloc Gutenberg propriétaire ;
- un module Divi propriétaire ;
- une intégration Spectra propriétaire ;
- un provider de collection dans Dynamic Data ;
- une interface de collection propre à un builder ;
- une politique juridique de consentement ;
- une migration ou suppression automatique de métadonnées historiques.

## 3. Règles d'architecture

### 3.1 Sélection et données restent séparées

La couche Collections retourne des identifiants. Elle n'appelle pas les renderers et ne construit pas d'objets de présentation.

La Content Data API reste seule responsable de la normalisation d'une Citation ou d'un Témoignage. Les consommateurs ne doivent pas recevoir une seconde représentation concurrente des mêmes données.

### 3.2 Dynamic Data ne sélectionne rien

Dynamic Data résout un champ pour un contenu déjà identifié. Un provider Divi ou Gutenberg peut recevoir l'ID courant d'une boucle, mais il ne crée, filtre, trie ou complète jamais cette boucle.

### 3.3 Les adaptateurs restent responsables du rendu

Les shortcodes, Templates WP Seed et builders peuvent présenter une même liste d'IDs de manières différentes. Aucun choix de constructeur ne modifie le sens des arguments de collection.

### 3.4 Aucun fallback implicite

Une sélection vide reste vide. La couche Collections ne remplace jamais une sélection manuelle invalide, une collection mise en avant vide ou une absence de Citation par des contenus non demandés.

### 3.5 Les modules désactivés restent inactifs

Si le module concerné n'est pas actif, la collection correspondante retourne son résultat vide contractuel. Elle ne contourne pas la configuration du plugin.

## 4. Modèle Témoignage enrichi cible

Le modèle cible comprend les données suivantes :

| Identifiant métier | Type | Obligatoire | Valeur vide | Stockage actuel ou cible | Libellé utilisateur |
| --- | --- | --- | --- | --- | --- |
| `testimonial.text` | chaîne multiligne | Oui dans l'édition | `''` | `_seed_testimonial_text` | Témoignage |
| `testimonial.name` | chaîne | Non | `''` | `_seed_testimonial_name` | Nom ou initiales |
| `testimonial.photo` | objet média ou `null` | Non | `null` | image mise en avant WordPress | Photo |
| `testimonial.testimonial_date` | chaîne ISO | Non | `''` | `_seed_testimonial_date` | Date du témoignage |
| `testimonial.context` | chaîne | Non | `''` | `_seed_testimonial_context` | Information complémentaire |
| `testimonial.featured` | booléen | Non | `false` | `_seed_featured` | Mis en avant |
| `testimonial.display_order` | entier | Non | `0` | `menu_order` | Position éditoriale |

Le titre WordPress reste une donnée WordPress native servant à l'identification éditoriale. Il ne remplace jamais `testimonial.text` ou `testimonial.name`.

L'obligation de `testimonial.text` est une règle d'interface et de qualité éditoriale. Les couches de lecture doivent continuer à tolérer une chaîne vide pour les données historiques ou incomplètes.

## 5. Date du témoignage

### 5.1 Sens métier

`testimonial.testimonial_date` représente la date à laquelle le témoignage a été donné ou recueilli.

Elle est distincte :

- de `post_date`, qui représente la date d'ajout ou de publication WordPress ;
- de `post_modified` ;
- de toute date libre incluse dans le texte du témoignage.

### 5.2 Contrat canonique cible

| Propriété | Règle |
| --- | --- |
| Identifiant | `testimonial.testimonial_date` |
| Stockage | `_seed_testimonial_date` |
| Type | chaîne |
| Format | ISO `YYYY-MM-DD` |
| Valeur vide | `''` |
| Obligatoire | Non |

Une date inconnue reste vide. La V1 n'invente jamais un jour pour représenter seulement un mois ou une année.

Les dates partielles, le texte libre et une précision configurable sont reportés. Si un besoin réel apparaît, il devra faire l'objet d'un nouveau contrat plutôt que d'étendre silencieusement le format ISO V1.

### 5.3 Normalisation et présentation

La Content Data API retourne la valeur ISO brute. Elle ne retourne pas une date localisée.

Une valeur est valide uniquement si elle respecte exactement le format `YYYY-MM-DD` et représente une date réelle du calendrier grégorien. La validation doit donc contrôler à la fois la forme et la validité du jour, du mois et de l'année. Par exemple, `2026-02-31` est invalide.

Une valeur historique qui ne respecte pas cette validation stricte reste intacte en base, mais elle est normalisée en `''` par la Content Data API. Cette normalisation en lecture n'autorise aucune réécriture.

La date du témoignage est une date civile sans heure ni fuseau. Sa normalisation, sa comparaison et son tri ne lui appliquent aucune conversion de fuseau horaire. Seule sa présentation peut être localisée selon les réglages WordPress, sans changer le jour, le mois ou l'année métier.

La localisation selon les réglages WordPress appartient au renderer, au fournisseur de placeholders ou au provider concerné. Aucun format d'affichage localisé n'est figé par ce document.

La date peut devenir un critère explicite de tri. Elle n'est jamais substituée automatiquement à la date WordPress.

### 5.4 Compatibilité historique

`_seed_testimonial_date` est une clé historique déjà lue par le renderer natif. Sa réutilisation évite une migration de clé.

Le lot Modèle Témoignage applique les règles suivantes :

- les valeurs historiques restent lisibles sans migration globale ;
- une valeur non ISO reste stockée si le champ est absent ou si une nouvelle valeur non vide est invalide ;
- une soumission exactement vide constitue une suppression volontaire, tandis qu'aucune réécriture ou suppression automatique n'est autorisée.

Le lot Modèle Témoignage ajoute ce champ au contrat Content Data API V1 sans modifier le contrat Collections ni créer de fonction de collection.

## 6. Information complémentaire

### 6.1 Identifiant conservé

Le besoin d'information complémentaire est couvert par le champ existant :

- identifiant métier : `testimonial.context` ;
- stockage : `_seed_testimonial_context` ;
- type : chaîne ;
- valeur vide : `''` ;
- obligatoire : non ;
- nouveau libellé utilisateur : **Information complémentaire**.

Exemples :

- En 3e année du parcours ;
- Après 2 ans de suivi ;
- Accompagnement individuel.

### 6.2 Compatibilité

Il n'existe pas de champ `testimonial.information` en V1.

Le contrat technique `testimonial.context` reste inchangé pour :

- la Content Data API ;
- Dynamic Data ;
- Gutenberg Block Bindings ;
- Divi Dynamic Content ;
- le filtre historique `context` du shortcode ;
- les données déjà stockées.

Le changement de libellé ne demande ni alias, ni copie, ni migration de donnée.

## 7. Collection Témoignages

### 7.1 Fonction PHP

La fonction PHP est :

```text
wp_seed_content_get_testimonials($args = array())
```

L'argument canonique est un tableau. La signature reste volontairement non typée afin qu'un appel mal formé soit normalisé sans `TypeError` sous PHP 7.0 ; les valeurs internes restent contrôlées strictement. Le contrat de sortie est :

```text
array<int>
```

Chaque élément est l'ID positif d'un `seed_testimonial` publié, accessible et non protégé par mot de passe : son `post_password` est exactement vide. La liste est ordonnée et ne contient aucun doublon.

### 7.2 Arguments V1

| Argument | Type canonique | Défaut | Valeurs |
| --- | --- | --- | --- |
| `ids` | tableau d'entiers | `array()` | IDs positifs |
| `featured` | chaîne | `all` | `all`, `only`, `exclude` |
| `limit` | entier | `0` | `0` ou entier positif |
| `orderby` | chaîne | `display_order` | `display_order`, `date`, `testimonial_date`, `id` |
| `order` | chaîne | `asc` | `asc`, `desc` |

Il n'existe aucun argument `mode`.

Ces valeurs par défaut servent l'API de collection. Elles diffèrent volontairement du shortcode `[seed_testimonials]`, qui conserve une limite par défaut de trois éléments et un tri par date WordPress descendante. L'adaptateur traduit explicitement ces valeurs historiques vers Collections.

### 7.3 Normalisation des arguments

- `ids` accepte uniquement un tableau dans l'API PHP ;
- une liste CSV appartient uniquement à un adaptateur comme le shortcode ;
- un `featured` inconnu devient `all` ;
- un `limit` absent, nul, négatif ou invalide devient `0` ;
- un `orderby` inconnu devient `display_order` ;
- un `order` inconnu devient `asc` ;
- les noms d'arguments inconnus sont ignorés en V1.

Un tableau `ids` non vide active la sélection manuelle autoritaire, même si tous ses éléments sont ensuite rejetés. Un type non conforme et non vide pour `ids` est une demande manuelle invalide et produit une collection vide. Cette règle évite un fallback inattendu vers tous les Témoignages.

## 8. Priorité des arguments

L'activation du module Témoignages est une précondition universelle. Si le module est désactivé, la fonction retourne `array()` avant toute autre sélection, y compris lorsqu'un tableau `ids` non vide est fourni. Une sélection manuelle ne contourne jamais l'état du module.

Lorsque le module est actif, la priorité V1 est :

1. si `ids` est fourni en sélection manuelle non vide, traiter uniquement ces IDs ;
2. sinon appliquer les règles de publication et de permissions ;
3. appliquer le filtre `featured` ;
4. appliquer le tri stable ;
5. appliquer `limit`.

Dans une sélection manuelle par IDs :

- `featured` est ignoré ;
- `orderby` est ignoré ;
- `order` est ignoré ;
- `limit` reste appliqué après nettoyage.

## 9. Sélection manuelle par IDs

La sélection manuelle suit les règles suivantes :

- seuls les entiers strictement positifs sont candidats dans l'API PHP ;
- l'ordre d'entrée est conservé ;
- la première occurrence d'un ID est conservée ;
- les occurrences suivantes sont ignorées ;
- un contenu inexistant est retiré ;
- un contenu d'un autre CPT est retiré ;
- un brouillon, contenu privé, en attente ou dans la corbeille est retiré ;
- une publication protégée par mot de passe est retirée, y compris lorsque son ID est fourni explicitement ;
- aucun contenu de remplacement n'est recherché ;
- une liste entièrement invalide retourne `array()` ;
- `limit` tronque la liste nettoyée depuis son début ;
- `limit=0` conserve toute la liste nettoyée.

La V1 publique ne fournit aucun argument permettant d'inclure des contenus non publiés. Une éventuelle sélection d'administration devra être documentée séparément avec ses permissions.

## 10. Filtre de mise en avant

`featured` utilise trois valeurs canoniques :

| Valeur | Effet |
| --- | --- |
| `all` | Ne filtre pas la mise en avant. |
| `only` | Conserve uniquement `testimonial.featured=true`. |
| `exclude` | Conserve uniquement `testimonial.featured=false`. |

Le shortcode accepte `all`, `true` et `false` pour compatibilité, ainsi que les valeurs canoniques. Son adaptateur applique les alias suivants, sans distinction de casse :

- `true` vers `only` ;
- `false` vers `exclude`.

La convention canonique de l'API reste `all|only|exclude` afin d'éviter de mélanger chaînes et booléens.

Si plusieurs valeurs `_seed_featured` existent exceptionnellement pour un même contenu, seule la valeur canonique renvoyée par `get_post_meta($post_id, '_seed_featured', true)` est prise en compte. Collections ne fusionne pas ces valeurs et ne crée aucune migration.

Une collection `only` vide retourne `array()`. Elle ne bascule jamais vers les Témoignages récents ou non mis en avant.

## 11. Limite

`limit` est appliqué après filtrage et tri, ou après nettoyage dans le mode `ids`.

- `0` signifie tous les résultats éligibles ;
- un entier positif fixe le nombre maximal d'IDs ;
- une valeur invalide ou négative est normalisée à `0` ;
- une limite supérieure au nombre de résultats retourne simplement tous les résultats éligibles.

Le shortcode conserve sa limite par défaut historique de trois et son plafond de 24 pour toute limite positive. Le lot D attribue désormais un sens explicite à `limit="0"` : tous les Témoignages éligibles. Pour une même liste d'IDs, le markup produit par les renderers reste identique. La sélection peut volontairement différer pour les contenus protégés par mot de passe, les anciennes valeurs non canoniques `_seed_featured=0` et les nouveaux parcours `ids`, `daily` ou `limit="0"`. Les appels historiques ordinaires conservent leurs valeurs par défaut et leur structure HTML.

## 12. Ordre et tris stables

### 12.1 Règle commune

Tout tri doit être déterministe. Lorsque deux contenus ont la même valeur principale, l'ID ascendant sert de départage stable, même lorsque `order=desc`.

### 12.2 `display_order`

`display_order` est la valeur publique normalisée de `menu_order`.

- `asc` place les petites positions en premier ;
- `desc` place les grandes positions en premier ;
- en ordre `asc`, une valeur `0` apparaît avant toutes les valeurs positives ;
- les égalités sont départagées par ID ascendant.

Le terme technique `menu_order` reste un détail de stockage. Sa prise en charge historique dans les shortcodes n'est pas supprimée.

### 12.3 `date`

`date` désigne exclusivement `post_date`, la date de publication WordPress.

- `orderby=date, order=desc` définit les plus récents au sens WordPress ;
- `orderby=date, order=asc` définit les plus anciens au sens WordPress ;
- les égalités sont départagées par ID ascendant.

Le terme `latest` n'est pas un argument V1. Lorsqu'il est utilisé dans une explication produit, il signifie `orderby=date, order=desc`.

### 12.4 `testimonial_date`

`testimonial_date` désigne uniquement la valeur ISO de `testimonial.testimonial_date`.

- les valeurs ISO valides sont triées chronologiquement ;
- `asc` place les dates les plus anciennes en premier ;
- `desc` place les dates les plus récentes en premier ;
- les contenus sans date ou avec une valeur historique invalide sont toujours placés après les dates valides, quel que soit `order` ;
- les dates vides sont départagées entre elles par ID ascendant ;
- les dates identiques sont départagées par ID ascendant ;
- aucune date WordPress n'est utilisée comme fallback.

### 12.5 `id`

`id` trie numériquement les identifiants WordPress :

- `asc` du plus petit au plus grand ;
- `desc` du plus grand au plus petit.

Ce tri technique fournit déjà un ordre total et ne demande pas de départage supplémentaire.

## 13. Résultat vide et erreurs de sélection

La collection retourne `array()` dans les cas suivants :

- module Témoignages désactivé ;
- aucun Témoignage publié ;
- filtre sans résultat ;
- sélection manuelle entièrement invalide ;
- tous les IDs sont inaccessibles ou d'un mauvais type.

La couche Collections ne produit ni HTML, ni message utilisateur, ni `WP_Error` pour ces absences normales. L'adaptateur décide s'il affiche un état vide.

Les erreurs techniques et le contrat exact d'exception ou de diagnostic interne restent à définir pendant l'implémentation. Elles ne doivent pas transformer une absence normale en contenu de remplacement.

## 14. Citation quotidienne

### 14.1 Fonction PHP

La fonction PHP est :

```text
wp_seed_content_get_daily_quote($args = array())
```

L'argument est réservé à une évolution documentée et toute valeur fournie est ignorée en V1. La signature défensive accepte un appel mal formé sans changer le type de retour.

Elle retourne :

- un ID positif de `seed_quote` publié et non protégé par mot de passe ;
- `0` lorsque le module Citations est désactivé ou qu'aucune Citation n'est éligible.

Aucun filtre supplémentaire n'est contractuel dans les arguments V1 ; le tableau réservé ne constitue pas un mécanisme d'extension implicite.

### 14.2 Algorithme déterministe

La sélection V1 suit cette formule canonique :

1. récupérer uniquement les IDs des Citations publiées, accessibles et dont `post_password` est exactement vide ;
2. retirer tout ID invalide ou d'un autre CPT ;
3. trier les IDs par ordre numérique ascendant ;
4. si la liste est vide, retourner `0` ;
5. obtenir la date civile courante au format `YYYY-MM-DD` selon le fuseau horaire configuré dans WordPress ;
6. construire la graine UTF-8 en concaténant exactement `home_url('/')`, le caractère `|` et cette date ;
7. calculer le SHA-256 hexadécimal de cette graine ;
8. convertir les sept premiers caractères hexadécimaux du résultat en un entier non signé de 28 bits ;
9. calculer `index = entier % nombre_de_candidats` ;
10. retourner l'ID situé à cet index dans la liste triée.

Avec une liste de candidats inchangée, le même site retourne le même ID pendant toute la même date civile WordPress. L'ajout, la suppression ou le changement de statut d'une Citation peut modifier le résultat du jour, car la liste éligible a changé.

La sélection n'utilise pas :

- `ORDER BY RAND()` ;
- une option WordPress mutable ;
- un cron ;
- un compteur de rotation ;
- un état de session ou un cookie visiteur.

SHA-256, la longueur de sept caractères hexadécimaux et le calcul modulo sont contractuels. La valeur intermédiaire maximale tient dans 28 bits ; la méthode produit donc le même index sur les plateformes PHP 32 et 64 bits. Le harnais du lot C couvre cette formule exacte par des tests.

### 14.3 Différence avec le shortcode historique

`[seed_quotes]` et `[seed_quotes orderby="random"]` continuent d'utiliser le chemin historique avec `ORDER BY RAND()`.

La Citation quotidienne utilise l'extension explicite suivante :

```text
[seed_quotes mode="daily"]
```

Elle appelle `wp_seed_content_get_daily_quote()`, rend une seule Citation avec Content Data et n'exécute jamais `ORDER BY RAND()`. Dans ce mode, `limit`, `featured`, `orderby` et `order` sont ignorés ; `template` reste pris en charge. Une valeur `mode` inconnue revient au parcours historique.

## 15. Cache et sens du mot quotidien

La couche Collections ne crée aucun cache obligatoire en V1.

Un cache de page WordPress, Divi, serveur ou CDN peut conserver le HTML généré au-delà de minuit. Dans ce cas, la Citation visible change au prochain rafraîchissement ou à la prochaine invalidation de ce cache, et non nécessairement à 00:00 précise.

Le contrat garantit :

- une sélection déterministe pour une date WordPress donnée et une liste éligible inchangée ;
- aucun changement aléatoire entre deux calculs identiques ;
- aucun mécanisme interne de purge d'un cache tiers.

L'implémentation récupère les posts publiés non protégés en une requête, amorce le cache des métadonnées pour les tris et filtres concernés, puis classe les résultats en PHP. Aucun cache applicatif, transient ou état persistant n'est ajouté. Cette stratégie est adaptée au faible volume actuellement audité ; une optimisation ne sera réévaluée qu'en présence de plusieurs centaines de contenus ou d'une mesure de performance défavorable.

Collections utilise `suppress_filters=true` afin de garantir une sélection canonique et stable. Certains filtres de requête tiers, notamment multilingues ou éditoriaux, qui pouvaient intervenir dans l'ancien `WP_Query` du shortcode Témoignages ne sont donc plus appliqués dans ce parcours. Collections V1 ne garantit pas la compatibilité avec ces filtres tiers. Une stratégie d'extension contrôlée pourra être étudiée ultérieurement, sans rendre ces filtres implicites dans le contrat V1.

Il ne garantit pas :

- une Citation différente tous les jours ;
- une rotation sans répétition ;
- une valeur différente par visiteur ou par page ;
- l'expiration des caches externes à minuit.

## 16. Relations avec Content Data et Dynamic Data

### 16.1 Content Data API

Une fois un ID sélectionné, le consommateur appelle la fonction Content Data correspondant au CPT. La couche Collections ne duplique ni `text`, ni `name`, ni `photo`, ni les autres champs normalisés.

Le lot Modèle Témoignage enrichit le contrat Content Data avec `testimonial_date`. Cette évolution reste distincte de l'API Collections.

### 16.2 Dynamic Data

Dynamic Data continue de résoudre un champ pour un ID courant ou explicite. Il ne reçoit aucun argument de collection.

Le lot Modèle Témoignage ajoute `testimonial.testimonial_date` au registre Dynamic Data comme chaîne ISO canonique brute. Cette décision ne place aucune logique de collection dans Dynamic Data.

Les providers Divi et Gutenberg ne doivent jamais lire `_seed_testimonial_date`, `_seed_featured` ou `menu_order` pour construire une collection.

## 17. Templates WP Seed

Un Template WP Seed définit la présentation d'un élément. Il ne sélectionne pas les éléments de la collection.

Le flux cible est :

1. le shortcode ou un autre adaptateur obtient une liste d'IDs ;
2. la Content Data API fournit les données de chaque ID ;
3. le fournisseur de placeholders prépare les valeurs ;
4. le Template WP Seed rend chaque élément ;
5. le shortcode conserve le wrapper de collection et l'état vide.

Le présent contrat ne crée pas de collection-template, de placeholder `{{items}}` ou de nouveau moteur de rendu.

Le placeholder `{{date}}` est une projection de présentation localisée. Il ne modifie pas la valeur ISO de la Content Data API.

## 18. Adaptateurs shortcodes

### 18.1 Témoignages

Les usages publics du lot D sont :

```text
[seed_testimonials]
[seed_testimonials limit="0" orderby="display_order" order="asc"]
[seed_testimonials featured="only" limit="3" orderby="date" order="desc"]
[seed_testimonials ids="12,18,27"]
[seed_testimonials orderby="testimonial_date" order="desc"]
```

L'adaptateur :

- convertit la liste CSV `ids` en entiers positifs, préserve l'ordre et supprime les doublons ;
- considère `ids` absent ou `ids=""` comme le mode normal ;
- ignore les jetons invalides d'une liste mixte ;
- transmet une demande manuelle vide à Collections lorsqu'une liste non vide ne contient aucun ID valide, afin d'interdire tout fallback ;
- conserve `columns`, `context` et `template` ;
- conserve `featured=true|false` comme alias de `only|exclude` ;
- conserve `orderby=menu_order` comme alias de `display_order` ;
- conserve la limite par défaut de trois, le plafond positif de 24 et l'ordre historique `date DESC` ;
- interprète explicitement `limit="0"` comme tous les résultats éligibles.

Une sélection manuelle `ids` est autoritaire : `featured`, `orderby`, `order` et le filtre historique `context` sont ignorés ; `limit` reste appliqué après nettoyage. Sans `ids`, `context` est conservé comme filtre de compatibilité après la sélection Collections et avant la limite, au moyen de Content Data. Comme dans le shortcode historique, une valeur absente, vide ou égale à la chaîne `"0"` n'active aucun filtre ; toute autre chaîne non vide active le filtre exact.

### 18.2 Citation quotidienne

La syntaxe publique retenue est :

```text
[seed_quotes mode="daily"]
[seed_quotes mode="daily" template="citation-du-jour"]
```

Aucun nouveau shortcode n'est créé. Le mode quotidien ignore les arguments de sélection historiques et accepte toujours `template`. Le mode historique aléatoire reste le défaut.

## 19. Divi

### 19.1 Cas simples

Le Loop Builder Divi 5 peut parcourir un CPT et fournir l'ID de chaque élément aux sources Dynamic Content WP Seed. Il peut convenir à une page listant les Témoignages ou à une sélection configurée entièrement dans Divi.

Dans ce parcours :

- Divi possède la requête et la boucle ;
- WP Seed Dynamic Content fournit uniquement les champs du Témoignage courant ;
- les règles exactes de l'API Collections ne sont garanties que si Divi les reproduit explicitement.

### 19.2 Règles WP Seed garanties

Pour garantir exactement les priorités `ids`, `featured`, `limit`, `orderby` et `order`, le parcours recommandé reste :

- shortcode de collection ;
- Template WP Seed ;
- contenu du template ou Layout Divi Library pour la présentation.

La Citation quotidienne doit suivre le même principe tant qu'aucun adaptateur de requête Divi distinct n'est documenté.

Il n'est prévu ni module Divi propriétaire, ni logique de collection dans les classes Dynamic Content.

## 20. Gutenberg

Le Query Loop Core peut gérer un CPT, une limite, des tris WordPress simples et sa propre pagination.

Le provider serveur Block Bindings WP Seed peut résoudre les champs texte autorisés lorsque le Query Loop fournit `postId` et `postType`. Il ne configure pas la requête.

Les cas simples peuvent utiliser :

- Query Loop Core ;
- blocs Core liés aux champs WP Seed par un markup contrôlé.

Les cas `ids`, `featured`, `display_order` et `testimonial_date` ne disposent pas actuellement d'une interface WP Seed de requête. Le shortcode et les Templates WP Seed restent l'adaptateur garanti.

Aucun bloc propriétaire et aucun provider de collection ne sont inclus dans Collections V1.

## 21. Spectra

Les parcours officiellement retenus sans intégration spécifique sont :

- shortcode dans un bloc compatible ;
- Template WP Seed composé avec Gutenberg ou Spectra ;
- blocs Core et Query Loop Core lorsque leur comportement suffit.

Les blocs de boucle Spectra peuvent proposer des CPT, des limites, des tris et des champs dynamiques selon la version et l'offre installée. Cette capacité ne constitue pas un contrat WP Seed pour les raisons suivantes :

- les options varient entre Post Grid, Post Carousel, Post Block et Loop Builder ;
- les priorités exactes de Collections V1 ne sont pas garanties ;
- les blocs Spectra propriétaires ne consomment pas automatiquement la source Block Bindings WP Seed ;
- la lecture directe des métadonnées WP Seed contournerait Content Data et n'est pas une intégration officielle.

Une compatibilité Spectra plus complète exige une recette bloc par bloc ou un adaptateur spécifique documenté dans un lot ultérieur.

## 22. Visibilité des CPT

Collections V1 ne modifie pas :

- `public` ;
- `publicly_queryable` ;
- `exclude_from_search` ;
- `show_in_rest` ;
- `has_archive` ;
- les règles de réécriture de `seed_testimonial` ou `seed_quote`.

Les singles et archives restent techniquement accessibles. Ils ne deviennent pas pour autant le parcours produit recommandé : les pages WordPress utilisant une collection constituent le parcours principal.

Une fermeture future des URLs devra être traitée dans un chantier séparé comprenant :

- inventaire des liens et usages existants ;
- audit SEO et sitemap ;
- stratégie de redirection ;
- compatibilité Divi Loop Builder ;
- compatibilité Gutenberg Query Loop ;
- compatibilité Spectra ;
- REST ;
- previews et administration.

## 23. Pagination

L'API Collections V1 ne fournit ni page, ni offset, ni total, ni curseur.

- `limit=0` peut retourner tous les IDs éligibles ;
- les builders restent libres de paginer leurs propres boucles natives ;
- le shortcode conserve une limite simple ;
- aucune pagination AJAX WP Seed n'est introduite.

Une pagination canonique sera documentée seulement si le volume réel ou un parcours public la rend nécessaire.

## 24. Consentement, identification et vie privée

Les Témoignages peuvent contenir des données personnelles : nom, photo, date, information de parcours et texte potentiellement identifiable.

Le présent contrat ne définit pas une base juridique, un workflow de consentement ou une durée de conservation.

Garde-fous :

- `testimonial.name` reste facultatif afin de permettre l'anonymat ou les initiales ;
- `testimonial.photo` reste facultative ;
- `_seed_testimonial_consent` n'est pas réintroduit implicitement ;
- aucune ancienne valeur de consentement n'est supprimée ;
- une décision produit et juridique est requise avant toute évolution augmentant l'exposition publique.

Cette décision ne bloque pas le contrat technique Collections, mais elle doit précéder une modification de la visibilité publique ou de l'interface de publication.

## 25. Lots futurs séparés

### Lot A - Contrat Collections

- valider et committer le présent document ;
- ne modifier aucun comportement runtime.

### Lot B - Modèle Témoignage intégré

- réactiver la date ISO facultative ;
- réintroduire l'édition de `context` sous le libellé Information complémentaire ;
- enrichir la Content Data API ;
- exposer la date ISO dans Dynamic Data et une date localisée dans `{{date}}` ;
- valider les données historiques ;
- ne pas introduire l'API Collections dans le même diff.

### Lot C - API Collections PHP intégrée

- sélection Témoignages dans `plugin/includes/core/collections.php` ;
- Citation quotidienne déterministe dans le même fichier ;
- chargement global après `core/modules.php` ;
- harnais direct reproductible dans `tests/collections-harness.php`.

### Lot D - Adaptation locale des shortcodes et Templates

- `[seed_testimonials]` utilise Collections pour la sélection ;
- `[seed_quotes mode="daily"]` utilise la Citation quotidienne ;
- le hasard historique de `[seed_quotes]` reste distinct ;
- les Templates natifs et Divi Library restent les renderers d'éléments ;
- le harnais `tests/collections-adapters-harness.php` couvre les contrats publics ;
- aucun provider builder, CPT ou numéro de version n'est modifié.

### Lot E - Recettes builders

- Divi Loop Builder et Dynamic Content ;
- Gutenberg Query Loop et Block Bindings ;
- Spectra bloc par bloc ;
- comparaison des résultats avec le shortcode canonique.

## 26. Matrice de tests V1

Le harnais du lot C couvre la sélection, les normalisations, les tris, les gardes de modules, les fuseaux et les types de retour. Le harnais du lot D couvre les adaptateurs, le rendu natif, les Templates, le Layout Divi Library et le rendu serveur d'un bloc Shortcode.

### 26.1 Modèle Témoignage

| Cas | Résultat attendu |
| --- | --- |
| Date ISO valide | Valeur brute conservée, affichage localisé en aval. |
| Date impossible `2026-02-31` | Rejetée par la validation calendaire stricte ; valeur API `''` sans réécriture automatique. |
| Date absente | `''`, aucun fallback vers `post_date`. |
| Date invalide historique laissée intacte | Stockage conservé sans réécriture ; valeur API `''` ; placée après les dates valides. |
| Date invalide non vide soumise | Nouvelle valeur rejetée ; ancienne méta conservée. |
| Champ soumis exactement vide | Ancienne méta valide ou invalide supprimée volontairement. |
| Date partielle | Rejetée par l'édition actuelle. |
| Information présente | `testimonial.context` restitue la chaîne. |
| Information vide | `''`. |
| Donnée historique context | Lecture maintenue sans migration. |
| Photo absente | `null`. |
| Nom absent | Chaîne vide, anonymat possible. |

### 26.2 Collection Témoignages

| Cas | Résultat attendu |
| --- | --- |
| Valeurs par défaut | Tous les publiés non protégés, `display_order ASC`, ID ASC. |
| `display_order=0` avec positions positives | Les valeurs `0` apparaissent en premier en ordre `asc`, puis ID ASC. |
| `featured=only` | Uniquement les mis en avant. |
| `featured=exclude` | Uniquement les non mis en avant. |
| Méta historique `_seed_featured=0` | Considérée comme non mise en avant ; incluse par `exclude`, exclue par `only`. |
| Plusieurs valeurs `_seed_featured` | Seule la valeur renvoyée avec `single=true` est évaluée ; aucune fusion ou migration. |
| Aucun featured | Tableau vide, aucun fallback. |
| `ids` ordonnés | Ordre fourni conservé. |
| CSV `ids` vide dans un adaptateur shortcode | Normalisé en `ids=array()` ; le mode manuel n'est pas activé. |
| IDs dupliqués | Première occurrence seulement. |
| ID inexistant | Ignoré. |
| Mauvais CPT | Ignoré. |
| Brouillon ou privé | Ignoré. |
| Publication protégée, y compris dans `ids` | Ignorée ; aucun fallback. |
| Tous IDs invalides | Tableau vide. |
| Module désactivé avec `ids` non vide | Tableau vide ; `ids` ne contourne pas l'activation. |
| `limit=0` | Tous les résultats éligibles. |
| `limit>0` | Liste tronquée après tri ou nettoyage. |
| `display_order` dupliqué | ID ASC comme départage. |
| `date` identique | ID ASC comme départage. |
| `testimonial_date` vide | Après toutes les dates valides. |
| Module désactivé | Tableau vide. |
| Aucun contenu | Tableau vide. |

### 26.3 Citation quotidienne

| Cas | Résultat attendu |
| --- | --- |
| Plusieurs Citations | Un ID publié non protégé déterministe. |
| Même jour et même liste | Même ID. |
| Même entrée sur PHP 32 et 64 bits | Même index et même ID. |
| Jour suivant | Nouvelle sélection possible, non garantie différente. |
| Une Citation | Même ID chaque jour. |
| Aucune Citation | `0`. |
| Brouillon | Jamais sélectionné. |
| Uniquement des Citations protégées | `0`. |
| Module désactivé | `0`. |
| Fuseau WordPress différent | Frontière de jour fondée sur ce fuseau. |
| Cache au-delà de minuit | Ancien HTML possible jusqu'à expiration du cache. |
| Liste modifiée pendant le jour | Résultat autorisé à changer. |

### 26.4 Adaptateurs et non-régression

Vérifier :

- shortcode Témoignages sans nouvel attribut ;
- shortcode Témoignages avec `featured`, `context`, `menu_order`, `columns` et `template` historiques ;
- shortcode Citations historique aléatoire ;
- Template Témoignage natif ;
- Template Citation natif ;
- Layout Divi Library ;
- Divi Dynamic Content dans une boucle ;
- Gutenberg Query Loop et Block Bindings ;
- Spectra via shortcode et blocs Core ;
- résultat vide de chaque adaptateur ;
- modules désactivés ;
- aucune exposition de brouillon ;
- aucune lecture directe des métadonnées dans un provider ;
- aucun changement des CPT ;
- aucun fatal, warning ou notice WP Seed.

## 27. Risques et garde-fous

### 27.1 Defaults API et shortcode différents

Risque : changer silencieusement le nombre ou l'ordre des Témoignages existants.

Garde-fou : l'adaptateur shortcode traduit explicitement ses valeurs historiques vers l'API Collections.

### 27.2 Ambiguïté des dates

Risque : confondre date WordPress et date du témoignage.

Garde-fou : identifiants `date` et `testimonial_date` distincts, aucune substitution automatique.

### 27.3 Dates historiques invalides

Risque : réécrire ou trier des valeurs inconnues comme des dates valides.

Garde-fou : inventaire avant implémentation, conservation des valeurs, valeurs invalides placées après les dates valides.

### 27.4 Fallback implicite

Risque : afficher un Témoignage non demandé lorsqu'une sélection IDs ou featured est vide.

Garde-fou : résultat vide contractuel.

### 27.5 Couplage aux builders

Risque : laisser Divi, Gutenberg ou Spectra définir la sémantique des collections.

Garde-fou : API d'IDs indépendante et adaptateurs séparés.

### 27.6 Hasard et caches

Risque : promettre une rotation par visite impossible avec un cache de page.

Garde-fou : sélection quotidienne déterministe et limites de cache documentées.

### 27.7 Visibilité des CPT

Risque : casser URLs, REST, sitemaps et boucles builders dans le lot Collections.

Garde-fou : aucun changement de visibilité dans la V1.

### 27.8 Données personnelles

Risque : augmenter l'exposition de noms, photos ou informations de parcours sans politique de consentement.

Garde-fou : champs facultatifs, chantier juridique séparé et aucune exposition publique supplémentaire dans Collections V1.

### 27.9 Effet framework

Risque : transformer une sélection de deux objets métier en moteur générique.

Garde-fou : deux fonctions métier, pas de repository, pas de registre de collections inter-plugin.

## 28. Invariants de compatibilité

L'implémentation future devra garantir :

- aucun HTML produit par la couche Collections ;
- sortie Témoignages limitée à une liste ordonnée d'IDs ;
- sortie Citation quotidienne limitée à un ID ou `0` ;
- Content Data responsable de la normalisation ;
- Dynamic Data sans requête de collection ;
- aucune lecture directe des métadonnées par les providers ;
- shortcodes historiques inchangés avant leur lot d'adaptation ;
- comportement aléatoire historique de `[seed_quotes]` inchangé ;
- Templates et placeholders existants inchangés avant le lot concerné ;
- aucune migration automatique de méta ;
- aucun changement des CPT dans Collections V1 ;
- aucun contenu non publié exposé ;
- aucun fallback vers un contenu arbitraire ;
- aucune dépendance obligatoire à un builder ;
- Cards hors du contrat Collections V1.

## 29. Documentation par lot

Le lot B intégré sur `main` met déjà en cohérence :

- [docs/CONTENT-DATA-API.md](CONTENT-DATA-API.md) pour `testimonial_date` ;
- [docs/DYNAMIC-DATA.md](DYNAMIC-DATA.md) pour le champ `testimonial.testimonial_date` ;
- [plugin/docs/USAGE.md](../plugin/docs/USAGE.md) pour les champs effectivement implémentés ;
- [plugin/docs/TESTING.md](../plugin/docs/TESTING.md) pour la recette du code courant ;
- [PROJECT-SNAPSHOT.md](../PROJECT-SNAPSHOT.md) pour distinguer le lot local de la release publique.

Les futurs lots Collections devront mettre à jour uniquement lorsque leur comportement existe :

- la documentation des nouveaux attributs de shortcode effectivement livrés ;
- le snapshot après chaque jalon validé ;
- le changelog de la future release.

La documentation utilisateur ne doit pas annoncer une syntaxe shortcode ou une intégration builder avant son implémentation et sa validation runtime.

Le lot D local met en cohérence les shortcodes, Templates et parcours builders indirects. Il devra être revu, validé en runtime et committé séparément avant toute annonce dans une release publique.

## 30. Règle de lecture

Ce document fixe le contrat Collections V1. Le lot C fournit l'API PHP de sélection et le lot D local fournit ses adaptateurs shortcode et Template. Les providers Dynamic Data restent unitaires et ne sélectionnent aucune collection.

La documentation distingue toujours le code local non publié de la release publique `0.3.0`.

En cas de contradiction entre une proposition technique et les priorités, états vides ou invariants de ce document, le contrat doit être réexaminé explicitement avant tout changement de code.
