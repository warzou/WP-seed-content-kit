# Provider Gutenberg Block Bindings V1

Statut : contrat de conception candidat avant implémentation

Ce document définit le contrat canonique du futur provider Gutenberg Block Bindings V1 de WP Seed Content Kit.

Il fixe le périmètre fonctionnel, les identifiants persistés, les règles de contexte et la séparation des responsabilités avant toute implémentation PHP ou JavaScript. Il ne modifie ni le contrat Dynamic Data V1, ni la Content Data API V1, ni les contrats publics existants.

## 1. Objectif

Le provider doit permettre à des blocs Gutenberg Core compatibles d'afficher une valeur métier normalisée de WP Seed Content Kit sans connaître son stockage WordPress.

La chaîne cible est :

Content Data API → registre et résolveur Dynamic Data → provider Gutenberg Block Bindings → bloc Core compatible → rendu dynamique

Dans cette chaîne :

- la Content Data API fournit les données métier normalisées ;
- le registre Dynamic Data décrit les champs disponibles ;
- le résolveur Dynamic Data fournit la valeur d'un champ pour un contenu donné ;
- le provider traduit les arguments et le contexte Gutenberg ;
- Gutenberg applique la valeur à un attribut de bloc compatible ;
- aucun composant ne reprend la responsabilité du composant précédent.

Le provider doit uniquement :

- enregistrer une source Gutenberg Block Bindings ;
- traduire les arguments persistés du binding ;
- traduire le contexte courant fourni par Gutenberg ;
- appeler `wp_seed_content_resolve_dynamic_data()` ;
- adapter le résultat au contrat Block Bindings.

Le provider ne doit jamais :

- lire directement des métadonnées WordPress ;
- recalculer une règle métier ;
- sélectionner, filtrer, trier ou paginer une collection ;
- produire du HTML métier ;
- contourner les règles d'accès de la Content Data API ;
- dépendre de Spectra, d'un thème ou d'un constructeur tiers.

## 2. Principes d'architecture

### 2.1 Une source de données unique

Le provider consomme exclusivement le résolveur Dynamic Data. Il ne possède ni copie des données, ni second modèle métier, ni fallback fondé sur les clés de stockage historiques.

### 2.2 Un adaptateur Gutenberg limité

Le provider connaît Gutenberg uniquement pour :

- lire les arguments du binding ;
- lire `postId` et `postType` dans le contexte du bloc ;
- retourner une valeur compatible avec l'attribut demandé.

Il ne transforme pas WP Seed Content Kit en framework Gutenberg et ne crée aucun bloc propriétaire.

### 2.3 Une responsabilité unitaire

Le provider résout un champ pour un contenu. Il ne crée pas de requête et ne choisit jamais le premier contenu disponible.

La règle centrale est :

La Query Loop sélectionne. Le résolveur fournit. Le provider adapte. Le bloc affiche.

## 3. Identifiant public de la source

L'identifiant stable recommandé est :

`wp-seed-content-kit/dynamic-data`

Cet identifiant :

- appartient explicitement à WP Seed Content Kit ;
- limite le risque de collision ;
- ne suggère pas un registre global partagé par tout l'écosystème WP Seed ;
- pourra être stocké durablement dans le contenu des blocs.

Il constitue donc un contrat public dès que des bindings l'utilisent. La V1 ne prévoit aucun alias, identifiant historique ou mécanisme de renommage.

## 4. Compatibilité WordPress

### 4.1 Provider serveur

Le provider serveur est possible à partir de WordPress 6.5 grâce à `register_block_bindings_source()`.

À ce niveau :

- un binding déjà présent dans le markup peut être résolu sur le frontend ;
- la source est enregistrée uniquement si la fonction WordPress requise existe ;
- une version antérieure de WordPress ne provoque ni erreur, ni rendu alternatif propre à WP Seed ;
- la version minimale globale de WP Seed Content Kit ne change pas dans ce chantier.

Un provider serveur compatible WordPress 6.5 ne constitue pas, à lui seul, une fonctionnalité utilisateur terminée. Sans intégration éditeur, la création du binding reste technique.

### 4.2 Expérience éditeur complète

L'expérience éditeur complète cible WordPress 6.9 ou supérieur.

Elle reposera sur une inscription JavaScript de la même source avec :

