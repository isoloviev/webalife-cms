<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_WL_AdminMenu($params, &$smarty)
{
    global $db;
    $newMenu = array();
    $cats = $db->sql("SELECT id, title, image FROM " . PREFIX . "admin_cats WHERE lang = '" . SLANG . "' ORDER BY sort_id, binary(title)", 2);
    foreach ($cats as $cat) {
        $menus = $db->sql("SELECT * FROM " . PREFIX . "admin_items WHERE lang = '" . SLANG . "' AND cat_id = " . $cat['ID'] . " ORDER BY sort_id, binary(title)", 2);
        $newMenu[] = array('TITLE' => $cat['TITLE'], 'SMNU' => $menus);
    }
    $smarty->assign('AdminMenu', $newMenu);
}