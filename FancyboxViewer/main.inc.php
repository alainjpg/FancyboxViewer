<?php
/*
Plugin Name: Fancybox Viewer
Version: 0.0.1 BETA
Description: Ouvre directement les photos dans Fancybox depuis la vue miniatures avec navigation sur tout l'album et masquage intelligent des noms automatiques.
Plugin URI: https://piwigo.org
Author: AJPG
Author URI: https://piwigo.org
Has Admin: true
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

define('FANCYBOX_VIEWER_PATH', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');

load_language('plugin.lang', FANCYBOX_VIEWER_PATH);

add_event_handler('get_admin_plugin_menu_links', 'fancybox_viewer_admin_menu');

if (defined('IN_ADMIN')) {
    function fancybox_viewer_admin_menu($menu)
    {
        $menu[] = array(
            'NAME' => 'Fancybox Viewer',
            'URL'  => get_root_url() . 'admin.php?page=plugin-' . basename(dirname(__FILE__))
        );

        return $menu;
    }
}

function fancybox_viewer_get_default_config() {
    return array(
        'enabled'              => true,
        'fancybox_source'      => 'cdn',
        'image_size'           => 'xlarge',
        'open_from_thumbnails' => true,
        'open_from_picture'    => true,
        'load_full_album'      => true,
        'show_caption'         => true,
        'show_description'     => true,
        'hide_auto_names'      => true,
        'page_link'            => true,
        'open_new_tab'         => true,
        'enable_download'      => true,
        'enable_zoom'          => false,
        'enable_fullscreen'    => true,
		'show_thumb_button'  	=> true,
        'enable_slideshow'     => true,
        'infinite'             => true,
		'slideshow_timeout' => 3000,
        'max_items_limit'      => 500,
        'filter_mode'          => 'all',
        'album_categories'     => array()
    );
}

function fancybox_viewer_serialize($data) {
    return function_exists('safe_serialize') ? safe_serialize($data) : serialize($data);
}

function fancybox_viewer_unserialize($data) {
    if (empty($data)) return array();
    return function_exists('safe_unserialize') ? safe_unserialize($data) : @unserialize($data);
}

add_event_handler('loc_end_page_header', 'fancybox_viewer_inject');

function fancybox_viewer_inject() {
    global $page, $template, $conf;

    $config = isset($conf['fancybox_viewer']) ? fancybox_viewer_unserialize($conf['fancybox_viewer']) : fancybox_viewer_get_default_config();

    if (empty($config['enabled'])) {
        return;
    }

    $category_id = isset($page['category']['id']) ? (int)$page['category']['id'] : null;

    if ($category_id && !empty($config['filter_mode']) && $config['filter_mode'] !== 'all') {
        $selected_cats = !empty($config['album_categories']) ? array_map('intval', $config['album_categories']) : array();

        if ($config['filter_mode'] === 'include' && !in_array($category_id, $selected_cats, true)) {
            return;
        }
        if ($config['filter_mode'] === 'exclude' && in_array($category_id, $selected_cats, true)) {
            return;
        }
    }

    include_once(PHPWG_ROOT_PATH . 'include/functions_metadata.inc.php');

    // Récupération de la structure de catégorie complète pour les URLs
    $category_param = null;
    if (isset($page['category']) && isset($page['category']['name'])) {
        $category_param = $page['category'];
    } elseif ($category_id && function_exists('page_get_category_data')) {
        $category_param = page_get_category_data($category_id);
    }

    // Détermination des IDs selon la configuration (tout l'album ou page courante)
// Détermination des IDs selon la configuration
$items = array();

if (!empty($config['load_full_album'])) {

    // Toutes les photos de l'album
    $items = isset($page['items']) ? $page['items'] : array();

    $limit = !empty($config['max_items_limit']) ? (int)$config['max_items_limit'] : 500;
    if (count($items) > $limit) {
        $items = isset($page['row_ids']) ? $page['row_ids'] : array_slice($items, 0, $limit);
    }

} else {

    // Si on est sur la page des miniatures :
    $thumbnails = $template->get_template_vars('thumbnails');

    if (!empty($thumbnails) && is_array($thumbnails)) {

        foreach ($thumbnails as $thumb) {
            if (isset($thumb['id'])) {
                $items[] = (int)$thumb['id'];
            }
        }

    }
    // Si on est sur picture.php :
    elseif (isset($page['image']) && isset($page['image']['id'])) {

        $items[] = (int)$page['image']['id'];

    }
}

    $images_data = array();

    if (!empty($items)) {
        $clean_ids = implode(',', array_map('intval', $items));
        $query = '
        SELECT id, file, name, comment, path
          FROM ' . IMAGES_TABLE . '
          WHERE id IN (' . $clean_ids . ')
          ORDER BY FIELD(id, ' . $clean_ids . ')
        ;';

        $result = pwg_query($query);

        while ($row = pwg_db_fetch_assoc($result)) {
            $src_image = DerivativeImage::url($config['image_size'], $row);
            $original_src = get_element_path($row);

            $url_params = array('image_id' => $row['id']);
            if ($category_param) {
                $url_params['category'] = $category_param;
            }

            $page_url = function_exists('duplicate_picture_url') 
                ? duplicate_picture_url($url_params) 
                : make_picture_url(array('image_id' => $row['id'], 'image_type' => 'picture'));

            $images_data[] = array(
                'id'           => (int)$row['id'],
                'src'          => $src_image,
                'download_src' => $original_src,
                'page_url'     => $page_url,
                'name'         => isset($row['name']) ? $row['name'] : '',
                'comment'      => isset($row['comment']) ? $row['comment'] : '',
                'file'         => isset($row['file']) ? $row['file'] : ''
            );
        }
    }

    if (isset($config['fancybox_source']) && $config['fancybox_source'] === 'cdn') {
    $fancybox_css = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.14/dist/fancybox/fancybox.css';
    $fancybox_js  = 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.14/dist/fancybox/fancybox.umd.js';
    } else {
        $fancybox_css = FANCYBOX_VIEWER_PATH . 'vendor/fancybox/fancybox.css';
        $fancybox_js  = FANCYBOX_VIEWER_PATH . 'vendor/fancybox/fancybox.umd.js';
    }

    $html_head = '
    <link rel="stylesheet" href="' . $fancybox_css . '" />
    <link rel="stylesheet" href="' . FANCYBOX_VIEWER_PATH . 'css/fancybox-viewer.css" />
    <script src="' . $fancybox_js . '"></script>
    <script type="text/javascript">
var FANCYBOX_VIEWER_DATA = ' . json_encode(array(
    'config'           => $config,
    'category_id'      => $category_id,
    'current_image_id' => isset($page['image_id']) ? (int)$page['image_id'] : 0,
    'items'            => $images_data,
    'lang'             => array(
        'page_link' => l10n('Open the photo page'),
        'autoplay'  => l10n('Start / Stop slideshow')
    )
)) . ';
    </script>
    <script type="text/javascript" src="' . FANCYBOX_VIEWER_PATH . 'js/fancybox-viewer.js"></script>
    ';

    $template->append('head_elements', $html_head);
}