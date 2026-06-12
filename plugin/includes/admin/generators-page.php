<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_register_generators_page()
{
    add_submenu_page(
        'wp-seed-content-kit',
        __('Générateurs', 'wp-seed-content-kit'),
        __('Générateurs', 'wp-seed-content-kit'),
        'manage_options',
        'wp-seed-content-kit-generators',
        'wp_seed_content_kit_render_generators_page'
    );
}
add_action('admin_menu', 'wp_seed_content_kit_register_generators_page');

function wp_seed_content_kit_get_generator_terms($taxonomy)
{
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));

    if (is_wp_error($terms) || !is_array($terms)) {
        return array();
    }

    return $terms;
}

function wp_seed_content_kit_render_generator_term_options($terms)
{
    foreach ($terms as $term) {
        printf(
            '<option value="%s">%s</option>',
            esc_attr($term->slug),
            esc_html($term->name)
        );
    }
}

function wp_seed_content_kit_render_cards_generator()
{
    $categories = wp_seed_content_kit_get_generator_terms('category');
    $tags = wp_seed_content_kit_get_generator_terms('post_tag');
    ?>
    <h2><?php echo esc_html__('Générateur Cards', 'wp-seed-content-kit'); ?></h2>
    <p><?php echo esc_html__('Générez un shortcode explicite à copier dans une page. Aucun réglage Cards n’est enregistré.', 'wp-seed-content-kit'); ?></p>

    <div id="wp-seed-content-kit-cards-generator">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-category"><?php echo esc_html__('Catégorie', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-cards-generator-category" data-seed-cards-attr="category">
                            <option value=""><?php echo esc_html__('Toutes les catégories', 'wp-seed-content-kit'); ?></option>
                            <?php wp_seed_content_kit_render_generator_term_options($categories); ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-tag"><?php echo esc_html__('Étiquette', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-cards-generator-tag" data-seed-cards-attr="tag">
                            <option value=""><?php echo esc_html__('Toutes les étiquettes', 'wp-seed-content-kit'); ?></option>
                            <?php wp_seed_content_kit_render_generator_term_options($tags); ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-limit"><?php echo esc_html__('Nombre d’articles', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-cards-generator-limit" type="number" min="1" max="24" value="6" data-seed-cards-attr="limit" data-seed-cards-default="6" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-columns"><?php echo esc_html__('Colonnes', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-cards-generator-columns" type="number" min="1" max="4" value="3" data-seed-cards-attr="columns" data-seed-cards-default="3" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-orderby"><?php echo esc_html__('Trier par', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-cards-generator-orderby" data-seed-cards-attr="orderby" data-seed-cards-default="date">
                            <option value="date"><?php echo esc_html__('Date', 'wp-seed-content-kit'); ?></option>
                            <option value="title"><?php echo esc_html__('Titre', 'wp-seed-content-kit'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-order"><?php echo esc_html__('Ordre', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-cards-generator-order" data-seed-cards-attr="order" data-seed-cards-default="desc">
                            <option value="desc"><?php echo esc_html__('Date descendante / Z vers A', 'wp-seed-content-kit'); ?></option>
                            <option value="asc"><?php echo esc_html__('Date ascendante / A vers Z', 'wp-seed-content-kit'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php echo esc_html__('Éléments visibles', 'wp-seed-content-kit'); ?></th>
                    <td>
                        <?php
                        $visibility_fields = array(
                            'show_image' => __('Image', 'wp-seed-content-kit'),
                            'show_category' => __('Catégorie', 'wp-seed-content-kit'),
                            'show_date' => __('Date', 'wp-seed-content-kit'),
                            'show_title' => __('Titre', 'wp-seed-content-kit'),
                            'show_excerpt' => __('Extrait', 'wp-seed-content-kit'),
                            'show_button' => __('Bouton', 'wp-seed-content-kit'),
                        );
                        ?>
                        <?php foreach ($visibility_fields as $field => $label) : ?>
                            <label>
                                <input type="checkbox" checked="checked" data-seed-cards-attr="<?php echo esc_attr($field); ?>" data-seed-cards-default="true" />
                                <?php echo esc_html($label); ?>
                            </label><br />
                        <?php endforeach; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-button-label"><?php echo esc_html__('Libellé du bouton', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-cards-generator-button-label" type="text" class="regular-text" value="<?php echo esc_attr__('Lire', 'wp-seed-content-kit'); ?>" data-seed-cards-attr="button_label" data-seed-cards-default="<?php echo esc_attr__('Lire', 'wp-seed-content-kit'); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="seed-cards-generator-shortcode"><?php echo esc_html__('Shortcode généré', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-cards-generator-shortcode" type="text" class="large-text code" readonly="readonly" value="[seed_cards]" />
                        <p>
                            <button type="button" class="button" id="seed-cards-generator-copy"><?php echo esc_html__('Copier', 'wp-seed-content-kit'); ?></button>
                            <span id="seed-cards-generator-copy-status" aria-live="polite"></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            var wrapper = document.getElementById('wp-seed-content-kit-cards-generator');
            var output = document.getElementById('seed-cards-generator-shortcode');
            var copyButton = document.getElementById('seed-cards-generator-copy');
            var copyStatus = document.getElementById('seed-cards-generator-copy-status');

            if (!wrapper || !output) {
                return;
            }

            function normalizeAttribute(value) {
                return String(value).replace(/"/g, "'").replace(/\[/g, '').replace(/\]/g, '');
            }

            function updateShortcode() {
                var fields = wrapper.querySelectorAll('[data-seed-cards-attr]');
                var parts = ['seed_cards'];

                fields.forEach(function (field) {
                    var attr = field.getAttribute('data-seed-cards-attr');
                    var defaultValue = field.getAttribute('data-seed-cards-default');
                    var value = '';

                    if ('checkbox' === field.type) {
                        value = field.checked ? 'true' : 'false';
                    } else {
                        value = field.value.trim();
                    }

                    if (!value || value === defaultValue) {
                        return;
                    }

                    parts.push(attr + '="' + normalizeAttribute(value) + '"');
                });

                output.value = '[' + parts.join(' ') + ']';
            }

            wrapper.addEventListener('input', updateShortcode);
            wrapper.addEventListener('change', updateShortcode);

            if (copyButton) {
                copyButton.addEventListener('click', function () {
                    output.select();
                    output.setSelectionRange(0, output.value.length);

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(output.value);
                    } else {
                        document.execCommand('copy');
                    }

                    if (copyStatus) {
                        copyStatus.textContent = '<?php echo esc_js(__(' Copié.', 'wp-seed-content-kit')); ?>';
                    }
                });
            }

            updateShortcode();
        }());
    </script>
    <?php
}

function wp_seed_content_kit_render_planned_generator($label)
{
    ?>
    <hr />
    <h2><?php echo esc_html($label); ?></h2>
    <p><?php echo esc_html__('Prévu pour une prochaine version.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_generators_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation de gérer les générateurs WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('WP Seed Content Kit - Générateurs', 'wp-seed-content-kit'); ?></h1>
        <p><?php echo esc_html__('Les générateurs produisent des shortcodes explicites à copier dans vos pages. Ils ne stockent aucun réglage global.', 'wp-seed-content-kit'); ?></p>

        <?php wp_seed_content_kit_render_cards_generator(); ?>
        <?php wp_seed_content_kit_render_planned_generator(__('Générateur Témoignages', 'wp-seed-content-kit')); ?>
        <?php wp_seed_content_kit_render_planned_generator(__('Générateur Citations', 'wp-seed-content-kit')); ?>
    </div>
    <?php
}
