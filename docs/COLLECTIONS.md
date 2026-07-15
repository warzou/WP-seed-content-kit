# Collections V1

Statut : contrat de conception candidat avant implémentation

Ce document définit le contrat canonique cible des Collections V1 de WP Seed Content Kit.

Il fixe le modèle métier enrichi du Témoignage, la sélection ordonnée des Témoignages, la sélection quotidienne d'une Citation et la frontière avec les adaptateurs de présentation. Il ne constitue pas une documentation de fonctionnalités déjà toutes implémentées.

En particulier :

- la Content Data API actuelle ne fournit pas encore `testimonial.testimonial_date` ;
- le registre Dynamic Data actuel reste limité à ses douze champs ;
- les shortcodes publics conservent leur syntaxe et leurs valeurs historiques tant qu'un lot d'adaptation distinct n'est pas validé ;
- aucune fonction PHP de collection n'existe au moment de la rédaction de ce contrat.

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

La future Content Data API enrichie devra retourner la valeur ISO brute. Elle ne retourne pas une date localisée.

Une valeur est valide uniquement si elle respecte exactement le format `YYYY-MM-DD` et représente une date réelle du calendrier grégorien. La validation doit donc contrôler à la fois la forme et la validité du jour, du mois et de l'année. Par exemple, `2026-02-31` est invalide.

Une valeur historique qui ne respecte pas cette validation stricte reste intacte en base, mais elle est normalisée en `''` par la Content Data API. Cette normalisation en lecture n'autorise aucune réécriture.

La date du témoignage est une date civile sans heure ni fuseau. Sa normalisation, sa comparaison et son tri ne lui appliquent aucune conversion de fuseau horaire. Seule sa présentation peut être localisée selon les réglages WordPress, sans changer le jour, le mois ou l'année métier.

La localisation selon les réglages WordPress appartient au renderer, au fournisseur de placeholders ou au provider concerné. Aucun format d'affichage localisé n'est figé par ce document.

La date peut devenir un critère explicite de tri. Elle n'est jamais substituée automatiquement à la date WordPress.

### 5.4 Compatibilité historique

`_seed_testimonial_date` est une clé historique déjà lue par le renderer natif. Sa réutilisation évite une migration de clé.

Avant implémentation :

- les valeurs historiques doivent être inventoriées sur les sites connus, notamment `avecguillaume.fr` ;
- les valeurs non ISO doivent être signalées et conservées ;
- aucune réécriture ou suppression automatique n'est autorisée.

Le contrat Content Data API V1 actuel reste exact tant que le lot Modèle Témoignage n'a pas ajouté ce champ. Le présent document définit la cible suivante, pas un changement déjà livré.

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

### 7.1 Fonction conceptuelle

La fonction métier recommandée est conceptuellement :

```text
wp_seed_content_get_testimonials( array $args = array() )
```

La signature PHP définitive sera vérifiée pendant le lot d'implémentation. Le contrat de sortie est :

```text
array<int>
```

Chaque élément est l'ID positif d'un `seed_testimonial` publié et accessible. La liste est ordonnée et ne contient aucun doublon.

### 7.2 Arguments V1

| Argument | Type canonique | Défaut | Valeurs |
| --- | --- | --- | --- |
| `ids` | tableau d'entiers | `array()` | IDs positifs |
| `featured` | chaîne | `all` | `all`, `only`, `exclude` |
| `limit` | entier | `0` | `0` ou entier positif |
| `orderby` | chaîne | `display_order` | `display_order`, `date`, `testimonial_date`, `id` |
| `order` | chaîne | `asc` | `asc`, `desc` |

Il n'existe aucun argument `mode`.

Ces valeurs par défaut servent la nouvelle API de collection. Elles diffèrent volontairement du shortcode historique `[seed_testimonials]`, qui limite actuellement à trois éléments et trie par date WordPress descendante. Le shortcode conserve son comportement tant que son lot d'adaptation n'est pas validé.

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

Le shortcode historique utilise actuellement `all`, `true` et `false`. Un futur adaptateur pourra conserver ces valeurs comme alias :

- `true` vers `only` ;
- `false` vers `exclude`.

La convention canonique de l'API reste `all|only|exclude` afin d'éviter de mélanger chaînes et booléens.

Une collection `only` vide retourne `array()`. Elle ne bascule jamais vers les Témoignages récents ou non mis en avant.

## 11. Limite

`limit` est appliqué après filtrage et tri, ou après nettoyage dans le mode `ids`.

- `0` signifie tous les résultats éligibles ;
- un entier positif fixe le nombre maximal d'IDs ;
- une valeur invalide ou négative est normalisée à `0` ;
- une limite supérieure au nombre de résultats retourne simplement tous les résultats éligibles.

La valeur `0` de cette nouvelle API ne modifie pas la limite historique du shortcode Témoignages.

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

