<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_register_template_post_type()
{
    $labels = array(
        'name' => __('Templates', 'wp-seed-content-kit'),
        'singular_name' => __('Template', 'wp-seed-content-kit'),
        'menu_name' => __('Templates', 'wp-seed-content-kit'),
        'add_new' => __('Ajouter', 'wp-seed-content-kit'),
        'add_new_item' => __('Ajouter un template', 'wp-seed-content-kit'),
        'edit_item' => __('Modifier le template', 'wp-seed-content-kit'),
        'new_item' => __('Nouveau template', 'wp-seed-content-kit'),
        'view_item' => __('Voir le template', 'wp-seed-content-kit'),
        'search_items' => __('Rechercher des templates', 'wp-seed-content-kit'),
        'not_found' => __('Aucun template trouvé', 'wp-seed-content-kit'),
        'not_found_in_trash' => __('Aucun template trouvé dans la corbeille', 'wp-seed-content-kit'),
        'all_items' => __('Templates', 'wp-seed-content-kit'),
        'items_list' => __('Liste des templates', 'wp-seed-content-kit'),
    );

    register_post_type('seed_template', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'revisions'),
        'capability_type' => 'post',
        'rewrite' => false,
    ));
}

function wp_seed_content_get_template_modules()
{
    return array(
        'testimonials' => 'seed_testimonials',
        'quotes' => 'seed_quotes',
        'directory' => 'seed_directory',
        'audio' => 'seed_audio',
    );
}

function wp_seed_content_get_template_shortcode_from_module($module)
{
    $modules = wp_seed_content_get_template_modules();
    $module = sanitize_key($module);
    if (!isset($modules[$module])) {
        return '';
    }

    return '[' . $modules[$module] . ' template="%s"]';
}

function wp_seed_content_get_template_module_name($module)
{
    $labels = array(
        'testimonials' => __('Témoignages', 'wp-seed-content-kit'),
        'quotes' => __('Citations', 'wp-seed-content-kit'),
        'directory' => __('Annuaire', 'wp-seed-content-kit'),
        'audio' => __('Créations sonores', 'wp-seed-content-kit'),
    );

    return $labels[$module] ?? __('Module non défini', 'wp-seed-content-kit');
}

function wp_seed_content_get_template_placeholders_by_module($module)
{
    $module = sanitize_key($module);
    if ('testimonials' === $module) {
        return array(
            'photo' => __('Photo du témoignage', 'wp-seed-content-kit'),
            'name' => __('Nom ou initiales', 'wp-seed-content-kit'),
            'text' => __('Texte du témoignage', 'wp-seed-content-kit'),
        );
    }

    return array();
}

function wp_seed_content_get_template_module($post_id)
{
    $module = get_post_meta($post_id, '_wp_seed_content_template_module', true);
    if (!is_string($module) || '' === trim($module)) {
        $module = get_post_meta($post_id, '_seed_template_module', true);
    }

    if (!is_string($module)) {
        return '';
    }

    $module = sanitize_key($module);
    if ('' === $module) {
        return '';
    }

    $modules = wp_seed_content_get_template_modules();
    return isset($modules[$module]) ? $module : '';
}

function wp_seed_content_template_shortcode_for_post($post_id)
{
    $module = wp_seed_content_get_template_module($post_id);
    if ('' === $module) {
        return '';
    }

    $pattern = wp_seed_content_get_template_shortcode_from_module($module);
    if ('' === $pattern) {
        return '';
    }

    $slug = get_post_field('post_name', $post_id, 'raw');
    $slug = sanitize_title($slug);
    if ('' === $slug) {
        return '';
    }

    return sprintf($pattern, $slug);
}

