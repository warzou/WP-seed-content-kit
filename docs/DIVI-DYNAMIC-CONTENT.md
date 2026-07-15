# Divi 5 Dynamic Content V1

Statut : provider class-based expérimental validé pour sept champs texte et une photo sous Divi 5.9.0

Ce document définit le contrat du provider expérimental Divi 5 Dynamic Content de WP Seed Content Kit. Il fixe son périmètre, ses identifiants persistants, sa traduction du contexte Divi et ses garde-fous.

L'architecture retenue utilise les classes Divi 5 `DynamicContentOptionBase` et `DynamicContentOptionInterface`. Chaque option est portée par une classe concrète et enregistrée par un appel unique à `load()`. Les filtres WordPress observés dans Divi 5.6.2 et 5.9.0 restent le pipeline sous-jacent encapsulé par cette base ; WP Seed ne les inscrit pas manuellement.

Ce contrat ne promet ni une compatibilité Divi générale, ni un support de Divi 4, Theme Builder ou Loop Builder. Il ne modifie pas les Templates WP Seed, les placeholders, les shortcodes ou le workflow Divi Library existant.

## 1. Statut expérimental

Le provider est :

- expérimental ;
- limité à Divi 5 ;
- fondé sur l'architecture class-based observée dans Divi 5.9.0 ;
- validé côté serveur et dans le Visual Builder sous Divi 5.9.0 pour quatre options Citation et quatre options Témoignage ;
- limité à `quote.quote`, `quote.author`, `quote.era`, `quote.source`, `testimonial.text`, `testimonial.name`, `testimonial.context` et `testimonial.photo` ;
- absent de la promesse produit tant qu'une décision humaine de promotion n'a pas été prise.

La validation runtime confirme le chargement class-based, la résolution serveur et la persistance des huit options. Pour `testimonial.photo`, la sélection et la persistance sont confirmées, ainsi que le rendu frontend ; l'aperçu Image du Visual Builder reste incomplet. Cette validation ne transforme pas les classes internes Divi en API tierce officiellement garantie. Le chargement reste donc défensif.

## 2. Objectif et chaîne de responsabilité

La chaîne cible est :

Dynamic Data WP Seed → option Divi Dynamic Content → contexte Divi courant → résolveur WP Seed → valeur adaptée au pipeline Divi

Le provider doit uniquement :

- enregistrer une allowlist d'options Dynamic Content ;
- recevoir le contexte transmis par Divi ;
- déterminer l'identifiant du contenu courant pertinent ;
- traduire ce contexte dans la forme attendue par le résolveur WP Seed ;
- appeler `wp_seed_content_resolve_dynamic_data()` ;
- retourner une chaîne compatible avec le pipeline Divi.

Il ne doit jamais :

- lire directement les métadonnées WordPress ;
- appeler directement la Content Data API lorsque le résolveur suffit ;
- recalculer les permissions ou les règles de publication ;
- créer une requête, une collection, un tri ou une pagination ;
- produire du HTML métier ;
- créer un endpoint REST ou AJAX WP Seed ;
- créer du JavaScript WP Seed ;
- créer un module Divi propriétaire ;
- remplacer les Templates WP Seed.

### 2.1 Architecture class-based retenue

Le provider utilise :

- `DynamicContentOptionBase` ;
- `DynamicContentOptionInterface` ;
- une classe concrète par option ;
- un appel unique à `load()` ;
- aucune inscription procédurale manuelle des filtres Divi.

Le bootstrap est `plugin/includes/integrations/divi/dynamic-content.php`. La base abstraite `WP_Seed_Content_Divi_Dynamic_Content_Quote_Base` mutualise strictement le contrat Citation. La base abstraite `WP_Seed_Content_Divi_Dynamic_Content_Testimonial_Base` mutualise séparément le contrat texte Témoignage. Quatre classes concrètes distinctes exposent Texte, Auteur, Époque et Source pour les Citations ; trois autres exposent Texte, Nom et Contexte pour les Témoignages. Une classe indépendante expose Photo avec le type Divi `image`.

Les huit identifiants réservés par le contrat sont implémentés. Les deux familles conservent des bases, des listes fermées et des chargeurs indépendants afin qu'une collision compatible ou incompatible dans une famille ne neutralise pas l'autre. Une collision propre à la classe Photo ne neutralise pas les sept sources texte.

## 3. Versions et détection

### 3.1 Divi 5

Divi 5 est le seul mécanisme ciblé par la V1.

- Les filtres modernes ont été observés dans Divi 5.6.2 et 5.9.0.
- L'architecture class-based a été validée en runtime sous Divi 5.9.0.
- Le chargement est différé sur `init` à la priorité 10 et vérifie les classes et l'interface nécessaires avant toute instanciation.
- L'absence de ces capacités laisse le provider inactif, sans comparaison de version et sans fatal.
- Une validation sur Divi 5.9.0 ne constitue pas une garantie de stabilité future.

### 3.2 Divi 4

Divi 4 utilise des filtres et un format persistant distincts. Il est hors périmètre de la V1.

