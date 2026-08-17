# Usage - WP Seed Content Kit

Statut : WP Seed Content Kit 0.8.0-rc.2. Cette Release Candidate est en validation et n'est pas une version stable.

WP Seed Content Kit fournit des contenus éditoriaux structurés, des shortcodes et des templates réutilisables dans WordPress.

Il fonctionne avec l'éditeur WordPress, Gutenberg, Spectra, Astra, Divi et les emplacements acceptant les shortcodes WordPress. ACF n'est pas requis.

## Administration

Le menu administrateur cible est Configuration, Témoignages, Citations, Annuaire et Utilisation. Utilisation propose quatre onglets : Fonctionnement, Templates, Collections et Intégrer dans une page. Ce dernier distingue Shortcodes, Gutenberg, Spectra et Divi.

Configuration conserve les réglages de modules et d’emplacement des menus, puis permet d’autoriser ou non Editor pour chaque module. Administrator garde tous les droits. Editor gère uniquement les contenus autorisés et ne voit ni Configuration, ni Utilisation, ni Templates, ni Collections ou outils techniques. La visibilité d’un menu ne constitue jamais une autorisation : les capacités WordPress réelles font foi.

Une Collection définit quels contenus afficher et dans quel ordre. Elle n’est pas enregistrée : il n’existe ni CPT Collection, ni écran de création, ni sauvegarde de Collection. Les générateurs produisent seulement des shortcodes à copier.
## Shortcodes disponibles

Les shortcodes publics sont :

- [seed_cards] ;
- [seed_testimonials] ;
- [seed_quotes] ;
- [seed_directory].

[wp_seed_directory] reste un alias temporaire deprecie et utilise exactement le meme callback, sans avertissement public.

## API PHP Collections V1

Collections V1 sélectionne des contenus publiés dont `post_password` est exactement vide et retourne uniquement leurs IDs. Elle ne produit aucun HTML. Le shortcode Témoignages et le mode Citation quotidienne l'utilisent comme couche de sélection ; les renderers et Templates restent responsables du HTML.

La collection Témoignages est disponible avec :

```text
wp_seed_content_get_testimonials($args = array())
```

Arguments canoniques et valeurs par défaut :

- `ids=array()` : sélection manuelle autoritaire par tableau d'entiers positifs ;
- `featured=all` : `all`, `only` ou `exclude` ;
- `limit=0` : tous les résultats éligibles ;
- `orderby=display_order` : `display_order`, `date`, `testimonial_date` ou `id` ;
- `order=asc` : `asc` ou `desc`.

Une sélection `ids` non vide conserve l'ordre fourni, retire doublons, contenus non publiés et publications protégées par mot de passe, ignore les autres filtres et ne cherche jamais de remplaçant. Un ID explicite ne contourne jamais cette protection. Le module Témoignages désactivé retourne toujours un tableau vide.

La Citation quotidienne est disponible avec :

```text
wp_seed_content_get_daily_quote($args = array())
```

Elle retourne un ID de Citation publiée non protégée, stable pour la date civile et le fuseau WordPress du site, ou `0` si aucune Citation publique non protégée n'est éligible ou si le module est désactivé. Elle n'utilise ni hasard, ni transient, ni état persistant.

`[seed_testimonials]` conserve ses valeurs par défaut historiques en les traduisant vers Collections. `[seed_quotes]` conserve son mode aléatoire historique et ajoute le mode quotidien explicite `mode="daily"`.

## Annuaire - saisie CK-A3

Dans Annuaire, Editor et Administrator utilisent la même fiche en cinq panneaux. Saisir le nom dans « Nom affiché », classer si nécessaire le profil comme praticien et/ou intervenant, indiquer séparément une recherche actuelle de modèles, puis compléter librement localisation et présentation. La photo reste facultative ; si elle est choisie, son texte alternatif devient obligatoire avant publication.

Chaque coordonnée est une ligne répétable avec type, libellé facultatif, lien complet, visibilité publique et ordre. Une personne peut posséder plusieurs lignes du même type. Le lien utilise directement `tel:`, `mailto:` ou HTTP(S) selon le type ; Adresse reste une valeur texte sans href. Le libellé facultatif remplace le lien comme texte affiché. Une valeur peut rester enregistrée en privé, même dans un brouillon incomplet. Cocher sa visibilité exige un lien publiable valide. L’autorisation « La personne a autorisé la publication de ses informations » est obligatoire mais ne rend aucune coordonnée publique automatiquement.