- `getFieldsList` pour proposer les champs dans l'interface native ;
- `getValues` pour fournir l'aperçu dans le canvas ;
- les mêmes identifiants et arguments que le provider serveur.

Cette intégration appartient à un lot séparé. Aucune interface propriétaire ne doit être créée si l'interface native de WordPress répond au besoin.

## 5. Découpage en deux lots

### 5.1 Lot 1 — Provider serveur interne

Le premier lot futur est limité à :

- une source PHP ;
- un callback serveur ;
- les sept champs texte définis par ce document ;
- `core/paragraph` et `core/heading` ;
- le contexte courant d'une Query Loop ;
- un ID explicite facultatif ;
- des tests frontend avec un markup contrôlé ;
- aucun JavaScript ;
- aucune promotion comme fonctionnalité utilisateur finalisée.

### 5.2 Lot 2 — Intégration éditeur

Le second lot futur couvrira :

- l'inscription JavaScript de la même source ;
- la liste native des sept champs ;
- l'aperçu dans l'éditeur ;
- le filtrage des blocs et attributs compatibles ;
- une validation UX sous WordPress 6.9 et 7.0 ;
- aucune interface propriétaire si l'interface native suffit.

Les deux lots restent indépendants, testables et réversibles.

## 6. Champs exposés en V1

Le provider texte V1 expose exactement sept champs.

Citation :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source`.

Témoignage :

- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context`.

Les champs suivants restent dans le registre Dynamic Data mais ne sont pas exposés par ce provider V1 :

- `quote.featured` ;
- `quote.display_order` ;
- `testimonial.photo` ;
- `testimonial.featured` ;
- `testimonial.display_order`.

Cette restriction appartient au provider Gutenberg. Elle ne modifie pas le registre Dynamic Data.

## 7. Blocs, attributs et matrice V1

Le premier périmètre est strictement limité à :

- `core/paragraph` → `content` ;
- `core/heading` → `content`.

`core/button` ne fait pas partie de la V1. Son éventuel support nécessitera un besoin utilisateur démontré et un audit distinct.

| Champ WP Seed | Type Dynamic Data | Type éditeur Block Bindings | Blocs autorisés | Attribut | Valeur vide | Retours à la ligne | Frontend | Éditeur |
|---|---|---|---|---|---|---|---|---|
| `quote.quote` | `textarea` | `string` | Paragraphe, Titre | `content` | `''` | Texte brut à valider | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `quote.author` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `quote.era` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `quote.source` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `testimonial.text` | `textarea` | `string` | Paragraphe, Titre | `content` | `''` | Texte brut à valider | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `testimonial.name` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |
| `testimonial.context` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Lot serveur, WordPress 6.5+ | Lot éditeur futur, WordPress 6.9+ |

Les types `text` et `textarea` restent les types internes du registre Dynamic Data. Ils décrivent notamment le caractère potentiellement multiligne d'une valeur. L'API éditeur Block Bindings reçoit cependant un champ de type `string` pour chacun des sept identifiants. Cette adaptation de type ne produit ni `nl2br()`, ni HTML, ni formatage propre au provider.

Le provider ne doit pas étendre silencieusement cette matrice à d'autres blocs ou attributs.

## 8. Arguments persistés du binding

La V1 définit deux arguments publics.

### 8.1 `field_id`

`field_id` est obligatoire.

Il doit :

- correspondre à l'un des sept champs exposés par le provider ;
- être contrôlé par une allowlist propre au provider ;
- ne jamais donner un accès indirect à l'intégralité du registre Dynamic Data.

Un champ absent de l'allowlist est considéré comme non résoluble par ce provider.

### 8.2 `post_id`

`post_id` est facultatif.

Il représente un contenu explicitement demandé et est traduit vers `explicit_post_id` pour le résolveur.

Lorsqu'il est présent :

- il est prioritaire sur tout contexte Gutenberg ;
- il reste autoritaire même s'il est invalide, incompatible ou inaccessible ;
- la valeur retournée normalement par le résolveur est conservée telle quelle, y compris `''` lorsque l'ID est invalide, incompatible ou inaccessible ;
- son échec ne provoque aucun fallback vers le contenu courant ;
- il ne déclenche aucune recherche alternative.