Un éventuel support de Divi 4 nécessiterait un adaptateur séparé, un contrat propre et une validation indépendante. Il ne doit pas être chargé implicitement par le provider Divi 5.

### 3.3 Sans Divi

Sans Divi, le provider doit rester inactif :

- aucun effet fonctionnel si des filtres WordPress défensifs sont enregistrés globalement ;
- aucun fatal ;
- aucun avertissement utilisateur ;
- aucun effet sur WP Seed Content Kit, ses shortcodes ou ses templates.

## 4. API Divi 5 observée

Les points d'extension suivants ont été observés dans les sources Divi 5.6.2 et 5.9.0. Dans l'implémentation retenue, ils appartiennent au pipeline sous-jacent encapsulé par `DynamicContentOptionBase::load()`. WP Seed n'enregistre directement aucun de ces filtres.

### 4.1 Enregistrement des options

Filtre observé : `divi_module_dynamic_content_options`

Rôle : compléter les options Dynamic Content proposées par Divi.

Paramètres observés :

- le tableau des options ;
- l'identifiant du contenu ;
- le contexte de la requête ou de l'éditeur.

La forme observée du callback correspond à un tableau d'options accompagné d'un `post_id` entier et d'un `context` textuel. Chaque clé ajoutée au tableau devient le nom persistant de l'option. Les propriétés courantes ci-dessous ont été observées dans les sources Divi 5 auditées. Elles décrivent une structure interne observable, pas une API garantie.

| Propriété | Observation dans les sources | Rôle apparent | Statut pour le prototype |
| --- | --- | --- | --- |
| `id` | Observée | Identifier l'option dans le registre et l'interface | Caractère obligatoire ou facultatif à confirmer |
| `label` | Observée | Afficher le nom utilisateur de l'option | Caractère obligatoire ou facultatif à confirmer |
| `type` | Observée | Déclarer la nature de la valeur et limiter les propriétés compatibles | Usages `text` et `image` validés dans leur propriété respective |
| `custom` | Observée | Signaler une option tierce ou personnalisée | Caractère obligatoire ou facultatif à confirmer |
| `group` | Observée | Regrouper et ordonner les options dans l'interface | Forme et nécessité à confirmer |
| `fields` | Observée | Décrire des réglages associés à l'option | Structure et caractère facultatif à confirmer |

Le prototype doit vérifier le sous-ensemble réellement requis par Divi 5.9.0. Il ne doit pas transformer la présence observée de ces propriétés en contrat de compatibilité durable.

Moment d'appel observé : lors de la préparation des options Dynamic Content, notamment pour l'éditeur et les endpoints internes Divi chargés de fournir cette liste.

Statut : filtre WordPress sous-jacent observé. Son inscription est assurée par la classe de base Divi via `load()`, pas par WP Seed directement.

### 4.2 Résolution générique

Filtre observé : `divi_module_dynamic_content_resolved_value`

Rôle : adapter la valeur résolue d'une variable Dynamic Content.

Paramètres observés :

- la valeur courante ;
- un tableau d'arguments de résolution.

Les arguments observés peuvent inclure le nom de l'option, ses réglages, `post_id`, `context`, des substitutions, un indicateur de propriété de contenu, `loop_id`, le type de requête de boucle, `loop_object` et `request_context`.

Moment d'appel observé : pendant la résolution serveur utilisée par l'éditeur, le backend ou le frontend selon le contexte Divi.

Statut : filtre WordPress sous-jacent observé et encapsulé par la classe de base Divi ; il n’est pas inscrit manuellement par WP Seed.

### 4.3 Résolution spécifique par nom

Filtre observé : `divi_module_dynamic_content_resolved_value_{$name}`

Rôle : résoudre ou adapter une option précise, identifiée par son nom persistant.

Le suffixe `{$name}` correspond au nom enregistré et stocké dans l'expression Dynamic Content. Ce filtre réduit le risque d'intercepter des options appartenant à Divi ou à un autre plugin.

Le premier spike ne choisit pas entre ce filtre et le filtre générique : `DynamicContentOptionBase::load()` prend en charge l'inscription nécessaire. WP Seed fournit uniquement les callbacks définis par le contrat class-based.

### 4.4 Statut de ces APIs

Ces filtres sont des points d'extension WordPress observés et utilisables. Ils ne sont toutefois pas présentés ici comme une API publique officiellement garantie par Elegant Themes :

- les annotations de version observées ne fixent pas une stabilité publique claire ;
- les structures d'arguments peuvent évoluer ;
- aucun type ou classe interne Divi ne doit devenir une dépendance obligatoire sans nécessité démontrée ;
- la signature et les contextes doivent être confirmés par un prototype runtime avant commit.

## 5. Identifiants persistants V1

Divi persiste le nom d'une option dans son expression Dynamic Content. Les identifiants suivants constituent donc un contrat durable s'ils sont effectivement utilisés dans du contenu enregistré.

La V1 définit exactement huit identifiants :

1. `wp_seed_content_quote_quote`
2. `wp_seed_content_quote_author`
3. `wp_seed_content_quote_era`
4. `wp_seed_content_quote_source`
5. `wp_seed_content_testimonial_text`
6. `wp_seed_content_testimonial_name`
7. `wp_seed_content_testimonial_context`
8. `wp_seed_content_testimonial_photo`