Sur une fiche déjà publiée et jusque-là valide, la première sauvegarde d’une nouvelle erreur de format corrigible conserve temporairement la publication et affiche un avertissement persistant dans l’éditeur. Corrigez-la avant la sauvegarde suivante : le même état invalide enregistré une seconde fois place la fiche en brouillon. Une autre valeur invalide ouvre un nouveau cycle de correction. Le retrait d’autorisation reste immédiat, et une fiche en brouillon ne peut jamais être publiée avec une erreur.

Administrator configure les types dans Configuration → Annuaire — Types de coordonnées. Téléphone et E-mail sont présents par défaut ; les types système ont un slug et un comportement protégés mais restent désactivables. Les types ajoutés utilisent un slug immuable, un comportement sûr fourni par Content Kit et un ordre. Désactiver un type conserve ses lignes et ses anciens bindings, mais le retire des nouvelles options Divi et du rendu public. Un type personnalisé inutilisé peut être supprimé après confirmation ; toute ligne canonique existante, même privée ou invalide, bloque cette suppression côté serveur. Les icônes et tout leur design appartiennent exclusivement au builder.

`Lien web` accepte les URL HTTP(S) valides sans imposer de domaine et rejette tout autre schéma. Site internet, Facebook et Instagram conservent leurs types, providers et conditions distincts, mais utilisent ce comportement générique : le contrat d'affichage reste `libellé facultatif, sinon lien lisible`. Ils ne sont pas marqués `Système` : ils peuvent être désactivés, et supprimés seulement en l'absence de toute coordonnée canonique. Leurs IDs publiés restent réservés indépendamment de cette protection administrative. Téléphone et E-mail sont les seuls types protégés. Recréer le même slug restaure les mêmes IDs déterministes ; vérifier les anciens bindings avant de le faire.

Les registres Types de profil et Statuts appliquent la même sécurité : une valeur système n'est jamais supprimable, une valeur personnalisée inutilisée peut être supprimée après confirmation, et toute fiche qui l'utilise bloque l'opération tout en laissant la désactivation disponible. Les providers individuels, la Content Data API et les Block Bindings Gutenberg consomment directement les coordonnées canoniques. Aucun profil d'affichage ni renderer composite n'intervient : le builder choisit chaque champ, son ordre et sa présentation.

La liste propose un filtre administratif de statut. Quick Edit et la publication en masse sont neutralisés. Editor peut publier, dépublier, modifier les personnes d’autres éditeurs, mettre à la corbeille et restaurer, sans voir Configuration, Utilisation, Templates ou Collections.

## Annuaire L4

[seed_directory] affiche les fiches eligibles dans deux groupes fixes. Attributs : status=all|practicing|seeking_models, department, country, featured=all|only|exclude, limit, orderby=display_order|name|date|id, order=asc|desc, ids et template.

Exemples : [seed_directory], [seed_directory status="practicing" department="75" featured="only"] et [seed_directory ids="12,18" template="annuaire-carte"].

Les valeurs invalides produisent une sortie vide. Il n'existe aucun parametre GET, formulaire ou filtre visible. Les groupes vides sont omis. Sans fiche, le message public est stable.

Sans template, une carte native est rendue. Un Template publie du module Annuaire peut utiliser vingt-cinq placeholders directory.*. Un echec de template produit un fallback natif fiche par fiche ; les autres cartes restent personnalisees. Les contacts masques ou invalides sont absents de la Data API, du contexte, des placeholders et du HTML.

Gutenberg utilise le bloc Shortcode. Divi 5 propose le module natif « WP Seed — Témoignages » ; Texte ou Code restent supportés pour les shortcodes historiques, et un Layout Divi Library peut servir de source à un Template. Aucun bloc Gutenberg dédié n’est fourni. Désactiver Annuaire rend ses shortcodes et Collections vides, sans supprimer les données.

## Cards

`[seed_cards]` affiche des articles WordPress natifs sous forme de cartes.

Exemples :

```text
[seed_cards]
[seed_cards category="inspirations" limit="3" columns="3"]
[seed_cards tag="actualites" orderby="title" order="ASC"]
[seed_cards show_image="false" show_excerpt="false"]
[seed_cards button_label="Lire la suite"]
```

Attributs disponibles :