La V1 n'expose pas dans le binding :

- `allow_unpublished` ;
- `current_post_id` ;
- `current_post_type` ;
- une option de requête ;
- un paramètre de collection ;
- une option de production HTML.

## 9. Contexte Gutenberg

La source serveur demande conceptuellement les contextes :

- `postId` ;
- `postType`.

Le provider les traduit ainsi :

- `postId` → `current_post_id` ;
- `postType` → `current_post_type`.

La priorité de résolution est :

1. `post_id` explicitement présent dans les arguments ;
2. `postId` et `postType` fournis par le contexte du bloc ;
3. le comportement interne documenté du résolveur lorsqu'aucun contexte n'est transmis.

Le provider transmet ce contexte au résolveur sans reconstruire ses contrôles métier ou d'accès. Toute valeur retournée normalement est conservée telle quelle. Pour les sept champs texte V1, l'absence de contenu compatible et accessible produit donc la valeur vide typée `''`, et non `null`.

Le provider n'utilise pas :

- `get_queried_object_id()` comme source parallèle ;
- une recherche globale ;
- un fallback arbitraire ;
- des données REST implicites ;
- un contenu de remplacement choisi par WP Seed.

L'échec d'un ID explicite arrête toujours la résolution pour ce binding.

## 10. Query Loop

Le scénario V1 prioritaire est :

Query Loop Gutenberg → `core/post-template` → contexte `postId` et `postType` de chaque élément → Paragraphe ou Titre lié à un champ WP Seed

La Query Loop reste responsable :

- du type de contenu interrogé ;
- de la requête ;
- des filtres ;
- de l'ordre ;
- de la pagination ;
- de l'itération.

Le provider ne crée, ne modifie et ne mémorise aucune collection. Il résout uniquement la valeur du contenu courant fourni par la boucle.

Le test prioritaire doit utiliser une Query Loop insérée dans une page d'un thème classique. Les sites connus n'utilisent pas le Site Editor comme parcours principal.

## 11. Types de contenu et édition

Les types `seed_quote` et `seed_testimonial` sont publics et exposés dans l'API REST WordPress.

Ils ne prennent actuellement pas en charge l'éditeur de contenu WordPress. Le provider :

- ne leur ajoute pas le support `editor` ;
- ne modifie aucune propriété de leur enregistrement ;
- ne crée pas un nouvel écran d'édition ;
- ne transforme pas leurs écrans actuels en éditeurs de blocs.

Cette limite réduit l'usage direct des bindings dans leurs propres écrans. Le parcours utilisateur natif prioritaire reste une Query Loop dans une page contenant un bloc compatible. Un `post_id` explicite relève du contrat serveur et des usages manuels, programmatiques ou de test documentés plus loin ; il n'est pas un champ de saisie de l'interface native V1.

## 12. Valeurs vides et erreurs

### 12.1 Binding mal formé

Le provider retourne `null` lorsque le binding est mal formé, notamment si :

- `field_id` est absent ;
- `field_id` n'est pas textuel ;
- le champ demandé ne fait pas partie de l'allowlist des sept champs ;
- la structure des arguments est manifestement invalide.

### 12.2 Valeur retournée normalement par le résolveur

Le provider conserve la valeur telle quelle. Cette règle couvre aussi bien une chaîne non vide que la chaîne vide `''`.

Pour les sept champs texte V1, le résolveur retourne `''` lorsque la valeur métier est vide ou lorsqu'aucun contenu compatible et accessible ne peut être résolu. Le provider ne tente pas de distinguer un champ réellement vide, un ID invalide, un mauvais type de contenu, un contexte incompatible ou un contenu inaccessible. Reconstruire cette distinction dupliquerait la logique métier et les contrôles d'accès du résolveur.

Le provider ne doit :

- afficher aucun message technique ;
- choisir aucun autre contenu ;
- convertir l'absence en texte utilisateur ;
- contourner les contrôles d'accès.

### 12.3 `post_id` explicite

Un `post_id` explicitement présent reste autoritaire. Si le résolveur retourne `''` parce que cet ID est invalide, incompatible ou inaccessible, le provider conserve `''`. Il ne poursuit pas avec `postId` ou `postType` et ne cherche aucun autre contenu.