Cette convention :

- utilise uniquement des lettres minuscules et des underscores ;
- évite les caractères risqués dans le format persistant observé ;
- appartient explicitement à WP Seed Content Kit ;
- ne suggère pas un registre global partagé par tout l'écosystème WP Seed ;
- reste identique en contexte normal et en contexte de boucle.

Aucun alias préfixé pour les boucles n'est défini en V1. Un alias ne pourra être envisagé qu'après démonstration runtime qu'il est indispensable au fonctionnement ou à l'expérience utilisateur.

Le contrat réserve et le provider implémente ces huit identifiants persistants.

## 6. Mapping vers Dynamic Data

Le provider utilise une allowlist locale exacte.

| Identifiant persistant Divi | Champ Dynamic Data WP Seed |
| --- | --- |
| `wp_seed_content_quote_quote` | `quote.quote` |
| `wp_seed_content_quote_author` | `quote.author` |
| `wp_seed_content_quote_era` | `quote.era` |
| `wp_seed_content_quote_source` | `quote.source` |
| `wp_seed_content_testimonial_text` | `testimonial.text` |
| `wp_seed_content_testimonial_name` | `testimonial.name` |
| `wp_seed_content_testimonial_context` | `testimonial.context` |
| `wp_seed_content_testimonial_photo` | `testimonial.photo` |

La V1 n'expose pas automatiquement les douze champs du registre Dynamic Data. Aucun filtre public du provider ne doit permettre d'étendre silencieusement cette allowlist.

## 7. Groupes, labels et traduction

Deux groupes utilisateur sont prévus dans la liste Dynamic Content :

- `WP Seed — Citations` ;
- `WP Seed — Témoignages`.

Labels des options Citation :

- `Texte` ;
- `Auteur` ;
- `Époque` ;
- `Source`.

Labels des options Témoignage :

- `Texte` ;
- `Nom` ;
- `Contexte` ;
- `Photo`.

Le domaine de traduction est `wp-seed-content-kit`.

Dans la structure Divi observée, `group` sert de chaîne de regroupement et de tri apparent dans l'interface. Aucun identifiant technique de groupe distinct n'a été identifié comme donnée persistante nécessaire à ce contrat. La V1 ne doit donc figer que les noms persistants des huit options et les valeurs requises pour leur enregistrement.

## 8. Type des options et filtrage

Les sept options textuelles utilisent le type Divi `text`. La source Photo utilise le type Divi `image`.

Ces types permettent à Divi de limiter techniquement leur présentation aux propriétés compatibles. Une option texte ne doit notamment pas être proposée comme source d'une propriété image, et Photo doit être proposée dans une propriété image compatible.

Ce filtrage par type est un avantage important par rapport à une interface qui afficherait des associations ensuite rejetées côté serveur. Il ne résout toutefois pas entièrement le filtrage métier :

- une option Citation peut rester visible dans un contexte Témoignage ;
- une option Témoignage peut rester visible dans un contexte Citation ;
- le provider ne doit pas inventer une seconde interface pour masquer ces cas ;
- un champ incompatible avec le CPT courant produit une chaîne vide par l'intermédiaire du résolveur.

La V1 ne promet pas un support universel de tous les modules ou de toutes les propriétés Divi.

## 9. Propriétés et modules à tester

Le contrat n'établit pas de liste exhaustive avant validation runtime.

Le prototype doit tester au minimum :

- la propriété de contenu d'un module Texte ;
- une propriété de titre textuelle ;
- une propriété textuelle simple d'un autre module, si Divi 5 en propose une compatible ;
- la propriété source d'un module Image pour Photo.

Ne sont pas annoncés en V1 :

- les boutons ;
- les URL hors propriété image testée ;
- les nombres ;
- les conditions de visibilité ;
- les Design Variables.

Le provider déclare sept valeurs de type `text` et une valeur de type `image`. Divi reste responsable de déterminer les propriétés compatibles dans son interface.

## 10. Contexte Divi et contenu courant

Les données de contexte suivantes ont été observées :

- `post_id` ;
- `loop_id` ;
- `loop_object` ;
- `context` ;
- `request_context`.

La règle runtime validée pour le premier spike est :

1. si la clé `loop_id` est absente, examiner `post_id` ;
2. si `loop_id` vaut strictement `null`, examiner `post_id` ;
3. si `loop_id` est non nul, il est autoritaire ;
4. normaliser l'identifiant retenu et vérifier qu'il désigne un post du CPT attendu par la famille de l'option : `seed_quote` ou `seed_testimonial` ;
5. si un `loop_id` non nul est invalide ou incompatible, transmettre `current_post_id => 0` sans fallback vers `post_id` ;
6. hors boucle, appliquer les mêmes validations à `post_id` ;
7. transmettre au résolveur l'identifiant et, lorsqu'il est valide, le type attendu par la famille de l'option.

`loop_object` est volontairement ignoré par le premier spike. Il ne sert ni de source d'identifiant, ni de confirmation, ni de fallback.

