<?php
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, webmaster@mk-studio.ru
*/

require_once "../../wl_config.php";
require_once CMS_CLASSES . "admin.class.php";

if (isset($_REQUEST['logout'])) {
    session_unset();
    header("Location: index.php?a=logout");
    exit;
}

$p = CMS_MODULES . $_REQUEST['module'] . '/admin/' . $_REQUEST['file'] . '.php';
if ($_REQUEST['module'] && file_exists($p)) {
    require_once($p);
    exit;
}

global $CPANEL_LANG;
$admin = new admin('Desktop');
$admin->WorkSpaceTitle = 'Добро пожаловать!';

if (is_numeric($_REQUEST['ManageSite'])) {
    $_SESSION['SITE_ID'] = $_REQUEST['ManageSite'];
    $res = $db->sql("SELECT domain FROM " . PREFIX . "sites WHERE site_id = " . $_SESSION['SITE_ID'], 1);
    $_SESSION['SITE_NAME'] = preg_replace('#^www\.|/$#', '', $res['DOMAIN']);
    header("Location: index.php");
    exit;
}

$admin->main();