- `limit` ;
- `columns` ;
- `category` ;
- `tag` ;
- `orderby` : `date` ou `title` ;
- `order` : `ASC` ou `DESC` ;
- `show_image` ;
- `show_category` ;
- `show_date` ;
- `show_title` ;
- `show_excerpt` ;
- `show_button` ;
- `button_label`.

Cards n'ajoute pas de type de contenu métier. Il utilise les articles WordPress publiés.

## Témoignages

Le stockage portable canonique associe le titre à `post_title`, le résumé à `post_excerpt`, le visuel à l’image mise en avant, puis le texte complet, le nom et le contexte à `seed_testimonial_text`, `seed_testimonial_name` et `seed_testimonial_context`. La date métier est optionnelle et reste vide lorsqu’elle est inconnue.

Les anciennes métas `_seed_testimonial_text`, `_seed_testimonial_name` et `_seed_testimonial_context` ne sont que des fallbacks backward compatibility. Elles ne remplacent jamais une méta publique présente.

`[seed_testimonials]` affiche les témoignages publiés.

Exemples :

```text
[seed_testimonials]
[seed_testimonials limit="0" orderby="display_order" order="asc"]
[seed_testimonials featured="only" limit="3" orderby="date" order="desc"]
[seed_testimonials limit="3" orderby="testimonial_date" order="desc"]
[seed_testimonials ids="12,18,27"]
[seed_testimonials template="accueil"]
[seed_testimonials context="workshop"]
```

Attributs disponibles :

- `ids` : liste CSV d'IDs, ordonnée et sans doublons après normalisation ;
- `limit` : trois par défaut, `0` pour tous, plafond de 24 pour une valeur positive ;
- `columns` : de 1 à 4 ;
- `featured` : `all`, `only`, `exclude`, avec `true` et `false` comme alias historiques ;
- `context` : filtre historique exact lorsqu'aucune sélection `ids` n'est active ; une valeur absente, vide ou égale à `"0"` n'active aucun filtre ;
- `orderby` : `display_order`, `date`, `testimonial_date` ou `id` ; `menu_order` reste un alias ;
- `order` : `asc` ou `desc` ;
- `template`.

`ids` absent ou vide conserve le mode normal. Une liste non vide entièrement invalide affiche l'état vide sans fallback. Dans une liste mixte, les jetons valides sont conservés. Le mode `ids` ignore `featured`, `orderby`, `order` et `context` ; `limit` reste appliqué après nettoyage.

Pour une page « Tous les Témoignages », utiliser un tri explicite :

```text
[seed_testimonials limit="0" orderby="display_order" order="asc"]
```

Champs d'édition actuels :

- titre WordPress ;
- nom ou initiales ;
- témoignage ;
- photo ;
- date du témoignage ;
- Information complémentaire ;
- mis en avant ;
- ordre d'affichage WordPress.

La date du témoignage est facultative, indépendante de la date d'ajout dans WordPress et stockée au format civil strict `YYYY-MM-DD`. Une date impossible n'est pas enregistrée et ne remplace pas une ancienne valeur. Laisser volontairement le champ vide puis enregistrer supprime la date stockée, même si une ancienne valeur était invalide. La date reste brute dans Content Data et Dynamic Data ; seuls les rendus de présentation la localisent selon les réglages WordPress, sans changer le jour métier.

Le champ Information complémentaire conserve l'identifiant métier `testimonial.context`. Son stockage canonique est `seed_testimonial_context` ; `_seed_testimonial_context` reste uniquement un fallback historique. Le consentement de publication reste un contrat séparé.

## Citations

`[seed_quotes]` affiche par défaut une citation publiée dans un ordre aléatoire.

Exemples :

```text
[seed_quotes]
[seed_quotes orderby="random"]
[seed_quotes limit="3"]
[seed_quotes limit="0"]
[seed_quotes featured="true" limit="1" orderby="random"]
[seed_quotes orderby="author" order="ASC"]
[seed_quotes orderby="menu_order" order="ASC" limit="0"]
[seed_quotes mode="daily"]
[seed_quotes mode="daily" template="citations-accueil"]
```

Attributs disponibles :

- `mode` : vide pour le comportement historique, `daily` pour la Citation quotidienne déterministe ;
- `limit` : absent pour le comportement par défaut, `0` pour toutes les citations, valeur positive pour une limite maximale ;
- `featured` : `true` pour limiter aux citations mises en avant ;
- `template` ;
- `orderby` : `random`, `author`, `date` ou `menu_order` ;
- `order` : `ASC` ou `DESC`, sans effet sur l'ordre aléatoire.

