<?php
$load = getenv('WP_SEED_WORDPRESS_LOAD');
if (!$load || !is_file($load)) { fwrite(STDERR, "Set WP_SEED_WORDPRESS_LOAD.\n"); exit(2); }
require $load;
$root = dirname(__DIR__);
require_once $root . '/plugin/wp-seed-content-kit.php';
wp_seed_content_kit_activate();
$GLOBALS['rc2a']=0; $GLOBALS['rc2f']=array();
function rc2ok($c,$l){$GLOBALS['rc2a']++;if(!$c)$GLOBALS['rc2f'][]=$l;}
function rc2same($e,$a,$l){rc2ok($e===$a,$l.' got '.var_export($a,true));}
$admin=get_users(array('role'=>'administrator','number'=>1,'fields'=>'ID')); wp_set_current_user((int)$admin[0]); do_action('init');
$ids=array();
function rc2post($status,$password='',$listed=null){global $ids;$id=wp_insert_post(array('post_type'=>'seed_directory','post_status'=>$status,'post_password'=>$password,'post_title'=>'RC2 MIGRATION '.count($ids),'post_excerpt'=>'Short '.count($ids),'post_content'=>'<p>Long '.count($ids).'</p>','meta_input'=>array('_seed_directory_status'=>'practicing','_seed_directory_country'=>'FR','_seed_directory_publication_authorized'=>'1')),true);if(is_wp_error($id))throw new RuntimeException($id->get_error_message());$ids[]=(int)$id;if(null!==$listed){if('0'===$listed){global $wpdb;$wpdb->insert($wpdb->postmeta,array('post_id'=>$id,'meta_key'=>'_seed_directory_publicly_listed','meta_value'=>'0'),array('%d','%s','%s'));clean_post_cache($id);}else{add_post_meta($id,'_seed_directory_publicly_listed',$listed,true);}}return(int)$id;}
delete_option(wp_seed_content_directory_public_listing_schema_option());delete_option(wp_seed_content_directory_public_listing_state_option());
$p1=rc2post('publish');$d=rc2post('draft');$pr=rc2post('private');$prot=rc2post('publish','secret');$one=rc2post('publish','','1');$zero=rc2post('publish','','0');
$before=array();foreach($ids as $id){$p=get_post($id);$before[$id]=array($p->post_status,$p->post_excerpt,$p->post_content,get_post_meta($id,'_seed_directory_status',true));}
$s=wp_seed_content_directory_upgrade_public_listing(2);rc2same('running',$s['status'],'Batch one resumable');$s=wp_seed_content_directory_upgrade_public_listing(2);rc2same('running',$s['status'],'Batch two resumable');$s=wp_seed_content_directory_upgrade_public_listing(2);rc2same('complete',$s['status'],'Migration complete');rc2same('1',get_post_meta($p1,'_seed_directory_publicly_listed',true),'Published absent listed');rc2same('',get_post_meta($d,'_seed_directory_publicly_listed',true),'Draft absent');rc2same('',get_post_meta($pr,'_seed_directory_publicly_listed',true),'Private absent');rc2same('',get_post_meta($prot,'_seed_directory_publicly_listed',true),'Protected absent');rc2same('1',get_post_meta($one,'_seed_directory_publicly_listed',true),'Explicit true kept');rc2same('0',get_post_meta($zero,'_seed_directory_publicly_listed',true),'Explicit nontrue kept');rc2same(1,$s['updated'],'One updated');rc2same(2,$s['kept'],'Two explicit kept');rc2same(3,$s['skipped'],'Three skipped');rc2same(0,$s['errors'],'No errors');
foreach($ids as $id){$p=get_post($id);rc2same($before[$id],array($p->post_status,$p->post_excerpt,$p->post_content,get_post_meta($id,'_seed_directory_status',true)),'Business data untouched '.$id);}
$meta=serialize(array_map(function($id){return get_post_meta($id);},$ids));$s2=wp_seed_content_directory_upgrade_public_listing(100);rc2same('complete',$s2['status'],'Second run complete');rc2same($meta,serialize(array_map(function($id){return get_post_meta($id);},$ids)),'Second run no metadata change');
foreach(array_reverse($ids) as $id)wp_delete_post($id,true);delete_option(wp_seed_content_directory_public_listing_schema_option());delete_option(wp_seed_content_directory_public_listing_state_option());
if($GLOBALS['rc2f']){fwrite(STDERR,'FAIL '.count($GLOBALS['rc2f']).' / '.$GLOBALS['rc2a'].PHP_EOL.implode(PHP_EOL,$GLOBALS['rc2f']).PHP_EOL);exit(1);}echo 'PASS '.$GLOBALS['rc2a'].' WordPress RC2 public listing migration assertions'.PHP_EOL;
