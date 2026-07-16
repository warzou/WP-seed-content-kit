# Usage - WP Seed Content Kit

WP Seed Content Kit fournit des contenus éditoriaux structurés, des shortcodes et des templates réutilisables dans WordPress.

Il fonctionne avec l'éditeur WordPress, Gutenberg, Spectra, Astra, Divi et les emplacements acceptant les shortcodes WordPress. ACF n'est pas requis.

## Shortcodes disponibles

Les shortcodes publics sont :

- `[seed_cards]` ;
- `[seed_testimonials]` ;
- `[seed_quotes]`.

## API PHP Collections V1

Collections V1 sélectionne des contenus publiés dont `post_password` est exactement vide et retourne uniquement leurs IDs. Elle ne produit aucun HTML et ne modifie pas encore les requêtes des shortcodes publics.

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

Les shortcodes `[seed_testimonials]` et `[seed_quotes]` conservent dans ce lot leurs paramètres, valeurs par défaut et requêtes historiques.

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

`[seed_testimonials]` affiche les témoignages publiés.

Exemples :

```text
[seed_testimonials]
[seed_testimonials limit="3" columns="3"]
[seed_testimonials featured="true"]
[seed_testimonials orderby="menu_order" order="ASC"]
[seed_testimonials template="accueil"]
[seed_testimonials context="workshop"]
```

Attributs disponibles :

- `limit` ;
- `columns` ;
- `featured` : `all`, `true` ou `false` ;
- `context` ;
- `orderby` : `date` ou `menu_order` ;
- `order` : `ASC` ou `DESC` ;
- `template`.

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

Le champ Information complémentaire conserve l'identifiant technique historique `context` et la méta `_seed_testimonial_context`. Le consentement de publication n'est pas réintroduit.

## Citations

`[seed_quotes]` affiche par défaut une citation publiée dans un ordre aléatoire.

Exemples :

```text
[seed_quotes]
[seed_quotes limit="3"]
[seed_quotes limit="0"]
[seed_quotes featured="true" limit="1" orderby="random"]
[seed_quotes orderby="author" order="ASC"]
[seed_quotes orderby="menu_order" order="ASC" limit="0"]
[seed_quotes template="citations-accueil"]
```

Attributs disponibles :

- `limit` : absent pour le comportement par défaut, `0` pour toutes les citations, valeur positive pour une limite maximale ;
- `featured` : `true` pour limiter aux citations mises en avant ;
- `template` ;
- `orderby` : `random`, `author`, `date` ou `menu_order` ;
- `order` : `ASC` ou `DESC`, sans effet sur l'ordre aléatoire.

Champs d'édition actuels :

- citation ;
- auteur facultatif ;
- époque ou date affichée facultative ;
- source ou contexte facultatif ;
- mis en avant ;
- ordre d'affichage WordPress.

Le titre WordPress est généré à partir de la citation et sert à l'identification dans l'administration.

## Templates WP Seed

Les templates permettent de choisir la mise en forme de chaque élément affiché par un shortcode.

Un template possède :

- un module associé : Témoignages ou Citations ;
- un identifiant utilisé par l'attribut `template` ;
- une source du rendu ;
- un contenu et des placeholders.

Exemples :

```text
[seed_testimonials template="accueil"]
[seed_quotes template="citations-accueil"]
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

Si le layout est absent, invalide ou non publié, le contenu du template reste le fallback.

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

Le provider Dynamic Content de Divi 5 enregistre côté serveur les sources regroupées ainsi :

WP Seed — Citations :

- Texte ;
- Auteur ;
- Époque ;
- Source.

WP Seed — Témoignages :

- Texte ;
- Nom ;
- Information complémentaire ;
- Date du témoignage ;
- Photo.

Aucun shortcode ni identifiant fixe n'est nécessaire. Les valeurs utilisent le contenu courant ou l'élément courant d'une boucle. Une page ordinaire, un mauvais type de contenu ou une boucle incompatible produit une valeur vide sans fallback arbitraire.

La source Date du témoignage retourne la valeur ISO canonique. Sa sélection et sa persistance visuelles doivent être validées séparément dans Divi ; les Templates utilisent `{{date}}` pour une présentation localisée.

Photo est prioritairement compatible avec la propriété source du module Image. Divi peut reconstruire l'ID média, les dimensions, `srcset` et `sizes` à partir d'une URL locale. L'aperçu du Visual Builder peut rester vide et le texte alternatif n'est pas garanti dans tous les modules ou contextes de boucle.

Ce provider cible Divi 5. Divi 4 n'est pas pris en charge. Les layouts Divi Library avec placeholders restent officiellement pris en charge et complémentaires de Dynamic Content.

## Styles

Le CSS public utilise le préfixe `seed-`.

Le plugin ne réinitialise pas les styles globaux du site et n'impose ni thème ni constructeur de page.

## Limites actuelles

WP Seed Content Kit ne fournit pas :

- de module Divi personnalisé ;
- de widget Elementor ;
- de bloc Gutenberg personnalisé ;
- de sélecteur WP Seed finalisé dans l'éditeur Gutenberg ;
- d'intégration ACF obligatoire ;
- d'outil d'import ou de migration ;
- de modules fonctionnels Annuaire ou Créations sonores.