Cette règle a été confirmée côté serveur sous Divi 5.9.0 : contexte hors boucle avec `loop_id => null`, single Citation, contextes incompatibles, `loop_id` non nul autoritaire et Loop Builder serveur avec des identifiants distincts. Les parcours visuels restent à valider.

Lorsqu'un ID valide est disponible, le contexte transmis prend la forme conceptuelle suivante :

```php
array(
    'current_post_id'   => $resolved_post_id,
    'current_post_type' => $expected_post_type,
);
```

Lorsqu'aucun ID Divi valide n'est disponible, le provider transmet explicitement :

```php
array(
    'current_post_id' => 0,
);
```

Cette valeur interdit tout fallback ambiant du résolveur : aucun recours à `get_the_ID()`, au post global, à la page actuellement éditée ou à un contexte mémorisé d'un appel précédent.

Le provider ne doit jamais utiliser :

- un identifiant arbitraire ;
- une requête globale ;
- l'objet global WordPress comme remplacement silencieux ;
- l'identifiant de l'objet interrogé comme fallback implicite ;
- `allow_unpublished` ;
- un identifiant choisi et stocké par l'utilisateur.

## 11. Absence d'identifiant explicite

La V1 ne propose pas :

- la sélection d'une Citation précise ;
- la sélection d'un Témoignage précis ;
- un champ de configuration d'identifiant ;
- un réglage persistant supplémentaire dans l'expression Divi.

Le provider repose uniquement sur le contexte courant transmis par Divi.

Cette règle réduit :

- la complexité de l'interface ;
- le nombre de paramètres persistants ;
- les cas de fallback ;
- les risques liés à un contenu supprimé, non publié ou inaccessible.

## 12. Résolution WP Seed

Chaque option appelle uniquement le résolveur commun avec :

- l'identifiant Dynamic Data de l'allowlist ;
- le contexte courant traduit depuis Divi.

Le point d'entrée est `wp_seed_content_resolve_dynamic_data($field_id, $context)`.

Le provider ne doit pas appeler directement :

- `wp_seed_content_get_content_data()` ;
- `get_post_meta()` ;
- les fonctions de templates ;
- les helpers propres aux modules Citation ou Témoignage.

Il ne transmet jamais `allow_unpublished`.

Le résolveur reste seul responsable :

- de la validation du champ ;
- du CPT attendu ;
- de l'identifiant invalide ;
- du contenu non publié ou inaccessible ;
- des permissions ;
- de la valeur vide conforme au type du champ.

## 13. Valeurs vides et erreurs

La traduction V1 est la suivante.

### 13.1 Résultat textuel

- Retourner la chaîne telle quelle.
- Conserver la chaîne vide.
- Conserver Unicode et les retours à la ligne.
- Ne pas appliquer de suppression d'espaces en début ou fin.
- Ne pas convertir les retours à la ligne en HTML.
- Ne pas produire de balise supplémentaire.

### 13.2 Erreur WP Seed

- Retourner une chaîne vide.
- Ne jamais afficher le code ou le message technique de l'erreur.

### 13.3 Contexte absent ou inexploitable

- En l'absence d'un ID Divi valide, le provider force `current_post_id` à `0` afin que le résolveur retourne une valeur vide typée sans fallback ambiant.
- Ne recourir ni à `get_the_ID()`, ni au post global, ni à la page actuellement éditée, ni à un contexte mémorisé d'un appel précédent.
- Ne pas rechercher un autre contenu.

### 13.4 Type inattendu

- Retourner une chaîne vide.
- Ne pas convertir artificiellement un objet, un booléen ou un nombre.

Le comportement exact de la valeur statique et des mécanismes de fallback Divi lorsqu'une source dynamique retourne une chaîne vide doit être observé pendant le prototype. Ce document ne promet pas que Divi conserve ou remplace automatiquement le contenu statique.

## 14. Textes multilignes et HTML historique

`quote.quote` et `testimonial.text` peuvent contenir plusieurs lignes. Le provider retourne la valeur brute fournie par le résolveur.

Il n'applique :

- ni `trim()` ;
- ni `wpautop()` ou autre formatage automatique de paragraphes ;
- ni `nl2br()` ou autre conversion des retours à la ligne ;
- ni transformation Rich Text propre à Divi ;
- ni échappement anticipé qui modifierait la valeur avant le pipeline Divi ;
- ni correction, décodage ou réencodage des entités HTML.

La validation doit couvrir :

- fins de ligne LF ;
- fins de ligne CRLF ;
- lignes vides ;
- accents ;
- guillemets ;
- apostrophes ;
- emoji ;
- HTML historique ;
- HTML déjà encodé ;
- entités HTML ;
- double encodage ;
- texte ressemblant à une balise ;
- balise autorisée ;
- balise de script ou contenu dangereux.

Ces cas doivent être comparés entre le Visual Builder et le frontend. Le provider WP Seed retourne toujours la chaîne brute du résolveur, sans correction, décodage ou encodage supplémentaire.

Le pipeline final Divi reste responsable de son filtrage, de son échappement contextuel et de son rendu. Le provider ne doit toutefois jamais contourner les règles d'accès de WP Seed sous prétexte que Divi assurera le rendu final.

