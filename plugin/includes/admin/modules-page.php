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
        wp_seed_content_kit_get_admin_menu_icon('parent'),
        58
    );

    add_submenu_page(
        'wp-seed-content-kit',
        __('Configuration', 'wp-seed-content-kit'),
        __('Configuration', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit',
        'wp_seed_content_kit_render_modules_page',
        1
    );

    if (!wp_seed_content_kit_is_module_active('testimonials')) {
        wp_seed_content_kit_register_placeholder_submenu(
            __('Témoignages', 'wp-seed-content-kit'),
            'wp-seed-content-kit-testimonials'
        );
    }

    if (!wp_seed_content_kit_is_module_active('quotes')) {
        wp_seed_content_kit_register_placeholder_submenu(
            __('Citations', 'wp-seed-content-kit'),
            'wp-seed-content-kit-quotes'
        );
    }

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
add_action('admin_post_wp_seed_content_kit_save_modules', 'wp_seed_content_kit_handle_modules_form');

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

function wp_seed_content_kit_get_available_roles_for_menu_visibility()
{
    if (!function_exists('wp_roles')) {
        return array();
    }

    $roles = wp_roles()->roles;
    if (!is_array($roles)) {
        return array();
    }

    $items = array();
    foreach ($roles as $slug => $role) {
        $items[sanitize_key((string) $slug)] = isset($role['name']) ? translate_user_role($role['name']) : sanitize_text_field((string) $slug);
    }
    asort($items);

    return $items;
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
        'quotes' => true,
    );

    $menu_visibility = wp_seed_content_kit_get_module_menu_visibility();
    $submitted = isset($_POST['wp_seed_content_kit_module_menu_visibility']) && is_array($_POST['wp_seed_content_kit_module_menu_visibility']) ? wp_unslash($_POST['wp_seed_content_kit_module_menu_visibility']) : array();
    $all_roles = wp_seed_content_kit_get_user_roles();

    foreach ($menu_visibility as $post_type => $visibility) {
        $visibility['show_in_menu'] = false;
        $visibility['roles'] = array('administrator');

        if (isset($submitted[$post_type]) && is_array($submitted[$post_type])) {
            if (!empty($submitted[$post_type]['show_in_menu'])) {
                $visibility['show_in_menu'] = wp_seed_content_bool_attr($submitted[$post_type]['show_in_menu'], false);
            }

            if (isset($submitted[$post_type]['roles']) && is_array($submitted[$post_type]['roles'])) {
                $roles = array();
                foreach ((array) $submitted[$post_type]['roles'] as $role) {
                    $role = sanitize_key((string) $role);
                    if (in_array($role, $all_roles, true)) {
                        $roles[] = $role;
                    }
                }
                if (!empty($roles)) {
                    $visibility['roles'] = array_values(array_unique($roles));
                }
            }
        }

        $menu_visibility[$post_type] = $visibility;
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

    if (!empty($modules['quotes'])) {
        if (!function_exists('wp_seed_content_register_quote_post_type')) {
            require_once WP_SEED_CONTENT_KIT_DIR . 'includes/modules/quotes/post-type.php';
        }

        wp_seed_content_register_quote_post_type();
    } elseif (function_exists('unregister_post_type') && post_type_exists('seed_quote')) {
        unregister_post_type('seed_quote');
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
    if (empty($module['active']) || empty($module['menu_supported']) || empty($module['post_type'])) {
        echo esc_html__('Non disponible', 'wp-seed-content-kit');
        return;
    }

    $post_type = sanitize_key($module['post_type']);
    $visibility = wp_seed_content_kit_get_module_menu_visibility_for_post_type($post_type);
    $selected_roles = isset($visibility['roles']) && is_array($visibility['roles']) ? $visibility['roles'] : array();
    $available_roles = wp_seed_content_kit_get_available_roles_for_menu_visibility();
    $name_base = esc_attr($post_type);

    printf(
        '<label><input type="checkbox" name="wp_seed_content_kit_module_menu_visibility[%1$s][show_in_menu]" value="1" %2$s /> %3$s</label><br/>',
        $name_base,
        checked(!empty($visibility['show_in_menu']), true, false),
        esc_html__('Afficher dans le menu WordPress principal', 'wp-seed-content-kit')
    );

    if (empty($available_roles)) {
        return;
    }

    echo '<small>' . esc_html__('Rôles autorisés :', 'wp-seed-content-kit') . '</small><br />';
    foreach ($available_roles as $role_key => $role_label) {
        printf(
            '<label style="margin-right:8px;display:inline-block;"><input type="checkbox" name="wp_seed_content_kit_module_menu_visibility[%1$s][roles][]" value="%2$s" %3$s /> %4$s</label>',
            $name_base,
            esc_attr($role_key),
            checked(in_array($role_key, $selected_roles, true), true, false),
            esc_html($role_label)
        );
    }
}

function wp_seed_content_kit_render_module_quick_links($module_key, $module)
{
    if (empty($module['active'])) {
        echo esc_html__('Prévu', 'wp-seed-content-kit');
        return;
    }

    if ('testimonials' === $module_key) {
        $post_type = 'seed_testimonial';
    } elseif ('quotes' === $module_key) {
        $post_type = 'seed_quote';
    } else {
        echo esc_html__('Prévu', 'wp-seed-content-kit');
        return;
    }

    $links = array(
        sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('edit.php?post_type=' . $post_type)),
            esc_html__('Gérer', 'wp-seed-content-kit')
        ),
        sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('post-new.php?post_type=' . $post_type)),
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

    if (!in_array($tab, array('configuration', 'generators', 'templates'), true)) {
        return 'configuration';
    }

    return $tab;
}

