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

function wp_seed_content_is_divi_available_for_template_admin()
{
    if (function_exists('wp_seed_content_is_divi_active')) {
        return (bool) wp_seed_content_is_divi_active();
    }

    return post_type_exists('et_pb_layout');
}

function wp_seed_content_get_divi_layout_type_label($layout_id)
{
    $layout_id = absint($layout_id);
    if ($layout_id <= 0 || !taxonomy_exists('layout_type')) {
        return '';
    }

    $terms = wp_get_post_terms($layout_id, 'layout_type', array('fields' => 'names'));
    if (is_wp_error($terms) || empty($terms)) {
        return '';
    }

    return implode(', ', array_map('sanitize_text_field', (array) $terms));
}

function wp_seed_content_get_divi_layout_status_label($layout_id)
{
    $status = get_post_status($layout_id);
    if (false === $status || '' === $status) {
        return '';
    }

    if ('publish' === $status) {
        return __('Publié', 'wp-seed-content-kit');
    }

    if ('draft' === $status) {
        return __('Brouillon', 'wp-seed-content-kit');
    }

    $status_object = get_post_status_object($status);
    if ($status_object && !empty($status_object->label)) {
        return $status_object->label;
    }

    return sanitize_text_field((string) $status);
}

function wp_seed_content_render_template_divi_guidance_notice($divi_detected)
{
    if (!$divi_detected) {
        return;
    }
    ?>
    <div class="notice notice-info inline" style="margin: 10px 0;">
        <p><strong><?php esc_html_e('ℹ️ Utilisation avec Divi', 'wp-seed-content-kit'); ?></strong></p>
        <p>
            <?php esc_html_e('Avec Divi, ce template reste le point d’entrée WP Seed.', 'wp-seed-content-kit'); ?>
        </p>
        <p>
            <?php esc_html_e('La mise en page est définie dans un layout Divi Library. Créez ou ouvrez un layout Divi, ajoutez les balises WP Seed dans un module Texte ou Code, puis sélectionnez ce layout ici.', 'wp-seed-content-kit'); ?>
        </p>
    </div>
    <?php
}

function wp_seed_content_render_selected_divi_layout_summary($layout_id)
{
    $layout_id = absint($layout_id);
    $layout = $layout_id > 0 ? get_post($layout_id) : null;

    if (!$layout || 'et_pb_layout' !== $layout->post_type) {
        ?>
        <p class="description" data-wp-seed-divi-layout-empty>
            <strong><?php esc_html_e('Aucun layout Divi sélectionné.', 'wp-seed-content-kit'); ?></strong><br />
            <?php esc_html_e('Créez un layout dans Divi Library puis sélectionnez-le ici.', 'wp-seed-content-kit'); ?>
        </p>
        <div class="wp-seed-template-divi-layout-summary" data-wp-seed-divi-layout-summary style="display:none; margin: 10px 0;">
            <p style="margin-bottom: 4px;">
                <strong><?php esc_html_e('Layout sélectionné :', 'wp-seed-content-kit'); ?></strong><br />
                <span data-wp-seed-divi-layout-title></span>
            </p>
            <p style="margin-bottom: 4px;">
                <strong><?php esc_html_e('Type :', 'wp-seed-content-kit'); ?></strong><br />
                <span data-wp-seed-divi-layout-type></span>
            </p>
            <p style="margin-bottom: 4px;">
                <strong><?php esc_html_e('Statut :', 'wp-seed-content-kit'); ?></strong><br />
                <span data-wp-seed-divi-layout-status></span>
            </p>
        </div>
        <?php
        return;
    }

    $layout_type = wp_seed_content_get_divi_layout_type_label($layout_id);
    $layout_status = wp_seed_content_get_divi_layout_status_label($layout_id);
    ?>
    <p class="description" data-wp-seed-divi-layout-empty style="display:none;">
        <strong><?php esc_html_e('Aucun layout Divi sélectionné.', 'wp-seed-content-kit'); ?></strong><br />
        <?php esc_html_e('Créez un layout dans Divi Library puis sélectionnez-le ici.', 'wp-seed-content-kit'); ?>
    </p>
    <div class="wp-seed-template-divi-layout-summary" data-wp-seed-divi-layout-summary style="margin: 10px 0;">
        <p style="margin-bottom: 4px;">
            <strong><?php esc_html_e('Layout sélectionné :', 'wp-seed-content-kit'); ?></strong><br />
            <span data-wp-seed-divi-layout-title><?php echo esc_html(get_the_title($layout)); ?></span>
        </p>
        <p style="margin-bottom: 4px;">
            <strong><?php esc_html_e('Type :', 'wp-seed-content-kit'); ?></strong><br />
            <span data-wp-seed-divi-layout-type><?php echo esc_html('' !== $layout_type ? $layout_type : __('Non défini', 'wp-seed-content-kit')); ?></span>
        </p>
        <p style="margin-bottom: 4px;">
            <strong><?php esc_html_e('Statut :', 'wp-seed-content-kit'); ?></strong><br />
            <span data-wp-seed-divi-layout-status><?php echo esc_html('' !== $layout_status ? $layout_status : __('Non défini', 'wp-seed-content-kit')); ?></span>
        </p>
    </div>
    <?php
}

