<?php

if (!defined('ABSPATH')) {
    exit;
}

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

function wp_seed_content_kit_render_cards_generator($show_heading = true)
{
    $categories = wp_seed_content_kit_get_generator_terms('category');
    $tags = wp_seed_content_kit_get_generator_terms('post_tag');
    ?>
    <?php if ($show_heading) : ?>
        <h2><?php echo esc_html__('Générateur Cards', 'wp-seed-content-kit'); ?></h2>
    <?php endif; ?>
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

function wp_seed_content_kit_render_planned_generator($label, $show_heading = true)
{
    ?>
    <?php if ($show_heading) : ?>
        <hr />
        <h2><?php echo esc_html($label); ?></h2>
    <?php endif; ?>
    <p><?php echo esc_html__('Prévu pour une prochaine version.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_testimonials_generator($show_heading = true)
{
    ?>
    <?php if ($show_heading) : ?>
        <h2><?php echo esc_html__('Générateur Témoignages', 'wp-seed-content-kit'); ?></h2>
    <?php endif; ?>
    <p><?php echo esc_html__('Générez un shortcode [seed_testimonials] pour vos pages.', 'wp-seed-content-kit'); ?></p>
    <div id="wp-seed-content-kit-testimonials-generator">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="seed-testimonials-generator-limit"><?php echo esc_html__('Nombre', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-testimonials-generator-limit" type="number" min="0" value="3" data-seed-testimonials-attr="limit" data-seed-testimonials-default="3" />
                        <p class="description">
                            <?php echo esc_html__('Définissez 0 pour afficher toutes les entrées.', 'wp-seed-content-kit'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-testimonials-generator-featured"><?php echo esc_html__('Mis en avant', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input id="seed-testimonials-generator-featured" type="checkbox" data-seed-testimonials-attr="featured" value="true" />
                            <?php echo esc_html__('Ne prendre que les éléments mis en avant', 'wp-seed-content-kit'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-testimonials-generator-template"><?php echo esc_html__('Template', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-testimonials-generator-template" type="text" class="regular-text code" placeholder="<?php echo esc_attr__('Identifiant', 'wp-seed-content-kit'); ?>" data-seed-testimonials-attr="template" data-seed-testimonials-default="" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-testimonials-generator-shortcode"><?php echo esc_html__('Shortcode généré', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-testimonials-generator-shortcode" type="text" class="large-text code" readonly="readonly" value="[seed_testimonials]" />
                        <p>
                            <button type="button" class="button" id="seed-testimonials-generator-copy"><?php echo esc_html__('Copier', 'wp-seed-content-kit'); ?></button>
                            <span id="seed-testimonials-generator-copy-status" aria-live="polite"></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <script>
        (function () {
            var wrapper = document.getElementById('wp-seed-content-kit-testimonials-generator');
            var output = document.getElementById('seed-testimonials-generator-shortcode');
            var copyButton = document.getElementById('seed-testimonials-generator-copy');
            var copyStatus = document.getElementById('seed-testimonials-generator-copy-status');

            if (!wrapper || !output) {
                return;
            }

            function normalizeAttribute(value) {
                return String(value).replace(/"/g, "'").replace(/\[/g, '').replace(/\]/g, '');
            }

            function isFieldEnabled(field) {
                return !field.disabled && field.getAttribute;
            }

            function updateShortcode() {
                var fields = wrapper.querySelectorAll('[data-seed-testimonials-attr]');
                var parts = ['seed_testimonials'];

                fields.forEach(function (field) {
                    if (!isFieldEnabled(field)) {
                        return;
                    }

                    var attr = field.getAttribute('data-seed-testimonials-attr');
                    var defaultValue = field.getAttribute('data-seed-testimonials-default');
                    var value = '';

                    if ('checkbox' === field.type) {
                        if (field.checked) {
                            value = 'true';
                        }
                    } else {
                        value = String(field.value).trim();
                        if (!field.value.trim()) {
                            value = '';
                        }
                    }

                    if (!value) {
                        return;
                    }

                    if (defaultValue && value === defaultValue) {
                        return;
                    }

                    if ('limit' === attr && Number(value) === 0) {
                        parts.push('limit="' + value + '"');
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
                        copyStatus.textContent = '<?php echo esc_js(__('Copié.', 'wp-seed-content-kit')); ?>';
                    }
                });
            }

            updateShortcode();
        }());
    </script>
    <?php
}

function wp_seed_content_kit_render_quotes_generator($show_heading = true)
{
    ?>
    <?php if ($show_heading) : ?>
        <h2><?php echo esc_html__('Générateur Citations', 'wp-seed-content-kit'); ?></h2>
    <?php endif; ?>
    <p><?php echo esc_html__('Générez un shortcode [seed_quotes] pour vos pages.', 'wp-seed-content-kit'); ?></p>
    <p class="description">
        <?php echo esc_html__('Règle : limit=0 pour afficher toutes les citations. orderby peut être random, author ou date.', 'wp-seed-content-kit'); ?>
    </p>
    <div id="wp-seed-content-kit-quotes-generator">
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-limit"><?php echo esc_html__('Nombre', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-quotes-generator-limit" type="number" min="0" value="1" data-seed-quotes-attr="limit" data-seed-quotes-default="1" />
                        <p class="description">
                            <?php echo esc_html__('0 = toutes les citations, valeur vide = 1 citation aléatoire.', 'wp-seed-content-kit'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-featured"><?php echo esc_html__('Mis en avant', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input id="seed-quotes-generator-featured" type="checkbox" data-seed-quotes-attr="featured" value="true" />
                            <?php echo esc_html__('Ne prendre que les citations mises en avant', 'wp-seed-content-kit'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-orderby"><?php echo esc_html__('Trier par', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-quotes-generator-orderby" data-seed-quotes-attr="orderby" data-seed-quotes-default="random">
                            <option value="random"><?php echo esc_html__('Aléatoire', 'wp-seed-content-kit'); ?></option>
                            <option value="author"><?php echo esc_html__('Auteur', 'wp-seed-content-kit'); ?></option>
                            <option value="date"><?php echo esc_html__('Date WordPress', 'wp-seed-content-kit'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-order"><?php echo esc_html__('Ordre', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <select id="seed-quotes-generator-order" data-seed-quotes-attr="order" data-seed-quotes-default="DESC">
                            <option value="DESC"><?php echo esc_html__('Décroissant', 'wp-seed-content-kit'); ?></option>
                            <option value="ASC"><?php echo esc_html__('Croissant', 'wp-seed-content-kit'); ?></option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Ignoré quand orderby="random".', 'wp-seed-content-kit'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-template"><?php echo esc_html__('Template', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-quotes-generator-template" type="text" class="regular-text code" placeholder="<?php echo esc_attr__('Identifiant', 'wp-seed-content-kit'); ?>" data-seed-quotes-attr="template" data-seed-quotes-default="" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="seed-quotes-generator-shortcode"><?php echo esc_html__('Shortcode généré', 'wp-seed-content-kit'); ?></label>
                    </th>
                    <td>
                        <input id="seed-quotes-generator-shortcode" type="text" class="large-text code" readonly="readonly" value="[seed_quotes]" />
                        <p>
                            <button type="button" class="button" id="seed-quotes-generator-copy"><?php echo esc_html__('Copier', 'wp-seed-content-kit'); ?></button>
                            <span id="seed-quotes-generator-copy-status" aria-live="polite"></span>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <script>
        (function () {
            var wrapper = document.getElementById('wp-seed-content-kit-quotes-generator');
            var output = document.getElementById('seed-quotes-generator-shortcode');
            var copyButton = document.getElementById('seed-quotes-generator-copy');
            var copyStatus = document.getElementById('seed-quotes-generator-copy-status');
            var orderSelect = document.getElementById('seed-quotes-generator-orderby');
            var orderField = document.getElementById('seed-quotes-generator-order');

            if (!wrapper || !output || !orderSelect || !orderField) {
                return;
            }

            function normalizeAttribute(value) {
                return String(value).replace(/"/g, "'").replace(/\[/g, '').replace(/\]/g, '');
            }

            function isRandomMode() {
                return 'random' === String(orderSelect.value);
            }

            function syncOrderField() {
                if (isRandomMode()) {
                    orderField.disabled = true;
                    orderField.value = 'DESC';
                } else {
                    orderField.disabled = false;
                }
            }

            function updateShortcode() {
                var fields = wrapper.querySelectorAll('[data-seed-quotes-attr]');
                var parts = ['seed_quotes'];

                fields.forEach(function (field) {
                    if (field.disabled) {
                        return;
                    }

                    var attr = field.getAttribute('data-seed-quotes-attr');
                    var defaultValue = field.getAttribute('data-seed-quotes-default');
                    var value = '';

                    if ('checkbox' === field.type) {
                        if (field.checked) {
                            value = 'true';
                        }
                    } else {
                        value = String(field.value).trim();
                    }

                    if (!value) {
                        return;
                    }

                    if (defaultValue !== null && value === defaultValue) {
                        if (!('template' === attr && value !== '')) {
                            return;
                        }
                    }

                    parts.push(attr + '="' + normalizeAttribute(value) + '"');
                });

                output.value = '[' + parts.join(' ') + ']';
            }

            wrapper.addEventListener('input', updateShortcode);
            wrapper.addEventListener('change', function () {
                syncOrderField();
                updateShortcode();
            });

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
                        copyStatus.textContent = '<?php echo esc_js(__('Copié.', 'wp-seed-content-kit')); ?>';
                    }
                });
            }

            syncOrderField();
            updateShortcode();
        }());
    </script>
    <?php
}

function wp_seed_content_kit_render_generators_tab()
{
    ?>
    <p><?php echo esc_html__('Les générateurs produisent des shortcodes explicites à copier dans vos pages. Ils ne stockent aucun réglage global.', 'wp-seed-content-kit'); ?></p>

    <details>
        <summary><strong><?php echo esc_html__('Générateur Cards', 'wp-seed-content-kit'); ?></strong></summary>
        <?php wp_seed_content_kit_render_cards_generator(false); ?>
    </details>

    <details>
        <summary><strong><?php echo esc_html__('Générateur Témoignages', 'wp-seed-content-kit'); ?></strong></summary>
        <?php wp_seed_content_kit_render_testimonials_generator(false); ?>
    </details>

    <details>
        <summary><strong><?php echo esc_html__('Générateur Citations', 'wp-seed-content-kit'); ?></strong></summary>
        <?php wp_seed_content_kit_render_quotes_generator(false); ?>
    </details>
    <?php
}