Le mode `daily` appelle Collections, rend une seule Citation et n'utilise jamais `ORDER BY RAND()`. Il ignore `limit`, `featured`, `orderby` et `order`, mais conserve `template`. La valeur reste déterministe pendant la date civile WordPress tant que la liste éligible ne change pas ; un cache de page peut prolonger l'ancien HTML au-delà de minuit.

Champs d'édition actuels :

- citation ;
- auteur facultatif ;
- époque ou date affichée facultative ;
- source ou contexte facultatif ;
- mis en avant ;
- ordre d'affichage WordPress.

Le titre WordPress est généré à partir de la citation et sert à l'identification dans l'administration.

## API publique d'extension des Templates

Un plugin tiers peut enregistrer un module et ses placeholders pendant `wp_seed_content_kit_register_template_modules`, puis appeler :

```php
$result = wp_seed_content_kit_render_template($slug, $module, $public_context);
```

Le résultat est toujours un `WP_Seed_Content_Kit_Render_Result`. En cas d'échec, son HTML et ses assets sont vides ; l'appelant utilise son propre renderer de secours. Les signatures, types, codes d'erreur et exemples sont définis dans `docs/TEMPLATE-EXTENSION-API.md`.

## Templates WP Seed

Les templates permettent de choisir la mise en forme de chaque élément affiché par un shortcode.

Un template possède :

- un module associé : Témoignages ou Citations ;
- un identifiant utilisé par l'attribut `template` ;
- une source du rendu ;
- un contenu et des placeholders.

Exemples :

```text
[seed_testimonials ids="12,18,27" template="accueil"]
[seed_quotes template="citations-accueil"]
[seed_quotes mode="daily" template="citation-du-jour"]
```

### Contenu de ce template

Cette source utilise le contenu enregistré dans le template avec l'éditeur WordPress. Elle est compatible avec Gutenberg et Spectra.

### Layout Divi Library

Lorsque Divi est disponible, un template peut sélectionner un layout Divi Library publié comme source du rendu.

Le workflow est :

1. créer un layout dans Divi Library ;
2. ajouter les placeholders WP Seed dans un module Texte ou Code ;
3. sélectionner ce layout dans les réglages du template ;
4. utiliser le shortcode généré dans une page.

Le template WP Seed reste le point d'entrée. L'édition directe du CPT Template avec Divi n'est pas le workflow pris en charge.

Pour un Template Témoignages, les cinq champs Dynamic Content WP Seed peuvent aussi être placés dans les modules du Layout. Pendant le rendu de la Collection, Content Kit injecte l'ID du témoignage courant dans chaque variable autorisée, uniquement en mémoire et avant le parsing frontend, que le payload soit direct ou sérialisé dans un attribut de bloc. Les variables Citations, Annuaire et Divi natives restent inchangées.

L'éditeur isolé du Layout ne dispose d'aucun témoignage courant : les champs peuvent y être vides. Tester le résultat dans le shortcode, le module Divi ou la page qui fournit réellement la Collection. Aucun ID fictif n'est enregistré dans le Layout.

Si le layout est absent, invalide ou non publié, le contenu du template reste le fallback.

Si le slug d'un Template demandé est introuvable ou appartient à un autre module, le shortcode utilise le renderer natif. Aucun placeholder incompatible brut n'est affiché et ce fallback n'est pas étendu à d'autres sélections implicites.

## Intégration dans les constructeurs

Les shortcodes restent des adaptateurs publics de collection. Divi 5 peut aussi consommer directement les collections Citations et Témoignages dans une Native Loop ; WPSCK fournit alors la sélection et les données, tandis que Divi conserve la présentation.

### Divi

Dans un module Code ou Texte, insérer par exemple :

```text
[seed_testimonials featured="only" limit="3" template="accueil"]
[seed_quotes mode="daily" template="citation-du-jour"]
```

Un Template peut utiliser un Layout Divi Library pour la mise en forme de chaque élément. Pour Témoignages, le module Divi 5 natif fournit désormais la sélection de Collection et l’aperçu sans shortcode ; les intégrations historiques restent compatibles.

### Gutenberg

Utiliser un bloc Shortcode. Le rendu serveur prend en charge les mêmes attributs. Un Pattern peut contenir ce bloc. Query Loop Core et Block Bindings restent un parcours distinct pour les requêtes simples et ne remplacent pas le contrat Collections.

### Spectra

