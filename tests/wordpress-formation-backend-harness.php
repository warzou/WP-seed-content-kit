<?php
// Run explicitly via WP-CLI eval-file on an authorized fixture environment.
if (!defined('WP_CLI') || !WP_CLI || !defined('WPSCK_FORMATION_TRANSACTIONAL_TEST')) return;
require_once __DIR__ . '/formation-backend-cases.php';
global $wpdb;
$previous_user = get_current_user_id();
$administrators = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$administrators) throw new RuntimeException('An existing administrator is required.');
wp_set_current_user($administrators[0]);
$wpdb->query('START TRANSACTION');
try {
    $formation_result = wpsck_formation_backend_cases();
    $saved_post = $_POST;
    try {
        $program = wp_insert_post(array('post_type'=>'seed_program','post_status'=>'publish','post_title'=>'WPSCK TEMP V4.1 sections'), true);
        if (is_wp_error($program)) throw new RuntimeException('Section fixture creation failed');
        $formation_result['fixture_ids'][] = $program;
        $_POST = array('wp_seed_content_formation_sections_nonce'=>wp_create_nonce('wp_seed_content_formation_save_sections'), 'wpsck_formation_sections'=>array('id'=>array(0,0),'label'=>array('Stages residentiels','Supervision libre'),'order'=>array(10,20)));
        wp_seed_content_formation_save_program_sections($program, get_post($program));
        $sections = wp_seed_content_formation_get_sections($program);
        $formation_result['checks']['admin_section_create'] = count($sections) === 2;
        if (count($sections) !== 2) throw new RuntimeException('Section handler creation failed');
        foreach ($sections as $section) $formation_result['fixture_ids'][] = $section->ID;
        $_POST['wpsck_formation_sections'] = array('id'=>array($sections[0]->ID,$sections[1]->ID),'label'=>array('Stages renommes','Supervision libre'),'order'=>array(20,10));
        wp_seed_content_formation_save_program_sections($program, get_post($program));
        $formation_result['checks']['admin_section_rename'] = get_post($sections[0]->ID)->post_title === 'Stages renommes';
        $formation_result['checks']['admin_section_reorder_up_down_persisted'] = array_map('intval', wp_list_pluck(wp_seed_content_formation_get_sections($program), 'ID')) === array($sections[1]->ID,$sections[0]->ID);
        $_POST['wpsck_formation_sections']['order'] = array(10,20);
        wp_seed_content_formation_save_program_sections($program, get_post($program));
        $formation_result['checks']['admin_section_reverse_reorder_persisted'] = array_map('intval', wp_list_pluck(wp_seed_content_formation_get_sections($program), 'ID')) === array($sections[0]->ID,$sections[1]->ID);
        $_POST['wpsck_formation_sections'] = array();
        $_POST['wpsck_formation_removed_sections'] = array($sections[0]->ID,$sections[1]->ID);
        wp_seed_content_formation_save_program_sections($program, get_post($program));
        $formation_result['checks']['admin_empty_section_remove'] = wp_seed_content_formation_get_sections($program) === array();
    } finally {
        $_POST = $saved_post;
    }
    $test_id = 0;
    foreach ($formation_result['fixture_ids'] as $id) if (get_post_type($id) === 'seed_schedule' && get_post_status($id) === 'publish') { $test_id = $id; break; }
    $field = 'formation.schedule.title';
    $expected = wp_seed_content_formation_get_schedule_data($test_id)['display_title'];
    $formation_result['checks']['dynamic_data_value'] = wp_seed_content_resolve_dynamic_data($field, array('explicit_post_id' => $test_id)) === $expected;
    $binding = array('source'=>'wp-seed-content-kit/dynamic-data','args'=>array('field_id'=>$field));
    $block = new WP_Block(array('blockName'=>'core/paragraph','attrs'=>array('metadata'=>array('bindings'=>array('content'=>$binding))),'innerBlocks'=>array(),'innerHTML'=>'<p>Placeholder</p>','innerContent'=>array('<p>Placeholder</p>')), array('postId'=>$test_id,'postType'=>'seed_schedule'));
    // WordPress hydrates source uses_context during rendering, not construction.
    $rendered = $block->render();
    $formation_result['checks']['block_binding_rendered_value'] = strpos($rendered, esc_html($expected)) !== false;
    $formation_result['checks']['block_binding_explicit_value'] = wp_seed_content_get_gutenberg_binding_value(array('field_id'=>$field,'post_id'=>$test_id), $block, 'content') === $expected;
    $formation_result['checks']['enabled_discovery'] = isset(wp_seed_content_get_discoverable_dynamic_data_fields()[$field]);
    $options = wp_seed_content_kit_get_module_options();
    $disabled = function () use ($options) { $options['formation'] = false; return $options; };
    add_filter('pre_option_wp_seed_content_kit_modules', $disabled);
    $formation_result['checks']['disabled_discovery'] = !isset(wp_seed_content_get_discoverable_dynamic_data_fields()[$field]);
    $formation_result['checks']['disabled_existing_binding_safe'] = is_string(wp_seed_content_get_gutenberg_binding_value(array('field_id'=>$field), $block, 'content'));
    remove_filter('pre_option_wp_seed_content_kit_modules', $disabled);
    $formation_result['checks']['reenabled_binding'] = wp_seed_content_get_gutenberg_binding_value(array('field_id'=>$field,'post_id'=>$test_id), $block, 'content') === $expected;
} finally {
    $wpdb->query('ROLLBACK');
    if (isset($formation_result)) foreach ($formation_result['fixture_ids'] as $id) clean_post_cache($id);
    wp_set_current_user($previous_user);
}
echo wp_json_encode($formation_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
if (in_array(false, $formation_result['checks'], true)) throw new RuntimeException('Formation backend assertions failed.');
