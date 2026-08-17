<?php

define('ABSPATH', __DIR__ . '/');

require_once __DIR__ . '/../plugin/includes/core/content-data.php';

$assertions = 0;
$failures = array();

function directory_more_same($expected, $actual, $label)
{
    global $assertions, $failures;
    $assertions++;
    if ($expected !== $actual) {
        $failures[] = $label;
    }
}

$cases = array(
    'empty' => array('', array('full' => '', 'intro' => '', 'more' => '', 'has_more' => false)),
    'none' => array('Contenu complet.', array('full' => 'Contenu complet.', 'intro' => 'Contenu complet.', 'more' => '', 'has_more' => false)),
    'lookalike' => array('Avant.<!--moreover-->Après.', array('full' => 'Avant.<!--moreover-->Après.', 'intro' => 'Avant.<!--moreover-->Après.', 'more' => '', 'has_more' => false)),
    'simple' => array('Avant.<!--more-->Après.', array('full' => 'Avant.Après.', 'intro' => 'Avant.', 'more' => 'Après.', 'has_more' => true)),
    'custom' => array('Avant.<!--more Lire la suite-->Après.', array('full' => 'Avant.Après.', 'intro' => 'Avant.', 'more' => 'Après.', 'has_more' => true)),
    'noteaser' => array('Avant.<!--more--><!--noteaser-->Après.', array('full' => 'Avant.Après.', 'intro' => 'Avant.', 'more' => 'Après.', 'has_more' => true)),
    'multiple' => array('A<!--more-->B<!--more Deuxième-->C', array('full' => 'ABC', 'intro' => 'A', 'more' => 'BC', 'has_more' => true)),
    'start' => array('<!--more-->Après.', array('full' => 'Après.', 'intro' => '', 'more' => 'Après.', 'has_more' => true)),
    'end' => array('Avant.<!--more-->', array('full' => 'Avant.', 'intro' => 'Avant.', 'more' => '', 'has_more' => true)),
    'spaces' => array(" Avant. \n <!--more--> \n Après. ", array('full' => "Avant. \n  \n Après.", 'intro' => 'Avant.', 'more' => 'Après.', 'has_more' => true)),
    'html' => array('<p>Avant.</p><!--more--><p>Après.</p>', array('full' => '<p>Avant.</p><p>Après.</p>', 'intro' => '<p>Avant.</p>', 'more' => '<p>Après.</p>', 'has_more' => true)),
    'shortcode' => array('[gallery ids="1"]<!--more-->[seed_directory]', array('full' => '[gallery ids="1"][seed_directory]', 'intro' => '[gallery ids="1"]', 'more' => '[seed_directory]', 'has_more' => true)),
    'accents' => array('Été, cœur.<!--more-->À bientôt.', array('full' => 'Été, cœur.À bientôt.', 'intro' => 'Été, cœur.', 'more' => 'À bientôt.', 'has_more' => true)),
    'gutenberg' => array(
        "<!-- wp:paragraph -->\n<p>Avant.</p>\n<!-- /wp:paragraph -->\n<!-- wp:more -->\n<!--more-->\n<!-- /wp:more -->\n<!-- wp:paragraph -->\n<p>Après.</p>\n<!-- /wp:paragraph -->",
        array(
            'full' => "<!-- wp:paragraph -->\n<p>Avant.</p>\n<!-- /wp:paragraph -->\n\n\n\n<!-- wp:paragraph -->\n<p>Après.</p>\n<!-- /wp:paragraph -->",
            'intro' => "<!-- wp:paragraph -->\n<p>Avant.</p>\n<!-- /wp:paragraph -->",
            'more' => "<!-- wp:paragraph -->\n<p>Après.</p>\n<!-- /wp:paragraph -->",
            'has_more' => true,
        ),
    ),
    'gutenberg_core' => array(
        "<!-- wp:paragraph --><p>Avant.</p><!-- /wp:paragraph --><!-- wp:core/more --><!--more--><!-- /wp:core/more --><!-- wp:paragraph --><p>Après.</p><!-- /wp:paragraph -->",
        array(
            'full' => '<!-- wp:paragraph --><p>Avant.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Après.</p><!-- /wp:paragraph -->',
            'intro' => '<!-- wp:paragraph --><p>Avant.</p><!-- /wp:paragraph -->',
            'more' => '<!-- wp:paragraph --><p>Après.</p><!-- /wp:paragraph -->',
            'has_more' => true,
        ),
    ),
    'complex' => array(
        "<!-- wp:heading --><h2>Titre</h2><!-- /wp:heading -->\n<p>Intro <strong>riche</strong>.</p>\n<!--more Continuer-->\n<ul><li>Suite</li></ul>\n[shortcode attr=\"é\"]",
        array(
            'full' => "<!-- wp:heading --><h2>Titre</h2><!-- /wp:heading -->\n<p>Intro <strong>riche</strong>.</p>\n\n<ul><li>Suite</li></ul>\n[shortcode attr=\"é\"]",
            'intro' => "<!-- wp:heading --><h2>Titre</h2><!-- /wp:heading -->\n<p>Intro <strong>riche</strong>.</p>",
            'more' => "<ul><li>Suite</li></ul>\n[shortcode attr=\"é\"]",
            'has_more' => true,
        ),
    ),
);

foreach ($cases as $name => $case) {
    directory_more_same($case[1], wp_seed_content_split_wordpress_more($case[0]), $name);
}

if ($failures) {
    fwrite(STDERR, 'FAIL ' . count($failures) . '/' . $assertions . ': ' . implode(', ', $failures) . PHP_EOL);
    exit(1);
}

echo 'PASS ' . $assertions . ' Directory More marker assertions' . PHP_EOL;
