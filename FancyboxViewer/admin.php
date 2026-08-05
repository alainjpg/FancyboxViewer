<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template, $page, $conf;

include_once(PHPWG_PLUGINS_PATH . 'FancyboxViewer/main.inc.php');

if (isset($_POST['submit'])) {
    check_pwg_token();

    $config = array(
        'enabled'              => isset($_POST['enabled']),
        'fancybox_source'      => isset($_POST['fancybox_source']) ? $_POST['fancybox_source'] : 'cdn',
        'image_size'           => isset($_POST['image_size']) ? $_POST['image_size'] : 'xlarge',
        
        // --- NOUVELLES OPTIONS DE PORTE D'ENTRÉE ET CHARGEMENT ---
        'open_from_thumbnails' => isset($_POST['open_from_thumbnails']),
        'open_from_picture'    => isset($_POST['open_from_picture']),
        'load_full_album'      => isset($_POST['load_full_album']),
        
        'show_caption'         => isset($_POST['show_caption']),
        'show_description'     => isset($_POST['show_description']),
        'hide_auto_names'      => isset($_POST['hide_auto_names']),
        'page_link'            => isset($_POST['page_link']),
        'open_new_tab'         => isset($_POST['open_new_tab']),
        'enable_download'      => isset($_POST['enable_download']),
        'enable_zoom'          => isset($_POST['enable_zoom']),
        'enable_fullscreen'    => isset($_POST['enable_fullscreen']),
		'show_thumb_button'    => isset($_POST['show_thumb_button']),
        'enable_slideshow'     => isset($_POST['enable_slideshow']),
        'infinite'             => isset($_POST['infinite']),
        'slideshow_timeout'	   => isset($_POST['slideshow_timeout']) ? (int) $_POST['slideshow_timeout'] : 3000,
        'max_items_limit'      => isset($_POST['max_items_limit']) ? (int)$_POST['max_items_limit'] : 500,
        'filter_mode'          => isset($_POST['filter_mode']) ? $_POST['filter_mode'] : 'all',
        'album_categories'     => isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : array()
    );

    // Troisième paramètre true pour forcer le rafraîchissement immédiat du cache
    conf_update_param('fancybox_viewer', fancybox_viewer_serialize($config), true);
    $page['infos'][] = l10n('Information data registered');
}

$raw_conf = isset($conf['fancybox_viewer']) ? $conf['fancybox_viewer'] : null;
$config = $raw_conf ? fancybox_viewer_unserialize($raw_conf) : fancybox_viewer_get_default_config();

$query = '
SELECT id, name
  FROM ' . CATEGORIES_TABLE . '
  ORDER BY name ASC
;';
$result = pwg_query($query);
$categories = array();
while ($row = pwg_db_fetch_assoc($result)) {
    $categories[] = $row;
}

$template->assign(array(
    'conf_fancybox' => $config,
    'categories' => $categories,
    'PWG_TOKEN' => get_pwg_token(),
    'FANCYBOX_ADMIN_ACTION' => get_root_url() . 'admin.php?page=plugin-FancyboxViewer'
));

$template->set_filename('plugin_admin_content', dirname(__FILE__) . '/admin.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');