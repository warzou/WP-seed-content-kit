<?php
namespace ET\Builder\Packages\Module\Layout\Components\DynamicContent {
    abstract class DynamicContentOptionBase { public function load() {} }
    interface DynamicContentOptionInterface {}
    class DynamicContentElements { public static function get_wrapper_element($args) { return $args['value']; } }
}
namespace {
require __DIR__ . '/formation-model-harness.php';
function wp_seed_content_kit_is_module_available_for_discovery($m) { return wp_seed_content_kit_is_module_active($m); }
function wp_seed_content_kit_should_resolve_module_binding($m) { return wp_seed_content_kit_is_module_active($m); }
function is_wp_error($v) { return false; }
function get_post_type($id) { $post = get_post($id); return $post ? $post->post_type : false; }
require dirname(__DIR__) . '/plugin/includes/core/dynamic-data.php';
require dirname(__DIR__) . '/plugin/includes/integrations/divi/loop-context.php';
require dirname(__DIR__) . '/plugin/includes/integrations/divi/collection-query.php';
require dirname(__DIR__) . '/plugin/includes/integrations/divi/formation-calendar.php';
require dirname(__DIR__) . '/plugin/includes/integrations/divi/class-dynamic-content-formation-field.php';
$before = serialize(array($GLOBALS['fm_posts'], $GLOBALS['fm_meta']));
$query = array('post_type'=>'seed_schedule','posts_per_page'=>99,'order'=>'ASC','meta_query'=>array(
    array('key'=>'_seed_formation_program_id','value'=>1),array('key'=>'_seed_formation_calendar_year','value'=>2027),array('key'=>'_seed_formation_section_id','value'=>3),
));
$items = wp_seed_content_formation_get_schedules(array('program_id'=>1,'year'=>2027,'section_id'=>3));
fm_same(array_column($items,'id'),wp_seed_content_divi_formation_query($query)['post__in'],'Explicit card filters canonical IDs');
fm_same($query['meta_query'],wp_seed_content_divi_formation_query($query)['meta_query'],'Native SQL filters retained');
$divi_query = $query;
$divi_query['meta_query']['relation'] = 'OR';
$fixed = wp_seed_content_divi_formation_query($divi_query);
fm_same('AND', $fixed['meta_query']['relation'], 'Real Divi flat OR dimensions become conjunctive');
fm_same(array_column($items, 'id'), $fixed['post__in'], 'Real Divi OR regression selects only matching IDs');
fm_same($fixed, wp_seed_content_divi_formation_query($fixed), 'Filter normalization is idempotent');
$frontend = wp_seed_content_divi_formation_frontend_query(array('query_args' => $divi_query));
fm_same($fixed, $frontend['query_args'], 'Frontend public hook shares REST normalization');
foreach (array('program_id'=>1,'calendar_year'=>2027,'section_id'=>3,'theme_id'=>999,'status'=>'cancelled') as $field=>$value) {
    $single = array('post_type'=>'seed_schedule','meta_query'=>array(array('key'=>'_seed_formation_'.$field,'value'=>$value)));
    $arg = 'calendar_year' === $field ? 'year' : $field;
    $expected = array_column(wp_seed_content_formation_get_schedules(array($arg=>$value,'include_cancelled'=>true)), 'id');
    fm_same($expected ? $expected : array(0), wp_seed_content_divi_formation_query($single)['post__in'], 'Independent dimension '.$arg);
}
foreach (array(
    array(array('key'=>'unrelated_meta','value'=>'x')),
    array(array('key'=>'_seed_formation_section_id','value'=>999)),
    array(array('relation'=>'OR',array('key'=>'_seed_formation_status','value'=>'planned'))),
    array(array('key'=>'_seed_formation_theme_id','value'=>array(1,2),'compare'=>'IN')),
    array(array('key'=>'_seed_formation_status','value'=>'cancelled','compare'=>'!=')),
) as $extra) {
    $complex = $divi_query;
    $complex['meta_query'] = array_merge($complex['meta_query'], $extra);
    fm_same($complex['meta_query'], wp_seed_content_divi_formation_query($complex)['meta_query'], 'Do not reinterpret unsupported OR semantics');
}
$external = $divi_query;
$external['post_type'] = 'seed_directory';
fm_same($external, wp_seed_content_divi_formation_query($external), 'Other CPT OR semantics untouched');
fm_same(array(0),wp_seed_content_divi_formation_query(array_merge($query,array('post__in'=>array(999))))['post__in'],'Empty intersection fails closed');
foreach (array('program_id'=>999,'year'=>2028,'section_id'=>999,'theme_id'=>999,'status'=>'bogus') as $k=>$v) fm_same(array(),wp_seed_content_formation_get_schedules(array($k=>$v)),'No leak for '.$k);
$cancelled = wp_seed_content_formation_get_schedules(array('status'=>'cancelled'));
fm_same(1,count($cancelled),'Cancelled selectable explicitly');
fm_same('cancelled',$cancelled[0]['status'],'Cancelled not relabelled');
$asc=wp_seed_content_formation_get_schedules(array('include_cancelled'=>true));
$desc=wp_seed_content_formation_get_schedules(array('include_cancelled'=>true,'order'=>'DESC'));
fm_same(array_reverse(array_column($asc,'id')),array_column($desc,'id'),'Date DESC');
$ordered=wp_seed_content_formation_get_schedules(array('include_cancelled'=>true,'orderby'=>'display_order'));
fm_same(array_column($ordered,'id'),wp_seed_content_divi_formation_query(array('post_type'=>'seed_schedule','order'=>'ASC'),array('order_by'=>'wpsck_schedule_order'))['post__in'],'REST custom order reaches canonical collection');
fm_same(array('post_type'=>'seed_directory'),wp_seed_content_divi_formation_query(array('post_type'=>'seed_directory')),'Unrelated queries unchanged');
fm_same(array(),wp_seed_content_formation_get_schedules(array('status'=>array('planned'))),'Malformed status fails closed');
$orders=array_column($ordered,'display_order');$sorted=$orders;sort($sorted);fm_same($sorted,$orders,'Display order');
$field_map=wp_seed_content_divi_formation_fields();
foreach (array('date','theme','annual_display_title','section','program','people','status','url','start_date','end_date','year','id','order','title') as $key) {
    $name='wpsck_formation_schedule_'.$key;
    fm_assert(isset($field_map[$name]),'Registered scalar '.$key);
    $option=new WP_Seed_Content_Divi_Dynamic_Content_Formation_Field($name,$field_map[$name]);
    fm_assert(isset($option->register_option_callback(array(),1,'')['loop_'.$name]),'Divi discovery '.$key);
    $value=$option->render_callback('',array('name'=>'loop_'.$name,'loop_id'=>10,'post_id'=>999));
    fm_assert(is_string($value),'Scalar rendered '.$key);
}
fm_same('',wp_seed_content_formation_get_schedule_data(10)['url'],'No invented URL');
$name='wpsck_formation_schedule_title';$option=new WP_Seed_Content_Divi_Dynamic_Content_Formation_Field($name,$field_map[$name]);
fm_same('', $option->render_callback('',array('name'=>'loop_'.$name,'loop_id'=>1)), 'Wrong CPT context empty');
$value=$option->render_callback('',array('name'=>'loop_'.$name,'loop_id'=>10));
$old=$GLOBALS['fm_meta'][10]['_seed_formation_annual_display_title'];
$GLOBALS['fm_meta'][10]['_seed_formation_annual_display_title']='Changed fixture only';
fm_same('Changed fixture only',$option->render_callback('',array('name'=>'loop_'.$name,'loop_id'=>10)),'Dynamic update without page save');
$GLOBALS['fm_meta'][10]['_seed_formation_annual_display_title']=$old;
fm_same($value,$option->render_callback('',array('name'=>'loop_'.$name,'loop_id'=>10)),'Fixture restored');
$GLOBALS['fm_module_disabled']=true;
fm_same(array(),wp_seed_content_formation_get_schedules(),'Disabled collection');
fm_same(array(),$option->register_option_callback(array(),1,''),'Disabled discovery');
fm_same(array(),wp_seed_content_divi_formation_sources(array()),'Disabled query fields');
fm_same(array(0),wp_seed_content_divi_formation_query($query)['post__in'],'Disabled query closed');
fm_same('', $option->render_callback('$variable(test)',array('name'=>'loop_'.$name,'loop_id'=>10)), 'Disabled persisted binding empty');
fm_same(array(),wp_seed_content_divi_formation_loop_post_types(array(),'/divi/v1/loop/query-types'),'Disabled CPT hidden');
$GLOBALS['fm_module_disabled']=false;
fm_assert(count($option->register_option_callback(array(),1,''))===1,'Reenabled discovery');
fm_same(array('seed_schedule'),wp_seed_content_divi_formation_loop_post_types(array(),'/divi/v1/loop/query-types'),'Reenabled CPT');
fm_same(array(),wp_seed_content_divi_formation_loop_post_types(array(),'/wp/v2/types'),'No unrelated route exposure');
fm_same($before,serialize(array($GLOBALS['fm_posts'],$GLOBALS['fm_meta'])),'No data lost across toggle/update');
class FormationCalendarRequest {
    private $route; private $type;
    public function __construct($route, $type = '') { $this->route = $route; $this->type = $type; }
    public function get_route() { return $this->route; }
    public function get_param($key) { return 'post_type' === $key ? $this->type : null; }
}
$GLOBALS['wp_post_types']['seed_schedule'] = (object) array('show_ui' => false, 'publicly_queryable' => false);
$discovery = new FormationCalendarRequest('/divi/v1/loop/query-types');
wp_seed_content_divi_formation_open_request(null, null, $discovery);
fm_same(true, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Technical CPT visible to real Divi enumeration');
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->publicly_queryable, 'Discovery never opens public query');
wp_seed_content_divi_formation_open_request(null, null, $discovery);
wp_seed_content_divi_formation_close_request(null, null, $discovery);
fm_same(true, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Nested discovery preserves outer state');
wp_seed_content_divi_formation_close_request(null, null, $discovery);
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Admin UI remains hidden after callback');
fm_same(array(), $GLOBALS['wpsck_formation_queryable_stack'], 'Request state cleaned');
$results = new FormationCalendarRequest('/divi/v1/loop/query-results', 'seed_schedule');
wp_seed_content_divi_formation_open_request(null, null, $results);
fm_same(true, $GLOBALS['wp_post_types']['seed_schedule']->publicly_queryable, 'Existing results scope preserved');
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Results do not expose admin UI');
wp_seed_content_divi_formation_close_request(null, null, $results);
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->publicly_queryable, 'Results scope restored');
$GLOBALS['fm_module_disabled'] = true;
wp_seed_content_divi_formation_open_request(null, null, $discovery);
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Disabled module hidden from enumeration');
$GLOBALS['fm_module_disabled'] = false;
$unrelated = new FormationCalendarRequest('/wp/v2/types');
wp_seed_content_divi_formation_open_request(null, null, $unrelated);
fm_same(false, $GLOBALS['wp_post_types']['seed_schedule']->show_ui, 'Other routes untouched');
$readable = array('enabled'=>'on','program'=>'1','year'=>'2027','section'=>'3','theme'=>'','status'=>'','sort'=>'date_asc');
$readable_result = wp_seed_content_divi_formation_query($divi_query, array('wpsck_calendar_filters'=>json_encode($readable)));
fm_same(array_column($items,'id'), $readable_result['post__in'], 'Readable UI and historical filters select same canonical items');
fm_same($readable_result, wp_seed_content_divi_formation_frontend_query(array('query_args'=>$divi_query), array('wpsckCalendarFilters'=>array('innerContent'=>array('desktop'=>array('value'=>$readable)))))['query_args'], 'Readable REST/frontend parity');
fm_same(wp_seed_content_divi_formation_query($divi_query), wp_seed_content_divi_formation_query($divi_query, array('wpsck_calendar_filters'=>array('enabled'=>'off'))), 'Disabled readable mode preserves historical filters');
foreach (array('program'=>'999','program-array'=>array(1),'section'=>'4','theme'=>'4','year'=>'bad','year-range'=>'2300','status'=>'bogus','sort'=>'random') as $key=>$invalid) {
    $bad=$readable;$bad[explode('-',$key)[0]]=$invalid;
    fm_same(array(0), wp_seed_content_divi_formation_query($divi_query,array('wpsck_calendar_filters'=>$bad))['post__in'], 'Readable invalid filter closes query '.$key);
}
fm_same(array(0),wp_seed_content_divi_formation_query($divi_query,array('wpsck_calendar_filters'=>'{invalid'))['post__in'],'Malformed JSON closes query');
$other=array('post_type'=>'seed_directory');
fm_same($other,wp_seed_content_divi_formation_query($other,array('wpsck_calendar_filters'=>$readable)),'Readable parameters ignore other CPTs');
$changed=$readable;$changed['year']='2026';
$changed_ids=array_column(wp_seed_content_formation_get_schedules(array('program_id'=>1,'year'=>2026,'section_id'=>3,'include_cancelled'=>true)),'id');
fm_same($changed_ids ? $changed_ids : array(0), wp_seed_content_divi_formation_query($divi_query,array('wpsck_calendar_filters'=>$changed))['post__in'],'Readable year replaces historical year');
$changed=$readable;$changed['sort']='date_desc';
fm_same(array_column(wp_seed_content_formation_get_schedules(array('program_id'=>1,'year'=>2027,'section_id'=>3,'include_cancelled'=>true,'order'=>'DESC')),'id'),wp_seed_content_divi_formation_query($divi_query,array('wpsck_calendar_filters'=>$changed))['post__in'],'Readable descending order');
$foreign=$divi_query;$foreign['meta_query'][]=array('key'=>'external','value'=>'keep');
fm_same('external',wp_seed_content_divi_calendar_filter_query($foreign,$readable)['meta_query'][0][0]['key'],'Foreign condition preserved');
fm_same($before,serialize(array($GLOBALS['fm_posts'],$GLOBALS['fm_meta'])),'Readable filter tests do not mutate data');
echo json_encode(array('assertions'=>$GLOBALS['fm_assertions'],'failures'=>$GLOBALS['fm_failures']),JSON_PRETTY_PRINT)."\n";
exit($GLOBALS['fm_failures']?1:0);
}
