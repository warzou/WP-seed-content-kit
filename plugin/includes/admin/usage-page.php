<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_usage_tabs()
{
    return array(
        'functioning' => __('Fonctionnement', 'wp-seed-content-kit'),
        'templates' => __('Templates', 'wp-seed-content-kit'),
        'collections' => __('Collections', 'wp-seed-content-kit'),
        'integrations' => __('Intégrer dans une page', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_kit_get_usage_integration_tabs()
{
    return array(
        'shortcodes' => __('Shortcodes', 'wp-seed-content-kit'),
        'gutenberg' => __('Gutenberg', 'wp-seed-content-kit'),
        'spectra' => __('Spectra', 'wp-seed-content-kit'),
        'divi' => __('Divi', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_kit_get_current_usage_tab()
{
    $tab = isset($_GET['usage_tab']) ? sanitize_key(wp_unslash($_GET['usage_tab'])) : 'functioning';
    return array_key_exists($tab, wp_seed_content_kit_get_usage_tabs()) ? $tab : 'functioning';
}

function wp_seed_content_kit_get_current_usage_integration_tab()
{
    $tab = isset($_GET['integration']) ? sanitize_key(wp_unslash($_GET['integration'])) : 'shortcodes';
    return array_key_exists($tab, wp_seed_content_kit_get_usage_integration_tabs()) ? $tab : 'shortcodes';
}

function wp_seed_content_kit_get_usage_url($tab, $integration = '')
{
    $args = array(
        'page' => 'wp-seed-content-kit-usage',
        'usage_tab' => sanitize_key($tab),
    );
    if ('' !== $integration) {
        $args['integration'] = sanitize_key($integration);
    }

    return add_query_arg($args, admin_url('admin.php'));
}

function wp_seed_content_kit_enqueue_usage_assets($hook_suffix)
{
    $expected = isset($GLOBALS['wp_seed_content_kit_usage_page_hook'])
        ? $GLOBALS['wp_seed_content_kit_usage_page_hook']
        : '';

    if ('' === $expected || $hook_suffix !== $expected) {
        return;
    }

    wp_enqueue_style(
        'wp-seed-content-kit-admin-usage',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/admin-usage.css',
        array(),
        WP_SEED_CONTENT_KIT_VERSION
    );
    wp_enqueue_script(
        'wp-seed-content-kit-admin-usage',
        WP_SEED_CONTENT_KIT_URL . 'assets/admin-usage.js',
        array(),
        WP_SEED_CONTENT_KIT_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'wp_seed_content_kit_enqueue_usage_assets');

function wp_seed_content_kit_render_usage_tabs($current)
{
    echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__('Rubriques d’utilisation', 'wp-seed-content-kit') . '">';
    foreach (wp_seed_content_kit_get_usage_tabs() as $tab => $label) {
        $active = $current === $tab;
        printf(
            '<a class="nav-tab%1$s" href="%2$s"%3$s>%4$s</a>',
            $active ? ' nav-tab-active' : '',
            esc_url(wp_seed_content_kit_get_usage_url($tab)),
            $active ? ' aria-current="page"' : '',
            esc_html($label)
        );
    }
    echo '</nav>';
}

function wp_seed_content_kit_render_usage_integration_tabs($current)
{
    echo '<nav class="seed-usage-subtabs" aria-label="' . esc_attr__('Méthodes d’intégration', 'wp-seed-content-kit') . '">';
    foreach (wp_seed_content_kit_get_usage_integration_tabs() as $tab => $label) {
        $active = $current === $tab;
        printf(
            '<a class="button%1$s" href="%2$s"%3$s>%4$s</a>',
            $active ? ' button-primary' : '',
            esc_url(wp_seed_content_kit_get_usage_url('integrations', $tab)),
            $active ? ' aria-current="page"' : '',
            esc_html($label)
        );
    }
    echo '</nav>';
}

function wp_seed_content_kit_get_usage_status_label($status)
{
    $labels = array(
        'functional' => __('Fonctionnel', 'wp-seed-content-kit'),
        'indirect' => __('Indirect', 'wp-seed-content-kit'),
        'experimental' => __('Expérimental', 'wp-seed-content-kit'),
        'unavailable' => __('Non disponible', 'wp-seed-content-kit'),
    );

    return isset($labels[$status]) ? $labels[$status] : '';
}

function wp_seed_content_kit_render_usage_status($status, $label)
{
    printf(
        '<p class="seed-usage-status seed-usage-status--%1$s"><strong>%2$s :</strong> %3$s</p>',
        esc_attr(sanitize_html_class($status)),
        esc_html($label),
        esc_html(wp_seed_content_kit_get_usage_status_label($status))
    );
}

function wp_seed_content_kit_render_usage_example($id, $label, $value)
{
    ?>
    <div class="seed-usage-copy">
        <label for="<?php echo esc_attr($id); ?>"><strong><?php echo esc_html($label); ?></strong></label>
        <div class="seed-usage-copy__controls">
            <input id="<?php echo esc_attr($id); ?>" class="large-text code" type="text" readonly="readonly" value="<?php echo esc_attr($value); ?>">
            <button type="button" class="button" data-seed-usage-copy="<?php echo esc_attr($id); ?>" data-copy-label="<?php echo esc_attr__('Copié.', 'wp-seed-content-kit'); ?>">
                <?php echo esc_html__('Copier', 'wp-seed-content-kit'); ?>
            </button>
            <span class="seed-usage-copy__status" aria-live="polite"></span>
        </div>
    </div>
    <?php
}

function wp_seed_content_kit_render_usage_functioning()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-functioning-title">
        <h2 id="seed-usage-functioning-title"><?php esc_html_e('Du contenu à la page', 'wp-seed-content-kit'); ?></h2>
        <ol class="seed-usage-flow">
            <li><strong><?php esc_html_e('Contenus', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Les informations que vous créez et mettez à jour.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Collections', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Quels contenus afficher et dans quel ordre.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Templates', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Comment présenter chaque contenu.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Intégrations', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Où insérer le résultat dans une page.', 'wp-seed-content-kit'); ?></span></li>
        </ol>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-examples-title">
        <h2 id="seed-usage-examples-title"><?php esc_html_e('Exemples concrets', 'wp-seed-content-kit'); ?></h2>
        <h3><?php esc_html_e('Annuaire', 'wp-seed-content-kit'); ?></h3>
        <p><?php esc_html_e('Les fiches sont les contenus. La Collection choisit les personnes et leur ordre. Le Template définit l’apparence des cartes. Un shortcode, Gutenberg, Spectra ou Divi insère le résultat dans une page.', 'wp-seed-content-kit'); ?></p>
        <h3><?php esc_html_e('Témoignages', 'wp-seed-content-kit'); ?></h3>
        <p><?php esc_html_e('Les témoignages sont les contenus. La Collection peut retenir les éléments mis en avant et leur ordre. Le Template définit leur présentation, puis une intégration les place dans la page.', 'wp-seed-content-kit'); ?></p>
        <h3><?php esc_html_e('Citations', 'wp-seed-content-kit'); ?></h3>
        <p><?php esc_html_e('Les citations sont les contenus. La Collection peut choisir une citation quotidienne ou une liste. Le Template définit son apparence et l’intégration choisit son emplacement.', 'wp-seed-content-kit'); ?></p>
    </section>
    <?php
}
function wp_seed_content_kit_get_usage_template_placeholders()
{
    return array(
        __('Témoignages', 'wp-seed-content-kit') => array('photo', 'photo_url', 'photo_alt', 'name', 'text', 'context', 'date'),
        __('Citations', 'wp-seed-content-kit') => array('quote', 'author', 'era', 'source'),
        __('Annuaire', 'wp-seed-content-kit') => array(
            'directory.name', 'directory.photo', 'directory.bio', 'directory.status',
            'directory.status_label', 'directory.city', 'directory.postal_code',
            'directory.department', 'directory.country', 'directory.phone',
            'directory.email', 'directory.website', 'directory.facebook',
            'directory.instagram', 'directory.featured',
        ),
    );
}

function wp_seed_content_kit_render_usage_templates()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-templates-title">
        <h2 id="seed-usage-templates-title"><?php esc_html_e('Un Template définit l’apparence d’un contenu.', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Témoignages, Citations et Annuaire utilisent le même moteur de Templates. Sans Template demandé, le rendu natif du module reste actif.', 'wp-seed-content-kit'); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=seed_template')); ?>"><?php esc_html_e('Gérer les Templates', 'wp-seed-content-kit'); ?></a>
            <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=seed_template')); ?>"><?php esc_html_e('Créer un Template', 'wp-seed-content-kit'); ?></a>
        </p>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-placeholders-title">
        <h2 id="seed-usage-placeholders-title"><?php esc_html_e('Placeholders disponibles', 'wp-seed-content-kit'); ?></h2>
        <?php foreach (wp_seed_content_kit_get_usage_template_placeholders() as $module => $placeholders) : ?>
            <h3><?php echo esc_html($module); ?></h3>
            <p class="seed-usage-code-list">
                <?php foreach ($placeholders as $placeholder) : ?>
                    <code>{{<?php echo esc_html($placeholder); ?>}}</code>
                <?php endforeach; ?>
            </p>
        <?php endforeach; ?>
    </section>

    <section class="seed-usage-section">
        <h2><?php esc_html_e('Sources et fallback', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Le contenu du Template peut être composé avec l’éditeur WordPress. Si Divi est disponible, un Layout Divi Library publié peut servir de source. Un Template absent, brouillon, incompatible ou en erreur déclenche le rendu natif prévu par le module, sans afficher de placeholder brut.', 'wp-seed-content-kit'); ?></p>
    </section>
    <?php
}
function wp_seed_content_kit_render_usage_collections()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-collections-title">
        <h2 id="seed-usage-collections-title"><?php esc_html_e('Une Collection définit quels contenus afficher et dans quel ordre.', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Une Collection n’est pas un contenu enregistré. Ce sont des règles de sélection utilisées par les shortcodes et les intégrations. Aucun objet ou écran de sauvegarde de Collection n’est créé.', 'wp-seed-content-kit'); ?></p>
        <table class="widefat striped seed-usage-table">
            <thead><tr><th><?php esc_html_e('Module', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Paramètres de sélection', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Contenus', 'wp-seed-content-kit'); ?></th></tr></thead>
            <tbody>                <tr>
                    <th><?php esc_html_e('Témoignages', 'wp-seed-content-kit'); ?></th>
                    <td><code>ids</code>, <code>featured</code>, <code>context</code>, <code>limit</code>, <code>orderby</code>, <code>order</code></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=seed_testimonial')); ?>">
                            <?php esc_html_e('Voir les Témoignages', 'wp-seed-content-kit'); ?>
                        </a>
                    </td>
                </tr>                <tr>
                    <th><?php esc_html_e('Citations', 'wp-seed-content-kit'); ?></th>
                    <td><code>mode</code>, <code>featured</code>, <code>limit</code>, <code>orderby</code>, <code>order</code></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=seed_quote')); ?>">
                            <?php esc_html_e('Voir les Citations', 'wp-seed-content-kit'); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Annuaire', 'wp-seed-content-kit'); ?></th>
                    <td>
                        <code>status</code>, <code>department</code>, <code>country</code>,
                        <code>featured</code>, <code>ids</code>, <code>limit</code>,
                        <code>orderby</code>, <code>order</code>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=seed_directory')); ?>">
                            <?php esc_html_e('Voir l’Annuaire', 'wp-seed-content-kit'); ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-generators-title">
        <h2 id="seed-usage-generators-title"><?php esc_html_e('Générateurs d’intégration', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Ces générateurs produisent uniquement du texte à copier. Ils n’enregistrent aucun réglage ni aucune Collection.', 'wp-seed-content-kit'); ?></p>
        <?php wp_seed_content_kit_render_generators_tab(); ?>
        <?php wp_seed_content_kit_render_directory_generator(); ?>
    </section>
    <?php
}
function wp_seed_content_kit_render_usage_shortcodes()
{
    wp_seed_content_kit_render_usage_status('functional', __('État', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Les shortcodes sont le parcours canonique pour appliquer les règles de sélection et afficher le résultat dans une page.', 'wp-seed-content-kit'); ?></p>
    <?php
    wp_seed_content_kit_render_usage_example(
        'seed-usage-shortcode-testimonials',
        __('Témoignages mis en avant', 'wp-seed-content-kit'),
        '[seed_testimonials featured="only" limit="3" template="accueil"]'
    );
    wp_seed_content_kit_render_usage_example(
        'seed-usage-shortcode-quotes',
        __('Citation quotidienne', 'wp-seed-content-kit'),
        '[seed_quotes mode="daily" template="citation-du-jour"]'
    );
    wp_seed_content_kit_render_usage_example(
        'seed-usage-shortcode-directory',
        __('Annuaire', 'wp-seed-content-kit'),
        '[seed_directory status="practicing" orderby="display_order" order="asc"]'
    );
    ?>
    <p><strong><?php esc_html_e('Attributs principaux :', 'wp-seed-content-kit'); ?></strong> <code>ids</code>, <code>status</code>, <code>featured</code>, <code>context</code>, <code>department</code>, <code>country</code>, <code>limit</code>, <code>columns</code>, <code>orderby</code>, <code>order</code>, <code>mode</code> et <code>template</code>, selon le module.</p>
    <p><strong><?php esc_html_e('Résultat attendu :', 'wp-seed-content-kit'); ?></strong> <?php esc_html_e('une sélection de contenus publics rendue par le module, avec son état vide et son fallback natif.', 'wp-seed-content-kit'); ?></p>
    <p><strong><?php esc_html_e('Alias déprécié :', 'wp-seed-content-kit'); ?></strong> <code>[wp_seed_directory]</code> <?php esc_html_e('reste temporairement identique à [seed_directory].', 'wp-seed-content-kit'); ?></p>
    <?php
}
function wp_seed_content_kit_render_usage_gutenberg()
{
    wp_seed_content_kit_render_usage_status('functional', __('Bloc Shortcode', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Ajoutez un bloc Shortcode et insérez l’un des exemples WP Seed. Le rendu serveur applique les mêmes règles que dans une page classique.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('indirect', __('Block Bindings', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Le provider serveur couvre huit champs texte Citations et Témoignages dans les blocs Core Paragraphe et Titre. Une Query Loop doit fournir le contenu courant.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('unavailable', __('Interface dédiée', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Aucun bloc WP Seed ni sélecteur finalisé n’est fourni dans l’éditeur. Les Block Bindings ne couvrent pas encore Annuaire.', 'wp-seed-content-kit'); ?></p>
    <?php
}
function wp_seed_content_kit_render_usage_spectra()
{
    wp_seed_content_kit_render_usage_status('indirect', __('État', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Utilisez un bloc Shortcode Core dans une page ou un Container Spectra, ou composez le contenu d’un Template avec des blocs Gutenberg et Spectra.', 'wp-seed-content-kit'); ?></p>
    <?php
    wp_seed_content_kit_render_usage_example(
        'seed-usage-spectra-directory',
        __('Exemple Annuaire', 'wp-seed-content-kit'),
        '[seed_directory template="annuaire-carte"]'
    );
    wp_seed_content_kit_render_usage_status('unavailable', __('Provider Spectra', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Aucun provider, bloc ou contrat de requête Spectra natif n’est annoncé. Les métadonnées WP Seed ne doivent pas être lues directement.', 'wp-seed-content-kit'); ?></p>
    <?php
}
function wp_seed_content_kit_render_usage_divi()
{
    wp_seed_content_kit_render_usage_status('functional', __('Shortcodes', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Insérez le shortcode dans un module Texte ou Code.', 'wp-seed-content-kit'); ?></p>
    <?php
    wp_seed_content_kit_render_usage_example(
        'seed-usage-divi-directory',
        __('Exemple Annuaire', 'wp-seed-content-kit'),
        '[seed_directory template="annuaire-carte"]'
    );
    wp_seed_content_kit_render_usage_status('indirect', __('Divi Library', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Un Layout Divi Library publié peut servir de source à un Template WP Seed. Placez les placeholders dans un module Texte ou Code du layout.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('experimental', __('Dynamic Content Divi 5', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Les sources Dynamic Content expérimentales couvrent Citations et Témoignages sous Divi 5. Elles ne couvrent pas Annuaire et ne remplacent pas les Templates.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('unavailable', __('Module Divi WP Seed', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Aucun module Divi propriétaire n’est fourni.', 'wp-seed-content-kit'); ?></p>
    <?php
}
function wp_seed_content_kit_render_usage_integrations()
{
    $current = wp_seed_content_kit_get_current_usage_integration_tab();
    wp_seed_content_kit_render_usage_integration_tabs($current);
    echo '<section class="seed-usage-section seed-usage-integration">';

    if ('gutenberg' === $current) {
        wp_seed_content_kit_render_usage_gutenberg();
    } elseif ('spectra' === $current) {
        wp_seed_content_kit_render_usage_spectra();
    } elseif ('divi' === $current) {
        wp_seed_content_kit_render_usage_divi();
    } else {
        wp_seed_content_kit_render_usage_shortcodes();
    }

    echo '</section>';
}

function wp_seed_content_kit_render_usage_page()
{
    if (!current_user_can('manage_wp_seed_integrations')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation d’accéder à l’utilisation de WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $current = wp_seed_content_kit_get_current_usage_tab();
    ?>
    <div class="wrap seed-usage">
        <h1><?php esc_html_e('Utilisation', 'wp-seed-content-kit'); ?></h1>
        <p class="description"><?php esc_html_e('Configuration active les modules et conserve les réglages. Utilisation explique comment sélectionner, présenter et insérer les contenus.', 'wp-seed-content-kit'); ?></p>
        <?php wp_seed_content_kit_render_usage_tabs($current); ?>

        <?php if ('templates' === $current) : ?>
            <?php wp_seed_content_kit_render_usage_templates(); ?>
        <?php elseif ('collections' === $current) : ?>
            <?php wp_seed_content_kit_render_usage_collections(); ?>
        <?php elseif ('integrations' === $current) : ?>
            <?php wp_seed_content_kit_render_usage_integrations(); ?>
        <?php else : ?>
            <?php wp_seed_content_kit_render_usage_functioning(); ?>
        <?php endif; ?>
    </div>
    <?php
}
function wp_seed_content_kit_render_directory_generator()
{
    ?>
    <details>
        <summary><strong><?php esc_html_e('Générateur Annuaire', 'wp-seed-content-kit'); ?></strong></summary>
        <div class="seed-usage-generator" data-seed-usage-generator data-shortcode="seed_directory">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th><label for="seed-directory-generator-status"><?php esc_html_e('Statut', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <select id="seed-directory-generator-status" data-seed-usage-attr="status" data-seed-usage-default="">
                                <option value=""><?php esc_html_e('Tous', 'wp-seed-content-kit'); ?></option>
                                <option value="practicing"><?php esc_html_e('En exercice', 'wp-seed-content-kit'); ?></option>
                                <option value="seeking_models"><?php esc_html_e('En recherche de modèles', 'wp-seed-content-kit'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-featured"><?php esc_html_e('Mise en avant', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <select id="seed-directory-generator-featured" data-seed-usage-attr="featured" data-seed-usage-default="all">
                                <option value="all"><?php esc_html_e('Toutes les fiches', 'wp-seed-content-kit'); ?></option>
                                <option value="only"><?php esc_html_e('Uniquement les fiches mises en avant', 'wp-seed-content-kit'); ?></option>
                                <option value="exclude"><?php esc_html_e('Exclure les fiches mises en avant', 'wp-seed-content-kit'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-limit"><?php esc_html_e('Limite', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-limit" type="number" min="0" max="100" value="0" data-seed-usage-attr="limit" data-seed-usage-default="0"></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-orderby"><?php esc_html_e('Trier par', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <select id="seed-directory-generator-orderby" data-seed-usage-attr="orderby" data-seed-usage-default="display_order">
                                <option value="display_order"><?php esc_html_e('Ordre manuel puis nom', 'wp-seed-content-kit'); ?></option>
                                <option value="name"><?php esc_html_e('Nom', 'wp-seed-content-kit'); ?></option>
                                <option value="date"><?php esc_html_e('Date WordPress', 'wp-seed-content-kit'); ?></option>
                                <option value="id"><?php esc_html_e('Identifiant', 'wp-seed-content-kit'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-order"><?php esc_html_e('Ordre', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <select id="seed-directory-generator-order" data-seed-usage-attr="order" data-seed-usage-default="asc">
                                <option value="asc"><?php esc_html_e('Croissant', 'wp-seed-content-kit'); ?></option>
                                <option value="desc"><?php esc_html_e('Décroissant', 'wp-seed-content-kit'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-template"><?php esc_html_e('Template', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-template" class="regular-text code" type="text" data-seed-usage-attr="template" data-seed-usage-default="" placeholder="<?php esc_attr_e('Identifiant', 'wp-seed-content-kit'); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-output"><?php esc_html_e('Shortcode généré', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <div class="seed-usage-copy__controls">
                                <input id="seed-directory-generator-output" class="large-text code" type="text" readonly="readonly" value="[seed_directory]" data-seed-usage-output>
                                <button type="button" class="button" data-seed-usage-copy="seed-directory-generator-output" data-copy-label="<?php esc_attr_e('Copié.', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Copier', 'wp-seed-content-kit'); ?></button>
                                <span class="seed-usage-copy__status" aria-live="polite"></span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>
    <?php
}
