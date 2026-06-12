<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_modules_page()
{
    add_menu_page(
        __('WP Seed Content Kit', 'wp-seed-content-kit'),
        __('WP Seed Content Kit', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit',
        'wp_seed_content_kit_render_modules_page',
        'dashicons-screenoptions',
        58
    );

    add_submenu_page(
        'wp-seed-content-kit',
        __('Configuration / Générateurs', 'wp-seed-content-kit'),
        __('Configuration / Générateurs', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit',
        'wp_seed_content_kit_render_modules_page',
        1
    );

    if (wp_seed_content_kit_is_module_active('testimonials') && 'plugin' === wp_seed_content_kit_get_module_menu_location('seed_testimonial')) {
        add_submenu_page(
            'wp-seed-content-kit',
            __('Témoignages', 'wp-seed-content-kit'),
            __('Témoignages', 'wp-seed-content-kit'),
            'edit_posts',
            'edit.php?post_type=seed_testimonial',
            null,
            2
        );
    } elseif (!wp_seed_content_kit_is_module_active('testimonials')) {
        wp_seed_content_kit_register_placeholder_submenu(
            __('Témoignages', 'wp-seed-content-kit'),
            'wp-seed-content-kit-testimonials'
        );
    }

    wp_seed_content_kit_register_placeholder_submenu(
        __('Citations', 'wp-seed-content-kit'),
        'wp-seed-content-kit-quotes'
    );

    wp_seed_content_kit_register_placeholder_submenu(
        __('Annuaire', 'wp-seed-content-kit'),
        'wp-seed-content-kit-directory'
    );

    wp_seed_content_kit_register_placeholder_submenu(
        __('Créations sonores', 'wp-seed-content-kit'),
        'wp-seed-content-kit-audio'
    );

    add_submenu_page(
        'wp-seed-content-kit',
        __('Aide / Documentation', 'wp-seed-content-kit'),
        __('Aide / Documentation', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit-help',
        'wp_seed_content_kit_render_help_page'
    );
}
add_action('admin_menu', 'wp_seed_content_kit_register_modules_page');

function wp_seed_content_kit_register_placeholder_submenu($label, $slug)
{
    add_submenu_page(
        'wp-seed-content-kit',
        $label,
        $label,
        'manage_options',
        $slug,
        'wp_seed_content_kit_render_placeholder_page'
    );
}

function wp_seed_content_kit_add_plugin_action_links($links)
{
    if (!current_user_can('manage_options')) {
        return $links;
    }

    $modules_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('admin.php?page=wp-seed-content-kit')),
        esc_html__('Configuration', 'wp-seed-content-kit')
    );

    array_unshift($links, $modules_link);

    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(WP_SEED_CONTENT_KIT_FILE), 'wp_seed_content_kit_add_plugin_action_links');

function wp_seed_content_kit_handle_modules_form()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer les modules WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    check_admin_referer('wp_seed_content_kit_save_modules', 'wp_seed_content_kit_modules_nonce');

    $enabled_modules = array();
    if (isset($_POST['wp_seed_content_kit_modules']) && is_array($_POST['wp_seed_content_kit_modules'])) {
        $enabled_modules = array_map('sanitize_key', wp_unslash($_POST['wp_seed_content_kit_modules']));
    }

    $previous = wp_seed_content_kit_get_module_options();
    $next = array(
        'testimonials' => in_array('testimonials', $enabled_modules, true),
    );

    $menu_visibility = array(
        'seed_testimonial' => 'plugin',
    );
    if (
        isset($_POST['wp_seed_content_kit_module_menu_visibility'])
        && is_array($_POST['wp_seed_content_kit_module_menu_visibility'])
    ) {
        $posted_menu_visibility = wp_unslash($_POST['wp_seed_content_kit_module_menu_visibility']);
        if (
            isset($posted_menu_visibility['seed_testimonial'])
            && 'root' === sanitize_key($posted_menu_visibility['seed_testimonial'])
        ) {
            $menu_visibility['seed_testimonial'] = 'root';
        }
    }

    update_option('wp_seed_content_kit_modules', $next);
    update_option('wp_seed_content_kit_module_menu_visibility', $menu_visibility);

    if ($previous !== $next) {
        wp_seed_content_kit_refresh_module_rewrite_rules($next);
    }

    wp_safe_redirect(add_query_arg(array(
        'page' => 'wp-seed-content-kit',
        'tab' => 'configuration',
        'settings-updated' => 'true',
    ), admin_url('admin.php')));
    exit;
}

