<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_WL_MainMenu($params, &$smarty)
{
	global $site;
	$menu = $site->MainMenu(1, 0, $params['level']);
	$smarty->assign('Menu', $menu);
}

?>