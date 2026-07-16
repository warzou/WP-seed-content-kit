# Provider Gutenberg Block Bindings V1

Statut : provider serveur implémenté et validé ; intégration éditeur native différée après audit

Ce document définit le contrat canonique du provider Gutenberg Block Bindings V1 de WP Seed Content Kit et consigne la décision relative à son intégration dans l'éditeur.

Il fixe le périmètre fonctionnel, les identifiants persistés, les règles de contexte et la séparation des responsabilités. Le provider serveur PHP est implémenté. Aucune intégration JavaScript éditeur n'est implémentée. Ce contrat ne modifie ni le contrat Dynamic Data V1, ni la Content Data API V1, ni les contrats publics existants.

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

L'état d'activation d'un module ne désenregistre pas la source et ne bloque pas un contenu publié explicitement compatible. La précondition « module actif » reste propre à la future couche Collections.

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

Le provider serveur s'appuie sur `register_block_bindings_source()`, disponible à partir de WordPress 6.5.

À ce niveau :

- un binding déjà présent dans le markup peut être résolu sur le frontend ;
- la source est enregistrée uniquement si la fonction WordPress requise existe ;
- une version antérieure de WordPress ne provoque ni erreur, ni rendu alternatif propre à WP Seed ;
- la version minimale globale de WP Seed Content Kit ne change pas dans ce chantier.

Un provider serveur compatible WordPress 6.5 ne constitue pas, à lui seul, une fonctionnalité utilisateur terminée. Sans intégration éditeur, la création du binding reste technique.

### 4.2 Intégration éditeur native différée

L'intégration éditeur native a été auditée avec les API publiques disponibles à partir de WordPress 6.9, puis observée sous WordPress 7.0.1. Elle n'est pas implémentée.

Les capacités confirmées sont :

- `registerBlockBindingsSource` pour inscrire la source côté éditeur ;
- `getFieldsList` pour exposer les huit champs texte et persister `field_id` ;
- `getValues` pour fournir des valeurs synchrones au canvas ;
- les contextes `postId` et `postType` d'une Query Loop pour distinguer chaque élément.

L'argument serveur facultatif `post_id` reste hors du parcours natif. Un aperçu complet via `getValues` demanderait en outre un transport et un cache de données, mais ce point n'est pas le blocage principal.

Le blocage principal est l'absence d'une API publique permettant de limiter exactement les champs d'une source à une combinaison source, bloc et attribut. Le filtrage natif observé est global par type d'attribut. Les champs WP Seed pourraient donc être proposés sur d'autres attributs de type `string`, alors que le provider serveur n'accepte que `core/paragraph.content` et `core/heading.content`.

Cette divergence entre les choix proposés dans l'éditeur et les bindings réellement acceptés au rendu n'est pas retenue. L'intégration JavaScript native est différée.

## 5. Découpage et état des lots

### 5.1 Lot 1 — Provider serveur interne

Le provider serveur implémenté reste limité à :

- une source PHP ;
- un callback serveur ;
- les huit champs texte définis par ce document ;
- `core/paragraph` et `core/heading` ;
- le contexte courant d'une Query Loop ;
- un ID explicite facultatif ;
- des tests frontend avec un markup contrôlé ;
- aucun JavaScript ;
- aucune promotion comme fonctionnalité utilisateur finalisée.

### 5.2 Lot 2 — Intégration éditeur différée

L'inscription JavaScript de la source n'est pas implémentée.

Sa reprise n'est autorisée que si au moins une condition est remplie :

- une API publique WordPress permet de filtrer les champs par source, bloc et attribut ;
- une solution native officiellement supportée permet de masquer les champs sur les blocs et attributs interdits ;
- un besoin produit suffisamment important justifie une interface WP Seed dédiée après un audit UX séparé.

Le provider serveur reste fonctionnel, public et testable indépendamment de cette décision.

## 6. Champs exposés en V1

Le provider texte V1 expose exactement huit champs.