function wp_seed_content_kit_refresh_module_rewrite_rules($modules)
{
    if (!empty($modules['testimonials'])) {
        if (!function_exists('wp_seed_content_register_testimonial_post_type')) {
            require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/testimonials/post-type.php';
        }

        wp_seed_content_register_testimonial_post_type();
    } elseif (function_exists('unregister_post_type') && post_type_exists('seed_testimonial')) {
        unregister_post_type('seed_testimonial');
    }

    flush_rewrite_rules();
}

function wp_seed_content_kit_get_module_status_label($module)
{
    if (!empty($module['planned'])) {
        return __('Prévu', 'wp-seed-content-kit');
    }

    if (!empty($module['active'])) {
        return __('Actif', 'wp-seed-content-kit');
    }

    return __('Inactif', 'wp-seed-content-kit');
}

function wp_seed_content_kit_render_module_status_badge($module)
{
    $classes = array('button', 'button-small', 'disabled');

    if (!empty($module['active']) && empty($module['planned'])) {
        $classes[] = 'button-primary';
    }

    printf(
        '<span class="%s">%s</span>',
        esc_attr(implode(' ', $classes)),
        esc_html(wp_seed_content_kit_get_module_status_label($module))
    );
}

function wp_seed_content_kit_render_shortcode_field($module_key, $module)
{
    if (empty($module['active']) || empty($module['shortcode'])) {
        echo esc_html__('Non disponible', 'wp-seed-content-kit');
        return;
    }

    printf(
        '<input type="text" class="regular-text code" readonly="readonly" value="%s" aria-label="%s" />',
        esc_attr($module['shortcode']),
        esc_attr(sprintf(__('Shortcode pour %s', 'wp-seed-content-kit'), $module['label']))
    );
}

function wp_seed_content_kit_render_module_toggle($module_key, $module)
{
    if (empty($module['activable'])) {
        echo esc_html__('Non', 'wp-seed-content-kit');
        return;
    }

    printf(
        '<label><input type="checkbox" name="wp_seed_content_kit_modules[]" value="%s" %s /> %s</label>',
        esc_attr($module_key),
        checked(!empty($module['active']), true, false),
        esc_html__('Actif', 'wp-seed-content-kit')
    );
}

function wp_seed_content_kit_render_module_menu_visibility_toggle($module_key, $module)
{
    if ('testimonials' !== $module_key || empty($module['active'])) {
        echo esc_html__('Non disponible', 'wp-seed-content-kit');
        return;
    }

    printf(
        '<label><input type="checkbox" name="wp_seed_content_kit_module_menu_visibility[seed_testimonial]" value="root" %s /> %s</label>',
        checked('root' === wp_seed_content_kit_get_module_menu_location('seed_testimonial'), true, false),
        esc_html__('Afficher dans le menu WordPress principal', 'wp-seed-content-kit')
    );
}

function wp_seed_content_kit_render_module_quick_links($module_key, $module)
{
    if ('testimonials' !== $module_key || empty($module['active'])) {
        echo esc_html__('Prévu', 'wp-seed-content-kit');
        return;
    }

    $links = array(
        sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('edit.php?post_type=seed_testimonial')),
            esc_html__('Gérer', 'wp-seed-content-kit')
        ),
        sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('post-new.php?post_type=seed_testimonial')),
            esc_html__('Ajouter', 'wp-seed-content-kit')
        ),
    );

    echo wp_kses_post(implode(' | ', $links));
}

function wp_seed_content_kit_render_usage_help($module)
{
    if (empty($module['usage']) || !is_array($module['usage'])) {
        echo '&mdash;';
        return;
    }

    echo '<ul>';
    foreach ($module['usage'] as $usage) {
        printf('<li>%s</li>', esc_html($usage));
    }
    echo '</ul>';
}

function wp_seed_content_kit_get_current_admin_tab()
{
    $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'configuration';

    if (!in_array($tab, array('configuration', 'generators'), true)) {
        return 'configuration';
    }

    return $tab;
}