### 14.1 Fonction conceptuelle

La fonction métier recommandée est conceptuellement :

```text
wp_seed_content_get_daily_quote( array $args = array() )
```

Elle retourne :

- un ID positif de `seed_quote` publié ;
- `0` lorsque le module Citations est désactivé ou qu'aucune Citation n'est éligible.

La signature reste conceptuelle. Aucun filtre supplémentaire n'est contractuel dans les arguments V1 ; le tableau est réservé à une évolution documentée et ne constitue pas un mécanisme d'extension implicite.

### 14.2 Algorithme déterministe

La sélection V1 suit cette formule canonique :

1. récupérer uniquement les IDs des Citations publiées et accessibles ;
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

SHA-256, la longueur de sept caractères hexadécimaux et le calcul modulo sont contractuels. La valeur intermédiaire maximale tient dans 28 bits ; la méthode produit donc le même index sur les plateformes PHP 32 et 64 bits. Le lot PHP devra couvrir cette formule exacte par des tests.

### 14.3 Différence avec le shortcode historique

`[seed_quotes]` sélectionne actuellement une Citation avec `ORDER BY RAND()` lorsqu'aucune limite ni ordre explicite n'est fourni.

Ce comportement reste inchangé. La Citation quotidienne constitue une nouvelle sélection métier et ne remplace pas silencieusement le hasard historique du shortcode.

## 15. Cache et sens du mot quotidien

La couche Collections ne crée aucun cache obligatoire en V1.

Un cache de page WordPress, Divi, serveur ou CDN peut conserver le HTML généré au-delà de minuit. Dans ce cas, la Citation visible change au prochain rafraîchissement ou à la prochaine invalidation de ce cache, et non nécessairement à 00:00 précise.

Le contrat garantit :

- une sélection déterministe pour une date WordPress donnée et une liste éligible inchangée ;
- aucun changement aléatoire entre deux calculs identiques ;
- aucun mécanisme interne de purge d'un cache tiers.

Il ne garantit pas :

- une Citation différente tous les jours ;
- une rotation sans répétition ;
- une valeur différente par visiteur ou par page ;
- l'expiration des caches externes à minuit.

## 16. Relations avec Content Data et Dynamic Data

### 16.1 Content Data API

Une fois un ID sélectionné, le consommateur appelle la fonction Content Data correspondant au CPT. La couche Collections ne duplique ni `text`, ni `name`, ni `photo`, ni les autres champs normalisés.

Le lot Modèle Témoignage devra enrichir le contrat Content Data avec `testimonial_date` avant qu'un consommateur puisse l'utiliser comme donnée normalisée. Cette évolution reste distincte de l'API Collections.

### 16.2 Dynamic Data

Dynamic Data continue de résoudre un champ pour un ID courant ou explicite. Il ne reçoit aucun argument de collection.

Le champ `testimonial.testimonial_date` n'est pas ajouté automatiquement au registre Dynamic Data par le présent contrat. Son type, sa valeur brute et sa projection par builder devront être arbitrés dans le lot Modèle Témoignage.

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

Le futur placeholder de date, s'il est validé dans le lot Modèle Témoignage, sera une projection de présentation localisée. Il ne modifiera pas la valeur ISO de la Content Data API.

## 18. Shortcodes futurs

### 18.1 Témoignages

Les extensions envisagées sont :

```text
[seed_testimonials featured="only" limit="3"]
[seed_testimonials ids="12,18,27"]
[seed_testimonials orderby="testimonial_date" order="desc"]
```

Ces exemples ne sont pas encore des garanties runtime.

Le futur adaptateur devra :

- convertir une liste CSV `ids` en liste ordonnée d'entiers ;
- appeler l'API de collection ;
- conserver `columns`, `context` et `template`, qui appartiennent au contrat historique du shortcode ;
- conserver `featured=true|false` comme alias historique de `only|exclude` ;
- conserver `orderby=menu_order` comme alias historique de `display_order` ;
- conserver la limite historique par défaut de trois éléments ;
- conserver l'ordre historique par défaut `date DESC` ;
- ne pas interpréter `limit=0` comme tous les résultats tant qu'une décision explicite de compatibilité n'a pas été prise pour ce shortcode.

La nouvelle API et le shortcode peuvent donc avoir des valeurs par défaut différentes. L'adaptateur traduit explicitement le contrat historique vers le contrat Collections.

### 18.2 Citation quotidienne

La syntaxe publique finale n'est pas figée.

Une future extension explicite de `[seed_quotes]` est préférée à un nouveau shortcode tant qu'elle reste claire et rétrocompatible. Le nom de l'attribut, sa valeur et son interaction avec `limit`, `featured` et `orderby` devront être documentés avant implémentation.