## 15. Visual Builder et endpoints Divi

Divi possède déjà ses propres mécanismes serveur pour :

- fournir les options Dynamic Content ;
- résoudre les données dynamiques.

Les routes observées sont :

- `GET /divi/v1/dynamic-content/options` pour les options Dynamic Content ;
- `POST /divi/v1/dynamic-data` pour la résolution Dynamic Data.

Ces endpoints restent sous la responsabilité de Divi, notamment pour les nonces et les permissions d'accès au builder.

Le prototype WP Seed ne crée :

- aucun endpoint REST ;
- aucun endpoint AJAX ;
- aucun asset JavaScript ;
- aucune duplication de la logique métier côté client.

Le même provider PHP doit servir le Visual Builder, le backend et le frontend, sous réserve de validation runtime. Une différence de résultat entre ces contextes constitue un risque bloquant.

Le parcours page/single constitue le cœur minimal du prototype. Il peut continuer uniquement si le Visual Builder et le frontend produisent un contexte et un résultat cohérents.

## 16. Theme Builder

Theme Builder fait partie du protocole de test, mais pas de la promesse V1.

Scénarios à valider :

- template Theme Builder assigné à `seed_quote` ;
- template Theme Builder assigné à `seed_testimonial` ;
- choix d'un contenu de prévisualisation ;
- rendu frontend d'un contenu publié ;
- absence de contenu de prévisualisation ;
- contenu brouillon ou inaccessible.

Aucune compatibilité Theme Builder ne doit être annoncée avant la réussite de ces scénarios dans le Visual Builder, après sauvegarde et sur le frontend. Theme Builder est une extension de périmètre testée séparément, pas une précondition absolue du provider texte page/single. Si son contexte n'est pas fiable, son support est reporté sans invalider un provider page/single par ailleurs validé.

## 17. Loop Builder

Loop Builder fait partie du protocole de test, mais pas de la promesse V1.

Scénarios à valider :

- boucle de Citations ;
- boucle de Témoignages ;
- `loop_id` distinct pour chaque élément ;
- champ correspondant au CPT courant ;
- champ incompatible ;
- aperçu Visual Builder ;
- rendu frontend ;
- sauvegarde et rechargement du module.

Le provider ne gère :

- ni requête ;
- ni filtre ;
- ni tri ;
- ni pagination ;
- ni limite ou comptage d'éléments.

Aucun alias dédié aux boucles n'est introduit avant preuve de sa nécessité. Loop Builder est une extension de périmètre testée séparément, pas une précondition absolue du provider texte page/single. Si son contexte ne peut pas être résolu avec les huit identifiants persistants, son support est reporté, aucune promesse de boucle n'est publiée et le provider page/single peut continuer s'il est fiable.

## 18. Divi Library et Templates WP Seed

Deux workflows distincts doivent pouvoir coexister.

Workflow officiel actuel :

Template WP Seed → placeholders → Layout Divi Library → shortcode WP Seed

Workflow futur expérimental :

Module Divi → Dynamic Content WP Seed → contexte Divi courant → résolveur WP Seed → rendu direct

Le workflow actuel reste officiel et inchangé. Les placeholders demeurent la méthode prévue dans les Layouts Divi Library utilisés comme source de rendu d'un Template WP Seed.

Le renderer actuel des Templates WP Seed ne garantit pas que l'identifiant métier de chaque élément soit injecté comme contexte global Divi. Lorsqu'un Layout Divi Library est rendu dans ce workflow, Divi peut transmettre l'ID du `seed_template`, l'ID de la page porteuse, aucun ID métier exploitable ou un autre contexte interne Divi. Aucun de ces cas ne permet au provider de deviner l'élément Citation ou Témoignage attendu.

Une option Dynamic Content WP Seed placée dans un Layout Divi Library n'est donc pas garantie dans ce workflow. Les placeholders restent la méthode officielle : le provider ne doit ni déduire l'élément métier depuis la page ou le template, ni créer un contexte implicite pour contourner cette limite.

La V1 ne doit :

- ni modifier ce renderer ;
- ni déprécier les placeholders ;
- ni remplacer les shortcodes ;
- ni présenter Dynamic Content comme une migration automatique des Layouts existants.

## 19. Média Témoignage

`testimonial.photo` est exposé comme une option Divi de type `image` sous l'identifiant persistant `wp_seed_content_testimonial_photo`.

Le provider :

- résout exclusivement `testimonial.photo` par le résolveur Dynamic Data WP Seed ;
- accepte uniquement un objet média normalisé contenant une URL non vide et, lorsqu'il est présent, un type MIME image ;
- projette uniquement `photo.url` vers le wrapper Divi officiel ;
- retourne une chaîne vide pour une valeur absente, inaccessible, invalide ou non image ;
- ne transmet ni tableau média complet, ni HTML, ni identifiant, ni alt, ni dimensions, ni type MIME à Divi.