### 12.4 Erreur du résolveur

Un `WP_Error` produit par le résolveur est converti en `null`.

Son code et son message ne sont jamais affichés sur le frontend. Une éventuelle journalisation technique appartient à un lot ultérieur et ne fait pas partie de la V1.

### 12.5 Booléens et nombres

`false` et `0` restent des valeurs métier distinctes dans le résolveur. Ils ne sont pas exposés par le provider texte V1.

### 12.6 Observation WordPress 7.0.1

Sous WordPress 7.0.1 observé :

- `null` conserve le contenu statique enregistré dans le bloc ;
- `''` remplace ce contenu par une valeur vide.

Cette observation ne constitue pas une promesse définitive pour toutes les versions WordPress. Le lot serveur devra valider ce comportement sur les versions réellement supportées.

## 13. Retours à la ligne

`quote.quote` et `testimonial.text` sont des champs `textarea`.

Ce type reste interne au registre Dynamic Data. Dans `getFieldsList`, Gutenberg reçoit ces deux champs comme des `string`, au même titre que les cinq champs internes de type `text`.

Pour ces champs :

- le résolveur retourne le texte brut et ses sauts de ligne ;
- le provider ne produit aucun `nl2br()` ;
- le provider n'ajoute ni paragraphe, ni balise de rupture, ni HTML propriétaire ;
- le comportement réel de `core/paragraph.content` et `core/heading.content` doit être validé visuellement et dans le HTML rendu.

Une éventuelle adaptation future du multiligne devra être documentée comme une décision de présentation du provider, sans modifier la valeur métier du registre.

## 14. Média Témoignage

`testimonial.photo` est reporté dans un lot média dédié.

Le champ Dynamic Data fournit un objet média structuré, tandis que `core/image` utilise plusieurs attributs scalaires. Un futur provider média devra notamment documenter et tester la projection de :

- l'ID de la pièce jointe ;
- l'URL ;
- le texte alternatif.

Cette projection n'appartient pas au provider texte V1.

Aucun nouveau champ ne doit être ajouté au registre pour contourner ce travail. En particulier, la V1 ne crée pas :

- `testimonial.photo_url` ;
- `testimonial.photo_alt` ;
- `testimonial.photo_html`.

## 15. Spectra

La V1 ne promet aucune compatibilité générale avec Spectra.

Les blocs `uagb/*` :

- ne font pas partie de l'allowlist Core de ce premier provider ;
- ne deviennent pas compatibles par la seule présence de Spectra ;
- doivent être audités séparément, bloc par bloc et attribut par attribut ;
- ne justifient aucun filtre de compatibilité dans le provider Core V1.

Les blocs Spectra candidats et les intégrations spécifiques restent reportés.

## 16. Coexistence avec les Templates WP Seed

Les deux workflows répondent à des besoins distincts et doivent coexister.

Workflow historique :

Template WP Seed → placeholders → Gutenberg, Spectra ou Layout Divi Library → shortcode WP Seed

Workflow futur :

Bloc Core → binding Gutenberg WP Seed → contenu courant ou Query Loop → rendu dynamique

Le provider ne remplace pas :

- les Templates WP Seed ;
- les placeholders ;
- les shortcodes ;
- le rendu par Layout Divi Library.

Un `seed_template` ne fournit pas naturellement un contexte `seed_quote` ou `seed_testimonial`. Sans `post_id` explicite, un binding WP Seed placé dans son contenu est donc susceptible de ne produire aucune donnée.

Le provider ne modifie aucune logique de template et ne change aucun shortcode public.

## 17. Contrat du futur lot serveur

Le futur lot serveur devra rester limité à :

- l'enregistrement PHP de la source ;
- un callback serveur ;
- l'utilisation des contextes `postId` et `postType` ;
- l'allowlist des sept champs texte ;
- la traduction de `field_id` et `post_id` ;
- le rejet des bindings mal formés vers `null` ;
- la conversion des `WP_Error` vers `null` ;
- la conservation exacte des valeurs retournées normalement par le résolveur, y compris `''` ;
- un chargement défensif selon la disponibilité de `register_block_bindings_source()` ;
- des tests frontend avec un markup de binding contrôlé.

Ce lot ne contient :