Le présent document ne garantit ni `[seed_random_quote]`, ni `[seed_quotes daily="true"]`, ni une autre syntaxe particulière.

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

### Lot B - Modèle Témoignage

- réactiver la date ISO facultative ;
- réintroduire l'édition de `context` sous le libellé Information complémentaire ;
- enrichir la Content Data API ;
- décider séparément de Dynamic Data et des placeholders ;
- valider les données historiques ;
- ne pas introduire l'API Collections dans le même diff.

### Lot C - API Collections PHP

- implémenter la sélection Témoignages ;
- implémenter la Citation quotidienne ;
- charger la couche sans modifier les consommateurs ;
- ajouter les tests directs.

### Lot D - Adaptation des shortcodes et Templates

- migrer les requêtes vers Collections sans régression ;
- ajouter uniquement les nouveaux attributs documentés ;
- conserver les valeurs historiques par défaut ;
- valider les Templates natifs et Divi Library.

### Lot E - Recettes builders

- Divi Loop Builder et Dynamic Content ;
- Gutenberg Query Loop et Block Bindings ;
- Spectra bloc par bloc ;
- comparaison des résultats avec le shortcode canonique.

## 26. Matrice de tests future

### 26.1 Modèle Témoignage

| Cas | Résultat attendu |
| --- | --- |
| Date ISO valide | Valeur brute conservée, affichage localisé en aval. |
| Date impossible `2026-02-31` | Rejetée par la validation calendaire stricte ; valeur API `''` sans réécriture automatique. |
| Date absente | `''`, aucun fallback vers `post_date`. |
| Date invalide historique | Stockage conservé sans réécriture ; valeur API `''` ; placée après les dates valides. |
| Date partielle | Rejetée par la future édition V1. |
| Information présente | `testimonial.context` restitue la chaîne. |
| Information vide | `''`. |
| Donnée historique context | Lecture maintenue sans migration. |
| Photo absente | `null`. |
| Nom absent | Chaîne vide, anonymat possible. |

### 26.2 Collection Témoignages

| Cas | Résultat attendu |
| --- | --- |
| Valeurs par défaut | Tous les publiés, `display_order ASC`, ID ASC. |
| `display_order=0` avec positions positives | Les valeurs `0` apparaissent en premier en ordre `asc`, puis ID ASC. |
| `featured=only` | Uniquement les mis en avant. |
| `featured=exclude` | Uniquement les non mis en avant. |
| Méta historique `_seed_featured=0` | Considérée comme non mise en avant ; incluse par `exclude`, exclue par `only`. |
| Aucun featured | Tableau vide, aucun fallback. |
| `ids` ordonnés | Ordre fourni conservé. |
| CSV `ids` vide dans un adaptateur shortcode | Normalisé en `ids=array()` ; le mode manuel n'est pas activé. |
| IDs dupliqués | Première occurrence seulement. |
| ID inexistant | Ignoré. |
| Mauvais CPT | Ignoré. |
| Brouillon ou privé | Ignoré. |
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
| Plusieurs Citations | Un ID publié déterministe. |
| Même jour et même liste | Même ID. |
| Même entrée sur PHP 32 et 64 bits | Même index et même ID. |
| Jour suivant | Nouvelle sélection possible, non garantie différente. |
| Une Citation | Même ID chaque jour. |
| Aucune Citation | `0`. |
| Brouillon | Jamais sélectionné. |
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

## 29. Documentation à mettre à jour dans les lots futurs

Après validation du présent contrat, les lots concernés devront mettre à jour uniquement lorsque leur comportement existe :

- [docs/CONTENT-DATA-API.md](CONTENT-DATA-API.md) pour `testimonial_date` ;
- [docs/DYNAMIC-DATA.md](DYNAMIC-DATA.md) si un champ date est retenu ;
- [plugin/docs/USAGE.md](../plugin/docs/USAGE.md) pour les nouveaux champs et attributs effectivement livrés ;
- [plugin/docs/TESTING.md](../plugin/docs/TESTING.md) pour la recette runtime ;
- [PROJECT-SNAPSHOT.md](../PROJECT-SNAPSHOT.md) après chaque jalon validé ;
- le changelog de la future release.

La documentation utilisateur ne doit pas annoncer une syntaxe shortcode ou une intégration builder avant son implémentation et sa validation runtime.

## 30. Règle de lecture

Ce document fixe le contrat cible Collections V1 avant implémentation.

Lorsqu'il décrit une cible qui n'existe pas encore, il ne remplace pas la description du comportement courant dans Content Data, Dynamic Data ou USAGE. Les lots futurs devront mettre ces documents en cohérence au moment exact où le comportement correspondant sera implémenté.

En cas de contradiction entre une proposition technique et les priorités, états vides ou invariants de ce document, le contrat doit être réexaminé explicitement avant tout changement de code.
