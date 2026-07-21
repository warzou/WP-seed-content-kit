<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_directory_get_primitive_capabilities()
{
    return wp_seed_content_kit_get_primitive_capabilities('directory');
}

function wp_seed_content_directory_get_capability_map()
{
    return wp_seed_content_kit_get_capability_map('directory');
}

function wp_seed_content_directory_grant_capabilities()
{
    wp_seed_content_kit_synchronize_role_capabilities();
}