function wp_seed_content_kit_render_admin_tabs($current_tab)
{
    $tabs = array(
        'configuration' => __('Configuration générale', 'wp-seed-content-kit'),
        'generators' => __('Générateurs de shortcodes', 'wp-seed-content-kit'),
    );

    echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__('Navigation WP Seed Content Kit', 'wp-seed-content-kit') . '">';
    foreach ($tabs as $tab => $label) {
        $class = 'nav-tab';
        if ($current_tab === $tab) {
            $class .= ' nav-tab-active';
        }

        printf(
            '<a class="%s" href="%s">%s</a>',
            esc_attr($class),
            esc_url(add_query_arg(array('page' => 'wp-seed-content-kit', 'tab' => $tab), admin_url('admin.php'))),
            esc_html($label)
        );
    }
    echo '</nav>';
}

function wp_seed_content_kit_render_configuration_tab()
{
    $modules = wp_seed_content_kit_get_modules();
    ?>
    <?php if (isset($_GET['settings-updated']) && 'true' === sanitize_key(wp_unslash($_GET['settings-updated']))) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__('Configuration enregistrée.', 'wp-seed-content-kit'); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(add_query_arg(array('page' => 'wp-seed-content-kit', 'tab' => 'configuration'), admin_url('admin.php'))); ?>">
        <?php wp_nonce_field('wp_seed_content_kit_save_modules', 'wp_seed_content_kit_modules_nonce'); ?>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php echo esc_html__('Module', 'wp-seed-content-kit'); ?></th>
                    <th scope="col"><?php echo esc_html__('Statut', 'wp-seed-content-kit'); ?></th>
                    <th scope="col"><?php echo esc_html__('Activable', 'wp-seed-content-kit'); ?></th>
                    <th scope="col"><?php echo esc_html__('Menu WordPress', 'wp-seed-content-kit'); ?></th>
                    <th scope="col"><?php echo esc_html__('Shortcode', 'wp-seed-content-kit'); ?></th>
                    <th scope="col"><?php echo esc_html__('Liens rapides', 'wp-seed-content-kit'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $module_key => $module) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html($module['label']); ?></th>
                        <td><?php wp_seed_content_kit_render_module_status_badge($module); ?></td>
                        <td><?php wp_seed_content_kit_render_module_toggle($module_key, $module); ?></td>
                        <td><?php wp_seed_content_kit_render_module_menu_visibility_toggle($module_key, $module); ?></td>
                        <td><?php wp_seed_content_kit_render_shortcode_field($module_key, $module); ?></td>
                        <td><?php wp_seed_content_kit_render_module_quick_links($module_key, $module); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2><?php echo esc_html__('Où l’utiliser ?', 'wp-seed-content-kit'); ?></h2>
        <?php wp_seed_content_kit_render_usage_help(array('usage' => wp_seed_content_kit_get_builder_usage_help())); ?>

        <?php submit_button(__('Enregistrer les modules', 'wp-seed-content-kit')); ?>
    </form>
    <?php
}

function wp_seed_content_kit_render_modules_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) : '';
    if ('POST' === $request_method) {
        wp_seed_content_kit_handle_modules_form();
    }

    $current_tab = wp_seed_content_kit_get_current_admin_tab();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WP Seed Content Kit', 'wp-seed-content-kit'); ?></h1>
        <?php wp_seed_content_kit_render_admin_tabs($current_tab); ?>

        <?php if ('generators' === $current_tab) : ?>
            <?php wp_seed_content_kit_render_generators_tab(); ?>
        <?php else : ?>
            <?php wp_seed_content_kit_render_configuration_tab(); ?>
        <?php endif; ?>
    </div>
    <?php
}

function wp_seed_content_kit_render_placeholder_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $pages = array(
        'wp-seed-content-kit-testimonials' => __('Témoignages', 'wp-seed-content-kit'),
        'wp-seed-content-kit-quotes' => __('Citations', 'wp-seed-content-kit'),
        'wp-seed-content-kit-directory' => __('Annuaire', 'wp-seed-content-kit'),
        'wp-seed-content-kit-audio' => __('Créations sonores', 'wp-seed-content-kit'),
    );
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    $title = isset($pages[$page]) ? $pages[$page] : __('Module prévu', 'wp-seed-content-kit');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html($title); ?></h1>
        <p><?php echo esc_html__('Prévu pour une prochaine version.', 'wp-seed-content-kit'); ?></p>
    </div>
    <?php
}

function wp_seed_content_kit_render_help_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Aide / Documentation', 'wp-seed-content-kit'); ?></h1>
        <p><?php echo esc_html__('Documentation légère prévue pour une prochaine version.', 'wp-seed-content-kit'); ?></p>
    </div>
    <?php
}
