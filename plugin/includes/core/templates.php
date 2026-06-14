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

function wp_seed_content_get_template_layout_source($post_id)
{
    $source = sanitize_key((string) get_post_meta((int) $post_id, '_wp_seed_content_template_source', true));
    if ('divi_layout' === $source) {
        return 'divi_layout';
    }

    return 'native';
}

function wp_seed_content_get_template_divi_layout_id($post_id)
{
    $layout_id = absint(get_post_meta((int) $post_id, '_wp_seed_content_divi_layout_id', true));
    return $layout_id > 0 ? $layout_id : 0;
}

function wp_seed_content_get_seed_template_divi_layouts()
{
    if (!post_type_exists('et_pb_layout')) {
        return array();
    }

    $layouts = get_posts(
        array(
            'post_type' => 'et_pb_layout',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'ids',
        )
    );

    if (empty($layouts) || !is_array($layouts)) {
        return array();
    }

    $result = array();
    foreach ($layouts as $layout_id) {
        $id = absint($layout_id);
        if ($id <= 0) {
            continue;
        }

        $layout = get_post($id);
        if (!$layout || 'et_pb_layout' !== $layout->post_type || 'publish' !== $layout->post_status) {
            continue;
        }

        $result[(string) $id] = get_the_title($layout_id);
    }

    return $result;
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
            'photo_url' => __('URL de la photo', 'wp-seed-content-kit'),
            'name' => __('Nom ou initiales', 'wp-seed-content-kit'),
            'text' => __('Texte du témoignage', 'wp-seed-content-kit'),
            'photo_alt' => __('Texte alternatif de la photo', 'wp-seed-content-kit'),
        );
    }
    if ('quotes' === $module) {
        return array(
            'quote' => __('Citation', 'wp-seed-content-kit'),
            'author' => __('Auteur', 'wp-seed-content-kit'),
            'era' => __('Époque / date affichée', 'wp-seed-content-kit'),
            'source' => __('Source / contexte', 'wp-seed-content-kit'),
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
    $supported_modules = array('testimonials', 'quotes');
    $placeholders_by_module = array();
    $shortcodes_by_module = array();
    foreach ($modules as $module_key => $module_shortcode) {
        $placeholders_by_module[$module_key] = wp_seed_content_get_template_placeholders_by_module($module_key);
        $shortcodes_by_module[$module_key] = wp_seed_content_get_template_shortcode_from_module($module_key);
    }

    $module_data = array();
    foreach ($placeholders_by_module as $module_key => $module_placeholders) {
        $module_data[$module_key] = array(
            'placeholders' => array_keys((array) $module_placeholders),
            'labels' => $module_placeholders,
            'shortcode' => $shortcodes_by_module[$module_key] ?? '',
        );
    }
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
        esc_html_e('Modules fonctionnels : Témoignages, Citations. Modules préparés : Annuaire, Créations sonores.', 'wp-seed-content-kit');
        ?>
    </p>
    <p><strong><?php esc_html_e('Source du rendu', 'wp-seed-content-kit'); ?></strong></p>
    <?php
    $template_source = wp_seed_content_get_template_layout_source($post->ID);
    $template_divi_layout_id = wp_seed_content_get_template_divi_layout_id($post->ID);
    $divi_layouts = wp_seed_content_get_seed_template_divi_layouts();
    $divi_library_available = post_type_exists('et_pb_layout');
    ?>
    <p>
        <label>
            <input type="radio" name="wp_seed_content_template_source" value="native" <?php checked('native', $template_source); ?> />
            <?php esc_html_e('Contenu de ce template', 'wp-seed-content-kit'); ?>
        </label>
        <br />
        <label>
            <input type="radio" name="wp_seed_content_template_source" value="divi_layout" <?php checked('divi_layout', $template_source); ?> />
            <?php esc_html_e('Layout Divi Library', 'wp-seed-content-kit'); ?>
        </label>
    </p>
    <?php if (!$divi_library_available) : ?>
        <p class="description">
            <?php esc_html_e('Divi Library n’est pas disponible. Le rendu utilisera le contenu de ce template si le layout est indisponible.', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
    <p>
        <label for="wp-seed-template-divi-layout-id">
            <strong><?php esc_html_e('Sélectionner un layout Divi', 'wp-seed-content-kit'); ?></strong>
        </label><br />
        <?php if (!empty($divi_layouts)) : ?>
            <select id="wp-seed-template-divi-layout-id" name="wp_seed_content_template_divi_layout_id" class="widefat">
                <option value="0"><?php esc_html_e('— Aucun —', 'wp-seed-content-kit'); ?></option>
                <?php foreach ($divi_layouts as $layout_id => $layout_title) : ?>
                    <option value="<?php echo esc_attr($layout_id); ?>" <?php selected((string) $template_divi_layout_id, (string) $layout_id); ?>>
                        <?php echo esc_html($layout_title); ?> (<?php echo esc_html($layout_id); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="description"><?php esc_html_e('Utilisez les balises WP Seed dans un module Texte ou Code Divi.', 'wp-seed-content-kit'); ?></span><br />
            <span class="description"><?php esc_html_e('Exemples : {{quote}}, {{author}}, {{photo}}, {{name}}, {{text}}', 'wp-seed-content-kit'); ?></span>
        <?php else : ?>
            <span class="description"><?php esc_html_e('Aucun layout Divi publié disponible.', 'wp-seed-content-kit'); ?></span>
        <?php endif; ?>
    </p>
    <p>
        <label for="wp-seed-template-identifier">
            <strong><?php esc_html_e('Identifiant du template', 'wp-seed-content-kit'); ?></strong>
        </label>
        <input
            type="text"
            id="wp-seed-template-identifier"
            name="wp_seed_content_template_identifier"
            value="<?php echo esc_attr($current_slug); ?>"
            class="widefat"
        />
    </p>
    <p class="description">
        <?php esc_html_e('Utilisé dans :', 'wp-seed-content-kit'); ?>
        <code>template="<?php echo esc_html('' !== $current_slug ? $current_slug : 'identifiant'); ?>"</code>
    </p>
    <p class="description" id="wp-seed-template-shortcode-block">
        <strong><?php esc_html_e('Shortcode', 'wp-seed-content-kit'); ?> :</strong><br />
        <code id="wp-seed-template-shortcode" class="wp-seed-template-shortcode" data-slug="<?php echo esc_attr($current_slug); ?>" <?php echo '' === $shortcode_example ? 'style="display:none;"' : ''; ?>><?php echo esc_html($shortcode_example); ?></code>
        <button type="button" class="button button-small wp-seed-content-kit-copy-template" data-shortcode="<?php echo esc_attr($shortcode_example); ?>" <?php echo '' === $shortcode_example ? 'style="display:none;"' : ''; ?>>
            <?php esc_html_e('Copier le shortcode', 'wp-seed-content-kit'); ?>
        </button>
        <span id="wp-seed-template-shortcode-empty" class="description" <?php echo '' !== $shortcode_example ? 'style="display:none;"' : ''; ?>>
            <?php esc_html_e('Choisissez un module et un identifiant pour générer le shortcode.', 'wp-seed-content-kit'); ?>
        </span>
    </p>

    <?php if (!empty($placeholders)) : ?>
        <p class="description">
            <strong><?php esc_html_e('Placeholders disponibles', 'wp-seed-content-kit'); ?></strong><br />
            <span id="wp-seed-template-placeholders">
                <?php foreach ($placeholders as $token => $label) : ?>
                    <span class="wp-seed-template-placeholder-row" style="display:block; margin-bottom:6px;">
                        <code><?php echo esc_html('{{' . $token . '}}'); ?></code>
                        — <?php echo esc_html($label); ?>
                        <button type="button" class="button button-small wp-seed-content-kit-copy-template-placeholder" data-token="<?php echo esc_attr($token); ?>">
                            <?php esc_html_e('Copier', 'wp-seed-content-kit'); ?>
                        </button>
                    </span>
                <?php endforeach; ?>
            </span>
        </p>
        <p class="description">
            <strong><?php esc_html_e('Exemple', 'wp-seed-content-kit'); ?></strong><br />
            <pre style="white-space: pre-wrap; margin: 0;"><?php echo esc_html(wp_seed_content_get_template_example_by_module($current)); ?></pre>
        </p>
        <div id="wp-seed-template-module-data" data-module-meta="<?php echo esc_attr(wp_json_encode($module_data)); ?>"></div>
    <?php else : ?>
        <p class="description">
            <span id="wp-seed-template-placeholders">
                <?php esc_html_e('Aucun placeholder spécifique n’est encore défini pour ce module.', 'wp-seed-content-kit'); ?>
            </span>
        </p>
        <p class="description">
            <strong><?php esc_html_e('Exemple', 'wp-seed-content-kit'); ?></strong><br />
            <pre style="white-space: pre-wrap; margin: 0;"><?php echo esc_html(wp_seed_content_get_template_example_by_module($current)); ?></pre>
        </p>
        <div id="wp-seed-template-module-data" data-module-meta="<?php echo esc_attr(wp_json_encode($module_data)); ?>"></div>
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

    if (isset($_POST['wp_seed_content_template_identifier'])) {
        wp_seed_content_update_template_identifier($post_id, wp_unslash($_POST['wp_seed_content_template_identifier']));
    }

    $module = sanitize_key(wp_unslash($_POST['wp_seed_content_template_module']));
    $modules = array_keys(wp_seed_content_get_template_modules());
    if (!in_array($module, $modules, true)) {
        $module = '';
    }
    $source = isset($_POST['wp_seed_content_template_source']) ? sanitize_key(wp_unslash($_POST['wp_seed_content_template_source'])) : 'native';
    if (!in_array($source, array('native', 'divi_layout'), true)) {
        $source = 'native';
    }
    update_post_meta((int) $post_id, '_wp_seed_content_template_source', $source);

    $layout_id = isset($_POST['wp_seed_content_template_divi_layout_id']) ? absint(wp_unslash($_POST['wp_seed_content_template_divi_layout_id'])) : 0;
    if ($layout_id > 0) {
        update_post_meta((int) $post_id, '_wp_seed_content_divi_layout_id', $layout_id);
    } else {
        update_post_meta((int) $post_id, '_wp_seed_content_divi_layout_id', 0);
    }

    update_post_meta($post_id, '_wp_seed_content_template_module', $module);
}

function wp_seed_content_update_template_identifier($post_id, $raw_identifier)
{
    $new_slug = sanitize_title($raw_identifier);
    if ('' === $new_slug) {
        return;
    }

    $post = get_post($post_id);
    if (!$post || 'seed_template' !== $post->post_type) {
        return;
    }

    if (sanitize_title((string) $post->post_name) === $new_slug) {
        return;
    }

    wp_update_post(
        array(
            'ID' => $post_id,
            'post_name' => $new_slug,
        )
    );
}

function wp_seed_content_render_template_identifier_quick_edit($column_name, $post_type = '', $has_taxonomy = '')
{
    $resolved_post_type = '';

    if (is_string($post_type)) {
        $resolved_post_type = $post_type;
    } elseif (class_exists('WP_Screen') && $post_type instanceof WP_Screen) {
        $resolved_post_type = (string) ($post_type->post_type ?? '');
        if ('' === $resolved_post_type && 'edit-seed_template' === (string) ($post_type->id ?? '')) {
            $resolved_post_type = 'seed_template';
        }
    } else {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ($screen instanceof WP_Screen) {
            $resolved_post_type = (string) ($screen->post_type ?? '');
            if ('' === $resolved_post_type && 'edit-seed_template' === (string) ($screen->id ?? '')) {
                $resolved_post_type = 'seed_template';
            }
        }
    }

    if ('wp_seed_content_template_slug' !== $column_name || 'seed_template' !== $resolved_post_type) {
        return;
    }

    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title"><?php esc_html_e('Identifiant', 'wp-seed-content-kit'); ?></span>
                <span class="input-text-wrap">
                    <input type="text" name="wp_seed_content_template_slug" value="" />
                </span>
            </label>
            <p class="inline-edit-tags">
                <?php
                echo esc_html__('Utilisé dans :', 'wp-seed-content-kit');
                echo ' ';
                echo '<code>template="accueil"</code>';
                ?>
            </p>
        </div>
    </fieldset>
    <?php
}

function wp_seed_content_save_template_identifier_quick_edit($post_id, $post, $update)
{
    if (!isset($_POST['wp_seed_content_template_slug'])) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['_inline_edit']) || !check_admin_referer('inlineeditnonce', '_inline_edit')) {
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

    wp_seed_content_update_template_identifier($post_id, wp_unslash($_POST['wp_seed_content_template_slug']));
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
        printf(
            '<span id="seed-template-inline-slug-%1$d" class="screen-reader-text">%2$s</span>%3$s',
            (int) $post_id,
            esc_attr($post->post_name),
            esc_html($post->post_name)
        );
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

    if ('edit' === $screen->base || 'post' === $screen->base || 'post-new' === $screen->base) {
        $script = <<<'JS'
jQuery(function($){
    function seedCopyToClipboard(value) {
        if (!value) {
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value);
            return;
        }

        var temp = document.createElement('textarea');
        temp.value = value;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }

    $(document).on('click', '.wp-seed-content-kit-copy-template', function (event) {
        event.preventDefault();
        var shortcode = $(this).data('shortcode');
        if (shortcode) {
            seedCopyToClipboard(shortcode);
        }
    });

    $(document).on('click', '.wp-seed-content-kit-copy-template-placeholder', function (event) {
        event.preventDefault();
        var token = $(this).data('token');
        if (token) {
            seedCopyToClipboard('{{' + token + '}}');
        }
    });

    function seedEnsureTemplateQuickEditField($inlineRow) {
        if ($inlineRow.find('input[name="wp_seed_content_template_slug"]').length) {
            return;
        }

        var field = ''
            + '<fieldset class="inline-edit-col-right wp-seed-template-quick-edit-identifier">'
            + '<div class="inline-edit-col">'
            + '<label>'
            + '<span class="title">Identifiant</span>'
            + '<span class="input-text-wrap">'
            + '<input type="text" name="wp_seed_content_template_slug" value="" />'
            + '</span>'
            + '</label>'
            + '<p class="inline-edit-tags">Utilisé dans : <code>template="accueil"</code></p>'
            + '</div>'
            + '</fieldset>';

        var $rightColumn = $inlineRow.find('.inline-edit-col-right').last();
        if ($rightColumn.length) {
            $rightColumn.after(field);
            return;
        }

        $inlineRow.find('.inline-edit-wrapper').append(field);
    }

    function seedUpdateTemplateQuickEditSlug() {
        $(document).off('click.wpSeedTemplateQuickEdit', '.editinline');
        $(document).on('click.wpSeedTemplateQuickEdit', '.editinline', function () {
            var $button = $(this);

            window.setTimeout(function () {
                var rowId = $button.closest('tr').attr('id') || '';
                var match = rowId.match(/post-(\d+)/);
                if (!match || !match[1]) {
                    return;
                }

                var postId = match[1];
                var slug = $('#seed-template-inline-slug-' + postId).text() || '';
                var $inlineRow = $('#edit-' + postId);
                if (!$inlineRow.length) {
                    return;
                }

                seedEnsureTemplateQuickEditField($inlineRow);
                $inlineRow.find('input[name="wp_seed_content_template_slug"]').val(slug);
            }, 0);
        });
    }

    seedUpdateTemplateQuickEditSlug();

    var $moduleSelect = $('#wp-seed-template-module');
    if (!$moduleSelect.length) {
        return;
    }

    var moduleDataRaw = $('#wp-seed-template-module-data').attr('data-module-meta');
    if (!moduleDataRaw) {
        return;
    }

    var moduleData = {};
    try {
        moduleData = JSON.parse(moduleDataRaw);
    } catch (error) {
        moduleData = {};
    }

    var $shortcodeBlock = $('#wp-seed-template-shortcode-block');
    var $shortcode = $('#wp-seed-template-shortcode');
    var $shortcodeEmpty = $('#wp-seed-template-shortcode-empty');
    var $placeholders = $('#wp-seed-template-placeholders');
    var slug = $shortcode.data('slug') || '';

    function updateTemplateMetaUI(module) {
        var normalized = (module || '').toString();
        var data = moduleData[normalized] || null;

        if (!normalized || !data || !data.shortcode) {
            if ($shortcode.length) {
                $shortcode.text('').hide();
                $shortcodeBlock.find('.wp-seed-content-kit-copy-template').hide();
            }

            if ($shortcodeEmpty.length) {
                $shortcodeEmpty.show();
            }

            if ($placeholders.length) {
                $placeholders.html('Aucun placeholder disponible pour ce module.');
            }
            return;
        }

        var generated = data.shortcode.replace('%s', slug);
        if ($shortcode.length) {
            $shortcode.text(generated).attr('data-shortcode', generated).show();
            $shortcodeBlock.find('.wp-seed-content-kit-copy-template').attr('data-shortcode', generated).show();
        }

        if ($shortcodeEmpty.length) {
            $shortcodeEmpty.hide();
        }

        if (!$placeholders.length) {
            return;
        }

        var placeholders = data.placeholders || [];
        if (!placeholders.length) {
            $placeholders.html('Aucun placeholder spécifique n’est encore défini pour ce module.');
            return;
        }

        var rows = '';
        placeholders.forEach(function(token) {
            var label = data.labels && data.labels[token] ? data.labels[token] : token;
            rows += '<span class="wp-seed-template-placeholder-row" style="display:block; margin-bottom:6px;">';
            rows += '<code>{{' + token + '}}</code> - ' + label;
            rows += ' <button type="button" class="button button-small wp-seed-content-kit-copy-template-placeholder" data-token="' + token + '">Copier</button>';
            rows += '</span>';
        });
        $placeholders.html(rows);
    }

    $moduleSelect.on('change', function () {
        updateTemplateMetaUI($(this).val());
    });

    updateTemplateMetaUI($moduleSelect.val());
});
JS;

        wp_add_inline_script(
            'jquery',
            $script
        );
    }
}

add_action('admin_init', 'wp_seed_content_seed_template_init_admin_columns');

function wp_seed_content_get_template_example_by_module($module)
{
    $module = sanitize_key($module);

    if ('quotes' === $module) {
        return "<div class=\"quote-template\">\n{{quote}}\n<p>{{author}}</p>\n<p>{{source}}</p>\n<p>{{era}}</p>\n</div>";
    }

    return "<div class=\"testimonial-template\">\n<img src=\"{{photo_url}}\" alt=\"{{photo_alt}}\">\n<h3>{{name}}</h3>\n<p>{{text}}</p>\n</div>";
}
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
    add_action('save_post_seed_template', 'wp_seed_content_save_template_identifier_quick_edit', 20, 3);
    add_action('quick_edit_custom_box', 'wp_seed_content_render_template_identifier_quick_edit', 10, 3);
    add_action('admin_enqueue_scripts', 'wp_seed_content_enqueue_template_admin_scripts');
    add_action('manage_seed_template_posts_extra_tablenav', 'wp_seed_content_seed_template_list_help', 10, 1);
}
