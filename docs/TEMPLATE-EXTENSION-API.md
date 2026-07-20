# Template Extension API

Statut : contrat public 1.0, fourni par WP Seed Content Kit 0.5.0-dev

## Objectif

La Template Extension API permet à un plugin tiers d'enregistrer un module de template et des placeholders typés, puis de demander le rendu d'un `seed_template` publié. Le plugin tiers reste propriétaire de ses données, de ses permissions et de ses règles métier. Content Kit valide uniquement le contexte explicitement transmis et produit la présentation.

Le contrat n'ajoute aucune dépendance d'activation, aucun endpoint REST/AJAX et aucune lecture implicite des métadonnées du plugin tiers.

## Détection et version

```php
wp_seed_content_kit_get_contract_version();
wp_seed_content_kit_supports($capability, $minimum_version = '');
```

La version du Template Extension Contract est `1.0`. Elle évolue indépendamment de la version du plugin.

Capacités publiques 1.0 :

- `template_extension` ;
- `template_modules` ;
- `template_placeholders` ;
- `typed_render_result` ;
- `render_assets` ;
- `recursion_guard`.

Une capacité inconnue, une version minimale invalide ou une version minimale supérieure retourne `false`.

## Fenêtre d'enregistrement

Un plugin tiers accroche son enregistrement à l'action suivante avant `init` :

```php
add_action('wp_seed_content_kit_register_template_modules', 'example_register_templates');
```

Content Kit ouvre le registre à `init` priorité `1`, enregistre ses modules natifs, exécute l'action une seule fois, puis ferme définitivement le registre pour la requête. Un doublon, un identifiant invalide ou un enregistrement tardif retourne `false` sans remplacer une définition existante.

## Registre des modules

```php
wp_seed_content_kit_register_template_module($module, array $definition);
```

L'identifiant suit `^[a-z][a-z0-9_-]*$`. La définition normalisée contient :

- `label` et `description` ;
- `placeholders` ;
- `provider`, callback facultatif recevant uniquement le contexte filtré ;
- `validate_context`, callback facultatif retournant strictement `true` ;
- `render_types`, sous-ensemble de `native` et `divi_layout` ;
- `assets.styles` et `assets.scripts`, listes de handles WordPress ;
- `shortcode`, facultatif, uniquement pour l'aide d'administration.

Le provider retourne un tableau indexé par clé de placeholder. Sans provider, chaque placeholder lit sa clé `context_key` dans le contexte filtré. Aucun callback ne reçoit les clés inconnues du contexte original.

## Registre des placeholders

```php
wp_seed_content_kit_register_template_placeholders($module, array $definitions);
```

Cette fonction étend un module déjà enregistré pendant la même fenêtre. Une clé suit `^[a-z][a-z0-9_.-]*$`. Chaque définition normalisée contient :

- `type` ;
- `label` ;
- `empty` ;
- `required` ;
- `escape`, fixé par le type ;
- `context_key`, égal à la clé par défaut ;
- `normalize_callback`, facultatif et appelé avec la seule valeur filtrée.

Types fermés et stratégies :

| Type | Entrée | Sortie |
| --- | --- | --- |
| `text` | scalaire | `esc_html()` |
| `textarea` | scalaire | `esc_html()` puis retours contrôlés |
| `html` | scalaire | `wp_kses()` avec allowlist publique |
| `url` | URL HTTP(S) | `esc_url_raw()` puis `esc_url()` |
| `email` | adresse valide | `sanitize_email()` puis `esc_html()` |
| `tel` | scalaire | caractères téléphoniques autorisés puis `esc_html()` |
| `image` | tableau `url`, `alt` | balise image validée et échappée |
| `text_list` | tableau de scalaires | éléments échappés puis séparés par une virgule |

Les scripts, événements inline, protocoles d'URL interdits, iframes, chemins locaux et valeurs non conformes sont retirés ou refusés. Les valeurs du contexte ne sont jamais exécutées comme PHP ou shortcode.

## API de rendu

```php
$result = wp_seed_content_kit_render_template(
    $slug,
    $module,
    array $context = array(),
    array $args = array()
);
```

`$args` est réservé aux extensions compatibles futures et n'altère aucun comportement en 1.0.

Le slug doit déjà être canonique selon `sanitize_title()`. Content Kit résout uniquement un `seed_template` publié et vérifie l'égalité exacte de son module. Une résolution positive est réutilisée pendant la requête ; aucun cache persistant n'est créé.

Le contexte est fermé par les `context_key` déclarées. Les clés inconnues sont supprimées avant tout validator, normalizer ou provider. Content Kit ne consulte aucune donnée métier implicite.

## Résultat typé

Tout appel retourne un `WP_Seed_Content_Kit_Render_Result` :

```php
$result->is_success();
$result->get_html();
$result->get_code();
$result->get_template_id();
$result->get_assets();
```

Codes contractuels :

- `success` ;
- `unavailable` ;
- `invalid_slug` ;
- `template_not_found` ;
- `module_mismatch` ;
- `invalid_context` ;
- `empty_render` ;
- `recursion_detected` ;
- `provider_error` ;
- `invalid_assets`.

Un échec contient toujours un HTML vide et aucun asset. Les détails internes d'une erreur ou exception ne sont jamais inclus dans le résultat public. Le plugin appelant reste seul responsable de son fallback métier.

## Récursion

Le moteur maintient une pile par couple `template ID:module`. Il refuse une entrée déjà active et toute profondeur supérieure ou égale à huit. La pile est restaurée dans un bloc `finally`, y compris après une erreur du provider ou du pipeline WordPress.

## Assets

Un module peut annoncer uniquement des handles de styles ou scripts WordPress. Tous les handles doivent être enregistrés avant le rendu. Ils sont validés avant tout enqueue, dédupliqués et chargés uniquement après un rendu réussi. Une URL, un chemin ou un handle inconnu produit `invalid_assets` sans chargement partiel.

## Gutenberg et Divi

Le rendu natif passe par le pipeline serveur `the_content` et fonctionne dans un bloc Shortcode ou un thème classique. Il ne crée aucun Block Binding implicite.

Un module déclarant `divi_layout` peut utiliser la source Layout Divi Library existante d'un Template. L'API publique ne dépend d'aucune classe interne Divi et reste entièrement fonctionnelle sans Divi. Content Kit ne charge jamais Divi manuellement.

## Exemple neutre

```php
function example_register_templates()
{
    wp_seed_content_kit_register_template_module(
        'catalog',
        array(
            'label' => 'Catalogue',
            'description' => 'Cartes éditoriales du catalogue.',
            'render_types' => array('native', 'divi_layout'),
            'assets' => array(
                'styles' => array('example-catalog'),
                'scripts' => array(),
            ),
            'placeholders' => array(
                'item.title' => array(
                    'type' => 'text',
                    'label' => 'Titre',
                    'context_key' => 'title',
                    'required' => true,
                ),
                'item.summary' => array(
                    'type' => 'textarea',
                    'label' => 'Résumé',
                    'context_key' => 'summary',
                ),
            ),
        )
    );
}
add_action('wp_seed_content_kit_register_template_modules', 'example_register_templates');

$result = wp_seed_content_kit_render_template(
    'catalog-card',
    'catalog',
    array('title' => 'Exemple', 'summary' => 'Contenu public')
);

echo $result->is_success() ? $result->get_html() : example_native_fallback();
```

Le template peut utiliser `{{item.title}}` et `{{item.summary}}`. Le plugin tiers doit préenregistrer `example-catalog` et ne transmettre que des données dont il a déjà autorisé la publication.
