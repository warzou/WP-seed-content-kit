<?php
// Run only on a disposable, local WordPress with MariaDB/MySQL and Formation enabled.
if (PHP_SAPI !== 'cli' || getenv('WPSCK_FORMATION_CONCURRENCY') !== 'disposable-local') exit(2);
$wp_load = realpath(getenv('WP_SEED_WORDPRESS_LOAD'));
if (!$wp_load || basename($wp_load) !== 'wp-load.php') exit(2);
require $wp_load;
global $wpdb;
if (wp_get_environment_type() !== 'local' || !preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/D', DB_HOST)
    || !in_array(wp_parse_url(home_url(), PHP_URL_HOST), array('localhost', '127.0.0.1'), true)
    || !function_exists('wp_seed_content_formation_store_schedule')) exit(2);

$admin = get_users(array('role' => 'administrator', 'number' => 1, 'fields' => 'ID'));
if (!$admin) throw new RuntimeException('A fixture administrator is required');
wp_set_current_user($admin[0]);
$lock = 'wpsck_schedule_' . substr(hash('sha256', DB_NAME . ':' . $wpdb->prefix), 0, 40);
$free = function () use ($wpdb, $lock) {
    return '1' === (string) $wpdb->get_var($wpdb->prepare('SELECT IS_FREE_LOCK(%s)', $lock));
};
$until = function ($predicate, $seconds, $message) {
    $deadline = microtime(true) + $seconds;
    while (!$predicate()) {
        if (microtime(true) >= $deadline) throw new RuntimeException($message);
        usleep(20000);
    }
};
if (isset($argv[1]) && 'worker' === $argv[1]) {
    $values = json_decode(base64_decode($argv[2], true), true);
    $barrier = $argv[3];
    $conn = (int) $wpdb->get_var('SELECT CONNECTION_ID()');
    if ('first' === $argv[4]) {
        add_filter('wp_insert_post_data', function ($data) use ($barrier, $until, $conn, $free) {
            if ($data['post_type'] === 'seed_schedule' && $data['post_status'] === 'draft') {
                if ($free()) throw new RuntimeException('First writer does not hold the lock');
                file_put_contents($barrier . '/owner', (string) $conn);
                $until(function () use ($barrier) { clearstatcache(); return is_file($barrier . '/release'); }, 4, 'Barrier release timed out');
            }
            return $data;
        });
    } else {
        file_put_contents($barrier . '/waiter', (string) $conn);
    }
    $result = wp_seed_content_formation_store_schedule($values);
    echo wp_json_encode(array('connection' => $conn, 'id' => is_wp_error($result) ? 0 : (int) $result,
        'error' => is_wp_error($result) ? $result->get_error_code() : null,
        'details' => is_wp_error($result) ? $result->get_error_data() : null));
    exit;
}

