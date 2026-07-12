# Dynamic Data V1

Statut : contrat de conception candidat avant implémentation

Ce document définit la future couche interne Dynamic Data V1 de WP Seed Content Kit. Il fixe son périmètre, le vocabulaire des champs et la séparation des responsabilités avant toute implémentation technique.

Il ne définit ni signature PHP définitive, ni provider pour un constructeur, ni mécanisme d’extension public.

## 1. Objectif

Dynamic Data doit permettre à des constructeurs ou à des thèmes de consommer les données métier normalisées de WP Seed Content Kit sans connaître leur stockage WordPress.

La chaîne cible est :

Stockage WordPress → Content Data API → registre Dynamic Data Content Kit → résolveur Dynamic Data Content Kit → providers propres aux builders → constructeur ou thème

Dans cette chaîne :

- la Content Data API reste la source métier ;
- le registre décrit les champs disponibles ;
- le résolveur fournit leurs valeurs normalisées ;
- chaque provider traduit ces valeurs pour son constructeur ;
- aucun builder ne relit directement les métadonnées ;
- aucun provider ne recalcule la logique métier ;
- aucun builder n’est une dépendance obligatoire.

Dynamic Data ne remplace ni les shortcodes, ni les templates, ni les placeholders existants. Ces mécanismes restent valables et conservent leurs contrats publics.

## 2. Principes

### 2.1 Données métier comme source unique

Le registre et le résolveur consomment uniquement :

- `wp_seed_content_get_quote_data()` pour une Citation ;
- `wp_seed_content_get_testimonial_data()` pour un Témoignage.

Ils ne lisent pas directement les clés de métadonnées WordPress.

### 2.2 Registre propre à Content Kit

Les identifiants Dynamic Data appartiennent uniquement à WP Seed Content Kit. Le registre n’est ni global à WordPress, ni partagé entre les plugins WP Seed.

Chaque plugin métier reste propriétaire de ses données et de son registre éventuel.

### 2.3 Aucune présentation

Dynamic Data retourne des valeurs brutes ou structurées. Il ne produit pas :

- de HTML ;
- de ponctuation de présentation ;
- de balise image ;
- de classe CSS ;
- de mise en page propre à un builder.

### 2.4 Champs métier uniquement

La V1 expose les données propres aux modules Citation et Témoignage. Les données WordPress déjà disponibles n’y sont pas redéfinies.

### 2.5 Séparation avec les collections

Dynamic Data résout un champ pour un contenu donné. Il ne cherche pas, ne filtre pas, ne trie pas et ne sélectionne pas une collection de contenus.

Une boucle peut fournir un contenu courant au résolveur, mais elle reste propriétaire de sa requête et de son itération.

## 3. Périmètre V1

La V1 couvre exactement douze champs.

Citation :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `quote.featured` ;
- `quote.display_order`.

Témoignage :

- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.photo` ;
- `testimonial.featured` ;
- `testimonial.display_order`.

Les identifiants `quote.*` et `testimonial.*` sont les identifiants stables de ce contrat V1.

## 4. Types et valeurs vides

| Type | Valeur retournée | Valeur vide | Règle |
| --- | --- | --- | --- |
| `text` | Chaîne simple | Chaîne vide | Aucun HTML ajouté. |
| `textarea` | Chaîne multiligne | Chaîne vide | Les sauts de ligne peuvent être conservés, sans conversion HTML. |
| `image` | Objet média minimal | `null` | Aucune balise image produite. |
| `number` | Entier | `0` | Aucun formatage de présentation. |
| `boolean` | Booléen strict | `false` | Ne doit pas être converti en libellé utilisateur. |

Lorsqu’un champ est connu mais vide, ou qu’aucun contexte compatible n’est disponible, le résolveur retourne la valeur vide déclarée pour son type.

Il ne retourne pas de message d’aide, ne produit pas de HTML et ne tente aucune recherche globale de contenu.

## 5. Contrat Citation

| Identifiant stable | Libellé utilisateur | Module | Type de contenu compatible | Type | Clé Content Data API | Valeur vide | Contexte courant | ID explicite | Registre V1 | Exposition par les providers |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `quote.quote` | Citation | Citation | `seed_quote` | `textarea` | `quote` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `quote.author` | Auteur | Citation | `seed_quote` | `text` | `author` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `quote.era` | Époque / date affichée | Citation | `seed_quote` | `text` | `era` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `quote.source` | Source / contexte | Citation | `seed_quote` | `text` | `source` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `quote.featured` | Mise en avant | Citation | `seed_quote` | `boolean` | `featured` | `false` | Oui | Oui | Inclus | Peut être reporté |
| `quote.display_order` | Position éditoriale | Citation | `seed_quote` | `number` | `display_order` | `0` | Oui | Oui | Inclus | Peut être reporté |

`quote.featured` représente un état métier. Il ne désigne jamais une image mise en avant WordPress.

`quote.display_order` expose une valeur éditoriale. Sa résolution ne déclenche aucune requête, aucun tri et aucune collection.

## 6. Contrat Témoignage

| Identifiant stable | Libellé utilisateur | Module | Type de contenu compatible | Type | Clé Content Data API | Valeur vide | Contexte courant | ID explicite | Registre V1 | Exposition par les providers |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `testimonial.text` | Témoignage | Témoignage | `seed_testimonial` | `textarea` | `text` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `testimonial.name` | Nom ou initiales | Témoignage | `seed_testimonial` | `text` | `name` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `testimonial.context` | Contexte | Témoignage | `seed_testimonial` | `text` | `context` | Chaîne vide | Oui | Oui | Inclus | À décider par provider |
| `testimonial.photo` | Photo | Témoignage | `seed_testimonial` | `image` | `photo` | `null` | Oui | Oui | Inclus | À décider par provider |
| `testimonial.featured` | Mise en avant | Témoignage | `seed_testimonial` | `boolean` | `featured` | `false` | Oui | Oui | Inclus | Peut être reporté |
| `testimonial.display_order` | Position éditoriale | Témoignage | `seed_testimonial` | `number` | `display_order` | `0` | Oui | Oui | Inclus | Peut être reporté |

`testimonial.featured` est un booléen métier distinct de l’image mise en avant WordPress.

`testimonial.display_order` ne constitue pas une API de tri ou de collection.

Dans ces tableaux, « Contexte courant : Oui » signifie que le champ est disponible uniquement lorsqu’un contenu compatible est effectivement fourni par le contexte.

## 7. Photo du Témoignage

`testimonial.photo` reste un champ Dynamic Data WP Seed même si certains builders savent déjà utiliser l’image mise en avant WordPress.

Le résolveur commun retourne l’objet média minimal de la Content Data API :

| Clé | Type | Valeur vide interne |
| --- | --- | --- |
| `id` | Entier | `0` |
| `url` | Chaîne | Chaîne vide |
| `alt` | Chaîne | Chaîne vide |
| `width` | Entier | `0` |
| `height` | Entier | `0` |
| `mime_type` | Chaîne | Chaîne vide |

Une photo absente est représentée par `null`.

Le provider choisit ensuite la projection attendue par son constructeur. Il peut utiliser l’identifiant, l’URL, le texte alternatif ou une structure propre au builder.

Le registre et le résolveur ne produisent jamais de balise `<img>`.

Un provider pourra masquer `testimonial.photo` dans un contexte où la source native « image mise en avant » est plus pertinente. Cette décision de présentation ne retire pas le champ du registre.

## 8. Données WordPress non dupliquées

La V1 n’ajoute pas les champs suivants :

- titre WordPress ;
- permalien ;
- identifiant d’URL ;
- statut ;
- date de publication ;
- image mise en avant générique WordPress.

Ces données restent sous la responsabilité de WordPress et des builders qui les exposent déjà.

Sont également exclus :

- champs historiques non stables ;
- données Cards ;
- données Annuaire ;
- données Créations sonores.

## 9. Modèle du registre

Le registre V1 est descriptif.

Chaque entrée décrit conceptuellement :

- `id` : identifiant stable du champ ;
- `label` : libellé utilisateur ;
- `module` : Citation ou Témoignage ;
- `type` : type Dynamic Data ;
- `post_type` : CPT compatible ;
- `data_key` : clé correspondante dans la Content Data API ;
- `empty_value` : valeur vide du champ.

Le registre V1 ne contient pas :

- de callback ;
- de HTML ;
- de requête ;
- de logique de builder ;
- de logique de collection ;
- de filtre public ;
- de mécanisme d’extension inter-plugin.

Ce document ne fixe pas de signature PHP ni de structure de fichier définitive.

## 10. Modèle de résolution

La priorité conceptuelle est :

1. ID explicite fourni :
   - s’il est valide, compatible et accessible, il est utilisé ;
   - s’il est invalide, inexistant, incompatible ou inaccessible, le résolveur retourne immédiatement la valeur vide du champ et arrête la résolution ;
2. sinon, ID courant fourni par le provider ;
3. sinon, contenu WordPress courant compatible ;
4. sinon, valeur vide déclarée pour le champ.

Un ID explicite fourni est prioritaire et autoritaire. S’il ne peut pas être résolu, le résolveur ne poursuit pas avec l’ID du contexte du provider, le contenu WordPress courant ou un autre contenu global. Cette règle évite d’afficher silencieusement les données d’un autre contenu que celui demandé.

Le résolveur :

- vérifie que l’identifiant du champ existe dans le registre ;
- détermine le module et le CPT compatibles ;
- résout le contenu concerné ;
- appelle uniquement la fonction Content Data API correspondante ;
- extrait la clé définie par `data_key` ;
- retourne la valeur selon le type du champ.

La résolution respecte les règles d’accès de la Content Data API. Elle ne contourne pas les règles applicables aux brouillons ou aux contenus non publiés.

Trois états doivent rester distincts :

- champ inconnu du registre ;
- champ connu dont la donnée métier est vide ;
- champ connu sans contexte compatible.

Pour les deux derniers états, la valeur vide déclarée est retournée. Le type technique exact du résultat d’un champ inconnu sera arbitré pendant l’implémentation et n’est pas figé ici.

## 11. Contexte commun

Le contexte commun est indépendant des builders.

Il reçoit conceptuellement :

- un ID explicite éventuel ;
- un ID courant éventuel ;
- éventuellement un type de contenu annoncé par le provider.

Il ne connaît aucun objet propre à Gutenberg, Divi, Spectra ou Elementor.

Chaque provider traduit son contexte vers cette forme minimale. Le résolveur vérifie ensuite la compatibilité réelle du contenu avec le champ demandé.

Le résolveur ne recherche jamais arbitrairement la première Citation ou le premier Témoignage disponible.

## 12. Dynamic Data et collections

Dynamic Data répond à la question : « Quelle est la valeur de ce champ pour ce contenu ? »

Une collection répond à la question : « Quels contenus faut-il sélectionner et parcourir ? »

Ces responsabilités restent séparées.

Une boucle Gutenberg ou Divi pourra fournir un ID courant au provider. Cependant :

- le registre ne crée pas la requête ;
- le résolveur ne choisit pas les contenus ;
- le provider ne filtre ni ne trie la collection ;
- aucune logique métier n’est recalculée dans la boucle.

`display_order` reste une simple valeur du contenu courant.

## 13. Placeholders et Dynamic Data

Les placeholders WP Seed servent les Templates WP Seed.

Ils peuvent :

- définir des types de rendu ;
- appliquer une projection de présentation ;
- produire du HTML, comme `{{photo}}`.

Dynamic Data sert les constructeurs et les thèmes.

Il :

- retourne des valeurs brutes ou structurées ;
- ne produit pas de HTML ;
- expose la photo comme un seul objet média.

Les deux mécanismes partagent le vocabulaire métier lorsque cela reste pertinent :

- `quote` ;
- `author` ;
- `era` ;
- `source` ;
- `text` ;
- `name` ;
- `context`.

La V1 ne crée pas :

- `testimonial.photo_url` ;
- `testimonial.photo_alt` ;
- `testimonial.photo_html`.

Ces projections pourront appartenir à un provider si un besoin concret le justifie. Elles ne deviennent pas des champs métier du registre.

## 14. Providers

Les providers sont des lots ultérieurs et indépendants.

### 14.1 Gutenberg

Un futur provider pourra utiliser Block Bindings.

Orientations :

- source serveur possible à partir de WordPress 6.5 ;
- inscription et découverte côté éditeur à étudier à partir de WordPress 6.7 ;
- blocs Core compatibles seulement dans la première version ;
- aperçu dans l’éditeur traité séparément ;
- aucun bloc propriétaire prévu en V1.

La version minimale globale de WordPress pour Dynamic Data n’est pas définie par ce document. Le registre et le résolveur internes ne dépendront pas de Block Bindings.

### 14.2 Divi 5

Un futur provider Divi devra faire l’objet d’un audit séparé fondé sur les API tierces réellement stables.

Deux workflows doivent pouvoir coexister :

- Template WP Seed utilisant un Layout Divi Library et des placeholders ;
- construction Divi utilisant des données dynamiques WP Seed.

Le futur provider ne supprimera ni ne remplacera le workflow Layout Divi Library actuel.

### 14.3 Spectra

Aucune compatibilité automatique n’est promise. La compatibilité devra être vérifiée bloc par bloc selon le support réel de Block Bindings.

### 14.4 Elementor et autres builders

Ils sont reportés. La V1 n’anticipe aucune abstraction universelle de provider.

## 15. Hors périmètre

Sont explicitement hors périmètre de ce contrat et de son premier lot d’implémentation :

- providers Gutenberg, Divi, Spectra ou Elementor ;
- implémentation de Block Bindings ;
- blocs ou modules custom ;
- collections ;
- requêtes ;
- boucles ;
- interface utilisateur ;
- HTML ;
- modification des shortcodes ;
- modification des templates ;
- nouveaux placeholders ;
- registre inter-plugin ;
- filtres publics ;
- modification de la Content Data API ;
- modification des CPT ;
- changement de version ;
- release.

## 16. Trajectoire d’implémentation

La trajectoire prévue est :

1. contrat documentaire Dynamic Data ;
2. registre interne descriptif ;
3. résolveur commun typé ;
4. chargement global du registre et du résolveur dans le bootstrap ;
5. tests directs du registre et du résolveur ;
6. provider Gutenberg dans un lot séparé ;
7. audit puis provider Divi dans un lot séparé.

Les étapes 2 à 5 constituent le premier lot PHP. Le registre et le résolveur sont chargés après la Content Data API et avant tout provider, indépendamment de Gutenberg, Divi, Spectra, Elementor ou de tout autre builder. Leur chargement ne dépend ni de la présence ni de l’activation d’un provider.

Les providers restent hors de ce premier lot PHP.

Chaque étape doit rester indépendante, testable et réversible.

Les shortcodes, templates, placeholders, renderers et intégrations builders actuels ne doivent pas changer pendant l’introduction du registre et du résolveur.

## 17. Risques et garde-fous

### 17.1 Registre trop générique

Risque : transformer une liste de douze champs en framework abstrait.

Garde-fou : registre local, descriptif, sans callback ni extension publique en V1.

### 17.2 Identifiants figés trop tôt

Risque : rendre publics des noms insuffisamment validés.

Garde-fou : limiter la V1 aux identifiants consignés dans ce document et valider le contrat avant le code.

### 17.3 Duplication des données WordPress

Risque : proposer deux sources concurrentes pour le titre, le lien ou le statut.

Garde-fou : ne pas inscrire les données WordPress natives dans le registre V1.

### 17.4 Confusion autour de `featured`

Risque : confondre le booléen métier avec l’image mise en avant.

Garde-fou : libellé « Mise en avant », type `boolean` et documentation explicite.

### 17.5 Confusion avec les collections

Risque : utiliser `display_order` pour introduire requêtes et boucles dans le résolveur.

Garde-fou : le résolveur ne traite qu’un contenu déjà identifié.

### 17.6 HTML dans les données

Risque : reproduire les projections des placeholders, notamment `{{photo}}`.

Garde-fou : valeurs brutes ou structurées uniquement ; projection réservée au provider.

### 17.7 Contournement de la Content Data API

Risque : un provider relit les métadonnées pour aller plus vite.

Garde-fou : tous les providers passent par le résolveur commun.

### 17.8 Contexte courant absent

Risque : afficher une valeur d’un contenu arbitraire.

Garde-fou : retourner la valeur vide du champ sans recherche globale.

### 17.9 Médias différents selon les builders

Risque : imposer une représentation propre à un constructeur.

Garde-fou : objet média commun puis projection dans chaque provider.

### 17.10 API Divi mouvante

Risque : dépendre d’un point d’extension non stable.

Garde-fou : audit Divi séparé, intégration défensive et aucun couplage dans le registre.

### 17.11 Compatibilité Spectra surestimée

Risque : annoncer une compatibilité générale sur la seule base de Gutenberg.

Garde-fou : vérification bloc par bloc et aucune promesse automatique.

### 17.12 Effet framework central

Risque : transformer Content Kit en registre de tout l’écosystème WP Seed.

Garde-fou : registre limité aux données possédées aujourd’hui par Content Kit, sans mécanisme inter-plugin.

## 18. Règle d’architecture

Le registre décrit les données.

Le résolveur les fournit.

Le provider les adapte.

Le builder les affiche.

Aucune de ces couches ne doit absorber la responsabilité de la couche précédente ou suivante.

## 19. Invariants de compatibilité

L’implémentation future devra garantir :

- Content Data API inchangée dans le premier lot ;
- shortcodes publics inchangés ;
- templates et placeholders inchangés ;
- renderers publics inchangés ;
- CPT et visibilité inchangés ;
- aucun builder obligatoire ;
- aucun HTML produit par le registre ou le résolveur ;
- aucune requête de collection ;
- aucune lecture directe des métadonnées par les providers ;
- aucun registre global WP Seed.

## 20. Points reportés

Les décisions suivantes sont reportées aux lots concernés :

- signature PHP exacte du registre ;
- signature PHP exacte du résolveur ;
- résultat technique d’un champ inconnu ;
- organisation définitive des fichiers ;
- filtres ou contrats publics éventuels ;
- version minimale WordPress du provider Gutenberg ;
- interface de découverte Gutenberg ;
- projection précise des médias par builder ;
- champs effectivement visibles dans chaque provider ;
- API Divi 5 retenue ;
- compatibilité de chaque bloc Spectra ;
- Elementor et autres builders.

## 21. Règle de lecture

Ce document fixe le contrat conceptuel Dynamic Data V1 avant son implémentation.

En cas de contradiction entre une future proposition technique et ce document, le contrat doit être réexaminé explicitement. Une contrainte de builder ne doit pas modifier silencieusement le sens des données métier.