Citation :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source`.

Témoignage :

- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.testimonial_date`.

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
| `quote.quote` | `textarea` | `string` | Paragraphe, Titre | `content` | `''` | Texte brut à valider | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `quote.author` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `quote.era` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `quote.source` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `testimonial.text` | `textarea` | `string` | Paragraphe, Titre | `content` | `''` | Texte brut à valider | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `testimonial.name` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `testimonial.context` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Sans traitement propre | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |
| `testimonial.testimonial_date` | `text` | `string` | Paragraphe, Titre | `content` | `''` | Date ISO canonique brute, sans localisation | Provider serveur, WordPress 6.5+ | Intégration éditeur différée |

Les types `text` et `textarea` restent les types internes du registre Dynamic Data. Ils décrivent notamment le caractère potentiellement multiligne d'une valeur. Dans le contrat d'un éventuel lot éditeur, chacun des huit identifiants serait déclaré comme un champ de type `string`. Cette adaptation de type ne produirait ni `nl2br()`, ni HTML, ni formatage propre au provider.

Le provider ne doit pas étendre silencieusement cette matrice à d'autres blocs ou attributs.

## 8. Arguments persistés du binding

La V1 définit deux arguments publics.

### 8.1 `field_id`

`field_id` est obligatoire.

Il doit :

- correspondre à l'un des huit champs exposés par le provider ;
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

Le provider transmet ce contexte au résolveur sans reconstruire ses contrôles métier ou d'accès. Toute valeur retournée normalement est conservée telle quelle. Pour les huit champs texte V1, l'absence de contenu compatible et accessible produit donc la valeur vide typée `''`, et non `null`.

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

Cette limite réduit l'usage direct des bindings dans leurs propres écrans. Le scénario de rendu principal validé reste une Query Loop dans une page contenant un bloc compatible et un markup de binding contrôlé. Un `post_id` explicite relève du contrat serveur et des usages manuels, programmatiques ou de test documentés plus loin ; il n'est pas exposé par une interface éditeur WP Seed.

## 12. Valeurs vides et erreurs

### 12.1 Binding mal formé

Le provider retourne `null` lorsque le binding est mal formé, notamment si :

- `field_id` est absent ;
- `field_id` n'est pas textuel ;
- le champ demandé ne fait pas partie de l'allowlist des huit champs ;
- la structure des arguments est manifestement invalide.

### 12.2 Valeur retournée normalement par le résolveur

Le provider conserve la valeur telle quelle. Cette règle couvre aussi bien une chaîne non vide que la chaîne vide `''`.

Pour les huit champs texte V1, le résolveur retourne `''` lorsque la valeur métier est vide ou lorsqu'aucun contenu compatible et accessible ne peut être résolu. Le provider ne tente pas de distinguer un champ réellement vide, un ID invalide, un mauvais type de contenu, un contexte incompatible ou un contenu inaccessible. Reconstruire cette distinction dupliquerait la logique métier et les contrôles d'accès du résolveur.

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

Cette observation ne constitue pas une promesse définitive pour toutes les versions WordPress. Elle a été confirmée pendant la validation runtime du provider serveur sous WordPress 7.0.1 et devra être réévaluée pour toute nouvelle version ciblée.

## 13. Retours à la ligne

`quote.quote` et `testimonial.text` sont des champs `textarea`.

Ce type reste interne au registre Dynamic Data. Le contrat d'un éventuel lot éditeur les déclarerait comme des `string`, au même titre que les cinq champs internes de type `text`.

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

Workflow Block Bindings :

Bloc Core → binding Gutenberg WP Seed → contenu courant ou Query Loop → rendu dynamique

Le provider ne remplace pas :

- les Templates WP Seed ;
- les placeholders ;
- les shortcodes ;
- le rendu par Layout Divi Library.

Un `seed_template` ne fournit pas naturellement un contexte `seed_quote` ou `seed_testimonial`. Sans `post_id` explicite, un binding WP Seed placé dans son contenu est donc susceptible de ne produire aucune donnée.

Le provider ne modifie aucune logique de template et ne change aucun shortcode public.

## 17. Contrat du provider serveur

Le provider serveur reste limité à :

- l'enregistrement PHP de la source ;
- un callback serveur ;
- l'utilisation des contextes `postId` et `postType` ;
- l'allowlist des huit champs texte ;
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

## 18. Décision sur l'intégration éditeur

### 18.1 Capacités publiques confirmées

L'audit confirme que les API publiques de WordPress disponibles à partir de la version 6.9 permettent :

