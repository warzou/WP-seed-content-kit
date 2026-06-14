<?php
/**
 * Page builder compatibility guidance for WP Seed templates.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_is_divi_active()
{
    $theme = wp_get_theme();
    $template = strtolower((string) $theme->get_template());
    $stylesheet = strtolower((string) $theme->get_stylesheet());

    return in_array($template, array('divi', 'extra'), true)
        || in_array($stylesheet, array('divi', 'extra'), true)
        || class_exists('ET_Builder_Plugin')
        || class_exists('ET_Builder_Element')
        || function_exists('et_setup_theme')
        || class_exists('ET_Builder_Module');
}

function wp_seed_content_is_elementor_active()
{
    return did_action('elementor/loaded')
        || defined('ELEMENTOR_VERSION')
        || class_exists('\Elementor\Plugin');
}

function wp_seed_content_is_seed_template_enabled_in_builder_option($option_values)
{
    if (!is_array($option_values)) {
        return null;
    }

    if (array_key_exists('seed_template', $option_values)) {
        return (bool) $option_values['seed_template'];
    }

    if (array_key_exists('post_types', $option_values) && is_array($option_values['post_types'])) {
        $post_types = $option_values['post_types'];

        if (array_key_exists('seed_template', $post_types)) {
            return (bool) $post_types['seed_template'];
        }

        if (in_array('seed_template', $post_types, true)) {
            return true;
        }
    }

    return null;
}

function wp_seed_content_is_divi_template_enabled()
{
    $option_names = array(
        'et_divi_options',
        'et_divi_builder',
        'et_pb_post_types',
        'et_pb_builder_options',
    );

    foreach ($option_names as $option_name) {
        $option = get_option($option_name);

        if (!is_array($option)) {
            continue;
        }

        $status = wp_seed_content_is_seed_template_enabled_in_builder_option($option);
        if (null !== $status) {
            return $status;
        }
    }

    return null;
}

function wp_seed_content_is_elementor_template_enabled()
{
    $option = get_option('elementor_cpt_support');

    if (!is_array($option)) {
        return null;
    }

    if (array_key_exists('seed_template', $option)) {
        return (bool) $option['seed_template'];
    }

    if (array_key_exists('post_types', $option) && is_array($option['post_types'])) {
        $post_types = $option['post_types'];

        if (array_key_exists('seed_template', $post_types)) {
            return (bool) $post_types['seed_template'];
        }

        if (in_array('seed_template', $post_types, true)) {
            return true;
        }
    }

    return null;
}

function wp_seed_content_get_builder_activation_status($builder)
{
    switch ($builder) {
        case 'spectra':
            return array(
                'detected' => defined('UAGB_VER') || class_exists('UAGB_Loader') || class_exists('UAGB_Init_Blocks'),
                'status' => 'not_configurable',
            );

        case 'divi':
            if (!wp_seed_content_is_divi_active()) {
                return array(
                    'detected' => false,
                    'status' => 'not_detected',
                );
            }

            $enabled = wp_seed_content_is_divi_template_enabled();
            if (null === $enabled) {
                return array(
                    'detected' => true,
                    'status' => 'unknown',
                );
            }

            return array(
                'detected' => true,
                'status' => $enabled ? 'enabled' : 'needs_activation',
            );

        case 'elementor':
            if (!wp_seed_content_is_elementor_active()) {
                return array(
                    'detected' => false,
                    'status' => 'not_detected',
                );
            }

            $enabled = wp_seed_content_is_elementor_template_enabled();
            if (null === $enabled) {
                return array(
                    'detected' => true,
                    'status' => 'unknown',
                );
            }

            return array(
                'detected' => true,
                'status' => $enabled ? 'enabled' : 'needs_activation',
            );

        default:
            return array(
                'detected' => false,
                'status' => 'not_detected',
            );
    }
}

function wp_seed_content_render_builder_compatibility_meta_box($post)
{
    if (!$post || 'seed_template' !== $post->post_type) {
        return;
    }

    $spectra = wp_seed_content_get_builder_activation_status('spectra');
    $divi = wp_seed_content_get_builder_activation_status('divi');
    $elementor = wp_seed_content_get_builder_activation_status('elementor');
    $divi_settings_url = admin_url('admin.php?page=et_divi_options');
    $elementor_settings_url = admin_url('admin.php?page=elementor');
    ?>
    <p>
        <strong><?php esc_html_e('Constructeur de page', 'wp-seed-content-kit'); ?></strong>
    </p>
    <p class="description">
        <?php esc_html_e('WP Seed fournit les contenus, les balises et les shortcodes.', 'wp-seed-content-kit'); ?>
        <?php esc_html_e('Le constructeur sert uniquement à composer la mise en page du template.', 'wp-seed-content-kit'); ?>
    </p>
    <p>
        <strong><?php esc_html_e('Constructeur détecté', 'wp-seed-content-kit'); ?> :</strong><br />
        Gutenberg (WordPress)<br />
        <?php esc_html_e('Compatible par défaut.', 'wp-seed-content-kit'); ?><br />
        <?php esc_html_e('Aucun réglage nécessaire.', 'wp-seed-content-kit'); ?>
    </p>

    <?php if (!empty($spectra['detected'])) : ?>
    <p>
        <strong><?php esc_html_e('Spectra', 'wp-seed-content-kit'); ?></strong><br />
        <?php esc_html_e('Compatible par défaut avec Gutenberg.', 'wp-seed-content-kit'); ?>
    </p>
    <?php endif; ?>

    <?php if ($divi['detected']) : ?>
    <p>
        <strong><?php esc_html_e('Divi', 'wp-seed-content-kit'); ?></strong><br />
        <?php if ('enabled' === $divi['status']) : ?>
            <?php esc_html_e('seed_template : OK.', 'wp-seed-content-kit'); ?>
        <?php elseif ('needs_activation' === $divi['status']) : ?>
            <a href="<?php echo esc_url($divi_settings_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Divi → Theme Options → Builder → Post Type Integration', 'wp-seed-content-kit'); ?>
            </a><br />
            <?php esc_html_e('Activez seed_template.', 'wp-seed-content-kit'); ?>
        <?php else : ?>
            <?php esc_html_e('Impossible de vérifier automatiquement.', 'wp-seed-content-kit'); ?><br />
            <?php esc_html_e('Vérifiez la configuration de seed_template dans Post Type Integration.', 'wp-seed-content-kit'); ?>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?php if ($elementor['detected']) : ?>
    <p>
        <strong><?php esc_html_e('Elementor', 'wp-seed-content-kit'); ?></strong><br />
        <?php if ('enabled' === $elementor['status']) : ?>
            <?php esc_html_e('seed_template : OK.', 'wp-seed-content-kit'); ?>
        <?php elseif ('needs_activation' === $elementor['status']) : ?>
            <a href="<?php echo esc_url($elementor_settings_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Elementor → Réglages → Général → Types de publication', 'wp-seed-content-kit'); ?>
            </a><br />
            <?php esc_html_e('Activez seed_template.', 'wp-seed-content-kit'); ?>
        <?php else : ?>
            <?php esc_html_e('Impossible de vérifier automatiquement.', 'wp-seed-content-kit'); ?><br />
            <?php esc_html_e('Vérifiez la configuration de seed_template dans Types de publication.', 'wp-seed-content-kit'); ?>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?php if (!$divi['detected'] && !$elementor['detected']) : ?>
    <p class="description">
        <?php esc_html_e('Si votre constructeur externe n’est pas détecté, activez seed_template dans ses réglages.', 'wp-seed-content-kit'); ?>
    </p>
    <?php endif; ?>

    <p>
        <label for="wp-seed-builder-post-type">
            <strong><?php esc_html_e('Type de contenu à utiliser', 'wp-seed-content-kit'); ?></strong>
        </label>
        <input
            id="wp-seed-builder-post-type"
            type="text"
            class="widefat"
            readonly
            value="seed_template"
            onclick="this.select();"
        />
    </p>
    <p class="description">
        <?php esc_html_e('Utilisez ce post type dans les modules Texte, Code ou HTML.', 'wp-seed-content-kit'); ?>
    </p>
    <?php
}

function wp_seed_content_register_builder_compatibility_meta_box()
{
    add_meta_box(
        'wp-seed-content-kit-builder-compatibility',
        __('Constructeur de page', 'wp-seed-content-kit'),
        'wp_seed_content_render_builder_compatibility_meta_box',
        'seed_template',
        'side',
        'default'
    );
}
add_action('add_meta_boxes_seed_template', 'wp_seed_content_register_builder_compatibility_meta_box');