Utiliser un bloc Shortcode Core dans une page ou un Container Spectra, ou un Template WP Seed composé avec des blocs Gutenberg/Spectra. Aucun provider Spectra n'est annoncé et la lecture directe des métadonnées n'est pas le contrat recommandé.

## Placeholders

### Témoignages

- `{{photo}}` : balise image complète ;
- `{{photo_url}}` : URL de la photo ;
- `{{photo_alt}}` : texte alternatif de la photo ;
- `{{name}}` : nom ou initiales ;
- `{{text}}` : texte du témoignage ;
- `{{context}}` : Information complémentaire ;
- `{{date}}` : date du témoignage localisée pour la présentation.

### Citations

- `{{quote}}` : citation ;
- `{{author}}` : auteur ;
- `{{era}}` : époque ou date affichée ;
- `{{source}}` : source ou contexte.

Les placeholders doivent être utilisés dans le contenu du template ou dans un module Texte ou Code du layout Divi.

## Dynamic Data

Dynamic Data fournit des champs normalisés aux intégrations Gutenberg et Divi. La valeur dépend toujours d'un contenu WP Seed explicite ou du contexte WordPress courant. Un contenu d'un mauvais type, absent, non publié ou inaccessible produit une valeur vide ; aucune Citation ni aucun Témoignage arbitraire n'est recherché en fallback.

Les Templates WP Seed restent un workflow complémentaire. Ils continuent d'utiliser leurs placeholders officiels, y compris dans les modules Texte ou Code d'un layout Divi Library.

## Gutenberg Block Bindings

Le provider serveur Gutenberg expose les champs texte suivants :

- `quote.quote` ;
- `quote.author` ;
- `quote.era` ;
- `quote.source` ;
- `testimonial.text` ;
- `testimonial.name` ;
- `testimonial.context` ;
- `testimonial.testimonial_date`.

Il prend en charge uniquement l'attribut `content` des blocs Core Paragraphe et Titre. Une Query Loop fournit le contexte de chaque contenu au rendu serveur.

L'interface éditeur native WP Seed reste différée. Aucun sélecteur WP Seed finalisé n'est actuellement fourni dans Gutenberg ; les bindings doivent être créés par un markup contrôlé ou une intégration technique.

## Divi 5 Dynamic Content expérimental

Le provider Dynamic Content de Divi 5 enregistre côté serveur les sources métier.

WPSCK — Citations :

- Texte ;
- Auteur ;
- Époque ;
- Source.

Ces quatre providers résolvent les métas publiques canoniques, avec fallback historique seulement lorsque la clé publique n'existe pas. Ils fonctionnent dans une Native Loop `seed_quote`, y compris sur un Group répété dans un Group Carousel natif. Aucun rendu ou Carousel propriétaire n'est ajouté par WPSCK.

Pour les Native Loops, le groupe « WPSCK — Témoignages » expose :

- Visuel ;
- Titre ;
- Résumé ;
- Témoignage complet ;
- Nom ;
- Contexte ;
- Date ;
- ID ;
- Ancre.

Aucun shortcode ni identifiant fixe n'est nécessaire sur un témoignage courant individuel. Dans un Template Content Kit utilisant un Layout Divi, Content Kit fournit explicitement le contexte de la carte. Une page ordinaire ou un mauvais type de contenu produit une valeur vide sans fallback arbitraire.

Dans un Layout utilisé comme Template de collection, le contexte explicite par carte est injecté en mémoire avant le parsing Divi. Dans une Native Loop, y compris sur le Group/slide d’un Group Carousel natif, les providers résolvent l’item imbriqué via `loop_id`, puis `loop_object`. Le Layout enregistré reste inchangé et WPSCK ne fournit aucun Carousel propriétaire.

Une erreur de parsing, un contexte invalide, un Layout indisponible, une résolution dynamique incomplète ou un rendu vide ne remplace pas toute la Collection : la carte concernée reprend son rendu natif et la pile de contexte, limitée à 16 niveaux, est restaurée avant la carte suivante. Un Layout statique non vide reste accepté.

La source Date retourne la valeur métier canonique lorsqu’elle existe et une chaîne vide sinon, sans fallback sur `post_date`. Les Templates utilisent `{{date}}` pour une présentation localisée.

Photo est prioritairement compatible avec la propriété source du module Image. Divi peut reconstruire l'ID média, les dimensions, `srcset` et `sizes` à partir d'une URL locale. Le texte alternatif n'est pas garanti dans tous les modules.