Dans le module Image testé, Divi reconstruit l'identifiant de pièce jointe, les dimensions, `srcset` et `sizes` à partir d'une URL locale reconnue. Cette reconstruction appartient à Divi et WordPress ; elle n'est pas garantie pour une URL externe ou un média qui ne peut plus être résolu. L'alt de la médiathèque est retrouvé sur le single testé, mais pas dans les éléments de la boucle testée : il n'est donc pas garanti universellement par cette projection URL.

Aucun champ dérivé de photo n'est ajouté au registre Dynamic Data. Les sept sources texte restent inchangées.

## 20. Booléens et nombres reportés

Les champs suivants sont reportés :

- `quote.featured` ;
- `quote.display_order` ;
- `testimonial.featured` ;
- `testimonial.display_order`.

Ils ne sont pas exposés parce que :

- aucune cible Divi Dynamic Content pertinente n'est démontrée dans ce lot ;
- les Design Variables constituent un mécanisme distinct ;
- convertir ces valeurs en texte uniquement pour les rendre visibles altérerait leur contrat métier.

## 21. Sécurité et accès

Les invariants suivants sont obligatoires :

- aucun contenu non publié n'est exposé par le provider ;
- `allow_unpublished` n'est jamais activé ;
- un contexte administrateur ou Visual Builder ne contourne pas le résolveur ;
- une permission Divi ne remplace pas les règles WP Seed ;
- une donnée de Témoignage inaccessible n'est jamais retournée ;
- aucun message d'erreur WP Seed n'est affiché dans le builder ou sur le frontend ;
- un contexte invalide ne déclenche aucune recherche de contenu de remplacement.

Les endpoints Divi existants restent responsables de leurs nonces et de leurs propres droits d'accès au builder. Cette responsabilité ne donne pas au provider un droit supplémentaire sur les données WP Seed.

## 22. Performance

Le modèle V1 prévoit une résolution par variable dynamique.

Dans une boucle, le coût potentiel est donc proportionnel au nombre d'éléments multiplié par le nombre de champs utilisés.

Garde-fous :

- s'appuyer d'abord sur les caches d'objets et de métadonnées déjà utilisés par WordPress ;
- ne pas créer de cache WP Seed dédié dans la V1 ;
- ne pas introduire de batch, de préchargement ou d'invalidation complexe sans mesure réelle ;
- mesurer séparément le Visual Builder et le frontend ;
- vérifier que Divi ne résout pas plusieurs fois inutilement la même variable pendant une requête.

Une optimisation ne doit pas déplacer la logique métier dans le provider ni contourner le résolveur commun.

## 23. Chargement class-based

Le bootstrap du provider est :

`plugin/includes/integrations/divi/dynamic-content.php`

La base abstraite Citation est :

`plugin/includes/integrations/divi/class-dynamic-content-quote-base.php`

Les quatre classes concrètes sont :

- `plugin/includes/integrations/divi/class-dynamic-content-quote-text.php` ;
- `plugin/includes/integrations/divi/class-dynamic-content-quote-author.php` ;
- `plugin/includes/integrations/divi/class-dynamic-content-quote-era.php` ;
- `plugin/includes/integrations/divi/class-dynamic-content-quote-source.php`.

La base abstraite Témoignage est :

`plugin/includes/integrations/divi/class-dynamic-content-testimonial-base.php`

Les trois classes texte concrètes sont :

- `plugin/includes/integrations/divi/class-dynamic-content-testimonial-text.php` ;
- `plugin/includes/integrations/divi/class-dynamic-content-testimonial-name.php` ;
- `plugin/includes/integrations/divi/class-dynamic-content-testimonial-context.php`.

La source média utilise une classe indépendante de la base texte Témoignage :

`plugin/includes/integrations/divi/class-dynamic-content-testimonial-photo.php`

Le bootstrap principal `plugin/wp-seed-content-kit.php` charge le provider globalement après Content Data, Dynamic Data et à proximité du provider Gutenberg. Le chargement n'est pas limité à l'administration.

Le contrat de chargement validé est le suivant :

- enregistrement sur `init`, priorité 10 ;
- vérification de `DynamicContentElements`, `DynamicContentOptionBase` et `DynamicContentOptionInterface` ;
- inclusion de la base et des classes concrètes uniquement si nécessaire ;
- acceptation d'une base ou d'une classe compatible déjà chargée ;
- neutralisation de la famille Citation ou Témoignage si sa base homonyme est incompatible, sans neutraliser l'autre famille ;
- neutralisation de la seule source concernée si une classe concrète homonyme est incompatible ;
- instances statiques ;
- appel unique à `load()` pour chaque source ;
- aucune inscription manuelle par `add_filter()`.

Sans Divi ou sans les capacités requises, le provider reste inactif sans fatal et sans effet sur les autres fonctionnalités.

## 24. Validation runtime et promotion expérimentale

Les providers Citation et Témoignage ont été validés sous WordPress 7.0.1, PHP 8.4.21 et Divi 5.9.0.

Résultats confirmés :