- aucun JavaScript ;
- aucune interface utilisateur ;
- aucun bloc propriétaire ;
- aucune image ;
- aucun booléen ou nombre ;
- aucune intégration Spectra.

## 18. Contrat du futur lot éditeur

Le futur lot éditeur devra rester distinct du provider serveur.

Il couvrira :

- un asset JavaScript chargé uniquement dans l'éditeur concerné ;
- l'inscription de `wp-seed-content-kit/dynamic-data` côté éditeur ;
- `getFieldsList` ;
- `getValues` ;
- les labels des sept champs, tous déclarés comme des champs de type `string` ;
- le filtrage des blocs et attributs définis par la matrice V1 ;
- l'aperçu dans le canvas ;
- une validation utilisateur sous WordPress 6.9 et 7.0.

Il ne crée aucun panneau propriétaire si l'interface native de WordPress suffit.

L'interface native ciblée peut découvrir la source, lister les champs avec `getFieldsList`, permettre le choix d'un champ prédéfini, persister ses arguments associés, notamment `field_id`, puis demander les valeurs d'aperçu avec `getValues`. Ces valeurs d'aperçu conservent le résultat normal du résolveur, y compris `''`.

Le parcours utilisateur natif V1 est :

Query Loop → contexte `postId` et `postType` → choix d'un champ WP Seed → rendu dynamique

Ce parcours ne nécessite aucune saisie manuelle de `post_id`. Dans le périmètre WordPress 6.9 et 7.0 ciblé, l'interface native ne fournit pas de champ générique permettant de saisir librement un `post_id` arbitraire.

L'argument `post_id` reste autorisé par le contrat serveur pour du markup écrit manuellement, du contenu généré programmatiquement, des tests contrôlés et de futures intégrations spécialisées. Il n'est pas présenté comme saisissable dans l'interface native Gutenberg V1.

Toute interface spécifique de sélection ou de saisie explicite d'un contenu WP Seed est reportée. La V1 ne promet ni panneau propriétaire, ni composant de sélection, ni bloc propriétaire, ni autre interface personnalisée.

Le mécanisme de transport des valeurs vers l'aperçu éditeur devra être audité et documenté avant l'implémentation de ce lot. Il ne doit pas provoquer de lecture directe des métadonnées par le JavaScript.

## 19. Hors périmètre

Sont explicitement exclus de la V1 :

- provider Divi ;
- compatibilité Spectra ;
- provider Elementor ;
- images ;
- booléens ;
- nombres ;
- `core/button` ;
- collections personnalisées ;
- variations Query Loop ;
- bloc Gutenberg propriétaire ;
- interface utilisateur propriétaire ;
- ajout du support `editor` aux types de contenu ;
- exposition de `allow_unpublished` ;
- modification du registre ou du résolveur Dynamic Data ;
- modification de la Content Data API ;
- modification des templates, placeholders ou shortcodes ;
- changement de version ;
- release.

## 20. Risques et garde-fous

### 20.1 Identifiant durable

Risque : l'identifiant de source est enregistré dans le contenu WordPress et devient difficile à renommer.

Garde-fou : documenter un identifiant unique, sans alias V1, avant toute implémentation.

### 20.2 Provider serveur trop technique

Risque : le rendu fonctionne, mais l'utilisateur ne peut pas créer facilement le binding.

Garde-fou : considérer le lot serveur comme un socle interne et ne pas le promouvoir comme fonctionnalité terminée.

### 20.3 Dépendance de l'UX à WordPress 6.9

Risque : l'interface complète n'est pas disponible sur toutes les versions pouvant exécuter le provider serveur.

Garde-fou : séparer clairement la compatibilité serveur 6.5 de l'expérience éditeur 6.9+ sans modifier prématurément la version minimale globale du plugin.

### 20.4 Différence entre `null` et `''`

Risque : un binding mal formé ou un `WP_Error` retourne `null`, tandis qu'une résolution normale vide retourne `''`. Ces deux résultats peuvent traiter différemment le contenu statique du bloc.

Garde-fou : conserver exactement la valeur normale du résolveur et tester le HTML avant/après sur toutes les versions WordPress ciblées. L'observation WordPress 7.0.1 doit être reconfirmée pendant le lot serveur.

### 20.5 Texte multiligne