function wp_seed_content_get_template_modules()
{
    $modules = array(
        'testimonials' => 'seed_testimonials',
        'quotes' => 'seed_quotes',
        'directory' => 'seed_directory',
        'audio' => 'seed_audio',
    );

    if (function_exists('wp_seed_content_kit_get_registered_template_modules')) {
        foreach (wp_seed_content_kit_get_registered_template_modules() as $module => $definition) {
            $modules[$module] = isset($definition['shortcode']) ? (string) $definition['shortcode'] : '';
        }
    }

    return $modules;
}

function wp_seed_content_get_template_shortcode_from_module($module)
{
    $modules = wp_seed_content_get_template_modules();
    $module = sanitize_key($module);
    if (!isset($modules[$module]) || '' === $modules[$module]) {
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

    if (function_exists('wp_seed_content_kit_get_registered_template_module')) {
        $definition = wp_seed_content_kit_get_registered_template_module($module);
        if (is_array($definition) && !empty($definition['label'])) {
            return $definition['label'];
        }
    }

    if (isset($labels[$module])) {
        return $labels[$module];
    }

    return __('Module non défini', 'wp-seed-content-kit');
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
            'context' => __('Information complémentaire', 'wp-seed-content-kit'),
            'date' => __('Date du témoignage', 'wp-seed-content-kit'),
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

    if (function_exists('wp_seed_content_kit_get_registered_template_placeholders')) {
        $labels = array();
        foreach (wp_seed_content_kit_get_registered_template_placeholders($module) as $key => $definition) {
            $labels[$key] = isset($definition['label']) ? $definition['label'] : $key;
        }
        return $labels;
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

function wp_seed_content_get_template_supported_modules()
{
    $modules = array('testimonials', 'quotes');
    if (function_exists('wp_seed_content_kit_get_registered_template_modules')) {
        $modules = array_merge($modules, array_keys(wp_seed_content_kit_get_registered_template_modules()));
    }

    return array_values(array_unique($modules));
}

function wp_seed_content_get_template_module_data()
{
    $module_data = array();
    foreach (wp_seed_content_get_template_modules() as $module_key => $module_shortcode) {
        if (!is_string($module_key)) {
            continue;
        }

        $placeholders = wp_seed_content_get_template_placeholders_by_module($module_key);
        $module_data[$module_key] = array(
            'placeholders' => array_keys((array) $placeholders),
            'labels' => $placeholders,
            'shortcode' => wp_seed_content_get_template_shortcode_from_module($module_key),
            'example' => wp_seed_content_get_template_example_by_module($module_key),
        );
    }

    return $module_data;
}

function wp_seed_content_get_template_usage_context($post)
{
    if (!$post || 'seed_template' !== $post->post_type) {
        return array();
    }

    $module = wp_seed_content_get_template_module($post->ID);
    $slug = sanitize_title((string) get_post_field('post_name', $post->ID, 'raw'));
    $shortcode = wp_seed_content_template_shortcode_for_post($post->ID);
    $placeholders = wp_seed_content_get_template_placeholders_by_module($module);
    $source = wp_seed_content_get_template_layout_source($post->ID);
    $divi_layout_id = wp_seed_content_get_template_divi_layout_id($post->ID);
    $divi_library_available = post_type_exists('et_pb_layout');
    $divi_detected = wp_seed_content_is_divi_available_for_template_admin();
    $divi_layouts = wp_seed_content_get_seed_template_divi_layouts();

    return array(
        'post_id' => (int) $post->ID,
        'module' => $module,
        'modules' => wp_seed_content_get_template_modules(),
        'supported_modules' => wp_seed_content_get_template_supported_modules(),
        'module_data' => wp_seed_content_get_template_module_data(),
        'slug' => $slug,
        'shortcode' => $shortcode,
        'placeholders' => $placeholders,
        'example' => wp_seed_content_get_template_example_by_module($module),
        'source' => $source,
        'divi_layout_id' => $divi_layout_id,
        'divi_library_available' => $divi_library_available,
        'divi_detected' => $divi_detected,
        'divi_layouts' => $divi_layouts,
    );
}

function wp_seed_content_render_template_module_meta_box($post)
{
    $context = wp_seed_content_get_template_usage_context($post);
    if (empty($context)) {
        return;
    }

    $current = $context['module'];
    $modules = $context['modules'];
    $current_slug = $context['slug'];
    $supported_modules = $context['supported_modules'];
    $template_source = $context['source'];
    $template_divi_layout_id = $context['divi_layout_id'];
    $divi_layouts = $context['divi_layouts'];
    $divi_library_available = $context['divi_library_available'];
    $divi_detected = $context['divi_detected'];
    ?>
    <p><strong><?php esc_html_e('Réglages du template', 'wp-seed-content-kit'); ?></strong></p>
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
        <?php esc_html_e('Utilisé dans le shortcode :', 'wp-seed-content-kit'); ?>
        <code>template="<?php echo esc_html('' !== $current_slug ? $current_slug : 'identifiant'); ?>"</code>
    </p>

    <p><strong><?php esc_html_e('Source du rendu', 'wp-seed-content-kit'); ?></strong></p>
    <p>
        <label>
            <input type="radio" name="wp_seed_content_template_source" value="native" <?php checked('native', $template_source); ?> />
            <?php esc_html_e('Contenu de ce template', 'wp-seed-content-kit'); ?>
        </label>
        <br />
        <span class="description"><?php esc_html_e('Utilise le contenu saisi dans cet éditeur WordPress.', 'wp-seed-content-kit'); ?></span>
        <br />
        <label>
            <input type="radio" name="wp_seed_content_template_source" value="divi_layout" <?php checked('divi_layout', $template_source); ?> />
            <?php esc_html_e('Layout Divi Library', 'wp-seed-content-kit'); ?>
        </label>
        <br />
        <span class="description"><?php esc_html_e('Utilise un layout créé dans Divi Library.', 'wp-seed-content-kit'); ?></span>
    </p>
    <?php if (!$divi_library_available) : ?>
        <p class="description">
            <?php esc_html_e('Divi Library n’est pas disponible. Le rendu utilisera le contenu de ce template si le layout est indisponible.', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
    <div data-wp-seed-divi-layout-settings <?php echo 'divi_layout' !== $template_source ? 'style="display:none;"' : ''; ?>>
        <?php wp_seed_content_render_template_divi_guidance_notice($divi_detected); ?>
        <p>
            <label for="wp-seed-template-divi-layout-id">
                <strong><?php esc_html_e('Sélectionner un layout Divi', 'wp-seed-content-kit'); ?></strong>
            </label><br />
        </p>
        <?php if (!empty($divi_layouts)) : ?>
            <select id="wp-seed-template-divi-layout-id" name="wp_seed_content_template_divi_layout_id" class="widefat">
                <option value="0"><?php esc_html_e('— Aucun —', 'wp-seed-content-kit'); ?></option>
                <?php foreach ($divi_layouts as $layout_id => $layout_title) : ?>
                    <?php
                    $layout_type = wp_seed_content_get_divi_layout_type_label($layout_id);
                    $layout_status = wp_seed_content_get_divi_layout_status_label($layout_id);
                    ?>
                    <option
                        value="<?php echo esc_attr($layout_id); ?>"
                        data-layout-title="<?php echo esc_attr($layout_title); ?>"
                        data-layout-type="<?php echo esc_attr('' !== $layout_type ? $layout_type : __('Non défini', 'wp-seed-content-kit')); ?>"
                        data-layout-status="<?php echo esc_attr('' !== $layout_status ? $layout_status : __('Non défini', 'wp-seed-content-kit')); ?>"
                        <?php selected((string) $template_divi_layout_id, (string) $layout_id); ?>
                    >
                        <?php echo esc_html($layout_title); ?> (<?php echo esc_html($layout_id); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php wp_seed_content_render_selected_divi_layout_summary($template_divi_layout_id); ?>
            <?php wp_seed_content_render_template_divi_actions($template_divi_layout_id); ?>
        <?php else : ?>
            <input type="hidden" name="wp_seed_content_template_divi_layout_id" value="<?php echo esc_attr((string) $template_divi_layout_id); ?>" />
            <p class="description">
                <strong><?php esc_html_e('Aucun layout Divi sélectionné.', 'wp-seed-content-kit'); ?></strong><br />
                <?php esc_html_e('Aucun layout Divi publié disponible. Créez un layout dans Divi Library puis sélectionnez-le ici.', 'wp-seed-content-kit'); ?>
            </p>
            <?php wp_seed_content_render_template_divi_actions($template_divi_layout_id); ?>
        <?php endif; ?>
    </div>
<?php
}

function wp_seed_content_render_template_divi_actions($layout_id)
{
    if (!post_type_exists('et_pb_layout')) {
        return;
    }

    $layout_id = absint($layout_id);
    ?>
    <p>
        <a class="button button-small" href="<?php echo esc_url(admin_url('post-new.php?post_type=et_pb_layout')); ?>">
            <?php esc_html_e('Créer un layout Divi', 'wp-seed-content-kit'); ?>
        </a>
        <a class="button button-small" href="<?php echo esc_url(admin_url('edit.php?post_type=et_pb_layout')); ?>">
            <?php esc_html_e('Gérer les layouts Divi', 'wp-seed-content-kit'); ?>
        </a>
        <?php if ($layout_id > 0) : ?>
            <a class="button button-small" href="<?php echo esc_url(admin_url('post.php?post=' . $layout_id . '&action=edit')); ?>">
                <?php esc_html_e('Ouvrir le layout sélectionné', 'wp-seed-content-kit'); ?>
            </a>
        <?php endif; ?>
    </p>
    <?php
}

function wp_seed_content_render_template_usage_meta_box($post)
{
    $context = wp_seed_content_get_template_usage_context($post);
    if (empty($context)) {
        return;
    }

    $module_data_json = wp_json_encode($context['module_data']);
    ?>
    <div
        class="wp-seed-template-usage"
        data-wp-seed-template-usage
        data-module-meta="<?php echo esc_attr($module_data_json); ?>"
        data-template-slug="<?php echo esc_attr($context['slug']); ?>"
    >
        <p>
            <?php esc_html_e('Le shortcode choisit les contenus à afficher. Ce template choisit leur mise en forme.', 'wp-seed-content-kit'); ?>
        </p>
        <p>
            <strong><?php esc_html_e('Shortcode', 'wp-seed-content-kit'); ?></strong><br />
            <code data-wp-seed-template-shortcode <?php echo '' === $context['shortcode'] ? 'style="display:none;"' : ''; ?>><?php echo esc_html($context['shortcode']); ?></code>
            <button
                type="button"
                class="button button-small"
                data-wp-seed-copy-value="<?php echo esc_attr($context['shortcode']); ?>"
                data-wp-seed-template-copy-shortcode
                <?php echo '' === $context['shortcode'] ? 'style="display:none;"' : ''; ?>
            >
                <?php esc_html_e('Copier le shortcode', 'wp-seed-content-kit'); ?>
            </button>
            <span data-wp-seed-template-shortcode-empty class="description" <?php echo '' !== $context['shortcode'] ? 'style="display:none;"' : ''; ?>>
                <?php esc_html_e('Choisissez un module et un identifiant pour générer le shortcode.', 'wp-seed-content-kit'); ?>
            </span>
        </p>
        <p>
            <strong><?php esc_html_e('Identifiant', 'wp-seed-content-kit'); ?></strong><br />
            <code><?php echo esc_html('' !== $context['slug'] ? $context['slug'] : 'identifiant'); ?></code>
        </p>
        <p>
            <strong><?php esc_html_e('Balises disponibles', 'wp-seed-content-kit'); ?></strong><br />
            <span data-wp-seed-template-placeholders>
                <?php wp_seed_content_render_template_placeholder_rows($context['placeholders']); ?>
            </span>
        </p>
        <p>
            <strong><?php esc_html_e('Exemple', 'wp-seed-content-kit'); ?></strong><br />
            <pre data-wp-seed-template-example style="white-space: pre-wrap; margin: 0;"><?php echo esc_html($context['example']); ?></pre>
        </p>
        <p class="description">
            <?php esc_html_e('Source actuelle :', 'wp-seed-content-kit'); ?>
            <span data-wp-seed-template-source-label>
                <?php echo esc_html('divi_layout' === $context['source'] ? __('Layout Divi Library', 'wp-seed-content-kit') : __('Contenu de ce template', 'wp-seed-content-kit')); ?>
            </span>
        </p>
    </div>
    <?php
}

function wp_seed_content_render_template_placeholder_rows($placeholders)
{
    if (empty($placeholders)) {
        esc_html_e('Aucune balise spécifique n’est encore définie pour ce module.', 'wp-seed-content-kit');
        return;
    }

    foreach ($placeholders as $token => $label) :
        $placeholder = '{{' . $token . '}}';
        ?>
        <span class="wp-seed-template-placeholder-row" style="display:block; margin-bottom:6px;">
            <code><?php echo esc_html($placeholder); ?></code>
            — <?php echo esc_html($label); ?>
            <button type="button" class="button button-small" data-wp-seed-copy-value="<?php echo esc_attr($placeholder); ?>">
                <?php esc_html_e('Copier', 'wp-seed-content-kit'); ?>
            </button>
        </span>
        <?php
    endforeach;
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

function wp_seed_content_get_primary_template_meta_box_ids()
{
    return array(
        'wp-seed-content-kit-template-module',
        'wp-seed-content-kit-template-usage',
    );
}

function wp_seed_content_keep_primary_template_meta_boxes_open($closed)
{
    if (!is_array($closed)) {
        return $closed;
    }

    return array_values(array_diff($closed, wp_seed_content_get_primary_template_meta_box_ids()));
}

function wp_seed_content_open_primary_template_postbox_classes($classes)
{
    if (!is_array($classes)) {
        return $classes;
    }

    return array_values(array_diff($classes, array('closed')));
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
    $('#wp-seed-content-kit-template-module, #wp-seed-content-kit-template-usage').removeClass('closed');

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

    function seedEscapeHtml(value) {
        return $('<div>').text(value || '').html();
    }

    $(document).on('click', '[data-wp-seed-copy-value]', function (event) {
        event.preventDefault();
        var value = $(this).attr('data-wp-seed-copy-value') || '';
        if (value) {
            seedCopyToClipboard(value);
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

    var $usage = $('[data-wp-seed-template-usage]').first();
    var moduleDataRaw = $usage.attr('data-module-meta');
    if (!moduleDataRaw) {
        return;
    }

    var moduleData = {};
    try {
        moduleData = JSON.parse(moduleDataRaw);
    } catch (error) {
        moduleData = {};
    }

    var $shortcode = $usage.find('[data-wp-seed-template-shortcode]');
    var $copyShortcode = $usage.find('[data-wp-seed-template-copy-shortcode]');
    var $shortcodeEmpty = $usage.find('[data-wp-seed-template-shortcode-empty]');
    var $placeholders = $usage.find('[data-wp-seed-template-placeholders]');
    var $example = $usage.find('[data-wp-seed-template-example]');
    var $sourceLabel = $usage.find('[data-wp-seed-template-source-label]');
    var $diviLayoutSettings = $('[data-wp-seed-divi-layout-settings]');
    var $diviLayoutSelect = $('#wp-seed-template-divi-layout-id');
    var $diviLayoutEmpty = $('[data-wp-seed-divi-layout-empty]');
    var $diviLayoutSummary = $('[data-wp-seed-divi-layout-summary]');
    var $diviLayoutTitle = $('[data-wp-seed-divi-layout-title]');
    var $diviLayoutType = $('[data-wp-seed-divi-layout-type]');
    var $diviLayoutStatus = $('[data-wp-seed-divi-layout-status]');
    var slug = $usage.attr('data-template-slug') || '';

    function updateTemplateMetaUI(module) {
        var normalized = (module || '').toString();
        var data = moduleData[normalized] || null;

        if (!normalized || !data || !data.shortcode) {
            if ($shortcode.length) {
                $shortcode.text('').hide();
                $copyShortcode.attr('data-wp-seed-copy-value', '').hide();
            }

            if ($shortcodeEmpty.length) {
                $shortcodeEmpty.show();
            }

            if ($placeholders.length) {
                $placeholders.html('Aucune balise disponible pour ce module.');
            }

            if ($example.length) {
                $example.text('');
            }
            return;
        }

        var generated = data.shortcode.replace('%s', slug);
        if ($shortcode.length) {
            $shortcode.text(generated).show();
            $copyShortcode.attr('data-wp-seed-copy-value', generated).show();
        }

        if ($shortcodeEmpty.length) {
            $shortcodeEmpty.hide();
        }

        if (!$placeholders.length) {
            return;
        }

        var placeholders = data.placeholders || [];
        if (!placeholders.length) {
            $placeholders.html('Aucune balise spécifique n’est encore définie pour ce module.');
            if ($example.length) {
                $example.text(data.example || '');
            }
            return;
        }

        var rows = '';
        placeholders.forEach(function(token) {
            var label = data.labels && data.labels[token] ? data.labels[token] : token;
            var placeholder = '{{' + token + '}}';
            rows += '<span class="wp-seed-template-placeholder-row" style="display:block; margin-bottom:6px;">';
            rows += '<code>' + seedEscapeHtml(placeholder) + '</code> - ' + seedEscapeHtml(label);
            rows += ' <button type="button" class="button button-small" data-wp-seed-copy-value="' + seedEscapeHtml(placeholder) + '">Copier</button>';
            rows += '</span>';
        });
        $placeholders.html(rows);

        if ($example.length) {
            $example.text(data.example || '');
        }
    }

    function updateTemplateSourceLabel() {
        if (!$sourceLabel.length) {
            return;
        }

        var source = $('input[name="wp_seed_content_template_source"]:checked').val() || 'native';
        $sourceLabel.text('divi_layout' === source ? 'Layout Divi Library' : 'Contenu de ce template');
        $diviLayoutSettings.toggle('divi_layout' === source);
    }

    function updateDiviLayoutSummary() {
        if (!$diviLayoutSelect.length || !$diviLayoutSummary.length || !$diviLayoutEmpty.length) {
            return;
        }

        var $selected = $diviLayoutSelect.find('option:selected');
        var selectedValue = $selected.val() || '0';
        if ('0' === selectedValue) {
            $diviLayoutSummary.hide();
            $diviLayoutEmpty.show();
            return;
        }

        $diviLayoutTitle.text($selected.attr('data-layout-title') || $selected.text());
        $diviLayoutType.text($selected.attr('data-layout-type') || 'Non défini');
        $diviLayoutStatus.text($selected.attr('data-layout-status') || 'Non défini');
        $diviLayoutEmpty.hide();
        $diviLayoutSummary.show();
    }

    $moduleSelect.on('change', function () {
        updateTemplateMetaUI($(this).val());
    });

    $('input[name="wp_seed_content_template_source"]').on('change', updateTemplateSourceLabel);
    $diviLayoutSelect.on('change', updateDiviLayoutSummary);

    updateTemplateMetaUI($moduleSelect.val());
    updateTemplateSourceLabel();
    updateDiviLayoutSummary();
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
    if ('testimonials' === $module || '' === $module) {
        return "<div class=\"testimonial-template\">\n<img src=\"{{photo_url}}\" alt=\"{{photo_alt}}\">\n<h3>{{name}}</h3>\n<p>{{text}}</p>\n<p>{{context}}</p>\n<p>{{date}}</p>\n</div>";
    }

    if (function_exists('wp_seed_content_kit_get_registered_template_placeholders')) {
        $lines = array();
        foreach (array_keys(wp_seed_content_kit_get_registered_template_placeholders($module)) as $placeholder) {
            $lines[] = '{{' . $placeholder . '}}';
        }
        if (!empty($lines)) {
            return '<div class="wp-seed-template-' . esc_attr($module) . '">' . "\n" . implode("\n", $lines) . "\n</div>";
        }
    }

    return '';
}
function wp_seed_content_seed_template_init_admin_columns()
{
    add_filter('manage_seed_template_posts_columns', 'wp_seed_content_seed_template_columns');
    add_action('manage_seed_template_posts_custom_column', 'wp_seed_content_seed_template_column_content', 10, 2);

    add_action('add_meta_boxes', function () {
        add_meta_box(
            'wp-seed-content-kit-template-module',
            __('Réglages du template', 'wp-seed-content-kit'),
            'wp_seed_content_render_template_module_meta_box',
            'seed_template',
            'side',
            'default'
        );

        add_meta_box(
            'wp-seed-content-kit-template-usage',
            __('Comment utiliser ce template', 'wp-seed-content-kit'),
            'wp_seed_content_render_template_usage_meta_box',
            'seed_template',
            'normal',
            'high'
        );
    });

    add_action('save_post_seed_template', 'wp_seed_content_save_template_module', 10, 3);
    add_action('save_post_seed_template', 'wp_seed_content_save_template_identifier_quick_edit', 20, 3);
    add_action('quick_edit_custom_box', 'wp_seed_content_render_template_identifier_quick_edit', 10, 3);
    add_action('admin_enqueue_scripts', 'wp_seed_content_enqueue_template_admin_scripts');
    add_action('manage_seed_template_posts_extra_tablenav', 'wp_seed_content_seed_template_list_help', 10, 1);
    add_filter('get_user_option_closedpostboxes_seed_template', 'wp_seed_content_keep_primary_template_meta_boxes_open');
    add_filter('postbox_classes_seed_template_wp-seed-content-kit-template-module', 'wp_seed_content_open_primary_template_postbox_classes');
    add_filter('postbox_classes_seed_template_wp-seed-content-kit-template-usage', 'wp_seed_content_open_primary_template_postbox_classes');
}