- huit options REST WP Seed uniques, sans altération des 61 autres sources Divi ;
- mapping exact vers les quatre champs texte Citation, les trois champs texte Témoignage et `testimonial.photo` ;
- single `seed_quote` et single `seed_testimonial` ;
- page et CPT métier incompatible ;
- `loop_id => null` hors boucle ;
- `loop_id` non nul autoritaire, sans fallback vers `post_id` s'il est invalide ;
- contextes de boucle serveur avec deux valeurs Témoignage distinctes ;
- chaîne vide, texte multiligne, Unicode et HTML historique ;
- brouillons non exposés ;
- aucune régression des shortcodes, templates ou providers existants ;
- présence unique des huit options dans le registre Divi ;
- sélection, application, sauvegarde et réouverture visuelles ;
- persistance brute unique des trois identifiants Témoignage dans trois modules Texte après le contrôle final ;
- persistance brute unique de l'identifiant Photo dans un module Image ;
- projection de l'URL et rendu frontend Image en single et en boucle ;
- reconstruction des IDs média, dimensions, `srcset` et `sizes` pour les pièces jointes locales testées ;
- frontend avec les trois valeurs texte Témoignage distinctes ;
- provider Citation et ses quatre classes inchangés.

Restent différés :

- prévisualisation directe d'un corps Theme Builder sans contexte métier transmis par Divi ;
- recette visuelle Loop Builder autonome ;
- aperçu dynamique de l'image dans le canvas du Visual Builder, qui reste vide malgré la source Photo persistée.

Le provider conserve donc un statut expérimental. Il ne doit pas être présenté comme une compatibilité Divi générale ou une fonctionnalité couvrant tous les champs WP Seed.

## 25. Hors périmètre V1

Sont explicitement exclus :

- Divi 4 ;
- JavaScript WP Seed ;
- endpoint REST ou AJAX WP Seed ;
- module Divi propriétaire ;
- identifiant de contenu explicite ;
- champs média dérivés ;
- booléens ;
- nombres ;
- Design Variables ;
- support garanti de Theme Builder ;
- support garanti de Loop Builder ;
- requêtes ou collections ;
- aliases propres aux boucles ;
- modification des CPT ;
- modification des Templates WP Seed ;
- modification des placeholders ;
- modification des shortcodes ;
- changement de version ;
- release.

## 26. Risques

### 26.1 API Divi non officiellement documentée

Les filtres existent dans les versions auditées, mais leur stabilité n'est pas garantie par une documentation publique tierce.

### 26.2 Évolution rapide de Divi 5

Le registre, les structures d'arguments ou le pipeline de résolution peuvent changer entre deux versions de Divi 5.

### 26.3 Identifiants persistants

Une fois enregistrés dans un contenu Divi, les huit noms deviennent coûteux à renommer. Ils doivent être testés avant tout commit produit.

### 26.4 Filtrage métier incomplet

Les types `text` et `image` filtrent les propriétés techniques, mais ne garantissent pas que l'option corresponde au CPT courant.

### 26.5 Contexte incorrect dans Divi Library

Un Layout utilisé par le renderer WP Seed peut ne pas recevoir l'identifiant métier de l'élément rendu.

### 26.6 Prévisualisation Theme Builder ambiguë

Le contenu choisi pour l'aperçu peut différer du contenu réellement rendu sur le frontend.

### 26.7 Contexte de boucle incertain

La priorité de `loop_id` non nul et le recours à `post_id` lorsque `loop_id` est absent ou strictement nul sont confirmés côté serveur. `loop_object` est ignoré. Les parcours visuels Loop Builder restent différés et peuvent encore révéler une incompatibilité sans remettre en cause les contextes serveur déjà validés.

### 26.8 HTML historique

Certaines valeurs peuvent contenir du HTML ancien. Le comportement du pipeline final Divi doit être vérifié sans transformation prématurée.

### 26.9 Différence Visual Builder et frontend

Les chemins de résolution peuvent produire des contextes, filtrages ou échappements différents.

### 26.10 Coût dans les boucles

Le nombre de résolutions peut augmenter rapidement avec le nombre d'éléments et de champs.

### 26.11 Absence de compatibilité Divi 4

Un site restant sur Divi 4 ne bénéficie pas du provider V1. Le plugin doit toutefois continuer à fonctionner normalement.

## 27. Conditions d'abandon ou de report

Le prototype page/single doit être abandonné ou différé si :

- les filtres ou leurs signatures diffèrent de manière incompatible entre les sources auditées et Divi 5.9.0 ;
- l'éditeur n'affiche pas correctement les options tierces ;
- Divi propose les options dans des propriétés incompatibles malgré leur type déclaré ;
- la sauvegarde altère les identifiants persistants ou leurs réglages ;
- le Visual Builder et le frontend utilisent des pipelines incompatibles ;
- l'API d'enregistrement ou de résolution est instable ou incompatible avec le contrat ;
- le contexte page/single ne peut pas être fiabilisé ;
- le provider exige une dépendance à des classes internes profondes ;
- l'intégration perturbe les sources Dynamic Content natives de Divi ou celles d'autres plugins ;
- les règles de publication de WP Seed ne peuvent pas être garanties ;
- le coût runtime devient excessif sans solution simple et mesurée.

Un échec propre à Theme Builder reporte uniquement le support Theme Builder si le contexte page/single reste fiable. Un échec propre à Loop Builder reporte uniquement le support Loop Builder : aucune promesse de boucle n'est publiée, aucun alias ou hack n'est ajouté et le provider page/single peut continuer. Theme Builder et Loop Builder sont des extensions de périmètre évaluées séparément.

