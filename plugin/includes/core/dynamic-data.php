<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_get_dynamic_data_fields()
{
    $fields = array(
        'quote.quote' => array(
            'id' => 'quote.quote',
            'label' => __('Citation', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'textarea',
            'post_type' => 'seed_quote',
            'data_key' => 'quote',
            'empty_value' => '',
        ),
        'quote.author' => array(
            'id' => 'quote.author',
            'label' => __('Auteur', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'text',
            'post_type' => 'seed_quote',
            'data_key' => 'author',
            'empty_value' => '',
        ),
        'quote.era' => array(
            'id' => 'quote.era',
            'label' => __('Époque / date affichée', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'text',
            'post_type' => 'seed_quote',
            'data_key' => 'era',
            'empty_value' => '',
        ),
        'quote.source' => array(
            'id' => 'quote.source',
            'label' => __('Source / contexte', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'text',
            'post_type' => 'seed_quote',
            'data_key' => 'source',
            'empty_value' => '',
        ),
        'quote.featured' => array(
            'id' => 'quote.featured',
            'label' => __('Mise en avant', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'boolean',
            'post_type' => 'seed_quote',
            'data_key' => 'featured',
            'empty_value' => false,
        ),
        'quote.display_order' => array(
            'id' => 'quote.display_order',
            'label' => __('Position éditoriale', 'wp-seed-content-kit'),
            'module' => 'quotes',
            'type' => 'number',
            'post_type' => 'seed_quote',
            'data_key' => 'display_order',
            'empty_value' => 0,
        ),
        'testimonial.title' => array(
            'id' => 'testimonial.title',
            'label' => __('Titre du témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'testimonial_title',
            'empty_value' => '',
        ),
        'testimonial.summary' => array(
            'id' => 'testimonial.summary',
            'label' => __('Résumé / citation mise en avant', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'textarea',
            'post_type' => 'seed_testimonial',
            'data_key' => 'summary',
            'empty_value' => '',
        ),
        'testimonial.text' => array(
            'id' => 'testimonial.text',
            'label' => __('Témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'textarea',
            'post_type' => 'seed_testimonial',
            'data_key' => 'text',
            'empty_value' => '',
        ),
        'testimonial.full_content' => array(
            'id' => 'testimonial.full_content',
            'label' => __('Témoignage complet', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'textarea',
            'post_type' => 'seed_testimonial',
            'data_key' => 'full_content',
            'empty_value' => '',
        ),
        'testimonial.intro' => array(
            'id' => 'testimonial.intro',
            'label' => __('Introduction', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'textarea',
            'post_type' => 'seed_testimonial',
            'data_key' => 'intro',
            'empty_value' => '',
        ),
        'testimonial.more' => array(
            'id' => 'testimonial.more',
            'label' => __('Suite du témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'textarea',
            'post_type' => 'seed_testimonial',
            'data_key' => 'more',
            'empty_value' => '',
        ),
        'testimonial.has_more' => array(
            'id' => 'testimonial.has_more',
            'label' => __('Témoignage avec suite', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'boolean',
            'post_type' => 'seed_testimonial',
            'data_key' => 'has_more',
            'empty_value' => false,
        ),
        'testimonial.id' => array(
            'id' => 'testimonial.id',
            'label' => __('Identifiant du témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'id',
            'empty_value' => '',
        ),
        'testimonial.anchor' => array(
            'id' => 'testimonial.anchor',
            'label' => __('Ancre du témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'anchor',
            'empty_value' => '',
        ),
        'testimonial.anchor_url' => array(
            'id' => 'testimonial.anchor_url',
            'label' => __('Lien vers le témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'anchor_url',
            'empty_value' => '',
        ),
        'testimonial.name' => array(
            'id' => 'testimonial.name',
            'label' => __('Nom ou initiales', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'name',
            'empty_value' => '',
        ),
        'testimonial.context' => array(
            'id' => 'testimonial.context',
            'label' => __('Information complémentaire', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'context',
            'empty_value' => '',
        ),
        'testimonial.testimonial_date' => array(
            'id' => 'testimonial.testimonial_date',
            'label' => __('Date du témoignage', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'text',
            'post_type' => 'seed_testimonial',
            'data_key' => 'testimonial_date',
            'empty_value' => '',
        ),
        'testimonial.photo' => array(
            'id' => 'testimonial.photo',
            'label' => __('Création associée', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'image',
            'post_type' => 'seed_testimonial',
            'data_key' => 'photo',
            'empty_value' => null,
        ),
        'testimonial.featured' => array(
            'id' => 'testimonial.featured',
            'label' => __('Mise en avant', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'boolean',
            'post_type' => 'seed_testimonial',
            'data_key' => 'featured',
            'empty_value' => false,
        ),
        'testimonial.display_order' => array(
            'id' => 'testimonial.display_order',
            'label' => __('Position éditoriale', 'wp-seed-content-kit'),
            'module' => 'testimonials',
            'type' => 'number',
            'post_type' => 'seed_testimonial',
            'data_key' => 'display_order',
            'empty_value' => 0,
        ),
        'directory.photo' => array(
            'id' => 'directory.photo', 'label' => __('Visuel', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'image', 'post_type' => 'seed_directory', 'data_key' => 'photo', 'empty_value' => null,
        ),
        'directory.name' => array(
            'id' => 'directory.name', 'label' => __('Nom', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'name', 'empty_value' => '',
        ),
        'directory.professional_label' => array(
            'id' => 'directory.professional_label', 'label' => __('Intitulé professionnel', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'professional_label', 'empty_value' => '',
        ),
        'directory.summary' => array(
            'id' => 'directory.summary', 'label' => __('Résumé', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'textarea', 'post_type' => 'seed_directory', 'data_key' => 'summary', 'empty_value' => '',
        ),
        'directory.presentation' => array(
            'id' => 'directory.presentation', 'label' => __('Présentation', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'textarea', 'post_type' => 'seed_directory', 'data_key' => 'presentation', 'empty_value' => '',
        ),
        'directory.presentation_intro' => array(
            'id' => 'directory.presentation_intro', 'label' => __('Introduction', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'textarea', 'post_type' => 'seed_directory', 'data_key' => 'presentation_intro', 'empty_value' => '',
        ),
        'directory.presentation_more' => array(
            'id' => 'directory.presentation_more', 'label' => __('Suite de présentation', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'textarea', 'post_type' => 'seed_directory', 'data_key' => 'presentation_more', 'empty_value' => '',
        ),
        'directory.has_more' => array(
            'id' => 'directory.has_more', 'label' => __('A une suite', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'boolean', 'post_type' => 'seed_directory', 'data_key' => 'has_more', 'empty_value' => false,
        ),
        'directory.status' => array(
            'id' => 'directory.status', 'label' => __('Statut', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'status_label', 'empty_value' => '',
        ),
        'directory.profile_types' => array(
            'id' => 'directory.profile_types', 'label' => __('Types de profil', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'profile_types_label', 'empty_value' => '',
        ),
        'directory.seeking_models' => array(
            'id' => 'directory.seeking_models', 'label' => __('Recherche de modèles', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'seeking_models_label', 'empty_value' => '',
        ),
        'directory.location' => array(
            'id' => 'directory.location', 'label' => __('Localisation', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'location_label', 'empty_value' => '',
        ),
        'directory.id' => array(
            'id' => 'directory.id', 'label' => __('ID', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'id', 'empty_value' => '',
        ),
        'directory.anchor' => array(
            'id' => 'directory.anchor', 'label' => __('Ancre', 'wp-seed-content-kit'), 'module' => 'directory',
            'type' => 'text', 'post_type' => 'seed_directory', 'data_key' => 'anchor', 'empty_value' => '',
        ),
    );

    if (function_exists('wp_seed_content_directory_individual_contact_provider_definitions')) {
        $provider_definitions = wp_seed_content_directory_individual_contact_provider_definitions();
        foreach ($provider_definitions as $definition) {
            $fields[$definition['display_field_id']] = array(
                'id' => $definition['display_field_id'],
                'label' => $definition['label'],
                'module' => 'directory',
                'type' => 'text',
                'post_type' => 'seed_directory',
                'data_key' => $definition['slug'],
                'empty_value' => '',
            );
        }
        foreach ($provider_definitions as $definition) {
            if (!empty($definition['has_href'])) {
                $fields[$definition['href_field_id']] = array(
                    'id' => $definition['href_field_id'],
                    'label' => $definition['label'] . ' — Lien',
                    'module' => 'directory',
                    'type' => 'url',
                    'post_type' => 'seed_directory',
                    'data_key' => $definition['slug'] . '_href',
                    'empty_value' => '',
                );
            }
        }
    }

    return $fields;
}

function wp_seed_content_get_dynamic_data_field($field_id)
{
    if (!is_string($field_id)) {
        return null;
    }

    $field_id = trim($field_id);
    $fields = wp_seed_content_get_dynamic_data_fields();

    return array_key_exists($field_id, $fields) ? $fields[$field_id] : null;
}

function _wp_seed_content_normalize_dynamic_data_post_id($post_id)
{
    if (is_int($post_id)) {
        return $post_id > 0 ? $post_id : 0;
    }

    if (!is_string($post_id) || !preg_match('/^[0-9]+$/D', $post_id)) {
        return 0;
    }

    $canonical_id = ltrim($post_id, '0');
    if ('' === $canonical_id) {
        return 0;
    }

    $normalized_id = (int) $post_id;
    if ($normalized_id <= 0 || (string) $normalized_id !== $canonical_id) {
        return 0;
    }

    return $normalized_id;
}

function _wp_seed_content_get_dynamic_data_context_post_id($context)
{
    if (array_key_exists('explicit_post_id', $context)) {
        return _wp_seed_content_normalize_dynamic_data_post_id($context['explicit_post_id']);
    }

    if (array_key_exists('current_post_id', $context)) {
        return _wp_seed_content_normalize_dynamic_data_post_id($context['current_post_id']);
    }

    return _wp_seed_content_normalize_dynamic_data_post_id(get_the_ID());
}

function _wp_seed_content_normalize_dynamic_data_value($value, $type)
{
    if ('text' === $type || 'textarea' === $type || 'url' === $type) {
        return is_scalar($value) ? (string) $value : '';
    }

    if ('number' === $type) {
        return is_int($value) ? $value : 0;
    }

    if ('boolean' === $type) {
        return is_bool($value) ? $value : false;
    }

    if ('image' === $type) {
        return is_array($value) ? $value : null;
    }

    return null;
}

function wp_seed_content_resolve_dynamic_data($field_id, $context = array())
{
    $definition = wp_seed_content_get_dynamic_data_field($field_id);
    if (null === $definition) {
        return new WP_Error(
            'wp_seed_content_unknown_dynamic_data_field',
            __('Unknown Dynamic Data field.', 'wp-seed-content-kit')
        );
    }

    $context = is_array($context) ? $context : array();
    $empty_value = $definition['empty_value'];

    if (array_key_exists('current_post_type', $context)) {
        if (!is_string($context['current_post_type'])) {
            return $empty_value;
        }

        $current_post_type = trim($context['current_post_type']);
        if ('' === $current_post_type || $current_post_type !== $definition['post_type']) {
            return $empty_value;
        }
    }

    $post_id = _wp_seed_content_get_dynamic_data_context_post_id($context);
    if (!$post_id) {
        return $empty_value;
    }

    $allow_unpublished = array_key_exists('allow_unpublished', $context)
        && true === $context['allow_unpublished'];
    $args = array(
        'allow_unpublished' => $allow_unpublished,
        'allow_unconsented' => $allow_unpublished,
    );

    if ('quotes' === $definition['module']) {
        $data = wp_seed_content_get_quote_data($post_id, $args);
    } elseif ('testimonials' === $definition['module']) {
        $data = wp_seed_content_get_testimonial_data($post_id, $args);
    } elseif ('directory' === $definition['module']) {
        $data = wp_seed_content_get_directory_data($post_id, $args);
    } else {
        return $empty_value;
    }

    if (!is_array($data) || !array_key_exists($definition['data_key'], $data)) {
        return $empty_value;
    }

    return _wp_seed_content_normalize_dynamic_data_value(
        $data[$definition['data_key']],
        $definition['type']
    );
}
