<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_primitive_capabilities()
{
    return array(
        'edit_seed_directory_entries',
        'publish_seed_directory_entries',
        'read_private_seed_directory_entries',
        'delete_seed_directory_entries',
    );
}

function wp_seed_content_directory_get_capability_map()
{
    return array(
        'edit_post' => 'edit_seed_directory_entry',
        'read_post' => 'read_seed_directory_entry',
        'delete_post' => 'delete_seed_directory_entry',
        'edit_posts' => 'edit_seed_directory_entries',
        'edit_others_posts' => 'edit_seed_directory_entries',
        'publish_posts' => 'publish_seed_directory_entries',
        'read_private_posts' => 'read_private_seed_directory_entries',
        'delete_posts' => 'delete_seed_directory_entries',
        'delete_private_posts' => 'delete_seed_directory_entries',
        'delete_published_posts' => 'delete_seed_directory_entries',
        'delete_others_posts' => 'delete_seed_directory_entries',
        'edit_private_posts' => 'edit_seed_directory_entries',
        'edit_published_posts' => 'edit_seed_directory_entries',
        'create_posts' => 'edit_seed_directory_entries',
    );
}

function wp_seed_content_directory_grant_capabilities()
{
    $role = get_role('administrator');
    if (!$role) {
        return;
    }

    foreach (wp_seed_content_directory_get_primitive_capabilities() as $capability) {
        $role->add_cap($capability);
    }
}