Ce provider cible Divi 5. Divi 4 n'est pas pris en charge. Le module `WP Seed — Témoignages`, les shortcodes et les layouts Divi Library avec placeholders restent officiellement pris en charge et complémentaires du Native Loop.

## Styles

Le CSS public utilise le préfixe `seed-`.

Le plugin ne réinitialise pas les styles globaux du site et n'impose ni thème ni constructeur de page.

## Limites actuelles

WP Seed Content Kit ne fournit pas :

-- de module Divi 4 ou de module Divi personnalisé pour Citations ;
- de widget Elementor ;
- de bloc Gutenberg personnalisé ;
- de sélecteur WP Seed finalisé dans l'éditeur Gutenberg ;
- d'intégration ACF obligatoire ;
- d'outil d'import ou de migration ;
- de recherche publique ni de fiche individuelle pour Annuaire ;
- de desinstallation destructive automatique des donnees ;
- de module fonctionnel Créations sonores.

## Comprendre Contenus, Collections, Templates et Intégrations

- **Contenus** : les Témoignages, Citations ou personnes de l’Annuaire.
- **Collections** : les paramètres qui choisissent quels contenus afficher et dans quel ordre.
- **Templates** : la présentation facultative de chaque contenu sélectionné.
- **Intégrations** : l’endroit où le shortcode et son résultat sont insérés.

Une Collection n’est jamais enregistrée et ne choisit aucun Template durablement. L’attribut template du shortcode relie temporairement sélection et présentation. Sans cet attribut, le rendu natif du module s’applique.

Pour Annuaire, le générateur couvre status, department, country, featured, ids, limit, orderby, order et template. Il produit le shortcode canonique [seed_directory]. L’alias [wp_seed_directory] est conservé uniquement pour compatibilité temporaire.

Le catalogue de Templates affiche sept placeholders Témoignages, quatre Citations et vingt-cinq Annuaire. Seules les données publiques sont proposées. Un contact Annuaire masqué, invalide ou non autorisé reste vide et n’est jamais transmis au Template.

### Intégrations

- **Shortcodes — Fonctionnel** : méthode canonique dans tout emplacement WordPress compatible.
- **Gutenberg — Fonctionnel** : bloc Shortcode Core et champs Annuaire publics compatibles avec les Block Bindings Core.
- **Spectra — Indirect** : bloc Shortcode Core dans une page ou un Container, ou blocs Spectra dans un Template ; aucun provider natif.
- **Divi — Fonctionnel/Indirect** : modules natifs Témoignages et Annuaire sous Divi 5, Native Loops avec providers WPSCK, shortcodes dans Texte ou Code pour compatibilité, ou Layout Divi Library comme source d’un Template.

Les filtres de Collection sont choisis par Administrator lors de l’intégration. Aucun champ de recherche ni filtre n’est présenté au visiteur.

## Migration fictive CK-A6

Le moteur CK-A6 n'est pas une fonctionnalite editoriale visible. Il est reserve aux recettes techniques Administrator et exige un appel PHP interne explicite avec `manage_wp_seed_imports`. Editor ne peut ni importer ni effectuer un rollback.

Le manifeste fourni contient exclusivement des noms, contacts et images de demonstration. Aucun import ne part a l'activation, au chargement, depuis un shortcode ou par REST/AJAX. Toute migration reelle requiert une autorisation et un lot distincts.

## Module Divi 5 « WP Seed — Témoignages »

Dans le Visual Builder, ajouter le module `WP Seed — Témoignages`, puis régler le titre facultatif, la sélection mis en avant, le contexte, les IDs explicites, la limite, le tri, l’ordre, le Template facultatif et les colonnes. Le module interroge la Collection canonique et affiche le rendu réel à chaque changement. Un Template publié du module Témoignages peut être choisi par Administrator ; un Template absent, brouillon ou incompatible déclenche le fallback natif.

Le shortcode `[seed_testimonials]` reste supporté pour les pages existantes. À paramètres identiques, son HTML de collection est produit par le même renderer. Editor conserve ses droits WordPress/Divi sur les pages et les Témoignages, sans accès à la gestion des Templates ni à la configuration globale Content Kit.

## Annuaire multi-usages

Le type décrit durablement la fonction publique de la personne. Le statut « Recherche de modèles » décrit une situation temporaire et ne remplace pas le type.