Le report est préférable à l'introduction d'une abstraction générale, d'un endpoint ou d'un module propriétaire uniquement pour contourner ces limites.

## 28. Matrice de validation

### 28.1 Chargement et enregistrement

Validé côté serveur sous Divi 5.9.0 :

- chargement défensif et `load()` unique pour chaque source ;
- huit options WP Seed enregistrées une seule fois ;
- identifiants exacts : `wp_seed_content_quote_quote`, `wp_seed_content_quote_author`, `wp_seed_content_quote_era`, `wp_seed_content_quote_source`, `wp_seed_content_testimonial_text`, `wp_seed_content_testimonial_name`, `wp_seed_content_testimonial_context` et `wp_seed_content_testimonial_photo` ;
- labels `Texte`, `Auteur`, `Époque` et `Source` dans le groupe `WP Seed — Citations` ; labels `Texte`, `Nom`, `Contexte` et `Photo` dans le groupe `WP Seed — Témoignages` ; types `text` et `image`, `custom => false`, `fields => array()` ;
- 61 autres sources Divi préservées ;
- aucune inscription manuelle des filtres Divi.

### 28.2 Interface Divi

Validé visuellement dans des modules Texte Divi :

- présence unique des quatre sources Citation et des trois sources Témoignage dans une propriété textuelle compatible ;
- sélection et application successives ;
- sauvegarde puis réouverture sans altération de l'identifiant final ;
- pastille `Source` conservée après réouverture ;
- pastilles `Texte` et `Contexte` Témoignage reconnues dans l'éditeur ;
- persistance brute unique de `Texte`, `Nom` et `Contexte` dans le brouillon de recette ;
- aucune duplication ou erreur visible.

Validé dans un module Image Divi :

- source Photo sélectionnée et pastille `Photo` conservée après sauvegarde et réouverture ;
- identifiant persistant présent une seule fois dans le contenu du module et absent des métadonnées Divi ;
- aperçu Image vide dans le canvas malgré la source conservée ;
- variables de boucle internes visibles sous forme brute dans le canvas de recette, sans incidence sur le frontend ;
- rendu frontend des images correct.

### 28.3 Contextes

Validé côté serveur :

- single Citation publié ;
- single Témoignage publié ;
- page incompatible ;
- CPT métier incompatible ;
- absence de contexte valide avec `current_post_id` forcé à `0` ;
- `loop_id => null` avec recours à `post_id` ;
- `loop_id` non nul autoritaire ;
- Loop Builder serveur avec identifiants distincts.

Restent différés : prévisualisation directe d'un corps Theme Builder sans contexte métier et recette visuelle Loop Builder autonome. Le frontend Theme Builder en contexte `seed_quote` est validé.

### 28.4 Valeurs

Validé :

- texte simple ;
- chaîne vide ;
- multiligne ;
- Unicode ;
- HTML historique ;
- photo locale publiée ;
- photo absente, média supprimé et média non image ;
- brouillon non exposé ;
- erreur WP Seed non affichée.

Le frontend Témoignage a conservé le multiligne, Unicode et le HTML historique dans le pipeline Divi. Les différences éventuelles avec d'autres propriétés ou modules Divi restent à évaluer.

### 28.5 Non-régression

Validé :

- shortcodes WP Seed inchangés ;
- templates et placeholders inchangés ;
- provider serveur Gutenberg inchangé ;
- sources Dynamic Content natives Divi préservées ;
- aucun warning ou fatal WP Seed.

## 29. Invariants du contrat

La V1 respecte les invariants suivants :

- Divi 5 uniquement ;
- statut expérimental maintenu jusqu'à décision humaine ;
- huit identifiants persistants réservés et implémentés par le contrat ;
- une classe concrète par option ;
- chargement par `DynamicContentOptionBase::load()` ;
- aucune inscription procédurale manuelle des filtres Divi ;
- types Divi `text` et `image` uniquement ;
- aucun identifiant de contenu explicite ;
- contexte Divi explicite uniquement ;
- résolution exclusivement par le résolveur Dynamic Data WP Seed ;
- aucune lecture directe des métadonnées ;
- aucun contenu non publié exposé ;
- aucun endpoint, JavaScript ou module Divi WP Seed ;
- Theme Builder et Loop Builder visuels reportés et non promis ;
- booléens et nombres reportés ;
- Templates WP Seed, placeholders, shortcodes et Divi Library conservés.

## 30. Règle de lecture

Ce document fixe le contrat expérimental après l'implémentation et la validation serveur et visuelle des quatre champs Citation, des trois champs texte Témoignage et de la source Photo. Il ne vaut ni compatibilité Divi générale, ni garantie universelle de l'aperçu ou des métadonnées média, ni promesse produit.

En cas de contradiction entre une proposition technique future et ce contrat, la décision doit être réexaminée explicitement. Une contrainte de Divi ne doit pas modifier silencieusement le sens des données WP Seed, contourner le résolveur ou fragiliser les workflows existants.
