<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_modules_page()
{
    add_menu_page(
        __('Modules', 'wp-seed-content-kit'),
        __('WP Seed Content Kit', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit',
        'wp_seed_content_kit_render_modules_page',
        'dashicons-screenoptions',
        58
    );

    global $submenu;
    if (isset($submenu['wp-seed-content-kit'][0][0])) {
        $submenu['wp-seed-content-kit'][0][0] = __('Modules', 'wp-seed-content-kit');
    }
}
add_action('admin_menu', 'wp_seed_content_kit_register_modules_page');

function wp_seed_content_kit_add_plugin_action_links($links)
{
    if (!current_user_can('manage_options')) {
        return $links;
    }

    $modules_link = sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('admin.php?page=wp-seed-content-kit')),
        esc_html__('Modules', 'wp-seed-content-kit')
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

    update_option('wp_seed_content_kit_modules', $next);

    if ($previous !== $next) {
        wp_seed_content_kit_refresh_module_rewrite_rules($next);
    }

    add_settings_error(
        'wp_seed_content_kit_modules',
        'wp_seed_content_kit_modules_saved',
        __('Modules enregistrés.', 'wp-seed-content-kit'),
        'updated'
    );
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

function wp_seed_content_kit_render_modules_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer les modules WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) : '';
    if ('POST' === $request_method) {
        wp_seed_content_kit_handle_modules_form();
    }

    $modules = wp_seed_content_kit_get_modules();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WP Seed Content Kit - Modules', 'wp-seed-content-kit'); ?></h1>

        <?php settings_errors('wp_seed_content_kit_modules'); ?>

        <form method="post" action="">
            <?php wp_nonce_field('wp_seed_content_kit_save_modules', 'wp_seed_content_kit_modules_nonce'); ?>

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th scope="col"><?php echo esc_html__('Module', 'wp-seed-content-kit'); ?></th>
                        <th scope="col"><?php echo esc_html__('Statut', 'wp-seed-content-kit'); ?></th>
                        <th scope="col"><?php echo esc_html__('Activable', 'wp-seed-content-kit'); ?></th>
                        <th scope="col"><?php echo esc_html__('Shortcode', 'wp-seed-content-kit'); ?></th>
                        <th scope="col"><?php echo esc_html__('Où l’utiliser ?', 'wp-seed-content-kit'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module_key => $module) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($module['label']); ?></th>
                            <td><?php echo esc_html(wp_seed_content_kit_get_module_status_label($module)); ?></td>
                            <td><?php wp_seed_content_kit_render_module_toggle($module_key, $module); ?></td>
                            <td><?php wp_seed_content_kit_render_shortcode_field($module_key, $module); ?></td>
                            <td><?php wp_seed_content_kit_render_usage_help($module); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button(__('Enregistrer les modules', 'wp-seed-content-kit')); ?>
        </form>
    </div>
    <?php
}