Risque : les sauts de ligne peuvent être rendus différemment entre le frontend et l'éditeur.

Garde-fou : conserver le texte brut et tester visuellement sans ajouter de formatage propriétaire.

### 20.6 Contexte absent dans un Template WP Seed

Risque : un binding placé dans un `seed_template` ne possède pas le contexte métier attendu.

Garde-fou : documenter les workflows séparés et réserver l'ID explicite aux usages serveur manuels, programmatiques ou de test lorsque ce cas est volontaire.

### 20.7 Compatibilité Spectra surestimée

Risque : annoncer une compatibilité sur la seule base de Gutenberg.

Garde-fou : limiter la V1 aux deux blocs Core documentés et auditer Spectra séparément.

### 20.8 Média prématuré

Risque : transformer un objet média structuré en attributs scalaires sans contrat stable.

Garde-fou : reporter `testimonial.photo` dans un lot média dédié.

### 20.9 Frontend et éditeur divergents

Risque : le rendu serveur est correct alors que l'aperçu du canvas est absent ou différent.

Garde-fou : traiter `getValues` et la validation éditeur dans un lot autonome.

### 20.10 Query Loop mal configurée

Risque : le bloc fournit un type de contenu incompatible avec le champ demandé.

Garde-fou : transmettre `postId` et `postType`, laisser le résolveur contrôler la compatibilité et ne jamais rechercher un autre contenu.

### 20.11 Confusion entre Templates et Dynamic Data

Risque : présenter Block Bindings comme le remplacement du moteur de templates.

Garde-fou : conserver les deux workflows, leurs responsabilités et leurs contrats publics.

### 20.12 `post_id` et interface native

Risque : laisser croire que l'utilisateur peut saisir un `post_id` arbitraire dans l'interface native Gutenberg V1.

Garde-fou : présenter la Query Loop comme parcours natif principal et réserver `post_id` au contrat serveur, au markup manuel, aux usages programmatiques, aux tests contrôlés et aux futures intégrations spécialisées.

## 21. Validations requises avant implémentation

Le présent contrat doit être validé avant tout code provider.

Le futur lot serveur devra ensuite vérifier au minimum :

- l'enregistrement défensif sous WordPress 6.5+ ;
- l'absence d'erreur sous une version sans Block Bindings ;
- les sept champs autorisés ;
- le rejet des autres champs du registre ;
- les deux blocs et leur unique attribut autorisé ;
- le contexte courant d'une Query Loop ;
- la priorité et l'autorité de `post_id` ;
- l'absence de fallback après un ID explicite invalide ;
- le retour `null` pour un binding mal formé ;
- les chaînes vides retournées normalement par le résolveur, sans conversion vers `null` ;
- la conservation de `''` après un ID explicite invalide, incompatible ou inaccessible ;
- la conversion de `WP_Error` vers `null` ;
- le comportement du contenu statique lorsque la source retourne `null` ou `''` ;
- le rendu HTML et visuel des champs multilignes ;
- l'absence de régression des templates, placeholders et shortcodes.

Le futur lot éditeur devra être validé séparément sous WordPress 6.9 et 7.0. Il vérifiera notamment que les sept champs sont annoncés comme `string`, que le choix natif persiste correctement `field_id`, que `getValues` respecte le résolveur commun et qu'aucune saisie libre de `post_id` n'est promise par l'interface native.

## 22. Invariants publics

L'introduction du provider ne doit modifier :

- aucun shortcode public ;
- aucun placeholder existant ;
- aucun type de contenu ;
- aucun template WP Seed ;
- aucun renderer existant ;
- aucun contrat de la Content Data API ;
- aucun identifiant Dynamic Data ;
- aucune règle d'accès aux contenus non publiés ;
- aucune intégration Divi Library.

La source `wp-seed-content-kit/dynamic-data`, les arguments `field_id` et `post_id`, ainsi que l'allowlist des sept champs deviennent des contrats persistés dès leur première utilisation dans du contenu WordPress.

## 23. Règle de lecture

Ce document définit le contrat du provider Gutenberg Block Bindings V1 avant son implémentation.

Il autorise la préparation ultérieure de deux lots séparés :

1. provider serveur interne ;
2. intégration éditeur native.

Il n'autorise pas leur implémentation dans le présent lot documentaire.