function wp_seed_content_render_template_module_meta_box($post)
{
    if (!$post || 'seed_template' !== $post->post_type) {
        return;
    }

    $current = wp_seed_content_get_template_module($post->ID);
    $modules = wp_seed_content_get_template_modules();
    $current_slug = sanitize_title((string) get_post_field('post_name', $post->ID, 'raw'));
    $shortcode_pattern = wp_seed_content_get_template_shortcode_from_module($current);
    $shortcode_example = '';
    if ('' !== $shortcode_pattern && '' !== $current_slug) {
        $shortcode_example = sprintf($shortcode_pattern, $current_slug);
    }
    $placeholders = wp_seed_content_get_template_placeholders_by_module($current);
    $supported_modules = array('testimonials');
    ?>
    <p><strong><?php esc_html_e('Utilisation du template', 'wp-seed-content-kit'); ?></strong></p>
    <input type="hidden" name="wp_seed_content_template_meta_nonce" value="<?php echo esc_attr(wp_create_nonce('wp_seed_content_template_meta')); ?>" />
    <p>
        <label for="wp-seed-template-module">
            <strong><?php esc_html_e('Module du template', 'wp-seed-content-kit'); ?></strong>
        </label>
    </p>
    <p class="description">
        <?php esc_html_e('Choisissez le module pour générer le shortcode d’utilisation.', 'wp-seed-content-kit'); ?>
    </p>
    <select id="wp-seed-template-module" name="wp_seed_content_template_module">
        <option value=""><?php esc_html_e('Non défini', 'wp-seed-content-kit'); ?></option>
<?php
    foreach ($modules as $key => $shortcode) {
        if (!is_string($key)) {
            continue;
        }
        $label = wp_seed_content_get_template_module_name($key);
        if (!in_array($key, $supported_modules, true)) {
            $label = sprintf(
                /* translators: %s: module name */
                __('%s (prévu)', 'wp-seed-content-kit'),
                $label
            );
        }
        $selected = selected($current, $key, false);
?>
        <option value="<?php echo esc_attr($key); ?>" <?php echo $selected; ?>>
            <?php echo esc_html($label); ?>
        </option>
<?php
    }
?>
    </select>
    <p class="description">
        <?php
        esc_html_e('Modules fonctionnels : Témoignages. Modules préparés : Citations, Annuaire, Créations sonores.', 'wp-seed-content-kit');
        ?>
    </p>
    <p class="description">
        <strong><?php esc_html_e('Identifiant du template', 'wp-seed-content-kit'); ?> :</strong>
        <code><?php echo esc_html($current_slug); ?></code>
    </p>
    <p class="description">
        <?php
        echo wp_kses_post(
            sprintf(
                /* translators: %s: shortcode attribute name */
                __('Le shortcode attendu est : %s', 'wp-seed-content-kit'),
                '<code>' . esc_html__('template="identifiant"', 'wp-seed-content-kit') . '</code>'
            )
        );
        ?>
    </p>
    <?php if ('' !== $shortcode_example) : ?>
        <p class="description">
            <strong><?php esc_html_e('Shortcode', 'wp-seed-content-kit'); ?> :</strong><br />
            <code class="wp-seed-template-shortcode"><?php echo esc_html($shortcode_example); ?></code>
            <button type="button" class="button button-small wp-seed-content-kit-copy-template" data-shortcode="<?php echo esc_attr($shortcode_example); ?>">
                <?php esc_html_e('Copier le shortcode', 'wp-seed-content-kit'); ?>
            </button>
        </p>
    <?php endif; ?>
    <p class="description">
        <?php esc_html_e('Pour modifier l’identifiant, utilisez le permalien “Identifiant” de l’écran du template.', 'wp-seed-content-kit'); ?>
    </p>
    <?php if (!empty($placeholders)) : ?>
        <p class="description">
            <strong><?php esc_html_e('Placeholders disponibles', 'wp-seed-content-kit'); ?></strong><br />
            <?php foreach ($placeholders as $token => $label) : ?>
                <code><?php echo esc_html('{{' . $token . '}}'); ?></code> — <?php echo esc_html($label); ?><br />
            <?php endforeach; ?>
        </p>
        <p class="description">
            <strong><?php esc_html_e('Exemple', 'wp-seed-content-kit'); ?></strong><br />
            <pre style="white-space: pre-wrap; margin: 0;"><?php echo esc_html("<div class=\"testimonial\">\n{{photo}}\n<h3>{{name}}</h3>\n<p>{{text}}</p>\n</div>"); ?></pre>
        </p>
    <?php else : ?>
        <p class="description">
            <?php esc_html_e('Aucun placeholder spécifique n’est encore défini pour ce module.', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
<?php
}

function wp_seed_content_save_template_module($post_id, $post, $update)
{
    if (!isset($_POST['wp_seed_content_template_meta_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(wp_unslash($_POST['wp_seed_content_template_meta_nonce']), 'wp_seed_content_template_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if ('seed_template' !== get_post_type($post_id)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['wp_seed_content_template_module'])) {
        return;
    }

    $module = sanitize_key(wp_unslash($_POST['wp_seed_content_template_module']));
    $modules = array_keys(wp_seed_content_get_template_modules());
    if (!in_array($module, $modules, true)) {
        $module = '';
    }

    update_post_meta($post_id, '_wp_seed_content_template_module', $module);
}

function wp_seed_content_seed_template_columns($columns)
{
    $columns['wp_seed_content_template_module'] = __('Module', 'wp-seed-content-kit');
    $columns['wp_seed_content_template_slug'] = __('Identifiant', 'wp-seed-content-kit');
    return $columns;
}

function wp_seed_content_seed_template_column_content($column, $post_id)
{
    if ('wp_seed_content_template_module' === $column) {
        echo esc_html(wp_seed_content_get_template_module_name(wp_seed_content_get_template_module($post_id)));
        return;
    }

    if ('wp_seed_content_template_slug' === $column) {
        $post = get_post($post_id);
        if (!$post) {
            echo '&nbsp;';
            return;
        }
        echo esc_html($post->post_name);
        return;
    }

}

function wp_seed_content_seed_template_list_help($which)
{
    if ('top' !== $which) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'edit-seed_template' !== $screen->id) {
        return;
    }

    if ('seed_template' !== ($screen->post_type ?? '')) {
        return;
    }

    echo '<div class="notice inline notice-info notice-alt" style="margin:8px 0 10px;">';
    echo '<p>' . esc_html__('L’identifiant apparaît dans le shortcode via template="identifiant".', 'wp-seed-content-kit') . '</p>';
    echo '<p>' . esc_html__('Exemples : testimonial-home, testimonial-list, quote-home, directory-card.', 'wp-seed-content-kit') . '</p>';
    echo '</div>';
}

function wp_seed_content_enqueue_template_admin_scripts($hook)
{
    if (!is_admin()) {
        return;
    }

    if ('edit.php' !== $hook && 'post-new.php' !== $hook && 'post.php' !== $hook) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || 'seed_template' !== $screen->post_type) {
        return;
    }

    if ('edit' === $screen->base) {
        wp_add_inline_script(
            'jquery',
            "jQuery(function($){\n                $('.wp-seed-content-kit-copy-template').on('click', function(event){\n                    event.preventDefault();\n                    var shortcode = $(this).data('shortcode');\n                    if (!shortcode) {\n                        return;\n                    }\n                    if (navigator.clipboard && navigator.clipboard.writeText) {\n                        navigator.clipboard.writeText(shortcode);\n                        return;\n                    }\n                    var temp = document.createElement('textarea');\n                    temp.value = shortcode;\n                    document.body.appendChild(temp);\n                    temp.select();\n                    document.execCommand('copy');\n                    document.body.removeChild(temp);\n                });\n            });"
        );
    }
}

add_action('admin_init', 'wp_seed_content_seed_template_init_admin_columns');
function wp_seed_content_seed_template_init_admin_columns()
{
    add_filter('manage_seed_template_posts_columns', 'wp_seed_content_seed_template_columns');
    add_action('manage_seed_template_posts_custom_column', 'wp_seed_content_seed_template_column_content', 10, 2);

    add_action('add_meta_boxes', function () {
        add_meta_box(
            'wp-seed-content-kit-template-module',
            __('Utilisation du template', 'wp-seed-content-kit'),
            'wp_seed_content_render_template_module_meta_box',
            'seed_template',
            'side',
            'default'
        );
    });

    add_action('save_post_seed_template', 'wp_seed_content_save_template_module', 10, 3);
    add_action('admin_enqueue_scripts', 'wp_seed_content_enqueue_template_admin_scripts');
    add_action('manage_seed_template_posts_extra_tablenav', 'wp_seed_content_seed_template_list_help', 10, 1);
}