- d'inscrire la source avec `registerBlockBindingsSource` ;
- de lister les huit champs texte avec `getFieldsList` ;
- de persister `field_id` dans les arguments du binding ;
- de fournir des valeurs synchrones au canvas avec `getValues` ;
- d'utiliser les contextes `postId` et `postType` d'une Query Loop.

Le parcours natif ne fournit pas de saisie générique de `post_id`. Cet argument reste un contrat serveur pour le markup manuel, les usages programmatiques, les tests contrôlés et de futures intégrations spécialisées.

Un aperçu complet avec `getValues` demanderait un transport et un cache adaptés. Cette question reste différée avec le lot éditeur, mais elle n'est pas la cause principale de la décision.

### 18.2 Blocage principal

Le filtrage natif observé est global par type d'attribut. Aucune API publique confirmée ne permet de restreindre précisément les champs d'une source à la combinaison suivante :

- source `wp-seed-content-kit/dynamic-data` ;
- bloc `core/paragraph` ou `core/heading` ;
- attribut `content`.

Les huit champs WP Seed, tous déclarés comme `string`, pourraient donc être proposés sur d'autres attributs compatibles de blocs Core, notamment des boutons, images, éléments de navigation, dates ou autres blocs possédant un attribut texte bindable. Le provider serveur rejetterait pourtant ces bindings hors matrice.

L'éditeur proposerait alors des choix qui ne produiraient pas de rendu dynamique. Cette incohérence est incompatible avec le contrat V1.

Aucun contournement fondé sur une API interne ou non documentée n'est retenu. Les filtres globaux de WordPress ne doivent pas être modifiés, car ils affecteraient aussi les autres sources de bindings, Pattern Overrides, les fonctionnalités Core et les extensions tierces.

### 18.3 Décision figée

L'intégration JavaScript native est différée. Elle n'est ni implémentée ni annoncée comme une fonctionnalité éditeur complète.

Le provider serveur n'est pas abandonné. Il reste :

- actif dans le code ;
- validé en runtime sous WordPress 7.0.1 ;
- utilisable avec un markup contrôlé, manuel ou généré programmatiquement ;
- utilisable au rendu frontend dans une Query Loop ;
- la fondation publique du contrat Gutenberg Block Bindings.

Une reprise de l'intégration éditeur exige l'une des conditions définies en section 5.2 et un nouvel audit des API WordPress alors disponibles.

### 18.4 Architectures différées

Les architectures suivantes ont été étudiées mais ne sont pas retenues dans le lot actuel :

- `getFieldsList` avec un `getValues` neutre : n'empêche pas la sélection de champs sur des cibles que le serveur refuse et laisse un aperçu statique trompeur ;
- endpoint REST unitaire ou batch : peut alimenter un aperçu, mais ne résout pas le filtrage des cibles et introduit prématurément un transport public ;
- préchargement du contenu courant : ne couvre pas correctement les contextes multiples d'une Query Loop ;
- panneau ou bloc propriétaire WP Seed : contourne l'interface native, augmente la maintenance et demande un audit UX séparé ;
- support forcé de `core/button`, `core/image` ou d'autres blocs : dépasse le contrat V1 et requiert des mappings de données distincts.

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

Garde-fou : maintenir l'identifiant public documenté et unique, sans alias V1.

### 20.2 Provider serveur trop technique

Risque : le rendu fonctionne, mais l'utilisateur ne peut pas créer facilement le binding.

Garde-fou : considérer le lot serveur comme un socle interne et ne pas le promouvoir comme fonctionnalité terminée.

### 20.3 Dépendance de l'UX aux API WordPress

Risque : les capacités de l'interface native et leur granularité varient indépendamment de l'API serveur.

Garde-fou : conserver le provider serveur indépendant et ne reprendre l'intégration éditeur qu'après un nouvel audit des API publiques disponibles, sans modifier prématurément la version minimale globale du plugin.

### 20.4 Différence entre `null` et `''`

Risque : un binding mal formé ou un `WP_Error` retourne `null`, tandis qu'une résolution normale vide retourne `''`. Ces deux résultats peuvent traiter différemment le contenu statique du bloc.

