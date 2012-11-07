<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_WL_AdminContent($params, Smarty_Internal_Template &$smarty)
{
    global $admin;
    // if template exists we will fetch it
    $p = CMS_TEMPLATE_PATH . 'globals/'.$_REQUEST['module'] . '/' . $_REQUEST['file'] . '.tpl';
    if (file_exists($p))
        echo $smarty->fetch($_REQUEST['module'] . '/' . $_REQUEST['file'] . '.tpl');
    // for backward compatibility
    // todo should be removed
    $admin->handler();
}