function wp_seed_content_kit_render_admin_tabs($current_tab)
{
    $tabs = array(
        'configuration' => __('Configuration générale', 'wp-seed-content-kit'),
        'generators' => __('Générateurs de shortcodes', 'wp-seed-content-kit'),
        'templates' => __('Templates', 'wp-seed-content-kit'),
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

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="wp_seed_content_kit_save_modules">
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

        <?php submit_button(__('Enregistrer la configuration', 'wp-seed-content-kit')); ?>
    </form>
    <?php
}

function wp_seed_content_kit_render_modules_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $current_tab = wp_seed_content_kit_get_current_admin_tab();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WP Seed Content Kit', 'wp-seed-content-kit'); ?></h1>
        <?php wp_seed_content_kit_render_admin_tabs($current_tab); ?>

        <?php if ('generators' === $current_tab) : ?>
            <?php wp_seed_content_kit_render_generators_tab(); ?>
        <?php elseif ('templates' === $current_tab) : ?>
            <?php wp_seed_content_kit_render_templates_tab(); ?>
        <?php else : ?>
            <?php wp_seed_content_kit_render_configuration_tab(); ?>
        <?php endif; ?>
    </div>
    <?php
}

function wp_seed_content_kit_render_templates_tab()
{
    if (!post_type_exists('seed_template')) {
        echo '<p>' . esc_html__('Le type de contenu Templates n’est pas disponible pour le moment.', 'wp-seed-content-kit') . '</p>';
        return;
    }

    $templates = get_posts(array(
        'post_type' => 'seed_template',
        'post_status' => array('publish', 'draft'),
        'posts_per_page' => -1,
        'orderby' => 'modified',
        'order' => 'DESC',
    ));

    $recent_templates = array_slice($templates, 0, 5);
    ?>
    <section class="wp-seed-content-kit-template-dashboard">
        <h2><?php esc_html_e('Créer un template', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Choisissez un module, ajoutez les balises disponibles, puis utilisez le shortcode généré.', 'wp-seed-content-kit'); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url(admin_url('post-new.php?post_type=seed_template')); ?>">
                <?php echo esc_html__('Créer un template', 'wp-seed-content-kit'); ?>
            </a>
            <a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=seed_template')); ?>">
                <?php echo esc_html__('Ouvrir la gestion complète', 'wp-seed-content-kit'); ?>
            </a>
        </p>
    </section>

    <section class="wp-seed-content-kit-template-dashboard">
        <h2><?php esc_html_e('Comment ça marche ?', 'wp-seed-content-kit'); ?></h2>
        <ol>
            <li><?php esc_html_e('Créer un template', 'wp-seed-content-kit'); ?></li>
            <li><?php esc_html_e('Choisir un module', 'wp-seed-content-kit'); ?></li>
            <li><?php esc_html_e('Utiliser les balises', 'wp-seed-content-kit'); ?> : {{photo}}, {{name}}, {{text}}</li>
            <li><?php echo wp_kses_post(__('Utiliser un shortcode, par exemple : <code>[seed_testimonials template="accueil"]</code>', 'wp-seed-content-kit')); ?></li>
        </ol>
        <p><?php esc_html_e('Exemple de module : Témoignages.', 'wp-seed-content-kit'); ?></p>
    </section>

    <h2><?php esc_html_e('Templates récents', 'wp-seed-content-kit'); ?></h2>
    <p><?php esc_html_e('Les derniers templates modifiés.', 'wp-seed-content-kit'); ?></p>

    <?php if (empty($recent_templates)) : ?>
        <p><?php esc_html_e('Aucun template pour le moment.', 'wp-seed-content-kit'); ?></p>
        <?php return; ?>
    <?php endif; ?>

    <table class="widefat striped wp-seed-content-kit-template-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Titre', 'wp-seed-content-kit'); ?></th>
                <th><?php echo esc_html__('Module', 'wp-seed-content-kit'); ?></th>
                <th><?php echo esc_html__('Identifiant', 'wp-seed-content-kit'); ?></th>
                <th><?php echo esc_html__('Actions', 'wp-seed-content-kit'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_templates as $template) : ?>
                <?php
                $module = wp_seed_content_get_template_module($template->ID);
                $module_name = wp_seed_content_get_template_module_name($module);
                if ('' === $module_name) {
                    $module_name = esc_html__('À définir', 'wp-seed-content-kit');
                }
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($template->post_title); ?></strong><br />
                        <small>
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . (int) $template->ID . '&action=edit')); ?>">
                                <?php echo esc_html__('Modifier le template', 'wp-seed-content-kit'); ?>
                            </a>
                        </small>
                    </td>
                    <td><?php echo esc_html($module_name); ?></td>
                    <td>
                        <strong><?php echo esc_html($template->post_name); ?></strong><br />
                        <small><?php echo esc_html__('Identifiant', 'wp-seed-content-kit'); ?></small>
                    </td>
                    <td>
                        <?php echo sprintf(
                            '<a href="%s">%s</a>',
                            esc_url(admin_url('post.php?post=' . (int) $template->ID . '&action=edit')),
                            esc_html__('Modifier', 'wp-seed-content-kit')
                        ); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
