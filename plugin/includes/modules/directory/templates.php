<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_register_template_module()
{
    wp_seed_content_kit_register_template_module('directory', array(
        'label' => __('Annuaire', 'wp-seed-content-kit'),
        'description' => __('Cartes publiques du module Annuaire.', 'wp-seed-content-kit'),
        'shortcode' => 'seed_directory',
        'render_types' => array('native', 'divi_layout'),
        'assets' => array(),
        'placeholders' => array(
            'directory.name' => array('type' => 'text', 'label' => __('Nom', 'wp-seed-content-kit')),
            'directory.photo' => array('type' => 'image', 'label' => __('Photo', 'wp-seed-content-kit')),
            'directory.summary' => array('type' => 'textarea', 'label' => __('Présentation courte', 'wp-seed-content-kit')),
            'directory.bio' => array('type' => 'textarea', 'label' => __('Présentation courte (alias historique)', 'wp-seed-content-kit')),
            'directory.professional_label' => array('type' => 'text', 'label' => __('Intitulé professionnel', 'wp-seed-content-kit')),
            'directory.full_presentation' => array('type' => 'html', 'label' => __('Présentation complète', 'wp-seed-content-kit')),
            'directory.presentation' => array('type' => 'html', 'label' => __('Présentation', 'wp-seed-content-kit')),
            'directory.presentation_intro' => array('type' => 'html', 'label' => __('Introduction', 'wp-seed-content-kit')),
            'directory.presentation_more' => array('type' => 'html', 'label' => __('Suite de présentation', 'wp-seed-content-kit')),
            'directory.has_more' => array('type' => 'text', 'label' => __('A une suite', 'wp-seed-content-kit')),
            'directory.status' => array('type' => 'text', 'label' => __('Code du statut', 'wp-seed-content-kit')),
            'directory.status_label' => array('type' => 'text', 'label' => __('Statut', 'wp-seed-content-kit')),
            'directory.profile_types' => array('type' => 'text', 'label' => __('Types de profil', 'wp-seed-content-kit')),
            'directory.profile_type_slugs' => array('type' => 'text', 'label' => __('Codes des types de profil', 'wp-seed-content-kit')),
            'directory.seeking_models' => array('type' => 'text', 'label' => __('Recherche de modèles', 'wp-seed-content-kit')),
            'directory.seeking_models_active' => array('type' => 'text', 'label' => __('Recherche de modèles active', 'wp-seed-content-kit')),
            'directory.city' => array('type' => 'text', 'label' => __('Ville', 'wp-seed-content-kit')),
            'directory.postal_code' => array('type' => 'text', 'label' => __('Code postal', 'wp-seed-content-kit')),
            'directory.department' => array('type' => 'text', 'label' => __('Département', 'wp-seed-content-kit')),
            'directory.country' => array('type' => 'text', 'label' => __('Pays', 'wp-seed-content-kit')),
            'directory.phone' => array('type' => 'tel', 'label' => __('Téléphone', 'wp-seed-content-kit')),
            'directory.email' => array('type' => 'email', 'label' => __('E-mail', 'wp-seed-content-kit')),
            'directory.website' => array('type' => 'url', 'label' => __('Site internet', 'wp-seed-content-kit')),
            'directory.facebook' => array('type' => 'url', 'label' => __('Facebook', 'wp-seed-content-kit')),
            'directory.instagram' => array('type' => 'url', 'label' => __('Instagram', 'wp-seed-content-kit')),
            'directory.featured' => array('type' => 'text', 'label' => __('Mise en avant', 'wp-seed-content-kit')),
        ),
    ));
}
add_action('wp_seed_content_kit_register_template_modules', 'wp_seed_content_directory_register_template_module');

function wp_seed_content_directory_get_template_context($data)
{
    if (!is_array($data)) {
        return array();
    }

    $location = isset($data['location']) && is_array($data['location']) ? $data['location'] : array();
    $contacts = isset($data['contacts']) && is_array($data['contacts']) ? $data['contacts'] : array();
    $photo = isset($data['photo']) && is_array($data['photo'])
        ? array('url' => isset($data['photo']['url']) ? $data['photo']['url'] : '', 'alt' => isset($data['photo']['alt']) ? $data['photo']['alt'] : '')
        : array('url' => '', 'alt' => '');

    $context = array(
        'directory.name' => isset($data['name']) ? $data['name'] : '',
        'directory.photo' => $photo,
        'directory.summary' => isset($data['summary']) ? $data['summary'] : '',
        'directory.bio' => isset($data['bio']) ? $data['bio'] : '',
        'directory.professional_label' => isset($data['professional_label']) ? $data['professional_label'] : '',
        'directory.full_presentation' => isset($data['full_presentation']) ? $data['full_presentation'] : '',
        'directory.presentation' => isset($data['presentation']) ? $data['presentation'] : '',
        'directory.presentation_intro' => isset($data['presentation_intro']) ? $data['presentation_intro'] : '',
        'directory.presentation_more' => isset($data['presentation_more']) ? $data['presentation_more'] : '',
        'directory.has_more' => !empty($data['has_more']) ? '1' : '',
        'directory.status' => isset($data['status']) ? $data['status'] : '',
        'directory.status_label' => isset($data['status_label']) ? $data['status_label'] : '',
        'directory.profile_types' => isset($data['profile_types_label']) ? $data['profile_types_label'] : '',
        'directory.profile_type_slugs' => !empty($data['profile_types']) && is_array($data['profile_types'])
            ? implode(',', $data['profile_types'])
            : '',
        'directory.seeking_models' => isset($data['seeking_models_label']) ? $data['seeking_models_label'] : '',
        'directory.seeking_models_active' => !empty($data['seeking_models']) ? '1' : '',
        'directory.city' => isset($location['city']) ? $location['city'] : '',
        'directory.postal_code' => isset($location['postal_code']) ? $location['postal_code'] : '',
        'directory.department' => isset($location['department']) ? $location['department'] : '',
        'directory.country' => isset($location['country']) ? $location['country'] : '',
        'directory.phone' => isset($contacts['phone']) ? $contacts['phone'] : '',
        'directory.email' => isset($contacts['email']) ? $contacts['email'] : '',
        'directory.website' => isset($contacts['website']) ? $contacts['website'] : '',
        'directory.facebook' => isset($contacts['facebook']) ? $contacts['facebook'] : '',
        'directory.instagram' => isset($contacts['instagram']) ? $contacts['instagram'] : '',
        'directory.featured' => !empty($data['featured']) ? '1' : '',
    );

    return apply_filters('wp_seed_content_directory_template_context', $context, $data);
}
