<?php

if (!defined('ABSPATH')) {
    exit;
}

function wp_seed_content_kit_get_usage_tabs()
{
    return array(
        'functioning' => __('Fonctionnement', 'wp-seed-content-kit'),
        'templates' => __('Templates', 'wp-seed-content-kit'),
        'collections' => __('Collections', 'wp-seed-content-kit'),
        'integrations' => __('Intégrer dans une page', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_kit_get_usage_integration_tabs()
{
    return array(
        'shortcodes' => __('Shortcodes', 'wp-seed-content-kit'),
        'gutenberg' => __('Gutenberg', 'wp-seed-content-kit'),
        'spectra' => __('Spectra', 'wp-seed-content-kit'),
        'divi' => __('Divi', 'wp-seed-content-kit'),
    );
}

function wp_seed_content_kit_get_current_usage_tab()
{
    $tab = isset($_GET['usage_tab']) ? sanitize_key(wp_unslash($_GET['usage_tab'])) : 'functioning';
    return array_key_exists($tab, wp_seed_content_kit_get_usage_tabs()) ? $tab : 'functioning';
}

function wp_seed_content_kit_get_current_usage_integration_tab()
{
    $tab = isset($_GET['integration']) ? sanitize_key(wp_unslash($_GET['integration'])) : 'shortcodes';
    return array_key_exists($tab, wp_seed_content_kit_get_usage_integration_tabs()) ? $tab : 'shortcodes';
}

function wp_seed_content_kit_get_usage_url($tab, $integration = '')
{
    $args = array(
        'page' => 'wp-seed-content-kit-usage',
        'usage_tab' => sanitize_key($tab),
    );
    if ('' !== $integration) {
        $args['integration'] = sanitize_key($integration);
    }

    return add_query_arg($args, admin_url('admin.php'));
}

function wp_seed_content_kit_enqueue_usage_assets($hook_suffix)
{
    $expected = isset($GLOBALS['wp_seed_content_kit_usage_page_hook'])
        ? $GLOBALS['wp_seed_content_kit_usage_page_hook']
        : '';

    if ('' === $expected || $hook_suffix !== $expected) {
        return;
    }

    wp_enqueue_style(
        'wp-seed-content-kit-admin-usage',
        WP_SEED_CONTENT_KIT_URL . 'assets/css/admin-usage.css',
        array(),
        WP_SEED_CONTENT_KIT_VERSION
    );
    wp_enqueue_script(
        'wp-seed-content-kit-admin-usage',
        WP_SEED_CONTENT_KIT_URL . 'assets/admin-usage.js',
        array(),
        WP_SEED_CONTENT_KIT_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'wp_seed_content_kit_enqueue_usage_assets');

function wp_seed_content_kit_render_usage_tabs($current)
{
    echo '<nav class="nav-tab-wrapper" role="tablist" aria-label="' . esc_attr__('Rubriques d’utilisation', 'wp-seed-content-kit') . '">';
    foreach (wp_seed_content_kit_get_usage_tabs() as $tab => $label) {
        $active = $current === $tab;
        printf(
            '<a class="nav-tab%2$s" id="seed-usage-tab-%1$s" role="tab" href="%3$s" aria-selected="%4$s" aria-controls="seed-usage-panel-%1$s"%5$s>%6$s</a>',
            esc_attr($tab),
            $active ? ' nav-tab-active' : '',
            esc_url(wp_seed_content_kit_get_usage_url($tab)),
            $active ? 'true' : 'false',
            $active ? ' aria-current="page"' : '',
            esc_html($label)
        );
    }
    echo '</nav>';
}

function wp_seed_content_kit_render_usage_integration_tabs($current)
{
    echo '<nav class="seed-usage-subtabs" role="tablist" aria-label="' . esc_attr__('Méthodes d’intégration', 'wp-seed-content-kit') . '">';
    foreach (wp_seed_content_kit_get_usage_integration_tabs() as $tab => $label) {
        $active = $current === $tab;
        printf(
            '<a class="button%2$s" id="seed-usage-integration-tab-%1$s" role="tab" href="%3$s" aria-selected="%4$s" aria-controls="seed-usage-integration-panel"%5$s>%6$s</a>',
            esc_attr($tab),
            $active ? ' button-primary' : '',
            esc_url(wp_seed_content_kit_get_usage_url('integrations', $tab)),
            $active ? 'true' : 'false',
            $active ? ' aria-current="page"' : '',
            esc_html($label)
        );
    }
    echo '</nav>';
}

function wp_seed_content_kit_get_usage_status_label($status)
{
    $labels = array(
        'functional' => __('Fonctionnel', 'wp-seed-content-kit'),
        'indirect' => __('Indirect', 'wp-seed-content-kit'),
        'experimental' => __('Expérimental', 'wp-seed-content-kit'),
        'unavailable' => __('Non disponible', 'wp-seed-content-kit'),
    );

    return isset($labels[$status]) ? $labels[$status] : '';
}

function wp_seed_content_kit_render_usage_status($status, $label)
{
    printf(
        '<p class="seed-usage-status seed-usage-status--%1$s"><strong>%2$s :</strong> %3$s</p>',
        esc_attr(sanitize_html_class($status)),
        esc_html($label),
        esc_html(wp_seed_content_kit_get_usage_status_label($status))
    );
}

function wp_seed_content_kit_render_usage_example($id, $label, $value)
{
    ?>
    <div class="seed-usage-copy">
        <label for="<?php echo esc_attr($id); ?>"><strong><?php echo esc_html($label); ?></strong></label>
        <div class="seed-usage-copy__controls">
            <input id="<?php echo esc_attr($id); ?>" class="large-text code" type="text" readonly="readonly" value="<?php echo esc_attr($value); ?>">
            <button type="button" class="button" data-seed-usage-copy="<?php echo esc_attr($id); ?>" data-copy-label="<?php echo esc_attr__('Copié.', 'wp-seed-content-kit'); ?>">
                <?php echo esc_html__('Copier', 'wp-seed-content-kit'); ?>
            </button>
            <span class="seed-usage-copy__status" aria-live="polite"></span>
        </div>
    </div>
    <?php
}

function wp_seed_content_kit_render_usage_functioning()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-functioning-title">
        <h2 id="seed-usage-functioning-title"><?php esc_html_e('Du contenu à la page', 'wp-seed-content-kit'); ?></h2>
        <ol class="seed-usage-flow">
            <li><strong><?php esc_html_e('Contenus', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Les informations que vous créez et mettez à jour.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Collections', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Quels contenus afficher et dans quel ordre.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Templates', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Comment présenter chaque contenu.', 'wp-seed-content-kit'); ?></span></li>
            <li><strong><?php esc_html_e('Intégrations', 'wp-seed-content-kit'); ?></strong><span><?php esc_html_e('Où insérer le résultat dans une page.', 'wp-seed-content-kit'); ?></span></li>
        </ol>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-examples-title">
        <h2 id="seed-usage-examples-title"><?php esc_html_e('Exemples concrets', 'wp-seed-content-kit'); ?></h2>
        <div class="seed-usage-examples">
            <?php
            $examples = array(
                __('Annuaire', 'wp-seed-content-kit') => array(
                    __('Contenus', 'wp-seed-content-kit') => __('Les personnes inscrites dans l’Annuaire.', 'wp-seed-content-kit'),
                    __('Collection', 'wp-seed-content-kit') => __('Sélectionner les personnes « En exercice », les ordonner et les limiter.', 'wp-seed-content-kit'),
                    __('Template', 'wp-seed-content-kit') => __('Définir l’apparence de chaque carte.', 'wp-seed-content-kit'),
                    __('Intégration', 'wp-seed-content-kit') => __('Insérer l’annuaire avec un shortcode, Gutenberg, Spectra ou Divi.', 'wp-seed-content-kit'),
                ),
                __('Témoignages', 'wp-seed-content-kit') => array(
                    __('Contenus', 'wp-seed-content-kit') => __('Les témoignages publiés.', 'wp-seed-content-kit'),
                    __('Collection', 'wp-seed-content-kit') => __('Choisir les témoignages mis en avant ou les plus récents.', 'wp-seed-content-kit'),
                    __('Template', 'wp-seed-content-kit') => __('Définir le style de la citation et de l’auteur.', 'wp-seed-content-kit'),
                    __('Intégration', 'wp-seed-content-kit') => __('Insérer le résultat dans une page.', 'wp-seed-content-kit'),
                ),
                __('Citations', 'wp-seed-content-kit') => array(
                    __('Contenus', 'wp-seed-content-kit') => __('Les citations publiées.', 'wp-seed-content-kit'),
                    __('Collection', 'wp-seed-content-kit') => __('Sélectionner les citations selon les critères disponibles.', 'wp-seed-content-kit'),
                    __('Template', 'wp-seed-content-kit') => __('Définir leur apparence.', 'wp-seed-content-kit'),
                    __('Intégration', 'wp-seed-content-kit') => __('Afficher le résultat dans une page.', 'wp-seed-content-kit'),
                ),
            );
            foreach ($examples as $module => $steps) :
                ?>
                <article class="seed-usage-example">
                    <h3><?php echo esc_html($module); ?></h3>
                    <dl>
                        <?php foreach ($steps as $label => $description) : ?>
                            <dt><?php echo esc_html($label); ?></dt>
                            <dd><?php echo esc_html($description); ?></dd>
                        <?php endforeach; ?>
                    </dl>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}
function wp_seed_content_kit_usage_placeholder($key, $label, $type, $description, $visibility, $empty)
{
    return compact('key', 'label', 'type', 'description', 'visibility', 'empty');
}

function wp_seed_content_kit_get_usage_template_placeholder_catalog()
{
    $public = __('Publique', 'wp-seed-content-kit');
    $approved = __('Publique si autorisée', 'wp-seed-content-kit');
    $empty = __('Chaîne vide', 'wp-seed-content-kit');

    return array(
        __('Témoignages', 'wp-seed-content-kit') => array(
            wp_seed_content_kit_usage_placeholder('photo', __('Photo', 'wp-seed-content-kit'), 'image', __('Image complète du témoignage.', 'wp-seed-content-kit'), $public, __('Image absente', 'wp-seed-content-kit')),
            wp_seed_content_kit_usage_placeholder('photo_url', __('URL de la photo', 'wp-seed-content-kit'), 'url', __('Adresse de l’image.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('photo_alt', __('Texte alternatif', 'wp-seed-content-kit'), 'text', __('Alternative textuelle de la photo.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('name', __('Nom ou initiales', 'wp-seed-content-kit'), 'text', __('Identité publique affichée.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('text', __('Témoignage', 'wp-seed-content-kit'), 'textarea', __('Texte public du témoignage.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('context', __('Contexte', 'wp-seed-content-kit'), 'text', __('Information complémentaire publique.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('date', __('Date', 'wp-seed-content-kit'), 'text', __('Date publique du témoignage.', 'wp-seed-content-kit'), $public, $empty),
        ),
        __('Citations', 'wp-seed-content-kit') => array(
            wp_seed_content_kit_usage_placeholder('quote', __('Citation', 'wp-seed-content-kit'), 'textarea', __('Texte de la citation.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('author', __('Auteur', 'wp-seed-content-kit'), 'text', __('Auteur affiché.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('era', __('Époque ou date', 'wp-seed-content-kit'), 'text', __('Repère temporel public.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('source', __('Source', 'wp-seed-content-kit'), 'text', __('Source ou contexte public.', 'wp-seed-content-kit'), $public, $empty),
        ),
        __('Annuaire', 'wp-seed-content-kit') => array(
            wp_seed_content_kit_usage_placeholder('directory.name', __('Nom', 'wp-seed-content-kit'), 'text', __('Nom affiché de la personne.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.photo', __('Photo', 'wp-seed-content-kit'), 'image', __('Photo et texte alternatif validés.', 'wp-seed-content-kit'), $public, __('Image absente', 'wp-seed-content-kit')),
            wp_seed_content_kit_usage_placeholder('directory.bio', __('Présentation', 'wp-seed-content-kit'), 'textarea', __('Présentation publique.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.status', __('Code du statut', 'wp-seed-content-kit'), 'text', __('Valeur technique du statut professionnel.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.status_label', __('Statut', 'wp-seed-content-kit'), 'text', __('Libellé public du statut professionnel.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.city', __('Ville', 'wp-seed-content-kit'), 'text', __('Ville publique.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.postal_code', __('Code postal', 'wp-seed-content-kit'), 'text', __('Code postal public.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.department', __('Département', 'wp-seed-content-kit'), 'text', __('Département public.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.country', __('Pays', 'wp-seed-content-kit'), 'text', __('Pays public.', 'wp-seed-content-kit'), $public, $empty),
            wp_seed_content_kit_usage_placeholder('directory.phone', __('Téléphone', 'wp-seed-content-kit'), 'tel', __('Téléphone validé et rendu public.', 'wp-seed-content-kit'), $approved, $empty),
            wp_seed_content_kit_usage_placeholder('directory.email', __('E-mail', 'wp-seed-content-kit'), 'email', __('E-mail validé et rendu public.', 'wp-seed-content-kit'), $approved, $empty),
            wp_seed_content_kit_usage_placeholder('directory.website', __('Site internet', 'wp-seed-content-kit'), 'url', __('Adresse web validée et rendue publique.', 'wp-seed-content-kit'), $approved, $empty),
            wp_seed_content_kit_usage_placeholder('directory.facebook', __('Facebook', 'wp-seed-content-kit'), 'url', __('Profil validé et rendu public.', 'wp-seed-content-kit'), $approved, $empty),
            wp_seed_content_kit_usage_placeholder('directory.instagram', __('Instagram', 'wp-seed-content-kit'), 'url', __('Profil validé et rendu public.', 'wp-seed-content-kit'), $approved, $empty),
            wp_seed_content_kit_usage_placeholder('directory.featured', __('Mise en avant', 'wp-seed-content-kit'), 'text', __('Retourne 1 pour une fiche mise en avant.', 'wp-seed-content-kit'), $public, __('Vide si non cochée', 'wp-seed-content-kit')),
        ),
    );
}

function wp_seed_content_kit_get_usage_template_placeholders()
{
    $result = array();
    foreach (wp_seed_content_kit_get_usage_template_placeholder_catalog() as $module => $definitions) {
        $result[$module] = array();
        foreach ($definitions as $definition) {
            $result[$module][] = $definition['key'];
        }
    }

    return $result;
}

function wp_seed_content_kit_render_usage_placeholder_table($module, $definitions)
{
    ?>
    <details class="seed-usage-details"<?php echo __('Annuaire', 'wp-seed-content-kit') === $module ? ' open' : ''; ?>>
        <summary><strong><?php echo esc_html($module); ?></strong> <span>(<?php echo esc_html(count($definitions)); ?>)</span></summary>
        <div class="seed-usage-table-wrap">
            <table class="widefat striped seed-usage-table seed-usage-placeholder-table">
                <thead><tr><th><?php esc_html_e('Clé', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Libellé', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Type', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Description', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Visibilité', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Si vide', 'wp-seed-content-kit'); ?></th></tr></thead>
                <tbody>
                    <?php foreach ($definitions as $definition) : ?>
                        <?php $id = 'seed-placeholder-' . sanitize_html_class(str_replace('.', '-', $definition['key'])); ?>
                        <tr>
                            <td data-label="<?php esc_attr_e('Clé', 'wp-seed-content-kit'); ?>"><code>{{<?php echo esc_html($definition['key']); ?>}}</code> <button type="button" class="button button-small seed-usage-copy-key" data-seed-usage-copy-value="{{<?php echo esc_attr($definition['key']); ?>}}" data-copy-label="<?php esc_attr_e('Copié.', 'wp-seed-content-kit'); ?>" aria-describedby="<?php echo esc_attr($id); ?>"><?php esc_html_e('Copier', 'wp-seed-content-kit'); ?></button><span id="<?php echo esc_attr($id); ?>" class="seed-usage-copy__status" aria-live="polite"></span></td>
                            <td data-label="<?php esc_attr_e('Libellé', 'wp-seed-content-kit'); ?>"><?php echo esc_html($definition['label']); ?></td>
                            <td data-label="<?php esc_attr_e('Type', 'wp-seed-content-kit'); ?>"><code><?php echo esc_html($definition['type']); ?></code></td>
                            <td data-label="<?php esc_attr_e('Description', 'wp-seed-content-kit'); ?>"><?php echo esc_html($definition['description']); ?></td>
                            <td data-label="<?php esc_attr_e('Visibilité', 'wp-seed-content-kit'); ?>"><?php echo esc_html($definition['visibility']); ?></td>
                            <td data-label="<?php esc_attr_e('Si vide', 'wp-seed-content-kit'); ?>"><?php echo esc_html($definition['empty']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </details>
    <?php
}

function wp_seed_content_kit_render_usage_templates()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-templates-title">
        <h2 id="seed-usage-templates-title"><?php esc_html_e('Un Template définit comment un contenu est présenté.', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Témoignages, Citations et Annuaire utilisent le même moteur de Templates. Un Template présente un contenu ; il ne sélectionne jamais les contenus.', 'wp-seed-content-kit'); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url(admin_url('edit.php?post_type=seed_template')); ?>"><?php esc_html_e('Gérer les Templates', 'wp-seed-content-kit'); ?></a>
            <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type=seed_template')); ?>"><?php esc_html_e('Créer un Template', 'wp-seed-content-kit'); ?></a>
        </p>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-template-behavior-title">
        <h2 id="seed-usage-template-behavior-title"><?php esc_html_e('Modules, sources et fallback', 'wp-seed-content-kit'); ?></h2>
        <div class="seed-usage-table-wrap">
            <table class="widefat striped seed-usage-table">
                <thead><tr><th><?php esc_html_e('Modules', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Source native', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Source Divi Library', 'wp-seed-content-kit'); ?></th><th><?php esc_html_e('Fallback', 'wp-seed-content-kit'); ?></th></tr></thead>
                <tbody><tr>
                    <td data-label="<?php esc_attr_e('Modules', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Témoignages, Citations, Annuaire', 'wp-seed-content-kit'); ?></td>
                    <td data-label="<?php esc_attr_e('Source native', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Contenu du Template WordPress', 'wp-seed-content-kit'); ?></td>
                    <td data-label="<?php esc_attr_e('Source Divi Library', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Layout publié et compatible', 'wp-seed-content-kit'); ?></td>
                    <td data-label="<?php esc_attr_e('Fallback', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Rendu natif du module si aucun Template n’est choisi ou si le Template demandé ne peut pas être utilisé.', 'wp-seed-content-kit'); ?></td>
                </tr></tbody>
            </table>
        </div>
        <p><?php esc_html_e('Le Template est facultatif. Sans Template choisi, le rendu natif s’applique. L’attribut template du shortcode choisit la présentation au moment de l’intégration, sans association enregistrée avec une Collection.', 'wp-seed-content-kit'); ?></p>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-placeholders-title">
        <h2 id="seed-usage-placeholders-title"><?php esc_html_e('Placeholders disponibles', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Seules les données de rendu publiques sont listées. Les données privées ne sont jamais proposées comme placeholders.', 'wp-seed-content-kit'); ?></p>
        <?php foreach (wp_seed_content_kit_get_usage_template_placeholder_catalog() as $module => $definitions) : ?>
            <?php wp_seed_content_kit_render_usage_placeholder_table($module, $definitions); ?>
        <?php endforeach; ?>
    </section>
    <?php
}
function wp_seed_content_kit_get_usage_collection_catalog()
{
    return array(
        __('Témoignages', 'wp-seed-content-kit') => array(
            'parameters' => array(
                'ids' => __('Identifiants positifs séparés par des virgules.', 'wp-seed-content-kit'),
                'featured' => __('all, only ou exclude ; true et false restent compatibles.', 'wp-seed-content-kit'),
                'context' => __('Contexte public exact.', 'wp-seed-content-kit'),
                'limit' => __('0 pour tout afficher, sinon 1 à 24.', 'wp-seed-content-kit'),
                'orderby' => __('display_order, date, testimonial_date ou id.', 'wp-seed-content-kit'),
                'order' => __('asc ou desc.', 'wp-seed-content-kit'),
            ),
            'default' => __('3 témoignages, triés par date décroissante.', 'wp-seed-content-kit'),
            'empty' => __('Un message indique qu’aucun témoignage n’est disponible.', 'wp-seed-content-kit'),
            'example' => '[seed_testimonials featured="only" limit="3" orderby="date" order="desc"]',
        ),
        __('Citations', 'wp-seed-content-kit') => array(
            'parameters' => array(
                'mode' => __('daily pour la citation quotidienne, sinon sélection standard.', 'wp-seed-content-kit'),
                'featured' => __('true pour les citations mises en avant, sinon all.', 'wp-seed-content-kit'),
                'limit' => __('Vide pour une citation, 0 pour toutes, ou un entier positif.', 'wp-seed-content-kit'),
                'orderby' => __('random, author, date ou menu_order.', 'wp-seed-content-kit'),
                'order' => __('asc ou desc ; random force un ordre technique stable.', 'wp-seed-content-kit'),
            ),
            'default' => __('Une citation aléatoire.', 'wp-seed-content-kit'),
            'empty' => __('Un message indique qu’aucune citation n’est disponible.', 'wp-seed-content-kit'),
            'example' => '[seed_quotes mode="daily"]',
        ),
        __('Annuaire', 'wp-seed-content-kit') => array(
            'parameters' => array(
                'status' => __('all, practicing ou seeking_models.', 'wp-seed-content-kit'),
                'department' => __('Département public exact.', 'wp-seed-content-kit'),
                'country' => __('Pays public exact.', 'wp-seed-content-kit'),
                'featured' => __('all, only ou exclude.', 'wp-seed-content-kit'),
                'ids' => __('Identifiants positifs séparés par des virgules.', 'wp-seed-content-kit'),
                'limit' => __('0 pour tout afficher, sinon 1 à 100.', 'wp-seed-content-kit'),
                'orderby' => __('display_order, name, date ou id.', 'wp-seed-content-kit'),
                'order' => __('asc ou desc.', 'wp-seed-content-kit'),
            ),
            'default' => __('Toutes les fiches éligibles, ordre manuel puis nom croissant.', 'wp-seed-content-kit'),
            'empty' => __('Un message indique qu’aucune fiche n’est disponible.', 'wp-seed-content-kit'),
            'example' => '[seed_directory status="practicing" orderby="display_order" order="asc"]',
        ),
    );
}

function wp_seed_content_kit_render_usage_collections()
{
    ?>
    <section class="seed-usage-section" aria-labelledby="seed-usage-collections-title">
        <h2 id="seed-usage-collections-title"><?php esc_html_e('Une Collection définit quels contenus afficher et dans quel ordre.', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Une Collection n’est pas un contenu enregistré. Ses paramètres sont transmis au moment du rendu et ne sont jamais associés durablement à un Template.', 'wp-seed-content-kit'); ?></p>
        <p><strong><?php esc_html_e('Aucune recherche publique et aucun filtre public visible.', 'wp-seed-content-kit'); ?></strong> <?php esc_html_e('L’administrateur choisit les filtres lors de l’intégration dans la page.', 'wp-seed-content-kit'); ?></p>

        <?php foreach (wp_seed_content_kit_get_usage_collection_catalog() as $module => $definition) : ?>
            <details class="seed-usage-details"<?php echo __('Annuaire', 'wp-seed-content-kit') === $module ? ' open' : ''; ?>>
                <summary><strong><?php echo esc_html($module); ?></strong></summary>
                <div class="seed-usage-collection">
                    <dl class="seed-usage-parameters">
                        <?php foreach ($definition['parameters'] as $parameter => $description) : ?>
                            <dt><code><?php echo esc_html($parameter); ?></code></dt>
                            <dd><?php echo esc_html($description); ?></dd>
                        <?php endforeach; ?>
                    </dl>
                    <p><strong><?php esc_html_e('Par défaut :', 'wp-seed-content-kit'); ?></strong> <?php echo esc_html($definition['default']); ?></p>
                    <p><strong><?php esc_html_e('Zéro résultat :', 'wp-seed-content-kit'); ?></strong> <?php echo esc_html($definition['empty']); ?></p>
                    <?php wp_seed_content_kit_render_usage_example('seed-collection-example-' . sanitize_html_class($module), __('Exemple', 'wp-seed-content-kit'), $definition['example']); ?>
                </div>
            </details>
        <?php endforeach; ?>
    </section>

    <section class="seed-usage-section" aria-labelledby="seed-usage-generators-title">
        <h2 id="seed-usage-generators-title"><?php esc_html_e('Générateurs de Collections et shortcodes', 'wp-seed-content-kit'); ?></h2>
        <p><?php esc_html_e('Choisissez un module, ses filtres, sa limite, son ordre et, si nécessaire, un Template. Le résultat est un shortcode à copier ; aucun réglage ni aucune Collection ne sont enregistrés.', 'wp-seed-content-kit'); ?></p>
        <?php wp_seed_content_kit_render_generators_tab(); ?>
        <?php wp_seed_content_kit_render_directory_generator(); ?>
    </section>
    <?php
}
function wp_seed_content_kit_render_usage_shortcodes()
{
    wp_seed_content_kit_render_usage_status('functional', __('Méthode canonique', 'wp-seed-content-kit'));
    ?>
    <p><?php esc_html_e('Copiez un shortcode dans une page. La Collection sélectionne les contenus ; l’attribut template facultatif choisit leur présentation. Sans Template, le rendu natif s’applique.', 'wp-seed-content-kit'); ?></p>
    <?php
    wp_seed_content_kit_render_usage_example('seed-usage-shortcode-testimonials', __('Témoignages mis en avant', 'wp-seed-content-kit'), '[seed_testimonials featured="only" limit="3" template="accueil"]');
    wp_seed_content_kit_render_usage_example('seed-usage-shortcode-quotes', __('Citation quotidienne', 'wp-seed-content-kit'), '[seed_quotes mode="daily" template="citation-du-jour"]');
    wp_seed_content_kit_render_usage_example('seed-usage-shortcode-directory', __('Annuaire', 'wp-seed-content-kit'), '[seed_directory status="practicing" orderby="display_order" order="asc"]');
    ?>
    <p><strong><?php esc_html_e('Attributs :', 'wp-seed-content-kit'); ?></strong> <?php esc_html_e('consultez l’onglet Collections pour les valeurs réellement acceptées par chaque module.', 'wp-seed-content-kit'); ?></p>
    <p><strong><?php esc_html_e('Compatibilité temporaire :', 'wp-seed-content-kit'); ?></strong> <code>[wp_seed_directory]</code> <?php esc_html_e('est un alias déprécié strictement identique. Utilisez [seed_directory] dans toute nouvelle page.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_usage_gutenberg()
{
    wp_seed_content_kit_render_usage_status('functional', __('Bloc Shortcode Core', 'wp-seed-content-kit'));
    ?>
    <ol>
        <li><?php esc_html_e('Ajoutez un bloc Shortcode dans la page.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Collez le shortcode produit dans l’onglet Collections.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Prévisualisez la page pour contrôler le rendu serveur.', 'wp-seed-content-kit'); ?></li>
    </ol>
    <?php wp_seed_content_kit_render_usage_example('seed-usage-gutenberg-directory', __('Exemple Annuaire', 'wp-seed-content-kit'), '[seed_directory]'); ?>
    <?php wp_seed_content_kit_render_usage_status('indirect', __('Block Bindings', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Le provider serveur couvre huit champs texte Citations et Témoignages dans les blocs Core Paragraphe et Titre. Une Query Loop doit fournir le contenu courant.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('unavailable', __('Interface dédiée', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Aucun bloc WP Seed ni sélecteur finalisé n’est fourni. Les Block Bindings ne couvrent pas encore Annuaire.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_usage_spectra()
{
    wp_seed_content_kit_render_usage_status('indirect', __('Intégration', 'wp-seed-content-kit'));
    ?>
    <ol>
        <li><?php esc_html_e('Ajoutez un bloc Shortcode Core, directement ou dans un Container Spectra.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Collez le shortcode WP Seed.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Utilisez éventuellement des blocs Spectra dans le contenu d’un Template.', 'wp-seed-content-kit'); ?></li>
    </ol>
    <?php wp_seed_content_kit_render_usage_example('seed-usage-spectra-directory', __('Exemple Annuaire', 'wp-seed-content-kit'), '[seed_directory template="annuaire-carte"]'); ?>
    <?php wp_seed_content_kit_render_usage_status('unavailable', __('Provider Spectra natif', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Aucun provider, bloc ou contrat de requête Spectra natif n’est fourni. Les métadonnées WP Seed ne doivent pas être lues directement.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_usage_divi()
{
    wp_seed_content_kit_render_usage_status('functional', __('Shortcode dans Divi', 'wp-seed-content-kit'));
    ?>
    <ol>
        <li><?php esc_html_e('Ajoutez un module Texte ou Code.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Collez le shortcode WP Seed dans le module.', 'wp-seed-content-kit'); ?></li>
        <li><?php esc_html_e('Enregistrez puis contrôlez la page publique.', 'wp-seed-content-kit'); ?></li>
    </ol>
    <?php wp_seed_content_kit_render_usage_example('seed-usage-divi-directory', __('Exemple Annuaire', 'wp-seed-content-kit'), '[seed_directory template="annuaire-carte"]'); ?>
    <?php wp_seed_content_kit_render_usage_status('indirect', __('Layout Divi Library', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Un Layout Divi Library publié peut servir de source à un Template WP Seed. Placez les placeholders dans un module Texte ou Code du layout.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('experimental', __('Dynamic Content Divi 5', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Les sources expérimentales couvrent Citations et Témoignages. Ces sources ne couvrent pas Annuaire et ne remplacent pas les Templates.', 'wp-seed-content-kit'); ?></p>
    <?php wp_seed_content_kit_render_usage_status('unavailable', __('Module Divi propriétaire', 'wp-seed-content-kit')); ?>
    <p><?php esc_html_e('Aucun module Divi propriétaire n’est fourni.', 'wp-seed-content-kit'); ?></p>
    <?php
}

function wp_seed_content_kit_render_usage_integrations()
{
    $current = wp_seed_content_kit_get_current_usage_integration_tab();
    wp_seed_content_kit_render_usage_integration_tabs($current);
    echo '<section id="seed-usage-integration-panel" class="seed-usage-section seed-usage-integration" role="tabpanel" tabindex="0" aria-labelledby="seed-usage-integration-tab-' . esc_attr($current) . '">';

    if ('gutenberg' === $current) {
        wp_seed_content_kit_render_usage_gutenberg();
    } elseif ('spectra' === $current) {
        wp_seed_content_kit_render_usage_spectra();
    } elseif ('divi' === $current) {
        wp_seed_content_kit_render_usage_divi();
    } else {
        wp_seed_content_kit_render_usage_shortcodes();
    }

    echo '</section>';
}
function wp_seed_content_kit_render_usage_page()
{
    if (!current_user_can('manage_wp_seed_integrations')) {
        wp_die(esc_html__('Vous n’avez pas l’autorisation d’accéder à l’utilisation de WP Seed Content Kit.', 'wp-seed-content-kit'));
    }

    $current = wp_seed_content_kit_get_current_usage_tab();
    ?>
    <div class="wrap seed-usage">
        <h1><?php esc_html_e('Utilisation', 'wp-seed-content-kit'); ?></h1>
        <p class="description"><?php esc_html_e('Comprendre les contenus, choisir une Collection, définir leur présentation et insérer le résultat dans une page.', 'wp-seed-content-kit'); ?></p>
        <?php wp_seed_content_kit_render_usage_tabs($current); ?>
        <div id="seed-usage-panel-<?php echo esc_attr($current); ?>" class="seed-usage-panel" role="tabpanel" tabindex="0" aria-labelledby="seed-usage-tab-<?php echo esc_attr($current); ?>">
            <?php if ('templates' === $current) : ?>
                <?php wp_seed_content_kit_render_usage_templates(); ?>
            <?php elseif ('collections' === $current) : ?>
                <?php wp_seed_content_kit_render_usage_collections(); ?>
            <?php elseif ('integrations' === $current) : ?>
                <?php wp_seed_content_kit_render_usage_integrations(); ?>
            <?php else : ?>
                <?php wp_seed_content_kit_render_usage_functioning(); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
function wp_seed_content_kit_render_directory_generator()
{
    ?>
    <details class="seed-usage-details">
        <summary><strong><?php esc_html_e('Générateur Annuaire', 'wp-seed-content-kit'); ?></strong></summary>
        <div class="seed-usage-generator" data-seed-usage-generator data-shortcode="seed_directory">
            <p><?php esc_html_e('Module : Annuaire. Les filtres sont appliqués par le serveur et ne sont jamais affichés aux visiteurs.', 'wp-seed-content-kit'); ?></p>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th><label for="seed-directory-generator-status"><?php esc_html_e('Statut professionnel', 'wp-seed-content-kit'); ?></label></th>
                        <td><select id="seed-directory-generator-status" data-seed-usage-attr="status" data-seed-usage-label="<?php esc_attr_e('statut', 'wp-seed-content-kit'); ?>" data-seed-usage-default="">
                            <option value=""><?php esc_html_e('Tous', 'wp-seed-content-kit'); ?></option>
                            <option value="practicing"><?php esc_html_e('En exercice', 'wp-seed-content-kit'); ?></option>
                            <option value="seeking_models"><?php esc_html_e('En recherche de modèles', 'wp-seed-content-kit'); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-department"><?php esc_html_e('Département', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-department" type="text" class="regular-text" data-seed-usage-attr="department" data-seed-usage-label="<?php esc_attr_e('département', 'wp-seed-content-kit'); ?>" data-seed-usage-default=""></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-country"><?php esc_html_e('Pays', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-country" type="text" class="regular-text" data-seed-usage-attr="country" data-seed-usage-label="<?php esc_attr_e('pays', 'wp-seed-content-kit'); ?>" data-seed-usage-default=""></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-featured"><?php esc_html_e('Mise en avant', 'wp-seed-content-kit'); ?></label></th>
                        <td><select id="seed-directory-generator-featured" data-seed-usage-attr="featured" data-seed-usage-label="<?php esc_attr_e('mise en avant', 'wp-seed-content-kit'); ?>" data-seed-usage-default="all">
                            <option value="all"><?php esc_html_e('Toutes les fiches', 'wp-seed-content-kit'); ?></option>
                            <option value="only"><?php esc_html_e('Uniquement les fiches mises en avant', 'wp-seed-content-kit'); ?></option>
                            <option value="exclude"><?php esc_html_e('Exclure les fiches mises en avant', 'wp-seed-content-kit'); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-ids"><?php esc_html_e('Identifiants', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-ids" type="text" class="regular-text code" inputmode="numeric" placeholder="12,34,56" data-seed-usage-attr="ids" data-seed-usage-label="<?php esc_attr_e('identifiants', 'wp-seed-content-kit'); ?>" data-seed-usage-default=""></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-limit"><?php esc_html_e('Limite', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-limit" type="number" min="0" max="100" value="0" data-seed-usage-attr="limit" data-seed-usage-label="<?php esc_attr_e('limite', 'wp-seed-content-kit'); ?>" data-seed-usage-default="0"></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-orderby"><?php esc_html_e('Trier par', 'wp-seed-content-kit'); ?></label></th>
                        <td><select id="seed-directory-generator-orderby" data-seed-usage-attr="orderby" data-seed-usage-label="<?php esc_attr_e('tri', 'wp-seed-content-kit'); ?>" data-seed-usage-default="display_order">
                            <option value="display_order"><?php esc_html_e('Ordre manuel puis nom', 'wp-seed-content-kit'); ?></option>
                            <option value="name"><?php esc_html_e('Nom', 'wp-seed-content-kit'); ?></option>
                            <option value="date"><?php esc_html_e('Date WordPress', 'wp-seed-content-kit'); ?></option>
                            <option value="id"><?php esc_html_e('Identifiant', 'wp-seed-content-kit'); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-order"><?php esc_html_e('Ordre', 'wp-seed-content-kit'); ?></label></th>
                        <td><select id="seed-directory-generator-order" data-seed-usage-attr="order" data-seed-usage-label="<?php esc_attr_e('ordre', 'wp-seed-content-kit'); ?>" data-seed-usage-default="asc">
                            <option value="asc"><?php esc_html_e('Croissant', 'wp-seed-content-kit'); ?></option>
                            <option value="desc"><?php esc_html_e('Décroissant', 'wp-seed-content-kit'); ?></option>
                        </select></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-template"><?php esc_html_e('Template facultatif', 'wp-seed-content-kit'); ?></label></th>
                        <td><input id="seed-directory-generator-template" class="regular-text code" type="text" data-seed-usage-attr="template" data-seed-usage-label="<?php esc_attr_e('Template', 'wp-seed-content-kit'); ?>" data-seed-usage-default="" placeholder="<?php esc_attr_e('Identifiant', 'wp-seed-content-kit'); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="seed-directory-generator-output"><?php esc_html_e('Shortcode généré', 'wp-seed-content-kit'); ?></label></th>
                        <td>
                            <div class="seed-usage-copy__controls">
                                <input id="seed-directory-generator-output" class="large-text code" type="text" readonly="readonly" value="[seed_directory]" data-seed-usage-output>
                                <button type="button" class="button" data-seed-usage-copy="seed-directory-generator-output" data-copy-label="<?php esc_attr_e('Copié.', 'wp-seed-content-kit'); ?>"><?php esc_html_e('Copier', 'wp-seed-content-kit'); ?></button>
                                <span class="seed-usage-copy__status" aria-live="polite"></span>
                            </div>
                            <p class="description" data-seed-usage-summary aria-live="polite"><?php esc_html_e('Toutes les fiches publiques, ordre manuel puis nom croissant, avec le rendu natif.', 'wp-seed-content-kit'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </details>
    <?php
}