```text
Tous                 [seed_directory]
Praticiens           [seed_directory profile_type="praticien"]
Intervenants         [seed_directory profile_type="intervenant"]
Recherche            [seed_directory seeking_models="1"]
Praticiens + recherche    [seed_directory profile_type="praticien" seeking_models="1"]
Intervenants + recherche  [seed_directory profile_type="intervenant" seeking_models="1"]
```

Une fiche `praticien,intervenant` peut apparaître dans les deux pages. `profile_types="praticien,intervenant" profile_type_operator="and"` sélectionne uniquement les profils cumulant les deux types. L'opérateur `or` sélectionne au moins un type. `exclude_ids`, `offset`, `limit`, `orderby` et `order` s'appliquent après les règles d'éligibilité.

Les fiches historiques sans `_seed_directory_profile_types` restent dans `[seed_directory]`, mais sont absentes d'un filtre typé. Aucun type n'est attribué automatiquement.

Gutenberg et Spectra utilisent le shortcode canonique. Divi 5 utilise de préférence le module `WP Seed — Annuaire`; le shortcode et les Templates Content Kit fondés sur un Layout Divi Library restent compatibles. Le Loop Builder Annuaire n'est pas pris en charge.


## Présentation complète et visibilité Annuaire

Renseignez la présentation courte dans l’extrait et la présentation complète dans l’éditeur WordPress. Cochez « Afficher cette personne dans les annuaires publics » seulement lorsque la fiche doit apparaître dans les Collections. Cette case ne change ni le statut WordPress, ni le consentement, ni les coordonnées affichées.

Dans un Template Annuaire, utilisez `directory.summary` ou son alias `directory.bio` pour le résumé indépendant, et `directory.presentation` ou son alias historique `directory.full_presentation` pour la présentation complète. Un bloc More WordPress facultatif dans la présentation fournit aussi `directory.presentation_intro`, `directory.presentation_more` et `directory.has_more`. Sans bloc More, l’introduction reprend la présentation complète, la suite est vide et `has_more` vaut faux.

`directory.professional_label` fournit l'« Intitulé professionnel » facultatif, destiné à être affiché sous le nom. Il ne remplace ni le nom WordPress ni les types de profil utilisés pour filtrer les Collections. Divi l'expose avec le provider `WPSCK — Annuaire — Intitulé professionnel`; Gutenberg utilise le même identifiant avec la source Block Bindings commune.

Dans Gutenberg, insérez le bloc More à l’endroit voulu entre deux blocs de contenu. La forme code `<!--more-->` et sa variante avec libellé sont également reconnues. Placez toujours la coupure entre blocs ou paragraphes : WPSCK ne répare pas une balise HTML coupée. WPSCK retire les marqueurs techniques des valeurs publiques et ne crée aucune méta, aucun bouton ni aucun état d’ouverture.

Ces deux valeurs restent indépendantes de la présentation : WPSCK ne stocke aucun état « Lire la suite », Toggle ou Accordion. Si les deux valeurs existent et diffèrent, le builder peut afficher le résumé puis révéler la présentation complète. Si le résumé est vide, il peut afficher directement la présentation. Si les deux valeurs sont identiques, ne les affichez pas simultanément.

- **Divi** : connecter `WPSCK — Annuaire — Introduction` et `WPSCK — Annuaire — Suite de présentation` à des modules natifs, avec un Toggle ou un Accordion si un contenu repliable est souhaité. Le provider `Présentation` fournit toujours le contenu complet. Divi 5.9 ne reçoit pas de faux provider booléen `has_more`; la condition reste un contrat de données générique.
- **Gutenberg** : utiliser la source Block Bindings `wp-seed-content-kit/dynamic-data` avec `directory.presentation_intro` et `directory.presentation_more` sur des blocs Core texte compatibles. Un bloc Details Core peut fournir le disclosure si la version WordPress ciblée le propose.
- **Spectra / Astra** : utiliser les mêmes données via les blocs Core, les Templates ou un adaptateur compatible. WPSCK ne charge ni Spectra ni Astra et ne promet pas de provider propriétaire.

Pour l’accessibilité, préférer `<details>/<summary>` ou un vrai bouton pilotant `aria-expanded`, utilisable au clavier. Ne pas simuler l’ouverture avec un lien sans état ni ajouter de JavaScript WPSCK lorsque le builder sait déjà gérer ce composant.

## Module Divi 5 « WP Seed — Annuaire »

Ajouter le module dans le Visual Builder, puis choisir :

