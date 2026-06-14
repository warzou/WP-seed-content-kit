<?php
/**
 * Page builder compatibility guidance for WP Seed templates.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_detect_page_builders()
{
    $theme = wp_get_theme();
    $template = strtolower((string) $theme->get_template());
    $stylesheet = strtolower((string) $theme->get_stylesheet());

    return array(
        'gutenberg' => true,
        'spectra' => defined('UAGB_VER') || class_exists('UAGB_Loader') || class_exists('UAGB_Init_Blocks'),
        'divi' => in_array($template, array('divi', 'extra'), true)
            || in_array($stylesheet, array('divi', 'extra'), true)
            || class_exists('ET_Builder_Plugin')
            || class_exists('ET_Builder_Element')
            || function_exists('et_setup_theme'),
        'elementor' => did_action('elementor/loaded')
            || defined('ELEMENTOR_VERSION')
            || class_exists('\\Elementor\\Plugin'),
    );
}

function wp_seed_content_render_builder_compatibility_meta_box($post)
{
    if (!$post || 'seed_template' !== $post->post_type) {
        return;
    }

    $builders = wp_seed_content_detect_page_builders();
    $divi_settings_url = admin_url('admin.php?page=et_divi_options');
    $elementor_settings_url = admin_url('admin.php?page=elementor');
    ?>
    <p>
        <strong><?php esc_html_e('Constructeur de page', 'wp-seed-content-kit'); ?></strong>
    </p>
    <p class="description">
        <?php esc_html_e('WP Seed fournit les contenus, les balises et les shortcodes. Le constructeur sert uniquement à composer la mise en page du template.', 'wp-seed-content-kit'); ?>
    </p>
    <p>
        <strong><?php esc_html_e('Constructeur détecté', 'wp-seed-content-kit'); ?></strong><br />
        <?php esc_html_e('Gutenberg (WordPress)', 'wp-seed-content-kit'); ?>
        <br />
        <?php esc_html_e('Compatible par défaut.', 'wp-seed-content-kit'); ?>
        <br />
        <?php esc_html_e('Aucun réglage nécessaire.', 'wp-seed-content-kit'); ?>
        <?php if ($builders['spectra']) : ?>
            <br />
            <?php esc_html_e('Spectra détecté.', 'wp-seed-content-kit'); ?>
        <?php endif; ?>
    </p>
    <?php if ($builders['divi']) : ?>
        <p>
            <strong><?php esc_html_e('Divi détecté', 'wp-seed-content-kit'); ?></strong><br />
            <a href="<?php echo esc_url($divi_settings_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Divi → Theme Options → Builder → Post Type Integration', 'wp-seed-content-kit'); ?>
            </a><br />
            <?php esc_html_e('Post type à activer : seed_template', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
    <?php if ($builders['elementor']) : ?>
        <p>
            <strong><?php esc_html_e('Elementor détecté', 'wp-seed-content-kit'); ?></strong><br />
            <a href="<?php echo esc_url($elementor_settings_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Elementor → Réglages → Général → Types de publication', 'wp-seed-content-kit'); ?>
            </a><br />
            <?php esc_html_e('Post type à activer : seed_template', 'wp-seed-content-kit'); ?>
        </p>
    <?php endif; ?>
    <p>
        <label for="wp-seed-builder-post-type">
            <strong><?php esc_html_e('Type de contenu à activer dans le constructeur', 'wp-seed-content-kit'); ?></strong>
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
        <?php esc_html_e('Utilisez les balises disponibles dans des modules Texte, Code ou HTML.', 'wp-seed-content-kit'); ?>
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
