<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * ���������� ��� MKS Engine
 * ���������� �������� ����
 */

function smarty_function_WL_MainBotMenu($params, &$smarty)
{
    global $db, $site, $retTXT, $cnt;
    $retTXT = array();
    $cnt = 0;

    $menu = $db->sql("SELECT c.menu_title name_menu, p.page_path, p.page_parent pid, p.page_id id, c.record_id rid FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.inbot = 1 AND p.page_active = 1 AND p.page_type != 'auth' AND p.site_id = " . $_SESSION['CMS_SITE_ID'], 2);

    for ($i = 0; $i < count($menu); $i++) {
        $PagePath = $site->PrepareURL($menu[$i]['PAGE_PATH']);
        $active = preg_match($menu[$i]['PAGE_PATH'], "#".$site->get_url()."#");
        $retTXT[] = array("Text" => $menu[$i]['NAME_MENU'], "Active" => $active, "Link" => $PagePath, "Menu" => preg_replace('#/#', '', $menu[$i]['PAGE_PATH']));
    }

    if (sizeof($menu) > 0) $smarty->assign('BOTMENU', $retTXT);
}

function smarty_function_MKS_MainBotMenu_Build()
{
    global $mysql;
}