- tous les profils ou un statut ;
- Praticien, Intervenant ou les deux ;
- la relation OR ou AND ;
- Recherche de modèles ;
- les filtres facultatifs de localisation et mise en avant ;
- les IDs inclus ou exclus ;
- limite, offset, tri et ordre ;
- rendu natif ou Template Annuaire publié.

Le module affiche immédiatement le rendu public réel dans le canevas. Le changement d’un filtre, de l’ordre, de la limite ou du Template relance une requête serveur annulable ; aucun shortcode n’est injecté dans le navigateur.

La route d’aperçu est privée : session WordPress, nonce REST et `edit_pages` sont obligatoires. Editor peut prévisualiser la Collection mais ne reçoit pas le catalogue de Templates réservé à `manage_wp_seed_templates`.

Le Builder n’assouplit aucune règle publique. Une fiche non listée ou non publiable reste absente, y compris avec un ID explicite. Les coordonnées masquées et données internes ne sont jamais envoyées.

Le shortcode `[seed_directory]` reste pris en charge. À réglages identiques, le shortcode, le frontend du module et l’aperçu utilisent le même renderer. Le fallback d’une carte en erreur reste local.

Le module nécessite Divi 5. Sans Divi, aucune route ni module n’est enregistré. Le Loop Builder natif Divi reste non pris en charge.

## Combiner `ids` et `exclude_ids` dans l'Annuaire

`ids` restreint la population et `exclude_ids` retire toujours les fiches correspondantes, y compris lorsqu'elles figurent dans `ids`.

```text
[seed_directory ids="12,18,27" exclude_ids="18"]
```

La Collection conserve ici les fiches 12 et 27 si elles sont publiées, listées et éligibles. Elle applique ensuite les filtres métier, `orderby`/`order`, `offset`, puis `limit`. Si toutes les fiches sont exclues, l'état vide est rendu. L'ordre CSV des IDs ne remplace pas le tri canonique.

## Native Divi Loop — Témoignages

Dans Divi 5, activer la boucle sur le type Témoignages. La requête est automatiquement bornée par la Collection publique Content Kit. Pour choisir la population, ajouter au besoin une Meta Query virtuelle sur `wp_seed_content_testimonial_selection_mode` avec `all`, `featured`, `random` ou `featured_or_random`. Le nombre de posts de la boucle constitue la limite.

Le groupe unique « WPSCK — Témoignages » expose exactement Visuel, Titre, Résumé, Témoignage complet, Nom, Contexte, Date, ID et Ancre. La date est vide lorsqu’aucune date métier n’est connue. Les neuf valeurs sont résolues par item dans le frontend et le Visual Builder. Le design reste entièrement dans Divi.

Pour un Group Carousel, utiliser le module natif Divi et placer la Loop sur le Group qui représente la slide. Les providers conservent le contexte de chaque clone via `loop_id` ou `loop_object`. WPSCK ne fournit ni module Carousel, ni structure, ni style de présentation Carousel.

Le frontend aléatoire varie réellement. Le Visual Builder conserve un échantillon stable. Le module « WP Seed — Témoignages », les shortcodes et les Templates existants restent disponibles comme fallback.

Divi 5.9.0 ne sait pas appliquer l’ordre des deux colonnes internes selon la parité du clone Loop parent : ses Grid Offset Rules produisent uniquement des sélecteurs sur les enfants directs du conteneur courant. Le contrat structurel partagé permet une alternance opt-in sans stocker de présentation dans les données.

Pour une Row directement bouclée, ajouter `wpsck-loop--alternating` à la Row, `wpsck-loop__media` à la colonne média et `wpsck-loop__content` à la colonne contenu. Pour une Loop portée par un Group, ajouter `wpsck-loop--alternating` au Group, `wpsck-loop__layout` à sa Row interne, puis les mêmes classes média et contenu aux colonnes. Le CSS inverse seulement les colonnes paires sur desktop et restaure toujours média puis contenu à 980 px et moins. La parité compte uniquement les vrais clones portant la classe d'alternance, afin que les frères d'overlay du Visual Builder ne changent jamais l'ordre. Les anciennes classes `wpsck-testimonial-loop--*` restent compatibles.

Le contrat de données est builder-agnostic : WPSCK fournit données, requête, consentement et providers ; Divi fournit présentation et responsive. Les mêmes métas publiques restent disponibles pour Gutenberg/custom-fields et de futurs adaptateurs Spectra/Astra, sans stockage spécifique à Divi.