if (PHP_VERSION_ID < 70400) throw new RuntimeException('The concurrent test runner requires PHP 7.4+');
$run = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wpsck-' . bin2hex(random_bytes(10));
if (!mkdir($run)) exit(2);
$checks = array();
$assert = function ($name, $pass) use (&$checks) {
    $checks[$name] = (bool) $pass;
    if (!$pass) throw new RuntimeException($name);
};
$fixture_ids = array();
$children = array();
$make = function ($type) use (&$fixture_ids, $run) {
    $id = wp_insert_post(array('post_type' => $type, 'post_status' => 'publish', 'post_title' => basename($run) . ' ' . $type), true);
    if (is_wp_error($id) || !$id) throw new RuntimeException('Fixture failed');
    $fixture_ids[] = $id;
    return $id;
};
try {
    $program = $make('seed_program');
    $section = $make('seed_section');
    $theme = $make('seed_theme');
    update_post_meta($section, '_seed_formation_program_id', $program);
    $values = array('program_id' => $program, 'section_id' => $section, 'theme_id' => $theme,
        'calendar_year' => 2027, 'start_date' => '2027-03-01', 'end_date' => '2027-03-01', 'status' => 'planned');
    $pair = function ($name, $a, $b) use ($run, $until, $wpdb, $lock, $free, $assert, &$children) {
        $dir = $run . '/' . $name;
        mkdir($dir);
        foreach (array('first' => $a, 'second' => $b) as $side => $values) {
            $cmd = array(PHP_BINARY, __FILE__, 'worker', base64_encode(wp_json_encode($values)), $dir, $side);
            $proc = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('file', $dir . '/' . $side . '.json', 'w'),
                2 => array('file', $dir . '/' . $side . '.err', 'w')), $pipes);
            if (!is_resource($proc)) throw new RuntimeException('Worker launch failed');
            fclose($pipes[0]);
            $children[] = $proc;
            if ($side === 'first') {
                $until(function () use ($dir) { clearstatcache(); return is_file($dir . '/owner'); }, 8, 'First lock not reached');
            }
        }
        $until(function () use ($dir, $wpdb) {
            clearstatcache();
            if (!is_file($dir . '/waiter')) return false;
            $id = (int) file_get_contents($dir . '/waiter');
            foreach ($wpdb->get_results('SHOW FULL PROCESSLIST', ARRAY_A) as $process) {
                if ((int) $process['Id'] === $id && stripos((string) $process['Info'], 'GET_LOCK(') !== false) return true;
            }
            return false;
        }, 3, 'Second writer was not observed waiting in GET_LOCK');
        $owner = (int) file_get_contents($dir . '/owner');
        $assert($name . '_observed_wait_with_distinct_connections', $owner !== (int) file_get_contents($dir . '/waiter')
            && $owner === (int) $wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)', $lock)));
        touch($dir . '/release');
        foreach ($children as $child) $assert($name . '_worker_exit_' . (int) $child, proc_close($child) === 0);
        $children = array();
        $first = json_decode(file_get_contents($dir . '/first.json'), true);
        $second = json_decode(file_get_contents($dir . '/second.json'), true);
        $assert($name . '_lock_released', $free());
        return array($first, $second);
    };
    list($first, $second) = $pair('identical', $values, $values);
    $assert('identical_exactly_one_saved', $first['id'] > 0 && $second['id'] === 0 && $second['error'] === 'invalid_values'
        && in_array('duplicate_schedule', $second['details'], true));
    $second_date = array_merge($values, array('start_date' => '2027-03-02', 'end_date' => '2027-03-02'));
    $third_date = array_merge($values, array('start_date' => '2027-03-03', 'end_date' => '2027-03-03'));
    list($other, $third) = $pair('distinct', $second_date, $third_date);
    $assert('distinct_both_saved', $other['id'] > 0 && $third['id'] > 0 && $other['id'] !== $third['id']);
    $assert('exclude_current_id', $first['id'] === wp_seed_content_formation_store_schedule($values, $first['id']) && $free());
    $invalid = wp_seed_content_formation_store_schedule($second_date, $first['id']);
    $assert('update_collision_rejected_and_released', is_wp_error($invalid) && in_array('duplicate_schedule', $invalid->get_error_data(), true) && $free());
    $values['end_date'] = '2027-03-04';
    $assert('update_distinct_tuple_after_error', $first['id'] === wp_seed_content_formation_store_schedule($values, $first['id']) && $free());
    $rows = wp_seed_content_formation_get_schedules(array('program_id' => $program, 'year' => 2027));
    $assert('three_schedules_shared_theme', count($rows) === 3 && count(array_unique(array_column($rows, 'theme_id'))) === 1);
} finally {
    foreach ($children as $child) if (is_resource($child)) { proc_terminate($child); proc_close($child); }
    if (isset($program)) {
        $ids = get_posts(array('post_type' => 'seed_schedule', 'post_status' => 'any', 'fields' => 'ids',
            'posts_per_page' => -1, 'meta_key' => '_seed_formation_program_id', 'meta_value' => $program));
        foreach ($ids as $id) wp_delete_post($id, true);
    }
    foreach (array_reverse($fixture_ids) as $id) wp_delete_post($id, true);
    foreach (glob($run . '/*', GLOB_ONLYDIR) as $dir) {
        foreach (glob($dir . '/*') as $file) unlink($file);
        rmdir($dir);
    }
    rmdir($run);
}
echo wp_json_encode(array('db' => $wpdb->get_var('SELECT VERSION()'), 'checks' => $checks), JSON_PRETTY_PRINT) . PHP_EOL;