Garde-fou : conserver exactement la valeur normale du résolveur et tester le HTML avant/après sur toute nouvelle version WordPress ciblée. L'observation a été confirmée pendant la validation runtime sous WordPress 7.0.1.

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

Garde-fou : conserver l'intégration éditeur différée tant qu'un aperçu fiable et un filtrage cohérent avec le provider serveur ne peuvent pas être garantis.

### 20.10 Query Loop mal configurée

Risque : le bloc fournit un type de contenu incompatible avec le champ demandé.

Garde-fou : transmettre `postId` et `postType`, laisser le résolveur contrôler la compatibilité et ne jamais rechercher un autre contenu.

### 20.11 Confusion entre Templates et Dynamic Data

Risque : présenter Block Bindings comme le remplacement du moteur de templates.

Garde-fou : conserver les deux workflows, leurs responsabilités et leurs contrats publics.

### 20.12 `post_id` et interface native

Risque : laisser croire que l'utilisateur peut saisir un `post_id` arbitraire dans l'interface native Gutenberg V1.

Garde-fou : documenter la Query Loop comme scénario de rendu principal du provider serveur et réserver `post_id` au markup manuel, aux usages programmatiques, aux tests contrôlés et aux futures intégrations spécialisées. Aucune saisie native de cet argument n'est annoncée.

### 20.13 Filtrage natif insuffisant

Risque : l'interface native propose les champs WP Seed sur des blocs ou attributs que le provider serveur rejette, parce que le filtrage public disponible est global par type d'attribut.

Garde-fou : différer l'intégration éditeur, ne pas utiliser d'API interne et ne pas modifier les filtres globaux de WordPress. La cause est une limite de filtrage de l'API publique observée, pas une impossibilité générale de Gutenberg.

## 21. Validations acquises et conditions de reprise

### 21.1 Provider serveur validé

Le provider serveur a été validé statiquement et en runtime sous WordPress 7.0.1. Les validations acquises couvrent notamment :

- l'enregistrement unique et défensif de la source ;
- les huit champs texte autorisés ;
- `core/paragraph.content` et `core/heading.content` ;
- les contextes distincts d'une Query Loop ;
- l'autorité de `post_id` et l'absence de fallback après un ID explicite invalide ;
- la différence entre `null` et `''` ;
- les textes multilignes, Unicode et le HTML historique ;
- la résolution d'un contenu publié compatible lorsque son module est désactivé ;
- les brouillons non exposés sans autorisation ;
- l'absence de régression des templates, placeholders et shortcodes.

### 21.2 Checklist d'un futur réaudit éditeur

Avant toute reprise, un nouvel audit devra :

- vérifier les évolutions de l'API publique WordPress alors disponible ;
- rechercher un filtrage exact par source, bloc et attribut ;
- réévaluer `getFieldsList` et `getValues` ;
- vérifier si l'interface native prend en charge les arguments tels que `post_id` ;
- auditer Spectra séparément, sans déduire sa compatibilité de Gutenberg ;
- réévaluer le besoin d'un transport REST batch pour le canvas ;
- tester le canvas d'une Query Loop avec plusieurs contextes ;
- confirmer la parité entre les choix proposés dans l'éditeur et le provider serveur.

Aucune version future de WordPress n'est présumée résoudre ces points.

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

La source `wp-seed-content-kit/dynamic-data`, les arguments `field_id` et `post_id`, ainsi que l'allowlist des huit champs deviennent des contrats persistés dès leur première utilisation dans du contenu WordPress.

## 23. Règle de lecture

Ce document définit le contrat du provider Gutenberg Block Bindings V1 implémenté côté serveur et consigne la décision de différer son intégration JavaScript native dans l'éditeur.

Il autorise la conservation, la maintenance et les tests du provider serveur existant. Il n'autorise pas, sans nouveau lot validé :

- l'ajout d'un JavaScript éditeur ;
- la création d'un endpoint REST pour le canvas ;
- l'utilisation d'API Gutenberg internes ou non documentées ;
- la modification des filtres globaux de Block Bindings ;
- la création d'un panneau ou d'un bloc propriétaire WP Seed ;
- l'extension silencieuse à d'autres blocs, attributs ou constructeurs.

Toute reprise suit les conditions de la section 5.2 et la checklist de la section 21.2.
