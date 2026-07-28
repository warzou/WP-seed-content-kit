<?php

define('ABSPATH', __DIR__ . '/');
define('WP_SEED_CONTENT_KIT_DIR', dirname(__DIR__) . '/plugin/');
$GLOBALS['a']=0; $GLOBALS['f']=array(); $GLOBALS['p']=array(); $GLOBALS['m']=array(); $GLOBALS['o']=array(); $GLOBALS['fail_add']=array(); $GLOBALS['actions']=array();
function ok($c,$l){$GLOBALS['a']++;if(!$c)$GLOBALS['f'][]=$l;}
function same($e,$a,$l){ok($e===$a,$l);}
function absint($v){return abs((int)$v);}
function add_action($h,$c,$p=10,$n=1){$GLOBALS['actions'][$h][]=array($c,$p,$n);}
function do_action($h){$GLOBALS['actions_fired'][]=$h;}
function get_option($k,$d=false){return array_key_exists($k,$GLOBALS['o'])?$GLOBALS['o'][$k]:$d;}
function update_option($k,$v,$autoload=null){$GLOBALS['o'][$k]=$v;return true;}
function get_posts($args){$ids=array_keys($GLOBALS['p']);sort($ids,SORT_NUMERIC);return $ids;}
function get_post($id){return isset($GLOBALS['p'][$id])?$GLOBALS['p'][$id]:null;}
function metadata_exists($type,$id,$key){return array_key_exists($key,isset($GLOBALS['m'][$id])?$GLOBALS['m'][$id]:array());}
function add_post_meta($id,$key,$value,$unique=false){if(!empty($GLOBALS['fail_add'][$id]))return false;if($unique&&metadata_exists('post',$id,$key))return false;$GLOBALS['m'][$id][$key]=$value;return true;}
function fixture($id,$status='publish',$password='',$meta=null){$GLOBALS['p'][$id]=(object)array('ID'=>$id,'post_type'=>'seed_directory','post_status'=>$status,'post_password'=>$password,'post_title'=>'P'.$id,'post_excerpt'=>'S'.$id,'post_content'=>'F'.$id);$GLOBALS['m'][$id]=array('_seed_directory_status'=>'practicing');if(null!==$meta)$GLOBALS['m'][$id]['_seed_directory_publicly_listed']=$meta;}
require WP_SEED_CONTENT_KIT_DIR.'includes/modules/directory/public-listing-upgrade.php';
fixture(1); fixture(2,'draft'); fixture(3,'private'); fixture(4,'publish','secret'); fixture(5,'publish','','1'); fixture(6,'publish','','0'); fixture(7); fixture(8); $GLOBALS['fail_add'][8]=true;
$before=serialize($GLOBALS['p']);
$s=wp_seed_content_directory_upgrade_public_listing(3);
same('running',$s['status'],'First batch remains resumable'); same(3,$s['cursor'],'First cursor'); same('1',$GLOBALS['m'][1]['_seed_directory_publicly_listed'],'Published absent migrated'); ok(!metadata_exists('post',2,'_seed_directory_publicly_listed'),'Draft remains absent'); ok(!metadata_exists('post',3,'_seed_directory_publicly_listed'),'Private remains absent');
$s=wp_seed_content_directory_upgrade_public_listing(3);
same(6,$s['cursor'],'Second cursor'); same('1',$GLOBALS['m'][5]['_seed_directory_publicly_listed'],'Explicit true preserved'); same('0',$GLOBALS['m'][6]['_seed_directory_publicly_listed'],'Explicit nontrue preserved');
$s=wp_seed_content_directory_upgrade_public_listing(3);
same('complete',$s['status'],'Migration completes'); same(8,$s['scanned'],'All fixtures scanned'); same(2,$s['updated'],'Only eligible absent values updated'); same(2,$s['kept'],'Explicit values kept'); same(3,$s['skipped'],'Draft private protected skipped'); same(1,$s['errors'],'Write failure counted'); same(array(1,7),$s['updated_ids'],'Rollback IDs exact'); same(1,$GLOBALS['o']['wp_seed_content_directory_public_listing_schema'],'Schema marker complete'); same($before,serialize($GLOBALS['p']),'Post records untouched');
$state_before=serialize($GLOBALS['o']); $meta_before=serialize($GLOBALS['m']); $s2=wp_seed_content_directory_upgrade_public_listing(100); same('complete',$s2['status'],'Second execution complete'); same($state_before,serialize($GLOBALS['o']),'Second execution does not rewrite state'); same($meta_before,serialize($GLOBALS['m']),'Second execution changes no metadata');
if($GLOBALS['f']){fwrite(STDERR,'FAIL '.count($GLOBALS['f']).' / '.$GLOBALS['a'].PHP_EOL.implode(PHP_EOL,$GLOBALS['f']).PHP_EOL);exit(1);} echo 'PASS '.$GLOBALS['a'].' Directory public listing migration assertions'.PHP_EOL;
